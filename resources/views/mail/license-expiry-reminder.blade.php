{{--
    Email reminder lisensi. Teks sapaan/pembuka/penutup dari Pengaturan Super Admin ($text),
    tabel lisensi otomatis. Markdown: jangan beri indentasi pada baris konten (akan jadi blok kode).
    Teks dari pengaturan di-escape lalu baris baru → <br>, supaya tidak bisa menyisipkan HTML.
--}}
@php $paragraph = fn (string $s) => nl2br(e($s), false); @endphp
<x-mail::message>
# Pengingat Masa Berlaku Lisensi

{!! $paragraph($text['greeting']) !!}

{!! $paragraph($text['intro']) !!}

<x-mail::table>
| Lisensi | Lokasi | Habis | Sisa |
|:--------|:-------|:------|-----:|
@foreach ($licenses as $l)
| [{{ $l->code }}]({{ $linkFor($l) }})<br>{{ $l->vendor?->name }} · {{ $l->name }}@if ($l->device?->serial_number)<br><small>SN {{ $l->device->serial_number }}</small>@endif | {{ $l->branch?->name ?? '-' }} | {{ tgl($l->end_date) }} | **{{ $l->days_remaining === 0 ? 'Hari ini' : $l->days_remaining.' hari' }}** |
@endforeach
</x-mail::table>

<x-mail::button :url="$url">
Buka di {{ config('app.name') }}
</x-mail::button>

{!! $paragraph($text['closing']) !!}

<small>Email ini dikirim otomatis oleh {{ config('app.name') }} — Sistem Monitoring Lisensi TI. Tidak perlu membalas email ini.</small>
</x-mail::message>
