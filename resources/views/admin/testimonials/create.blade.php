@extends('layouts.admin')
@section('title','Nouveau témoignage')
@section('page-title','Nouveau témoignage')
@section('page-subtitle','Ajouter la parole d’une membre à la page d’accueil')

@section('content')
<div class="max-w-3xl">
    <form action="{{ route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.testimonials.partials.form')

        <div class="flex gap-3">
            <button type="submit" class="btn-rose">Ajouter le témoignage</button>
            <a href="{{ route('admin.testimonials.index') }}" class="btn-gold">Annuler</a>
        </div>
    </form>
</div>
@endsection
