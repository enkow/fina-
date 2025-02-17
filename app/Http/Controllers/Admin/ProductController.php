<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;

use App\Http\Resources\ProductResource;

use App\Models\Product;

use App\Custom\Fakturownia;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
	public function index(): Response
	{
		$products = ProductResource::collection(Product::paginate(50));

		return Inertia::render('Admin/Products/Index', compact('products'));
	}

	public function create(): Response
	{
		return Inertia::render('Admin/Products/Create');
	}

	public function edit(Product $product): Response
	{
		return Inertia::render('Admin/Products/Edit', [
			'product' => $product,
		]);
	}

	public function update(UpdateProductRequest $request, Product $product): RedirectResponse
	{
		$fakturownia = new Fakturownia();

		$fakturownia->updateProduct($product->fakturownia_id_pl, $request->input('name_pl'));
		$fakturownia->updateProduct($product->fakturownia_id_en, $request->input('name_en'));

		$product->update($request->only(['name_pl', 'name_en']));

		return redirect()
			->route('admin.products.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano produkt',
			]);
	}

	public function store(StoreProductRequest $request): RedirectResponse
	{
		$fakturownia = new Fakturownia();

		$productData = $request->only(['name_pl', 'name_en']);

		$productData['fakturownia_id_pl'] = $fakturownia->createProduct($request->input('name_pl'))['id'];
		$productData['fakturownia_id_en'] = $fakturownia->createProduct($request->input('name_en'))['id'];

		Product::create($productData);

		return redirect()
			->route('admin.products.index')
			->with('message', [
				'type' => 'success',
				'content' => 'Dodano nowy produkt',
			]);
	}

	public function destroy(Product $product): RedirectResponse
	{
		if (count($product->clubs)) {
			return redirect()
				->route('admin.products.index')
				->with('message', [
					'type' => 'error',
					'content' => 'Produkt jest przypisany do klubu',
				]);
		}

		$fakturownia = new Fakturownia();

		$fakturownia->deleteProduct($product->fakturownia_id_pl);
		$fakturownia->deleteProduct($product->fakturownia_id_en);

		$product->update([
			'fakturownia_id_pl' => 0,
			'fakturownia_id_en' => 0,
		]);

		$product->delete();

		return redirect()
			->route('admin.products.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Usunięto produkt',
			]);
	}
}
