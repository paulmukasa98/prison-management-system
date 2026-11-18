<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PruneUserAccounts extends Command
{
    protected $signature = 'ups:users-prune {--keep= : Email of the national administrator to retain} {--force : Apply the cleanup instead of showing a preview}';

    protected $description = 'Archive other local login accounts and revoke access while retaining operational and audit history';

    public function handle(AuditService $auditService): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Account cleanup is limited to local and testing environments.');

            return self::FAILURE;
        }
        $email = trim((string) $this->option('keep'));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('Supply the email of the administrator to retain with --keep.');

            return self::FAILURE;
        }

        try {
            return DB::transaction(function () use ($email, $auditService): int {
                $accounts = User::orderBy('id')->lockForUpdate()->get();
                $keeper = $accounts->first(fn (User $account): bool => strcasecmp($account->email, $email) === 0);
                if (! $keeper || ! $keeper->isActive() || ! $keeper->activeRoleAssignments()
                    ->whereHas('role', fn ($query) => $query->where('slug', 'system_admin'))
                    ->whereHas('scope', fn ($query) => $query->where('kind', 'National'))->exists()) {
                    throw new InvalidArgumentException('The retained account must exist and be an active national System Administrator.');
                }
                $targets = $accounts->where('id', '!=', $keeper->id);
                $count = $targets->count();
                if (! $this->option('force')) {
                    $this->info("Preview: archive {$count} other accounts; retain {$keeper->email}. Use --force to apply.");

                    return self::SUCCESS;
                }
                if ($count === 0) {
                    $this->info("No other accounts to archive. Retained {$keeper->email}.");

                    return self::SUCCESS;
                }
                $ids = $targets->modelKeys();
                UserRoleAssignment::whereIn('user_id', $ids)->whereNull('ended_at')->update(['ended_at' => now()]);
                DB::table('sessions')->whereIn('user_id', $ids)->delete();
                DB::table('password_reset_tokens')->whereIn('email', $targets->pluck('email'))->delete();
                User::whereKey($ids)->update([
                    'status' => 'Disabled', 'remember_token' => null, 'password' => Hash::make(Str::random(64)),
                ]);
                User::whereKey($ids)->delete();
                $auditService->log(
                    action: 'accounts_archived', outcome: 'Success', resourceType: 'AccountMaintenance',
                    resourceReference: $keeper->account_ref,
                    changes: ['retained_user_id' => $keeper->id, 'archived_count' => $count, 'archived_user_ids' => $ids],
                    description: 'Local CLI account cleanup; historical actor records retained and login access revoked.'
                );
                $this->info("Archived {$count} other accounts. Retained {$keeper->email}.");

                return self::SUCCESS;
            }, 3);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
