<?php

namespace App\Support\Demo;

/**
 * AuditDemoData — Phase 7 fictional audit events.
 *
 * IMPORTANT: Read-only design preview. Real audit logging (append-only,
 * correlation IDs, retention) is backend phase B7 work. Events reference the
 * canonical DEMO fixtures so every actor, record and facility actually exists
 * in the demo datasets. Sensitive values are redacted; no credentials or
 * unmasked identity documents ever appear here.
 */
class AuditDemoData
{
    public const OUTCOMES = ['Success', 'Failed', 'Denied'];

    /**
     * @return list<array<string, mixed>> newest first
     */
    public static function all(): array
    {
        return [
            [
                'id' => 1, 'audit_ref' => 'DEMO-AUD-0001', 'occurred_at' => '2026-11-11 09:05:12',
                'actor_user_id' => 4, 'action' => 'visitor.check_in', 'outcome' => 'Success',
                'resource_type' => 'Visit', 'resource_id' => 31, 'resource_ref' => 'DEMO-VIS-0031',
                'facility' => 'Kampala Prison', 'ip' => '10.20.4.15', 'correlation' => 'REQ-20261111-0905-a17',
                'description' => 'Visitor checked in against scheduled visit.',
                'before' => null,
                'after' => ['status' => 'Checked In', 'check_in_time' => '09:59 (planned 10:00)'],
            ],
            [
                'id' => 2, 'audit_ref' => 'DEMO-AUD-0002', 'occurred_at' => '2026-11-11 08:47:03',
                'actor_user_id' => 1, 'action' => 'transfer.authorize', 'outcome' => 'Denied',
                'resource_type' => 'Transfer', 'resource_id' => 10, 'resource_ref' => 'DEMO-TRF-0010',
                'facility' => 'Luzira Upper Prison', 'ip' => '10.20.1.4', 'correlation' => 'REQ-20261111-0847-b32',
                'description' => 'Authorization attempt blocked — request awaiting clarification.',
                'before' => ['status' => 'Awaiting Clarification'],
                'after' => ['status' => 'Awaiting Clarification'],
            ],
            [
                'id' => 3, 'audit_ref' => 'DEMO-AUD-0003', 'occurred_at' => '2026-11-11 08:42:55',
                'actor_user_id' => 1, 'action' => 'auth.login', 'outcome' => 'Success',
                'resource_type' => 'Session', 'resource_id' => null, 'resource_ref' => null,
                'facility' => 'National', 'ip' => '10.20.1.4', 'correlation' => 'REQ-20261111-0842-c01',
                'description' => 'Sign-in from service network.',
                'before' => null, 'after' => ['session' => 'redacted'],
            ],
            [
                'id' => 4, 'audit_ref' => 'DEMO-AUD-0004', 'occurred_at' => '2026-11-11 07:58:31',
                'actor_user_id' => 3, 'action' => 'inmate.record_view', 'outcome' => 'Success',
                'resource_type' => 'Inmate', 'resource_id' => 1, 'resource_ref' => 'DEMO-PN-0001',
                'facility' => 'Soroti Prison', 'ip' => '10.20.6.22', 'correlation' => 'REQ-20261111-0758-d44',
                'description' => 'Sensitive read logged for accountability review.',
                'before' => null, 'after' => null,
            ],
            [
                'id' => 5, 'audit_ref' => 'DEMO-AUD-0005', 'occurred_at' => '2026-11-11 07:31:19',
                'actor_user_id' => 2, 'action' => 'inmate.admission_create', 'outcome' => 'Success',
                'resource_type' => 'Inmate', 'resource_id' => 25, 'resource_ref' => 'DEMO-PN-0025',
                'facility' => 'Luzira Upper Prison', 'ip' => '10.20.1.11', 'correlation' => 'REQ-20261111-0731-e19',
                'description' => 'New custody episode opened from court warrant.',
                'before' => null,
                'after' => ['status' => 'In Custody', 'admission_ref' => 'see record', 'identity' => 'redacted'],
            ],
            [
                'id' => 6, 'audit_ref' => 'DEMO-AUD-0006', 'occurred_at' => '2026-11-10 18:22:40',
                'actor_user_id' => 9, 'action' => 'auth.login', 'outcome' => 'Failed',
                'resource_type' => 'Session', 'resource_id' => null, 'resource_ref' => null,
                'facility' => 'Murchison Bay Prison', 'ip' => '10.20.2.37', 'correlation' => 'REQ-20261110-1822-f02',
                'description' => 'Credential mismatch. Rate limiter engaged after repeated attempts.',
                'before' => null, 'after' => null,
            ],
            [
                'id' => 7, 'audit_ref' => 'DEMO-AUD-0007', 'occurred_at' => '2026-11-10 16:44:02',
                'actor_user_id' => 5, 'action' => 'inmate.correction', 'outcome' => 'Success',
                'resource_type' => 'Inmate', 'resource_id' => 12, 'resource_ref' => 'DEMO-PN-0012',
                'facility' => 'Murchison Bay Prison', 'ip' => '10.20.2.18', 'correlation' => 'REQ-20261110-1644-a55',
                'description' => 'Authorized correction to kin contact detail, with reason recorded.',
                'before' => ['kin_contact' => '+256 ••• ••• 118'],
                'after' => ['kin_contact' => '+256 ••• ••• 204'],
            ],
            [
                'id' => 8, 'audit_ref' => 'DEMO-AUD-0008', 'occurred_at' => '2026-11-10 15:10:47',
                'actor_user_id' => 3, 'action' => 'report.export_request', 'outcome' => 'Denied',
                'resource_type' => 'Report', 'resource_id' => null, 'resource_ref' => 'population',
                'facility' => 'Soroti Prison', 'ip' => '10.20.6.22', 'correlation' => 'REQ-20261110-1510-b71',
                'description' => 'Export attempted without reports.export permission.',
                'before' => null, 'after' => null,
            ],
            [
                'id' => 9, 'audit_ref' => 'DEMO-AUD-0009', 'occurred_at' => '2026-11-10 14:02:33',
                'actor_user_id' => 6, 'action' => 'visit.cancel', 'outcome' => 'Success',
                'resource_type' => 'Visit', 'resource_id' => 21, 'resource_ref' => 'DEMO-VIS-0021',
                'facility' => 'Fort Portal Prison', 'ip' => '10.20.5.9', 'correlation' => 'REQ-20261110-1402-c28',
                'description' => 'Visit cancelled — visitor withdrew request by phone.',
                'before' => ['status' => 'Scheduled'],
                'after' => ['status' => 'Cancelled', 'reason' => 'Change of travel plans'],
            ],
            [
                'id' => 10, 'audit_ref' => 'DEMO-AUD-0010', 'occurred_at' => '2026-11-09 11:20:05',
                'actor_user_id' => 2, 'action' => 'user.account_create', 'outcome' => 'Success',
                'resource_type' => 'User', 'resource_id' => 8, 'resource_ref' => 'DEMO-USR-0008',
                'facility' => 'Gulu Prison', 'ip' => '10.20.1.11', 'correlation' => 'REQ-20261109-1120-d93',
                'description' => 'Account invitation issued to personnel DEMO-STF-0004. Personnel link ≠ automatic access.',
                'before' => null,
                'after' => ['status' => 'Invited', 'role' => 'Records Officer'],
            ],
            [
                'id' => 11, 'audit_ref' => 'DEMO-AUD-0011', 'occurred_at' => '2026-11-08 09:15:52',
                'actor_user_id' => 1, 'action' => 'transfer.dispatch', 'outcome' => 'Success',
                'resource_type' => 'Transfer', 'resource_id' => 4, 'resource_ref' => 'DEMO-TRF-0004',
                'facility' => 'Luzira Upper Prison', 'ip' => '10.20.1.4', 'correlation' => 'REQ-20261108-0915-e47',
                'description' => 'Prisoner dispatched Luzira Upper → Kampala under escort.',
                'before' => ['status' => 'Approved'],
                'after' => ['status' => 'Dispatched', 'escort' => 'redacted'],
            ],
            [
                'id' => 12, 'audit_ref' => 'DEMO-AUD-0012', 'occurred_at' => '2026-11-07 13:41:27',
                'actor_user_id' => 2, 'action' => 'inmate.discharge_authorize', 'outcome' => 'Success',
                'resource_type' => 'Inmate', 'resource_id' => 6, 'resource_ref' => 'DEMO-PN-0006',
                'facility' => 'Luzira Upper Prison', 'ip' => '10.20.1.11', 'correlation' => 'REQ-20261107-1341-f13',
                'description' => 'Discharge authorized on documented legal basis (sentence served).',
                'before' => ['status' => 'In Custody'],
                'after' => ['status' => 'Released', 'discharge_auth_ref' => 'see record'],
            ],
            [
                'id' => 13, 'audit_ref' => 'DEMO-AUD-0013', 'occurred_at' => '2026-11-06 10:03:44',
                'actor_user_id' => 7, 'action' => 'audit.view', 'outcome' => 'Success',
                'resource_type' => 'AuditLog', 'resource_id' => null, 'resource_ref' => null,
                'facility' => 'National', 'ip' => '10.30.0.8', 'correlation' => 'REQ-20261106-1003-a09',
                'description' => 'Compliance auditor reviewed October activity summary.',
                'before' => null, 'after' => null,
            ],
            [
                'id' => 14, 'audit_ref' => 'DEMO-AUD-0014', 'occurred_at' => '2026-11-05 16:55:10',
                'actor_user_id' => 4, 'action' => 'visitor.register', 'outcome' => 'Success',
                'resource_type' => 'Visitor', 'resource_id' => 3, 'resource_ref' => 'DEMO-VST-0003',
                'facility' => 'Kampala Prison', 'ip' => '10.20.4.15', 'correlation' => 'REQ-20261105-1655-b66',
                'description' => 'Visitor registered at reception desk.',
                'before' => null,
                'after' => ['status' => 'Registered', 'identity_document' => 'masked on file'],
            ],
            [
                'id' => 15, 'audit_ref' => 'DEMO-AUD-0015', 'occurred_at' => '2026-11-04 12:18:36',
                'actor_user_id' => 6, 'action' => 'facility.capacity_update', 'outcome' => 'Success',
                'resource_type' => 'Facility', 'resource_id' => 5, 'resource_ref' => 'DEMO-FAC-005',
                'facility' => 'Fort Portal Prison', 'ip' => '10.20.5.9', 'correlation' => 'REQ-20261104-1218-c31',
                'description' => 'Recorded capacity updated with effective date and source note.',
                'before' => ['capacity' => 'unknown'],
                'after' => ['capacity' => 'recorded (fictional value)', 'effective_date' => '2026-11-04'],
            ],
            [
                'id' => 16, 'audit_ref' => 'DEMO-AUD-0016', 'occurred_at' => '2026-11-03 08:30:21',
                'actor_user_id' => 9, 'action' => 'inmate.record_view', 'outcome' => 'Denied',
                'resource_type' => 'Inmate', 'resource_id' => 20, 'resource_ref' => 'DEMO-PN-0020',
                'facility' => 'Murchison Bay Prison', 'ip' => '10.20.2.37', 'correlation' => 'REQ-20261103-0830-d78',
                'description' => 'Cross-facility read blocked by scope rule (design assumption).',
                'before' => null, 'after' => null,
            ],
            [
                'id' => 17, 'audit_ref' => 'DEMO-AUD-0017', 'occurred_at' => '2026-10-28 15:12:09',
                'actor_user_id' => 1, 'action' => 'role.assignment_change', 'outcome' => 'Success',
                'resource_type' => 'User', 'resource_id' => 5, 'resource_ref' => 'DEMO-USR-0005',
                'facility' => 'National', 'ip' => '10.20.1.4', 'correlation' => 'REQ-20261028-1512-e54',
                'description' => 'Role confirmed after records-officer rotation.',
                'before' => ['role' => 'Duty Warden'],
                'after' => ['role' => 'Records Officer'],
            ],
            [
                'id' => 18, 'audit_ref' => 'DEMO-AUD-0018', 'occurred_at' => '2026-10-20 10:45:58',
                'actor_user_id' => null, 'action' => 'system.backup', 'outcome' => 'Success',
                'resource_type' => 'System', 'resource_id' => null, 'resource_ref' => null,
                'facility' => 'National', 'ip' => '—', 'correlation' => 'CRON-20261020-1045-f21',
                'description' => 'Nightly database backup completed (placeholder event).',
                'before' => null, 'after' => null,
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        foreach (static::all() as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }

        return null;
    }

    public static function enrich(array $event): array
    {
        $event['actor_name'] = UserDemoData::displayName($event['actor_user_id']);

        return $event;
    }

    /** @return list<array<string, mixed>> */
    public static function enriched(): array
    {
        return array_map([static::class, 'enrich'], static::all());
    }

    /**
     * Per-resource trail (newest first), e.g. everything done to DEMO-PN-0006.
     *
     * @return list<array<string, mixed>>
     */
    public static function forResource(string $type, ?string $ref = null): array
    {
        return array_values(array_filter(
            static::enriched(),
            fn ($e) => $e['resource_type'] === $type && ($ref === null || $e['resource_ref'] === $ref)
        ));
    }

    /** @return array{total:int, success:int, failed:int, denied:int} */
    public static function summaryCounts(): array
    {
        $counts = ['total' => count(static::all()), 'success' => 0, 'failed' => 0, 'denied' => 0];
        foreach (static::all() as $event) {
            $counts[strtolower($event['outcome'])]++;
        }

        return $counts;
    }

    /** Distinct actions present in the dataset, for filter dropdowns. */
    public static function actions(): array
    {
        return array_values(array_unique(array_column(static::all(), 'action')));
    }

    /** Human label for an action slug like inmate.record_view. */
    public static function actionLabel(string $action): string
    {
        [$domain, $verb] = array_pad(explode('.', $action, 2), 2, '');
        $label = str_replace('_', ' ', trim($verb).' '.str_replace('.', ' ', $domain));

        return ucwords($label);
    }

    /**
     * Timeline rows shaped for x-activity-timeline (requires `timestamp`).
     *
     * @return list<array<string, mixed>>
     */
    public static function timelineFor(string $type, ?string $ref = null): array
    {
        return array_map(fn ($e) => [
            'type' => match (true) {
                str_starts_with($e['action'], 'inmate') => 'admission',
                str_starts_with($e['action'], 'transfer') => 'transfer',
                str_starts_with($e['action'], 'visit') => 'visit',
                default => 'system',
            },
            'event' => static::actionLabel($e['action']).' — '.$e['outcome'],
            'detail' => $e['description'],
            'timestamp' => $e['occurred_at'],
            'actor' => $e['actor_name'],
        ], static::forResource($type, $ref));
    }
}
