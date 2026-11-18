<?php

namespace App\Support\Demo;

/**
 * VisitorDemoData - Phase 6 fictional visitor identities.
 *
 * IMPORTANT: All records are completely invented for UI demonstration.
 * No real visitor information is used. Demo visitor references use the
 * DEMO-VST- prefix; identity numbers are masked (partial + asterisks)
 * because visitor identity documents must never be exposed in full.
 *
 * A visitor identity is distinct from each visit occurrence - visits
 * live in VisitDemoData and reference these IDs.
 */
class VisitorDemoData
{
    public const ID_TYPES = [
        'National ID',
        'Passport',
        'Driving Permit',
        'Refugee ID',
        'None / Not Presented',
    ];

    public const RELATIONSHIPS = [
        'Spouse',
        'Husband',
        'Wife',
        'Mother',
        'Father',
        'Brother',
        'Sister',
        'Sibling',
        'Cousin',
        'Aunt',
        'Son',
        'Daughter',
        'Legal Representative',
        'Advocate',
        'NGO Case Worker',
        'Consulate Officer',
        'Clergy',
        'Community Elder',
        'Employer',
        'Friend',
    ];

    /** Statuses describe the visitor record itself, not individual visits. */
    public const VISITOR_STATUSES = [
        'Registered',
        'Under Review',
        'Blocked',
    ];

    // -------------------------------------------------------------------------
    // The 25-record fictional dataset
    // -------------------------------------------------------------------------

