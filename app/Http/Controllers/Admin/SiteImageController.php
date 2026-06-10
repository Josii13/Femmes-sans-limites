<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteImageController extends Controller
{
    public function index()
    {
        $pages = [
            'home' => 'Page d\'accueil',
            'about' => 'Page À propos',
        ];
        $images = SiteImage::all()->groupBy('page');

        return view('admin.site-images.index', compact('images', 'pages'));
    }

    public function update(Request $request, SiteImage $siteImage)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'image.required' => 'Veuillez sélectionner une image.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Formats acceptés : JPEG, PNG, WebP.',
            'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
        ]);

        if ($siteImage->custom_path) {
            Storage::disk('public')->delete($siteImage->custom_path);
        }

        $path = $request->file('image')->store('site-images', 'public');
        $siteImage->update(['custom_path' => $path]);

        return back()->with('success', '«&nbsp;'.$siteImage->label.'&nbsp;» a bien été mis à jour.');
    }

    public function reset(SiteImage $siteImage)
    {
        if ($siteImage->custom_path) {
            Storage::disk('public')->delete($siteImage->custom_path);
            $siteImage->update(['custom_path' => null]);
        }

        return back()->with('success', '«&nbsp;'.$siteImage->label.'&nbsp;» a été réinitialisée à l\'image par défaut.');
    }
}
