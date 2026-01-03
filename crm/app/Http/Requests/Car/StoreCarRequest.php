<?php

namespace App\Http\Requests\Car;

use App\Enums\CarStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'vin' => ['required', 'string', 'min:10', 'max:17'],
            'make_model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'lot_number' => ['nullable', 'string', 'max:50'],
            'auction_name' => ['nullable', 'string', Rule::in(['COPART', 'IAAI', 'MANHEIM'])],
            'status' => ['nullable', Rule::enum(CarStatus::class)],
            'vehicle_cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'client_name' => ['nullable', 'string', 'max:100'],
            'client_phone' => ['nullable', 'string', 'max:20'],
            'client_id_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Custom attribute names (Georgian)
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'მფლობელი',
            'vin' => 'VIN კოდი',
            'make_model' => 'მარკა/მოდელი',
            'year' => 'წელი',
            'lot_number' => 'ლოტის ნომერი',
            'auction_name' => 'აუქციონი',
            'status' => 'სტატუსი',
            'vehicle_cost' => 'ავტომობილის ღირებულება',
            'shipping_cost' => 'ტრანსპორტირების ღირებულება',
            'client_name' => 'კლიენტის სახელი',
            'client_phone' => 'კლიენტის ტელეფონი',
            'client_id_number' => 'პირადი ნომერი',
        ];
    }

    /**
     * Custom error messages (Georgian)
     */
    public function messages(): array
    {
        return [
            'vin.required' => 'VIN კოდი აუცილებელია',
            'vin.min' => 'VIN კოდი უნდა შეიცავდეს მინიმუმ :min სიმბოლოს',
            'make_model.required' => 'მარკა და მოდელი აუცილებელია',
            'user_id.exists' => 'მითითებული მფლობელი არ არსებობს',
            'year.integer' => 'წელი უნდა იყოს რიცხვი',
            'vehicle_cost.numeric' => 'ღირებულება უნდა იყოს რიცხვი',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize VIN code
        if ($this->has('vin')) {
            $this->merge([
                'vin' => strtoupper(trim($this->vin)),
            ]);
        }

        // Set default status if not provided
        if (!$this->has('status') || empty($this->status)) {
            $this->merge(['status' => CarStatus::PURCHASED->value]);
        }
    }
}
