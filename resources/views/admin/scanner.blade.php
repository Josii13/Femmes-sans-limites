<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scanner QR — {{ $event->title }}</title>
    @vite(['resources/css/app.css'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body style="background:#1A0A10;min-height:100vh;" class="flex flex-col">

<div class="flex-1 flex flex-col max-w-md mx-auto w-full p-4">

    {{-- Header --}}
    <div class="text-center pt-6 pb-4">
        <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-12 mx-auto mb-3" style="filter:brightness(0) invert(1) opacity(0.8);">
        <h1 class="text-white font-bold text-lg">Scanner d'accès</h1>
        <p class="text-sm mt-1 truncate" style="color:rgba(255,255,255,0.5);">{{ $event->title }}</p>
        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.35);">{{ $event->event_date->format('d M Y à H:i') }}</p>
    </div>

    {{-- Scanner --}}
    <div class="rounded-2xl overflow-hidden my-4" style="border:2px solid rgba(217,30,110,0.4);">
        <div id="qr-reader" class="w-full"></div>
    </div>

    {{-- Result --}}
    <div id="result" class="rounded-2xl p-5 text-center hidden">
        <div id="result-icon" class="text-5xl mb-3"></div>
        <h2 id="result-name" class="text-xl font-bold text-white mb-1"></h2>
        <p id="result-message" class="text-sm" style="color:rgba(255,255,255,0.7);"></p>
    </div>

    {{-- Manual input --}}
    <div class="mt-4">
        <p class="text-xs text-center mb-3" style="color:rgba(255,255,255,0.4);">Ou saisir manuellement</p>
        <div class="flex gap-2">
            <input id="manual-token" type="text" placeholder="Token QR code..." class="form-input flex-1 text-sm" style="background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.15);color:white;">
            <button onclick="verifyToken(document.getElementById('manual-token').value)" class="btn-rose text-sm px-4 py-2.5">OK</button>
        </div>
    </div>

    <a href="{{ route('admin.registrations.index', $event) }}" class="block text-center text-sm mt-6 mb-4" style="color:rgba(255,255,255,0.4);">← Retour aux inscriptions</a>
</div>

<script>
let lastScanned = null;
let isProcessing = false;

const html5QrCode = new Html5Qrcode("qr-reader");

html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 280, height: 280 } },
    (decodedText) => {
        if (decodedText !== lastScanned && !isProcessing) {
            lastScanned = decodedText;
            verifyToken(decodedText);
        }
    },
    () => {}
).catch(err => {
    document.getElementById('qr-reader').innerHTML = '<p style="color:rgba(255,255,255,0.5);text-align:center;padding:40px;font-size:14px;">Caméra non disponible.<br>Utilisez la saisie manuelle ci-dessous.</p>';
});

function verifyToken(token) {
    if (!token || isProcessing) return;
    isProcessing = true;

    fetch('{{ route('admin.scanner.verify', $event) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ token })
    })
    .then(r => r.json())
    .then(data => {
        const result = document.getElementById('result');
        const icon   = document.getElementById('result-icon');
        const name   = document.getElementById('result-name');
        const msg    = document.getElementById('result-message');

        result.classList.remove('hidden');

        if (data.valid) {
            result.style.background = 'rgba(5,150,105,0.2)';
            result.style.border     = '2px solid rgba(5,150,105,0.5)';
            icon.textContent = '✅';
            name.textContent = data.name;
            msg.textContent  = data.message;
            navigator.vibrate && navigator.vibrate([100, 50, 100]);
        } else if (data.already) {
            result.style.background = 'rgba(124,58,237,0.2)';
            result.style.border     = '2px solid rgba(124,58,237,0.5)';
            icon.textContent = '⚠️';
            name.textContent = data.name;
            msg.textContent  = data.at ? data.message + ' (à ' + data.at + ')' : data.message;
        } else {
            result.style.background = 'rgba(220,38,38,0.2)';
            result.style.border     = '2px solid rgba(220,38,38,0.5)';
            icon.textContent = '❌';
            name.textContent = data.name ?? 'Inconnu';
            msg.textContent  = data.message;
            navigator.vibrate && navigator.vibrate([300]);
        }

        setTimeout(() => {
            result.classList.add('hidden');
            lastScanned  = null;
            isProcessing = false;
        }, 4000);
    });
}
</script>
</body>
</html>
