<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\TopsisResult;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->withCount('products')->get();
        $featuredProducts = Product::where('is_active', true)
            ->with('category')
            ->orderBy('stock', 'desc')
            ->take(8)
            ->get();
        $latestTopsis = auth()->user()?->isOwner()
            ? TopsisResult::query()->latest()->first()
            : null;

        return view('home.index', compact('categories', 'featuredProducts', 'latestTopsis'));
    }

    public function catalog(Request $request)
    {
        $categories = Category::where('is_active', true)->get();
        $query = Product::where('is_active', true)->with('category');

        // Filter Category
        if ($request->filled('kategori')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->kategori);
            });
        }

        // Search Query
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('barcode', 'like', "%{$searchTerm}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        if ($sort === 'termurah') {
            $query->orderBy('sell_price', 'asc');
        } elseif ($sort === 'termahal') {
            $query->orderBy('sell_price', 'desc');
        } elseif ($sort === 'stok_terbanyak') {
            $query->orderBy('stock', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        return view('home.catalog', compact('categories', 'products'));
    }

    public function productDetail($slug)
    {
        $product = Product::where('slug', $slug)->with(['category', 'reviews.user'])->firstOrFail();
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('home.detail', compact('product', 'relatedProducts'));
    }

    public function about()
    {
        return view('home.about');
    }

    public function contact()
    {
        return view('home.contact');
    }
}
