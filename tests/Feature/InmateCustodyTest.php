<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\AuditEvent;
use App\Models\CustodyLocation;
use App\Models\Discharge;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\LegalReference;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\CanonicalDemoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\B1DatabaseTestCase;

class InmateCustodyTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(CanonicalDemoSeeder::class);
    }

    protected function createOfficer(string $roleSlug, ?int $facilityId = null): User
    {
        $user = User::factory()->create([
            'email' => 'officer.'.uniqid().'@demo.ups.go.ug',
            'status' => 'Active',
        ]);

        $role = Role::where('slug', $roleSlug)->firstOrFail();

        if ($facilityId !== null) {
            $scope = AccessScope::firstOrCreate(
                ['kind' => 'Facility', 'facility_id' => $facilityId],
                ['scope_key' => 'SCOPE-FAC-'.$facilityId]
            );
        } else {
            $scope = AccessScope::where('kind', 'National')->firstOrFail();
        }

        UserRoleAssignment::create([
            'assignment_ref' => 'URA-TEST-'.strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'role_id' => $role->id,
            'access_scope_id' => $scope->id,
            'granted_at' => now(),
        ]);

        return $user;
    }

    public function test_authorized_officer_can_admit_new_inmate_with_custody_episode_location_and_audit(): void
    {
        $facility = Facility::firstOrFail();
        $officer = $this->createOfficer('records_officer', $facility->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $warrantRef = 'WAR-'.rand(1000, 9999);
        $firstName = 'Kato_'.Str::random(5);
        $lastName = 'Muwanga_'.Str::random(5);

        $payload = [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'middle_name' => 'John',
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1996-04-12',
            'nationality' => 'Ugandan',
            'district' => 'Mukono',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Remand',
            'legal_basis' => 'Chief Magistrate Remand Order',
            'warrant_ref' => $warrantRef,
            'issuing_authority' => 'Chief Magistrates Court Mukono',
            'admission_date' => now()->toDateString(),
            'facility_id' => $facility->id,
            'kin_name' => 'Sarah Muwanga',
            'kin_relationship' => 'Sister',
            'kin_contact' => '+256770123456',
            'notes' => 'Admitted with standard remand documentation.',
        ];

        $response = $this->actingAs($officer)
            ->post(route('inmates.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Verify Inmate created
        $inmate = Inmate::where('id_number', $idNumber)->first();
        $this->assertNotNull($inmate);
        $this->assertSame($firstName, $inmate->first_name);
        $this->assertSame($lastName, $inmate->last_name);
        $this->assertSame($facility->id, $inmate->currentFacilityId());

        // Verify Admission Episode
        $episode = AdmissionEpisode::where('inmate_id', $inmate->id)->first();
        $this->assertNotNull($episode);
        $this->assertSame('Active', $episode->state);
        $this->assertNull($episode->closed_at);
        $this->assertSame($facility->id, $episode->admitting_facility_id);

        // Verify Custody Location
        $location = CustodyLocation::where('admission_episode_id', $episode->id)->first();
        $this->assertNotNull($location);
        $this->assertSame($facility->id, $location->facility_id);
        $this->assertNull($location->left_at);

        // Verify Legal Reference
        $legal = LegalReference::where('admission_episode_id', $episode->id)->first();
        $this->assertNotNull($legal);
        $this->assertSame($warrantRef, $legal->external_reference);

        // Verify Audit Event
        $audit = AuditEvent::where('action', 'inmate_admitted')
            ->where('resource_reference', $inmate->prisoner_number)
            ->first();
        $this->assertNotNull($audit);
        $this->assertSame($officer->id, $audit->actor_id);
        $this->assertSame($facility->id, $audit->facility_id);
        $this->assertSame('Success', $audit->outcome);
    }

    public function test_duplicate_admission_with_active_episode_is_rejected(): void
    {
        $facility = Facility::firstOrFail();
        $officer = $this->createOfficer('records_officer', $facility->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'Geoffrey_'.Str::random(5);
        $lastName = 'Okello_'.Str::random(5);

        $payload = [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1990-08-20',
            'nationality' => 'Ugandan',
            'district' => 'Gulu',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Remand',
            'legal_basis' => 'Remand Warrant',
            'warrant_ref' => 'WAR-DUP-1',
            'facility_id' => $facility->id,
        ];

        // First admission succeeds
        $this->actingAs($officer)->post(route('inmates.store'), $payload)->assertRedirect();

        // Second admission attempt for same person while active must be rejected
        $secondResponse = $this->actingAs($officer)->post(route('inmates.store'), $payload);
        $secondResponse->assertSessionHasErrors(['inmate']);
    }

    public function test_readmission_of_discharged_inmate_creates_new_episode_without_duplicate_inmate(): void
    {
        $facility = Facility::firstOrFail();
        $recordsOfficer = $this->createOfficer('records_officer', $facility->id);
        $oicOfficer = $this->createOfficer('facility_officer_in_charge', $facility->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'Ronald_'.Str::random(5);
        $lastName = 'Semakula_'.Str::random(5);

        $payload = [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1988-12-05',
            'nationality' => 'Ugandan',
            'district' => 'Masaka',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Convicted',
            'legal_basis' => 'Court Conviction Warrant',
            'warrant_ref' => 'CONV-WAR-'.rand(1000, 9999),
            'facility_id' => $facility->id,
        ];

        // 1. Initial admission
        $this->actingAs($recordsOfficer)->post(route('inmates.store'), $payload)->assertRedirect();
        $inmate = Inmate::where('id_number', $idNumber)->firstOrFail();

        // 2. Discharge inmate
        $this->actingAs($oicOfficer)->post(route('inmates.discharge.store', $inmate->id), [
            'discharge_reason' => 'Sentence Served',
            'discharge_date' => now()->toDateString(),
            'auth_ref' => 'REL-CERT-'.rand(1000, 9999),
            'auth_authority' => 'Uganda Prisons Service HQ',
        ])->assertRedirect();

        // Verify first episode closed
        $firstEpisode = AdmissionEpisode::where('inmate_id', $inmate->id)->firstOrFail();
        $this->assertNotNull($firstEpisode->closed_at);
        $this->assertSame('Discharged', $firstEpisode->state);

        // 3. Readmit inmate with new charge/episode
        $readmissionPayload = $payload;
        $readmissionPayload['classification'] = 'Remand';
        $readmissionPayload['legal_basis'] = 'Fresh High Court Remand Warrant';
        $readmissionPayload['warrant_ref'] = 'NEW-WAR-'.rand(1000, 9999);

        $readmitResponse = $this->actingAs($recordsOfficer)->post(route('inmates.store'), $readmissionPayload);
        $readmitResponse->assertRedirect();

        // Inmate record should NOT be duplicated
        $this->assertSame(1, Inmate::where('id_number', $idNumber)->count());

        // Admission episodes should now be 2
        $episodes = AdmissionEpisode::where('inmate_id', $inmate->id)->orderBy('id')->get();
        $this->assertCount(2, $episodes);
        $this->assertNotNull($episodes[0]->closed_at);
        $this->assertNull($episodes[1]->closed_at);
        $this->assertSame('Active', $episodes[1]->state);

        // Audit event should note is_readmission = true
        $audit = AuditEvent::where('action', 'inmate_admitted')
            ->where('resource_reference', $inmate->prisoner_number)
            ->latest('id')
            ->first();
        $this->assertNotNull($audit);
        $this->assertTrue($audit->changes['is_readmission']);
    }

    public function test_admission_validation_rejects_missing_required_fields_and_future_birth_date(): void
    {
        $facility = Facility::firstOrFail();
        $officer = $this->createOfficer('records_officer', $facility->id);

        $response = $this->actingAs($officer)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => '',
            'last_name' => '',
            'gender' => 'UnknownGender',
            'date_of_birth' => now()->addDays(5)->toDateString(), // future date
            'classification' => 'InvalidClassification',
            'legal_basis' => '',
        ]);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'date_of_birth',
            'classification',
            'legal_basis',
        ]);
    }

    public function test_authorized_officer_can_update_inmate_details_generating_before_after_audit(): void
    {
        $facility = Facility::firstOrFail();
        $recordsOfficer = $this->createOfficer('records_officer', $facility->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'Sam_'.Str::random(5);
        $lastName = 'Bwambale_'.Str::random(5);

        $this->actingAs($recordsOfficer)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1992-03-10',
            'nationality' => 'Ugandan',
            'district' => 'Kasese',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Remand',
            'legal_basis' => 'Remand Warrant',
            'facility_id' => $facility->id,
            'kin_name' => 'Initial Kin',
            'kin_contact' => '+256700000000',
        ])->assertRedirect();

        $inmate = Inmate::where('id_number', $idNumber)->firstOrFail();

        // Update ordinary details
        $updateResponse = $this->actingAs($recordsOfficer)->post(route('inmates.update', $inmate->id), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'nationality' => 'Ugandan',
            'district' => 'Kasese Updated',
            'kin_name' => 'Janet Bwambale',
            'kin_relationship' => 'Wife',
            'kin_contact' => '+256788990011',
            'notes' => 'Updated emergency contact number.',
        ]);

        $updateResponse->assertRedirect(route('inmates.show', $inmate->id));
        $updateResponse->assertSessionHas('status');

        $inmate->refresh();
        $this->assertSame('Kasese Updated', $inmate->district);
        $this->assertSame('Janet Bwambale', $inmate->kin_name);

        // Verify audit event
        $audit = AuditEvent::where('action', 'inmate_updated')
            ->where('resource_reference', $inmate->prisoner_number)
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($recordsOfficer->id, $audit->actor_id);
        $this->assertSame('Initial Kin', $audit->changes['before']['kin_name']);
        $this->assertSame('Janet Bwambale', $audit->changes['after']['kin_name']);
    }

    public function test_authorized_officer_can_discharge_inmate_closing_episode_and_location(): void
    {
        $facility = Facility::firstOrFail();
        $recordsOfficer = $this->createOfficer('records_officer', $facility->id);
        $oicOfficer = $this->createOfficer('facility_officer_in_charge', $facility->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'Patrick_'.Str::random(5);
        $lastName = 'Ochen_'.Str::random(5);

        $this->actingAs($recordsOfficer)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1991-07-22',
            'nationality' => 'Ugandan',
            'district' => 'Lira',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Remand',
            'legal_basis' => 'Court Remand Warrant',
            'facility_id' => $facility->id,
        ])->assertRedirect();

        $inmate = Inmate::where('id_number', $idNumber)->firstOrFail();
        $episode = AdmissionEpisode::where('inmate_id', $inmate->id)->firstOrFail();
        $location = CustodyLocation::where('admission_episode_id', $episode->id)->firstOrFail();

        $dischargeResponse = $this->actingAs($oicOfficer)->post(route('inmates.discharge.store', $inmate->id), [
            'discharge_reason' => 'Bail Granted',
            'discharge_date' => now()->toDateString(),
            'auth_ref' => 'BAIL-ORD-'.rand(1000, 9999),
            'auth_authority' => 'High Court Lira',
            'discharge_notes' => 'Released on bail following court order.',
        ]);

        $dischargeResponse->assertRedirect(route('inmates.show', $inmate->id));
        $dischargeResponse->assertSessionHas('status');

        $episode->refresh();
        $location->refresh();

        $this->assertSame('Discharged', $episode->state);
        $this->assertNotNull($episode->closed_at);
        $this->assertNotNull($location->left_at);

        // Verify Discharge model
        $discharge = Discharge::where('admission_episode_id', $episode->id)->first();
        $this->assertNotNull($discharge);
        $this->assertSame('Executed', $discharge->state);
        $this->assertSame('Bail Granted', $discharge->reason);
        $this->assertSame($oicOfficer->id, $discharge->authorized_by);

        // Verify Audit Event
        $audit = AuditEvent::where('action', 'inmate_discharged')
            ->where('resource_reference', $inmate->prisoner_number)
            ->latest('id')
            ->first();
        $this->assertNotNull($audit);
        $this->assertSame('Bail Granted', $audit->changes['reason']);
    }

    public function test_cannot_discharge_already_discharged_inmate(): void
    {
        $facility = Facility::firstOrFail();
        $recordsOfficer = $this->createOfficer('records_officer', $facility->id);
        $oicOfficer = $this->createOfficer('facility_officer_in_charge', $facility->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'Moses_'.Str::random(5);
        $lastName = 'Kasirye_'.Str::random(5);

        $this->actingAs($recordsOfficer)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1987-09-14',
            'nationality' => 'Ugandan',
            'district' => 'Wakiso',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Convicted',
            'legal_basis' => 'Conviction Warrant',
            'facility_id' => $facility->id,
        ])->assertRedirect();

        $inmate = Inmate::where('id_number', $idNumber)->firstOrFail();

        // 1. First discharge succeeds
        $this->actingAs($oicOfficer)->post(route('inmates.discharge.store', $inmate->id), [
            'discharge_reason' => 'Court Acquittal',
            'discharge_date' => now()->toDateString(),
            'auth_ref' => 'ACQ-ORD-1',
            'auth_authority' => 'Court of Appeal',
        ])->assertRedirect();

        // 2. Second discharge attempt must fail
        $secondDischarge = $this->actingAs($oicOfficer)->post(route('inmates.discharge.store', $inmate->id), [
            'discharge_reason' => 'Court Acquittal',
            'discharge_date' => now()->toDateString(),
            'auth_ref' => 'ACQ-ORD-2',
            'auth_authority' => 'Court of Appeal',
        ]);

        $secondDischarge->assertSessionHasErrors(['inmate']);
    }

    public function test_officer_without_discharge_permission_is_forbidden(): void
    {
        $facility = Facility::firstOrFail();
        $recordsOfficer = $this->createOfficer('records_officer', $facility->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'Fred_'.Str::random(5);
        $lastName = 'Mukasa_'.Str::random(5);

        $this->actingAs($recordsOfficer)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1994-01-18',
            'nationality' => 'Ugandan',
            'district' => 'Mpigi',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Remand',
            'legal_basis' => 'Remand Warrant',
            'facility_id' => $facility->id,
        ])->assertRedirect();

        $inmate = Inmate::where('id_number', $idNumber)->firstOrFail();

        // Records officer does NOT have inmates.discharge permission
        $response = $this->actingAs($recordsOfficer)->post(route('inmates.discharge.store', $inmate->id), [
            'discharge_reason' => 'Bail Granted',
            'discharge_date' => now()->toDateString(),
            'auth_ref' => 'BAIL-1',
            'auth_authority' => 'High Court',
        ]);

        $response->assertForbidden();
    }

    public function test_officer_without_admit_permission_is_forbidden(): void
    {
        $facility = Facility::firstOrFail();
        $auditor = $this->createOfficer('auditor', $facility->id);

        $response = $this->actingAs($auditor)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => 'Test_'.Str::random(5),
            'last_name' => 'Person_'.Str::random(5),
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'classification' => 'Remand',
            'legal_basis' => 'Order',
            'facility_id' => $facility->id,
        ]);

        $response->assertForbidden();
    }

    public function test_facility_scoped_officer_cannot_admit_inmate_to_another_facility(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        // Officer strictly assigned to Facility 1
        $officer = $this->createOfficer('records_officer', $fac1->id);

        // Attempting to admit into Facility 2
        $response = $this->actingAs($officer)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => 'Cross_'.Str::random(5),
            'last_name' => 'Facility_'.Str::random(5),
            'gender' => 'Male',
            'date_of_birth' => '1993-06-11',
            'classification' => 'Remand',
            'legal_basis' => 'Warrant',
            'facility_id' => $fac2->id,
        ]);

        $response->assertForbidden();
    }

    public function test_facility_scoped_officer_cannot_discharge_inmate_at_another_facility(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        // Officer at Facility 2 admits an inmate into Facility 2
        $fac2Officer = $this->createOfficer('facility_officer_in_charge', $fac2->id);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'Other_'.Str::random(5);
        $lastName = 'Prisoner_'.Str::random(5);

        $this->actingAs($fac2Officer)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'date_of_birth' => '1992-10-10',
            'nationality' => 'Ugandan',
            'district' => 'Jinja',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Remand',
            'legal_basis' => 'Warrant',
            'facility_id' => $fac2->id,
        ])->assertRedirect();

        $inmate = Inmate::where('id_number', $idNumber)->firstOrFail();

        // Officer at Facility 1 attempts to discharge inmate held at Facility 2
        $fac1Officer = $this->createOfficer('facility_officer_in_charge', $fac1->id);

        $response = $this->actingAs($fac1Officer)->post(route('inmates.discharge.store', $inmate->id), [
            'discharge_reason' => 'Sentence Served',
            'discharge_date' => now()->toDateString(),
            'auth_ref' => 'DCH-CROSS-1',
            'auth_authority' => 'High Court',
        ]);

        $response->assertNotFound();
    }

    public function test_national_scope_officer_can_admit_and_discharge_across_any_facility(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        // Officer with National scope
        $nationalAdmin = $this->createOfficer('system_admin', null);

        $idNumber = 'CM'.rand(10000000, 99999999).'UG';
        $firstName = 'National_'.Str::random(5);
        $lastName = 'Inmate_'.Str::random(5);

        // 1. Admit at Facility 2
        $admitResponse = $this->actingAs($nationalAdmin)->post(route('inmates.store'), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Female',
            'date_of_birth' => '1995-11-20',
            'nationality' => 'Ugandan',
            'district' => 'Arua',
            'id_type' => 'National ID',
            'id_number' => $idNumber,
            'classification' => 'Convicted',
            'legal_basis' => 'High Court Sentence',
            'facility_id' => $fac2->id,
        ]);

        $admitResponse->assertRedirect();
        $inmate = Inmate::where('id_number', $idNumber)->firstOrFail();
        $this->assertSame($fac2->id, $inmate->currentFacilityId());

        // 2. Discharge at Facility 2
        $dischargeResponse = $this->actingAs($nationalAdmin)->post(route('inmates.discharge.store', $inmate->id), [
            'discharge_reason' => 'Presidential Pardon',
            'discharge_date' => now()->toDateString(),
            'auth_ref' => 'PARDON-'.rand(1000, 9999),
            'auth_authority' => 'Presidential Prerogative of Mercy',
        ]);

        $dischargeResponse->assertRedirect(route('inmates.show', $inmate->id));

        $episode = AdmissionEpisode::where('inmate_id', $inmate->id)->firstOrFail();
        $this->assertSame('Discharged', $episode->state);
    }

    public function test_inmates_directory_renders_successfully_with_database_records(): void
    {
        $facility = Facility::firstOrFail();
        $officer = $this->createOfficer('records_officer', $facility->id);

        $response = $this->actingAs($officer)->get(route('inmates.index'));

        $response->assertOk();
        $response->assertViewHas('counts');
        $response->assertViewHas('inmates');
        $response->assertSee('In Custody');
    }
}
