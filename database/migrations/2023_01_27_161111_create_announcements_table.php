<?php

use App\Enums\AnnouncementType;
use App\Models\Club;
use App\Models\Game;
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
		Schema::create('announcements', static function (Blueprint $table) {
			$table->id();
			$table->tinyInteger('type')->default(AnnouncementType::Panel->value);

			$table->foreignIdFor(Club::class)->constrained();
			$table->foreignIdFor(Game::class)->nullable();

			$table->date('start_at')->nullable();
			$table->date('end_at')->nullable();

			$table->string('content', 4000)->nullable();
			$table->string('content_top', 4000)->nullable();
			$table->string('content_bottom', 4000)->nullable();

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
		Schema::dropIfExists('announcements');
	}
};
