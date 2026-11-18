<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_ref', 64)->unique();
            $table->foreignId('admission_episode_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('origin_facility_id')->constrained('facilities')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('destination_facility_id')->constrained('facilities')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('requesting_facility_id')->constrained('facilities')->restrictOnDelete()->restrictOnUpdate();
            $table->string('state', 64)->default('Draft');
            $table->string('priority', 64)->default('Routine');
            $table->text('reason')->nullable();
            $table->dateTime('requested_at');
            $table->date('proposed_on')->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['id', 'admission_episode_id'], 'b1_transfer_episode_pair');
            $table->index(['origin_facility_id', 'state', 'requested_at']);
            $table->index(['destination_facility_id', 'state', 'requested_at']);
            $table->index(['admission_episode_id', 'requested_at']);
        });
        Schema::create('transfer_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_ref', 64)->unique();
            $table->foreignId('transfer_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedInteger('sequence');
            $table->string('event_type', 64);
            $table->string('from_state', 64)->nullable();
            $table->string('to_state', 64)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('facility_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('occurred_at');
            $table->dateTime('recorded_at');
            $table->string('authority_reference')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['transfer_id', 'sequence']);
        });
        Schema::create('custody_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_ref', 64)->unique();
            $table->foreignId('admission_episode_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('entered_at');
            $table->dateTime('left_at')->nullable();
            $table->unsignedBigInteger('entered_transfer_id')->nullable();
            $table->unsignedBigInteger('left_transfer_id')->nullable();
            $table->unsignedBigInteger('open_admission_id')->nullable()->storedAs('CASE WHEN left_at IS NULL THEN admission_episode_id ELSE NULL END')->unique();
            $table->timestamps();
            $table->foreign(['entered_transfer_id', 'admission_episode_id'], 'b1_location_enter_transfer')->references(['id', 'admission_episode_id'])->on('transfers')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['left_transfer_id', 'admission_episode_id'], 'b1_location_leave_transfer')->references(['id', 'admission_episode_id'])->on('transfers')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['admission_episode_id', 'entered_at']);
            $table->index(['facility_id', 'entered_at', 'left_at']);
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE transfers ADD CONSTRAINT b1_transfer_facilities CHECK (origin_facility_id <> destination_facility_id)');
            DB::statement('ALTER TABLE transfers ADD CONSTRAINT b1_transfer_dates CHECK ((dispatched_at IS NULL OR dispatched_at >= requested_at) AND (received_at IS NULL OR (dispatched_at IS NOT NULL AND received_at >= dispatched_at)))');
            DB::statement('ALTER TABLE transfer_events ADD CONSTRAINT b1_transfer_sequence CHECK (sequence > 0)');
            DB::statement('ALTER TABLE custody_locations ADD CONSTRAINT b1_location_dates CHECK (left_at IS NULL OR left_at >= entered_at)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('custody_locations');
        Schema::dropIfExists('transfer_events');
        Schema::dropIfExists('transfers');
    }
};
