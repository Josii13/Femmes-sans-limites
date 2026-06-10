<?php

namespace App\Http\Controllers;

use App\Models\Event;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(3)
            ->get();

        return view('public.home', compact('events'));
    }

    public function about()
    {
        return view('public.about');
    }
}
