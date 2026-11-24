@extends('layouts.app')

@section('title', 'Pengaturan')

@php
    $saved = [
        'warning' => (int) $thresholds['warning'],
        'critical' => (int) $thresholds['critical'],
        'days' => array_values(array_map('intval', $reminderDays)),
        'mail' => $mail,
    ];
    $current = [
        'warning' => (int) old('warning', $saved['warning']),
        'critical' => (int) old('critical', $saved['critical']),
        'days' => array_values(array_map('intval', (array) old('reminder_days', $saved['days']))),
        'mail' => array_merge($saved['mail'], (array) old('mail', [])),
    ];

    // Bagian yang otomatis terbuka: yang punya error validasi.
    $open = array_keys(array_filter([
        'status' => $errors->hasAny(['warning', 'critical']),
        'schedule' => $errors->has('reminder_days') || $errors->has('reminder_days.*'),
        'template' => $errors->hasAny(['mail.subject', 'mail.greeting', 'mail.intro', 'mail.closing']),
        'test' => $errors->has('test_email'),
    ]));

    $sample = [
        'values' => [
            '{nama}' => auth()->user()->name,
            '{jumlah}' => (string) max($sampleLicenses->count(), 1),
            '{judul}' => $sampleLicenses->count() > 1 ? $sampleLicenses->count().' lisensi akan segera habis masa berlakunya' : 'Lisensi akan habis dalam 14 hari',
            '{aplikasi}' => config('app.name'),
        ],
        'rows' => $sampleLicenses->map(fn ($l) => [
            'code' => $l->code,
            'name' => trim(($l->vendor?->name ? $l->vendor->name.' · ' : '').$l->name),
            'branch' => $l->branch?->name ?? '-',
            'end' => tgl($l->end_date),
            'left' => $l->days_remaining === 0 ? 'Hari ini' : $l->days_remaining.' hari',
        ])->values(),
    ];
@endphp

@section('content')
<x-page-header title="Pengaturan" subtitle="Klik setiap bagian untuk membuka dan mengubahnya" />

