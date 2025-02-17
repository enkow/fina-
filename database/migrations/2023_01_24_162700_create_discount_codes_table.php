<?php

use App\Enums\DiscountCodeType;
use App\Models\Club;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('discount_codes', function (Blueprint $table) {
			$table->id();
			$table->boolean('active')->default(true);
			$table->foreignIdFor(Club::class)->constrained();
			$table->foreignIdFor(Game::class)->constrained();
			$table->tinyInteger('type')->default(DiscountCodeType::Percent->value);
			$table->string('code', 50);
			$table->integer('value');
			$table->integer('code_quantity')->nullable();
			$table->integer('code_quantity_per_customer')->nullable();
			$table->timestamp('start_at')->nullable();
			$table->timestamp('end_at')->nullable();
			$table->foreignIdFor(User::class, 'creator_id')->nullable();
			$table->softDeletes();
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
		Schema::dropIfExists('discount_codes');
	}
};
