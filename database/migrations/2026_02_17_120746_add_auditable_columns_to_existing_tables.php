<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'users',
        'categories',
        'products',
        'sales',
        'sale_items',
        'integrations'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
                
                if (!Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                }
                
                if (!Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
                
                if (!Schema::hasColumn($table->getTable(), 'deleted_by')) {
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign([$table->getTable() . '_created_by_foreign']);
                $table->dropForeign([$table->getTable() . '_updated_by_foreign']);
                $table->dropForeign([$table->getTable() . '_deleted_by_foreign']);
                
                $table->dropColumn(['deleted_at', 'created_by', 'updated_by', 'deleted_by']);
            });
        }
    }
};
