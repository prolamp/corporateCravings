<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('show_on_website')
                ->default(true)
                ->after('status');

            $table->index(['status', 'show_on_website', 'sort_order'], 'packages_public_listing_index');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('packages_public_listing_index');
            $table->dropColumn('show_on_website');
        });
    }
};
