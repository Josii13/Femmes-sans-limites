<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query()->with('payable')->latest();

        if ($request->filled('type')) {
            $map = ['event' => (new Registration)->getMorphClass(), 'ebook' => (new Ebook)->getMorphClass()];
            if (isset($map[$request->type])) {
                $query->where('payable_type', $map[$request->type]);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(30)->withQueryString();

        // Statistiques (uniquement les paiements aboutis).
        $completed = Payment::whereIn('status', ['completed', 'paid']);
        $eventType = (new Registration)->getMorphClass();
        $ebookType = (new Ebook)->getMorphClass();

        $stats = [
            'total' => (clone $completed)->sum('amount'),
            'count' => (clone $completed)->count(),
            'events' => (clone $completed)->where('payable_type', $eventType)->sum('amount'),
            'ebooks' => (clone $completed)->where('payable_type', $ebookType)->sum('amount'),
            'ebooks_count' => (clone $completed)->where('payable_type', $ebookType)->count(),
        ];

        return view('admin.sales.index', compact('payments', 'stats'));
    }
}
