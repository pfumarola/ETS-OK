<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\ExpenseRefund;
use App\Models\Incasso;
use App\Models\Member;
use App\Models\Receipt;
use App\Models\Settings;
use Illuminate\Support\Facades\Storage;

/**
 * Genera ricevute (PDF) per incassi (quote/donazioni) e rimborsi.
 */
class ReceiptService
{
    /**
     * Genera ricevuta per un incasso. Crea record Receipt e salva PDF su storage.
     */
    public function generateForIncasso(Incasso $incasso): Receipt
    {
        if ($incasso->receipt) {
            return $incasso->receipt;
        }

        $member = $incasso->member;
        $recipientName = $incasso->donor_name;
        if (! $member && ! $recipientName) {
            throw new \InvalidArgumentException('Per emettere ricevuta l\'incasso deve avere un socio/donatore in anagrafica o un donatore inserito a mano.');
        }

        $number = $this->nextReceiptNumber($incasso->paid_at);
        $issuedAt = $incasso->paid_at->toDateString();
        if ($incasso->type === Incasso::TYPE_DONAZIONE) {
            $causale = $incasso->description ?: Settings::get('causale_default_donazione', 'Erogazione liberale');
        } else {
            $baseQuota = Settings::get('causale_default_quota', 'Quota associativa');
            $causale = $incasso->description ?: ($incasso->subscription ? $baseQuota . ' ' . $incasso->subscription->year : $baseQuota);
        }

        $receipt = Receipt::create([
            'member_id' => $member?->id,
            'recipient_name' => $recipientName ?: null,
            'receivable_type' => Incasso::class,
            'receivable_id' => $incasso->id,
            'number' => $number,
            'issued_at' => $issuedAt,
            'type' => 'liberale',
        ]);

        $isDonazione = $incasso->type === Incasso::TYPE_DONAZIONE;
        $path = $this->savePdf($receipt, $member, $recipientName, $incasso->amount, $causale, $issuedAt, $isDonazione);
        $receipt->update(['file_path' => $path]);
        $incasso->update(['receipt_issued_at' => $incasso->paid_at]);

        return $receipt->fresh();
    }

    /**
     * Genera ricevuta per un rimborso spese.
     */
    public function generateForExpenseRefund(ExpenseRefund $refund): Receipt
    {
        if ($refund->receipt) {
            return $refund->receipt;
        }

        $member = $refund->member;
        $number = $this->nextReceiptNumber($refund->refund_date);
        $issuedAt = $refund->refund_date->format('Y-m-d');
        $causale = Settings::get('causale_default_rimborso', 'Rimborso spese');

        $receipt = Receipt::create([
            'member_id' => $member->id,
            'receivable_type' => ExpenseRefund::class,
            'receivable_id' => $refund->id,
            'number' => $number,
            'issued_at' => $issuedAt,
            'type' => 'rimborso',
        ]);

        $path = $this->savePdf($receipt, $member, null, $refund->total, $causale, $issuedAt, false);
        $receipt->update(['file_path' => $path]);
        $refund->update(['receipt_id' => $receipt->id, 'status' => 'stampata']);

        return $receipt->fresh();
    }

    /**
     * Rigenera il PDF della ricevuta mantenendo numero e dati esistenti (utile dopo modifiche al template).
     */
    public function regenerate(Receipt $receipt): Receipt
    {
        $receipt->load(['member', 'receivable' => fn ($q) => $q->with('subscription')]);
        $receivable = $receipt->receivable;
        if (! $receivable) {
            throw new \InvalidArgumentException('Ricevuta senza incasso o rimborso collegato.');
        }

        $member = $receipt->member;
        $recipientName = $receipt->recipient_name;
        $issuedAt = $receipt->issued_at->format('Y-m-d');

        if ($receivable instanceof Incasso) {
            $amount = $receivable->amount;
            if ($receivable->type === Incasso::TYPE_DONAZIONE) {
                $causale = $receivable->description ?: Settings::get('causale_default_donazione', 'Erogazione liberale');
            } else {
                $baseQuota = Settings::get('causale_default_quota', 'Quota associativa');
                $causale = $receivable->description ?: ($receivable->subscription ? $baseQuota . ' ' . $receivable->subscription->year : $baseQuota);
            }
        } elseif ($receivable instanceof ExpenseRefund) {
            $amount = $receivable->total;
            $causale = Settings::get('causale_default_rimborso', 'Rimborso spese');
        } else {
            throw new \InvalidArgumentException('Tipo di ricevuta non supportato per la rigenerazione.');
        }

        $isDonazione = $receivable instanceof Incasso && $receivable->type === Incasso::TYPE_DONAZIONE;
        $path = $this->savePdf($receipt, $member, $recipientName, $amount, $causale, $issuedAt, $isDonazione);
        $receipt->update(['file_path' => $path]);

        return $receipt->fresh();
    }

    /**
     * Numero progressivo annuale per ricevute (es. 2026/0001).
     */
    private function nextReceiptNumber($date): string
    {
        $year = $date instanceof \DateTimeInterface ? $date->format('Y') : date('Y', strtotime($date));
        $last = Receipt::whereYear('issued_at', $year)->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->number, -4) + 1 : 1;
        return $year . '/' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  Member|null  $member  Socio/donatore in anagrafica (null se donatore inserito a mano)
     * @param  string|null  $recipientName  Nome destinatario per ricevuta senza socio (donatore a mano)
     * @param  bool  $isDonazione  Se true usa il template RICEVUTA DONAZIONE con diciture art. 83 ETS
     */
    private function savePdf(Receipt $receipt, ?Member $member, ?string $recipientName, $amount, string $causale, string $issuedAt, bool $isDonazione = false): string
    {
        $logoDataUri = Attachment::logoDataUriForPdf();

        $viewData = [
            'receipt' => $receipt,
            'member' => $member,
            'recipient_name' => $recipientName,
            'amount' => $amount,
            'causale' => $causale,
            'issued_at' => $issuedAt,
            'nome_associazione' => Settings::get('nome_associazione', 'Associazione - Ente del Terzo Settore'),
            'indirizzo_associazione' => Settings::get('indirizzo_associazione', ''),
            'email_associazione' => Settings::get('email_associazione', ''),
            'pec_associazione' => Settings::get('pec_associazione', ''),
            'codice_fiscale_associazione' => Settings::get('codice_fiscale_associazione', ''),
            'partita_iva_associazione' => Settings::get('partita_iva_associazione', ''),
            'logo_data_uri' => $logoDataUri,
        ];

        if ($isDonazione) {
            $dataRunts = Settings::get('data_iscrizione_runts', '');
            try {
                $viewData['data_iscrizione_runts'] = $dataRunts ? \Carbon\Carbon::parse($dataRunts)->format('d/m/Y') : '';
            } catch (\Throwable $e) {
                $viewData['data_iscrizione_runts'] = $dataRunts;
            }
            $viewData['legale_rappresentante'] = Settings::get('legale_rappresentante_associazione', '');
            $viewData['ets_è_odv'] = (bool) Settings::get('ets_è_odv', false);
            $viewData['luogo_emissione'] = Settings::get('luogo_emissione_ricevute', '') ?: Settings::get('indirizzo_associazione', '');
            $template = 'receipts.template_donazione';
        } else {
            $template = 'receipts.template';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($template, $viewData);
        $year = date('Y', strtotime($issuedAt));
        $dir = "media/receipts/{$year}";
        Storage::disk('local')->makeDirectory($dir);
        $path = "{$dir}/{$receipt->id}.pdf";
        Storage::disk('local')->put($path, $pdf->output());
        return $path;
    }

}
