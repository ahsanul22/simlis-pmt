<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/** Tambah & ubah user oleh Super Admin. */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::SUPER_ADMIN);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->name),
            'username' => strtolower(trim((string) $this->username)),
            'email' => strtolower(trim((string) $this->email)),
            'nip' => trim((string) $this->nip) ?: null,
            'phone' => trim((string) $this->phone) ?: null,
            'branch_id' => $this->branch_id ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $user = $this->editedUser();
        // Unik termasuk akun yang sudah dihapus (soft delete) karena constraint DB juga begitu.
        $unique = fn (string $column) => Rule::unique('users', $column)->ignore($user?->id);

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9._]+$/', $unique('username')],
            'email' => ['required', 'string', 'email:rfc', 'max:255', $unique('email')],
            'nip' => ['nullable', 'string', 'max:30', $unique('nip')],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'role' => ['required', new Enum(UserRole::class)],
            'branch_id' => [
                Rule::requiredIf($this->role === UserRole::PIC_BRANCH->value),
                'nullable', 'integer', Rule::exists('branches', 'id'),
            ],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->editedUser();

            if (! $user || $validator->errors()->isNotEmpty()) {
                return;
            }

            $losesSuperAdmin = $user->role === UserRole::SUPER_ADMIN
                && ($this->role !== UserRole::SUPER_ADMIN->value || ! $this->is_active);

            if ($user->is($this->user()) && $losesSuperAdmin) {
                $validator->errors()->add(
                    $this->is_active ? 'role' : 'is_active',
                    'Anda tidak dapat mengubah role atau menonaktifkan akun Anda sendiri.'
                );

                return;
            }

            if ($losesSuperAdmin && $user->is_active && ! User::otherActiveSuperAdminExists($user)) {
                $validator->errors()->add('role', 'Harus ada minimal satu Super Admin yang aktif.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'nip' => 'NIP / NIPP',
            'phone' => 'no. HP',
            'branch_id' => 'lokasi',
            'is_active' => 'status akun',
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh huruf kecil, angka, titik, dan garis bawah.',
            'username.unique' => 'Username sudah dipakai (termasuk oleh akun yang sudah dihapus).',
            'email.unique' => 'Email sudah dipakai (termasuk oleh akun yang sudah dihapus).',
            'nip.unique' => 'NIP / NIPP sudah terdaftar.',
            'phone.regex' => 'No. HP hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'branch_id.required' => 'Lokasi wajib dipilih untuk PIC Branch.',
        ];
    }

    /** Data siap simpan (password hanya ikut bila diisi; cast "hashed" di model meng-hash otomatis). */
    public function userData(): array
    {
        $data = $this->safe()->except(['password']);

        if ($this->filled('password')) {
            $data['password'] = $this->password;
        }

        return $data;
    }

    private function editedUser(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }
}
