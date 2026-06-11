@extends('layouts.admin')
@section('title','Créer un événement')
@section('page-title','Créer un événement')

@section('content')
<div class="max-w-3xl">
    <div class="admin-card">
        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="form-label">Titre de l'événement *</label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-input @error('title') border-red-400 @enderror" placeholder="Nom de l'événement">
                @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Description courte</label>
                <input type="text" name="short_description" value="{{ old('short_description') }}" class="form-input" placeholder="Résumé en 1-2 phrases (affiché dans les listes)">
            </div>

            <div>
                <label class="form-label">Description complète *</label>
                <textarea name="description" rows="6" class="form-input resize-none @error('description') border-red-400 @enderror" placeholder="Description détaillée de l'événement...">{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date et heure *</label>
                    <input type="datetime-local" name="event_date" value="{{ old('event_date') }}" class="form-input @error('event_date') border-red-400 @enderror">
                    @error('event_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Clôture des inscriptions</label>
                    <input type="datetime-local" name="registration_closes_at" value="{{ old('registration_closes_at') }}" class="form-input" placeholder="Laisser vide si pas de limite">
                    <p class="text-xs text-gray-400 mt-1">Optionnel — les inscriptions ferment automatiquement à cette date.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Capacité max</label>
                    <input type="number" name="capacity" value="{{ old('capacity') }}" class="form-input" placeholder="Illimité si vide" min="1">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Lieu / Salle *</label>
                    <input type="text" name="location" value="{{ old('location') }}" class="form-input @error('location') border-red-400 @enderror" placeholder="Nom de la salle, adresse...">
                    @error('location')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="form-input" placeholder="Abidjan">
                </div>
            </div>

            {{-- Payment --}}
            <div class="rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="font-medium text-sm" style="color:var(--dark);">Événement payant</p>
                        <p class="text-xs text-gray-400 mt-0.5">Activez si des frais d'inscription sont requis</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_paid" value="1" id="is_paid" {{ old('is_paid') ? 'checked' : '' }} class="sr-only peer" onchange="document.getElementById('payment-fields').classList.toggle('hidden', !this.checked)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500" style="--tw-peer-checked-bg:var(--rose);"></div>
                    </label>
                </div>

                <div id="payment-fields" class="{{ old('is_paid') ? '' : 'hidden' }} space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="form-label">Prix *</label>
                            <input type="number" name="price" value="{{ old('price') }}" class="form-input" placeholder="5000" step="100" min="0">
                        </div>
                        <div>
                            <label class="form-label">Devise</label>
                            <select name="currency" class="form-input">
                                <option value="XOF" @selected(in_array(old('currency','XOF'), ['XOF','FCFA']))>FCFA (XOF)</option>
                                <option value="EUR" @selected(old('currency')==='EUR')>EUR</option>
                                <option value="USD" @selected(old('currency')==='USD')>USD</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Lien de paiement</label>
                        <input type="url" name="payment_link" value="{{ old('payment_link') }}" class="form-input" placeholder="https://wave.com/... ou CinetPay, PayDunya...">
                        <p class="text-xs text-gray-400 mt-1">Créez le lien dans votre outil de paiement, collez-le ici. Il sera envoyé par email aux inscrits.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Statut de publication *</label>
                    <select name="status" class="form-input">
                        <option value="draft" @selected(old('status','draft')==='draft')>Brouillon</option>
                        <option value="published" @selected(old('status')==='published')>Publié</option>
                        <option value="cancelled" @selected(old('status')==='cancelled')>Annulé</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Image de couverture</label>
                    <input type="file" name="image" accept="image/*" class="form-input">
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-rose">Créer l'événement</button>
                <a href="{{ route('admin.events.index') }}" class="btn-gold">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
