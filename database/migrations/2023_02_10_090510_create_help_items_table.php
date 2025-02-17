<?php

use App\Models\HelpSection;
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
		Schema::create('help_items', function (Blueprint $table) {
			$table->id();
			$table->boolean('active')->default(true);
			$table->foreignIdFor(HelpSection::class)->constrained();
			$table->string('video_url', 1000)->nullable();
			$table->string('thumbnail', 255)->nullable();
			$table->string('title')->nullable();
			$table->string('description', 100);
			$table->text('content');
			$table->integer('weight')->default(1);
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
		Schema::dropIfExists('help_items');
	}
};
