<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'table_id' => ['required', 'exists:tables,id'],
            'observations' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(){
        return [
            'items.required' => 'O pedido deve conter pelo menos um item',
            'items.min' => 'O pedido deve conter pelo menos um item',
            'items.*.product_id.required' => 'O item deve conter um produto',
            'items.*.product_id.exists' => 'O item deve conter um produto válido',
            'items.*.quantity.required' => 'O item deve conter uma quantidade',
            'items.*.quantity.integer' => 'A quantidade deve ser um número inteiro',
            'items.*.quantity.min' => 'A quantidade deve ser pelo menos 1',
        ];
    }
}
