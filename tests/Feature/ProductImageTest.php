<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_have_valid_image_urls(): void
    {
        $this->seed();

        $products = Product::all();
        $this->assertGreaterThan(0, $products->count());

        foreach ($products as $product) {
            $this->assertNotNull($product->image_url);
            $this->assertNotEmpty($product->image_url);
        }
    }
}
