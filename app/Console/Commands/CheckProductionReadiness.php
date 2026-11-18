<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckProductionReadiness extends Command
{
    protected $signature = 'pms:readiness';

    protected $description = 'Check production configuration and approval references without exposing secrets';

    public function handle(): int
    {
        $key = base64_decode((string) config('operations.backup_key'), true);
        $checks = [
            'Production approval recorded' => (bool) config('operations.production_approved'),
            'Security review reference recorded' => filled(config('operations.security_review_reference')),
            'User acceptance reference recorded' => filled(config('operations.uat_reference')),
            'Restore drill reference recorded' => filled(config('operations.restore_drill_reference')),
            'Debug disabled' => ! config('app.debug'),
            'HTTPS application URL' => str_starts_with(config('app.url'), 'https://'),
            'Secure session cookies' => (bool) config('session.secure'),
            'HTTP-only session cookies' => (bool) config('session.http_only'),
            'Session encryption enabled' => (bool) config('session.encrypt'),
            'Persistent production database' => in_array(config('database.default'), ['mysql', 'mariadb'], true),
            'Asynchronous queue configured' => config('queue.default') !== 'sync',
            'Delivery mailer configured' => ! in_array(config('mail.default'), ['log', 'array'], true),
            'Visitation rules confirmed' => (bool) config('visitation.rules_confirmed'),
            'Offline custody writes disabled' => ! config('operations.offline_writes_enabled'),
            'Independent backup key configured' => is_string($key) && strlen($key) === 32,
            'Built asset manifest present' => is_file(public_path('build/manifest.json')),
        ];
        foreach ($checks as $label => $passed) {
            $this->line(($passed ? 'PASS ' : 'FAIL ').$label);
        }
        $this->line('This check does not replace infrastructure review, penetration testing or institutional signoff.');

        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }
}
