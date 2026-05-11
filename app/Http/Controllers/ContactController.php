<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('public.contact');
    }

    public function send(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        \Log::info('Contact form submission', $request->only('name', 'email', 'subject', 'message'));

        return back()->with('success', 'Votre message a bien été envoyé. Nous vous répondrons sous 48h.');
    }
}
