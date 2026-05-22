<x-mail::message>
<div style="text-align:center;margin-bottom:24px;">
<img src="{{ asset('logo_FSL.png') }}" alt="FSL" style="height:48px;width:auto;">
</div>

# Nouvel ebook disponible 📚

Bonjour {{ $subscriber->name ?? 'chère membre' }},

Un nouvel ebook vient d'être ajouté à la bibliothèque **Femme Sans Limites** !

---

## {{ $ebook->title }}

**Catégorie :** {{ $ebook->category }}

{{ Str::limit($ebook->description, 300) }}

@if($ebook->author_note)

> *« {{ $ebook->author_note }} »*

@endif

<x-mail::button :url="route('ebooks.show', $ebook->slug)" color="success">
Découvrir cet ebook →
</x-mail::button>

---

*Tu reçois cet email car tu es abonnée à la newsletter FSL.*
*[Se désabonner]({{ route('newsletter.unsubscribe', $subscriber->token) }})*

Avec amour,
**L'équipe Femme Sans Limites**
</x-mail::message>
