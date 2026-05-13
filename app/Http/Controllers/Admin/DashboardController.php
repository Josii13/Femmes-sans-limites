<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Event;
use App\Models\Registration;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'members'       => Member::count(),
            'premium'       => Member::where('type', 'premium')->count(),
            'gold'          => Member::where('type', 'gold')->count(),
            'standard'      => Member::where('type', 'standard')->count(),
            'events'        => Event::count(),
            'published'     => Event::where('status', 'published')->count(),
            'registrations' => Registration::count(),
            'paid'          => Registration::where('status', 'paid')->count(),
        ];

        $upcoming = Event::where('status', 'published')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->withCount(['registrations' => fn($q) => $q->whereNotIn('status', ['cancelled'])])
            ->get();

        $stats['pending'] = Member::where('status', 'inactive')->count();
        $stats['active']  = Member::where('status', 'active')->count();

        $recentMembers  = Member::latest()->limit(6)->get();
        $pendingMembers = Member::where('status', 'inactive')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'upcoming', 'recentMembers', 'pendingMembers'));
    }
}
