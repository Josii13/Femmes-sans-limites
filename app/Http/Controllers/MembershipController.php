<?php

namespace App\Http\Controllers;

use App\Mail\MembershipConfirmationMail;
use App\Mail\NewMembershipMail;
use App\Models\Member;
use App\Models\User;
use App\Services\MemberCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    public function __construct(private MemberCardService $cardService) {}

    public function index()
    {
        return view('public.join');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:members,email',
            'profession' => 'required|string|max:100',
            'country'    => 'required|string|max:100',
            'city'       => 'required|string|max:100',
            'photo'      => 'nullable|image|max:3072',
        ]);

        $validated['member_number'] = 'FSL-' . strtoupper(Str::random(6));
        $validated['type']          = 'standard';
        $validated['status']        = 'inactive';

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $member = Member::create($validated);

        $cardPath = $this->cardService->generate($member);
        $member->update(['card_path' => $cardPath]);

        // Notify admin
        $admin = User::first();
        if ($admin) {
            \Mail::to($admin->email)->send(new NewMembershipMail($member));
        }

        // Confirm to applicant
        \Mail::to($member->email)->send(new MembershipConfirmationMail($member));

        return redirect()->route('membership.success');
    }

    public function success()
    {
        return view('public.join-success');
    }
}
