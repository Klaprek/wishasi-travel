<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->ensureForeignKey('rating', 'paket_id', 'pakettour', 'id', 'rating_paket_id_fk');
        $this->ensureForeignKey('rating', 'pesanan_id', 'pesanan', 'id', 'rating_pesanan_id_fk');
        $this->ensureForeignKey('pesanan', 'paket_id', 'pakettour', 'id', 'pesanan_paket_id_fk');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropForeignIfExists('rating', 'paket_id');
        $this->dropForeignIfExists('rating', 'pesanan_id');
        $this->dropForeignIfExists('pesanan', 'paket_id');
    }

    private function ensureForeignKey(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $constraintName
    ): void {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $current = $this->getForeignKeyInfo($table, $column);
        if ($current
            && $current->REFERENCED_TABLE_NAME === $referencedTable
            && $current->REFERENCED_COLUMN_NAME === $referencedColumn
        ) {
            return;
        }

        if ($current) {
            Schema::table($table, function (Blueprint $table) use ($current) {
                $table->dropForeign($current->CONSTRAINT_NAME);
            });
        }

        Schema::table($table, function (Blueprint $table) use (
            $column,
            $referencedTable,
            $referencedColumn,
            $constraintName
        ) {
            $table
                ->foreign($column, $constraintName)
                ->references($referencedColumn)
                ->on($referencedTable)
                ->onDelete('cascade');
        });
    }

    private function dropForeignIfExists(string $table, string $column): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $current = $this->getForeignKeyInfo($table, $column);
        if (! $current) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($current) {
            $table->dropForeign($current->CONSTRAINT_NAME);
        });
    }

    private function getForeignKeyInfo(string $table, string $column): ?object
    {
        $schema = DB::getDatabaseName();

        return DB::selectOne(
            'SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1',
            [$schema, $table, $column]
        );
    }
};
