<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetActivePrizesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->can('manage-prizes') || $user->can('manage-users'));
    }

    public function rules(): array
    {
        return [];
    }
}
