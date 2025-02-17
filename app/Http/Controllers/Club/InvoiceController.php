<?php

namespace App\Http\Controllers\Club;

use App\Custom\Fakturownia;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
	public function index(): Response
	{
		club()->reloadFakturowniaInvoicesData();
		$invoices = club()
			->invoices()
			->whereNotNull('fakturownia_id')
			->where(function ($query) {
				$query->whereNotNull('sent_at');
				$query->orWhereNotNull('paid_at');
			})
			->orderByDesc('id')
			->paginate(10);
		$invoices = InvoiceResource::collection($invoices);
		return Inertia::render('Club/Invoices/Index', compact('invoices'));
	}

	public function export(Invoice $invoice): \Symfony\Component\HttpFoundation\StreamedResponse
	{
		return response()->stream(
			function () use ($invoice) {
				echo (new Fakturownia())->getInvoiceContent($invoice->fakturownia_id)->body();
			},
			200,
			[
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => 'attachment; filename="' . $invoice->fakturownia_id . '.pdf"',
			]
		);
	}
}
