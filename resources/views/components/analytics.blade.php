{{--
    Mesure d'audience, pilotée par la configuration.

    Rien n'est chargé tant qu'aucun fournisseur n'est renseigné : par défaut le
    site ne dépose aucun cookie tiers et n'appelle aucun domaine externe.

    Jamais actif en environnement local ni pendant les tests : sinon les chiffres
    de fréquentation seraient pollués par le développement.
--}}
@php
    $provider = config('services.analytics.provider');
    $enabled = $provider && app()->environment('production');
@endphp

@if($enabled && $provider === 'plausible' && config('services.analytics.domain'))
    {{-- Sans cookie : aucune bannière de consentement nécessaire. --}}
    <script defer data-domain="{{ config('services.analytics.domain') }}"
            src="https://plausible.io/js/script.js"></script>

@elseif($enabled && $provider === 'matomo' && config('services.analytics.url') && config('services.analytics.site_id'))
    <script>
        var _paq = window._paq = window._paq || [];
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function () {
            var u = @json(rtrim((string) config('services.analytics.url'), '/').'/');
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', @json((string) config('services.analytics.site_id'))]);
            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.async = true; g.src = u + 'matomo.js'; s.parentNode.insertBefore(g, s);
        })();
    </script>

@elseif($enabled && $provider === 'ga4' && config('services.analytics.measurement_id'))
    @php $gaId = config('services.analytics.measurement_id'); @endphp
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        gtag('js', new Date());
        {{-- Adresses IP tronquées : exigé pour un usage sans consentement explicite. --}}
        gtag('config', @json($gaId), { anonymize_ip: true });
    </script>
@endif
