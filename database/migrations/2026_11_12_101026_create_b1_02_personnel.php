<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            $table->string('staff_number', 64)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender', 64)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('rank')->nullable();
            $table->string('department')->nullable();
            $table->string('employment_status', 64)->default('Inactive');
            $table->date('appointed_on')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('kin_name')->nullable();
            $table->string('kin_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['last_name', 'first_name']);
            $table->index(['employment_status', 'department']);
        });

        Schema::create('personnel_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_ref', 64)->unique();
            $table->foreignId('personnel_id')->constrained('personnel')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->string('duty_title')->nullable();
            $table->text('reason')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 64)->default('Draft');
            $table->string('authority_reference')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->timestamps();
            $table->index(['personnel_id', 'start_date']);
            $table->index(['facility_id', 'status', 'start_date']);
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE personnel_assignments ADD CONSTRAINT b1_posting_dates CHECK (end_date IS NULL OR end_date >= start_date)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_assignments');
        Schema::dropIfExists('personnel');
    }
};
