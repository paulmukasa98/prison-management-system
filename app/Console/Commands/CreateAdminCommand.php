<?php

namespace App\Console\Commands;

use App\Models\AccessScope;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ups:admin-create
                            {--name= : Full name of the administrator}
                            {--email= : Email address of the administrator}
                            {--password= : Password for the administrator account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap a secure System Administrator account with national scope';

    /**
     * Execute the console command.
     */
    public function handle(AuditService $auditService): int
    {
        $name = $this->option('name') ?: $this->ask('Administrator Full Name', 'System Administrator');
        $email = $this->option('email') ?: $this->ask('Administrator Email (e.g. admin@ups.go.ug)');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address format.');

            return self::FAILURE;
        }

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Enter Password (minimum 8 characters)');
            $confirmPassword = $this->secret('Confirm Password');

            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match.');

                return self::FAILURE;
            }
        }

        if (strlen((string) $password) < 8) {
            $this->error('Password must be at least 8 characters long.');

            return self::FAILURE;
        }

        $adminRole = Role::firstOrCreate(
            ['slug' => 'system_admin'],
            ['name' => 'System Administrator', 'description' => 'Full administrative access across Uganda Prisons Service.']
        );

        $nationalScope = AccessScope::firstOrCreate(
            ['kind' => 'National', 'facility_id' => null],
            ['scope_key' => 'SCOPE-NATIONAL']
        );

        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;

        $user->name = $name;
        $user->password = Hash::make($password);
        $user->status = 'Active';
        if ($isNew) {
            $user->account_ref = 'ADM-'.date('Y').'-'.strtoupper(Str::random(6));
        }
        $user->save();

        // Assign national system_admin role
        $existingAssignment = $user->activeRoleAssignments()
            ->where('role_id', $adminRole->id)
            ->where('access_scope_id', $nationalScope->id)
            ->first();

        if (! $existingAssignment) {
            UserRoleAssignment::create([
                'assignment_ref' => 'URA-ADM-'.strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'role_id' => $adminRole->id,
                'access_scope_id' => $nationalScope->id,
                'granted_by' => $user->id,
                'granted_at' => now(),
            ]);
        }

        $auditService->log(
            action: 'bootstrap_admin_created',
            outcome: 'Success',
            resourceType: 'User',
            resourceReference: $user->email,
            changes: [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => 'system_admin',
                'scope' => 'National',
                'is_new_account' => $isNew,
            ],
            description: 'System Administrator bootstrapped via CLI command ups:admin-create.',
            actorId: $user->id
        );

        $this->info("Administrator account [{$user->email}] successfully ".($isNew ? 'created' : 'updated').'.');

        return self::SUCCESS;
    }
}
