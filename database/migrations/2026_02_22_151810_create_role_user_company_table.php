<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_user_company', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'user_id', 'company_id']);
        });

        // Migrate existing roles from company_user to role_user_company
        $existing = DB::table('company_user')->whereNotNull('role_id')->get();
        foreach ($existing as $row) {
            DB::table('role_user_company')->insert([
                'role_id' => $row->role_id,
                'user_id' => $row->user_id,
                'company_id' => $row->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user_company');
    }
};
