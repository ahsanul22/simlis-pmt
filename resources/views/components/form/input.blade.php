{{-- <x-form.input name="serial_number" label="Serial Number" :value="$device->serial_number" required hint="..." /> --}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])

@php $id = $attributes->get('id', str_replace(['[', ']'], ['_', ''], $name)); @endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
           value="{{ $type === 'password' ? '' : old($name, $value instanceof \Carbon\CarbonInterface ? $value->format('Y-m-d') : $value) }}"
           @if ($required) required @endif
           {{ $attributes->except('id')->class(['form-control', 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' => $errors->has($name)]) }}>
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @enderror
</div>
