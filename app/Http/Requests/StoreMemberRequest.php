<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Member::class);
    }

    public function rules(): array
    {
        return [
            'member_type_id' => 'required|exists:member_types,id',
            'numero_tessera' => 'nullable|integer|min:1|unique:members,numero_tessera',
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'codice_fiscale' => 'required|string|size:16',
            'data_iscrizione' => 'nullable|date',
            'stato' => 'nullable|in:attivo,sospeso,cessato,aspirante',
            'presentare_domanda' => 'nullable|boolean',
            'indirizzo' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'note' => 'nullable|string',
        ];
    }
}
