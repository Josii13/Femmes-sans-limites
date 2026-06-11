<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function index()
    {
        return view('public.contact');
    }

    public function send(Request $request)
    {
        // Honeypot anti-bot : un humain ne remplit jamais ce champ caché.
        if ($request->filled('website')) {
            return back()->with('success', 'Votre message a bien été envoyé. Nous vous répondrons sous 48h.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email',
                'subject' => 'required|string|max:200',
                'message' => 'required|string|max:2000',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('modal', 'contact');
        }

        Log::info('Contact form submission', $request->only('name', 'email', 'subject', 'message'));

        return back()->with('success', 'Votre message a bien été envoyé. Nous vous répondrons sous 48h.')
            ->with('modal', 'contact');
    }
}
