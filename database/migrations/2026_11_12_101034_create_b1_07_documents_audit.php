<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_ref', 64)->unique();
            $table->string('disk', 64)->default('local');
            $table->string('storage_key')->nullable()->unique();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->char('sha256', 64)->nullable();
            $table->string('state', 64)->default('Metadata Only');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('retain_until')->nullable();
            $table->timestamps();
        });
        Schema::create('document_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('inmate_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('admission_episode_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('legal_reference_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('discharge_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('transfer_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('personnel_id')->nullable()->constrained('personnel')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('visitor_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('visit_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->string('purpose')->nullable();
            $table->timestamps();
            $table->unique(['document_id', 'inmate_id'], 'b1_document_inmate');
            $table->unique(['document_id', 'admission_episode_id'], 'b1_document_admission');
            $table->unique(['document_id', 'legal_reference_id'], 'b1_document_legal');
            $table->unique(['document_id', 'discharge_id'], 'b1_document_discharge');
            $table->unique(['document_id', 'transfer_id'], 'b1_document_transfer');
            $table->unique(['document_id', 'personnel_id'], 'b1_document_personnel');
            $table->unique(['document_id', 'visitor_id'], 'b1_document_visitor');
            $table->unique(['document_id', 'visit_id'], 'b1_document_visit');
        });
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->string('audit_ref', 64)->unique();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('facility_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('occurred_at');
            $table->dateTime('recorded_at');
            $table->string('action', 128);
            $table->string('outcome', 64);
            $table->string('resource_type', 64);
            $table->string('resource_reference')->nullable();
            $table->string('correlation_id', 64);
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->index(['actor_id', 'occurred_at']);
            $table->index(['facility_id', 'occurred_at']);
            $table->index(['resource_type', 'resource_reference']);
            $table->index('correlation_id');
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE document_links ADD CONSTRAINT b1_document_target CHECK ((inmate_id IS NOT NULL) + (admission_episode_id IS NOT NULL) + (legal_reference_id IS NOT NULL) + (discharge_id IS NOT NULL) + (transfer_id IS NOT NULL) + (personnel_id IS NOT NULL) + (visitor_id IS NOT NULL) + (visit_id IS NOT NULL) = 1)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('document_links');
        Schema::dropIfExists('documents');
    }
};
