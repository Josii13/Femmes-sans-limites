<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use App\Models\Event;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Registration;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'members' => Member::count(),
            'premium' => Member::where('type', 'premium')->count(),
            'gold' => Member::where('type', 'gold')->count(),
            'standard' => Member::where('type', 'standard')->count(),
            'events' => Event::count(),
            'published' => Event::where('status', 'published')->count(),
            'registrations' => Registration::count(),
            'paid' => Registration::where('status', 'paid')->count(),
            'pending' => Member::where('status', 'pending')->count(),
            'active' => Member::where('status', 'active')->count(),
        ];

        $upcoming = Event::where('status', 'published')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->withCount(['registrations' => fn ($q) => $q->whereNotIn('status', ['cancelled'])])
            ->get();

        $recentMembers = Member::latest()->limit(6)->get();
        $pendingMembers = Member::where('status', 'pending')->latest()->limit(5)->get();

        // Le tableau de bord est ouvert à tous les rôles, y compris « éditrice ».
        // Les recettes et les données d'adhésion ne doivent pas y fuiter : le
        // cloisonnement des routes ne servirait à rien si cette page les affichait.
        $seesOperations = (bool) auth()->user()?->canManageOperations();

        return view('admin.dashboard', [
            'stats' => $stats,
            'upcoming' => $upcoming,
            'recentMembers' => $recentMembers,
            'pendingMembers' => $pendingMembers,
            'seesOperations' => $seesOperations,
            'revenue' => $seesOperations ? $this->revenue() : null,
            'growth' => $seesOperations ? $this->membershipGrowth() : [],
            'attention' => $seesOperations ? $this->needsAttention() : [],
        ]);
    }

    /**
     * Recettes réelles, par source et par période.
     *
     * Seuls les paiements aboutis sont comptés : un paiement abandonné n'est pas
     * une recette, et l'afficher gonflerait artificiellement les chiffres.
     *
     * @return array<string, mixed>
     */
    private function revenue(): array
    {
        $completed = fn () => Payment::whereIn('status', ['completed', 'paid']);

        $types = [
            'ebooks' => (new Ebook)->getMorphClass(),
            'events' => (new Registration)->getMorphClass(),
            'memberships' => (new Member)->getMorphClass(),
        ];

        $byType = [];
        foreach ($types as $key => $morphClass) {
            $byType[$key] = (float) $completed()->where('payable_type', $morphClass)->sum('amount');
        }

        $thisMonth = (float) $completed()->where('paid_at', '>=', now()->startOfMonth())->sum('amount');
        $lastMonth = (float) $completed()
            ->whereBetween('paid_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('amount');

        return [
            'total' => (float) $completed()->sum('amount'),
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            // Évolution en pourcentage ; null quand le mois précédent est vide,
            // car « +100 % » à partir de zéro ne veut rien dire.
            'trend' => $lastMonth > 0 ? (int) round((($thisMonth - $lastMonth) / $lastMonth) * 100) : null,
            'by_type' => $byType,
            'currency' => Payment::whereIn('status', ['completed', 'paid'])->value('currency') ?? 'XOF',
            'pending_count' => Payment::whereIn('status', ['pending', 'processing'])->count(),
        ];
    }

    /**
     * Adhésions activées sur les six derniers mois, pour voir une tendance
     * plutôt qu'un total figé.
     *
     * @return array<int, array{label: string, count: int}>
     */
    private function membershipGrowth(): array
    {
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            $months[] = [
                'label' => $start->translatedFormat('M'),
                'count' => Member::whereBetween('joined_at', [$start, $end])->count(),
            ];
        }

        return $months;
    }

    /**
     * Ce qui réclame une action, plutôt qu'un simple état des lieux : le tableau
     * de bord doit dire quoi faire, pas seulement où l'on en est.
     *
     * @return array<int, array{label: string, count: int, route: string, tone: string}>
     */
    private function needsAttention(): array
    {
        $items = [];

        $pending = Member::where('status', 'pending')->count();
        if ($pending > 0) {
            $items[] = [
                'label' => $pending.' candidature'.($pending > 1 ? 's' : '').' à traiter',
                'count' => $pending,
                'route' => route('admin.members.index', ['status' => 'pending']),
                'tone' => 'urgent',
            ];
        }

        // Adhésions arrivant à échéance : la relance automatique part, mais un
        // suivi humain rattrape celles qui ne réagissent pas.
        $expiring = Member::where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->count();
        if ($expiring > 0) {
            $items[] = [
                'label' => $expiring.' adhésion'.($expiring > 1 ? 's' : '').' à renouveler sous 30 jours',
                'count' => $expiring,
                'route' => route('admin.members.index', ['status' => 'active']),
                'tone' => 'warning',
            ];
        }

        $expired = Member::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();
        if ($expired > 0) {
            $items[] = [
                'label' => $expired.' adhésion'.($expired > 1 ? 's' : '').' échue'.($expired > 1 ? 's' : '').' encore marquée'.($expired > 1 ? 's' : '').' active'.($expired > 1 ? 's' : ''),
                'count' => $expired,
                'route' => route('admin.members.index', ['status' => 'active']),
                'tone' => 'warning',
            ];
        }

        // Un ebook payant sans PDF ne peut être ni vendu ni livré.
        $unsellable = Ebook::where('status', 'published')
            ->whereNotNull('price')->where('price', '>', 0)
            ->where(fn ($q) => $q->whereNull('file_path')->orWhere('file_path', ''))
            ->count();
        if ($unsellable > 0) {
            $items[] = [
                'label' => $unsellable.' ebook'.($unsellable > 1 ? 's' : '').' avec un prix mais sans fichier',
                'count' => $unsellable,
                'route' => route('admin.ebooks.index'),
                'tone' => 'urgent',
            ];
        }

        $stalled = Payment::whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<', now()->subDay())
            ->count();
        if ($stalled > 0) {
            $items[] = [
                'label' => $stalled.' paiement'.($stalled > 1 ? 's' : '').' en attente depuis plus de 24 h',
                'count' => $stalled,
                'route' => route('admin.sales.index', ['status' => 'pending']),
                'tone' => 'info',
            ];
        }

        return $items;
    }
}
