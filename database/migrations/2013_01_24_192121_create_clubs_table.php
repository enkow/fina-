<?php

use App\Enums\OnlinePayments;
use App\Models\Country;
use App\Models\PaymentMethod;
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
		Schema::create('clubs', function (Blueprint $table) {
			$table->id();

			$table->foreignIdFor(Country::class)->constrained();

			$table->string('name', 100);
			$table->string('slug', 100);
			$table->text('description')->nullable();
			$table->string('email', 100)->nullable();

			$table->string('address', 100)->nullable();
			$table->string('postal_code', 20)->nullable();
			$table->string('city', 100)->nullable();

			$table->string('phone_number', 30)->nullable();
			$table->string('vat_number', 100)->nullable();
			$table->json('invoice_emails')->nullable();

			$table->text('first_login_message')->nullable();
			$table->boolean('first_login_message_showed')->default(false);

			$table->integer('monthly_subscription_amount')->default(0);
			$table->integer('yearly_subscription_amount')->default(0);

			$table->boolean('panel_enabled')->default(true);
			$table->boolean('widget_enabled')->default(true);
			$table->boolean('calendar_enabled')->default(true);
			$table->boolean('sets_enabled')->default(true);
			$table->boolean('aggregator_enabled')->default(false);
			$table->boolean('customer_registration_required')->default(true);

			$table->boolean('offline_payments_enabled')->default(true);
			$table->string('online_payments_enabled')->default(OnlinePayments::Disabled->value);

			$table->integer('additional_commission_percent')->default(0);
			$table->integer('additional_commission_fixed')->default(0);

			$table->string('stripe_customer_id')->nullable();

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
		Schema::dropIfExists('clubs');
	}
};
