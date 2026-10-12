<?php

namespace App\Http\Controllers;

use App\Models\DestinationItem;
use App\Models\DestinationSource;
use App\Services\DestinationPageCaptureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DestinationSourceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDestinationSource(
            $request
        );

        $source = DestinationSource::create($validated);

        return redirect()
            ->to($this->destinationRedirectUrl($source))
            ->with(
                'success',
                'Destination source added successfully.'
            );
    }

    public function update(
    Request $request,
    DestinationSource $destinationSource
    ): RedirectResponse {
        $validated = $this->validateDestinationSource(
            $request,
            $destinationSource
        );

        $destinationSource->update($validated);

        return redirect()
            ->to(
                $this->destinationRedirectUrl(
                    $destinationSource
                )
            )
            ->with(
                'success',
                'Destination source updated successfully.'
            );
    }

    public function destroy(
        DestinationSource $destinationSource
    ): RedirectResponse {
        $redirectUrl = $this->destinationRedirectUrl(
            $destinationSource
        );

        $destinationSource->delete();

        return redirect()
            ->to($redirectUrl)
            ->with(
                'success',
                'Destination source deleted successfully.'
            );
    }

    protected function validateDestinationSource(
        Request $request,
        ?DestinationSource $source = null
    ): array {
        $data = $request->all();

        $validator = validator(
            $data,
            $this->destinationSourceRules(
                $data,
                $source
            )
        );

        $validator->after(
            function (Validator $validator) use (
                $data,
                $source
            ) {
                $this->validateSourceOwnership(
                    $validator,
                    $data
                );

                $this->validateDerivedSource(
                    $validator,
                    $data,
                    $source
                );

                $this->validateReviewDetails(
                    $validator,
                    $data
                );

                $this->validateApprovedContent(
                    $validator,
                    $data,
                    $source
                );
            }
        );

        $validated = $validator->validate();

        return $this->normaliseValidatedData(
            $validated
        );
    }

    protected function destinationSourceRules(
        array $data,
        ?DestinationSource $source = null
    ): array {
        $sourceUrlRules = [
            'nullable',
            'url',
            'max:255',
        ];

        /*
         * This mirrors uq_destsources_dest_url and provides
         * a readable validation message instead of allowing
         * the database unique constraint to fail.
         *
         * Item-only sources are not covered because the current
         * database index only applies to destinationid/sourceurl.
         */
        if (
            !empty($data['destinationid'])
            && trim((string) ($data['sourceurl'] ?? '')) !== ''
        ) {
            $uniqueSourceUrl = Rule::unique(
                'destinationsources',
                'sourceurl'
            )->where(
                fn ($query) => $query->where(
                    'destinationid',
                    (int) $data['destinationid']
                )
            );

            if ($source) {
                $uniqueSourceUrl->ignore($source);
            }

            $sourceUrlRules[] = $uniqueSourceUrl;
        }

        $derivedSourceRules = [
            'nullable',
            'integer',
            'exists:destinationsources,id',
        ];

        if ($source) {
            $derivedSourceRules[] = Rule::notIn([
                $source->getKey(),
            ]);
        }

        return [
            'destinationid' => [
                'nullable',
                'integer',
                'exists:destinations,id',
            ],

            'destinationitemid' => [
                'nullable',
                'integer',
                'exists:destinationitems,id',
            ],

            'derivedfromsourceid' => $derivedSourceRules,

            'sourcetype' => [
                'nullable',
                'string',
                Rule::in(
                    DestinationSource::sourceTypeValues()
                ),
            ],

            'sourceurl' => $sourceUrlRules,

            'sourcetitle' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sourcepublisher' => [
                'nullable',
                'string',
                'max:150',
            ],

            'retrievedon' => [
                'nullable',
                'date',
            ],

            'importedsummary' => [
                'nullable',
                'string',
            ],

            'importednotes' => [
                'nullable',
                'string',
            ],

            'rawcontent' => [
                'nullable',
                'string',
            ],

            'contentformat' => [
                'nullable',
                'string',
                Rule::in(
                    DestinationSource::contentFormatValues()
                ),
            ],

            'importstatus' => [
                'required',
                'string',
                Rule::in(
                    DestinationSource::importStatusValues()
                ),
            ],

            'reviewedon' => [
                'nullable',
                'date',
            ],

            'reviewedby' => [
                'nullable',
                'string',
                'max:100',
            ],

            'internalnotes' => [
                'nullable',
                'string',
            ],
        ];
    }

    protected function validateSourceOwnership(
        Validator $validator,
        array $data
    ): void {
        $hasDestination = !empty(
            $data['destinationid']
        );

        $hasDestinationItem = !empty(
            $data['destinationitemid']
        );

        if (!$hasDestination && !$hasDestinationItem) {
            $validator->errors()->add(
                'destinationid',
                'A destination or destination item is required.'
            );

            return;
        }

        if (!$hasDestination || !$hasDestinationItem) {
            return;
        }

        $item = DestinationItem::find(
            $data['destinationitemid']
        );

        if (
            $item
            && (int) $item->destinationid
                !== (int) $data['destinationid']
        ) {
            $validator->errors()->add(
                'destinationitemid',
                'The selected destination item does not belong to the selected destination.'
            );
        }
    }

    protected function validateDerivedSource(
        Validator $validator,
        array $data,
        ?DestinationSource $source
    ): void {
        if (
            !$source
            || empty($data['derivedfromsourceid'])
        ) {
            return;
        }

        if (
            (int) $data['derivedfromsourceid']
                === (int) $source->getKey()
        ) {
            $validator->errors()->add(
                'derivedfromsourceid',
                'A destination source cannot be derived from itself.'
            );
        }
    }

    protected function validateReviewDetails(
        Validator $validator,
        array $data
    ): void {
        $hasReviewedOn = !empty(
            $data['reviewedon']
        );

        $hasReviewedBy = trim(
            (string) ($data['reviewedby'] ?? '')
        ) !== '';

        if ($hasReviewedOn && !$hasReviewedBy) {
            $validator->errors()->add(
                'reviewedby',
                'Reviewed by is required when a review date is entered.'
            );
        }

        if ($hasReviewedBy && !$hasReviewedOn) {
            $validator->errors()->add(
                'reviewedon',
                'Reviewed on is required when a reviewer is entered.'
            );
        }
    }

    protected function validateApprovedContent(
        Validator $validator,
        array $data,
        ?DestinationSource $source
    ): void {
        if (
            ($data['importstatus'] ?? null)
                !== 'approved'
        ) {
            return;
        }

        $hasCapturedContent =
            trim(
                (string) (
                    $data['importedsummary'] ?? ''
                )
            ) !== ''
            || trim(
                (string) (
                    $data['importednotes'] ?? ''
                )
            ) !== ''
            || trim(
                (string) (
                    $data['rawcontent'] ?? ''
                )
            ) !== '';

        /*
         * An existing source may also be approved after extracts
         * or destination-item candidates have been prepared.
         */
        $hasReviewCandidates = $source
            && (
                $source->extracts()->exists()
                || $source->itemCandidates()->exists()
            );

        if (
            !$hasCapturedContent
            && !$hasReviewCandidates
        ) {
            $validator->errors()->add(
                'importedsummary',
                'An approved source should contain imported content, raw content, an extract, or an item candidate.'
            );
        }
    }

    protected function normaliseValidatedData(
        array $validated
    ): array {
        $validated['destinationid'] =
            !empty($validated['destinationid'])
                ? (int) $validated['destinationid']
                : null;

        $validated['destinationitemid'] =
            !empty($validated['destinationitemid'])
                ? (int) $validated['destinationitemid']
                : null;

        $validated['derivedfromsourceid'] =
            !empty($validated['derivedfromsourceid'])
                ? (int) $validated['derivedfromsourceid']
                : null;

        $validated['sourcetype'] =
            $validated['sourcetype']
                ?? 'website';

        $validated['sourceurl'] = $this->nullIfBlank(
            $validated['sourceurl'] ?? null
        );

        $validated['sourcetitle'] = $this->nullIfBlank(
            $validated['sourcetitle'] ?? null
        );

        $validated['sourcepublisher'] =
            $this->nullIfBlank(
                $validated['sourcepublisher'] ?? null
            );

        $validated['retrievedon'] =
            $validated['retrievedon']
                ?? now()->toDateString();

        $validated['importedsummary'] =
            $this->nullIfBlank(
                $validated['importedsummary'] ?? null
            );

        $validated['importednotes'] =
            $this->nullIfBlank(
                $validated['importednotes'] ?? null
            );

        $validated['rawcontent'] =
            $this->nullIfBlank(
                $validated['rawcontent'] ?? null
            );

        $validated['contentformat'] =
            $this->nullIfBlank(
                $validated['contentformat'] ?? null
            );

        if (
            $validated['rawcontent'] !== null
            && $validated['contentformat'] === null
        ) {
            $validated['contentformat'] = 'text';
        }

        if ($validated['rawcontent'] === null) {
            $validated['contentformat'] = null;
        }

        $validated['reviewedon'] =
            $validated['reviewedon'] ?? null;

        $validated['reviewedby'] =
            $this->nullIfBlank(
                $validated['reviewedby'] ?? null
            );

        $validated['internalnotes'] =
            $this->nullIfBlank(
                $validated['internalnotes'] ?? null
            );

        return $validated;
    }

    protected function destinationRedirectUrl(
        DestinationSource $source
    ): string {
        if ($source->destinationid) {
            return route(
                'destinations.edit',
                $source->destinationid
            );
        }

        if ($source->destinationitemid) {
            $item = $source->destinationItem;

            if ($item && $item->destinationid) {
                return route(
                    'destinations.edit',
                    $item->destinationid
                );
            }

            return route(
                'destination-items.edit',
                $source->destinationitemid
            );
        }

        return route('destinations.index');
    }

    protected function nullIfBlank(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }

    public function capturePage(
    Request $request,
    DestinationSource $destinationSource,
    DestinationPageCaptureService $service
): JsonResponse {
    $validated = $request->validate([
        'replace' => ['required', 'boolean'],
    ]);

    $replace = (bool) $validated['replace'];

    if (blank($destinationSource->sourceurl)) {
        throw ValidationException::withMessages([
            'sourceurl' => ['This source has no URL to fetch.'],
        ]);
    }

    if (filled($destinationSource->rawcontent) && !$replace) {
        throw ValidationException::withMessages([
            'rawcontent' => [
                'Captured content already exists. Confirm replacement before fetching again.',
            ],
        ]);
    }

    $originalUrl = $destinationSource->sourceurl;
    $originalContent = $destinationSource->rawcontent;

    try {
        $capture = $service->capture($originalUrl);
    } catch (RuntimeException $exception) {
        return response()->json([
            'message' => $exception->getMessage(),
        ], 422);
    }

    DB::transaction(function () use (
        $destinationSource,
        $originalUrl,
        $originalContent,
        $capture
    ) {
        $source = DestinationSource::query()
            ->lockForUpdate()
            ->findOrFail($destinationSource->getKey());

        if (
            $source->sourceurl !== $originalUrl
            || $source->rawcontent !== $originalContent
        ) {
            throw ValidationException::withMessages([
                'rawcontent' => [
                    'This source changed while the page was being fetched. Reload before trying again.',
                ],
            ]);
        }

        $captureNote = sprintf(
            'Readable webpage text captured on %s. Final URL: %s%s',
            now()->format('Y-m-d H:i:s'),
            $capture['final_url'],
            $capture['truncated'] ? ' [Text truncated to capture limit.]' : ''
        );

        $notes = trim((string) $source->internalnotes);

        $source->update([
            'rawcontent' => $capture['text'],
            'contentformat' => 'text',
            'internalnotes' => $notes === ''
                ? $captureNote
                : $notes . "\n" . $captureNote,
        ]);
    });

    return response()->json([
        'message' => $capture['truncated']
            ? 'Page captured. Text was truncated to 150,000 characters. Review before use.'
            : 'Readable page text captured. Review before use.',
        'text' => $capture['text'],
        'characters' => $capture['characters'],
        'final_url' => $capture['final_url'],
    ]);
}

