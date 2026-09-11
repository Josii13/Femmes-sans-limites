@extends('layouts.admin')
@section('title','Modifier le témoignage')
@section('page-title','Modifier le témoignage')
@section('page-subtitle', $testimonial->name)

@section('content')
<div class="max-w-3xl">
    <form action="{{ route('admin.testimonials.update', $testimonial) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')
        @include('admin.testimonials.partials.form')

        <div class="flex gap-3">
            <button type="submit" class="btn-rose">Enregistrer</button>
            <a href="{{ route('admin.testimonials.index') }}" class="btn-gold">Annuler</a>
        </div>
    </form>
</div>
@endsection
