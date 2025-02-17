<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_slots', function (Blueprint $table) {
            $table->index(['reservation_id', 'slot_id'], 'reservation_slot_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_slots', function (Blueprint $table) {
            $table->dropIndex('reservation_slot_index');
        });
    }
};
