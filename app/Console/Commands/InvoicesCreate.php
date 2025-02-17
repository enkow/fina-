<?php

namespace App\Console\Commands;

use App\Jobs\CreateInvoices;
use App\Models\Club;
use Illuminate\Console\Command;

class InvoicesCreate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'invoices:create';

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
		(new CreateInvoices())->handle(Club::whereNotNull('id')->get());
		$this->info('Utworzono faktury');
	}
}
