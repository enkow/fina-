<?php

use App\Models\Customer;
use App\Models\Game;
use App\Models\PaymentMethod;
use App\Enums\ReservationSource;
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
		Schema::create('reservations', function (Blueprint $table) {
			$table->id();

			$table->tinyInteger('source')->default(ReservationSource::Panel->value);
			$table->foreignIdFor(Game::class)->constrained();

			$table->string('customer_note', 4000)->nullable();
			$table->string('club_note', 4000)->nullable();

			$table->foreignIdFor(Customer::class)->nullable();
			$table->json('unregistered_customer_data')->nullable();

			$table->tinyInteger('return_status')->nullable();
			$table->foreignIdFor(User::class, 'returner_id')->nullable();

			$table->tinyInteger('rate_service')->nullable();
			$table->tinyInteger('rate_atmosphere')->nullable();
			$table->tinyInteger('rate_staff')->nullable();
			$table->tinyInteger('rate_final')->nullable();
			$table->string('rate_content', 1000)->nullable();

			$table->integer('commission')->default(0);
			$table->integer('price')->default(0);
			$table->string('currency');
			$table->timestamp('paid_at')->nullable();
			$table
				->foreignIdFor(PaymentMethod::class)
				->nullable()
				->constrained();
			$table->string('payment_token', 255)->nullable();
			$table->string('customer_ip')->nullable();

			$table->timestamp('expired_at')->nullable();
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
		Schema::dropIfExists('reservations');
	}
};
