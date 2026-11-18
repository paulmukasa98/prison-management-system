<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inmates', function (Blueprint $table) {
            $table->id();
            $table->string('identity_ref', 64)->unique();
            $table->string('prisoner_number', 64)->nullable()->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender', 64)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('district')->nullable();
            $table->string('id_type', 64)->nullable();
            $table->string('id_number')->nullable();
            $table->string('kin_name')->nullable();
            $table->string('kin_relationship')->nullable();
            $table->string('kin_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['last_name', 'first_name']);
        });
        Schema::create('admission_episodes', function (Blueprint $table) {
            $table->id();
            $table->string('admission_ref', 64)->unique();
            $table->foreignId('inmate_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('admitting_facility_id')->constrained('facilities')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('admitted_at');
            $table->dateTime('closed_at')->nullable();
            $table->string('state', 64)->default('Draft');
            $table->string('classification', 64)->nullable();
            $table->text('legal_basis')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('open_inmate_id')->nullable()->storedAs('CASE WHEN closed_at IS NULL THEN inmate_id ELSE NULL END')->unique();
            $table->timestamps();
            $table->index(['inmate_id', 'admitted_at']);
            $table->index(['admitting_facility_id', 'admitted_at']);
        });
        Schema::create('legal_references', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->foreignId('admission_episode_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->string('type', 64);
            $table->string('external_reference')->nullable()->index();
            $table->string('issuing_authority')->nullable();
            $table->date('issued_on')->nullable();
            $table->string('case_reference')->nullable()->index();
            $table->date('next_court_on')->nullable();
            $table->text('sentence_text')->nullable();
            $table->date('supplied_release_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['id', 'admission_episode_id'], 'b1_legal_episode_pair');
        });
        Schema::create('discharges', function (Blueprint $table) {
            $table->id();
            $table->string('discharge_ref', 64)->unique();
            $table->foreignId('admission_episode_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('legal_reference_id')->nullable();
            $table->string('reason')->nullable();
            $table->string('state', 64)->default('Draft');
            $table->string('authority_reference')->nullable();
            $table->dateTime('requested_at')->nullable();
            $table->dateTime('authorized_at')->nullable();
            $table->dateTime('executed_at')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('executed_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign(['legal_reference_id', 'admission_episode_id'], 'b1_discharge_legal_episode')->references(['id', 'admission_episode_id'])->on('legal_references')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['admission_episode_id', 'requested_at']);
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE admission_episodes ADD CONSTRAINT b1_admission_dates CHECK (closed_at IS NULL OR closed_at >= admitted_at)');
            DB::statement('ALTER TABLE discharges ADD CONSTRAINT b1_discharge_dates CHECK ((authorized_at IS NULL OR (requested_at IS NOT NULL AND authorized_at >= requested_at)) AND (executed_at IS NULL OR (authorized_at IS NOT NULL AND executed_at >= authorized_at)))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discharges');
        Schema::dropIfExists('legal_references');
        Schema::dropIfExists('admission_episodes');
        Schema::dropIfExists('inmates');
    }
};
