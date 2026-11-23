{{-- Daftar lampiran. Variabel: $attachments. Lampiran sensitif (foto license key) disembunyikan dari role tanpa izin license.view_key. --}}
@php
    $attachments = auth()->user()->hasPermission('license.view_key')
        ? $attachments
        : $attachments->reject(fn ($a) => $a->category->isSensitive());
@endphp
@if ($attachments->isEmpty())
    <x-empty-state icon="paper-clip" title="Belum ada dokumen">Unggah sertifikat lisensi, foto stiker SN, PO, invoice, atau BAST.</x-empty-state>
@else
    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
        @foreach ($attachments as $file)
            <li class="flex items-center gap-3 px-5 py-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800">
                    <x-icon name="document-text" class="h-5 w-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ $file->original_name }}</p>
                    <p class="text-xs text-slate-500">{{ $file->category->label() }} · {{ number_format(($file->size ?? 0) / 1024, 0, ',', '.') }} KB · {{ $file->uploader?->name }}</p>
                </div>
            </li>
        @endforeach
    </ul>
@endif
