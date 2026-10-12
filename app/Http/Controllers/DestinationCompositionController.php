<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationSource;
use App\Models\DestinationSourceExtract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DestinationCompositionController extends Controller
{
    private const FIELDS = [
        'overview' => ['column' => 'overview', 'label' => 'Overview'],
        'travelnotes' => ['column' => 'travelnotes', 'label' => 'Travel Notes'],
        'suitability' => ['column' => 'suitability', 'label' => 'Suitability'],
        'access' => ['column' => 'accessnotes', 'label' => 'Access Notes'],
    ];

    public function data(Request $request, Destination $destination): JsonResponse
    {
        $validated = $request->validate([
            'targetfield' => ['required', 'string', Rule::in(array_keys(self::FIELDS))],
        ]);
        $target = $validated['targetfield'];
        $column = self::FIELDS[$target]['column'];
        $destination->refresh();

        $extracts = DestinationSourceExtract::query()
            ->with('source')
            ->where('targetfield', $target)
            ->where('extractstatus', 'approved')
            ->whereHas('source', fn ($query) => $query
                ->where('destinationid', $destination->getKey())
                ->whereNotIn('importstatus', ['rejected', 'archived']))
            ->orderBy('destinationsourceid')
            ->orderBy('sortorder')
            ->orderBy('id')
            ->get();

        return response()->json([
            'targetfield' => $target,
            'column' => $column,
            'label' => self::FIELDS[$target]['label'],
            'saved_value' => (string) ($destination->getAttribute($column) ?? ''),
            'saved_token' => hash('sha256', (string) ($destination->getAttribute($column) ?? '')),
            'extracts' => $extracts->map(fn ($extract) => [
                'id' => $extract->id,
                'source_id' => $extract->destinationsourceid,
                'source_title' => $extract->source->sourcetitle ?: 'Source',
                'source_publisher' => $extract->source->sourcepublisher,
                'text' => trim($extract->applicable_text),
                'confidence' => $extract->confidence,
                'token' => $this->token($extract),
            ])->values()->all(),
        ]);
    }

    public function save(Request $request, Destination $destination): JsonResponse
    {
        $validated = $request->validate([
            'targetfield' => ['required', 'string', Rule::in(array_keys(self::FIELDS))],
            'text' => ['required', 'string', 'max:65000'],
            'expected_token' => ['required', 'string', 'size:64'],
            'extract_ids' => ['present', 'array', 'max:50'],
            'extract_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
            'extract_tokens' => ['present', 'array', 'max:50'],
            'extract_tokens.*' => ['required', 'string', 'size:64'],
            'confirmed' => ['required', 'accepted'],
        ]);

        $text = trim($validated['text']);
        if ($text === '' || strlen($text) > 65535) {
            throw ValidationException::withMessages([
                'text' => ['The composed text must be non-empty and fit within 65,535 UTF-8 bytes.'],
            ]);
        }

        $target = $validated['targetfield'];
        $column = self::FIELDS[$target]['column'];
        $ids = array_map('intval', $validated['extract_ids']);
        sort($ids);
        $expected = $validated['expected_token'];

        // Render Markdown with raw HTML and unsafe links disabled.
        $html = Str::markdown($text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $applied = DB::transaction(function () use (
            $destination, $validated, $text, $target, $column, $ids, $expected
        ) {
            // Match the existing apply lock order: sources, extracts, destination.
            $sourceIds = DestinationSourceExtract::query()
                ->whereIn('id', $ids)
                ->pluck('destinationsourceid')
                ->unique()->sort()->values()->all();

            $sources = DestinationSource::query()
                ->whereIn('id', $sourceIds)->orderBy('id')
                ->lockForUpdate()->get()->keyBy('id');

            $extracts = DestinationSourceExtract::query()
                ->whereIn('id', $ids)->orderBy('id')
                ->lockForUpdate()->get();

            if ($extracts->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'extract_ids' => ['An extract was removed. Reload the composition before saving.'],
                ]);
            }

            foreach ($extracts as $extract) {
                $source = $sources->get($extract->destinationsourceid);
                $providedToken = $validated['extract_tokens'][$extract->id] ?? '';

                if (
                    !$source
                    || (int) $source->destinationid !== (int) $destination->getKey()
                    || in_array($source->importstatus, ['rejected', 'archived'], true)
                    || $extract->targetfield !== $target
                    || $extract->extractstatus !== 'approved'
                    || !hash_equals($this->token($extract), $providedToken)
                ) {
                    throw ValidationException::withMessages([
                        'extract_ids' => ['An extract or its source changed, is unavailable, or is no longer approved. Reload the composition before saving.'],
                    ]);
                }
            }

            $lockedDestination = Destination::query()
                ->lockForUpdate()->findOrFail($destination->getKey());

            if (!hash_equals(hash('sha256', (string) ($lockedDestination->getAttribute($column) ?? '')), $expected)) {
                throw ValidationException::withMessages([
                    'expected_token' => ['This Destination field changed after the page was loaded. Your draft has been preserved. Reconcile the newer saved content before trying again.'],
                ]);
            }

            $lockedDestination->forceFill([$column => $text])->save();

            foreach ($extracts as $extract) {
                $extract->update([
                    'extractstatus' => 'applied',
                    'appliedon' => now(),
                ]);
            }

            return $extracts->map(fn ($extract) => [
                'id' => $extract->id,
                'destinationsourceid' => $extract->destinationsourceid,
                'targetfield' => $extract->targetfield,
                'targetfield_label' => DestinationSourceExtract::targetFieldOptions()[$extract->targetfield] ?? $extract->targetfield,
                'extractedtext' => $extract->extractedtext,
                'editedtext' => $extract->editedtext,
                'confidence' => $extract->confidence,
                'sortorder' => (int) ($extract->sortorder ?? 0),
                'extractstatus' => 'applied',
                'extractstatus_label' => 'Applied',
                'appliedon_display' => $extract->appliedon?->format('d M Y H:i'),
            ])->values()->all();
        }, 3);

        return response()->json([
            'message' => self::FIELDS[$target]['label'] . ' saved. Confirmed contributing extracts have been marked applied.',
            'column' => $column,
            'text' => $text,
            'saved_token' => hash('sha256', $text),
            'rendered_html' => $html,
            'applied_extracts' => $applied,
        ]);
    }

    private function token(DestinationSourceExtract $extract): string
    {
        return hash('sha256', json_encode([
            $extract->targetfield,
            $extract->extractedtext,
            $extract->editedtext,
            $extract->extractstatus,
        ], JSON_THROW_ON_ERROR));
    }
}
