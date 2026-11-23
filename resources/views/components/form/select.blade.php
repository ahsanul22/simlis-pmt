{{--
    <x-form.select name="branch_id" label="Lokasi" :options="$branches->pluck('name','id')" :value="$x->branch_id" placeholder="Pilih lokasi" />
    Atau isi <option> sendiri lewat slot.
--}}
@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false])

@php
    $id = $attributes->get('id', $name);
    $selected = old($name, $value instanceof \BackedEnum ? $value->value : $value);
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif
            {{ $attributes->except('id')->class(['form-control', 'border-rose-400' => $errors->has($name)]) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) $selected === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @enderror
</div>
