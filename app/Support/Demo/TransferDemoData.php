<?php

namespace App\Support\Demo;

/**
 * TransferDemoData — Phase 4 fictional transfer records.
 *
 * IMPORTANT: All records are completely invented for UI demonstration.
 * No real prisoner data, real officer identities, actual escort details,
 * or operational transport routes are included.
 *
 * Transfer references use the DEMO-TRF- prefix to make their fictional
 * nature unambiguous. They are NOT official Uganda Prisons Service records.
 *
 * Inmate IDs correspond to InmateDemoData records (IDs 1–30).
 * Only inmates whose status is 'In Custody' or 'Remanded' are eligible
 * for transfer requests in this demonstration.
 *
 * Status vocabulary (provisional — not official UPS terminology):
 *   Draft | Pending Approval | Approved | Rejected | Awaiting Clarification
 *   Cancelled | Ready for Dispatch | In Transit | Arrival Confirmation Pending | Completed
 */
class TransferDemoData
{
    // -------------------------------------------------------------------------
    // Centralized status definitions
    // -------------------------------------------------------------------------

    public const STATUSES = [
        'Draft' => ['color' => 'gray',   'badge' => 'inactive'],
        'Pending Approval' => ['color' => 'amber',  'badge' => 'pending'],
        'Approved' => ['color' => 'blue',   'badge' => 'released'],
        'Rejected' => ['color' => 'red',    'badge' => 'danger'],
        'Awaiting Clarification' => ['color' => 'orange', 'badge' => 'warning'],
        'Cancelled' => ['color' => 'gray',   'badge' => 'inactive'],
        'Ready for Dispatch' => ['color' => 'indigo', 'badge' => 'active'],
        'In Transit' => ['color' => 'purple', 'badge' => 'pending'],
        'Arrival Confirmation Pending' => ['color' => 'amber',  'badge' => 'pending'],
        'Completed' => ['color' => 'green',  'badge' => 'success'],
    ];

    // -------------------------------------------------------------------------
    // Transfer reasons (configurable design examples — not verified UPS categories)
    // -------------------------------------------------------------------------

    public const REASONS = [
        'Administrative Reassignment',
        'Facility Capacity Management',
        'Court-Related Requirement',
        'Welfare or Medical Referral',
        'Security Reclassification',
        'Other Authorized Reason',
    ];

    // -------------------------------------------------------------------------
    // Priority levels
    // -------------------------------------------------------------------------

    public const PRIORITIES = ['Routine', 'Urgent', 'Emergency'];

    // -------------------------------------------------------------------------
    // The centralized fictional transfer dataset
    // -------------------------------------------------------------------------

