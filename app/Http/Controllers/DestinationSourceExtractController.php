<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationSource;
use App\Models\DestinationSourceExtract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DestinationSourceExtractController extends Controller
{
    public function store(
        Request $request,
        DestinationSource $destinationSource
    ): JsonResponse {
        $validated = $this->validatedData($request);

        $extract = $destinationSource
            ->extracts()
            ->create([
                ...$validated,
                'extractstatus' => 'pendingreview',
                'appliedon' => null,
            ]);

        return response()->json([
            'message' => 'Destination source extract created.',
            'extract' => $this->extractPayload(
                $extract->fresh()
            ),
        ], 201);
    }

    public function update(
        Request $request,
        DestinationSource $destinationSource,
        DestinationSourceExtract $destinationSourceExtract
    ): JsonResponse {
        $this->ensureOwnership(
            $destinationSource,
            $destinationSourceExtract
        );

        $this->ensureNotApplied(
            $destinationSourceExtract
        );

        $validated = $this->validatedData($request);

        /*
         * If an approved extract is edited, return it to pending
         * review because its approved content has changed.
         */
        if ($destinationSourceExtract->extractstatus === 'approved') {
            $validated['extractstatus'] = 'pendingreview';
            $validated['appliedon'] = null;
        }

        $destinationSourceExtract->update($validated);

        return response()->json([
            'message' => 'Destination source extract updated.',
            'extract' => $this->extractPayload(
                $destinationSourceExtract->fresh()
            ),
        ]);
    }

    public function approve(
        DestinationSource $destinationSource,
        DestinationSourceExtract $destinationSourceExtract
    ): JsonResponse {
        $this->ensureOwnership(
            $destinationSource,
            $destinationSourceExtract
        );

        $this->ensureNotApplied(
            $destinationSourceExtract
        );

        if ($this->applicableText($destinationSourceExtract) === '') {
            throw ValidationException::withMessages([
                'extractedtext' => [
                    'The extract must contain text before it can be approved.',
                ],
            ]);
        }

        $destinationSourceExtract->update([
            'extractstatus' => 'approved',
            'appliedon' => null,
        ]);

        return response()->json([
            'message' => 'Destination source extract approved.',
            'extract' => $this->extractPayload(
                $destinationSourceExtract->fresh()
            ),
        ]);
    }

    public function reject(
        DestinationSource $destinationSource,
        DestinationSourceExtract $destinationSourceExtract
    ): JsonResponse {
        $this->ensureOwnership(
            $destinationSource,
            $destinationSourceExtract
        );

        $this->ensureNotApplied(
            $destinationSourceExtract
        );

        $destinationSourceExtract->update([
            'extractstatus' => 'rejected',
            'appliedon' => null,
        ]);

        return response()->json([
            'message' => 'Destination source extract rejected.',
            'extract' => $this->extractPayload(
                $destinationSourceExtract->fresh()
            ),
        ]);
    }

    public function apply(
        DestinationSource $destinationSource,
        DestinationSourceExtract $destinationSourceExtract
    ): JsonResponse {
        $this->ensureOwnership(
            $destinationSource,
            $destinationSourceExtract
        );

        $result = DB::transaction(function () use (
            $destinationSource,
            $destinationSourceExtract
        ) {
            $source = DestinationSource::query()
                ->with('destinationItem')
                ->lockForUpdate()
                ->findOrFail($destinationSource->getKey());

            $extract = DestinationSourceExtract::query()
                ->lockForUpdate()
                ->findOrFail($destinationSourceExtract->getKey());

            $this->ensureOwnership(
                $source,
                $extract
            );

            if ($extract->extractstatus !== 'approved') {
                throw ValidationException::withMessages([
                    'extractstatus' => [
                        'Only an approved extract can be applied.',
                    ],
                ]);
            }

            $fieldMap = $this->destinationFieldMap();

            if (!array_key_exists($extract->targetfield, $fieldMap)) {
                throw ValidationException::withMessages([
                    'targetfield' => [
                        'The selected destination field cannot be updated.',
                    ],
                ]);
            }

            $text = $this->applicableText($extract);

            if ($text === '') {
                throw ValidationException::withMessages([
                    'extractedtext' => [
                        'The extract contains no text to apply.',
                    ],
                ]);
            }

            $destinationId = $this->resolveDestinationId(
                $source
            );

            $destination = Destination::query()
                ->lockForUpdate()
                ->findOrFail($destinationId);

            $destinationColumn = $fieldMap[
                $extract->targetfield
            ];

            /*
             * forceFill is intentional here. The column name comes
             * only from the controlled server-side field map.
             */
            $destination->forceFill([
                $destinationColumn => $text,
            ])->save();

            $extract->update([
                'extractstatus' => 'applied',
                'appliedon' => now(),
            ]);

            return [
                'destination' => $destination->fresh(),
                'extract' => $extract->fresh(),
                'destinationColumn' => $destinationColumn,
            ];
        });

        return response()->json([
            'message' => 'Extract applied to the destination.',
            'extract' => $this->extractPayload(
                $result['extract']
            ),
            'destination' => [
                'id' => $result['destination']->id,
                'updatedfield' =>
                    $result['destinationColumn'],
                'updatedvalue' =>
                    $result['destination']
                        ->getAttribute(
                            $result['destinationColumn']
                        ),
            ],
        ]);
    }

    public function destroy(
        DestinationSource $destinationSource,
        DestinationSourceExtract $destinationSourceExtract
    ) {
        $this->ensureOwnership(
            $destinationSource,
            $destinationSourceExtract
        );

        if ($destinationSourceExtract->extractstatus === 'applied') {
            throw ValidationException::withMessages([
                'extractstatus' => [
                    'An applied extract cannot be deleted because it forms part of the application history.',
                ],
            ]);
        }

        $destinationSourceExtract->delete();

        return response()->noContent();
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'targetfield' => [
                'required',
                'string',
                Rule::in(
                    DestinationSourceExtract::targetFieldValues()
                ),
            ],
            'extractedtext' => [
                'required',
                'string',
            ],
            'editedtext' => [
                'nullable',
                'string',
            ],
            'confidence' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
            ],
            'sortorder' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);
    }

    private function ensureOwnership(
        DestinationSource $destinationSource,
        DestinationSourceExtract $destinationSourceExtract
    ): void {
        abort_unless(
            (int) $destinationSourceExtract->destinationsourceid
                === (int) $destinationSource->getKey(),
            404
        );
    }

    private function ensureNotApplied(
        DestinationSourceExtract $destinationSourceExtract
    ): void {
        if ($destinationSourceExtract->extractstatus === 'applied') {
            throw ValidationException::withMessages([
                'extractstatus' => [
                    'An applied extract cannot be changed.',
                ],
            ]);
        }
    }

    private function resolveDestinationId(
        DestinationSource $destinationSource
    ): int {
        $directDestinationId = $destinationSource->destinationid
            ? (int) $destinationSource->destinationid
            : null;

        $itemDestinationId =
            $destinationSource->destinationItem?->destinationid
                ? (int) $destinationSource
                    ->destinationItem
                    ->destinationid
                : null;

        /*
         * Where both links exist, they must identify the same
         * destination.
         */
        if (
            $directDestinationId !== null
            && $itemDestinationId !== null
            && $directDestinationId !== $itemDestinationId
        ) {
            throw ValidationException::withMessages([
                'destinationid' => [
                    'The source destination does not match the destination item.',
                ],
            ]);
        }

        $destinationId =
            $directDestinationId
            ?? $itemDestinationId;

        if ($destinationId === null) {
            throw ValidationException::withMessages([
                'destinationid' => [
                    'The source is not linked to a destination.',
                ],
            ]);
        }

        return $destinationId;
    }

    private function applicableText(
        DestinationSourceExtract $destinationSourceExtract
    ): string {
        return trim(
            $destinationSourceExtract->applicable_text
        );
    }

    private function destinationFieldMap(): array
    {
        return [
            'overview' => 'overview',
            'travelnotes' => 'travelnotes',
            'bestseason' => 'bestseason',
            'suitability' => 'suitability',
            'access' => 'accessnotes',
        ];
    }

    private function extractPayload(
        DestinationSourceExtract $extract
    ): array {
        $targetFieldOptions =
            DestinationSourceExtract::targetFieldOptions();

        $statusOptions =
            DestinationSourceExtract::statusOptions();

        return [
            'id' => $extract->id,
            'destinationsourceid' =>
                $extract->destinationsourceid,
            'targetfield' => $extract->targetfield,
            'targetfield_label' =>
                $targetFieldOptions[$extract->targetfield]
                ?? $extract->targetfield,
            'extractedtext' =>
                $extract->extractedtext,
            'editedtext' =>
                $extract->editedtext,
            'applicabletext' =>
                $this->applicableText($extract),
            'confidence' =>
                $extract->confidence,
            'extractstatus' =>
                $extract->extractstatus,
            'extractstatus_label' =>
                $statusOptions[$extract->extractstatus]
                ?? $extract->extractstatus,
            'appliedon' =>
                $extract->appliedon?->format(
                    'Y-m-d H:i:s'
                ),
            'appliedon_display' =>
                $extract->appliedon?->format(
                    'd M Y H:i'
                ),
            'sortorder' =>
                (int) ($extract->sortorder ?? 0),
            'can_edit' =>
                $extract->extractstatus !== 'applied',
            'can_approve' =>
                $extract->extractstatus !== 'applied',
            'can_reject' =>
                $extract->extractstatus !== 'applied',
            'can_apply' =>
                $extract->extractstatus === 'approved',
            'can_delete' =>
                $extract->extractstatus !== 'applied',
        ];
    }
}