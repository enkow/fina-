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
            $table->index('start_at', 'start_at_index');
            $table->index('end_at', 'end_at_index');
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
            $table->dropIndex('start_at_index');
            $table->dropIndex('end_at_index');
        });
    }
};