    public static function all(): array
    {
        return [

            // ---- 1 — Completed ----
            [
                'id' => 1,
                'transfer_ref' => 'DEMO-TRF-0001',
                'inmate_id' => 15,
                'prisoner_number' => 'DEMO-PN-0015',
                'inmate_name' => 'Moses Byamugisha',
                'classification' => 'Convicted',
                'origin_facility' => 'Fort Portal Prison',
                'destination_facility' => 'Murchison Bay Prison',
                'requesting_facility' => 'Fort Portal Prison',
                'status' => 'Completed',
                'priority' => 'Routine',
                'transfer_reason' => 'Facility Capacity Management',
                'notes' => 'Routine reallocation due to population pressures at Fort Portal.',
                'requesting_officer' => 'Demo Officer — Fort Portal (demo role)',
                'request_date' => '2021-02-20',
                'proposed_transfer_date' => '2021-03-14',
                'supporting_doc_ref' => 'DOC-FTP-2021-0088',
                // Authorization
                'review_status' => 'Approved',
                'reviewing_role' => 'Prisons Commissioner (demo role)',
                'review_date' => '2021-03-01',
                'auth_ref' => 'AUTH-2021-0033',
                'review_notes' => 'Approved. Population levels confirmed.',
                'rejection_reason' => null,
                'clarification_request' => null,
                // Dispatch
                'dispatch_date' => '2021-03-14 09:00',
                'dispatching_facility' => 'Fort Portal Prison',
                'dispatching_officer' => 'Demo Dispatch Officer (demo role)',
                'handover_ref' => 'HO-TRF-2021-0088',
                'escort_ref' => 'ESC-2021-0022',
                'departure_notes' => 'Inmate departed in good health. Escorted as per standard procedure.',
                // Receiving
                'arrival_date' => '2021-03-14 14:30',
                'receiving_facility' => 'Murchison Bay Prison',
                'receiving_officer' => 'Demo Receiving Officer (demo role)',
                'arrival_ref' => 'ARR-2021-0044',
                'receipt_notes' => 'Inmate received in good health. All documentation verified.',
                // Dates
                'created_date' => '2021-02-20',
                'updated_date' => '2021-03-14',
                'closed_date' => '2021-03-14',
            ],

            // ---- 2 — Completed ----
            [
                'id' => 2,
                'transfer_ref' => 'DEMO-TRF-0002',
                'inmate_id' => 3,
                'prisoner_number' => 'DEMO-PN-0003',
                'inmate_name' => 'Peter James Okello',
                'classification' => 'Convicted',
                'origin_facility' => 'Soroti Prison',
                'destination_facility' => 'Gulu Prison',
                'requesting_facility' => 'Soroti Prison',
                'status' => 'Completed',
                'priority' => 'Routine',
                'transfer_reason' => 'Security Reclassification',
                'notes' => 'Security reclassification following review. Transfer to higher-security facility.',
                'requesting_officer' => 'Demo Officer — Soroti (demo role)',
                'request_date' => '2024-01-25',
                'proposed_transfer_date' => '2024-02-10',
                'supporting_doc_ref' => 'DOC-SRT-2024-0045',
                'review_status' => 'Approved',
                'reviewing_role' => 'Regional Prisons Officer (demo role)',
                'review_date' => '2024-02-05',
                'auth_ref' => 'AUTH-2024-0019',
                'review_notes' => 'Approved following security review.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => '2024-02-10 08:00',
                'dispatching_facility' => 'Soroti Prison',
                'dispatching_officer' => 'Demo Dispatch Officer (demo role)',
                'handover_ref' => 'HO-TRF-2024-0045',
                'escort_ref' => 'ESC-2024-0011',
                'departure_notes' => 'Departed on schedule.',
                'arrival_date' => '2024-02-10 15:00',
                'receiving_facility' => 'Gulu Prison',
                'receiving_officer' => 'Demo Receiving Officer (demo role)',
                'arrival_ref' => 'ARR-2024-0028',
                'receipt_notes' => 'Received in good order.',
                'created_date' => '2024-01-25',
                'updated_date' => '2024-02-10',
                'closed_date' => '2024-02-10',
            ],

            // ---- 3 — Completed ----
            [
                'id' => 3,
                'transfer_ref' => 'DEMO-TRF-0003',
                'inmate_id' => 7,
                'prisoner_number' => 'DEMO-PN-0007',
                'inmate_name' => 'Samuel Timothy Opio',
                'classification' => 'Convicted',
                'origin_facility' => 'Kampala Prison',
                'destination_facility' => 'Luzira Upper Prison',
                'requesting_facility' => 'Kampala Prison',
                'status' => 'Completed',
                'priority' => 'Routine',
                'transfer_reason' => 'Administrative Reassignment',
                'notes' => 'Long-term convicted inmate reassigned to maximum-security facility.',
                'requesting_officer' => 'Demo Officer — Kampala (demo role)',
                'request_date' => '2021-02-10',
                'proposed_transfer_date' => '2021-03-22',
                'supporting_doc_ref' => 'DOC-KPL-2021-0188',
                'review_status' => 'Approved',
                'reviewing_role' => 'Prisons Commissioner (demo role)',
                'review_date' => '2021-03-10',
                'auth_ref' => 'AUTH-2021-0021',
                'review_notes' => 'Approved. Inmate to serve sentence at Luzira.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => '2021-03-22 09:30',
                'dispatching_facility' => 'Kampala Prison',
                'dispatching_officer' => 'Demo Dispatch Officer (demo role)',
                'handover_ref' => 'HO-TRF-2021-0021',
                'escort_ref' => 'ESC-2021-0009',
                'departure_notes' => 'Departed in good health.',
                'arrival_date' => '2021-03-22 11:00',
                'receiving_facility' => 'Luzira Upper Prison',
                'receiving_officer' => 'Demo Receiving Officer (demo role)',
                'arrival_ref' => 'ARR-2021-0019',
                'receipt_notes' => 'Received and admitted.',
                'created_date' => '2021-02-10',
                'updated_date' => '2021-03-22',
                'closed_date' => '2021-03-22',
            ],

            // ---- 4 — Pending Approval ----
            [
                'id' => 4,
                'transfer_ref' => 'DEMO-TRF-0004',
                'inmate_id' => 1,
                'prisoner_number' => 'DEMO-PN-0001',
                'inmate_name' => 'John Robert Mukasa',
                'classification' => 'Convicted',
                'origin_facility' => 'Luzira Upper Prison',
                'destination_facility' => 'Kampala Prison',
                'requesting_facility' => 'Luzira Upper Prison',
                'status' => 'Pending Approval',
                'priority' => 'Routine',
                'transfer_reason' => 'Court-Related Requirement',
                'notes' => 'Transfer requested to facilitate attendance at ongoing court proceedings.',
                'requesting_officer' => 'Demo Officer — Luzira (demo role)',
                'request_date' => '2025-01-10',
                'proposed_transfer_date' => '2025-02-01',
                'supporting_doc_ref' => 'DOC-LUZ-2025-0004',
                'review_status' => null,
                'reviewing_role' => null,
                'review_date' => null,
                'auth_ref' => null,
                'review_notes' => null,
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-01-10',
                'updated_date' => '2025-01-10',
                'closed_date' => null,
            ],

            // ---- 5 — Pending Approval ----
            [
                'id' => 5,
                'transfer_ref' => 'DEMO-TRF-0005',
                'inmate_id' => 11,
                'prisoner_number' => 'DEMO-PN-0011',
                'inmate_name' => 'Emmanuel Gabriel Tumwine',
                'classification' => 'Convicted',
                'origin_facility' => 'Kampala Prison',
                'destination_facility' => 'Luzira Upper Prison',
                'requesting_facility' => 'Kampala Prison',
                'status' => 'Pending Approval',
                'priority' => 'Urgent',
                'transfer_reason' => 'Welfare or Medical Referral',
                'notes' => 'Medical referral — requires specialist facility proximity.',
                'requesting_officer' => 'Demo Officer — Kampala (demo role)',
                'request_date' => '2025-01-28',
                'proposed_transfer_date' => '2025-02-15',
                'supporting_doc_ref' => 'DOC-KPL-2025-0011',
                'review_status' => null,
                'reviewing_role' => null,
                'review_date' => null,
                'auth_ref' => null,
                'review_notes' => null,
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-01-28',
                'updated_date' => '2025-01-28',
                'closed_date' => null,
            ],

            // ---- 6 — Approved / Ready for Dispatch ----
            [
                'id' => 6,
                'transfer_ref' => 'DEMO-TRF-0006',
                'inmate_id' => 23,
                'prisoner_number' => 'DEMO-PN-0023',
                'inmate_name' => 'Henry Kiberu',
                'classification' => 'Convicted',
                'origin_facility' => 'Kigo Prison',
                'destination_facility' => 'Gulu Prison',
                'requesting_facility' => 'Kigo Prison',
                'status' => 'Ready for Dispatch',
                'priority' => 'Routine',
                'transfer_reason' => 'Administrative Reassignment',
                'notes' => 'Facility reassignment — approved and cleared for dispatch.',
                'requesting_officer' => 'Demo Officer — Kigo (demo role)',
                'request_date' => '2025-01-05',
                'proposed_transfer_date' => '2025-02-20',
                'supporting_doc_ref' => 'DOC-KIG-2025-0006',
                'review_status' => 'Approved',
                'reviewing_role' => 'Regional Prisons Officer (demo role)',
                'review_date' => '2025-01-20',
                'auth_ref' => 'AUTH-2025-0006',
                'review_notes' => 'Approved. Proceed with standard transfer protocol.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-01-05',
                'updated_date' => '2025-01-20',
                'closed_date' => null,
            ],

            // ---- 7 — In Transit ----
            [
                'id' => 7,
                'transfer_ref' => 'DEMO-TRF-0007',
                'inmate_id' => 27,
                'prisoner_number' => 'DEMO-PN-0027',
                'inmate_name' => 'Andrew Daniel Wamala',
                'classification' => 'Convicted',
                'origin_facility' => 'Luzira Upper Prison',
                'destination_facility' => 'Fort Portal Prison',
                'requesting_facility' => 'Luzira Upper Prison',
                'status' => 'In Transit',
                'priority' => 'Routine',
                'transfer_reason' => 'Administrative Reassignment',
                'notes' => 'Population management. Currently en route.',
                'requesting_officer' => 'Demo Officer — Luzira (demo role)',
                'request_date' => '2025-01-15',
                'proposed_transfer_date' => '2025-02-10',
                'supporting_doc_ref' => 'DOC-LUZ-2025-0027',
                'review_status' => 'Approved',
                'reviewing_role' => 'Prisons Commissioner (demo role)',
                'review_date' => '2025-01-28',
                'auth_ref' => 'AUTH-2025-0012',
                'review_notes' => 'Approved.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => '2025-02-10 08:00',
                'dispatching_facility' => 'Luzira Upper Prison',
                'dispatching_officer' => 'Demo Dispatch Officer (demo role)',
                'handover_ref' => 'HO-TRF-2025-0012',
                'escort_ref' => 'ESC-2025-0007',
                'departure_notes' => 'Departed on schedule. All documents verified.',
                'arrival_date' => null,
                'receiving_facility' => 'Fort Portal Prison',
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-01-15',
                'updated_date' => '2025-02-10',
                'closed_date' => null,
            ],

            // ---- 8 — Arrival Confirmation Pending ----
            [
                'id' => 8,
                'transfer_ref' => 'DEMO-TRF-0008',
                'inmate_id' => 19,
                'prisoner_number' => 'DEMO-PN-0019',
                'inmate_name' => 'Fred William Kasibante',
                'classification' => 'Convicted',
                'origin_facility' => 'Luzira Upper Prison',
                'destination_facility' => 'Murchison Bay Prison',
                'requesting_facility' => 'Luzira Upper Prison',
                'status' => 'Arrival Confirmation Pending',
                'priority' => 'Routine',
                'transfer_reason' => 'Facility Capacity Management',
                'notes' => 'Dispatched. Awaiting arrival confirmation from Murchison Bay.',
                'requesting_officer' => 'Demo Officer — Luzira (demo role)',
                'request_date' => '2025-01-20',
                'proposed_transfer_date' => '2025-02-05',
                'supporting_doc_ref' => 'DOC-LUZ-2025-0019',
                'review_status' => 'Approved',
                'reviewing_role' => 'Regional Prisons Officer (demo role)',
                'review_date' => '2025-01-30',
                'auth_ref' => 'AUTH-2025-0015',
                'review_notes' => 'Approved.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => '2025-02-05 09:00',
                'dispatching_facility' => 'Luzira Upper Prison',
                'dispatching_officer' => 'Demo Dispatch Officer (demo role)',
                'handover_ref' => 'HO-TRF-2025-0015',
                'escort_ref' => 'ESC-2025-0009',
                'departure_notes' => 'Departed. Dispatch confirmed.',
                'arrival_date' => null,
                'receiving_facility' => 'Murchison Bay Prison',
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-01-20',
                'updated_date' => '2025-02-05',
                'closed_date' => null,
            ],

            // ---- 9 — Rejected ----
            [
                'id' => 9,
                'transfer_ref' => 'DEMO-TRF-0009',
                'inmate_id' => 4,
                'prisoner_number' => 'DEMO-PN-0004',
                'inmate_name' => 'Mary Rose Nansubuga',
                'classification' => 'Convicted',
                'origin_facility' => 'Kampala Prison',
                'destination_facility' => 'Fort Portal Prison',
                'requesting_facility' => 'Kampala Prison',
                'status' => 'Rejected',
                'priority' => 'Routine',
                'transfer_reason' => 'Administrative Reassignment',
                'notes' => 'Transfer request for administrative reassignment.',
                'requesting_officer' => 'Demo Officer — Kampala (demo role)',
                'request_date' => '2024-10-15',
                'proposed_transfer_date' => '2024-11-01',
                'supporting_doc_ref' => 'DOC-KPL-2024-0400',
                'review_status' => 'Rejected',
                'reviewing_role' => 'Regional Prisons Officer (demo role)',
                'review_date' => '2024-10-25',
                'auth_ref' => null,
                'review_notes' => 'Rejected — insufficient justification provided.',
                'rejection_reason' => 'Insufficient justification. Supporting documentation does not meet the required standard.',
                'clarification_request' => null,
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2024-10-15',
                'updated_date' => '2024-10-25',
                'closed_date' => '2024-10-25',
            ],

            // ---- 10 — Awaiting Clarification ----
            [
                'id' => 10,
                'transfer_ref' => 'DEMO-TRF-0010',
                'inmate_id' => 17,
                'prisoner_number' => 'DEMO-PN-0017',
                'inmate_name' => 'Gerald Francis Nkurunziza',
                'classification' => 'Convicted',
                'origin_facility' => 'Fort Portal Prison',
                'destination_facility' => 'Kampala Prison',
                'requesting_facility' => 'Fort Portal Prison',
                'status' => 'Awaiting Clarification',
                'priority' => 'Routine',
                'transfer_reason' => 'Court-Related Requirement',
                'notes' => 'Transfer requested for upcoming court appearances.',
                'requesting_officer' => 'Demo Officer — Fort Portal (demo role)',
                'request_date' => '2025-01-18',
                'proposed_transfer_date' => '2025-02-28',
                'supporting_doc_ref' => 'DOC-FTP-2025-0010',
                'review_status' => 'Awaiting Clarification',
                'reviewing_role' => 'Regional Prisons Officer (demo role)',
                'review_date' => '2025-01-28',
                'auth_ref' => null,
                'review_notes' => 'Clarification requested on court reference.',
                'rejection_reason' => null,
                'clarification_request' => 'Please provide the court case number and hearing date confirmation.',
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-01-18',
                'updated_date' => '2025-01-28',
                'closed_date' => null,
            ],

            // ---- 11 — Cancelled ----
            [
                'id' => 11,
                'transfer_ref' => 'DEMO-TRF-0011',
                'inmate_id' => 9,
                'prisoner_number' => 'DEMO-PN-0009',
                'inmate_name' => 'David Paul Kyomuhendo',
                'classification' => 'Civil Debtor',
                'origin_facility' => 'Kampala Prison',
                'destination_facility' => 'Soroti Prison',
                'requesting_facility' => 'Kampala Prison',
                'status' => 'Cancelled',
                'priority' => 'Routine',
                'transfer_reason' => 'Administrative Reassignment',
                'notes' => 'Request cancelled — court order suspended the civil case.',
                'requesting_officer' => 'Demo Officer — Kampala (demo role)',
                'request_date' => '2024-08-10',
                'proposed_transfer_date' => '2024-09-01',
                'supporting_doc_ref' => 'DOC-KPL-2024-0350',
                'review_status' => 'Cancelled',
                'reviewing_role' => null,
                'review_date' => '2024-08-18',
                'auth_ref' => null,
                'review_notes' => 'Request withdrawn — legal basis for transfer no longer applicable.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2024-08-10',
                'updated_date' => '2024-08-18',
                'closed_date' => '2024-08-18',
            ],

            // ---- 12 — Draft ----
            [
                'id' => 12,
                'transfer_ref' => 'DEMO-TRF-0012',
                'inmate_id' => 29,
                'prisoner_number' => 'DEMO-PN-0029',
                'inmate_name' => 'Kenneth Patrick Sebagereka',
                'classification' => 'Convicted',
                'origin_facility' => 'Jinja Prison',
                'destination_facility' => 'Luzira Upper Prison',
                'requesting_facility' => 'Jinja Prison',
                'status' => 'Draft',
                'priority' => 'Routine',
                'transfer_reason' => 'Other Authorized Reason',
                'notes' => 'Draft — not yet submitted for review.',
                'requesting_officer' => 'Demo Officer — Jinja (demo role)',
                'request_date' => '2025-02-01',
                'proposed_transfer_date' => '2025-03-15',
                'supporting_doc_ref' => null,
                'review_status' => null,
                'reviewing_role' => null,
                'review_date' => null,
                'auth_ref' => null,
                'review_notes' => null,
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-02-01',
                'updated_date' => '2025-02-01',
                'closed_date' => null,
            ],

            // ---- 13 — Completed ----
            [
                'id' => 13,
                'transfer_ref' => 'DEMO-TRF-0013',
                'inmate_id' => 25,
                'prisoner_number' => 'DEMO-PN-0025',
                'inmate_name' => 'Joseph Stephen Alele',
                'classification' => 'Convicted',
                'origin_facility' => 'Lira Prison',
                'destination_facility' => 'Gulu Prison',
                'requesting_facility' => 'Lira Prison',
                'status' => 'Completed',
                'priority' => 'Routine',
                'transfer_reason' => 'Facility Capacity Management',
                'notes' => 'Population rebalancing.',
                'requesting_officer' => 'Demo Officer — Lira (demo role)',
                'request_date' => '2016-07-10',
                'proposed_transfer_date' => '2016-08-18',
                'supporting_doc_ref' => 'DOC-LRA-2016-0411',
                'review_status' => 'Approved',
                'reviewing_role' => 'Regional Prisons Officer (demo role)',
                'review_date' => '2016-08-01',
                'auth_ref' => 'AUTH-2016-0088',
                'review_notes' => 'Approved.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => '2016-08-18 08:30',
                'dispatching_facility' => 'Lira Prison',
                'dispatching_officer' => 'Demo Dispatch Officer (demo role)',
                'handover_ref' => 'HO-TRF-2016-0088',
                'escort_ref' => 'ESC-2016-0044',
                'departure_notes' => 'Departed in good health.',
                'arrival_date' => '2016-08-18 14:00',
                'receiving_facility' => 'Gulu Prison',
                'receiving_officer' => 'Demo Receiving Officer (demo role)',
                'arrival_ref' => 'ARR-2016-0066',
                'receipt_notes' => 'Received and admitted.',
                'created_date' => '2016-07-10',
                'updated_date' => '2016-08-18',
                'closed_date' => '2016-08-18',
            ],

            // ---- 14 — Pending Approval ----
            [
                'id' => 14,
                'transfer_ref' => 'DEMO-TRF-0014',
                'inmate_id' => 21,
                'prisoner_number' => 'DEMO-PN-0021',
                'inmate_name' => 'Patrick James Obbo',
                'classification' => 'Convicted',
                'origin_facility' => 'Soroti Prison',
                'destination_facility' => 'Kampala Prison',
                'requesting_facility' => 'Soroti Prison',
                'status' => 'Pending Approval',
                'priority' => 'Routine',
                'transfer_reason' => 'Court-Related Requirement',
                'notes' => 'Transfer required for court attendance.',
                'requesting_officer' => 'Demo Officer — Soroti (demo role)',
                'request_date' => '2025-02-05',
                'proposed_transfer_date' => '2025-03-01',
                'supporting_doc_ref' => 'DOC-SRT-2025-0014',
                'review_status' => null,
                'reviewing_role' => null,
                'review_date' => null,
                'auth_ref' => null,
                'review_notes' => null,
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => null,
                'dispatching_facility' => null,
                'dispatching_officer' => null,
                'handover_ref' => null,
                'escort_ref' => null,
                'departure_notes' => null,
                'arrival_date' => null,
                'receiving_facility' => null,
                'receiving_officer' => null,
                'arrival_ref' => null,
                'receipt_notes' => null,
                'created_date' => '2025-02-05',
                'updated_date' => '2025-02-05',
                'closed_date' => null,
            ],

            // ---- 15 — Completed ----
            [
                'id' => 15,
                'transfer_ref' => 'DEMO-TRF-0015',
                'inmate_id' => 14,
                'prisoner_number' => 'DEMO-PN-0014',
                'inmate_name' => 'Lydia Comfort Ampaire',
                'classification' => 'Convicted',
                'origin_facility' => 'Mbarara Prison',
                'destination_facility' => 'Kampala Prison',
                'requesting_facility' => 'Mbarara Prison',
                'status' => 'Completed',
                'priority' => 'Routine',
                'transfer_reason' => 'Court-Related Requirement',
                'notes' => 'Transfer to attend High Court proceedings.',
                'requesting_officer' => 'Demo Officer — Mbarara (demo role)',
                'request_date' => '2024-02-10',
                'proposed_transfer_date' => '2024-02-29',
                'supporting_doc_ref' => 'DOC-MBA-2024-0073',
                'review_status' => 'Approved',
                'reviewing_role' => 'Prisons Commissioner (demo role)',
                'review_date' => '2024-02-20',
                'auth_ref' => 'AUTH-2024-0073',
                'review_notes' => 'Approved for court attendance.',
                'rejection_reason' => null,
                'clarification_request' => null,
                'dispatch_date' => '2024-02-29 08:00',
                'dispatching_facility' => 'Mbarara Prison',
                'dispatching_officer' => 'Demo Dispatch Officer (demo role)',
                'handover_ref' => 'HO-TRF-2024-0073',
                'escort_ref' => 'ESC-2024-0033',
                'departure_notes' => 'Departed on schedule.',
                'arrival_date' => '2024-02-29 14:00',
                'receiving_facility' => 'Kampala Prison',
                'receiving_officer' => 'Demo Receiving Officer (demo role)',
                'arrival_ref' => 'ARR-2024-0073',
                'receipt_notes' => 'Received in good order. Documents verified.',
                'created_date' => '2024-02-10',
                'updated_date' => '2024-02-29',
                'closed_date' => '2024-02-29',
            ],

        ];
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    /** Find by internal transfer ID. */
    public static function find(int $id): ?array
    {
        foreach (static::all() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }

        return null;
    }

    /** All active (non-terminal) transfers. */
    public static function active(): array
    {
        $terminal = ['Completed', 'Rejected', 'Cancelled'];

        return array_values(array_filter(
            static::all(),
            fn ($t) => ! in_array($t['status'], $terminal)
        ));
    }

    /** All historical (terminal) transfers. */
    public static function historical(): array
    {
        $terminal = ['Completed', 'Rejected', 'Cancelled'];

        return array_values(array_filter(
            static::all(),
            fn ($t) => in_array($t['status'], $terminal)
        ));
    }

    /** Summary counts derived from dataset. */
    public static function summaryCounts(): array
    {
        $all = static::all();

        return [
            'total' => count($all),
            'pending' => count(array_filter($all, fn ($t) => $t['status'] === 'Pending Approval')),
            'in_transit' => count(array_filter($all, fn ($t) => in_array($t['status'], ['In Transit', 'Arrival Confirmation Pending']))),
            'completed' => count(array_filter($all, fn ($t) => $t['status'] === 'Completed')),
        ];
    }

    /** Transfers for a specific inmate ID. */
    public static function forInmate(int $inmateId): array
    {
        return array_values(array_filter(
            static::all(),
            fn ($t) => $t['inmate_id'] === $inmateId
        ));
    }

    /**
     * Build a timeline of events for a transfer.
     * Only includes events that have actually occurred (non-null dates).
     */
    public static function timeline(array $transfer): array
    {
        $events = [];

        $events[] = [
            'event' => 'Request Created',
            'detail' => 'Transfer request created by '.$transfer['requesting_officer'].'.',
            'ts' => $transfer['created_date'].' 09:00',
            'type' => 'created',
            'terminal' => false,
        ];

        if (! in_array($transfer['status'], ['Draft'])) {
            $events[] = [
                'event' => 'Submitted for Review',
                'detail' => 'Request submitted to reviewing authority.',
                'ts' => $transfer['created_date'].' 10:30',
                'type' => 'submitted',
                'terminal' => false,
            ];
        }

        if ($transfer['review_date']) {
            $decision = $transfer['review_status'] ?? 'Reviewed';
            $detail = match ($transfer['review_status']) {
                'Approved' => 'Transfer approved. Auth Ref: '.($transfer['auth_ref'] ?? 'N/A'),
                'Rejected' => 'Request rejected. Reason: '.($transfer['rejection_reason'] ?? ''),
                'Awaiting Clarification' => 'Clarification requested: '.($transfer['clarification_request'] ?? ''),
                'Cancelled' => 'Request cancelled. Notes: '.($transfer['review_notes'] ?? ''),
                default => $transfer['review_notes'] ?? '',
            };
            $events[] = [
                'event' => 'Authorization Decision: '.$decision,
                'detail' => $detail,
                'ts' => $transfer['review_date'].' 14:00',
                'type' => in_array($decision, ['Rejected', 'Cancelled']) ? 'rejected' : 'approved',
                'terminal' => in_array($decision, ['Rejected', 'Cancelled']),
            ];
        }

        if ($transfer['dispatch_date']) {
            $events[] = [
                'event' => 'Dispatch Recorded',
                'detail' => 'Inmate dispatched from '.$transfer['dispatching_facility'].'. Handover Ref: '.$transfer['handover_ref'],
                'ts' => $transfer['dispatch_date'],
                'type' => 'dispatched',
                'terminal' => false,
            ];
        }

        if ($transfer['arrival_date']) {
            $events[] = [
                'event' => 'Arrival Confirmed',
                'detail' => 'Inmate arrived at '.$transfer['receiving_facility'].'. Arrival Ref: '.$transfer['arrival_ref'],
                'ts' => $transfer['arrival_date'],
                'type' => 'arrived',
                'terminal' => false,
            ];
        }

        if ($transfer['status'] === 'Completed' && $transfer['closed_date']) {
            $events[] = [
                'event' => 'Transfer Completed',
                'detail' => 'Transfer record closed on '.$transfer['closed_date'].'.',
                'ts' => $transfer['closed_date'].' 16:00',
                'type' => 'completed',
                'terminal' => true,
            ];
        }

        return $events;
    }

    /** Pending transfer count — used by dashboard. */
    public static function pendingCount(): int
    {
        return count(array_filter(
            static::all(),
            fn ($t) => in_array($t['status'], ['Pending Approval', 'Awaiting Clarification'])
        ));
    }
}
