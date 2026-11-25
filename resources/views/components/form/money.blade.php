{{--
    Input nominal rupiah dengan pemisah ribuan otomatis (20000000 → 20.000.000).
    <x-form.money name="estimated_cost" label="Estimasi biaya" :value="$renewal->estimated_cost" />
    Yang dikirim ke server tetap angka polos (hidden input), tampilan berformat hanya di layar.
--}}
@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false, 'placeholder' => '0'])

@php
    $id = $attributes->get('id', str_replace(['[', ']'], ['_', ''], $name));
    $raw = old($name, $value);
    $raw = is_numeric($raw) ? (string) (int) round((float) $raw) : '';
@endphp

<div x-data="money(@js($raw))">
    @if ($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-500 dark:text-slate-400">Rp</span>
        <input type="text" id="{{ $id }}" inputmode="numeric" autocomplete="off" placeholder="{{ $placeholder }}"
               x-init="$el.value = display" @input="input($event)"
               @if ($required) required @endif
               {{ $attributes->except('id')->class(['form-control pl-10', 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' => $errors->has($name)]) }}>
    </div>
    <input type="hidden" name="{{ $name }}" :value="raw" value="{{ $raw }}">
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @enderror
</div>
