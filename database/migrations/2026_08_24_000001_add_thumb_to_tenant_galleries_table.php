<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom "thumb" untuk menyimpan path thumbnail galeri.
     * Kolom ini opsional (nullable) — jika kosong, frontend memakai gambar asli.
     */
    public function up(): void
    {
        Schema::table('tenant_galleries', function (Blueprint $table) {
            $table->string('thumb')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_galleries', function (Blueprint $table) {
            $table->dropColumn('thumb');
        });
    }
};
