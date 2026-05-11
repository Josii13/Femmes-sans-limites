<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\MemberCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function __construct(private MemberCardService $cardService) {}

    public function index(Request $request)
    {
        $members = Member::when($request->search, fn($q) => $q->where('name', 'like', '%'.$request->search.'%')
            ->orWhere('email', 'like', '%'.$request->search.'%'))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:members,email',
            'profession' => 'required|string|max:100',
            'country'    => 'required|string|max:100',
            'city'       => 'required|string|max:100',
            'type'       => 'required|in:standard,gold,premium',
            'photo'      => 'nullable|image|max:3072',
        ]);

        $validated['member_number'] = 'FSL-' . strtoupper(Str::random(6));

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $member = Member::create($validated);

        $cardPath = $this->cardService->generate($member);
        $member->update(['card_path' => $cardPath]);

        return redirect()->route('admin.members.show', $member)
            ->with('success', 'Membre créé et carte générée avec succès.');
    }

    public function show(Member $member)
    {
        return view('admin.members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('admin.members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:members,email,'.$member->id,
            'profession' => 'required|string|max:100',
            'country'    => 'required|string|max:100',
            'city'       => 'required|string|max:100',
            'type'       => 'required|in:standard,gold,premium',
            'status'     => 'required|in:active,inactive',
            'photo'      => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('photo')) {
            if ($member->photo) Storage::disk('public')->delete($member->photo);
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $typeChanged = $request->type !== $member->type;
        $member->update($validated);

        if ($typeChanged || $request->hasFile('photo')) {
            $cardPath = $this->cardService->generate($member->fresh());
            $member->update(['card_path' => $cardPath]);
        }

        return redirect()->route('admin.members.show', $member)->with('success', 'Membre mis à jour.');
    }

    public function destroy(Member $member)
    {
        if ($member->photo) Storage::disk('public')->delete($member->photo);
        if ($member->card_path) Storage::disk('public')->delete($member->card_path);
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Membre supprimé.');
    }

    public function downloadCard(Member $member)
    {
        if (!$member->card_path || !Storage::disk('public')->exists($member->card_path)) {
            $cardPath = $this->cardService->generate($member);
            $member->update(['card_path' => $cardPath]);
        }

        return Storage::disk('public')->download(
            $member->card_path,
            'carte-membre-'.Str::slug($member->name).'.png'
        );
    }

    public function sendCard(Member $member)
    {
        if (!$member->card_path) {
            $cardPath = $this->cardService->generate($member);
            $member->update(['card_path' => $cardPath]);
        }

        \Mail::to($member->email)->send(new \App\Mail\MemberCardMail($member));

        return back()->with('success', 'Carte envoyée à '.$member->email);
    }
}
