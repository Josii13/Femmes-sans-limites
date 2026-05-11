@extends('layouts.admin')
@section('title','Modifier '.$event->title)
@section('page-title','Modifier l\'événement')
@section('page-subtitle', $event->title)

@section('content')
<div class="max-w-3xl">
    <div class="admin-card">
        <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Titre *</label>
                <input type="text" name="title" value="{{ old('title', $event->title) }}" class="form-input">
            </div>

            <div>
                <label class="form-label">Description courte</label>
                <input type="text" name="short_description" value="{{ old('short_description', $event->short_description) }}" class="form-input">
            </div>

            <div>
                <label class="form-label">Description complète *</label>
                <textarea name="description" rows="6" class="form-input resize-none">{{ old('description', $event->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date et heure *</label>
                    <input type="datetime-local" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d\TH:i')) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Capacité max</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $event->capacity) }}" class="form-input" min="1">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Lieu *</label>
                    <input type="text" name="location" value="{{ old('location', $event->location) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $event->city) }}" class="form-input">
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="font-medium text-sm" style="color:var(--dark);">Événement payant</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_paid" value="1" id="is_paid" {{ old('is_paid', $event->is_paid) ? 'checked' : '' }} class="sr-only peer" onchange="document.getElementById('payment-fields').classList.toggle('hidden', !this.checked)">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all" style="{{ old('is_paid', $event->is_paid) ? 'background:var(--rose)' : '' }}"></div>
                    </label>
                </div>
                <div id="payment-fields" class="{{ old('is_paid', $event->is_paid) ? '' : 'hidden' }} space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="form-label">Prix</label>
                            <input type="number" name="price" value="{{ old('price', $event->price) }}" class="form-input" step="100" min="0">
                        </div>
                        <div>
                            <label class="form-label">Devise</label>
                            <select name="currency" class="form-input">
                                <option value="FCFA" @selected(old('currency',$event->currency)==='FCFA')>FCFA</option>
                                <option value="EUR" @selected(old('currency',$event->currency)==='EUR')>EUR</option>
                                <option value="USD" @selected(old('currency',$event->currency)==='USD')>USD</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Lien de paiement</label>
                        <input type="url" name="payment_link" value="{{ old('payment_link', $event->payment_link) }}" class="form-input" placeholder="https://...">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" class="form-input">
                        @foreach(['draft'=>'Brouillon','published'=>'Publié','cancelled'=>'Annulé','completed'=>'Terminé'] as $s => $l)
                        <option value="{{ $s }}" @selected(old('status',$event->status)===$s)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Nouvelle image</label>
                    @if($event->image)<img src="{{ asset('storage/'.$event->image) }}" class="h-10 rounded mb-2 object-cover">@endif
                    <input type="file" name="image" accept="image/*" class="form-input">
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-rose">Enregistrer</button>
                <a href="{{ route('admin.events.show', $event) }}" class="btn-gold">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
