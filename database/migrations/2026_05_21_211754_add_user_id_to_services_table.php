<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
        });

        DB::statement(
            'UPDATE services
             SET user_id = (
                SELECT clients.user_id
                FROM contracts
                INNER JOIN clients ON clients.id = contracts.client_id
                WHERE contracts.service_id = services.id
                ORDER BY contracts.id
                LIMIT 1
             )
             WHERE user_id IS NULL
               AND EXISTS (
                SELECT 1
                FROM contracts
                INNER JOIN clients ON clients.id = contracts.client_id
                WHERE contracts.service_id = services.id
             )'
        );
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
