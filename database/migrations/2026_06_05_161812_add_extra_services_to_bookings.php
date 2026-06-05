<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY COLUMN `type` ENUM('atl','hotel','aula','integracao','pack_creche','pet_sitting','dog_walking','banho') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY COLUMN `type` ENUM('atl','hotel','aula','integracao','pack_creche') NOT NULL");
    }
};
