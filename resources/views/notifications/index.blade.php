@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<x-page-header title="Notifikasi" subtitle="Pengingat lisensi dan update pengajuan perpanjangan">
    <x-slot:actions>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-secondary"><x-icon name="check-circle" class="h-4 w-4" /> Tandai semua dibaca</button>
        </form>
    </x-slot:actions>
</x-page-header>

<div class="card overflow-hidden">
    @forelse ($notifications as $n)
        <a href="{{ route('notifications.show', $n->id) }}"
           class="flex gap-4 border-b border-slate-100 px-5 py-4 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/40 {{ $n->read_at ? '' : 'bg-brand-50/40 dark:bg-brand-500/5' }}">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $n->read_at ? 'bg-slate-100 text-slate-400 dark:bg-slate-800' : 'bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300' }}">
                <x-icon :name="$n->data['icon'] ?? 'bell'" class="h-4 w-4" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm {{ $n->read_at ? 'text-slate-700 dark:text-slate-300' : 'font-semibold text-slate-900 dark:text-white' }}">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $n->data['message'] ?? '' }}</p>
            </div>
            <span class="shrink-0 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
        </a>
    @empty
        <x-empty-state icon="bell" title="Belum ada notifikasi">Pengingat lisensi akan muncul di sini mulai H-90 sebelum masa berlaku habis.</x-empty-state>
    @endforelse
    @if ($notifications->total() > min(PER_PAGE_OPTIONS))
        <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
