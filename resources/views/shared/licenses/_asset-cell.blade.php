{{-- Kolom "aset" untuk lisensi: perangkat (model + SN) atau produk software. Variabel: $license --}}
@if ($license->device)
    <div class="flex items-center gap-2">
        <x-icon name="server-stack" class="h-4 w-4 shrink-0 text-slate-400" />
        <div class="min-w-0">
            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $license->device->model->name }}</p>
            <p class="font-mono text-xs text-slate-500 dark:text-slate-400">SN {{ $license->device->serial_number }}</p>
        </div>
    </div>
@elseif ($license->softwareProduct)
    <div class="flex items-center gap-2">
        <x-icon name="code-bracket" class="h-4 w-4 shrink-0 text-slate-400" />
        <div class="min-w-0">
            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $license->softwareProduct->name }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $license->seats ? $license->seats.' seat' : 'Software' }}</p>
        </div>
    </div>
@else
    <span class="text-slate-400">-</span>
@endif
