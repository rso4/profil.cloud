<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // hotel, school, sme
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // subdomain
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('template_slug')->default('hotel-01');
            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('secondary_color', 7)->default('#0f172a');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('template_slug')->references('slug')->on('templates');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role')->default('tenant_admin')->after('tenant_id'); // super_admin | tenant_admin
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role']);
        });
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('templates');
    }
};
