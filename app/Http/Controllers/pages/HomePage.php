<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\GlobalSetting;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\Log;

class HomePage extends Controller
{
  public function index()
  {
    // Check Redis for sliders first
    if (Redis::exists('active_sliders')) {
      // Log retrieval from Redis
      Log::info('Sliders loaded from Redis cache.');

      // Retrieve sliders from Redis and decode them
      $sliders = json_decode(Redis::get('active_sliders'), true);

      // Log the count of sliders loaded from cache
      Log::info('Number of sliders loaded from cache: ' . count($sliders));
    } else {
      // Load from database, log, and store in Redis
      Log::info('Sliders loaded from the database.');
      $sliders = Slider::where('status', 1)->get()->toArray(); // Convert to array for storage

      // Store in Redis with a 60-minute expiration
      Redis::setex('active_sliders', 3600, json_encode($sliders));
      Log::info('Sliders stored in Redis for future use.');
    }

    // Check Redis for products first
    if (Redis::exists('all_products')) {
      // Log retrieval from Redis
      Log::info('Products loaded from Redis cache.');

      // Retrieve products from Redis and decode them
      $products = json_decode(Redis::get('all_products'), true);

      // Log the count of products loaded from cache
      Log::info('Number of products loaded from cache: ' . count($products));
    } else {
      // Load from database, log, and store in Redis
      Log::info('Products loaded from the database.');
      $products = Product::get()->toArray(); // Convert to array for storage

      // Store in Redis with a 60-minute expiration
      Redis::setex('all_products', 3600, json_encode($products));
      Log::info('Products stored in Redis for future use.');
    }

    // Log the total count of sliders and products before rendering the view
    Log::info('Total sliders: ' . count($sliders) . ', Total products: ' . count($products));

    return view('homepage.index', compact('products', 'sliders'));
  }

  public function productShow($slug)
  {
    // Retrieve cart from session
    $carts = session()->get('cart', []);
    $totalPrice = 0;

    // Calculate total price from the cart
    foreach ($carts as $item) {
      $totalPrice += $item['price'] * $item['quantity'];
    }

    // Check if the product is cached in Redis
    $product = Cache::store('redis')->remember("product_{$slug}", 60, function () use ($slug) {
      // Log loading from database if not cached
      Log::info("Product '{$slug}' loaded from the database.");
      return Product::with('category')->where('slug', $slug)->first();
    });

    // Log when the product is loaded from Redis
    if (Cache::store('redis')->has("product_{$slug}")) {
      Log::info("Product '{$slug}' loaded from Redis cache.");
    }

    return view('homepage.show', compact('product', 'totalPrice'));
  }
  public function products()
  {
    // Check if products are cached
    $products = Cache::store('redis')->remember('all_products', 60, function () {
      // Log loading from database if not cached
      Log::info('Products loaded from the database.');
      return Product::get();
    });

    // Log when products are loaded from Redis
    if (Cache::store('redis')->has('all_products')) {
      Log::info('Products loaded from Redis cache.');
    }

    return view('homepage.products', compact('products'));
  }
  public function cartPage()
  {
    $carts = session()->get('cart', []);

    return view('homepage.cart.index', compact('carts'));
  }
}
