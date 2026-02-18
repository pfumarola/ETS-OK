<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('member'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('codice_fiscale') && trim((string) $this->codice_fiscale) === '') {
            $this->merge(['codice_fiscale' => null]);
        }
    }

    public function rules(): array
    {
        if ($this->user()->hasRole('admin', 'segreteria')) {
            return [
                'member_type_id' => 'sometimes|required|exists:member_types,id',
                'numero_tessera' => 'nullable|integer|min:1|unique:members,numero_tessera,' . $this->route('member')->id,
                'nome' => 'required|string|max:255',
                'cognome' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'codice_fiscale' => 'nullable|string|max:64',
                'data_iscrizione' => 'nullable|date',
                'stato' => 'nullable|in:attivo,sospeso,cessato,aspirante,rigettato,in_ricorso,decesso,dimesso,escluso,moroso',
                'domanda_presentata_at' => 'nullable|date',
                'ammissione_decisa_at' => 'nullable|date',
                'ammissione_esito' => 'nullable|in:accolta,rigettata',
                'rigetto_motivo' => 'nullable|string',
                'rigetto_comunicato_at' => 'nullable|date',
                'ricorso_presentato_at' => 'nullable|date',
                'assemblea_esame_data' => 'nullable|date',
                'data_cessazione' => 'nullable|date',
                'cessazione_causa' => 'nullable|in:decesso,morosita,dimissioni,esclusione',
                'dimissioni_presentate_at' => 'nullable|date',
                'motivo_esclusione' => 'nullable|string',
                'deceduto_at' => 'nullable|date',
                'indirizzo' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:50',
                'note' => 'nullable|string',
            ];
        }

        // Socio che modifica il proprio profilo: solo anagrafica personale
        return [
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'indirizzo' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
        ];
    }
}
