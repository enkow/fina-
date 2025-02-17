<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Enums\RefundStatus;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('refunds', function (Blueprint $table) {
			$table->id();
			$table->tinyInteger('status')->default(\App\Enums\RefundStatus::Pending->value);
			$table->morphs('refundable');
			$table->foreignIdFor(User::class, 'approver_id')->nullable();
			$table->timestamp('approved_at')->nullable();
			$table->integer('price');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('refunds');
	}
};
