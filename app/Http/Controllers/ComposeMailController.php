<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Pagina per inviare email manuali (testo o HTML) tramite il mailer configurato (es. SMTP).
 * Accesso: admin e segreteria.
 */
class ComposeMailController extends Controller
{
    private const BODY_MAX_BYTES = 262144; // 256 KiB

    public function __construct()
    {
        $this->middleware('role:admin,segreteria');
    }

    public function create()
    {
        $from = config('mail.from', []);

        return Inertia::render('Mail/Compose', [
            'mailer' => config('mail.default'),
            'fromPreview' => [
                'address' => $from['address'] ?? '',
                'name' => $from['name'] ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string|max:2000',
            'cc' => 'nullable|string|max:2000',
            'bcc' => 'nullable|string|max:2000',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:' . self::BODY_MAX_BYTES,
            'is_html' => 'nullable|boolean',
        ]);

        $this->assertTokensValidForField($validated['to'], 'to');
        $this->assertTokensValidForField($validated['cc'] ?? '', 'cc');
        $this->assertTokensValidForField($validated['bcc'] ?? '', 'bcc');

        $to = $this->parseEmailList($validated['to']);
        $cc = $this->parseEmailList($validated['cc'] ?? '');
        $bcc = $this->parseEmailList($validated['bcc'] ?? '');

        if ($to === []) {
            throw ValidationException::withMessages([
                'to' => 'Indica almeno un indirizzo email valido in A.',
            ]);
        }

        $subject = $validated['subject'];
        $body = $validated['body'];
        $isHtml = $request->boolean('is_html');

        try {
            if ($isHtml) {
                Mail::html($body, function ($message) use ($to, $cc, $bcc, $subject): void {
                    $message->to($to)->subject($subject);
                    if ($cc !== []) {
                        $message->cc($cc);
                    }
                    if ($bcc !== []) {
                        $message->bcc($bcc);
                    }
                });
            } else {
                Mail::raw($body, function ($message) use ($to, $cc, $bcc, $subject): void {
                    $message->to($to)->subject($subject);
                    if ($cc !== []) {
                        $message->cc($cc);
                    }
                    if ($bcc !== []) {
                        $message->bcc($bcc);
                    }
                });
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('mail.compose')
                ->with('flash', ['type' => 'error', 'message' => 'Errore nell\'invio: ' . $e->getMessage()])
                ->withInput($request->except(['body']));
        }

        $count = count($to) + count($cc) + count($bcc);

        return redirect()->route('mail.compose')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Email inviata correttamente' . ($count > 1 ? " ({$count} indirizzi totali tra A, CC e BCC)." : '.'),
            ]);
    }

    /**
     * @return list<string>
     */
    private function parseEmailList(string $raw): array
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $parts = preg_split('/[\n,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $email = strtolower(trim($part));
            if ($email === '') {
                continue;
            }
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out[] = $email;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Ogni token non vuoto nella stringa deve essere un indirizzo valido.
     *
     * @throws ValidationException
     */
    private function assertTokensValidForField(string $raw, string $field): void
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $parts = preg_split('/[\n,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $invalid = [];
        foreach ($parts as $part) {
            $token = trim($part);
            if ($token === '') {
                continue;
            }
            if (! filter_var(strtolower($token), FILTER_VALIDATE_EMAIL)) {
                $invalid[] = $token;
            }
        }
        if ($invalid !== []) {
            $sample = implode(', ', array_slice($invalid, 0, 3)) . (count($invalid) > 3 ? '…' : '');
            throw ValidationException::withMessages([
                $field => 'Indirizzi non validi: ' . $sample,
            ]);
        }
    }
}
