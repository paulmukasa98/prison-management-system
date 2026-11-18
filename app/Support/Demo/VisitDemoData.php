<?php

namespace App\Support\Demo;

/**
 * VisitDemoData - Phase 6 fictional visit occurrences.
 *
 * IMPORTANT: All records are completely invented for UI demonstration.
 * Each row is ONE visit by one visitor to one inmate at one facility.
 * Times/dates are fictional. Status transitions shown here are design
 * assumptions, NOT verified UPS visitation rules.
 *
 * Visit refs use the DEMO-VIS- prefix. Facility names always match the
 * inmate's current facility (canonical FacilityDemoData names).
 */
class VisitDemoData
{
    /** Provisional lifecycle - pending operational validation. */
    public const STATUSES = [
        'Requested',
        'Pending Review',
        'Scheduled',
        'Denied',
        'Cancelled',
        'Checked In',
        'Completed',
        'No-Show',
    ];

    public const PURPOSES = [
        'Family Visit',
        'Legal Consultation',
        'Pastoral Visit',
        'Consular Visit',
        'Welfare Visit',
        'Employer Visit',
        'Friend Visit',
    ];

    // -------------------------------------------------------------------------
    // The 45-record fictional dataset
    // -------------------------------------------------------------------------

    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'visit_ref' => 'DEMO-VIS-0001',
                'visitor_id' => 1,
                'inmate_id' => 1,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-09-12',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-30',
                'scheduled_date' => '2026-09-12',
                'check_in_time' => '09:52',
                'check_out_time' => '10:34',
                'notes' => 'Spouse visit recorded without incidents.',
            ],
            [
                'id' => 2,
                'visit_ref' => 'DEMO-VIS-0002',
                'visitor_id' => 2,
                'inmate_id' => 1,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-10-03',
                'start_time' => '11:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-20',
                'scheduled_date' => '2026-10-03',
                'check_in_time' => '11:05',
                'check_out_time' => '11:41',
                'notes' => null,
            ],
            [
                'id' => 3,
                'visit_ref' => 'DEMO-VIS-0003',
                'visitor_id' => 3,
                'inmate_id' => 7,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-10-21',
                'start_time' => '09:30',
                'duration_mins' => 45,
                'purpose' => 'Legal Consultation',
                'status' => 'Completed',
                'request_date' => '2026-10-10',
                'scheduled_date' => '2026-10-21',
                'check_in_time' => '09:31',
                'check_out_time' => '10:18',
                'notes' => 'Legal representative - case discussion.',
            ],
            [
                'id' => 4,
                'visit_ref' => 'DEMO-VIS-0004',
                'visitor_id' => 1,
                'inmate_id' => 1,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-11-07',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-10-24',
                'scheduled_date' => '2026-11-07',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 5,
                'visit_ref' => 'DEMO-VIS-0005',
                'visitor_id' => 4,
                'inmate_id' => 19,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-11-07',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-10-28',
                'scheduled_date' => '2026-11-07',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Same slot as visit DEMO-VIS-0004 - potential capacity conflict for preview display.',
            ],
            [
                'id' => 6,
                'visit_ref' => 'DEMO-VIS-0006',
                'visitor_id' => 18,
                'inmate_id' => 27,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-11-05',
                'start_time' => '14:00',
                'duration_mins' => 45,
                'purpose' => 'Legal Consultation',
                'status' => 'Checked In',
                'request_date' => '2026-10-30',
                'scheduled_date' => '2026-11-05',
                'check_in_time' => '13:58',
                'check_out_time' => null,
                'notes' => 'Visitor checked in; awaiting check-out.',
            ],
            [
                'id' => 7,
                'visit_ref' => 'DEMO-VIS-0007',
                'visitor_id' => 9,
                'inmate_id' => 7,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-11-04',
                'start_time' => '11:00',
                'duration_mins' => 45,
                'purpose' => 'Legal Consultation',
                'status' => 'No-Show',
                'request_date' => '2026-10-27',
                'scheduled_date' => '2026-11-04',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Visitor did not appear at the scheduled window.',
            ],
            [
                'id' => 8,
                'visit_ref' => 'DEMO-VIS-0008',
                'visitor_id' => 14,
                'inmate_id' => 19,
                'facility' => 'Luzira Upper Prison',
                'visit_date' => '2026-08-15',
                'start_time' => '10:30',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-01',
                'scheduled_date' => '2026-08-15',
                'check_in_time' => '10:28',
                'check_out_time' => '11:02',
                'notes' => null,
            ],
            [
                'id' => 9,
                'visit_ref' => 'DEMO-VIS-0009',
                'visitor_id' => 10,
                'inmate_id' => 2,
                'facility' => 'Murchison Bay Prison',
                'visit_date' => '2026-09-05',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-22',
                'scheduled_date' => '2026-09-05',
                'check_in_time' => '10:12',
                'check_out_time' => '10:47',
                'notes' => null,
            ],
            [
                'id' => 10,
                'visit_ref' => 'DEMO-VIS-0010',
                'visitor_id' => 16,
                'inmate_id' => 8,
                'facility' => 'Murchison Bay Prison',
                'visit_date' => '2026-10-10',
                'start_time' => '11:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-26',
                'scheduled_date' => '2026-10-10',
                'check_in_time' => '11:03',
                'check_out_time' => '11:38',
                'notes' => null,
            ],
            [
                'id' => 11,
                'visit_ref' => 'DEMO-VIS-0011',
                'visitor_id' => 10,
                'inmate_id' => 2,
                'facility' => 'Murchison Bay Prison',
                'visit_date' => '2026-11-06',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-10-20',
                'scheduled_date' => '2026-11-06',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 12,
                'visit_ref' => 'DEMO-VIS-0012',
                'visitor_id' => 12,
                'inmate_id' => 15,
                'facility' => 'Murchison Bay Prison',
                'visit_date' => '2026-11-06',
                'start_time' => '14:00',
                'duration_mins' => 45,
                'purpose' => 'Consular Visit',
                'status' => 'Pending Review',
                'request_date' => '2026-11-01',
                'scheduled_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Foreign-national consular request under demo review.',
            ],
            [
                'id' => 13,
                'visit_ref' => 'DEMO-VIS-0013',
                'visitor_id' => 17,
                'inmate_id' => 8,
                'facility' => 'Murchison Bay Prison',
                'visit_date' => '2026-10-18',
                'start_time' => '09:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Denied',
                'request_date' => '2026-10-05',
                'scheduled_date' => '2026-10-15',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Demo reason: relationship could not be verified from submitted documents.',
            ],
            [
                'id' => 14,
                'visit_ref' => 'DEMO-VIS-0014',
                'visitor_id' => 22,
                'inmate_id' => 4,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-08-28',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-14',
                'scheduled_date' => '2026-08-28',
                'check_in_time' => '09:58',
                'check_out_time' => '10:36',
                'notes' => null,
            ],
            [
                'id' => 15,
                'visit_ref' => 'DEMO-VIS-0015',
                'visitor_id' => 5,
                'inmate_id' => 4,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-09-11',
                'start_time' => '14:00',
                'duration_mins' => 20,
                'purpose' => 'Pastoral Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-29',
                'scheduled_date' => '2026-09-11',
                'check_in_time' => '14:04',
                'check_out_time' => '14:26',
                'notes' => null,
            ],
            [
                'id' => 16,
                'visit_ref' => 'DEMO-VIS-0016',
                'visitor_id' => 6,
                'inmate_id' => 11,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-10-02',
                'start_time' => '10:30',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-18',
                'scheduled_date' => '2026-10-02',
                'check_in_time' => '10:31',
                'check_out_time' => '11:05',
                'notes' => null,
            ],
            [
                'id' => 17,
                'visit_ref' => 'DEMO-VIS-0017',
                'visitor_id' => 1,
                'inmate_id' => 14,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-11-07',
                'start_time' => '11:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Requested',
                'request_date' => '2026-11-04',
                'scheduled_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Requested via front desk; pending scheduling.',
            ],
            [
                'id' => 18,
                'visit_ref' => 'DEMO-VIS-0018',
                'visitor_id' => 13,
                'inmate_id' => 14,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-10-24',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-10-12',
                'scheduled_date' => '2026-10-24',
                'check_in_time' => '10:07',
                'check_out_time' => '10:40',
                'notes' => null,
            ],
            [
                'id' => 19,
                'visit_ref' => 'DEMO-VIS-0019',
                'visitor_id' => 23,
                'inmate_id' => 9,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-11-08',
                'start_time' => '09:30',
                'duration_mins' => 45,
                'purpose' => 'Welfare Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-10-31',
                'scheduled_date' => '2026-11-08',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 20,
                'visit_ref' => 'DEMO-VIS-0020',
                'visitor_id' => 6,
                'inmate_id' => 11,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-11-08',
                'start_time' => '09:30',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-11-01',
                'scheduled_date' => '2026-11-08',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Same slot as visit DEMO-VIS-0019 - shown as a scheduling-conflict example.',
            ],
            [
                'id' => 21,
                'visit_ref' => 'DEMO-VIS-0021',
                'visitor_id' => 24,
                'inmate_id' => 26,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-11-03',
                'start_time' => '11:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Cancelled',
                'request_date' => '2026-10-22',
                'scheduled_date' => '2026-10-30',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Demo reason: visitor travelled from outside the region.',
            ],
            [
                'id' => 22,
                'visit_ref' => 'DEMO-VIS-0022',
                'visitor_id' => 18,
                'inmate_id' => 22,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-09-30',
                'start_time' => '14:00',
                'duration_mins' => 45,
                'purpose' => 'Legal Consultation',
                'status' => 'Completed',
                'request_date' => '2026-09-16',
                'scheduled_date' => '2026-09-30',
                'check_in_time' => '14:02',
                'check_out_time' => '14:50',
                'notes' => null,
            ],
            [
                'id' => 23,
                'visit_ref' => 'DEMO-VIS-0023',
                'visitor_id' => 15,
                'inmate_id' => 3,
                'facility' => 'Gulu Prison',
                'visit_date' => '2026-08-21',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-07',
                'scheduled_date' => '2026-08-21',
                'check_in_time' => '10:09',
                'check_out_time' => '10:44',
                'notes' => null,
            ],
            [
                'id' => 24,
                'visit_ref' => 'DEMO-VIS-0024',
                'visitor_id' => 19,
                'inmate_id' => 10,
                'facility' => 'Gulu Prison',
                'visit_date' => '2026-09-25',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-10',
                'scheduled_date' => '2026-09-25',
                'check_in_time' => '10:01',
                'check_out_time' => '10:33',
                'notes' => null,
            ],
            [
                'id' => 25,
                'visit_ref' => 'DEMO-VIS-0025',
                'visitor_id' => 15,
                'inmate_id' => 3,
                'facility' => 'Gulu Prison',
                'visit_date' => '2026-10-30',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-10-16',
                'scheduled_date' => '2026-10-30',
                'check_in_time' => '09:57',
                'check_out_time' => '10:39',
                'notes' => null,
            ],
            [
                'id' => 26,
                'visit_ref' => 'DEMO-VIS-0026',
                'visitor_id' => 20,
                'inmate_id' => 16,
                'facility' => 'Gulu Prison',
                'visit_date' => '2026-11-09',
                'start_time' => '11:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-10-26',
                'scheduled_date' => '2026-11-09',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 27,
                'visit_ref' => 'DEMO-VIS-0027',
                'visitor_id' => 19,
                'inmate_id' => 25,
                'facility' => 'Gulu Prison',
                'visit_date' => '2026-11-09',
                'start_time' => '11:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Requested',
                'request_date' => '2026-11-03',
                'scheduled_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 28,
                'visit_ref' => 'DEMO-VIS-0028',
                'visitor_id' => 10,
                'inmate_id' => 20,
                'facility' => 'Gulu Prison',
                'visit_date' => '2026-10-14',
                'start_time' => '09:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-30',
                'scheduled_date' => '2026-10-14',
                'check_in_time' => '09:11',
                'check_out_time' => '09:47',
                'notes' => null,
            ],
            [
                'id' => 29,
                'visit_ref' => 'DEMO-VIS-0029',
                'visitor_id' => 14,
                'inmate_id' => 5,
                'facility' => 'Fort Portal Prison',
                'visit_date' => '2026-09-08',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-25',
                'scheduled_date' => '2026-09-08',
                'check_in_time' => '10:03',
                'check_out_time' => '10:41',
                'notes' => null,
            ],
            [
                'id' => 30,
                'visit_ref' => 'DEMO-VIS-0030',
                'visitor_id' => 9,
                'inmate_id' => 17,
                'facility' => 'Fort Portal Prison',
                'visit_date' => '2026-10-17',
                'start_time' => '14:00',
                'duration_mins' => 45,
                'purpose' => 'Legal Consultation',
                'status' => 'Completed',
                'request_date' => '2026-10-06',
                'scheduled_date' => '2026-10-17',
                'check_in_time' => '14:06',
                'check_out_time' => '14:52',
                'notes' => null,
            ],
            [
                'id' => 31,
                'visit_ref' => 'DEMO-VIS-0031',
                'visitor_id' => 14,
                'inmate_id' => 28,
                'facility' => 'Fort Portal Prison',
                'visit_date' => '2026-11-10',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Checked In',
                'request_date' => '2026-10-29',
                'scheduled_date' => '2026-11-10',
                'check_in_time' => '09:59',
                'check_out_time' => null,
                'notes' => 'Morning session - check-out pending.',
            ],
            [
                'id' => 32,
                'visit_ref' => 'DEMO-VIS-0032',
                'visitor_id' => 21,
                'inmate_id' => 5,
                'facility' => 'Fort Portal Prison',
                'visit_date' => '2026-11-11',
                'start_time' => '14:00',
                'duration_mins' => 20,
                'purpose' => 'Pastoral Visit',
                'status' => 'Requested',
                'request_date' => '2026-11-06',
                'scheduled_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 33,
                'visit_ref' => 'DEMO-VIS-0033',
                'visitor_id' => 20,
                'inmate_id' => 13,
                'facility' => 'Soroti Prison',
                'visit_date' => '2026-08-19',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-08-05',
                'scheduled_date' => '2026-08-19',
                'check_in_time' => '10:05',
                'check_out_time' => '10:42',
                'notes' => null,
            ],
            [
                'id' => 34,
                'visit_ref' => 'DEMO-VIS-0034',
                'visitor_id' => 24,
                'inmate_id' => 21,
                'facility' => 'Soroti Prison',
                'visit_date' => '2026-09-23',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-09',
                'scheduled_date' => '2026-09-23',
                'check_in_time' => '10:00',
                'check_out_time' => '10:37',
                'notes' => null,
            ],
            [
                'id' => 35,
                'visit_ref' => 'DEMO-VIS-0035',
                'visitor_id' => 20,
                'inmate_id' => 13,
                'facility' => 'Soroti Prison',
                'visit_date' => '2026-11-02',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-10-19',
                'scheduled_date' => '2026-11-02',
                'check_in_time' => '10:08',
                'check_out_time' => '10:45',
                'notes' => null,
            ],
            [
                'id' => 36,
                'visit_ref' => 'DEMO-VIS-0036',
                'visitor_id' => 25,
                'inmate_id' => 21,
                'facility' => 'Soroti Prison',
                'visit_date' => '2026-11-12',
                'start_time' => '11:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-10-30',
                'scheduled_date' => '2026-11-12',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 37,
                'visit_ref' => 'DEMO-VIS-0037',
                'visitor_id' => 2,
                'inmate_id' => 23,
                'facility' => 'Kigo Prison',
                'visit_date' => '2026-09-17',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-02',
                'scheduled_date' => '2026-09-17',
                'check_in_time' => '10:02',
                'check_out_time' => '10:35',
                'notes' => null,
            ],
            [
                'id' => 38,
                'visit_ref' => 'DEMO-VIS-0038',
                'visitor_id' => 23,
                'inmate_id' => 23,
                'facility' => 'Kigo Prison',
                'visit_date' => '2026-10-26',
                'start_time' => '14:00',
                'duration_mins' => 45,
                'purpose' => 'Welfare Visit',
                'status' => 'Completed',
                'request_date' => '2026-10-13',
                'scheduled_date' => '2026-10-26',
                'check_in_time' => '14:03',
                'check_out_time' => '14:48',
                'notes' => null,
            ],
            [
                'id' => 39,
                'visit_ref' => 'DEMO-VIS-0039',
                'visitor_id' => 2,
                'inmate_id' => 23,
                'facility' => 'Kigo Prison',
                'visit_date' => '2026-11-13',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Requested',
                'request_date' => '2026-11-05',
                'scheduled_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 40,
                'visit_ref' => 'DEMO-VIS-0040',
                'visitor_id' => 4,
                'inmate_id' => 29,
                'facility' => 'Jinja Prison',
                'visit_date' => '2026-08-12',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-07-29',
                'scheduled_date' => '2026-08-12',
                'check_in_time' => '10:10',
                'check_out_time' => '10:46',
                'notes' => null,
            ],
            [
                'id' => 41,
                'visit_ref' => 'DEMO-VIS-0041',
                'visitor_id' => 13,
                'inmate_id' => 29,
                'facility' => 'Jinja Prison',
                'visit_date' => '2026-09-29',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Completed',
                'request_date' => '2026-09-15',
                'scheduled_date' => '2026-09-29',
                'check_in_time' => '10:00',
                'check_out_time' => '10:38',
                'notes' => null,
            ],
            [
                'id' => 42,
                'visit_ref' => 'DEMO-VIS-0042',
                'visitor_id' => 7,
                'inmate_id' => 29,
                'facility' => 'Jinja Prison',
                'visit_date' => '2026-10-20',
                'start_time' => '11:00',
                'duration_mins' => 20,
                'purpose' => 'Employer Visit',
                'status' => 'Denied',
                'request_date' => '2026-10-08',
                'scheduled_date' => '2026-10-16',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Demo reason: visit purpose not supported by inmate contact list.',
            ],
            [
                'id' => 43,
                'visit_ref' => 'DEMO-VIS-0043',
                'visitor_id' => 4,
                'inmate_id' => 29,
                'facility' => 'Jinja Prison',
                'visit_date' => '2026-11-14',
                'start_time' => '10:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'Scheduled',
                'request_date' => '2026-11-02',
                'scheduled_date' => '2026-11-14',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
            [
                'id' => 44,
                'visit_ref' => 'DEMO-VIS-0044',
                'visitor_id' => 16,
                'inmate_id' => 29,
                'facility' => 'Jinja Prison',
                'visit_date' => '2026-11-01',
                'start_time' => '09:00',
                'duration_mins' => 30,
                'purpose' => 'Family Visit',
                'status' => 'No-Show',
                'request_date' => '2026-10-20',
                'scheduled_date' => '2026-11-01',
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => 'Visitor did not arrive before the window closed.',
            ],
            [
                'id' => 45,
                'visit_ref' => 'DEMO-VIS-0045',
                'visitor_id' => 11,
                'inmate_id' => 4,
                'facility' => 'Kampala Prison',
                'visit_date' => '2026-11-15',
                'start_time' => '14:00',
                'duration_mins' => 30,
                'purpose' => 'Friend Visit',
                'status' => 'Requested',
                'request_date' => '2026-11-08',
                'scheduled_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
                'notes' => null,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public static function find(int $id): ?array
    {
        foreach (static::all() as $visit) {
            if ($visit['id'] === $id) {
                return $visit;
            }
        }

        return null;
    }

    /** Visits for one visitor, newest first. */
    public static function forVisitor(int $visitorId): array
    {
        $visits = array_values(array_filter(
            static::all(),
            fn ($v) => $v['visitor_id'] === $visitorId
        ));
        usort($visits, fn ($a, $b) => strcmp($b['visit_date'], $a['visit_date']));

        return $visits;
    }

    /** Canonical visits for one inmate, newest first. */
    public static function forInmate(int $inmateId): array
    {
        $visits = array_values(array_filter(
            static::all(),
            fn ($v) => $v['inmate_id'] === $inmateId
        ));
        usort($visits, fn ($a, $b) => strcmp($b['visit_date'], $a['visit_date']));

        return $visits;
    }

    /** Completed visits within a reporting period [from, to] inclusive. */
    public static function completedBetween(string $from, string $to): array
    {
        return array_values(array_filter(
            static::all(),
            fn ($v) => $v['status'] === 'Completed' && $v['visit_date'] >= $from && $v['visit_date'] <= $to
        ));
    }

    /**
     * Other scheduled/open visits sharing the same facility, date and start
     * time - surfaced as potential conflicts in previews only.
     */
    public static function conflictsFor(array $visit): array
    {
        return array_values(array_filter(
            static::all(),
            fn ($v) => $v['id'] !== $visit['id']
                && $v['facility'] === $visit['facility']
                && $v['visit_date'] === $visit['visit_date']
                && $v['start_time'] === $visit['start_time']
                && in_array($v['status'], ['Requested', 'Pending Review', 'Scheduled', 'Checked In'])
        ));
    }

    /** Derive computed display fields (names resolved from canonical fixtures). */
    public static function enrich(array $visit): array
    {
        $visitor = VisitorDemoData::find($visit['visitor_id']);
        $inmate = InmateDemoData::find($visit['inmate_id']);

        $visit['visitor_name'] = $visitor ? trim($visitor['first_name'].' '.$visitor['last_name']) : 'Unknown visitor';
        $visit['visitor_ref'] = $visitor['visitor_ref'] ?? '—';
        $visit['inmate_name'] = $inmate ? trim($inmate['first_name'].' '.$inmate['last_name']) : 'Unknown inmate';
        $visit['prisoner_number'] = $inmate['prisoner_number'] ?? '—';
        $visit['conflicts'] = static::conflictsFor($visit);

        return $visit;
    }

    /** Summary counts for the records page. */
    public static function summaryCounts(): array
    {
        $all = static::all();

        return [
            'total' => count($all),
            'completed' => count(array_filter($all, fn ($v) => $v['status'] === 'Completed')),
            'upcoming' => count(array_filter($all, fn ($v) => $v['status'] === 'Scheduled')),
            'open_requests' => count(array_filter($all, fn ($v) => in_array($v['status'], ['Requested', 'Pending Review']))),
            'checked_in' => count(array_filter($all, fn ($v) => $v['status'] === 'Checked In')),
        ];
    }

    /**
     * Build a lifecycle timeline for one visit. Only events that actually
     * occurred (non-null dates/times) are included.
     */
    public static function timeline(array $visit): array
    {
        $events = [];

        if ($visit['request_date']) {
            $events[] = ['event' => 'Visit Requested', 'detail' => 'Request logged for '.$visit['purpose'].'.', 'timestamp' => $visit['request_date'].' 09:00:00', 'type' => 'system'];
        }
        if ($visit['scheduled_date']) {
            $events[] = ['event' => 'Scheduled', 'detail' => 'Slot confirmed for '.$visit['visit_date'].' '.$visit['start_time'].'.', 'timestamp' => $visit['scheduled_date'].' 10:00:00', 'type' => 'system'];
        }
        if ($visit['check_in_time']) {
            $events[] = ['event' => 'Checked In', 'detail' => 'Visitor checked in at reception.', 'timestamp' => $visit['visit_date'].' '.$visit['check_in_time'].':00', 'type' => 'visit'];
        }
        if ($visit['check_out_time']) {
            $events[] = ['event' => 'Checked Out', 'detail' => 'Visit ended after '.$visit['duration_mins'].' minutes.', 'timestamp' => $visit['visit_date'].' '.$visit['check_out_time'].':00', 'type' => 'visit'];
        }
        if (in_array($visit['status'], ['Denied', 'Cancelled', 'No-Show'])) {
            $events[] = ['event' => $visit['status'], 'detail' => $visit['notes'] ?? 'Closed without a visit.', 'timestamp' => ($visit['scheduled_date'] ?? $visit['visit_date']).' 16:00:00', 'type' => $visit['status'] === 'Denied' ? 'discharge' : 'edit'];
        }

        usort($events, fn ($a, $b) => strcmp($a['timestamp'], $b['timestamp']));

        return $events;
    }
}
