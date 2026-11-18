<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Models\Visitor;
use App\Support\B1DatabaseGuard;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class VisitorConcurrencyTest extends TestCase
{
    public function test_concurrent_requests_checkins_and_checkouts_are_serialized_without_duplicate_events(): void
    {
        config(['database.default' => 'b1_test']);
        app(B1DatabaseGuard::class)->assertAllowed('b1_test', disposable: true);
        $this->artisan('migrate', ['--database' => 'b1_test', '--no-interaction' => true])->assertSuccessful();

        try {
            $facility = Facility::factory()->create(['status' => 'Active']);
            $episode = AdmissionEpisode::factory()->create(['admitting_facility_id' => $facility->id, 'state' => 'Active']);
            CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
            $visitor = Visitor::factory()->create(['registered_facility_id' => $facility->id]);
            $actor = User::factory()->create(['status' => 'Active']);
            $role = Role::firstOrCreate(['slug' => 'system_admin'], ['name' => 'Fictional concurrency administrator']);
            $scope = AccessScope::firstOrCreate(['kind' => 'National'], ['scope_key' => 'CONCURRENCY-NATIONAL']);
            UserRoleAssignment::factory()->create(['user_id' => $actor->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);
            $payload = [
                'facility_id' => $facility->id, 'admission_episode_id' => $episode->id, 'visitor_id' => $visitor->id,
                'scheduled_start_at' => now(config('visitation.timezone'))->addDay()->format('Y-m-d\TH:i'),
                'scheduled_end_at' => now(config('visitation.timezone'))->addDay()->addMinutes(30)->format('Y-m-d\TH:i'),
                'purpose' => 'Family Visit', 'relationship' => 'Mother',
            ];
            $this->assertSame(['accepted', 'rejected'], $this->runRace($actor->id, 'request', 0, $payload));
            $this->assertSame(1, Visit::where('visitor_id', $visitor->id)->count());
            $visit = Visit::where('visitor_id', $visitor->id)->firstOrFail();
            $visit->update([
                'state' => 'Scheduled', 'scheduled_start_at' => now()->subMinute(),
                'scheduled_end_at' => now()->addMinutes(30),
            ]);
            $this->assertSame(['accepted', 'rejected'], $this->runRace($actor->id, 'check-in', $visit->id));
            $this->assertSame('Checked In', $visit->fresh()->state);
            $this->assertSame(1, $visit->events()->where('event_type', 'check-in')->count());
            $this->assertSame(['accepted', 'rejected'], $this->runRace($actor->id, 'check-out', $visit->id));
            $this->assertSame('Completed', $visit->fresh()->state);
            $this->assertSame(1, $visit->events()->where('event_type', 'check-out')->count());
        } finally {
            app(B1DatabaseGuard::class)->assertAllowed('b1_test', disposable: true);
            $this->artisan('migrate:fresh', ['--database' => 'b1_test', '--no-interaction' => true])->assertSuccessful();
            RefreshDatabaseState::$migrated = true;
        }
    }

    /** @param array<string, mixed> $payload
     * @return list<string>
     */
    private function runRace(int $actorId, string $action, int $visitId, array $payload = []): array
    {
        $code = <<<'PHP'
        require getcwd()."/vendor/autoload.php";
        $app = require getcwd()."/bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        config(["database.default" => "b1_test"]);
        app(App\Support\B1DatabaseGuard::class)->assertAllowed("b1_test", disposable: true);
        $actor = App\Models\User::findOrFail((int) $argv[1]);
        try {
            if ($argv[2] === "request") {
                app(App\Services\VisitService::class)->request(json_decode($argv[4], true, flags: JSON_THROW_ON_ERROR), $actor);
            } else {
                $visit = App\Models\Visit::findOrFail((int) $argv[3]);
                app(App\Services\VisitService::class)->transition($visit, $argv[2], ["reason" => "Fictional concurrency test", "identity_verified" => true], $actor);
            }
            echo "accepted";
        } catch (Illuminate\Validation\ValidationException) {
            echo "rejected";
        }
        PHP;
        $command = [PHP_BINARY, '-r', $code, (string) $actorId, $action, (string) $visitId, json_encode($payload, JSON_THROW_ON_ERROR)];
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'b1_test', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $first = new Process($command, base_path(), $environment, timeout: 60);
        $second = new Process($command, base_path(), $environment, timeout: 60);
        $first->start();
        $second->start();
        $first->wait();
        $second->wait();
        $this->assertTrue($first->isSuccessful(), $first->getErrorOutput());
        $this->assertTrue($second->isSuccessful(), $second->getErrorOutput());
        $outcomes = [trim($first->getOutput()), trim($second->getOutput())];
        sort($outcomes);

        return $outcomes;
    }
}
