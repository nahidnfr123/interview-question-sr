<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
//        $productVariants = ProductVariant::select('variant')->distinct('variant')->get();
//        return response()->json($productVariants);

        $variants = Variant::with(['productVariants' => function ($query) {
            return $query->select('variant')->groupBy('variant');
        }])->orderBy('title')
            ->distinct()
            ->get()
            ->groupBy('title');

        /*$variants = Variant::with(['productVariants' => function ($query) {
//            $query->select(['variant'])->groupBy('variant');
        }])->get()->groupBy('title');*/

        /* $variants = DB::table('variants')
             ->join('product_variants', 'variants.id', '=', 'product_variants.variant_id')
             ->select('title', 'product_variants.variant')
             ->groupBy('product_variants.variant')
             ->groupBy('title')
             ->get();*/
//        return response()->json($variants);
//        dd($variants);

        $products = Product::paginate(2);
        return view('products.index', compact('products', 'variants'));
    }

    public function search()
    {
        $variants = Variant::with('productVariants')->get()->groupBy('title');
        $productsSearch = Product::query();
        $title = \request('title');
        $variant = \request('variant');
        $price_from = \request('price_from');
        $price_to = \request('price_to');
        $date = \request('date');

        if ($title) {
            $productsSearch->where('title', 'LIKE', '%' . $title . '%');
        }
        if ($variant) {

        }
        if ($price_from && $price_to) {
            $productsSearch->whereHas('productVariantPrices', function ($q) use ($price_from, $price_to) {
                $q->whereBetween('price', [(int)$price_from, (int)$price_to])->get();
            });
        }
        if ($date) {
            $productsSearch->whereDate('created_at', date('Y-m-d', strtotime($date)));
        }
//        return [$price_from, number_format($price_to)];

        $products = $productsSearch->paginate(2);
//        return $products;
        return view('products.index', compact('products', 'variants'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Http\Response|View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductStoreRequest $request
     * @return RedirectResponse
     */
    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $product = new Product();
        $product->fill($request->only('title', 'sku', 'description'));
        $product->save();

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
            $productVariants = $product->productVariants()->createMany($productVariantsData);
            dd($productVariants);
        }
        /// Left to do .... this code needs to be sent in observer ...
        if (count($request->product_variant_prices) && count($product->productVariants())) {
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
