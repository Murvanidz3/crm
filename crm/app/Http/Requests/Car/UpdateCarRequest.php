<?php

namespace App\Http\Requests\Car;

use App\Enums\CarStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $car = $this->route('car');
        return $this->user()?->canEditCar($car) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user();
        $rules = [];

        // Admin can update all fields
        if ($user->isAdmin()) {
            $rules = [
                'user_id' => ['sometimes', 'exists:users,id'],
                'vin' => ['required', 'string', 'min:10', 'max:17'],
                'make_model' => ['required', 'string', 'max:100'],
                'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
                'lot_number' => ['nullable', 'string', 'max:50'],
                'auction_name' => ['nullable', 'string', Rule::in(['COPART', 'IAAI', 'MANHEIM'])],
                'auction_location' => ['nullable', 'string', 'max:100'],
                'container_number' => ['nullable', 'string', 'max:50'],
                'status' => ['required', Rule::enum(CarStatus::class)],
                'vehicle_cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
                'shipping_cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
                'additional_cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
                'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
                'purchase_date' => ['nullable', 'date'],
                'client_name' => ['nullable', 'string', 'max:100'],
                'client_phone' => ['nullable', 'string', 'max:20'],
                'client_id_number' => ['nullable', 'string', 'max:20'],
            ];
        }
        // Dealers can only update client info (if not already set)
        else if ($user->isDealer()) {
            $car = $this->route('car');
            
            // Only allow client fields if not already set
            if (empty($car->client_name)) {
                $rules = [
                    'client_name' => ['nullable', 'string', 'max:100'],
                    'client_phone' => ['nullable', 'string', 'max:20'],
                    'client_id_number' => ['nullable', 'string', 'max:20'],
                ];
            }
        }

        return $rules;
    }

    /**
     * Custom attribute names
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
            'auction_location' => 'ლოკაცია',
            'container_number' => 'კონტეინერი',
            'status' => 'სტატუსი',
            'vehicle_cost' => 'ავტომობილის ღირებულება',
            'shipping_cost' => 'ტრანსპორტირების ღირებულება',
            'additional_cost' => 'დამატებითი ხარჯი',
            'paid_amount' => 'გადახდილი თანხა',
            'purchase_date' => 'შეძენის თარიღი',
            'client_name' => 'კლიენტის სახელი',
            'client_phone' => 'კლიენტის ტელეფონი',
            'client_id_number' => 'პირადი ნომერი',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('vin')) {
            $this->merge([
                'vin' => strtoupper(trim($this->vin)),
            ]);
        }
    }
}
