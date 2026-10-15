<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use App\Support\MailSamples;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Journal des envois : répondre à « est-ce que ça marche ? ».
 *
 * La question n'est pas tant « combien d'emails sont partis » que « lesquels
 * ne partent plus ». D'où le tableau par type avec la date du dernier envoi :
 * une carte d'anniversaire silencieuse depuis dix jours s'y voit tout de
 * suite, alors qu'elle se noierait dans une liste chronologique.
 */
class MailLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = MailLog::query()
            ->when($request->filled('statut'), function ($q) use ($request) {
                $statut = $request->string('statut')->toString();

                return $statut === 'sans-confirmation'
                    ? $q->stale()
                    : $q->where('status', $statut);
            })
            ->when($request->filled('type'), fn ($q) => $q->where('mailable', 'like', '%'.$request->string('type')->toString()))
            ->when(! $request->boolean('tests'), fn ($q) => $q->real())
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('admin.emails.logs', [
            'logs' => $logs,
            'health' => $this->health(),
            'byType' => $this->byType(),
        ]);
    }

    /** Les trois chiffres qui disent si le système respire. */
    private function health(): array
    {
        $since = now()->subDay();

        return [
            'last_sent' => MailLog::real()->where('status', 'sent')->latest('sent_at')->first()?->sent_at,
            'sent_24h' => MailLog::real()->where('status', 'sent')->where('created_at', '>=', $since)->count(),
            'failed_24h' => MailLog::real()->where('created_at', '>=', $since)
                ->where(fn ($q) => $q->where('status', 'failed')->orWhere(fn ($s) => $s->stale()))
                ->count(),
            'total' => MailLog::real()->count(),
        ];
    }

    /**
     * Un état par type d'email, y compris ceux qui ne sont jamais partis :
     * c'est précisément l'absence qu'on cherche à repérer.
     *
     * @return array<int, array<string, mixed>>
     */
    private function byType(): array
    {
        $logs = MailLog::real()
            ->selectRaw('mailable, count(*) as total')
            ->selectRaw("sum(case when status = 'sent' then 1 else 0 end) as sent")
            ->selectRaw("sum(case when status = 'failed' then 1 else 0 end) as failed")
            ->selectRaw('max(sent_at) as last_sent')
            ->where('created_at', '>=', now()->subDays(90))
            ->groupBy('mailable')
            ->get()
            ->keyBy('mailable');

        $rows = [];

        foreach (MailSamples::catalog() as $item) {
            $class = $item['mailable'];

            // Deux entrées du catalogue peuvent partager un mailable (relance
            // et échéance d'adhésion) : une seule ligne ici, sinon le même
            // compteur s'afficherait deux fois.
            if (isset($rows[$class])) {
                continue;
            }

            $row = $logs->get($class);

            $rows[$class] = [
                'label' => $item['label'],
                'group' => $item['group'],
                'total' => (int) ($row->total ?? 0),
                'sent' => (int) ($row->sent ?? 0),
                'failed' => (int) ($row->failed ?? 0),
                'last_sent' => isset($row->last_sent) && $row->last_sent
                    ? Carbon::parse($row->last_sent)
                    : null,
            ];
        }

        return array_values($rows);
    }
}
