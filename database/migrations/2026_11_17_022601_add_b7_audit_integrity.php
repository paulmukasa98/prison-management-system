<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->char('integrity_hash', 64)->nullable();
        });
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            foreach (['audit_events', 'visit_events', 'transfer_events'] as $table) {
                foreach (['UPDATE', 'DELETE'] as $operation) {
                    $trigger = 'b7_'.$table.'_no_'.strtolower($operation);
                    DB::unprepared("CREATE TRIGGER {$trigger} BEFORE {$operation} ON {$table} FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Recorded events are append-only.'");
                }
            }
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            foreach (['audit_events', 'visit_events', 'transfer_events'] as $table) {
                foreach (['update', 'delete'] as $operation) {
                    DB::unprepared("DROP TRIGGER IF EXISTS b7_{$table}_no_{$operation}");
                }
            }
        }
        Schema::table('audit_events', fn (Blueprint $table) => $table->dropColumn('integrity_hash'));
    }
};
