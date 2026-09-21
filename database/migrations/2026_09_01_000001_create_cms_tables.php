<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel CMS (Content Management System) milik tenant:
     * - tenant_media     : media library (upload gambar terpusat per tenant)
     * - tenant_categories: kategori artikel per tenant
     * - tenant_tags      : tag artikel per tenant
     * - tenant_posts     : artikel/blog per tenant (draf/terbit/terjadwal, soft delete, SEO)
     * - tenant_post_tag  : pivot artikel <-> tag
     */
    public function up(): void
    {
        Schema::create('tenant_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('thumb_path')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('tenant_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('tenant_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('tenant_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('tenant_categories')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('tenant_media')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status', 20)->default('draft'); // draft | published | scheduled
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status']);
            $table->index('published_at');
        });

        Schema::create('tenant_post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('tenant_posts')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tenant_tags')->cascadeOnDelete();
            $table->primary(['post_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_post_tag');
        Schema::dropIfExists('tenant_posts');
        Schema::dropIfExists('tenant_tags');
        Schema::dropIfExists('tenant_categories');
        Schema::dropIfExists('tenant_media');
    }
};
