{{-- resources/views/components/report-print-styles.blade.php --}}
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm;
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
    }
</style>