<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:191',
            'name'  => 'nullable|string|max:100',
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => $request->email],
            ['name'  => $request->name]
        );

        return back()->with('newsletter_success', true);
    }

    public function unsubscribe(string $token)
    {
        $sub = NewsletterSubscriber::where('token', $token)->first();

        if ($sub) {
            $sub->delete();
        }

        return view('public.newsletter.unsubscribed');
    }
}
