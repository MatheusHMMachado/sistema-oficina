{{-- CSS/JS principais: Tailwind local (funciona offline) + CDN reserva + Font Awesome + Alpine --}}
@php
    $tailwindLocal = public_path('vendor/tailwindcss.js');
    $tailwindSrc = file_exists($tailwindLocal)
        ? '/vendor/tailwindcss.js'
        : 'https://cdn.tailwindcss.com/3.4.17';
@endphp
<script src="{{ $tailwindSrc }}"></script>
@include('partials.brand-theme')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.1/cdn.min.js" defer crossorigin="anonymous"></script>
