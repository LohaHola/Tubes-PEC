@props(['href', 'label' => 'Kembali'])

<a href="{{ $href }}" class="page-back">
    <span class="page-back-icon" aria-hidden="true">←</span>
    <span>{{ $label }}</span>
</a>
