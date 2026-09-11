<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::with('member')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create', ['members' => $this->members()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('testimonials', 'public');
        }

        $testimonial = Testimonial::create($validated);
        ActivityLog::record('testimonial.created', $testimonial);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Témoignage de '.$testimonial->name.' ajouté.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', [
            'testimonial' => $testimonial,
            'members' => $this->members(),
        ]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('photo')) {
            if ($testimonial->photo) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $validated['photo'] = $request->file('photo')->store('testimonials', 'public');
        }

        $testimonial->update($validated);
        ActivityLog::record('testimonial.updated', $testimonial);

        return redirect()->route('admin.testimonials.index')->with('success', 'Témoignage mis à jour.');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($testimonial->photo) {
            Storage::disk('public')->delete($testimonial->photo);
        }

        ActivityLog::record('testimonial.deleted', $testimonial);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Témoignage supprimé.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'name' => 'required|string|max:120',
            'role' => 'nullable|string|max:120',
            // Assez long pour être sincère, assez court pour tenir dans une carte.
            'quote' => 'required|string|min:40|max:600',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Indiquez le nom de la personne citée.',
            'quote.required' => 'Le témoignage ne peut pas être vide.',
            'quote.min' => 'Un témoignage de moins de 40 caractères n’apporte rien : développez un peu.',
            'quote.max' => 'Le témoignage ne doit pas dépasser 600 caractères.',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        unset($validated['photo']);

        return $validated;
    }

    /** Membres actives, pour rattacher un témoignage et reprendre sa photo. */
    private function members()
    {
        return Member::where('status', 'active')->orderBy('name')->get(['id', 'name', 'profession', 'city']);
    }
}
