<?php

namespace App\Services;

use App\Models\Convocazione;
use App\Models\Member;
use Illuminate\Support\Collection;

class ConvocazioneRecipientService
{
    /**
     * @return array{
     *   rows: array<int, array{id:int, nome:string, email:?string}>,
     *   emails: list<string>,
     *   total:int,
     *   with_email:int,
     *   without_email:int
     * }
     */
    public function recipientsForTipo(string $tipo): array
    {
        $members = match ($tipo) {
            Convocazione::TIPO_CONSIGLIO => $this->consiglioDirettivoMembers(),
            default => $this->assembleaMembers(),
        };

        $rows = $members->map(function (Member $member): array {
            $email = $this->normalizeEmail($member->email);

            return [
                'id' => (int) $member->id,
                'nome' => trim(($member->cognome ?? '') . ' ' . ($member->nome ?? '')),
                'email' => $email,
            ];
        })->values();

        $emails = $rows->pluck('email')->filter()->values()->all();

        return [
            'rows' => $rows->all(),
            'emails' => $emails,
            'total' => $rows->count(),
            'with_email' => count($emails),
            'without_email' => $rows->count() - count($emails),
        ];
    }

    /**
     * @return Collection<int, Member>
     */
    private function assembleaMembers(): Collection
    {
        return Member::query()
            ->where('stato', 'attivo')
            ->orderBy('cognome')
            ->orderBy('nome')
            ->get(['id', 'nome', 'cognome', 'email']);
    }

    /**
     * @return Collection<int, Member>
     */
    private function consiglioDirettivoMembers(): Collection
    {
        return Member::query()
            ->whereHas('incarichi.caricaSociale.organo', function ($q) {
                $q->where('slug', 'consiglio_direttivo');
            })
            ->orderBy('cognome')
            ->orderBy('nome')
            ->distinct('members.id')
            ->get(['members.id', 'members.nome', 'members.cognome', 'members.email']);
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = trim((string) $email);
        if ($email === '') {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : null;
    }
}
