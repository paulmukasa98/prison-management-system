<?php

namespace App\Support\Demo;

use Carbon\Carbon;

/**
 * InmateDemoData — Phase 3 fictional inmate records.
 *
 * IMPORTANT: Every record here is completely invented.
 * No real personal data has been used. These records exist solely
 * to demonstrate the Phase 3 frontend interfaces.
 *
 * Prisoner numbers use the prefix DEMO-PN- to make their fictional
 * nature unambiguous. They are NOT official Uganda Prisons Service numbers.
 *
 * Internal record IDs (key 'id') are immutable integer identifiers
 * and are distinct from prisoner numbers.
 */
class InmateDemoData
{
    // -------------------------------------------------------------------------
    // Static reference lists
    // -------------------------------------------------------------------------

    public static array $facilities = [
        'Luzira Upper Prison',
        'Murchison Bay Prison',
        'Kampala Prison',
        'Gulu Prison',
        'Fort Portal Prison',
        'Soroti Prison',
        'Kigo Prison',
        'Jinja Prison',
    ];

    public static array $classifications = [
        'Convicted',
        'Remand',
        'Civil Debtor',
    ];

    public static array $statuses = [
        'In Custody',
        'Remanded',
        'Released',
        'Transferred',
        'Deceased',
    ];

    public static array $districts = [
        'Kampala', 'Wakiso', 'Mukono', 'Jinja', 'Gulu',
        'Lira', 'Arua', 'Mbarara', 'Kabale', 'Fort Portal',
        'Soroti', 'Mbale', 'Tororo', 'Masaka', 'Hoima',
        'Kasese', 'Bushenyi', 'Iganga', 'Moroto', 'Kitgum',
    ];

    public static array $idTypes = [
        'National ID',
        'Passport',
        'Driving Permit',
        'Refugee ID',
        'None / Not Presented',
    ];

    public static array $dischargeReasons = [
        'Sentence Served',
        'Bail Granted',
        'Court Acquittal',
        'Presidential Pardon',
        'Appeal Upheld',
        'Civil Order Lifted',
        'Death',
    ];

    // -------------------------------------------------------------------------
    // The 30-record fictional dataset
    // -------------------------------------------------------------------------

