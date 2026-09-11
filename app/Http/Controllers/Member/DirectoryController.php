<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Annuaire de la communauté.
 *
 * La raison première d'adhérer à un réseau est d'y rencontrer les autres. Cet
 * annuaire n'est accessible qu'aux membres connectées : le nom, le métier et la
 * ville d'une adhérente n'ont pas à être exposés publiquement.
 *
 * Aucune coordonnée directe n'y figure — ni email ni téléphone. L'annuaire sert
 * à savoir qui compose la communauté, pas à en extraire un fichier de contacts.
 */
class DirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::where('status', 'active')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = trim($request->input('q'));
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%'.$search.'%')
                        ->orWhere('profession', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%')
                        ->orWhere('country', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('pays'), fn ($q) => $q->where('country', $request->input('pays')))
            ->when($request->filled('profession'), fn ($q) => $q->where('profession', $request->input('profession')));

        $members = $query->orderBy('name')->paginate(24)->withQueryString();

        return view('member.directory', [
            'members' => $members,
            'countries' => Member::where('status', 'active')
                ->whereNotNull('country')->where('country', '!=', '')
                ->distinct()->orderBy('country')->pluck('country'),
            'professions' => Member::where('status', 'active')
                ->whereNotNull('profession')->where('profession', '!=', '')
                ->distinct()->orderBy('profession')->pluck('profession'),
            'currentMember' => Auth::guard('member')->user(),
        ]);
    }
}
