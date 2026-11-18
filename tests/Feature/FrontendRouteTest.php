<?php

namespace Tests\Feature;

use App\Support\Demo\VisitDemoData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FrontendRouteTest extends TestCase
{
    public function test_every_application_get_route_renders_or_redirects_to_a_preview(): void
    {
        $checkedIn = collect(VisitDemoData::all())->firstWhere('status', 'Checked In');

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true) || str_starts_with($route->uri(), 'storage/') || $route->uri() === 'up' || str_starts_with($route->uri(), '_')) {
                continue;
            }
            $id = $route->getName() === 'visitors.visits.check-out' ? $checkedIn['id'] : 1;
            $uri = str_replace(['{id}', '{report}'], [(string) $id, 'population'], $route->uri());

            $this->followingRedirects()->get('/'.ltrim($uri, '/'))->assertOk();
        }
    }

    public function test_non_numeric_inmate_id_and_unlisted_report_are_not_found(): void
    {
        $this->get('/inmates/not-an-id')->assertNotFound();
        $this->get('/reports/not-a-report/print')->assertNotFound();
    }
}
