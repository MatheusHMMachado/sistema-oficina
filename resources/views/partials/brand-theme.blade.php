@php
    $brand = $brand ?? \App\Models\ConfiguracaoSistema::atual();
    $primary = $brand->cor_primaria ?? '#FF6B35';
    $secondary = $brand->cor_secundaria ?? '#FF8C42';
    $isDark = ($brand->tema ?? 'light') === 'dark';
@endphp
<script>
    function applyTailwindBrandConfig() {
        if (typeof tailwind === 'undefined') {
            return false;
        }
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'orange-primary': @json($primary),
                        'orange-secondary': @json($secondary),
                        'black-primary': '#1A1A1A',
                        'gray-dark': '#2D2D2D'
                    }
                }
            }
        };
        return true;
    }
    if (!applyTailwindBrandConfig()) {
        document.addEventListener('DOMContentLoaded', function () {
            if (!applyTailwindBrandConfig()) {
                var s = document.querySelector('script[src*="tailwindcss"]');
                if (s) {
                    s.addEventListener('load', applyTailwindBrandConfig);
                }
            }
        });
    }
</script>
<style>
    :root {
        --brand-primary: {{ $primary }};
        --brand-secondary: {{ $secondary }};
        --brand-primary-rgb: {{ implode(', ', sscanf($primary, '#%02x%02x%02x') ?: [255, 107, 53]) }};
        --surface: {{ $isDark ? '#0f1419' : '#f5f7fa' }};
        --surface-2: {{ $isDark ? '#1a2332' : '#ffffff' }};
        --text-main: {{ $isDark ? '#e8eef7' : '#111827' }};
        --text-muted: {{ $isDark ? '#9aa8bc' : '#6b7280' }};
        --border-soft: {{ $isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)' }};
    }

    body.theme-app {
        background: {{ $isDark ? 'linear-gradient(135deg, #0b1220 0%, #152033 100%)' : 'linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%)' }};
        background-attachment: fixed;
        color: var(--text-main);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(var(--brand-primary-rgb), 0.25);
    }
    .btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 8px 22px rgba(var(--brand-primary-rgb), 0.35);
    }

    .header-gradient {
        background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
    }

    .sidebar-gradient {
        background: {{ $isDark ? 'linear-gradient(180deg, #0b1220 0%, #152033 100%)' : 'linear-gradient(180deg, #1A1A1A 0%, #2D2D2D 50%, #1A1A1A 100%)' }};
    }

    .card-modern, .card-glass {
        background: {{ $isDark ? 'rgba(26, 35, 50, 0.92)' : 'rgba(255, 255, 255, 0.92)' }};
        backdrop-filter: blur(14px);
        border: 1px solid var(--border-soft);
        color: var(--text-main);
    }

    .nav-item::before {
        background: linear-gradient(180deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
    }

    .theme-dark .bg-white { background-color: #1a2332 !important; }
    .theme-dark .text-gray-900, .theme-dark .text-gray-800 { color: #e8eef7 !important; }
    .theme-dark .text-gray-700, .theme-dark .text-gray-600, .theme-dark .text-gray-500 { color: #9aa8bc !important; }
    .theme-dark .border-gray-200, .theme-dark .border-gray-100, .theme-dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: rgba(255,255,255,0.08) !important;
    }
    .theme-dark .bg-gray-50, .theme-dark .bg-gray-100 { background-color: #121a27 !important; }
    .theme-dark .shadow, .theme-dark .shadow-lg, .theme-dark .shadow-xl { box-shadow: 0 10px 30px rgba(0,0,0,0.35) !important; }

    [x-cloak] { display: none !important; }
</style>
