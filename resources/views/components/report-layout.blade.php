@props([
    'title' => 'Report',
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 12mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            font-size: 12px;
            line-height: 1.4;
        }

        .report-page {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .screen-only {
            display: block;
        }

        .print-only {
            display: none;
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .items-start {
            align-items: flex-start;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-3 {
            gap: 0.75rem;
        }

        .gap-4 {
            gap: 1rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .mt-1 {
            margin-top: 0.25rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .font-semibold {
            font-weight: 600;
        }

        .text-gray-900 {
            color: #111827;
        }

        .text-gray-800 {
            color: #1f2937;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .text-gray-500 {
            color: #6b7280;
        }

        .inline-flex {
            display: inline-flex;
        }

        .rounded {
            border-radius: 0.375rem;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .bg-blue-600 {
            background: #2563eb;
        }

        .bg-gray-200 {
            background: #e5e7eb;
        }

        .text-white {
            color: #ffffff;
        }

        .tree-grid {
            display: grid;
            gap: 1.75rem;
        }

        .tree-row {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 1rem;
            align-items: start;
        }

        .span-3 { grid-column: span 3 / span 3; }
        .span-4 { grid-column: span 4 / span 4; }
        .span-5 { grid-column: span 5 / span 5; }
        .span-6 { grid-column: span 6 / span 6; }
        .span-12 { grid-column: span 12 / span 12; }

        .tree-card {
            border: 1px solid #d1d5db;
            border-radius: .75rem;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
            min-height: 120px;
        }

        .tree-label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6b7280;
            margin-bottom: .35rem;
        }

        .tree-name {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.3;
        }

        .tree-meta {
            font-size: .85rem;
            color: #374151;
        }

        .tree-muted {
            color: #6b7280;
            font-size: .8rem;
        }

        .tree-marriage {
            border: 1px dashed #cbd5e1;
            border-radius: .75rem;
            background: #f8fafc;
            padding: .75rem;
            min-height: 96px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tree-two-col {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .tree-children-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        a.button-link {
            text-decoration: none;
        }

        @media screen and (max-width: 1024px) {
            .tree-row {
                grid-template-columns: 1fr;
            }

            .span-3,
            .span-4,
            .span-5,
            .span-6,
            .span-12 {
                grid-column: span 1 / span 1;
            }

            .tree-two-col,
            .tree-children-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            html, body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                background: #fff;
            }

            .screen-only {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .report-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .tree-row {
                display: grid !important;
                grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
                gap: 1rem !important;
                align-items: start !important;
            }

            .span-3 { grid-column: span 3 / span 3 !important; }
            .span-4 { grid-column: span 4 / span 4 !important; }
            .span-5 { grid-column: span 5 / span 5 !important; }
            .span-6 { grid-column: span 6 / span 6 !important; }
            .span-12 { grid-column: span 12 / span 12 !important; }

            .tree-two-col {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: .75rem !important;
            }

            .tree-children-grid {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 1rem !important;
            }

            .tree-card,
            .tree-marriage,
            .tree-children-grid > div {
                break-inside: avoid;
                page-break-inside: avoid;
                box-shadow: none !important;
            }

            a {
                color: inherit !important;
                text-decoration: none !important;
            }
        }
    </style>
</head>
<body>
    <main class="report-page">
        {{ $slot }}
    </main>
</body>
</html>