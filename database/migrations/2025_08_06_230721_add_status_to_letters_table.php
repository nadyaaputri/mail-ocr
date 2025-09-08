<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan nama tabelnya 'letters'
        Schema::table('letters', function (Blueprint $table) {
            // Tambahkan kolom status, sesuaikan 'after' dengan nama kolom yang ada
            // Kode yang sudah benar
            $table->string('status')->default('Baru')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