    public static function all(): array
    {
        return [

            // ---- 1 ----
            [
                'id' => 1,
                'prisoner_number' => 'DEMO-PN-0001',
                'first_name' => 'John',
                'middle_name' => 'Robert',
                'last_name' => 'Mukasa',
                'gender' => 'Male',
                'date_of_birth' => '1985-03-14',
                'nationality' => 'Ugandan',
                'district' => 'Kampala',
                'id_type' => 'National ID',
                'id_number' => 'CM85031400XX',
                'facility' => 'Luzira Upper Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2022-06-10',
                'admission_ref' => 'ADM-2022-0387',
                'legal_basis' => 'Sentenced — High Court of Uganda',
                'warrant_ref' => 'HCT-00-CR-2022-0112',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 112/2022',
                'next_court_date' => null,
                'sentence' => '7 years imprisonment',
                'expected_release' => '2029-06-10',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Mary Mukasa',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 700 112 233',
                'notes' => 'No special requirements noted.',
                'photo' => null,
            ],

            // ---- 2 ----
            [
                'id' => 2,
                'prisoner_number' => 'DEMO-PN-0002',
                'first_name' => 'Grace',
                'middle_name' => null,
                'last_name' => 'Atim',
                'gender' => 'Female',
                'date_of_birth' => '1992-07-22',
                'nationality' => 'Ugandan',
                'district' => 'Gulu',
                'id_type' => 'National ID',
                'id_number' => 'CF92072200YY',
                'facility' => 'Murchison Bay Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2024-01-14',
                'admission_ref' => 'ADM-2024-0021',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-MBR-2024-0021',
                'issuing_authority' => 'Chief Magistrates Court, Gulu',
                'case_ref' => 'Criminal Case No. 021/2024',
                'next_court_date' => '2025-02-15',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Patrick Atim',
                'kin_relationship' => 'Father',
                'kin_contact' => '+256 755 334 455',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 3 ----
            [
                'id' => 3,
                'prisoner_number' => 'DEMO-PN-0003',
                'first_name' => 'Peter',
                'middle_name' => 'James',
                'last_name' => 'Okello',
                'gender' => 'Male',
                'date_of_birth' => '1978-11-05',
                'nationality' => 'Ugandan',
                'district' => 'Soroti',
                'id_type' => 'Passport',
                'id_number' => 'B07812345',
                'facility' => 'Gulu Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2023-11-03',
                'admission_ref' => 'ADM-2023-0982',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2023-0876',
                'issuing_authority' => 'High Court of Uganda, Gulu',
                'case_ref' => 'Criminal Case No. 876/2023',
                'next_court_date' => null,
                'sentence' => '10 years imprisonment',
                'expected_release' => '2033-11-03',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Susan Okello',
                'kin_relationship' => 'Mother',
                'kin_contact' => '+256 712 445 566',
                'notes' => 'Second admission. First admission: 2015–2018.',
                'photo' => null,
            ],

            // ---- 4 ----
            [
                'id' => 4,
                'prisoner_number' => 'DEMO-PN-0004',
                'first_name' => 'Mary',
                'middle_name' => 'Rose',
                'last_name' => 'Nansubuga',
                'gender' => 'Female',
                'date_of_birth' => '1990-04-18',
                'nationality' => 'Ugandan',
                'district' => 'Masaka',
                'id_type' => 'National ID',
                'id_number' => 'CF90041800ZZ',
                'facility' => 'Kampala Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2023-08-28',
                'admission_ref' => 'ADM-2023-0761',
                'legal_basis' => 'Sentenced — Magistrates Court',
                'warrant_ref' => 'CRC-KPL-2023-0761',
                'issuing_authority' => 'Grade One Magistrates Court, Masaka',
                'case_ref' => 'Criminal Case No. 761/2023',
                'next_court_date' => null,
                'sentence' => '3 years imprisonment',
                'expected_release' => '2026-08-28',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'James Nansubuga',
                'kin_relationship' => 'Brother',
                'kin_contact' => '+256 701 556 677',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 5 ----
            [
                'id' => 5,
                'prisoner_number' => 'DEMO-PN-0005',
                'first_name' => 'Robert',
                'middle_name' => null,
                'last_name' => 'Ssemakula',
                'gender' => 'Male',
                'date_of_birth' => '1995-09-30',
                'nationality' => 'Ugandan',
                'district' => 'Wakiso',
                'id_type' => 'National ID',
                'id_number' => 'CM95093000AA',
                'facility' => 'Fort Portal Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2024-01-05',
                'admission_ref' => 'ADM-2024-0009',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-FTP-2024-0009',
                'issuing_authority' => 'Chief Magistrates Court, Fort Portal',
                'case_ref' => 'Criminal Case No. 009/2024',
                'next_court_date' => '2025-03-10',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => null,
                'kin_relationship' => null,
                'kin_contact' => null,
                'notes' => 'Next-of-kin details not yet recorded.',
                'photo' => null,
            ],

            // ---- 6 ---- (discharged)
            [
                'id' => 6,
                'prisoner_number' => 'DEMO-PN-0006',
                'first_name' => 'Alice',
                'middle_name' => 'Joyce',
                'last_name' => 'Aceng',
                'gender' => 'Female',
                'date_of_birth' => '1988-02-07',
                'nationality' => 'Ugandan',
                'district' => 'Lira',
                'id_type' => 'National ID',
                'id_number' => 'CF88020700BB',
                'facility' => 'Luzira Upper Prison',
                'classification' => 'Convicted',
                'status' => 'Released',
                'admission_date' => '2023-06-17',
                'admission_ref' => 'ADM-2023-0456',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2023-0400',
                'issuing_authority' => 'High Court of Uganda, Lira',
                'case_ref' => 'Criminal Case No. 400/2023',
                'next_court_date' => null,
                'sentence' => '1 year 6 months imprisonment',
                'expected_release' => '2024-12-17',
                'actual_discharge' => '2024-12-17',
                'discharge_reason' => 'Sentence Served',
                'discharge_auth_ref' => 'DCH-2024-0211',
                'kin_name' => 'Isaac Aceng',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 783 667 788',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 7 ----
            [
                'id' => 7,
                'prisoner_number' => 'DEMO-PN-0007',
                'first_name' => 'Samuel',
                'middle_name' => 'Timothy',
                'last_name' => 'Opio',
                'gender' => 'Male',
                'date_of_birth' => '1980-12-01',
                'nationality' => 'Ugandan',
                'district' => 'Kitgum',
                'id_type' => 'None / Not Presented',
                'id_number' => null,
                'facility' => 'Luzira Upper Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2021-03-22',
                'admission_ref' => 'ADM-2021-0188',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2021-0155',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 155/2021',
                'next_court_date' => null,
                'sentence' => '15 years imprisonment',
                'expected_release' => '2036-03-22',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Ruth Opio',
                'kin_relationship' => 'Sister',
                'kin_contact' => '+256 790 778 899',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 8 ----
            [
                'id' => 8,
                'prisoner_number' => 'DEMO-PN-0008',
                'first_name' => 'Fatuma',
                'middle_name' => null,
                'last_name' => 'Nakato',
                'gender' => 'Female',
                'date_of_birth' => '2000-05-19',
                'nationality' => 'Ugandan',
                'district' => 'Jinja',
                'id_type' => 'National ID',
                'id_number' => 'CF00051900CC',
                'facility' => 'Murchison Bay Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2024-08-30',
                'admission_ref' => 'ADM-2024-0314',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-MBR-2024-0314',
                'issuing_authority' => 'Grade One Magistrates Court, Jinja',
                'case_ref' => 'Criminal Case No. 314/2024',
                'next_court_date' => '2025-04-20',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Halima Nakato',
                'kin_relationship' => 'Mother',
                'kin_contact' => '+256 762 889 900',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 9 ----
            [
                'id' => 9,
                'prisoner_number' => 'DEMO-PN-0009',
                'first_name' => 'David',
                'middle_name' => 'Paul',
                'last_name' => 'Kyomuhendo',
                'gender' => 'Male',
                'date_of_birth' => '1975-08-14',
                'nationality' => 'Ugandan',
                'district' => 'Mbarara',
                'id_type' => 'Driving Permit',
                'id_number' => 'DP75081400DD',
                'facility' => 'Kampala Prison',
                'classification' => 'Civil Debtor',
                'status' => 'In Custody',
                'admission_date' => '2024-03-15',
                'admission_ref' => 'ADM-2024-0092',
                'legal_basis' => 'Civil Order — Committal for Civil Debt',
                'warrant_ref' => 'HCCS-MBR-2024-0092',
                'issuing_authority' => 'High Court Commercial Division',
                'case_ref' => 'Civil Suit No. 092/2024',
                'next_court_date' => '2025-01-28',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Jane Kyomuhendo',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 741 990 011',
                'notes' => 'Civil debt committal — not a criminal sentence.',
                'photo' => null,
            ],

            // ---- 10 ----
            [
                'id' => 10,
                'prisoner_number' => 'DEMO-PN-0010',
                'first_name' => 'Christine',
                'middle_name' => null,
                'last_name' => 'Aber',
                'gender' => 'Female',
                'date_of_birth' => '1998-01-25',
                'nationality' => 'Ugandan',
                'district' => 'Arua',
                'id_type' => 'National ID',
                'id_number' => 'CF98012500EE',
                'facility' => 'Gulu Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2024-07-11',
                'admission_ref' => 'ADM-2024-0271',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-GLU-2024-0271',
                'issuing_authority' => 'Chief Magistrates Court, Arua',
                'case_ref' => 'Criminal Case No. 271/2024',
                'next_court_date' => '2025-05-05',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Thomas Aber',
                'kin_relationship' => 'Father',
                'kin_contact' => '+256 723 101 122',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 11 ----
            [
                'id' => 11,
                'prisoner_number' => 'DEMO-PN-0011',
                'first_name' => 'Emmanuel',
                'middle_name' => 'Gabriel',
                'last_name' => 'Tumwine',
                'gender' => 'Male',
                'date_of_birth' => '1983-06-28',
                'nationality' => 'Ugandan',
                'district' => 'Kabale',
                'id_type' => 'National ID',
                'id_number' => 'CM83062800FF',
                'facility' => 'Kampala Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2020-09-12',
                'admission_ref' => 'ADM-2020-0633',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2020-0590',
                'issuing_authority' => 'High Court of Uganda, Kabale',
                'case_ref' => 'Criminal Case No. 590/2020',
                'next_court_date' => null,
                'sentence' => '12 years imprisonment',
                'expected_release' => '2032-09-12',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Agnes Tumwine',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 704 212 233',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 12 ---- (discharged)
            [
                'id' => 12,
                'prisoner_number' => 'DEMO-PN-0012',
                'first_name' => 'Harriet',
                'middle_name' => null,
                'last_name' => 'Babirye',
                'gender' => 'Female',
                'date_of_birth' => '1987-10-11',
                'nationality' => 'Ugandan',
                'district' => 'Mukono',
                'id_type' => 'National ID',
                'id_number' => 'CF87101100GG',
                'facility' => 'Luzira Upper Prison',
                'classification' => 'Convicted',
                'status' => 'Released',
                'admission_date' => '2022-01-20',
                'admission_ref' => 'ADM-2022-0045',
                'legal_basis' => 'Sentenced — Magistrates Court',
                'warrant_ref' => 'CRC-MKN-2022-0045',
                'issuing_authority' => 'Grade One Magistrates Court, Mukono',
                'case_ref' => 'Criminal Case No. 045/2022',
                'next_court_date' => null,
                'sentence' => '2 years imprisonment',
                'expected_release' => '2024-01-20',
                'actual_discharge' => '2024-01-20',
                'discharge_reason' => 'Sentence Served',
                'discharge_auth_ref' => 'DCH-2024-0019',
                'kin_name' => 'Fred Babirye',
                'kin_relationship' => 'Brother',
                'kin_contact' => '+256 718 323 344',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 13 ----
            [
                'id' => 13,
                'prisoner_number' => 'DEMO-PN-0013',
                'first_name' => 'Isaac',
                'middle_name' => 'Benard',
                'last_name' => 'Olweny',
                'gender' => 'Male',
                'date_of_birth' => '1991-03-03',
                'nationality' => 'Ugandan',
                'district' => 'Tororo',
                'id_type' => 'Passport',
                'id_number' => 'B09123456',
                'facility' => 'Soroti Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2024-10-02',
                'admission_ref' => 'ADM-2024-0501',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-SRT-2024-0501',
                'issuing_authority' => 'Chief Magistrates Court, Soroti',
                'case_ref' => 'Criminal Case No. 501/2024',
                'next_court_date' => '2025-06-15',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Eunice Olweny',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 735 434 455',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 14 ----
            [
                'id' => 14,
                'prisoner_number' => 'DEMO-PN-0014',
                'first_name' => 'Lydia',
                'middle_name' => 'Comfort',
                'last_name' => 'Ampaire',
                'gender' => 'Female',
                'date_of_birth' => '1994-08-16',
                'nationality' => 'Ugandan',
                'district' => 'Bushenyi',
                'id_type' => 'National ID',
                'id_number' => 'CF94081600HH',
                'facility' => 'Kampala Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2024-02-29',
                'admission_ref' => 'ADM-2024-0073',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2024-0062',
                'issuing_authority' => 'High Court of Uganda, Mbarara',
                'case_ref' => 'Criminal Case No. 062/2024',
                'next_court_date' => null,
                'sentence' => '5 years imprisonment',
                'expected_release' => '2029-02-28',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Paul Ampaire',
                'kin_relationship' => 'Father',
                'kin_contact' => '+256 756 545 566',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 15 ---- (transferred)
            [
                'id' => 15,
                'prisoner_number' => 'DEMO-PN-0015',
                'first_name' => 'Moses',
                'middle_name' => null,
                'last_name' => 'Byamugisha',
                'gender' => 'Male',
                'date_of_birth' => '1977-01-09',
                'nationality' => 'Ugandan',
                'district' => 'Kasese',
                'id_type' => 'National ID',
                'id_number' => 'CM77010900II',
                'facility' => 'Murchison Bay Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2019-05-07',
                'admission_ref' => 'ADM-2019-0344',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2019-0290',
                'issuing_authority' => 'High Court of Uganda, Fort Portal',
                'case_ref' => 'Criminal Case No. 290/2019',
                'next_court_date' => null,
                'sentence' => '20 years imprisonment',
                'expected_release' => '2039-05-07',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Ruth Byamugisha',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 707 656 677',
                'notes' => 'Transferred from Fort Portal Prison on 14 Mar 2021.',
                'photo' => null,
            ],

            // ---- 16 ----
            [
                'id' => 16,
                'prisoner_number' => 'DEMO-PN-0016',
                'first_name' => 'Irene',
                'middle_name' => 'Peace',
                'last_name' => 'Akello',
                'gender' => 'Female',
                'date_of_birth' => '1996-04-23',
                'nationality' => 'Ugandan',
                'district' => 'Moroto',
                'id_type' => 'None / Not Presented',
                'id_number' => null,
                'facility' => 'Gulu Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2025-01-08',
                'admission_ref' => 'ADM-2025-0003',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-GLU-2025-0003',
                'issuing_authority' => 'Chief Magistrates Court, Moroto',
                'case_ref' => 'Criminal Case No. 003/2025',
                'next_court_date' => '2025-07-20',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => null,
                'kin_relationship' => null,
                'kin_contact' => null,
                'notes' => 'Next-of-kin not yet recorded. ID not presented at admission.',
                'photo' => null,
            ],

            // ---- 17 ----
            [
                'id' => 17,
                'prisoner_number' => 'DEMO-PN-0017',
                'first_name' => 'Gerald',
                'middle_name' => 'Francis',
                'last_name' => 'Nkurunziza',
                'gender' => 'Male',
                'date_of_birth' => '1982-09-19',
                'nationality' => 'Ugandan',
                'district' => 'Kabale',
                'id_type' => 'National ID',
                'id_number' => 'CM82091900JJ',
                'facility' => 'Fort Portal Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2023-04-18',
                'admission_ref' => 'ADM-2023-0334',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2023-0290',
                'issuing_authority' => 'High Court of Uganda, Fort Portal',
                'case_ref' => 'Criminal Case No. 290/2023',
                'next_court_date' => null,
                'sentence' => '8 years imprisonment',
                'expected_release' => '2031-04-18',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Beatrice Nkurunziza',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 778 767 788',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 18 ---- (bail granted / discharged)
            [
                'id' => 18,
                'prisoner_number' => 'DEMO-PN-0018',
                'first_name' => 'Agnes',
                'middle_name' => null,
                'last_name' => 'Namukasa',
                'gender' => 'Female',
                'date_of_birth' => '2001-12-04',
                'nationality' => 'Ugandan',
                'district' => 'Wakiso',
                'id_type' => 'National ID',
                'id_number' => 'CF01120400KK',
                'facility' => 'Murchison Bay Prison',
                'classification' => 'Remand',
                'status' => 'Released',
                'admission_date' => '2024-04-10',
                'admission_ref' => 'ADM-2024-0152',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-MBR-2024-0152',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 152/2024',
                'next_court_date' => '2025-08-01',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => '2024-09-15',
                'discharge_reason' => 'Bail Granted',
                'discharge_auth_ref' => 'DCH-2024-0178',
                'kin_name' => 'Paul Namukasa',
                'kin_relationship' => 'Father',
                'kin_contact' => '+256 744 878 899',
                'notes' => 'Released on bail. Case still ongoing.',
                'photo' => null,
            ],

            // ---- 19 ----
            [
                'id' => 19,
                'prisoner_number' => 'DEMO-PN-0019',
                'first_name' => 'Fred',
                'middle_name' => 'William',
                'last_name' => 'Kasibante',
                'gender' => 'Male',
                'date_of_birth' => '1970-07-07',
                'nationality' => 'Ugandan',
                'district' => 'Hoima',
                'id_type' => 'National ID',
                'id_number' => 'CM70070700LL',
                'facility' => 'Luzira Upper Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2018-11-14',
                'admission_ref' => 'ADM-2018-0712',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2018-0650',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 650/2018',
                'next_court_date' => null,
                'sentence' => '25 years imprisonment',
                'expected_release' => '2043-11-14',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Rachael Kasibante',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 712 989 900',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 20 ----
            [
                'id' => 20,
                'prisoner_number' => 'DEMO-PN-0020',
                'first_name' => 'Jane',
                'middle_name' => null,
                'last_name' => 'Aciro',
                'gender' => 'Female',
                'date_of_birth' => '2003-11-30',
                'nationality' => 'Ugandan',
                'district' => 'Kitgum',
                'id_type' => 'National ID',
                'id_number' => 'CF03113000MM',
                'facility' => 'Gulu Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2025-02-03',
                'admission_ref' => 'ADM-2025-0011',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-GLU-2025-0011',
                'issuing_authority' => 'Chief Magistrates Court, Kitgum',
                'case_ref' => 'Criminal Case No. 011/2025',
                'next_court_date' => '2025-09-12',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Margaret Aciro',
                'kin_relationship' => 'Mother',
                'kin_contact' => '+256 798 090 011',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 21 ----
            [
                'id' => 21,
                'prisoner_number' => 'DEMO-PN-0021',
                'first_name' => 'Patrick',
                'middle_name' => 'James',
                'last_name' => 'Obbo',
                'gender' => 'Male',
                'date_of_birth' => '1989-05-14',
                'nationality' => 'Ugandan',
                'district' => 'Iganga',
                'id_type' => 'National ID',
                'id_number' => 'CM89051400NN',
                'facility' => 'Soroti Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2022-10-21',
                'admission_ref' => 'ADM-2022-0754',
                'legal_basis' => 'Sentenced — Magistrates Court',
                'warrant_ref' => 'CRC-SRT-2022-0754',
                'issuing_authority' => 'Grade One Magistrates Court, Soroti',
                'case_ref' => 'Criminal Case No. 754/2022',
                'next_court_date' => null,
                'sentence' => '4 years imprisonment',
                'expected_release' => '2026-10-21',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Helen Obbo',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 707 101 122',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 22 ----
            [
                'id' => 22,
                'prisoner_number' => 'DEMO-PN-0022',
                'first_name' => 'Brenda',
                'middle_name' => 'Hope',
                'last_name' => 'Nankabirwa',
                'gender' => 'Female',
                'date_of_birth' => '1993-02-28',
                'nationality' => 'Ugandan',
                'district' => 'Masaka',
                'id_type' => 'National ID',
                'id_number' => 'CF93022800OO',
                'facility' => 'Kampala Prison',
                'classification' => 'Civil Debtor',
                'status' => 'In Custody',
                'admission_date' => '2024-06-04',
                'admission_ref' => 'ADM-2024-0229',
                'legal_basis' => 'Civil Order — Committal for Civil Debt',
                'warrant_ref' => 'HCCS-KPL-2024-0229',
                'issuing_authority' => 'High Court Commercial Division',
                'case_ref' => 'Civil Suit No. 229/2024',
                'next_court_date' => '2025-04-10',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Kenneth Nankabirwa',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 719 212 233',
                'notes' => 'Civil debt committal.',
                'photo' => null,
            ],

            // ---- 23 ----
            [
                'id' => 23,
                'prisoner_number' => 'DEMO-PN-0023',
                'first_name' => 'Henry',
                'middle_name' => null,
                'last_name' => 'Kiberu',
                'gender' => 'Male',
                'date_of_birth' => '1981-10-08',
                'nationality' => 'Ugandan',
                'district' => 'Mukono',
                'id_type' => 'National ID',
                'id_number' => 'CM81100800PP',
                'facility' => 'Kigo Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2023-07-29',
                'admission_ref' => 'ADM-2023-0601',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2023-0540',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 540/2023',
                'next_court_date' => null,
                'sentence' => '6 years imprisonment',
                'expected_release' => '2029-07-29',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Josephine Kiberu',
                'kin_relationship' => 'Mother',
                'kin_contact' => '+256 731 323 344',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 24 ---- (acquittal)
            [
                'id' => 24,
                'prisoner_number' => 'DEMO-PN-0024',
                'first_name' => 'Sarah',
                'middle_name' => 'Comfort',
                'last_name' => 'Lukwago',
                'gender' => 'Female',
                'date_of_birth' => '1999-06-14',
                'nationality' => 'Ugandan',
                'district' => 'Kampala',
                'id_type' => 'National ID',
                'id_number' => 'CF99061400QQ',
                'facility' => 'Murchison Bay Prison',
                'classification' => 'Remand',
                'status' => 'Released',
                'admission_date' => '2024-03-05',
                'admission_ref' => 'ADM-2024-0101',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-MBR-2024-0101',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 101/2024',
                'next_court_date' => null,
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => '2024-11-10',
                'discharge_reason' => 'Court Acquittal',
                'discharge_auth_ref' => 'DCH-2024-0188',
                'kin_name' => 'Grace Lukwago',
                'kin_relationship' => 'Mother',
                'kin_contact' => '+256 744 434 455',
                'notes' => 'Acquitted after full trial.',
                'photo' => null,
            ],

            // ---- 25 ----
            [
                'id' => 25,
                'prisoner_number' => 'DEMO-PN-0025',
                'first_name' => 'Joseph',
                'middle_name' => 'Stephen',
                'last_name' => 'Alele',
                'gender' => 'Male',
                'date_of_birth' => '1972-04-20',
                'nationality' => 'Ugandan',
                'district' => 'Lira',
                'id_type' => 'Refugee ID',
                'id_number' => 'REF-72042000RR',
                'facility' => 'Gulu Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2016-08-18',
                'admission_ref' => 'ADM-2016-0411',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2016-0375',
                'issuing_authority' => 'High Court of Uganda, Lira',
                'case_ref' => 'Criminal Case No. 375/2016',
                'next_court_date' => null,
                'sentence' => '30 years imprisonment',
                'expected_release' => '2046-08-18',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => null,
                'kin_relationship' => null,
                'kin_contact' => null,
                'notes' => 'No next-of-kin recorded. Refugee ID provided.',
                'photo' => null,
            ],

            // ---- 26 ----
            [
                'id' => 26,
                'prisoner_number' => 'DEMO-PN-0026',
                'first_name' => 'Violet',
                'middle_name' => null,
                'last_name' => 'Nassolo',
                'gender' => 'Female',
                'date_of_birth' => '1986-09-29',
                'nationality' => 'Ugandan',
                'district' => 'Wakiso',
                'id_type' => 'National ID',
                'id_number' => 'CF86092900SS',
                'facility' => 'Kampala Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2024-11-11',
                'admission_ref' => 'ADM-2024-0588',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-KPL-2024-0588',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 588/2024',
                'next_court_date' => '2025-10-05',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Barbara Nassolo',
                'kin_relationship' => 'Sister',
                'kin_contact' => '+256 758 545 566',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 27 ----
            [
                'id' => 27,
                'prisoner_number' => 'DEMO-PN-0027',
                'first_name' => 'Andrew',
                'middle_name' => 'Daniel',
                'last_name' => 'Wamala',
                'gender' => 'Male',
                'date_of_birth' => '1968-03-11',
                'nationality' => 'Ugandan',
                'district' => 'Mbarara',
                'id_type' => 'National ID',
                'id_number' => 'CM68031100TT',
                'facility' => 'Luzira Upper Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2015-06-25',
                'admission_ref' => 'ADM-2015-0288',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2015-0245',
                'issuing_authority' => 'High Court of Uganda, Mbarara',
                'case_ref' => 'Criminal Case No. 245/2015',
                'next_court_date' => null,
                'sentence' => '20 years imprisonment',
                'expected_release' => '2035-06-25',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Dorothy Wamala',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 701 656 677',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 28 ----
            [
                'id' => 28,
                'prisoner_number' => 'DEMO-PN-0028',
                'first_name' => 'Charity',
                'middle_name' => 'Anne',
                'last_name' => 'Ajambo',
                'gender' => 'Female',
                'date_of_birth' => '2004-01-16',
                'nationality' => 'Ugandan',
                'district' => 'Arua',
                'id_type' => 'National ID',
                'id_number' => 'CF04011600UU',
                'facility' => 'Fort Portal Prison',
                'classification' => 'Remand',
                'status' => 'Remanded',
                'admission_date' => '2025-03-01',
                'admission_ref' => 'ADM-2025-0022',
                'legal_basis' => 'Remand — Pending Trial',
                'warrant_ref' => 'CRB-FTP-2025-0022',
                'issuing_authority' => 'Grade One Magistrates Court, Arua',
                'case_ref' => 'Criminal Case No. 022/2025',
                'next_court_date' => '2025-11-20',
                'sentence' => null,
                'expected_release' => null,
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Elizabeth Ajambo',
                'kin_relationship' => 'Mother',
                'kin_contact' => '+256 712 767 788',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 29 ----
            [
                'id' => 29,
                'prisoner_number' => 'DEMO-PN-0029',
                'first_name' => 'Kenneth',
                'middle_name' => 'Patrick',
                'last_name' => 'Sebagereka',
                'gender' => 'Male',
                'date_of_birth' => '1974-11-22',
                'nationality' => 'Ugandan',
                'district' => 'Jinja',
                'id_type' => 'Driving Permit',
                'id_number' => 'DP74112200VV',
                'facility' => 'Jinja Prison',
                'classification' => 'Convicted',
                'status' => 'In Custody',
                'admission_date' => '2021-07-15',
                'admission_ref' => 'ADM-2021-0455',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2021-0400',
                'issuing_authority' => 'High Court of Uganda, Jinja',
                'case_ref' => 'Criminal Case No. 400/2021',
                'next_court_date' => null,
                'sentence' => '9 years imprisonment',
                'expected_release' => '2030-07-15',
                'actual_discharge' => null,
                'discharge_reason' => null,
                'discharge_auth_ref' => null,
                'kin_name' => 'Florence Sebagereka',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 723 878 899',
                'notes' => null,
                'photo' => null,
            ],

            // ---- 30 ---- (Presidential Pardon / discharged)
            [
                'id' => 30,
                'prisoner_number' => 'DEMO-PN-0030',
                'first_name' => 'Douglas',
                'middle_name' => null,
                'last_name' => 'Kintu',
                'gender' => 'Male',
                'date_of_birth' => '1960-07-04',
                'nationality' => 'Ugandan',
                'district' => 'Kampala',
                'id_type' => 'National ID',
                'id_number' => 'CM60070400WW',
                'facility' => 'Luzira Upper Prison',
                'classification' => 'Convicted',
                'status' => 'Released',
                'admission_date' => '2010-03-30',
                'admission_ref' => 'ADM-2010-0101',
                'legal_basis' => 'Sentenced — High Court',
                'warrant_ref' => 'HCT-00-CR-2010-0088',
                'issuing_authority' => 'High Court of Uganda, Kampala',
                'case_ref' => 'Criminal Case No. 088/2010',
                'next_court_date' => null,
                'sentence' => '15 years imprisonment',
                'expected_release' => '2025-03-30',
                'actual_discharge' => '2024-10-09',
                'discharge_reason' => 'Presidential Pardon',
                'discharge_auth_ref' => 'DCH-2024-0191',
                'kin_name' => 'Catherine Kintu',
                'kin_relationship' => 'Spouse',
                'kin_contact' => '+256 707 989 900',
                'notes' => 'Released early via Presidential Pardon.',
                'photo' => null,
            ],

        ];
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    /** Find by internal ID. Returns null if not found. */
    public static function find(int $id): ?array
    {
        foreach (static::all() as $inmate) {
            if ($inmate['id'] === $id) {
                return $inmate;
            }
        }

        return null;
    }

    /** All currently in custody. */
    public static function active(): array
    {
        return array_filter(
            static::all(),
            fn ($i) => in_array($i['status'], ['In Custody', 'Remanded'])
        );
    }

    /** All discharged / released. */
    public static function discharged(): array
    {
        return array_filter(
            static::all(),
            fn ($i) => $i['status'] === 'Released'
        );
    }

    /** Summary counts for the directory stats bar. */
    public static function summaryCounts(): array
    {
        $all = static::all();
        $active = array_filter($all, fn ($i) => in_array($i['status'], ['In Custody', 'Remanded']));
        $convicted = array_filter($active, fn ($i) => $i['classification'] === 'Convicted');
        $remand = array_filter($active, fn ($i) => $i['classification'] === 'Remand');
        $discharged = array_filter($all, fn ($i) => $i['status'] === 'Released');

        return [
            'total' => count($all),
            'active' => count($active),
            'convicted' => count($convicted),
            'remand' => count($remand),
            'discharged' => count($discharged),
        ];
    }

    /**
     * Derive computed fields — age, initials, full_name — and add them to a record.
     */
    public static function enrich(array $inmate): array
    {
        $dob = Carbon::parse($inmate['date_of_birth']);
        $inmate['age'] = $dob->age;
        $inmate['full_name'] = trim(
            $inmate['first_name'].' '.
            ($inmate['middle_name'] ? $inmate['middle_name'].' ' : '').
            $inmate['last_name']
        );
        $inmate['initials'] = strtoupper(
            substr($inmate['first_name'], 0, 1).
            substr($inmate['last_name'], 0, 1)
        );

        return $inmate;
    }

    /**
     * Fictional per-inmate admission history (supporting multiple episodes).
     * Only a subset of inmates have prior episodes for demonstration.
     */
    public static function admissionHistory(int $inmateId): array
    {
        $histories = [
            3 => [
                [
                    'admission_ref' => 'ADM-2015-0190',
                    'admission_date' => '2015-04-10',
                    'facility' => 'Gulu Prison',
                    'classification' => 'Convicted',
                    'legal_basis' => 'Sentenced — Magistrates Court',
                    'warrant_ref' => 'CRC-GLU-2015-0190',
                    'discharge_date' => '2018-04-10',
                    'discharge_reason' => 'Sentence Served',
                    'status' => 'Released',
                ],
            ],
            7 => [
                [
                    'admission_ref' => 'ADM-2016-0280',
                    'admission_date' => '2016-08-01',
                    'facility' => 'Kampala Prison',
                    'classification' => 'Remand',
                    'legal_basis' => 'Remand — Pending Trial',
                    'warrant_ref' => 'CRB-KPL-2016-0280',
                    'discharge_date' => '2017-02-14',
                    'discharge_reason' => 'Bail Granted',
                    'status' => 'Released',
                ],
            ],
            15 => [
                [
                    'admission_ref' => 'ADM-2019-0344',
                    'admission_date' => '2019-05-07',
                    'facility' => 'Fort Portal Prison',
                    'classification' => 'Convicted',
                    'legal_basis' => 'Sentenced — High Court',
                    'warrant_ref' => 'HCT-00-CR-2019-0290',
                    'discharge_date' => null,
                    'discharge_reason' => null,
                    'status' => 'Transferred to Murchison Bay on 14 Mar 2021',
                ],
            ],
            19 => [
                [
                    'admission_ref' => 'ADM-2010-0518',
                    'admission_date' => '2010-06-20',
                    'facility' => 'Kampala Prison',
                    'classification' => 'Remand',
                    'legal_basis' => 'Remand — Pending Trial',
                    'warrant_ref' => 'CRB-KPL-2010-0518',
                    'discharge_date' => '2011-01-05',
                    'discharge_reason' => 'Court Acquittal',
                    'status' => 'Released',
                ],
            ],
        ];

        $main = static::find($inmateId);
        $episodes = [];

        if ($main) {
            $episodes[] = [
                'admission_ref' => $main['admission_ref'],
                'admission_date' => $main['admission_date'],
                'facility' => $main['facility'],
                'classification' => $main['classification'],
                'legal_basis' => $main['legal_basis'],
                'warrant_ref' => $main['warrant_ref'],
                'discharge_date' => $main['actual_discharge'],
                'discharge_reason' => $main['discharge_reason'],
                'status' => $main['actual_discharge'] ? 'Released' : 'Current',
            ];
        }

        foreach (($histories[$inmateId] ?? []) as $ep) {
            $episodes[] = $ep;
        }

        // Sort descending by admission date
        usort($episodes, fn ($a, $b) => strcmp($b['admission_date'], $a['admission_date']));

        return $episodes;
    }

    /**
     * Fictional per-inmate transfer history.
     */
    public static function transferHistory(int $inmateId): array
    {
        $transfers = [
            15 => [
                [
                    'transfer_ref' => 'TRF-2021-0088',
                    'from_facility' => 'Fort Portal Prison',
                    'to_facility' => 'Murchison Bay Prison',
                    'transfer_date' => '2021-03-14',
                    'status' => 'Completed',
                    'reason' => 'Administrative — population management',
                ],
            ],
            3 => [
                [
                    'transfer_ref' => 'TRF-2024-0045',
                    'from_facility' => 'Soroti Prison',
                    'to_facility' => 'Gulu Prison',
                    'transfer_date' => '2024-02-10',
                    'status' => 'Completed',
                    'reason' => 'Security reclassification',
                ],
            ],
        ];

        return $transfers[$inmateId] ?? [];
    }

    /**
     * Fictional per-inmate visit records.
     */
    /**
     * Canonical visits for an inmate (Phase 6 — delegates to VisitDemoData).
     */
    public static function visitHistory(int $inmateId): array
    {
        return array_map([VisitDemoData::class, 'enrich'], VisitDemoData::forInmate($inmateId));
    }

    /**
     * Fictional per-inmate activity timeline.
     */
    public static function activityTimeline(int $inmateId): array
    {
        $main = static::find($inmateId);
        if (! $main) {
            return [];
        }

        $events = [
            [
                'event' => 'Record Created',
                'detail' => 'Inmate record created in the Prison Management System.',
                'timestamp' => $main['admission_date'].' 08:14:00',
                'actor' => 'Admissions Officer',
                'type' => 'system',
            ],
            [
                'event' => 'Admission Recorded',
                'detail' => 'Admitted to '.$main['facility'].'. Ref: '.$main['admission_ref'].'.',
                'timestamp' => $main['admission_date'].' 09:02:00',
                'actor' => 'Admissions Officer',
                'type' => 'admission',
            ],
            [
                'event' => 'Personal Details Updated',
                'detail' => 'Emergency contact and identification details updated.',
                'timestamp' => date('Y-m-d', strtotime($main['admission_date'].' +2 days')).' 14:30:00',
                'actor' => 'Records Clerk',
                'type' => 'edit',
            ],
        ];

        if ($main['actual_discharge']) {
            $events[] = [
                'event' => 'Discharge Recorded',
                'detail' => 'Inmate discharged. Reason: '.$main['discharge_reason'].'. Auth Ref: '.$main['discharge_auth_ref'].'.',
                'timestamp' => $main['actual_discharge'].' 10:00:00',
                'actor' => 'Discharge Processing Officer',
                'type' => 'discharge',
            ];
        }

        return $events;
    }
}
