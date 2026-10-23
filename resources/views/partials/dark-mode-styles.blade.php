<style>
    /* ==========================================================================
       MOBELI MASTER DARK MODE STYLESYSTEM
       Fixes all color clashes, invisible text, and glaring pastel backgrounds.
       ========================================================================== */

    html.dark {
        color-scheme: dark;
    }

    /* 1. Global Page Background & Base Text */
    html.dark body {
        background-color: #0b1120 !important;
        color: #f1f5f9 !important;
    }

    /* Keep explicit white text white */
    html.dark .text-white {
        color: #ffffff !important;
    }

    /* 2. Main Containers, Cards, Panels, Modals, Navbars */
    html.dark .bg-white,
    html.dark header.bg-white\/95,
    html.dark aside.bg-white,
    html.dark section.bg-white,
    html.dark div.bg-white,
    html.dark nav.bg-white {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
    }

    /* Secondary / Subdued Backgrounds */
    html.dark .bg-neutral-50,
    html.dark .bg-slate-50,
    html.dark .bg-gray-50,
    html.dark .bg-zinc-50,
    html.dark .bg-neutral-100,
    html.dark .bg-slate-100,
    html.dark .bg-gray-100,
    html.dark .bg-zinc-100,
    html.dark .bg-neutral-50\/50,
    html.dark .bg-neutral-50\/70,
    html.dark .bg-slate-50\/50,
    html.dark .bg-slate-100\/70,
    html.dark .bg-neutral-100\/70,
    html.dark .bg-neutral-100\/80 {
        background-color: #0f172a !important;
    }

    /* 3. Text Colors (Fix invisible dark-on-dark text) */
    html.dark .text-neutral-900,
    html.dark .text-slate-900,
    html.dark .text-gray-900,
    html.dark .text-zinc-900,
    html.dark .text-black {
        color: #f8fafc !important;
    }

    html.dark .text-neutral-800,
    html.dark .text-slate-800,
    html.dark .text-gray-800,
    html.dark .text-zinc-800 {
        color: #f1f5f9 !important;
    }

    html.dark .text-neutral-700,
    html.dark .text-slate-700,
    html.dark .text-gray-700,
    html.dark .text-zinc-700,
    html.dark .text-neutral-600,
    html.dark .text-slate-600,
    html.dark .text-gray-600,
    html.dark .text-zinc-600 {
        color: #cbd5e1 !important;
    }

    html.dark .text-neutral-500,
    html.dark .text-slate-500,
    html.dark .text-gray-500,
    html.dark .text-zinc-500 {
        color: #94a3b8 !important;
    }

    html.dark .text-neutral-400,
    html.dark .text-slate-400,
    html.dark .text-gray-400,
    html.dark .text-zinc-400,
    html.dark .text-neutral-300,
    html.dark .text-slate-300 {
        color: #64748b !important;
    }

    /* 4. Borders & Dividers */
    html.dark .border-neutral-100,
    html.dark .border-neutral-200,
    html.dark .border-neutral-300,
    html.dark .border-slate-100,
    html.dark .border-slate-200,
    html.dark .border-slate-300,
    html.dark .border-gray-100,
    html.dark .border-gray-200,
    html.dark .border-gray-300,
    html.dark .border-neutral-200\/60,
    html.dark .border-neutral-200\/80 {
        border-color: #334155 !important;
    }

    html.dark .divide-neutral-100 > :not([hidden]) ~ :not([hidden]),
    html.dark .divide-neutral-200 > :not([hidden]) ~ :not([hidden]),
    html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
    html.dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: #334155 !important;
    }

    html.dark .border-dashed {
        border-color: #475569 !important;
    }

    /* 5. Semantic Badges, Alerts & Accent Surfaces */
    /* Emerald / Green */
    html.dark .bg-emerald-50,
    html.dark .bg-emerald-100,
    html.dark .bg-emerald-50\/30,
    html.dark .bg-emerald-50\/40,
    html.dark .bg-emerald-50\/50,
    html.dark .bg-emerald-50\/60,
    html.dark .bg-emerald-50\/70,
    html.dark .bg-emerald-50\/80,
    html.dark .bg-emerald-100\/70 {
        background-color: rgba(16, 185, 129, 0.16) !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }
    html.dark .text-emerald-700,
    html.dark .text-emerald-800,
    html.dark .text-emerald-900,
    html.dark .text-emerald-950 {
        color: #34d399 !important;
    }
    html.dark .border-emerald-100,
    html.dark .border-emerald-200,
    html.dark .border-emerald-200\/80 {
        border-color: rgba(16, 185, 129, 0.35) !important;
    }

    /* Blue / Primary */
    html.dark .bg-blue-50,
    html.dark .bg-blue-100,
    html.dark .bg-blue-50\/40,
    html.dark .bg-blue-50\/50,
    html.dark .bg-blue-50\/60,
    html.dark .bg-blue-50\/80 {
        background-color: rgba(59, 130, 246, 0.16) !important;
        border-color: rgba(59, 130, 246, 0.35) !important;
    }
    html.dark .text-blue-700,
    html.dark .text-blue-800,
    html.dark .text-blue-900,
    html.dark .text-blue-950 {
        color: #60a5fa !important;
    }
    html.dark .border-blue-100,
    html.dark .border-blue-200,
    html.dark .border-blue-200\/80 {
        border-color: rgba(59, 130, 246, 0.35) !important;
    }

    /* Orange / Restock */
    html.dark .bg-orange-50,
    html.dark .bg-orange-100,
    html.dark .bg-orange-50\/40,
    html.dark .bg-orange-50\/50,
    html.dark .bg-orange-50\/60,
    html.dark .bg-orange-50\/70,
    html.dark .bg-orange-50\/80 {
        background-color: rgba(249, 115, 22, 0.18) !important;
        border-color: rgba(249, 115, 22, 0.35) !important;
    }
    html.dark .text-orange-600,
    html.dark .text-orange-700,
    html.dark .text-orange-800,
    html.dark .text-orange-900,
    html.dark .text-orange-950 {
        color: #fb923c !important;
    }
    html.dark .border-orange-100,
    html.dark .border-orange-200,
    html.dark .border-orange-200\/80 {
        border-color: rgba(249, 115, 22, 0.35) !important;
    }

    /* Amber / Caution */
    html.dark .bg-amber-50,
    html.dark .bg-amber-100,
    html.dark .bg-amber-50\/40,
    html.dark .bg-amber-50\/50,
    html.dark .bg-amber-50\/80 {
        background-color: rgba(245, 158, 11, 0.18) !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
    }
    html.dark .text-amber-600,
    html.dark .text-amber-700,
    html.dark .text-amber-800,
    html.dark .text-amber-900,
    html.dark .text-amber-950 {
        color: #fbbf24 !important;
    }
    html.dark .border-amber-100,
    html.dark .border-amber-200,
    html.dark .border-amber-200\/80 {
        border-color: rgba(245, 158, 11, 0.35) !important;
    }

    /* Red / Danger */
    html.dark .bg-red-50,
    html.dark .bg-red-100,
    html.dark .bg-red-50\/40,
    html.dark .bg-red-50\/50,
    html.dark .bg-red-50\/80 {
        background-color: rgba(239, 68, 68, 0.18) !important;
        border-color: rgba(239, 68, 68, 0.35) !important;
    }
    html.dark .text-red-600,
    html.dark .text-red-700,
    html.dark .text-red-800,
    html.dark .text-red-900,
    html.dark .text-red-950 {
        color: #f87171 !important;
    }
    html.dark .border-red-100,
    html.dark .border-red-200 {
        border-color: rgba(239, 68, 68, 0.35) !important;
    }

    /* 6. Form Controls (Inputs, Selects, Textareas) */
    html.dark input,
    html.dark select,
    html.dark textarea {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    html.dark input::placeholder,
    html.dark textarea::placeholder {
        color: #64748b !important;
    }

    html.dark select option {
        background-color: #0f172a !important;
        color: #f8fafc !important;
    }

    html.dark input:focus,
    html.dark select:focus,
    html.dark textarea:focus {
        background-color: #0f172a !important;
        border-color: #3b82f6 !important;
    }

    html.dark input[type="date"] {
        color-scheme: dark;
    }

    html.dark input[type="date"]::-webkit-calendar-picker-indicator {
        filter: brightness(0) invert(1) !important;
        opacity: 0.95 !important;
        cursor: pointer;
        padding: 2px;
        border-radius: 4px;
        transition: all 0.2s ease-in-out;
    }

    html.dark input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1 !important;
        transform: scale(1.15);
        filter: brightness(0) saturate(100%) invert(60%) sepia(85%) saturate(1800%) hue-rotate(185deg) brightness(105%) contrast(105%) !important;
    }

    /* 7. Tables */
    html.dark table thead,
    html.dark table thead th {
        background-color: #0f172a !important;
        color: #94a3b8 !important;
        border-color: #334155 !important;
    }

    html.dark table tbody tr,
    html.dark table td {
        border-color: #334155 !important;
    }

    html.dark table tfoot,
    html.dark table tfoot tr,
    html.dark table tfoot td {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }

    /* 8. Interactive & Hover States */
    html.dark tr.hover\:bg-neutral-50:hover,
    html.dark tr.hover\:bg-slate-50:hover,
    html.dark .hover\:bg-neutral-50:hover,
    html.dark .hover\:bg-slate-50:hover,
    html.dark .hover\:bg-gray-50:hover,
    html.dark .hover\:bg-neutral-100:hover,
    html.dark .hover\:bg-slate-100:hover,
    html.dark .hover\:bg-neutral-200:hover,
    html.dark .hover\:bg-slate-200:hover {
        background-color: #1e293b !important;
    }

    html.dark .hover\:bg-white:hover {
        background-color: #334155 !important;
    }

    html.dark .hover\:bg-emerald-50:hover {
        background-color: rgba(16, 185, 129, 0.25) !important;
    }

    html.dark .hover\:bg-red-50:hover {
        background-color: rgba(239, 68, 68, 0.25) !important;
    }

    /* 9. Layout-Specific Polish */
    /* Storefront Hero */
    html.dark section.bg-gradient-to-b {
        background: transparent !important;
    }

    /* Cashier Receipt Preview Modal */
    html.dark #receiptModal .bg-white {
        background-color: #0f172a !important;
        color: #f1f5f9 !important;
        border: 1px solid #334155 !important;
    }

    html.dark #receiptModal .border-dashed {
        border-color: #334155 !important;
    }

    html.dark #receiptModal .text-slate-900 {
        color: #f8fafc !important;
    }

    html.dark #receiptModal .text-slate-500 {
        color: #94a3b8 !important;
    }

    html.dark #receiptModal .bg-slate-200 {
        background-color: #334155 !important;
        color: #f8fafc !important;
    }

    /* POS Fast Cash Buttons */
    html.dark #cashInputSection .bg-slate-100 {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border: 1px solid #334155 !important;
    }
    html.dark #cashInputSection .bg-slate-100:hover {
        background-color: #334155 !important;
    }

    /* SPK Step Badges */
    html.dark .bg-slate-900.text-white.font-mono {
        background-color: #334155 !important;
        border: 1px solid #475569 !important;
    }

    /* Nested Ideal Boxes */
    html.dark .bg-emerald-50\/70 .bg-white,
    html.dark .bg-orange-50\/70 .bg-white {
        background-color: #0f172a !important;
    }

    /* Chart Bar Track in Owner Dashboard */
    html.dark .bg-blue-50.h-2\.5 {
        background-color: #0f172a !important;
        border: 1px solid #334155 !important;
    }

    /* Scrollbars */
    html.dark ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    html.dark ::-webkit-scrollbar-track {
        background: #0b1120;
    }
    html.dark ::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 9999px;
    }
    html.dark ::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
</style>
