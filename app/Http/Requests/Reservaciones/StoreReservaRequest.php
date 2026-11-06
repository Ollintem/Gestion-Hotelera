<?php

namespace App\Http\Requests\Reservaciones;

use App\Models\Habitacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'habitacion_id' => ['required', 'exists:habitaciones,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $habitacion = Habitacion::with('tipoHabitacion')->find($this->integer('habitacion_id'));
                $capacidad = (int) ($habitacion?->tipoHabitacion?->capacidad ?? 0);
                $huespedes = (int) ($this->input('guests') ?? 1);

                if ($capacidad > 0 && $huespedes > $capacidad) {
                    $validator->errors()->add(
                        'guests',
                        "La habitación admite hasta {$capacidad} huéspedes.",
                    );
                }
            },
        ];
    }
}
