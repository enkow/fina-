<?php

namespace App\Custom;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;

class Fakturownia
{
	private string $token;
	private string $host;

	public function __construct()
	{
		$this->token = config('fakturownia.token');
		$this->host = config('fakturownia.url');

		Http::macro('fakturownia', function () {
			return $this->baseUrl('https://' . config('fakturownia.url'))->withUrlParameters([
				'auth' => '?api_token=' . config('fakturownia.token'),
			]);
		});
	}

	private function bodyFactory($body)
	{
		$body['api_token'] = config('fakturownia.token');

		return $body;
	}

	public function createProduct(string $name)
	{
		$body = $this->bodyFactory([
			'name' => $name,
			'service' => 1,
		]);

		return Http::fakturownia()
			->post('/products.json', $body)
			->json();
	}

	public function updateProduct(int $id, string $name)
	{
		$body = $this->bodyFactory([
			'name' => $name,
		]);

		return Http::fakturownia()
			->put("/products/$id.json", $body)
			->json();
	}

	public function deleteProduct(int $id)
	{
		return Http::fakturownia()
			->delete("/products/$id.json{+auth}")
			->json();
	}

	public function products()
	{
		return Http::fakturownia()
			->get('/products.json{+auth}')
			->json();
	}

	public function clients()
	{
		return Http::fakturownia()
			->get('/clients.json{+auth}')
			->json();
	}

	public function createClient(
		$body = [
			'name' => '',
			'tax_no' => '',
			'phone' => '',
			'email' => '',
			'country' => '',
			'post_code' => '',
			'city' => '',
			'street' => '',
			'payment_to_kind' => 14,
		]
	) {
		return Http::fakturownia()
			->post('/clients.json', $this->bodyFactory($body))
			->json();
	}

	public function updateClient(
		$id,
		$body = [
			'name' => '',
			'tax_no' => '',
			'phone' => '',
			'email' => '',
			'country' => '',
			'post_code' => '',
			'city' => '',
			'street' => '',
			'payment_to_kind' => 14,
		]
	) {
        $result = Http::fakturownia()
            ->put("/clients/$id.json", $this->bodyFactory($body))
            ->json();
		return $result;
	}

	public function removeClient($id)
	{
		return Http::fakturownia()
			->delete("/clients/$id.json")
			->json();
	}

	public function createDepartments(
		$body = [
			'name' => '',
			'shortcut' => '',
			'use_pattern' => true,
			'invoice_pattern' => 'nr-m/mm/yyyy/EN',
			'invoice_lang' => 'pl',
		]
	) {
		return Http::fakturownia()
			->post('/departments.json', $this->bodyFactory($body))
			->json();
	}

	public function departments()
	{
		return Http::fakturownia()
			->get('/departments.json{+auth}')
			->json();
	}

	public function removeDepartments($id)
	{
		return Http::fakturownia()
			->delete("/departments/$id.json{+auth}")
			->json();
	}

	public function createInvoice($body)
	{
		return Http::fakturownia()
			->post('/invoices.json', $this->bodyFactory($body))
			->json();
	}

	public function sendInvoice(int $invoiceId, array $emails)
	{
		$emailsGroup = array_slice($emails, 0, 5);
		$emailsString = implode(',', $emailsGroup);

		$body = [
			'email_to' => $emailsString,
			'email_pdf' => true,
		];

		return Http::fakturownia()
			->post("/invoices/$invoiceId/send_by_email.json", $this->bodyFactory($body))
			->json();
	}

	public function getInvoiceContent(int $invoiceId): \Illuminate\Http\Client\Response
	{
		return Http::get(
			'https://' . $this->host . '/invoices/' . $invoiceId . '.pdf?api_token=' . $this->token
		);
	}

	public function getInvoiceData(int $invoiceId): \Illuminate\Http\Client\Response
	{
		return Http::get(
			'https://' . $this->host . '/invoices/' . $invoiceId . '.json?api_token=' . $this->token
		);
	}

	public function sendInvoiceToClient(int $invoiceId): \Illuminate\Http\Client\Response
	{
		return Http::post(
			'https://' .
				$this->host .
				'/invoices/' .
				$invoiceId .
				'/send_by_email.json?api_token=' .
				$this->token .
				'&email_to=' .
				implode(',', (Invoice::where('fakturownia_id', $invoiceId)->first()->club->invoice_emails ?? []))
		);
	}
}
