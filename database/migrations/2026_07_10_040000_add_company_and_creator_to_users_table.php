<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->after('company_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        if (DB::table('users')->whereIn('role', ['admin', 'member'])->exists()) {
            $now = now();
            $companyId = DB::table('companies')->insertGetId([
                'name' => 'Default Company',
                'slug' => 'default-company',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('users')
                ->whereIn('role', ['admin', 'member'])
                ->whereNull('company_id')
                ->update(['company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
