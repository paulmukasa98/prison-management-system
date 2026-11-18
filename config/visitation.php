<?php

return [
    'rules_confirmed' => env('VISITATION_RULES_CONFIRMED', false),
    'timezone' => env('VISITATION_TIMEZONE', 'Africa/Kampala'),
    'prevent_visitor_overlap' => true,
    'prevent_inmate_overlap' => true,
    'require_identity_verification' => true,
    'check_in_early_minutes' => 0,
    'visiting_hours' => null,
    'max_visits_per_inmate_per_day' => null,
    'approval_permission' => 'visits.authorize',
    'identity_retention_days' => null,
    'purposes' => ['Family Visit', 'Legal Consultation', 'Pastoral Visit', 'Consular Visit', 'Welfare Visit', 'Employer Visit', 'Friend Visit'],
    'id_types' => ['National ID', 'Passport', 'Driving Permit', 'Refugee ID', 'None / Not Presented'],
    'relationships' => ['Spouse', 'Parent', 'Mother', 'Father', 'Sibling', 'Brother', 'Sister', 'Child', 'Son', 'Daughter', 'Relative', 'Friend', 'Legal Representative', 'Advocate', 'Clergy', 'Consulate Officer', 'NGO Case Worker', 'Other'],
];
