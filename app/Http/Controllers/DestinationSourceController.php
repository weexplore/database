<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\DestinationSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DestinationSourceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDestinationSource($request);

        $source = DestinationSource::create($validated);

        return redirect()
            ->to($this->destinationRedirectUrl($source))
            ->with('success', 'Destination source added successfully.');
    }

    public function update(Request $request, DestinationSource $destinationsource): RedirectResponse
    {
        $validated = $this->validateDestinationSource($request, $destinationsource);

        $destinationsource->update($validated);

        return redirect()
            ->to($this->destinationRedirectUrl($destinationsource))
            ->with('success', 'Destination source updated successfully.');
    }

    public function destroy(DestinationSource $destinationsource): RedirectResponse
    {
        $redirectUrl = $this->destinationRedirectUrl($destinationsource);

        $destinationsource->delete();

        return redirect()
            ->to($redirectUrl)
            ->with('success', 'Destination source deleted successfully.');
    }

    protected function validateDestinationSource(Request $request, ?DestinationSource $source = null): array
    {
        $data = $request->all();

        $validator = validator($data, $this->destinationSourceRules());

        $validator->after(function (Validator $validator) use ($data) {
            $hasDestination = !empty($data['destinationid']);
            $hasDestinationItem = !empty($data['destinationitemid']);

            if (!$hasDestination && !$hasDestinationItem) {
                $validator->errors()->add(
                    'destinationid',
                    'A destination or destination item is required.'
                );
            }

            if (!empty($data['destinationid']) && !empty($data['destinationitemid'])) {
                $item = DestinationItem::find($data['destinationitemid']);

                if ($item && (int) $item->destinationid !== (int) $data['destinationid']) {
                    $validator->errors()->add(
                        'destinationitemid',
                        'The selected destination item does not belong to the selected destination.'
                    );
                }
            }

            if (!empty($data['reviewedon']) && empty($data['reviewedby'])) {
                $validator->errors()->add(
                    'reviewedby',
                    'Reviewed by is required when a review date is entered.'
                );
            }

            if (!empty($data['reviewedby']) && empty($data['reviewedon'])) {
                $validator->errors()->add(
                    'reviewedon',
                    'Reviewed on is required when a reviewer is entered.'
                );
            }

            if (($data['importstatus'] ?? null) === 'approved') {
                $hasImportedContent =
                    !empty(trim((string) ($data['importedsummary'] ?? ''))) ||
                    !empty(trim((string) ($data['importednotes'] ?? '')));

                if (!$hasImportedContent) {
                    $validator->errors()->add(
                        'importedsummary',
                        'Approved sources should include an imported summary or imported notes.'
                    );
                }
            }
        });

        $validated = $validator->validate();

        $validated['destinationid'] = $validated['destinationid'] ?? null;
        $validated['destinationitemid'] = $validated['destinationitemid'] ?? null;
        $validated['sourcetype'] = $validated['sourcetype'] ?? 'website';
        $validated['sourceurl'] = $this->nullIfBlank($validated['sourceurl'] ?? null);
        $validated['sourcetitle'] = $this->nullIfBlank($validated['sourcetitle'] ?? null);
        $validated['sourcepublisher'] = $this->nullIfBlank($validated['sourcepublisher'] ?? null);
        $validated['retrievedon'] = $validated['retrievedon'] ?? now()->toDateString();
        $validated['importedsummary'] = $this->nullIfBlank($validated['importedsummary'] ?? null);
        $validated['importednotes'] = $this->nullIfBlank($validated['importednotes'] ?? null);
        $validated['reviewedon'] = $validated['reviewedon'] ?? null;
        $validated['reviewedby'] = $this->nullIfBlank($validated['reviewedby'] ?? null);
        $validated['internalnotes'] = $this->nullIfBlank($validated['internalnotes'] ?? null);

        return $validated;
    }

    protected function destinationSourceRules(): array
    {
        return [
            'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'sourcetype' => ['nullable', 'string', Rule::in(array_keys(DestinationSource::sourceTypeOptions()))],
            'sourceurl' => ['nullable', 'url', 'max:255'],
            'sourcetitle' => ['nullable', 'string', 'max:255'],
            'sourcepublisher' => ['nullable', 'string', 'max:150'],
            'retrievedon' => ['nullable', 'date'],
            'importedsummary' => ['nullable', 'string'],
            'importednotes' => ['nullable', 'string'],
            'importstatus' => ['required', 'string', Rule::in(array_keys(DestinationSource::importStatusOptions()))],
            'reviewedon' => ['nullable', 'date'],
            'reviewedby' => ['nullable', 'string', 'max:100'],
            'internalnotes' => ['nullable', 'string'],
        ];
    }

    protected function destinationRedirectUrl(DestinationSource $source): string
    {
        if ($source->destinationid) {
            return route('destinations.edit', $source->destinationid);
        }

        if ($source->destinationitemid) {
            $item = $source->destinationItem;

            if ($item && $item->destinationid) {
                return route('destinations.edit', $item->destinationid);
            }
        }

        return route('destinations.index');
    }

    protected function nullIfBlank(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}