<?php

namespace App\Console\Commands;

use App\Models\AuditEvent;
use App\Services\AuditService;
use Illuminate\Console\Command;

class VerifyAuditIntegrity extends Command
{
    protected $signature = 'audit:verify {--allow-legacy : Report unsigned historical events without failing}';

    protected $description = 'Verify signatures of audit events without modifying history';

    public function handle(): int
    {
        $verified = 0;
        $legacy = 0;
        $invalid = 0;
        foreach (AuditEvent::orderBy('id')->lazyById(250) as $event) {
            if ($event->integrity_hash === null) {
                $legacy++;
            } elseif (! hash_equals($event->integrity_hash, AuditService::signatureFor($event))) {
                $invalid++;
                $this->error('Invalid audit signature: '.$event->audit_ref);
            } else {
                $verified++;
            }
        }
        $this->info("Verified: {$verified}; unsigned historical events: {$legacy}; invalid signatures: {$invalid}.");
        if ($legacy > 0) {
            $this->warn('Historical events predate signatures. Database append-only controls apply; their original content cannot be cryptographically verified.');
        }

        return $invalid > 0 || ($legacy > 0 && ! $this->option('allow-legacy')) ? self::FAILURE : self::SUCCESS;
    }
}
