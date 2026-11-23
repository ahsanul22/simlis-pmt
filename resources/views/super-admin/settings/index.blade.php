@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<x-page-header title="Pengaturan" subtitle="Ambang status masa berlaku dan jadwal pengingat" />

<form method="POST" action="{{ route('super-admin.settings.update') }}" class="max-w-3xl space-y-6"
      x-data="{
          warning: {{ $thresholds['warning'] }},
          critical: {{ $thresholds['critical'] }},
          days: @js($reminderDays),
          newDay: '',
          add() { const d = parseInt(this.newDay); if (d > 0 && !this.days.includes(d)) { this.days.push(d); this.days.sort((a, b) => b - a); } this.newDay = ''; },
      }">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header"><h2 class="card-title">Ambang Status Lisensi</h2></div>
        <div class="card-body space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="form-label">"Akan Habis" jika sisa ≤ (hari)</label>
                    <input type="number" name="warning" x-model.number="warning" min="1" class="form-control">
                </div>
                <div>
                    <label class="form-label">"Kritis" jika sisa ≤ (hari)</label>
                    <input type="number" name="critical" x-model.number="critical" min="1" class="form-control">
                    <p class="form-error" x-show="critical >= warning" x-cloak>Harus lebih kecil dari ambang "Akan Habis".</p>
                </div>
            </div>
            {{-- Pratinjau skala --}}
            <div>
                <p class="mb-2 text-xs font-medium text-slate-500">Pratinjau</p>
                <div class="flex h-8 overflow-hidden rounded-lg text-[11px] font-semibold text-white">
                    <div class="flex w-16 items-center justify-center bg-rose-500">Expired</div>
                    <div class="flex items-center justify-center bg-orange-500" :style="`flex: ${critical}`" x-text="`Kritis ≤ ${critical}h`"></div>
                    <div class="flex items-center justify-center bg-amber-500" :style="`flex: ${Math.max(warning - critical, 1)}`" x-text="`Akan habis ≤ ${warning}h`"></div>
                    <div class="flex flex-1 items-center justify-center bg-emerald-500" style="min-width: 5rem">Aktif</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Jadwal Pengingat (Email & Notifikasi)</h2></div>
        <div class="card-body space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400">Pengingat dikirim ke Admin TI dan PIC branch terkait pada hari-hari berikut sebelum lisensi berakhir.</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="(d, i) in days" :key="d">
                    <span class="badge gap-2 bg-brand-50 px-3 py-1.5 text-sm text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300">
                        <span x-text="`H-${d}`"></span>
                        <input type="hidden" name="reminder_days[]" :value="d">
                        <button type="button" @click="days.splice(i, 1)" class="opacity-60 hover:opacity-100"><x-icon name="x-mark" class="h-3.5 w-3.5" /></button>
                    </span>
                </template>
            </div>
            <div class="flex max-w-xs gap-2">
                <input type="number" x-model="newDay" @keydown.enter.prevent="add()" min="1" placeholder="Hari, mis. 45" class="form-control">
                <button type="button" class="btn btn-secondary" @click="add()">Tambah</button>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </div>
</form>
@endsection
