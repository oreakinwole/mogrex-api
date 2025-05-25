<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'type' => 'required|in:credit,debit',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Amount must be greater than 0',
            'amount.max' => 'Amount exceeds maximum limit',
            'type.required' => 'Transaction type is required',
            'type.in' => 'Transaction type must be either credit or debit',
        ];
    }
}
