@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false, 'rows' => 3])

@php $id = $attributes->get('id', $name); @endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($required) required @endif
              {{ $attributes->except('id')->class(['form-control', 'border-rose-400' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @enderror
</div>
