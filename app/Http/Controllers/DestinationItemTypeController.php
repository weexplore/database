<?php

namespace App\Http\Controllers;

use App\Models\DestinationItemType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DestinationItemTypeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'active' => trim((string) $request->query('active', '')),
        ];

        $query = DestinationItemType::query()
            ->orderBy('typename');

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('typename', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('slug', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', $filters['active'] === '1' ? 1 : 0);
        }

        $types = $query->get();

        return view('destination-item-types.index', [
            'pageTitle' => 'Destination Item Types',
            'filters' => $filters,
            'types' => $types,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.typename' => ['required', 'string', 'max:100'],
            'existing.*.slug' => ['nullable', 'string', 'max:120'],
            'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.typename' => ['nullable', 'string', 'max:100'],
            'new.slug' => ['nullable', 'string', 'max:120'],
            'new.sortorder' => ['nullable', 'integer', 'min:0'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'active' => ['nullable', 'string'],
        ]);

        foreach ($validated['existing'] ?? [] as $id => $row) {
            $type = DestinationItemType::findOrFail($id);

            $typename = trim((string) ($row['typename'] ?? ''));
            $slug = trim((string) ($row['slug'] ?? ''));

            if ($slug === '') {
                $slug = Str::slug($typename);
            }

            $duplicateName = DestinationItemType::query()
                ->whereRaw('LOWER(typename) = ?', [mb_strtolower($typename)])
                ->where('id', '<>', $type->id)
                ->exists();

            if ($duplicateName) {
                throw ValidationException::withMessages([
                    "existing.$id.typename" => 'Type name must be unique.',
                ]);
            }

            $duplicateSlug = DestinationItemType::query()
                ->where('slug', $slug)
                ->where('id', '<>', $type->id)
                ->exists();

            if ($duplicateSlug) {
                throw ValidationException::withMessages([
                    "existing.$id.slug" => 'Slug must be unique.',
                ]);
            }

            $type->update([
                'typename' => $typename,
                'slug' => $slug,
                'sortorder' => $row['sortorder'] ?? 0,
                'isactive' => (bool) ($row['isactive'] ?? false),
            ]);
        }

        $new = $validated['new'] ?? [];
        $hasNewRow = trim((string) ($new['typename'] ?? '')) !== '';

        if ($hasNewRow) {
            $typename = trim((string) $new['typename']);
            $slug = trim((string) ($new['slug'] ?? ''));

            if ($slug === '') {
                $slug = Str::slug($typename);
            }

            $duplicateName = DestinationItemType::query()
                ->whereRaw('LOWER(typename) = ?', [mb_strtolower($typename)])
                ->exists();

            if ($duplicateName) {
                throw ValidationException::withMessages([
                    'new.typename' => 'Type name must be unique.',
                ]);
            }

            $duplicateSlug = DestinationItemType::query()
                ->where('slug', $slug)
                ->exists();

            if ($duplicateSlug) {
                throw ValidationException::withMessages([
                    'new.slug' => 'Slug must be unique.',
                ]);
            }

            DestinationItemType::create([
                'typename' => $typename,
                'slug' => $slug,
                'sortorder' => $new['sortorder'] ?? 0,
                'isactive' => array_key_exists('isactive', $new) ? (bool) $new['isactive'] : true,
            ]);
        }

        return redirect()->route('destination-item-types.index', [
            'search' => $request->input('search'),
            'active' => $request->input('active'),
        ])->with('success', 'Destination item types saved successfully.');
    }

    public function destroy(DestinationItemType $destinationItemType): RedirectResponse
    {
        $destinationItemType->loadCount('destinationItems');

        if ($destinationItemType->destination_items_count > 0) {
            return redirect()
                ->route('destination-item-types.index')
                ->with('error', 'This item type cannot be deleted because it is linked to destination items.');
        }

        $destinationItemType->delete();

        return redirect()
            ->route('destination-item-types.index')
            ->with('success', 'Destination item type deleted.');
    }
}