<?php

namespace App\Console\Commands;

use App\Custom\Timezone;
use App\Custom\Fakturownia;

use App\Models\Club;
use Illuminate\Console\Command;
use Carbon\Carbon;

use Illuminate\Support\Facades\Notification;
use App\Notifications\UnpaidInvoiceNotification;

use App\Models\Country;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Translation;
use App\Models\Reservation;

class InvoiceReset extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'invoice:reset';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		Country::whereNotNull('id')->update([
			'fakturownia_id' => null,
		]);
		Club::whereNotNull('id')->update([
			'fakturownia_id' => null,
		]);
		$fakturownia = new Fakturownia();

		$departments = $fakturownia->departments();

		foreach ($departments as $key => $department) {
			$r = $fakturownia->removeDepartments($department['id']);

			$this->line("Oddziały $key/" . count($departments));
		}

		$products = $fakturownia->products();

		foreach ($products as $key => $product) {
			$fakturownia->deleteProduct($product['id']);

			$this->line("Produkty $key/" . count($products));
		}

		$clients = $fakturownia->clients();

		foreach ($clients as $key => $client) {
			if ($client['id'] === 122522055) {
				continue;
			}

			$fakturownia->removeClient($client['id']);

			$this->line("Klienci $key/" . count($clients));
		}

		return Command::SUCCESS;
	}
}
