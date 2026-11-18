<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AuditEvent;
use App\Models\Document;
use App\Models\DocumentLink;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AuditService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\B1DatabaseTestCase;

class InmatePhotoTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    /** @param list<string> $permissions */
    private function officer(Facility $facility, array $permissions = ['inmates.admit', 'inmates.read', 'inmates.edit']): User
    {
        $user = User::factory()->create(['status' => 'Active']);
        $role = Role::factory()->create();
        foreach ($permissions as $permission) {
            $role->permissions()->attach(Permission::firstOrCreate(['slug' => $permission], ['name' => $permission, 'module' => 'Inmates']));
        }
        $scope = AccessScope::where('kind', 'Facility')->where('facility_id', $facility->id)->first()
            ?? AccessScope::factory()->create(['kind' => 'Facility', 'facility_id' => $facility->id]);
        UserRoleAssignment::factory()->create(['user_id' => $user->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);

        return $user;
    }

    /** @return array<string, mixed> */
    private function payload(Facility $facility): array
    {
        return [
            'first_name' => 'Fictional', 'last_name' => 'Photo Inmate', 'gender' => 'Male',
            'date_of_birth' => '1995-01-15', 'classification' => 'Remand',
            'legal_basis' => 'Fictional court order', 'facility_id' => $facility->id,
        ];
    }

    public function test_admission_uploads_a_private_normalized_photo_and_displays_its_authorized_url(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $officer = $this->officer($facility);
        $original = UploadedFile::fake()->image('private-client-name.png', 100, 160);
        $photo = UploadedFile::fake()->createWithContent('private-client-name.png', $original->getContent().'UNTRUSTED-TRAILER-12345');
        $this->actingAs($officer)->post(route('inmates.store'), $this->payload($facility) + ['photo' => $photo])->assertSessionHasNoErrors()->assertRedirect();
        $inmate = Inmate::firstOrFail();
        $document = $inmate->photoLink->document;
        Storage::disk('local')->assertExists($document->storage_key);
        Storage::disk('public')->assertMissing($document->storage_key);
        $bytes = Storage::disk('local')->get($document->storage_key);
        $this->assertSame('image/jpeg', getimagesizefromstring($bytes)['mime']);
        $this->assertStringNotContainsString('UNTRUSTED-TRAILER-12345', $bytes);
        $this->assertSame($officer->id, $document->uploaded_by);
        $this->assertSame(hash('sha256', $bytes), $document->sha256);
        $this->get(route('inmates.show', $inmate))->assertOk()->assertSee(route('inmates.photo', $inmate), false)->assertDontSee('inmate-photos/');
        $inmate->update(['id_number' => '12']);
        $this->get(route('inmates.show', $inmate))->assertOk()->assertSee('••');
        $this->get(route('inmates.create'))->assertOk()->assertSee('name="photo"', false)->assertDontSee('Photo is not uploaded');
        $response = $this->get(route('inmates.photo', $inmate))->assertOk()->assertHeader('Content-Type', 'image/jpeg')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame($bytes, $response->streamedContent());
        $this->assertDatabaseHas('audit_events', ['action' => 'inmate_photo_uploaded']);
        $this->assertDatabaseHas('audit_events', ['action' => 'inmate_photo_viewed']);
    }

    public function test_new_admissions_require_a_photograph_without_creating_partial_custody_records(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $officer = $this->officer($facility);
        $this->actingAs($officer)->post(route('inmates.store'), $this->payload($facility))->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('inmates', 0);
        $this->assertDatabaseCount('admission_episodes', 0);
        $this->assertDatabaseCount('custody_locations', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->get(route('inmates.create'))->assertOk()->assertSee('type="file" required', false)->assertDontSee('(optional)</span></p>', false);
    }

    public function test_invalid_oversized_or_excessive_dimension_images_do_not_create_admissions_or_files(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $this->actingAs($this->officer($facility));
        $photos = [
            UploadedFile::fake()->createWithContent('pretend.jpg', '<?php echo "not a photo";'),
            UploadedFile::fake()->createWithContent('unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->image('large.jpg')->size(2049),
            UploadedFile::fake()->image('wide.png', 3001, 1),
        ];
        foreach ($photos as $photo) {
            $this->post(route('inmates.store'), $this->payload($facility) + ['photo' => $photo])->assertSessionHasErrors('photo');
        }
        $this->assertSame(0, Inmate::count());
        $this->assertSame(0, Document::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_photo_reads_and_edits_require_authentication_and_the_correct_facility_scope(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $owner = $this->officer($facility);
        $this->actingAs($owner)->post(route('inmates.store'), $this->payload($facility) + ['photo' => UploadedFile::fake()->image('portrait.jpg')])->assertSessionHasNoErrors();
        $inmate = Inmate::firstOrFail();
        $other = $this->officer(Facility::factory()->create(['status' => 'Active']));
        $this->actingAs($other)->get(route('inmates.photo', $inmate))->assertNotFound();
        $this->post(route('inmates.update', $inmate), $this->payload($facility) + ['photo' => UploadedFile::fake()->image('replacement.png')])->assertNotFound();
        $this->post(route('inmates.store'), $this->payload($facility) + ['photo' => UploadedFile::fake()->image('forbidden.png')])->assertForbidden();
        $this->assertSame(1, Document::count());
        $admitter = $this->officer($facility, ['inmates.admit']);
        $this->actingAs($admitter)->get(route('inmates.photo', $inmate))->assertNotFound();
        $reader = $this->officer($facility, ['inmates.read']);
        $this->actingAs($reader)->post(route('inmates.update', $inmate), $this->payload($facility) + ['photo' => UploadedFile::fake()->image('read-only.png')])->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->get(route('inmates.photo', $inmate))->assertRedirect(route('login'));
    }

    public function test_editing_can_add_or_replace_a_photo_and_unrelated_documents_do_not_hide_it(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $officer = $this->officer($facility);
        $payload = $this->payload($facility);
        $this->actingAs($officer)->post(route('inmates.store'), $payload + ['photo' => UploadedFile::fake()->image('admission.jpg')])->assertSessionHasNoErrors();
        $inmate = Inmate::firstOrFail();
        $inmate->photoLink()->delete();
        $inmate = Inmate::firstOrFail();
        $this->get(route('inmates.photo', $inmate))->assertNotFound();
        $this->post(route('inmates.update', $inmate), $payload + ['photo' => UploadedFile::fake()->image('first.jpg')])->assertSessionHasNoErrors();
        $first = $inmate->fresh()->photoLink->document;
        $this->post(route('inmates.update', $inmate), $payload)->assertSessionHasNoErrors();
        $this->assertSame($first->id, $inmate->fresh()->photoLink->document_id);
        $this->post(route('inmates.update', $inmate), $payload + ['photo' => UploadedFile::fake()->image('second.webp', 40, 60)])->assertSessionHasNoErrors();
        $latest = $inmate->fresh()->photoLink->document;
        $this->assertNotSame($first->id, $latest->id);
        DocumentLink::factory()->create(['inmate_id' => $inmate->id, 'purpose' => 'Other legal metadata']);
        $this->assertSame($latest->id, $inmate->fresh()->photoLink->document_id);
        Storage::disk('local')->assertExists($first->storage_key);
        Storage::disk('local')->assertExists($latest->storage_key);
        $this->assertSame(Storage::disk('local')->get($latest->storage_key), $this->get(route('inmates.photo', $inmate))->assertOk()->streamedContent());
        $this->get(route('inmates.edit', $inmate))->assertOk()->assertSee('Choose photograph');
    }

    public function test_storage_failure_rolls_back_the_admission_and_audit(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $officer = $this->officer($facility);
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('put')->once()->andReturn(false);
        $disk->shouldReceive('delete')->once()->andReturn(true);
        Storage::shouldReceive('disk')->with('local')->andReturn($disk);
        $this->actingAs($officer)->post(route('inmates.store'), $this->payload($facility) + ['photo' => UploadedFile::fake()->image('portrait.jpg')])->assertSessionHasErrors('photo');
        $this->assertSame(0, Inmate::count());
        $this->assertSame(0, Document::count());
        $this->assertSame(0, AuditEvent::where('action', 'inmate_admitted')->count());
    }

    public function test_audit_failure_after_file_storage_removes_the_uncommitted_file_and_records(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $officer = $this->officer($facility);
        $audit = $this->partialMock(AuditService::class);
        $audit->shouldReceive('log')->passthru()->byDefault();
        $audit->shouldReceive('log')->withArgs(fn (string $action, mixed ...$arguments): bool => $action === 'inmate_photo_uploaded')->once()->andThrow(new RuntimeException('Fictional photo audit failure'));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($officer)->post(route('inmates.store'), $this->payload($facility) + ['photo' => UploadedFile::fake()->image('portrait.png')]);
            $this->fail('An unaudited upload must not succeed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fictional photo audit failure', $exception->getMessage());
        }
        $this->assertSame(0, Inmate::count());
        $this->assertSame(0, Document::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
