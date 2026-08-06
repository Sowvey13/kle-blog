<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('content');
            }
        });

        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });

        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });
    }
};