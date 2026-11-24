<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/** Pengaturan sistem: ambang status, jadwal reminder, template email reminder. */
class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::SUPER_ADMIN);
    }

    protected function prepareForValidation(): void
    {
        $days = collect((array) $this->input('reminder_days', []))
            ->filter(fn ($d) => is_numeric($d))
            ->map(fn ($d) => (int) $d)
            ->unique()->sortDesc()->values()->all();

        $this->merge([
            'reminder_days' => $days,
            'mail' => array_map(fn ($v) => is_string($v) ? str_replace("\r\n", "\n", trim($v)) : $v, (array) $this->input('mail', [])),
        ]);
    }

    public function rules(): array
    {
        return [
            'warning' => ['required', 'integer', 'min:2', 'max:730'],
            'critical' => ['required', 'integer', 'min:1', 'lt:warning'],
            'reminder_days' => ['required', 'array', 'min:1', 'max:12'],
            'reminder_days.*' => ['integer', 'min:1', 'max:730'],
            'mail.subject' => ['required', 'string', 'max:150'],
            'mail.greeting' => ['required', 'string', 'max:150'],
            'mail.intro' => ['required', 'string', 'max:1000'],
            'mail.closing' => ['required', 'string', 'max:300'],
        ];
    }

    public function attributes(): array
    {
        return [
            'warning' => 'ambang "Akan Habis"',
            'critical' => 'ambang "Kritis"',
            'reminder_days' => 'jadwal pengingat',
            'reminder_days.*' => 'hari pengingat',
            'mail.subject' => 'subjek email',
            'mail.greeting' => 'sapaan',
            'mail.intro' => 'paragraf pembuka',
            'mail.closing' => 'penutup',
        ];
    }

    public function messages(): array
    {
        return [
            'critical.lt' => 'Ambang "Kritis" harus lebih kecil dari ambang "Akan Habis".',
            'reminder_days.required' => 'Minimal satu jadwal pengingat.',
            'reminder_days.min' => 'Minimal satu jadwal pengingat.',
        ];
    }
}
