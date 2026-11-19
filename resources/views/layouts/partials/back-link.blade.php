@php
    $backHref = $href ?? route('home');
    $backLabel = $label ?? 'Kembali';
@endphp
<a href="{{ $backHref }}" class="page-back">
    <span class="page-back-icon" aria-hidden="true">←</span>
    <span>{{ $backLabel }}</span>
</a>