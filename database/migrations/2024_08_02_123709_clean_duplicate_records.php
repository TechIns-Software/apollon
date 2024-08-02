<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = <<<SQL
DELETE FROM delivery_order WHERE id IN (
  SELECT id FROM (
    SELECT
      id,
      delivery_id,
      order_id,
      delivery_sequence,
      ROW_NUMBER() OVER (PARTITION BY delivery_id, order_id ORDER BY delivery_sequence DESC) AS rn
    FROM
      delivery_order
  ) AS RankedDuplicates WHERE rn > 1);
SQL;

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
