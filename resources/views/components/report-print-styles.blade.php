{{-- resources/views/components/report-print-styles.blade.php --}}
@props([
    'orientation' => 'portrait',
])

<style>
    /*
    |--------------------------------------------------------------------------
    | Shared report screen typography
    |--------------------------------------------------------------------------
    |
    | Reports are reading and reference documents rather than compact admin
    | screens. Increase the common Tailwind typography classes modestly, but
    | only inside a report-content wrapper.
    |
    | Each report Blade should wrap its report body as:
    |
    | <div class="py-6 report-content">
    |     ...
    | </div>
    |
    */

    .report-content {
        font-size: 1.0625rem;
    }

    .report-content .text-xs {
        font-size: 0.8125rem;
        line-height: 1.2rem;
    }

    .report-content .text-sm {
        font-size: 0.9375rem;
        line-height: 1.45rem;
    }

    .report-content .text-base {
        font-size: 1.0625rem;
        line-height: 1.6rem;
    }

    .report-content .text-lg {
        font-size: 1.1875rem;
        line-height: 1.75rem;
    }

    /*
     * Markdown content is often the main readable content within a report.
     * Make it match the report body rather than inheriting smaller admin text.
     */
    .report-content .markdown-content,
    .report-content .markdown-content p,
    .report-content .markdown-content li,
    .report-content .markdown-content td,
    .report-content .markdown-content th,
    .report-content .markdown-content blockquote {
        font-size: 0.9375rem;
        line-height: 1.5rem;
    }

    .report-content .markdown-content h1 {
        font-size: 1.5rem;
        line-height: 1.9rem;
    }

    .report-content .markdown-content h2 {
        font-size: 1.3125rem;
        line-height: 1.75rem;
    }

    .report-content .markdown-content h3 {
        font-size: 1.125rem;
        line-height: 1.6rem;
    }

    .report-content .markdown-content h4,
    .report-content .markdown-content h5,
    .report-content .markdown-content h6 {
        font-size: 1rem;
        line-height: 1.5rem;
    }

    @media print {
        @page {
            size: A4 {{ $orientation === 'landscape' ? 'landscape' : 'portrait' }};
            margin: 10mm;
        }

        /*
        * Keep the compact report-selection metadata on one line where possible.
        * The Cashbook report normally has Scope, Legal Entity, and Date Range;
        * a fourth Bank Account field is accommodated automatically.
        */
        .report-selection-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            column-gap: 16px !important;
            row-gap: 5px !important;
        }

        html,
        body {
            font-size: 10px !important;
            line-height: 1.25 !important;
            color: #000 !important;
            background: #fff !important;
        }

        .print-hide,
        form,
        button {
            display: none !important;
        }

        a {
            color: #000 !important;
            text-decoration: none !important;
        }

        /*
         * The report screen typography above deliberately improves on-screen
         * reading. The following print overrides remain compact for A4 output.
         */
        .report-content {
            font-size: 10px !important;
        }

        .report-content .text-xs {
            font-size: 8.5px !important;
            line-height: 1.2 !important;
        }

        .report-content .text-sm {
            font-size: 10px !important;
            line-height: 1.25 !important;
        }

        .report-content .text-base {
            font-size: 11px !important;
            line-height: 1.18 !important;
        }

        .report-content .text-lg {
            font-size: 12px !important;
            line-height: 1.2 !important;
        }

        /*
         * The overall report, category cards, and long item content
         * must be able to continue naturally across printed pages.
         */
        .report-category-card,
        .report-item,
        .report-long-section,
        .report-section-content,
        .markdown-content,
        .markdown-content p,
        .markdown-content ul,
        .markdown-content ol,
        .markdown-content li {
            break-inside: auto !important;
            page-break-inside: auto !important;
        }

        /*
         * Keep a heading with the beginning of the content below it
         * where there is enough room, but permit long text to continue.
         */
        .report-parent-heading,
        .report-category-heading,
        .report-item-heading,
        .report-section-heading,
        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            break-after: avoid-page;
            page-break-after: avoid;
        }

        .report-section-heading + .report-section-content {
            break-before: avoid-page;
            page-break-before: avoid;
        }

        /*
         * Keep only genuinely compact blocks together.
         * Do not apply this class to the complete item record.
         */
        .break-inside-avoid {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .report-item.break-inside-avoid {
            break-inside: auto !important;
            page-break-inside: auto !important;
        }

        /*
         * Compact page and container spacing.
         */
        .py-6 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .space-y-6 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 6px !important;
        }

        .space-y-5 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 6px !important;
        }

        .space-y-4 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 5px !important;
        }

        .space-y-3 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 4px !important;
        }

        .space-y-2 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 3px !important;
        }

        .px-4,
        .sm\:px-6,
        .lg\:px-8,
        .xl\:px-10,
        .\32xl\:px-12 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }

        .rounded-lg,
        .rounded-md,
        .rounded-full,
        .sm\:rounded-lg {
            border-radius: 0 !important;
        }

        .px-6 {
            padding-left: 7px !important;
            padding-right: 7px !important;
        }

        .py-5 {
            padding-top: 7px !important;
            padding-bottom: 7px !important;
        }

        .py-4 {
            padding-top: 5px !important;
            padding-bottom: 5px !important;
        }

        .p-4 {
            padding: 7px !important;
        }

        .px-3 {
            padding-left: 5px !important;
            padding-right: 5px !important;
        }

        .py-2 {
            padding-top: 3px !important;
            padding-bottom: 3px !important;
        }

        .pt-4 {
            padding-top: 5px !important;
        }

        .pt-3 {
            padding-top: 4px !important;
        }

        .mt-2 {
            margin-top: 3px !important;
        }

        .mt-1 {
            margin-top: 2px !important;
        }

        .mb-2 {
            margin-bottom: 3px !important;
        }

        .mb-1 {
            margin-bottom: 2px !important;
        }

        .gap-5 {
            gap: 7px !important;
        }

        .gap-4 {
            gap: 5px !important;
        }

        .gap-3 {
            gap: 4px !important;
        }

        .gap-2 {
            gap: 3px !important;
        }

        /*
         * Compact print typography.
         */
        h1,
        .text-2xl {
            font-size: 16px !important;
            line-height: 1.12 !important;
        }

        h2,
        .text-xl {
            font-size: 13px !important;
            line-height: 1.18 !important;
        }

        h3,
        h4,
        h5,
        .text-base {
            font-size: 11px !important;
            line-height: 1.18 !important;
        }

        .text-sm,
        .text-sm.font-semibold {
            font-size: 10px !important;
            line-height: 1.25 !important;
        }

        .text-xs {
            font-size: 8.5px !important;
            line-height: 1.2 !important;
        }

        /*
         * The included Markdown stylesheet may otherwise restore
         * larger paragraph, heading, and list typography.
         */
        .markdown-content,
        .markdown-content p,
        .markdown-content li,
        .markdown-content td,
        .markdown-content th,
        .markdown-content blockquote {
            font-size: 10px !important;
            line-height: 1.25 !important;
        }

        .markdown-content h1 {
            font-size: 14px !important;
        }

        .markdown-content h2 {
            font-size: 12.5px !important;
        }

        .markdown-content h3 {
            font-size: 11.5px !important;
        }

        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            font-size: 11px !important;
        }

        .markdown-content p {
            margin-top: 0 !important;
            margin-bottom: 3px !important;
            orphans: 3;
            widows: 3;
        }

        .markdown-content ul,
        .markdown-content ol {
            margin-top: 2px !important;
            margin-bottom: 3px !important;
            padding-left: 15px !important;
        }

        .markdown-content li {
            margin-bottom: 1px !important;
        }

        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            margin-top: 5px !important;
            margin-bottom: 2px !important;
            line-height: 1.12 !important;
        }

        /*
         * Keep inherently compact or difficult-to-split Markdown objects
         * together, without applying the rule to all bordered report cards.
         */
        .markdown-content table,
        .markdown-content pre,
        .markdown-content blockquote {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /*
         * Compact pills used extensively in item metadata and notes.
         */
        .inline-flex.items-center {
            padding: 1px 4px !important;
        }

        /*
         * Budget Lines report: compact 14-column financial table for A4 landscape.
         *
         * Screen layout intentionally uses a 1456px-wide scrollable table.
         * Print layout must instead fit Category, Total and Jul–Jun within
         * the printable landscape A4 width.
         */
        .budget-lines-print-wrapper {
            overflow: visible !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .budget-lines-table {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }

        /*
         * Hide interactive Actions column in the PDF/printout.
         *
         * This removes the matching colgroup column as well as table header,
         * body and footer cells. The selectors ensure it is removed even where
         * an empty placeholder action cell is used.
         */
        .budget-lines-table .budget-lines-actions-column {
            display: none !important;
        }

        /*
         * Category plus Total plus twelve months = fourteen printed columns.
         *
         * Category receives 33% of the available width for readable names.
         * The remaining 67% is shared by Total and Jul–Jun.
         */
        .budget-lines-table col:nth-child(1) {
            width: 33% !important;
        }

        .budget-lines-table col:nth-child(2),
        .budget-lines-table col:nth-child(n + 3):not(.budget-lines-actions-column) {
            width: 5.1538% !important;
        }

        /*
         * Tighten every table cell for print. The global report stylesheet
         * remains in control of all other report layout.
         */
        .budget-lines-table th,
        .budget-lines-table td {
            padding: 2px 2px !important;
            font-size: 7.2px !important;
            line-height: 1.1 !important;
            vertical-align: top !important;
        }

        /*
         * Long category labels may wrap; financial values should remain compact,
         * right aligned, and on one line.
         */
        .budget-lines-table th:first-child,
        .budget-lines-table td:first-child {
            white-space: normal !important;
            overflow-wrap: anywhere !important;
            text-align: left !important;
        }

        .budget-lines-table th:not(:first-child),
        .budget-lines-table td:not(:first-child) {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: clip !important;
            text-align: right !important;
        }

        /*
         * The Category-heading rows use colspan and should retain enough
         * prominence without wasting vertical paper space.
         */
        .budget-lines-table tr.bg-slate-700 td,
        .budget-lines-table tr.bg-gray-200 td,
        .budget-lines-table tr.bg-gray-50 td {
            padding-top: 3px !important;
            padding-bottom: 3px !important;
            font-size: 8px !important;
            line-height: 1.1 !important;
        }

        /*
         * Trip Summary Report: wide planned-versus-actual comparison table.
         */
        .trip-summary-table {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }

        .trip-summary-table th,
        .trip-summary-table td {
            padding: 2px 2px !important;
            font-size: 6.8px !important;
            line-height: 1.1 !important;
        }

        .trip-summary-table th:nth-child(1),
        .trip-summary-table td:nth-child(1) {
            width: 16% !important;
        }

        .trip-summary-table th:nth-child(2),
        .trip-summary-table td:nth-child(2) {
            width: 8% !important;
        }

        .trip-summary-table th:nth-child(n + 3),
        .trip-summary-table td:nth-child(n + 3) {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: clip !important;
            text-align: right !important;
        }
    

            /*
         * Agenda-format Knowledge Category Reports.
         *
         * Agenda output is intended for meeting reading and circulation, not
         * a dense operational/financial table. Use a more legible A4 print
         * size only when the report body has the agenda-report class.
         */
                .agenda-report,
        .agenda-report .markdown-content,
        .agenda-report .markdown-content p,
        .agenda-report .markdown-content li,
        .agenda-report .markdown-content td,
        .agenda-report .markdown-content th,
        .agenda-report .markdown-content blockquote {
            font-size: 10.5pt !important;
            line-height: 1.38 !important;
        }

        .agenda-report .text-xs {
            font-size: 8.75pt !important;
            line-height: 1.25 !important;
        }

        .agenda-report .text-sm,
        .agenda-report .text-sm.font-semibold {
            font-size: 9.75pt !important;
            line-height: 1.32 !important;
        }

        .agenda-report .text-base {
            font-size: 11pt !important;
            line-height: 1.3 !important;
        }

        .agenda-report .text-lg {
            font-size: 12pt !important;
            line-height: 1.25 !important;
        }

        .agenda-report h1,
        .agenda-report .text-2xl {
            font-size: 17pt !important;
            line-height: 1.12 !important;
        }

        .agenda-report h2,
        .agenda-report .text-xl {
            font-size: 14pt !important;
            line-height: 1.16 !important;
        }

        .agenda-report h3,
        .agenda-report h4,
        .agenda-report h5 {
            font-size: 11.5pt !important;
            line-height: 1.25 !important;
        }

        .agenda-report .markdown-content h1 {
            font-size: 15pt !important;
        }

        .agenda-report .markdown-content h2 {
            font-size: 13pt !important;
        }

        .agenda-report .markdown-content h3 {
            font-size: 11.5pt !important;
        }

        .agenda-report .markdown-content h4,
        .agenda-report .markdown-content h5,
        .agenda-report .markdown-content h6 {
            font-size: 11pt !important;
        }

        .agenda-report .space-y-6 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 10px !important;
        }

        .agenda-report .space-y-5 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 8px !important;
        }

        .agenda-report .space-y-4 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 7px !important;
        }

        .agenda-report .space-y-3 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 6px !important;
        }

        .agenda-report .p-4 {
            padding: 9px !important;
        }

        .agenda-report .px-6 {
            padding-left: 9px !important;
            padding-right: 9px !important;
        }

        .agenda-report .py-5 {
            padding-top: 9px !important;
            padding-bottom: 9px !important;
        }

        .agenda-report .py-4 {
            padding-top: 7px !important;
            padding-bottom: 7px !important;
        }

        .agenda-report .pt-3 {
            padding-top: 6px !important;
        }

        .agenda-report .pt-4 {
            padding-top: 7px !important;
        }
                /*
         * Agenda print output should read as a clean document, not as the
         * bordered-card layout used by the on-screen report.
         */
        .agenda-report .report-category-card,
        .agenda-report .report-item,
        .agenda-report .rounded-lg,
        .agenda-report .rounded-md,
        .agenda-report .border,
        .agenda-report .border-t,
        .agenda-report .border-b,
        .agenda-report .border-gray-100,
        .agenda-report .border-gray-200 {
            border-color: transparent !important;
            border-width: 0 !important;
            box-shadow: none !important;
        }

        /*
         * Retain a modest separator between agenda items without drawing
         * a boxed line at the top and bottom of each printed page.
         */
        .agenda-report .report-item {
            border-bottom: 1px solid #b0b0b0 !important;
            padding-bottom: 10px !important;
            margin-bottom: 10px !important;
        }

        .agenda-report .report-item:last-child {
            border-bottom: 0 !important;
        }

        /*
         * Preserve category separation using spacing and the category heading,
         * rather than a surrounding card border.
         */
        .agenda-report .report-category-card {
            margin-bottom: 14px !important;
        }

        .agenda-report .report-category-heading {
            border-bottom: 1.5px solid #000 !important;
            padding-bottom: 5px !important;
            margin-bottom: 8px !important;
        }
}
</style>