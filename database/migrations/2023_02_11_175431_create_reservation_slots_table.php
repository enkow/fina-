<?php

use App\Enums\ReservationSlotStatus;
use App\Models\Reservation;
use App\Models\ReservationType;
use App\Models\Settlement;
use App\Models\Slot;
use App\Models\User;
use App\Models\DiscountCode;
use App\Models\SpecialOffer;
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
		Schema::create('reservation_slots', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Reservation::class);
			$table->foreignIdFor(Slot::class);
			$table->boolean('slot_occupied')->default(true);

			$table->boolean('club_reservation')->default(false);

			$table->foreignIdFor(DiscountCode::class)->nullable();
			$table->foreignIdFor(SpecialOffer::class)->nullable();

			$table->boolean('presence')->default(true);

			$table->tinyInteger('status')->default(ReservationSlotStatus::Pending->value);

			$table->timestamp('start_at');
			$table->timestamp('end_at')->nullable();

			$table->boolean('timer_enabled')->default(false);

			$table->foreignIdFor(ReservationType::class)->nullable();
			$table->foreignIdFor(Settlement::class)->nullable();

			$table->integer('cancelation_type')->nullable();
			$table->text('cancelation_reason')->nullable();
			$table->foreignIdFor(User::class, 'canceler_id')->nullable();
			$table->timestamp('canceled_at')->nullable();

			$table->integer('price')->default(0);
			$table->integer('final_price')->default(0);
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
		Schema::dropIfExists('reservation_slot');
	}
};
