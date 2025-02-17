<?php

use App\Models\Club;
use App\Models\Country;
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
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('type')->default('employee');
			$table->string('first_name', 100);
			$table->string('last_name', 100);
			$table->string('email', 150)->unique();
			$table->timestamp('email_verified_at')->nullable();
			$table->string('password')->nullable();
			$table->foreignIdFor(Club::class)->nullable();
			$table->foreignIdFor(Country::class)->nullable();
			$table->boolean('sidebar_reduced')->default(false);
			$table->rememberToken();
			$table->timestamp('last_login')->nullable();
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
		Schema::dropIfExists('users');
	}
};
