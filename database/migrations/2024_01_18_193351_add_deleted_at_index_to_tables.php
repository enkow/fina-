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
        Schema::table('pricelists', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('reservation_slots', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('features', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('slots', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('sets', function (Blueprint $table) {
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pricelists', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('reservation_slots', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('features', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('slots', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('sets', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
    }
};
