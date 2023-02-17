<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        // Get Variants with Product Variants
        $variants = $this->getVariants();

        // Get Products Paginated by 2 products per page
        $products = Product::paginate(2);
        return view('products.index', compact('products', 'variants'));
    }


    public function search()
    {
        $variants = $this->getVariants();
        $products = Product::query();

        $title = \request('title');
        $variant = \request('variant');
        $price_from = \request('price_from');
        $price_to = \request('price_to');
        $date = \request('date');

        if ($title) {
            $products->where('title', 'LIKE', '%' . $title . '%');
        }
        if ($variant) {
            $products->whereHas('productVariants', function ($q) use ($variant) {
                $q->where('variant', $variant);
            });
        }
        if ($price_from) {
            $products->whereHas('productVariantPrices', function ($q) use ($price_from) {
                $q->where('price', '<=', $price_from);
            });
        }
        if ($price_to) {
            $products->whereHas('productVariantPrices', function ($q) use ($price_to) {
                $q->where('price', '>=', $price_to);
            });
        }
        if ($date) {
            $products->whereDate('created_at', date('Y-m-d', strtotime($date)));
        }

        $products = $products->paginate(2);
        return view('products.index', compact('products', 'variants'));
    }


    public function getVariants()
    {
        return Variant::with(['productVariants' => function ($query) {
            $query->select('variant_id', 'variant')->distinct('variant')->get();
        }])->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
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
     * @return JsonResponse
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Create Product Data ...
            $product = Product::create($request->only('title', 'sku', 'description'));

            $created_at = Carbon::now();
            if ($request->product_images && count($request->product_images)) {
                $productImagesData = [];
                foreach ($request->product_images as $image) {
                    $productImagesData[] = [
                        'product_id' => $product->id,
                        'file_path' => $image,
                        'created_at' => $created_at
                    ];
                }
                $product->productImages()->insert($productImagesData);
            }

            if ($request->product_variants && count($request->product_variants)) {
                $productVariantsData = [];
                foreach ($request->product_variants as $variant) {
                    if ($variant['option'] && count($variant['tags'])) {
                        foreach ($variant['tags'] as $tags) {
                            $productVariantsData[] = [
                                'product_id' => $product->id,
                                'variant' => $tags,
                                'variant_id' => $variant['option'],
                                'created_at' => $created_at
                            ];
                        }
                    }
                }

                // Create Product Variant ...
                $productVariants = $product->productVariants()->createMany($productVariantsData);

                if (count($request->product_variant_prices) && count($productVariants)) {
                    $productVariantsPricesData = [];
                    foreach ($request->product_variant_prices as $variant_prices) {
                        $productVariantsPricesData[] = [
                            'price' => $variant_prices['price'],
                            'stock' => $variant_prices['stock'],
                            'product_id' => $product->id,
                            'product_variant_one' => $this->getVariantId($productVariants, explode('/', $variant_prices['title'])[0]),
                            'product_variant_two' => $this->getVariantId($productVariants, explode('/', $variant_prices['title'])[1]),
                            'product_variant_three' => $this->getVariantId($productVariants, explode('/', $variant_prices['title'])[2]),
                            'created_at' => $created_at
                        ];
                    }
                    // Create Product Variant Price...
                    ProductVariantPrice::insert($productVariantsPricesData);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Product Saved'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e], 400);
        }
    }

    public function getVariantId($productVariants, $variant)
    {
        return $productVariants->where('variant', $variant)->first()->id ?? null;
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return void
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     * @return Application|Factory|View
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        $product->load('productImages', 'productVariants', 'productVariantPrices');
        return view('products.edit', compact('product', 'variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $product->update($request->only('title', 'sku', 'description'));

            $created_at = Carbon::now();
            if ($request->product_images && count($request->product_images)) {
                $productImagesData = [];
                foreach ($request->product_images as $image) {
                    $productImagesData[] = [
                        'product_id' => $product->id,
                        'file_path' => $image,
                        'created_at' => $created_at
                    ];
                }
                $product->productImages()->delete();
                $product->productImages()->insert($productImagesData);
            }

            if ($request->product_variants && count($request->product_variants)) {
                $productVariantsData = [];
                foreach ($request->product_variants as $variant) {
                    if ($variant['option'] && count($variant['tags'])) {
                        foreach ($variant['tags'] as $tags) {
                            $productVariantsData[] = [
                                'product_id' => $product->id,
                                'variant' => $tags,
                                'variant_id' => $variant['option'],
                                'created_at' => $created_at
                            ];
                        }
                    }
                }
                $product->productVariants()->delete();
                $productVariants = $product->productVariants()->createMany($productVariantsData);

                if (count($request->product_variant_prices) && count($productVariants)) {
                    $productVariantsPricesData = [];
                    foreach ($request->product_variant_prices as $variant_prices) {
                        $productVariantsPricesData[] = [
                            'price' => $variant_prices['price'],
                            'stock' => $variant_prices['stock'],
                            'product_id' => $product->id,
                            'product_variant_one' => $this->getVariantId($productVariants, explode('/', $variant_prices['title'])[0]),
                            'product_variant_two' => $this->getVariantId($productVariants, explode('/', $variant_prices['title'])[1]),
                            'product_variant_three' => $this->getVariantId($productVariants, explode('/', $variant_prices['title'])[2]),
                            'created_at' => $created_at
                        ];
                    }
                    $product->productVariantPrices()->delete();
                    ProductVariantPrice::insert($productVariantsPricesData);
                }
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
