<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->text('location_description')->nullable();
            $table->string('status', 64)->default('Inactive');
            $table->date('established_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['name', 'status']);
        });

        Schema::create('facility_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedInteger('capacity')->nullable();
            $table->date('effective_on');
            $table->string('source_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->timestamps();
            $table->unique(['facility_id', 'effective_on']);
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE facility_capacities ADD CONSTRAINT b1_capacity_nonnegative CHECK (capacity IS NULL OR capacity >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_capacities');
        Schema::dropIfExists('facilities');
    }
};
