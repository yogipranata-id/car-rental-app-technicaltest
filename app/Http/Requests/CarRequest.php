<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // All authenticated users can manage cars per MVP assumptions
    }

    public function rules(): array
    {
        $carId = $this->route('car') ? $this->route('car')->id : null;

        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'license_plate' => ['required', 'string', 'max:30', 'unique:cars,license_plate,' . $carId],
            'daily_rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
