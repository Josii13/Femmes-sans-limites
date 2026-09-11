@extends('layouts.member')
@section('title', 'Mes ebooks')

@section('content')

<h1 class="text-2xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Mes ebooks</h1>
<p class="text-sm mb-6" style="color:var(--gray);">
    Vos achats restent accessibles ici indéfiniment, même si le lien reçu par email a expiré.
</p>

@if($purchases->isEmpty())
<div class="card p-10 text-center">
    <p class="font-semibold" style="color:var(--dark);">Aucun ebook acheté</p>
    <p class="text-sm mt-1 mb-5" style="color:var(--gray);">Découvrez la bibliothèque de la communauté.</p>
    <a href="{{ route('ebooks.index') }}" class="btn-rose text-sm">Voir la bibliothèque</a>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach($purchases as $purchase)
    @php $ebook = $purchase->payable; @endphp
    <div class="card p-5 flex gap-4">
        <div class="w-16 flex-shrink-0 rounded-lg overflow-hidden" style="aspect-ratio:3/4;">
            @if($ebook->image)
            <img src="{{ asset('storage/'.$ebook->image) }}" alt="" class="w-full h-full object-cover">
            @else
            <div class="w-full h-full flex items-center justify-center" style="background:var(--rose-pale);">
                <svg class="w-6 h-6 opacity-40" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            @endif
        </div>

        <div class="min-w-0 flex flex-col">
            <p class="font-bold text-sm leading-snug" style="color:var(--dark);">{{ $ebook->title }}</p>
            <p class="text-xs mt-0.5" style="color:var(--gray);">
                Acheté le {{ $purchase->paid_at?->translatedFormat('d F Y') ?? $purchase->created_at->translatedFormat('d F Y') }}
            </p>
            <a href="{{ $purchase->download_url }}" class="btn-rose text-xs mt-auto pt-0 inline-flex items-center justify-center gap-1.5 px-4 py-2 self-start">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Télécharger
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
