<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AgreementType;
use App\Enums\AgreementContentType;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('agreements', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(\App\Models\Club::class);
			$table->boolean('active')->default(true);
			$table->tinyInteger('type')->default(AgreementType::GeneralTerms->value);
			$table->tinyInteger('content_type')->default(AgreementContentType::Text->value);
			$table->text('text')->nullable();
			$table->string('file')->nullable();
			$table->boolean('required')->default(false);
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
		Schema::dropIfExists('agreements');
	}
};
