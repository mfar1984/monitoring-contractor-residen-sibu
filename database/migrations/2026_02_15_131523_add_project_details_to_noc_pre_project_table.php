<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('noc_pre_project', function (Blueprint $table) {
            $table->string('tahun_rtp')->nullable()->after('pre_project_id');
            $table->string('no_projek')->nullable()->after('tahun_rtp');
            $table->string('nama_projek_asal')->nullable()->after('no_projek');
            $table->string('nama_projek_baru')->nullable()->after('nama_projek_asal');
            $table->decimal('kos_asal', 15, 2)->nullable()->after('nama_projek_baru');
            $table->decimal('kos_baru', 15, 2)->nullable()->after('kos_asal');
            $table->string('agensi_pelaksana_asal')->nullable()->after('kos_baru');
            $table->string('agensi_pelaksana_baru')->nullable()->after('agensi_pelaksana_asal');
            $table->foreignId('noc_note_id')->nullable()->after('agensi_pelaksana_baru')->constrained('noc_notes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noc_pre_project', function (Blueprint $table) {
            $table->dropForeign(['noc_note_id']);
            $table->dropColumn([
                'tahun_rtp',
                'no_projek',
                'nama_projek_asal',
                'nama_projek_baru',
                'kos_asal',
                'kos_baru',
                'agensi_pelaksana_asal',
                'agensi_pelaksana_baru',
                'noc_note_id',
            ]);
        });
    }
};
