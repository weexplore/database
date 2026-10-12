<?php

namespace App\Http\Controllers;

use App\Models\DestinationItemType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DestinationItemTypeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim(
                (string) $request->query('search', '')
            ),
            'active' => trim(
                (string) $request->query('active', '')
            ),
        ];

        $query = DestinationItemType::query()
            ->orderBy('typename');

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'typename',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'slug',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'matchkeywords',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        if ($filters['active'] !== '') {
            $query->where(
                'isactive',
                $filters['active'] === '1' ? 1 : 0
            );
        }

        $types = $query->get();

        return view('destination-item-types.index', [
            'pageTitle' => 'Destination Item Types',
            'filters' => $filters,
            'types' => $types,
        ]);
    }

    public function bulkSave(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'existing' => [
                'nullable',
                'array',
            ],
            'existing.*.typename' => [
                'required',
                'string',
                'max:100',
            ],
            'existing.*.slug' => [
                'nullable',
                'string',
                'max:50',
            ],
            'existing.*.matchkeywords' => [
                'nullable',
                'string',
                'max:500',
            ],
            'existing.*.sortorder' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'existing.*.isactive' => [
                'nullable',
                'boolean',
            ],

            'new' => [
                'nullable',
                'array',
            ],
            'new.typename' => [
                'nullable',
                'string',
                'max:100',
            ],
            'new.slug' => [
                'nullable',
                'string',
                'max:50',
            ],
            'new.matchkeywords' => [
                'nullable',
                'string',
                'max:500',
            ],
            'new.sortorder' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'new.isactive' => [
                'nullable',
                'boolean',
            ],

            'search' => [
                'nullable',
                'string',
            ],
            'active' => [
                'nullable',
                'string',
            ],
        ]);

        foreach (
            $validated['existing'] ?? [] as $id => $row
        ) {
            $type = DestinationItemType::findOrFail($id);

            $typename = trim(
                (string) ($row['typename'] ?? '')
            );

            $slug = trim(
                (string) ($row['slug'] ?? '')
            );

            $matchKeywords = $this->normaliseMatchKeywords(
                $row['matchkeywords'] ?? null
            );

            if ($slug === '') {
                $slug = Str::slug($typename);
            }

            $duplicateName = DestinationItemType::query()
                ->whereRaw(
                    'LOWER(typename) = ?',
                    [mb_strtolower($typename)]
                )
                ->where('id', '<>', $type->id)
                ->exists();

            if ($duplicateName) {
                throw ValidationException::withMessages([
                    "existing.$id.typename" =>
                        'Type name must be unique.',
                ]);
            }

            $duplicateSlug = DestinationItemType::query()
                ->where('slug', $slug)
                ->where('id', '<>', $type->id)
                ->exists();

            if ($duplicateSlug) {
                throw ValidationException::withMessages([
                    "existing.$id.slug" =>
                        'Slug must be unique.',
                ]);
            }

            $type->update([
                'typename' => $typename,
                'slug' => $slug,
                'matchkeywords' => $matchKeywords,
                'sortorder' => $row['sortorder'] ?? 0,
                'isactive' => (bool) (
                    $row['isactive'] ?? false
                ),
            ]);
        }

        $new = $validated['new'] ?? [];

        $hasNewRow = trim(
            (string) ($new['typename'] ?? '')
        ) !== '';

        if ($hasNewRow) {
            $typename = trim(
                (string) $new['typename']
            );

            $slug = trim(
                (string) ($new['slug'] ?? '')
            );

            $matchKeywords = $this->normaliseMatchKeywords(
                $new['matchkeywords'] ?? null
            );

            if ($slug === '') {
                $slug = Str::slug($typename);
            }

            $duplicateName = DestinationItemType::query()
                ->whereRaw(
                    'LOWER(typename) = ?',
                    [mb_strtolower($typename)]
                )
                ->exists();

            if ($duplicateName) {
                throw ValidationException::withMessages([
                    'new.typename' =>
                        'Type name must be unique.',
                ]);
            }

            $duplicateSlug = DestinationItemType::query()
                ->where('slug', $slug)
                ->exists();

            if ($duplicateSlug) {
                throw ValidationException::withMessages([
                    'new.slug' =>
                        'Slug must be unique.',
                ]);
            }

            DestinationItemType::create([
                'typename' => $typename,
                'slug' => $slug,
                'matchkeywords' => $matchKeywords,
                'sortorder' => $new['sortorder'] ?? 0,
                'isactive' => array_key_exists(
                    'isactive',
                    $new
                )
                    ? (bool) $new['isactive']
                    : true,
            ]);
        }

        return redirect()
            ->route('destination-item-types.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
            ])
            ->with(
                'success',
                'Destination item types saved successfully.'
            );
    }

    public function destroy(
        DestinationItemType $destinationItemType
    ): RedirectResponse {
        $destinationItemType->loadCount(
            'destinationItems'
        );

        if (
            $destinationItemType->destination_items_count > 0
        ) {
            return redirect()
                ->route('destination-item-types.index')
                ->with(
                    'error',
                    'This item type cannot be deleted because it is linked to destination items.'
                );
        }

        $destinationItemType->delete();

        return redirect()
            ->route('destination-item-types.index')
            ->with(
                'success',
                'Destination item type deleted.'
            );
    }

    private function normaliseMatchKeywords(
        mixed $value
    ): ?string {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $keywords = array_filter(
            array_map(
                static fn (string $keyword): string =>
                    trim($keyword),
                explode(',', $value)
            ),
            static fn (string $keyword): bool =>
                $keyword !== ''
        );

        return $keywords === []
            ? null
            : implode(',', $keywords);
    }
}