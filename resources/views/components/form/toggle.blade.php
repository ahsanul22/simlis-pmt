{{-- Switch boolean. Hidden input 0 memastikan nilai false ikut terkirim. --}}
@props(['name', 'label', 'checked' => false, 'hint' => null])

<label class="flex cursor-pointer items-start gap-3" x-data="{ on: @js((bool) old($name, $checked)) }">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" class="sr-only" x-model="on">
    <span class="relative mt-0.5 inline-flex h-5 w-9 shrink-0 rounded-full transition" :class="on ? 'bg-brand-600' : 'bg-slate-300 dark:bg-slate-700'">
        <span class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition" :class="on && 'translate-x-4'"></span>
    </span>
    <span>
        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</span>
        @if ($hint)<span class="block text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</span>@endif
    </span>
</label>
