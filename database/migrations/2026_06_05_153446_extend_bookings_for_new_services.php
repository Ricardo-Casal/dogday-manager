<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the type enum to include new service types
        DB::statement("ALTER TABLE bookings MODIFY COLUMN `type` ENUM('atl','hotel','aula','integracao','pack_creche') NOT NULL");

        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_regular')->default(true)->after('type');
            $table->string('subtype', 50)->nullable()->after('is_regular');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['is_regular', 'subtype']);
        });

        DB::statement("ALTER TABLE bookings MODIFY COLUMN `type` ENUM('atl','hotel','aula') NOT NULL");
    }
};
