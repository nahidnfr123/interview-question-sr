<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = [
        'product_variant_one',
        'product_variant_two',
        'product_variant_three',
        'price',
        'stock',
        'product_id'
    ];

    public function getVarientConbinationAttribute()
    {
        $var1 = $this->productVariantOne ? $this->productVariantOne->variant : '';
        $var2 = $this->productVariantTwo ? '/' . $this->productVariantTwo->variant : '';
        $var3 = $this->productVariantThree ? '/' . $this->productVariantThree->variant : '';
        return $var1 . $var2 . $var3;
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariantOne(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one');
    }

    public function productVariantTwo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two');
    }

    public function productVariantThree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three');
    }
}
