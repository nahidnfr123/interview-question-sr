<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|\Illuminate\Http\Response|View
     */
    public function index()
    {
        $products = Product::paginate(2);
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Http\Response|View
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductStoreRequest $request
     * @return RedirectResponse
     */
    public function store(ProductStoreRequest $request): RedirectResponse
    {
        // Insert product ...
        $product = new Product();
        $product->fill($request->only('title', 'sku', 'description'));
        $product->save();

        // Insert Variants
        $productVariants = $product->productVariants();
        if (count($request->product_variant)) {
            $productVariantsData = [];
            foreach ($request->product_variant as $variant) {
                if ($variant['option'] && count($variant['tags'])) {
                    foreach ($variant['tags'] as $tags) {
                        $productVariantsData[] = [
                            'product_id' => $product->id,
                            'variant' => $tags,
                            'variant_id' => $variant['option'],
                        ];
                    }
                }
            }
            $productVariants->insert($productVariantsData);
        }

        /// Left to do .... this code needs to be sent in observer ...
        if (count($request->product_variant_prices) && count($productVariants)) {
            $productVariantsPricesData = [];
            foreach ($request->product_variant_prices as $variant_prices) {
                $productVariantsPricesData[] = [
                    'price' => $variant_prices['price'],
                    'stock' => $variant_prices['stock'],
                    'product_id' => $product->id,
                    'title' => $variant_prices['title'],
                    'product_variant_one' => $variant_prices['title'],
                    'product_variant_two' => $variant_prices['title'],
                    'product_variant_three' => $variant_prices['title'],
                ];
            }
            $productVariants->productVariantPrices()->insert($productVariantsPricesData);
        }


        return redirect()->back()->with('success', 'Product Saved');
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return Application|Factory|\Illuminate\Http\Response|View
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
