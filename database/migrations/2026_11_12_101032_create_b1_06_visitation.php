<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_ref', 64)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender', 64)->nullable();
            $table->string('id_type', 64)->nullable();
            $table->string('id_number')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('email')->nullable();
            $table->text('residence')->nullable();
            $table->string('status', 64)->default('Under Review');
            $table->dateTime('registered_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['last_name', 'first_name']);
        });
        Schema::create('visitor_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('inmate_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->string('relationship');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['visitor_id', 'inmate_id']);
        });
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_ref', 64)->unique();
            $table->foreignId('visitor_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('admission_episode_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->string('purpose')->nullable();
            $table->string('relationship')->nullable();
            $table->string('state', 64)->default('Requested');
            $table->dateTime('requested_at');
            $table->dateTime('scheduled_start_at')->nullable();
            $table->dateTime('scheduled_end_at')->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['facility_id', 'state', 'scheduled_start_at']);
            $table->index(['visitor_id', 'scheduled_start_at']);
            $table->index(['admission_episode_id', 'scheduled_start_at']);
        });
        Schema::create('visit_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_ref', 64)->unique();
            $table->foreignId('visit_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedInteger('sequence');
            $table->string('event_type', 64);
            $table->string('from_state', 64)->nullable();
            $table->string('to_state', 64)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('occurred_at');
            $table->dateTime('recorded_at');
            $table->text('reason')->nullable();
            $table->unique(['visit_id', 'sequence']);
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE visits ADD CONSTRAINT b1_visit_schedule CHECK ((scheduled_start_at IS NULL AND scheduled_end_at IS NULL) OR (scheduled_start_at IS NOT NULL AND scheduled_end_at IS NOT NULL AND scheduled_end_at > scheduled_start_at))');
            DB::statement('ALTER TABLE visits ADD CONSTRAINT b1_visit_checkout CHECK (checked_out_at IS NULL OR (checked_in_at IS NOT NULL AND checked_out_at >= checked_in_at))');
            DB::statement('ALTER TABLE visit_events ADD CONSTRAINT b1_visit_sequence CHECK (sequence > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_events');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('visitor_relationships');
        Schema::dropIfExists('visitors');
    }
};