<div x-data="settingsPage(@js(['saved' => $saved, 'current' => $current, 'open' => $open, 'defaults' => $mailDefaults, 'sample' => $sample]))"
     class="max-w-4xl space-y-4 pb-24">

    <form id="settings-form" method="POST" action="{{ route('super-admin.settings.update') }}" novalidate
          class="space-y-4" @submit="submitting = true">
        @csrf
        @method('PUT')

        {{-- ============================================================ Ambang status --}}
        <x-accordion-section id="status" title="Ambang Status Lisensi" icon="adjustments" tone="amber"
                             description="Menentukan kapan lisensi diberi label “Akan Habis” dan “Kritis” di seluruh dashboard, tabel, dan laporan.">
            <x-slot:summary>
                <span x-text="`Akan habis ≤ ${warning} hari · Kritis ≤ ${critical} hari`"></span>
            </x-slot:summary>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="warning" class="form-label">"Akan Habis" jika sisa ≤</label>
                    <div class="relative">
                        <input type="number" id="warning" name="warning" x-model.number="warning" min="2" max="730" class="form-control pr-14">
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">hari</span>
                    </div>
                    @error('warning')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="critical" class="form-label">"Kritis" jika sisa ≤</label>
                    <div class="relative">
                        <input type="number" id="critical" name="critical" x-model.number="critical" min="1" class="form-control pr-14">
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">hari</span>
                    </div>
                    <p class="form-error" x-show="critical >= warning" x-cloak>Harus lebih kecil dari ambang "Akan Habis".</p>
                    @error('critical')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-5">
                <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">Pratinjau skala</p>
                <div class="flex h-8 overflow-hidden rounded-lg text-[11px] font-semibold text-white">
                    <div class="flex w-16 items-center justify-center bg-rose-500">Expired</div>
                    <div class="flex items-center justify-center bg-orange-500 px-1" :style="`flex: ${Math.max(critical, 1)}`" x-text="`Kritis ≤ ${critical}h`"></div>
                    <div class="flex items-center justify-center bg-amber-500 px-1" :style="`flex: ${Math.max(warning - critical, 1)}`" x-text="`Akan habis ≤ ${warning}h`"></div>
                    <div class="flex flex-1 items-center justify-center bg-emerald-500" style="min-width: 5rem">Aktif</div>
                </div>
            </div>
        </x-accordion-section>

        {{-- ============================================================ Jadwal pengingat --}}
        <x-accordion-section id="schedule" title="Jadwal Pengingat" icon="calendar" tone="brand">
            <x-slot:summary>
                <span x-text="days.length ? `${days.length} jadwal · ${days.map(d => 'H-' + d).join(', ')} · dicek tiap hari {{ config('app.reminder_time') }}` : 'Belum ada jadwal'"></span>
            </x-slot:summary>

            <p class="mb-5 text-sm text-slate-500 dark:text-slate-400">
                Sistem mengecek setiap hari pukul <strong class="text-slate-700 dark:text-slate-200">{{ config('app.reminder_time') }}</strong>.
                Saat sisa masa berlaku mencapai salah satu hari di bawah, pengingat (email + lonceng) dikirim <strong class="text-slate-700 dark:text-slate-200">sekali</strong>
                ke Admin TI, Manajemen, dan PIC Branch lokasi terkait.
            </p>

            {{-- Linimasa --}}
            <div class="relative mb-6 mt-8 h-1.5 rounded-full bg-gradient-to-r from-emerald-400 via-amber-400 to-rose-500" x-show="days.length" aria-hidden="true">
                <template x-for="d in days" :key="'t' + d">
                    <span class="absolute top-1/2 -translate-x-1/2 -translate-y-1/2" :style="`left: ${100 - (d / Math.max(...days)) * 92}%`">
                        <span class="block h-3.5 w-3.5 rounded-full border-2 border-white bg-slate-700 shadow dark:border-slate-900 dark:bg-slate-200"></span>
                        <span class="absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap text-[11px] font-medium text-slate-500 dark:text-slate-400" x-text="'H-' + d"></span>
                    </span>
                </template>
                <span class="absolute -bottom-5 right-0 text-[11px] text-slate-400">Habis</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <template x-for="(d, i) in days" :key="d">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 py-1.5 pl-3 pr-2 text-sm font-medium text-brand-700 ring-1 ring-inset ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-400/30">
                        <span x-text="`H-${d}`"></span>
                        <input type="hidden" name="reminder_days[]" :value="d">
                        <button type="button" @click="days.splice(i, 1)" class="rounded p-0.5 opacity-60 hover:bg-brand-100 hover:opacity-100 dark:hover:bg-brand-500/20" :aria-label="`Hapus H-${d}`">
                            <x-icon name="x-mark" class="h-3.5 w-3.5" />
                        </button>
                    </span>
                </template>
                <p x-show="!days.length" x-cloak class="text-sm text-rose-600 dark:text-rose-400">Belum ada jadwal — tambahkan minimal satu.</p>

                <div class="flex items-center gap-1.5">
                    <input type="number" x-model="newDay" @keydown.enter.prevent="addDay()" min="1" max="730" placeholder="mis. 45"
                           class="form-control w-24 py-1.5 text-sm" aria-label="Hari pengingat baru">
                    <button type="button" class="btn btn-secondary btn-sm" @click="addDay()"><x-icon name="plus" class="h-4 w-4" /> Tambah</button>
                </div>
            </div>
            @error('reminder_days')<p class="form-error">{{ $message }}</p>@enderror
            @error('reminder_days.*')<p class="form-error">{{ $message }}</p>@enderror
        </x-accordion-section>

        {{-- ============================================================ Template email --}}
        <x-accordion-section id="template" title="Template Email Pengingat" icon="envelope" tone="violet">
            <x-slot:summary>
                <span x-text="'Subjek: ' + fill(mail.subject)"></span>
            </x-slot:summary>

            @unless ($mailConfigured)
                <div class="mb-5 flex gap-2 rounded-lg bg-amber-50 px-3 py-2.5 text-sm text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                    <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Akun pengirim belum diisi (<code>MAIL_USERNAME</code> / <code>MAIL_PASSWORD</code> di file <code>.env</code>). Email belum bisa terkirim.</span>
                </div>
            @endunless

            <div class="grid gap-6 lg:grid-cols-5">
                {{-- Isian --}}
                <div class="space-y-4 lg:col-span-2">
                    <div>
                        <p class="mb-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">Klik untuk menyisipkan ke kolom yang sedang aktif:</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($placeholders as $code => $desc)
                                <button type="button" @click="insert(@js($code))" title="{{ $desc }}"
                                        class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-700 transition hover:bg-violet-100 hover:text-violet-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-violet-500/20 dark:hover:text-violet-300">{{ $code }}</button>
                            @endforeach
                        </div>
                    </div>

                    @foreach ([
                        'subject' => ['Subjek email', 'input', 150],
                        'greeting' => ['Sapaan', 'input', 150],
                        'intro' => ['Paragraf pembuka', 'textarea', 1000],
                        'closing' => ['Penutup / tanda tangan', 'textarea', 300],
                    ] as $field => [$label, $type, $max])
                        <div>
                            <div class="flex items-baseline justify-between">
                                <label for="mail_{{ $field }}" class="form-label">{{ $label }}</label>
                                <span class="text-[11px] text-slate-400" x-text="`${(mail.{{ $field }} || '').length}/{{ $max }}`"></span>
                            </div>
                            @if ($type === 'input')
                                <input type="text" id="mail_{{ $field }}" name="mail[{{ $field }}]" x-model="mail.{{ $field }}" x-ref="mail_{{ $field }}"
                                       @focus="focused = '{{ $field }}'" maxlength="{{ $max }}"
                                       class="form-control @error('mail.'.$field) border-rose-400 @enderror">
                            @else
                                <textarea id="mail_{{ $field }}" name="mail[{{ $field }}]" x-model="mail.{{ $field }}" x-ref="mail_{{ $field }}"
                                          @focus="focused = '{{ $field }}'" maxlength="{{ $max }}" rows="{{ $field === 'intro' ? 4 : 2 }}"
                                          class="form-control @error('mail.'.$field) border-rose-400 @enderror"></textarea>
                            @endif
                            @error('mail.'.$field)<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach

                    <button type="button" @click="resetTemplate()" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400">
                        <x-icon name="arrow-path" class="h-3.5 w-3.5" /> Kembalikan ke teks bawaan
                    </button>
                </div>

                {{-- Pratinjau --}}
                <div class="lg:col-span-3">
                    <div class="lg:sticky lg:top-20">
                        <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">Pratinjau email</p>
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-950">
                            <div class="space-y-1 border-b border-slate-200 bg-white px-4 py-3 text-xs dark:border-slate-700 dark:bg-slate-900">
                                <p><span class="inline-block w-12 text-slate-400">Dari</span> <span class="text-slate-700 dark:text-slate-300">{{ config('mail.from.name') }} &lt;{{ $mailFrom ?: 'belum diatur' }}&gt;</span></p>
                                <p><span class="inline-block w-12 text-slate-400">Subjek</span> <span class="font-semibold text-slate-900 dark:text-white" x-text="fill(mail.subject)"></span></p>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3 rounded-lg bg-white p-5 text-sm text-slate-700 shadow-sm dark:bg-slate-900 dark:text-slate-300">
                                    <p class="text-base font-bold text-slate-900 dark:text-white">Pengingat Masa Berlaku Lisensi</p>
                                    <p class="whitespace-pre-line" x-text="fill(mail.greeting)"></p>
                                    <p class="whitespace-pre-line" x-text="fill(mail.intro)"></p>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-xs">
                                            <thead class="border-b border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                                <tr><th class="py-1.5 pr-2 font-semibold">Lisensi</th><th class="py-1.5 pr-2 font-semibold">Lokasi</th><th class="py-1.5 pr-2 font-semibold">Habis</th><th class="py-1.5 text-right font-semibold">Sisa</th></tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                <template x-for="r in sample.rows" :key="r.code">
                                                    <tr>
                                                        <td class="py-1.5 pr-2"><span class="font-medium text-brand-600 dark:text-brand-400" x-text="r.code"></span><span class="block text-slate-500 dark:text-slate-400" x-text="r.name"></span></td>
                                                        <td class="py-1.5 pr-2" x-text="r.branch"></td>
                                                        <td class="whitespace-nowrap py-1.5 pr-2" x-text="r.end"></td>
                                                        <td class="whitespace-nowrap py-1.5 text-right font-semibold" x-text="r.left"></td>
                                                    </tr>
                                                </template>
                                                <tr x-show="!sample.rows.length"><td colspan="4" class="py-3 text-center text-slate-400">[ tabel lisensi otomatis ]</td></tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="py-1 text-center">
                                        <span class="inline-block rounded-md bg-slate-800 px-4 py-2 text-xs font-semibold text-white dark:bg-slate-700">Buka di {{ config('app.name') }}</span>
                                    </div>
                                    <p class="whitespace-pre-line" x-text="fill(mail.closing)"></p>
                                    <p class="border-t border-slate-100 pt-3 text-[11px] text-slate-400 dark:border-slate-800">Email ini dikirim otomatis oleh {{ config('app.name') }} — Sistem Monitoring Lisensi TI. Tidak perlu membalas email ini.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-accordion-section>
    </form>

    {{-- ============================================================ Uji kirim (form terpisah) --}}
    <x-accordion-section id="test" title="Uji Kirim Email" icon="paper-airplane" tone="emerald">
        <x-slot:summary>Kirim contoh email pengingat untuk memastikan email sampai ke inbox</x-slot:summary>

        <form method="POST" action="{{ route('super-admin.settings.test-mail') }}" class="space-y-3">
            @csrf
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Memakai template yang <strong class="text-slate-700 dark:text-slate-200">sudah tersimpan</strong> dan {{ $sampleLicenses->count() ?: 'beberapa' }} lisensi terdekat sebagai contoh.
                Tidak memengaruhi jadwal pengingat asli.
            </p>
            <div x-show="dirty" x-cloak class="flex gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" /> Ada perubahan yang belum disimpan — simpan dulu agar email contoh memakai teks terbaru.
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                <div class="flex-1">
                    <input type="email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" required aria-label="Email tujuan"
                           class="form-control @error('test_email') border-rose-400 @enderror" placeholder="email@contoh.com">
                    @error('test_email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-primary"><x-icon name="paper-airplane" class="h-4 w-4" /> Kirim Email Contoh</button>
            </div>
        </form>
    </x-accordion-section>

    {{-- ============================================================ Bar simpan (muncul saat ada perubahan) --}}
    <div x-show="dirty" x-cloak
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0"
         class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 lg:left-64">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="flex max-w-4xl items-center gap-3 py-3">
            <span class="flex h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
            <p class="flex-1 text-sm text-slate-600 dark:text-slate-300">Ada perubahan yang belum disimpan</p>
            <button type="button" class="btn btn-secondary btn-sm" @click="resetAll()">Batalkan</button>
            <button type="submit" form="settings-form" class="btn btn-primary btn-sm" :disabled="critical >= warning || !days.length">Simpan Pengaturan</button>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settingsPage', (cfg) => ({
            ...JSON.parse(JSON.stringify(cfg.current)),
            saved: JSON.stringify(cfg.saved),
            open: [...cfg.open],
            sample: cfg.sample,
            newDay: '',
            focused: 'intro',
            submitting: false,

            init() {
                window.addEventListener('beforeunload', (e) => {
                    if (this.dirty && !this.submitting) { e.preventDefault(); e.returnValue = ''; }
                });
            },

            // ----- akordeon
            isOpen(id) { return this.open.includes(id); },
            toggle(id) { this.open = this.isOpen(id) ? this.open.filter((x) => x !== id) : [...this.open, id]; },

            // ----- perubahan
            get dirty() {
                return JSON.stringify({ warning: +this.warning, critical: +this.critical, days: this.days, mail: this.mail }) !== this.saved;
            },
            resetAll() {
                const s = JSON.parse(this.saved);
                Object.assign(this, { warning: s.warning, critical: s.critical, days: s.days, mail: s.mail });
            },

            // ----- jadwal
            addDay() {
                const d = parseInt(this.newDay, 10);
                if (d > 0 && d <= 730 && !this.days.includes(d)) {
                    this.days = [...this.days, d].sort((a, b) => b - a);
                }
                this.newDay = '';
            },

            // ----- template
            fill(text) {
                return Object.entries(this.sample.values).reduce((s, [k, v]) => s.split(k).join(v), text || '');
            },
            insert(code) {
                const field = this.focused;
                const el = this.$refs['mail_' + field];
                const value = this.mail[field] || '';
                const start = el?.selectionStart ?? value.length;
                const end = el?.selectionEnd ?? value.length;
                this.mail[field] = value.slice(0, start) + code + value.slice(end);
                this.$nextTick(() => { el?.focus(); el?.setSelectionRange(start + code.length, start + code.length); });
            },
            resetTemplate() { this.mail = { ...cfg.defaults }; },
        }));
    });
</script>
@endpush
