<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Personnel;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\CanonicalDemoSeeder;
use Symfony\Component\Process\Process;
use Tests\B1DatabaseTestCase;

class BackendFormsTest extends B1DatabaseTestCase
{
    public function test_operational_forms_render_authorized_submission_actions_with_valid_inline_javascript(): void
    {
        $this->seed(CanonicalDemoSeeder::class);
        $admin = User::factory()->create(['status' => 'Active']);
        UserRoleAssignment::factory()->create([
            'user_id' => $admin->id, 'role_id' => Role::where('slug', 'system_admin')->firstOrFail()->id,
            'access_scope_id' => AccessScope::where('kind', 'National')->firstOrFail()->id,
        ]);
        $inmate = Inmate::whereHas('admissions', fn ($query) => $query->whereNull('closed_at'))->firstOrFail();
        $person = Personnel::firstOrFail();
        $facility = Facility::firstOrFail();
        $transfer = Transfer::firstOrFail();
        $routes = [
            ['profile.show', null], ['inmates.index', null],
            ['inmates.create', null], ['inmates.edit', $inmate->id], ['inmates.discharge', $inmate->id],
            ['transfers.create', null], ['transfers.review', $transfer->id], ['transfers.dispatch', $transfer->id], ['transfers.receive', $transfer->id],
            ['personnel.create', null], ['personnel.edit', $person->id], ['personnel.assignment.create', null],
            ['facilities.create', null], ['facilities.edit', $facility->id],
        ];
        $this->actingAs($admin);
        foreach ($routes as [$name, $id]) {
            $response = $this->get(route($name, $id === null ? [] : [$id]))->assertOk()->assertSee('window.pmsSubmit', false);
            preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/si', $response->getContent(), $scripts, PREG_SET_ORDER);
            $code = '';
            preg_match_all('/\bx-data="([^"]*)"/s', $response->getContent(), $expressions);
            foreach ($expressions[1] as $expression) {
                $code .= "\n(() => (".html_entity_decode($expression, ENT_QUOTES | ENT_HTML5, 'UTF-8')."));\n";
            }
            foreach ($scripts as $script) {
                if (! str_contains($script[1], 'application/json')) {
                    $code .= $script[2]."\n";
                }
            }
            $process = new Process(['node', '--check'], base_path(), input: $code, timeout: 20);
            $process->run();
            $this->assertTrue($process->isSuccessful(), $name.': '.$process->getErrorOutput());
        }
    }
}
