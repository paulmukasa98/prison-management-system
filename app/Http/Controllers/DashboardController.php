<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, ReportingService $reporting): View
    {
        $data = $reporting->dashboard($request->user());
        $activities = AuditEvent::visibleTo($request->user())->latest('occurred_at')->latest('id')->limit(8)->get()->map(fn (AuditEvent $event): array => [
            'type' => 'personnel', 'color' => 'blue', 'text' => str_replace('_', ' ', $event->action),
            'detail' => $event->resource_type.' · '.$event->resource_reference,
            'time' => $event->occurred_at->diffForHumans(), 'status' => $event->outcome === 'Success' ? 'active' : 'warning',
            'outcome' => $event->outcome,
        ])->all();

        return view('dashboard.index', [
            'periodsJson' => json_encode($data['periods'], JSON_THROW_ON_ERROR),
            'facilitiesJson' => json_encode($data['facilities'], JSON_THROW_ON_ERROR),
            'demoContext' => ['recent_activities' => $activities, 'period_options' => [
                'this_month' => 'This Month', 'last_month' => 'Last Month',
                'last_3_months' => 'Last 3 Months', 'this_year' => 'This Year',
            ]],
        ]);
    }
}