public function copySummaryToContent(
    Request $request,
    DestinationSource $destinationSource
): JsonResponse {
    $validated = $request->validate([
        'replace' => ['required', 'boolean'],
    ]);

    $replace = (bool) $validated['replace'];

    $text = DB::transaction(function () use (
        $destinationSource,
        $replace
    ) {
        $source = DestinationSource::query()
            ->lockForUpdate()
            ->findOrFail($destinationSource->getKey());

        $summary = trim(
            (string) $source->importedsummary
        );

        if ($summary === '') {
            throw ValidationException::withMessages([
                'importedsummary' => [
                    'This source has no saved Imported Summary. Enter and save the summary first.',
                ],
            ]);
        }

        if (filled($source->rawcontent) && !$replace) {
            throw ValidationException::withMessages([
                'rawcontent' => [
                    'Captured content already exists. Confirm replacement before copying the summary.',
                ],
            ]);
        }

        $note = sprintf(
            'Saved Imported Summary copied into captured content on %s. No webpage was fetched.',
            now()->format('Y-m-d H:i:s')
        );

        $existingNotes = trim(
            (string) $source->internalnotes
        );

        $source->update([
            'rawcontent' => $summary,
            'contentformat' => 'text',
            'internalnotes' => $existingNotes === ''
                ? $note
                : $existingNotes . "\n" . $note,
        ]);

        return $summary;
    });

    return response()->json([
        'message' => 'Imported Summary copied into captured content. No webpage was fetched. You can now select passages for extracts or item candidates.',
        'text' => $text,
        'characters' => mb_strlen($text),
    ]);
}
}