<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom kolaborasi di tabel documents
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'is_internal')) {
                $table->boolean('is_internal')->default(false)->after('is_from_lawyer');
            }
            if (!Schema::hasColumn('documents', 'collaboration_status')) {
                $table->string('collaboration_status')->default('Draf')->after('is_internal');
            }
            if (!Schema::hasColumn('documents', 'finalized_by')) {
                $table->foreignId('finalized_by')->nullable()->after('collaboration_status')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('documents', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('finalized_by');
            }
        });

        // 2. Buat tabel document_versions
        if (!Schema::hasTable('document_versions')) {
            Schema::create('document_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
                $table->unsignedInteger('version_number')->default(1);
                $table->string('file_path');
                $table->string('original_filename');
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('revision_notes')->nullable();
                $table->string('status')->default('Draf'); // Draf, Dalam Revisi, Menunggu Persetujuan, Final
                $table->boolean('is_final')->default(false);
                $table->timestamps();

                $table->index(['document_id', 'version_number']);
            });
        }

        // 3. Buat tabel document_comments
        if (!Schema::hasTable('document_comments')) {
            Schema::create('document_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
                $table->foreignId('document_version_id')->nullable()->constrained('document_versions')->onDelete('set null');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('comment');
                $table->timestamps();
            });
        }

        // 4. Sinkronisasi dokumen eksisting yang sudah memiliki berkas fisik menjadi versi 1
        $existingDocuments = DB::table('documents')->whereNotNull('file_path')->where('file_path', '!=', '')->get();
        foreach ($existingDocuments as $doc) {
            $hasVersion = DB::table('document_versions')->where('document_id', $doc->id)->exists();
            if (!$hasVersion) {
                DB::table('document_versions')->insert([
                    'document_id'       => $doc->id,
                    'version_number'    => $doc->version ?: 1,
                    'file_path'         => $doc->file_path,
                    'original_filename' => $doc->original_filename ?: ($doc->name . '.pdf'),
                    'file_size'         => $doc->file_size,
                    'mime_type'         => $doc->mime_type,
                    'uploaded_by'       => $doc->uploaded_by ?: $doc->lawyer_id,
                    'revision_notes'    => 'Berkas awal dokumen.',
                    'status'            => ($doc->status === 'Terverifikasi' || $doc->status === 'Sudah Diterima') ? 'Final' : 'Draf',
                    'is_final'          => ($doc->status === 'Terverifikasi' || $doc->status === 'Sudah Diterima'),
                    'created_at'        => $doc->created_at ?: now(),
                    'updated_at'        => $doc->updated_at ?: now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_comments');
        Schema::dropIfExists('document_versions');

        Schema::table('documents', function (Blueprint $table) {
            $cols = ['is_internal', 'collaboration_status', 'finalized_by', 'finalized_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('documents', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
