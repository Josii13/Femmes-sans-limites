<x-mail::message>
<div style="text-align:center;margin-bottom:24px;">
<img src="{{ asset('logo-email.png') }}" alt="FSL" style="height:48px;width:auto;">
</div>

# Merci pour ton achat ! 📚

Bonjour {{ $payment->customer_name ?? '' }},

Ton paiement a bien été reçu. Voici ton ebook :

## {{ $ebook->title }}

@if($ebook->category)
*{{ $ebook->category }}*
@endif

Clique sur le bouton ci-dessous pour le télécharger. Ce lien est personnel et valable **30 jours**.

<x-mail::button :url="$downloadUrl" color="primary">
Télécharger mon ebook →
</x-mail::button>

Bonne lecture, et merci de soutenir **Femme Sans Limites** ✨

Avec gratitude,
**L'équipe Femme Sans Limites**

<small style="color:#9CA3AF;">Référence d'achat : {{ $payment->reference }}</small>
</x-mail::message>
