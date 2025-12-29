<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavingsGoalRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtenir les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'current_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|size:7|regex:/^#([A-Fa-f0-9]{6})$/',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Obtenir les messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'objectif est requis.',
            'target_amount.required' => 'Le montant cible est requis.',
            'target_amount.min' => 'Le montant cible doit être d\'au moins 1 FDJ.',
            'deadline.after_or_equal' => 'La date limite doit être aujourd\'hui ou une date future.',
            'color.regex' => 'La couleur doit être au format hexadécimal (#FFFFFF).',
        ];
    }

    /**
     * Préparer les données pour la validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'current_amount' => $this->current_amount ?? 0,
            'color' => $this->color ?? '#10B981',
        ]);
    }
}