    public static function all(): array
    {
        return [
            // ---- 1 ----
            [
                'id' => 1,
                'visitor_ref' => 'DEMO-VST-0001',
                'first_name' => 'Aisha',
                'middle_name' => null,
                'last_name' => 'Nalweyiso',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'CM9**0471',
                'phone' => '+256 772 100 001',
                'email' => 'a.nalweyiso@demo.mail.ug',
                'residence' => 'Wakiso',
                'primary_relationship' => 'Spouse',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 2 ----
            [
                'id' => 2,
                'visitor_ref' => 'DEMO-VST-0002',
                'first_name' => 'Edward',
                'middle_name' => null,
                'last_name' => 'Ssekandi',
                'gender' => 'Male',
                'id_type' => 'National ID',
                'id_number_masked' => 'AQ7**8830',
                'phone' => '+256 772 100 002',
                'email' => 'e.ssekandi@demo.mail.ug',
                'residence' => 'Kampala',
                'primary_relationship' => 'Brother',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 3 ----
            [
                'id' => 3,
                'visitor_ref' => 'DEMO-VST-0003',
                'first_name' => 'Flavia',
                'middle_name' => null,
                'last_name' => 'Naigaga',
                'gender' => 'Female',
                'id_type' => 'Passport',
                'id_number_masked' => 'PN0**4512',
                'phone' => '+256 772 100 003',
                'email' => null,
                'residence' => 'Mukono',
                'primary_relationship' => 'Legal Representative',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 4 ----
            [
                'id' => 4,
                'visitor_ref' => 'DEMO-VST-0004',
                'first_name' => 'Godfrey',
                'middle_name' => null,
                'last_name' => 'Katumba',
                'gender' => 'Male',
                'id_type' => 'National ID',
                'id_number_masked' => 'C97**2210',
                'phone' => '+256 772 100 004',
                'email' => 'g.katumba@demo.mail.ug',
                'residence' => 'Jinja',
                'primary_relationship' => 'Father',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 5 ----
            [
                'id' => 5,
                'visitor_ref' => 'DEMO-VST-0005',
                'first_name' => 'Henry',
                'middle_name' => null,
                'last_name' => 'Tuli',
                'gender' => 'Male',
                'id_type' => 'None / Not Presented',
                'id_number_masked' => null,
                'phone' => '+256 772 100 005',
                'email' => null,
                'residence' => 'Kampala',
                'primary_relationship' => 'Clergy',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 6 ----
            [
                'id' => 6,
                'visitor_ref' => 'DEMO-VST-0006',
                'first_name' => 'Irene',
                'middle_name' => null,
                'last_name' => 'Namutebi',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'A01**7743',
                'phone' => '+256 772 100 006',
                'email' => 'i.namutebi@demo.mail.ug',
                'residence' => 'Wakiso',
                'primary_relationship' => 'Mother',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 7 ----
            [
                'id' => 7,
                'visitor_ref' => 'DEMO-VST-0007',
                'first_name' => 'Joseph',
                'middle_name' => null,
                'last_name' => 'Sentongo',
                'gender' => 'Male',
                'id_type' => 'Driving Permit',
                'id_number_masked' => 'DK4**9021',
                'phone' => '+256 772 100 007',
                'email' => null,
                'residence' => 'Mukono',
                'primary_relationship' => 'Employer',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 8 ----
            [
                'id' => 8,
                'visitor_ref' => 'DEMO-VST-0008',
                'first_name' => 'Kemi',
                'middle_name' => null,
                'last_name' => 'Adeleke',
                'gender' => 'Female',
                'id_type' => 'Refugee ID',
                'id_number_masked' => 'RGA0**331',
                'phone' => '+256 772 100 008',
                'email' => 'k.adeleke@demo.mail.ug',
                'residence' => 'Kampala (settlement)',
                'primary_relationship' => 'Sibling',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 9 ----
            [
                'id' => 9,
                'visitor_ref' => 'DEMO-VST-0009',
                'first_name' => 'Lawrence',
                'middle_name' => null,
                'last_name' => 'Mugisha',
                'gender' => 'Male',
                'id_type' => 'National ID',
                'id_number_masked' => 'BQ2**5560',
                'phone' => '+256 772 100 009',
                'email' => null,
                'residence' => 'Mbarara',
                'primary_relationship' => 'Legal Representative',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 10 ----
            [
                'id' => 10,
                'visitor_ref' => 'DEMO-VST-0010',
                'first_name' => 'Miriam',
                'middle_name' => null,
                'last_name' => 'Achan',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'BM8**1147',
                'phone' => '+256 772 100 010',
                'email' => 'm.achan@demo.mail.ug',
                'residence' => 'Gulu',
                'primary_relationship' => 'Spouse',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 11 ----
            [
                'id' => 11,
                'visitor_ref' => 'DEMO-VST-0011',
                'first_name' => 'Nsubuga',
                'middle_name' => null,
                'last_name' => 'Eriasafu',
                'gender' => 'Male',
                'id_type' => 'None / Not Presented',
                'id_number_masked' => null,
                'phone' => '+256 772 100 011',
                'email' => null,
                'residence' => 'Kayunga',
                'primary_relationship' => 'Friend',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 12 ----
            [
                'id' => 12,
                'visitor_ref' => 'DEMO-VST-0012',
                'first_name' => 'Olivia',
                'middle_name' => null,
                'last_name' => 'Nassanga',
                'gender' => 'Female',
                'id_type' => 'Passport',
                'id_number_masked' => 'PF3**6690',
                'phone' => '+256 772 100 012',
                'email' => 'o.nassanga@demo.mail.ug',
                'residence' => 'Kampala',
                'primary_relationship' => 'Consulate Officer',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 13 ----
            [
                'id' => 13,
                'visitor_ref' => 'DEMO-VST-0013',
                'first_name' => 'Peter',
                'middle_name' => null,
                'last_name' => 'Kasirye',
                'gender' => 'Male',
                'id_type' => 'National ID',
                'id_number_masked' => 'AN5**3382',
                'phone' => '+256 772 100 013',
                'email' => null,
                'residence' => 'Jinja',
                'primary_relationship' => 'Brother',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 14 ----
            [
                'id' => 14,
                'visitor_ref' => 'DEMO-VST-0014',
                'first_name' => 'Queen',
                'middle_name' => null,
                'last_name' => 'Mbabazi',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'AK1**9902',
                'phone' => '+256 772 100 014',
                'email' => 'q.mbabazi@demo.mail.ug',
                'residence' => 'Fort Portal',
                'primary_relationship' => 'Mother',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 15 ----
            [
                'id' => 15,
                'visitor_ref' => 'DEMO-VST-0015',
                'first_name' => 'Richard',
                'middle_name' => null,
                'last_name' => 'Otim',
                'gender' => 'Male',
                'id_type' => 'Driving Permit',
                'id_number_masked' => 'DG7**2214',
                'phone' => '+256 772 100 015',
                'email' => null,
                'residence' => 'Gulu',
                'primary_relationship' => 'Spouse',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 16 ----
            [
                'id' => 16,
                'visitor_ref' => 'DEMO-VST-0016',
                'first_name' => 'Sarah',
                'middle_name' => null,
                'last_name' => 'Lubega',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'CM3**8871',
                'phone' => '+256 772 100 016',
                'email' => 's.lubega@demo.mail.ug',
                'residence' => 'Tororo',
                'primary_relationship' => 'Aunt',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 17 ----
            [
                'id' => 17,
                'visitor_ref' => 'DEMO-VST-0017',
                'first_name' => 'Tom',
                'middle_name' => null,
                'last_name' => 'Okwi',
                'gender' => 'Male',
                'id_type' => 'Refugee ID',
                'id_number_masked' => 'RGB2**774',
                'phone' => '+256 772 100 017',
                'email' => null,
                'residence' => 'Kitgum',
                'primary_relationship' => 'Father',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 18 ----
            [
                'id' => 18,
                'visitor_ref' => 'DEMO-VST-0018',
                'first_name' => 'Uma',
                'middle_name' => null,
                'last_name' => 'Patel',
                'gender' => 'Female',
                'id_type' => 'Passport',
                'id_number_masked' => 'PA8**1105',
                'phone' => '+256 772 100 018',
                'email' => 'u.patel@demo.mail.ug',
                'residence' => 'Kampala',
                'primary_relationship' => 'Advocate',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 19 ----
            [
                'id' => 19,
                'visitor_ref' => 'DEMO-VST-0019',
                'first_name' => 'Victor',
                'middle_name' => null,
                'last_name' => 'Ojera',
                'gender' => 'Male',
                'id_type' => 'National ID',
                'id_number_masked' => 'BM6**4428',
                'phone' => '+256 772 100 019',
                'email' => null,
                'residence' => 'Lira',
                'primary_relationship' => 'Cousin',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 20 ----
            [
                'id' => 20,
                'visitor_ref' => 'DEMO-VST-0020',
                'first_name' => 'Winnie',
                'middle_name' => null,
                'last_name' => 'Becho',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'AP0**6634',
                'phone' => '+256 772 100 020',
                'email' => 'w.becho@demo.mail.ug',
                'residence' => 'Soroti',
                'primary_relationship' => 'Sister',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 21 ----
            [
                'id' => 21,
                'visitor_ref' => 'DEMO-VST-0021',
                'first_name' => 'Yusuf',
                'middle_name' => null,
                'last_name' => 'Kirya',
                'gender' => 'Male',
                'id_type' => 'None / Not Presented',
                'id_number_masked' => null,
                'phone' => '+256 772 100 021',
                'email' => null,
                'residence' => 'Mbale',
                'primary_relationship' => 'Community Elder',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 22 ----
            [
                'id' => 22,
                'visitor_ref' => 'DEMO-VST-0022',
                'first_name' => 'Zawedde',
                'middle_name' => null,
                'last_name' => 'Nabbanja',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'A90**3357',
                'phone' => '+256 772 100 022',
                'email' => 'z.nabbanja@demo.mail.ug',
                'residence' => 'Masaka',
                'primary_relationship' => 'Spouse',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 23 ----
            [
                'id' => 23,
                'visitor_ref' => 'DEMO-VST-0023',
                'first_name' => 'Andrew',
                'middle_name' => null,
                'last_name' => 'Kamau',
                'gender' => 'Male',
                'id_type' => 'Passport',
                'id_number_masked' => 'PB5**7719',
                'phone' => '+256 772 100 023',
                'email' => null,
                'residence' => 'Kampala (Kenyan resident)',
                'primary_relationship' => 'NGO Case Worker',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 24 ----
            [
                'id' => 24,
                'visitor_ref' => 'DEMO-VST-0024',
                'first_name' => 'Beatrice',
                'middle_name' => null,
                'last_name' => 'Alaso',
                'gender' => 'Female',
                'id_type' => 'National ID',
                'id_number_masked' => 'AR3**9965',
                'phone' => '+256 772 100 024',
                'email' => 'b.alaso@demo.mail.ug',
                'residence' => 'Moroto',
                'primary_relationship' => 'Mother',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
            // ---- 25 ----
            [
                'id' => 25,
                'visitor_ref' => 'DEMO-VST-0025',
                'first_name' => 'Charles',
                'middle_name' => null,
                'last_name' => 'Muhammad',
                'gender' => 'Male',
                'id_type' => 'Driving Permit',
                'id_number_masked' => 'DH8**2270',
                'phone' => '+256 772 100 025',
                'email' => null,
                'residence' => 'Iganga',
                'primary_relationship' => 'Husband',
                'status' => 'Registered',
                'registered_date' => '2026-07-01',
                'notes' => null,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public static function find(int $id): ?array
    {
        foreach (static::all() as $visitor) {
            if ($visitor['id'] === $id) {
                return $visitor;
            }
        }

        return null;
    }

    /** Derive computed display fields. */
    public static function enrich(array $visitor): array
    {
        $visits = VisitDemoData::forVisitor($visitor['id']);
        $visitor['full_name'] = trim($visitor['first_name'].' '.($visitor['middle_name'] ?? '').' '.$visitor['last_name']);
        $visitor['initials'] = mb_strtoupper(mb_substr($visitor['first_name'], 0, 1).mb_substr($visitor['last_name'], 0, 1));
        $visitor['visit_count'] = count($visits);
        $visitor['last_visit'] = $visits[0]['visit_date'] ?? null;
        $visitor['facilities_visited'] = array_values(array_unique(array_column($visits, 'facility')));

        return $visitor;
    }

    /** Summary counts for the directory page. */
    public static function summaryCounts(): array
    {
        $all = static::all();

        return [
            'total' => count($all),
            'registered' => count(array_filter($all, fn ($v) => $v['status'] === 'Registered')),
            'with_visits' => count(array_filter($all, fn ($v) => VisitDemoData::forVisitor($v['id']) !== [])),
        ];
    }
}
