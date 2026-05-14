<?php

namespace App\Http\Controllers;

use App\Models\BibleVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BibleVersionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'active' => (string) $request->query('active', ''),
        ];

        $query = BibleVersion::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('versioncode', 'like', "%{$filters['search']}%")
                    ->orWhere('versionname', 'like', "%{$filters['search']}%")
                    ->orWhere('languagecode', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', (int) $filters['active']);
        }

        return view('bible-versions.index', [
            'pageTitle' => 'Bible Versions',
            'rows' => $query->orderBy('versionname')->get(),
            'filters' => $filters,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.versioncode' => ['required', 'string', 'max:20'],
            'existing.*.versionname' => ['required', 'string', 'max:150'],
            'existing.*.languagecode' => ['nullable', 'string', 'max:20'],
            'existing.*.notes' => ['nullable', 'string'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.versioncode' => ['nullable', 'string', 'max:20'],
            'new.versionname' => ['nullable', 'string', 'max:150'],
            'new.languagecode' => ['nullable', 'string', 'max:20'],
            'new.notes' => ['nullable', 'string'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'active' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $id => $row) {
                $versionCode = strtoupper(trim((string) ($row['versioncode'] ?? '')));
                $versionName = trim((string) ($row['versionname'] ?? ''));
                $languageCode = strtoupper(trim((string) ($row['languagecode'] ?? '')));
                $notes = isset($row['notes']) ? trim((string) $row['notes']) : null;

                if ($versionCode === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.versioncode" => 'Version code is required.',
                    ]);
                }

                if ($versionName === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.versionname" => 'Version name is required.',
                    ]);
                }

                $duplicateCode = BibleVersion::query()
                    ->whereRaw('UPPER(versioncode) = ?', [$versionCode])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        "existing.$id.versioncode" => 'Version code must be unique.',
                    ]);
                }

                $version = BibleVersion::findOrFail($id);

                $version->update([
                    'versioncode' => $versionCode,
                    'versionname' => $versionName,
                    'languagecode' => $languageCode !== '' ? $languageCode : null,
                    'notes' => $notes !== '' ? $notes : null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];
            $newCode = strtoupper(trim((string) ($new['versioncode'] ?? '')));
            $newName = trim((string) ($new['versionname'] ?? ''));
            $newLanguage = strtoupper(trim((string) ($new['languagecode'] ?? '')));
            $newNotes = isset($new['notes']) ? trim((string) $new['notes']) : null;

            $hasNewRow = $newCode !== '' || $newName !== '' || $newLanguage !== '' || ($newNotes !== null && $newNotes !== '');

            if ($hasNewRow) {
                if ($newCode === '') {
                    throw ValidationException::withMessages([
                        'new.versioncode' => 'Version code is required for a new Bible version.',
                    ]);
                }

                if ($newName === '') {
                    throw ValidationException::withMessages([
                        'new.versionname' => 'Version name is required for a new Bible version.',
                    ]);
                }

                $duplicateCode = BibleVersion::query()
                    ->whereRaw('UPPER(versioncode) = ?', [$newCode])
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        'new.versioncode' => 'Version code must be unique.',
                    ]);
                }

                BibleVersion::create([
                    'versioncode' => $newCode,
                    'versionname' => $newName,
                    'languagecode' => $newLanguage !== '' ? $newLanguage : null,
                    'notes' => $newNotes !== '' ? $newNotes : null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('bible-versions.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
            ])
            ->with('success', 'Bible versions saved successfully.');
    }

    public function destroy(BibleVersion $bibleVersion): RedirectResponse
    {
        $bibleVersion->delete();

        return redirect()
            ->route('bible-versions.index', [
                'search' => request('search'),
                'active' => request('active'),
            ])
            ->with('success', 'Bible version deleted.');
    }
}