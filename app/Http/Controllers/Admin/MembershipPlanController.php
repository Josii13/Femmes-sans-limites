<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Tarifs d'adhésion.
 *
 * Les niveaux eux-mêmes (standard, gold, premium) sont structurants : ils sont
 * inscrits sur les cartes et servent au ciblage des campagnes. On peut donc les
 * régler et les désactiver, mais pas en créer ni en supprimer depuis cet écran.
 */
class MembershipPlanController extends Controller
{
    public function index()
    {
        return view('admin.membership-plans.index', [
            'plans' => MembershipPlan::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function edit(MembershipPlan $membershipPlan)
    {
        return view('admin.membership-plans.edit', ['plan' => $membershipPlan]);
    }

    public function update(Request $request, MembershipPlan $membershipPlan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'description' => 'nullable|string|max:500',
            // Vide = niveau gratuit : l'adhésion d'entrée doit pouvoir le rester.
            'price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_months' => 'required|integer|min:1|max:60',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'duration_months.min' => 'Une adhésion dure au moins un mois.',
            'duration_months.max' => 'Une durée au-delà de 60 mois est probablement une erreur de saisie.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? $membershipPlan->sort_order;
        $validated['price'] = $validated['price'] === null || $validated['price'] === ''
            ? null
            : $validated['price'];

        $membershipPlan->update($validated);

        ActivityLog::record('membership_plan.updated', $membershipPlan, [
            'price' => $membershipPlan->price,
            'active' => $membershipPlan->is_active,
        ]);

        return redirect()->route('admin.membership-plans.index')
            ->with('success', 'Tarif « '.$membershipPlan->name.' » mis à jour.');
    }
}
