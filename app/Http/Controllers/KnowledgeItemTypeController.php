<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeItemType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KnowledgeItemTypeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'active' => (string) $request->query('active', ''),
        ];

        $query = KnowledgeItemType::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('typename', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', (int) $filters['active']);
        }

        $rows = $query
            ->withCount([
                'knowledgeItems as itemscount' => function ($q) {
                    $q->select(DB::raw('count(*)'));
                },
            ])
            ->orderBy('sortorder')
            ->orderBy('typename')
            ->get();

        return view('knowledge.item-types.index', [
            'pageTitle' => 'Knowledge Item Types',
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.typename' => ['required', 'string', 'max:100'],
            'existing.*.description' => ['nullable', 'string', 'max:255'],
            'existing.*.sortorder' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.typename' => ['nullable', 'string', 'max:100'],
            'new.description' => ['nullable', 'string', 'max:255'],
            'new.sortorder' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'active' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $id => $row) {
                $typeName = trim((string) ($row['typename'] ?? ''));
                $description = trim((string) ($row['description'] ?? ''));
                $sortOrder = (int) ($row['sortorder'] ?? 0);

                if ($typeName === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.typename" => 'Type name is required.',
                    ]);
                }

                $duplicateName = KnowledgeItemType::query()
                    ->whereRaw('LOWER(typename) = ?', [mb_strtolower($typeName)])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        "existing.$id.typename" => 'Type name must be unique.',
                    ]);
                }

                $record = KnowledgeItemType::findOrFail($id);
                $oldTypeName = $record->typename;

                $record->update([
                    'typename' => $typeName,
                    'description' => $description !== '' ? $description : null,
                    'sortorder' => $sortOrder,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);

                if ($oldTypeName !== $typeName) {
                    KnowledgeItem::query()
                        ->where('itemtype', $oldTypeName)
                        ->update(['itemtype' => $typeName]);
                }
            }

            $new = $validated['new'] ?? [];
            $newTypeName = trim((string) ($new['typename'] ?? ''));
            $newDescription = trim((string) ($new['description'] ?? ''));
            $newSortOrder = (int) ($new['sortorder'] ?? 0);

            $hasNewRow =
                $newTypeName !== '' ||
                $newDescription !== '';

            if ($hasNewRow) {
                $duplicateName = KnowledgeItemType::query()
                    ->whereRaw('LOWER(typename) = ?', [mb_strtolower($newTypeName)])
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        'new.typename' => 'Type name must be unique.',
                    ]);
                }

                KnowledgeItemType::create([
                    'typename' => $newTypeName,
                    'description' => $newDescription !== '' ? $newDescription : null,
                    'sortorder' => $newSortOrder,
                    'isactive' => (bool) ($new['isactive'] ?? true),
                ]);
            }
        });

        return redirect()
            ->route('knowledge.item-types.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
            ])
            ->with('success', 'Knowledge item types saved successfully.');
    }

    public function destroy(Request $request, KnowledgeItemType $knowledgeItemType): RedirectResponse
    {
        $usageCount = KnowledgeItem::query()
            ->where('itemtype', $knowledgeItemType->typename)
            ->count();

        if ($usageCount > 0) {
            return redirect()
                ->route('knowledge.item-types.index', [
                    'search' => $request->input('search'),
                    'active' => $request->input('active'),
                ])
                ->with('error', 'This knowledge item type cannot be deleted because knowledge items are attached to it.');
        }

        $knowledgeItemType->delete();

        return redirect()
            ->route('knowledge.item-types.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
            ])
            ->with('success', 'Knowledge item type deleted.');
    }
}