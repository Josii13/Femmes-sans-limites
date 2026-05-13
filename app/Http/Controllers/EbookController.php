<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use Illuminate\Http\Request;

class EbookController extends Controller
{
    public function index(Request $request)
    {
        $query = Ebook::published();

        if ($request->filled('categorie')) {
            $query->where('category', $request->categorie);
        }

        $ebooks = $query->get();

        $categories = Ebook::where('status', 'published')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return view('public.ebooks.index', compact('ebooks', 'categories'));
    }

    public function show(string $slug)
    {
        $ebook = Ebook::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $others = Ebook::published()
            ->where('id', '!=', $ebook->id)
            ->limit(4)
            ->get();

        return view('public.ebooks.show', compact('ebook', 'others'));
    }
}
