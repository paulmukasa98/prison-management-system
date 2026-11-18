<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\ReportFilterRequest;
use App\Models\AuditEvent;
use App\Services\AuditService;
use App\Services\CsvExportService;
use App\Services\ReportingService;
use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportingService $reporting, protected AuditService $audit) {}

    /** @return array<string, mixed> */
    private function context(ReportFilterRequest $request): array
    {
        $filters = $request->validated();
        $facilities = $this->reporting->facilities($request->user());
        $facilityId = empty($filters['facility_id']) ? null : (int) $filters['facility_id'];
        abort_if($facilityId !== null && ! $facilities->contains('id', $facilityId), 404);

        return [
            'from' => $filters['from'], 'to' => $filters['to'], 'facilityId' => $facilityId,
            'facilities' => $facilities, 'timezone' => config('reporting.timezone'),
        ];
    }

    /** @return array<string, mixed> */
    private function data(ReportFilterRequest $request, string $report, string $permission = 'reports.view'): array
    {
        $context = $this->context($request);
        if ($permission === 'reports.export') {
            abort_unless($request->user()->hasPermission('reports.export', $context['facilityId']), 403);
        }
        $data = $this->reporting->report(
            $report, $request->user(),
            CarbonImmutable::parse($context['from'], $context['timezone'])->startOfDay()->utc(),
            CarbonImmutable::parse($context['to'], $context['timezone'])->endOfDay()->utc(),
            $context['facilityId'], $permission
        );

        return ['reportData' => $data] + $context;
    }

    public function index(ReportFilterRequest $request): View
    {
        return view('reports.index', ['reportTitles' => ReportingService::TITLES] + $this->context($request));
    }

    public function population(ReportFilterRequest $request): View
    {
        return $this->show($request, 'population');
    }

    public function movement(ReportFilterRequest $request): View
    {
        return $this->show($request, 'movement');
    }

    public function transfers(ReportFilterRequest $request): View
    {
        return $this->show($request, 'transfers');
    }

    public function visitation(ReportFilterRequest $request): View
    {
        return $this->show($request, 'visitation');
    }

    public function personnel(ReportFilterRequest $request): View
    {
        return $this->show($request, 'personnel');
    }

    public function occupancy(ReportFilterRequest $request): View
    {
        return $this->show($request, 'occupancy');
    }

    private function show(ReportFilterRequest $request, string $report): View
    {
        $data = $this->data($request, $report);
        $this->recordAccess($request, $report, 'report_viewed', $data['facilityId']);

        return view('reports.'.$report, $data);
    }

    public function print(ReportFilterRequest $request, string $report): View
    {
        $data = $this->data($request, $report, 'reports.export');
        $event = $this->recordAccess($request, $report, 'report_printed', $data['facilityId']);

        return view('reports.print', ['auditReference' => $event->audit_ref] + $data);
    }

    public function export(ReportFilterRequest $request, string $report, CsvExportService $csv): StreamedResponse
    {
        abort_unless(config('reporting.csv_exports_enabled'), 409, 'CSV exports have not been enabled by the system administrator.');
        $data = $this->data($request, $report, 'reports.export');
        $event = $this->recordAccess($request, $report, 'report_exported', $data['facilityId']);
        $reportData = $data['reportData'];

        $rows = (function () use ($reportData, $event, $data): \Generator {
            yield [config('reporting.classification')];
            yield [$reportData['title'], $data['from'], $data['to'], $data['timezone'], $event->audit_ref];
            yield $reportData['columns'];
            foreach ($reportData['rows'] as $row) {
                yield $row;
            }
        })();

        return $csv->download($report.'-'.now()->format('Ymd-His').'.csv', $rows);
    }

    private function recordAccess(ReportFilterRequest $request, string $report, string $action, ?int $facilityId): AuditEvent
    {
        return $this->audit->log(
            action: $action, outcome: 'Success', resourceType: 'Report', resourceReference: $report,
            changes: ['from' => $request->validated('from'), 'to' => $request->validated('to')],
            facilityId: $facilityId, actorId: $request->user()->id
        );
    }
}
