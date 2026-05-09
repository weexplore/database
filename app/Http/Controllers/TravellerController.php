<?php

namespace App\Http\Controllers;

use App\Models\Traveller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TravellerController extends Controller
{
    public function index(Request $request)
    {
        $query = Traveller::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('displayname', 'like', "%{$search}%");
            });
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('isactive', (int) $request->status);
        }

        if ($request->primary !== null && $request->primary !== '') {
            $query->where('isprimarytraveller', (int) $request->primary);
        }

        $travellers = $query
            ->orderBy('displayname')
            ->get();

        return view('travellers.index', compact('travellers'));
    }

    public function bulkSave(Request $request)
    {
        $existing = $request->input('existing', []);
        $new = $request->input('new', []);
        $primaryType = $request->input('primary_type');
        $primaryId = $request->input('primary_id');

        DB::transaction(function () use ($existing, $new, $primaryType, $primaryId) {
            foreach ($existing as $id => $row) {
                $traveller = Traveller::find($id);

                if (! $traveller) {
                    continue;
                }

                $firstname = trim($row['firstname'] ?? '');
                $lastname = trim($row['lastname'] ?? '');
                $displayname = trim($row['displayname'] ?? '');

                if ($firstname === '' || $displayname === '') {
                    throw ValidationException::withMessages([
                        "existing.$id" => 'First name and display name are required for all existing travellers.',
                    ]);
                }

                $traveller->update([
                    'firstname' => $firstname,
                    'lastname' => $lastname !== '' ? $lastname : null,
                    'displayname' => $displayname,
                    'isactive' => !empty($row['isactive']) ? 1 : 0,
                ]);
            }

            $newTraveller = null;
            $newFirstname = trim($new['firstname'] ?? '');
            $newLastname = trim($new['lastname'] ?? '');
            $newDisplayname = trim($new['displayname'] ?? '');

            if ($newFirstname !== '' || $newLastname !== '' || $newDisplayname !== '') {
                if ($newFirstname === '' || $newDisplayname === '') {
                    throw ValidationException::withMessages([
                        'new' => 'New traveller requires first name and display name.',
                    ]);
                }

                $newTraveller = Traveller::create([
                    'firstname' => $newFirstname,
                    'lastname' => $newLastname !== '' ? $newLastname : null,
                    'displayname' => $newDisplayname,
                    'isactive' => !empty($new['isactive']) ? 1 : 0,
                    'isprimarytraveller' => 0,
                ]);
            }

            $targetPrimaryId = null;

            if ($primaryType === 'existing' && $primaryId) {
                $targetPrimaryId = Traveller::where('id', $primaryId)->value('id');
            } elseif ($primaryType === 'new' && $newTraveller) {
                $targetPrimaryId = $newTraveller->id;
            }

            if ($targetPrimaryId) {
                Traveller::query()->update(['isprimarytraveller' => 0]);
                Traveller::where('id', $targetPrimaryId)->update(['isprimarytraveller' => 1]);
            } else {
                $currentPrimaryExists = Traveller::where('isprimarytraveller', 1)->exists();

                if (! $currentPrimaryExists) {
                    $fallbackTraveller = Traveller::where('isactive', 1)
                        ->orderBy('displayname')
                        ->first();

                    if ($fallbackTraveller) {
                        $fallbackTraveller->update(['isprimarytraveller' => 1]);
                    }
                }
            }
        });

        return redirect()
            ->route('travellers.index', $request->only(['search', 'status', 'primary']))
            ->with('success', 'Travellers saved successfully.');
    }

    public function destroy(Request $request, Traveller $traveller)
    {
        if ($traveller->tripTravellerLinks()->exists()) {
            return redirect()
                ->route('travellers.index', $request->only(['search', 'status', 'primary']))
                ->with('error', 'Traveller cannot be deleted because it is linked to one or more trips. Set it inactive instead.');
        }

        $wasPrimary = (bool) $traveller->isprimarytraveller;

        $traveller->delete();

        if ($wasPrimary) {
            $replacement = Traveller::where('isactive', 1)
                ->orderBy('displayname')
                ->first();

            if ($replacement) {
                Traveller::where('id', '!=', $replacement->id)
                    ->update(['isprimarytraveller' => 0]);

                $replacement->update(['isprimarytraveller' => 1]);
            }
        }

        return redirect()
            ->route('travellers.index', $request->only(['search', 'status', 'primary']))
            ->with('success', 'Traveller deleted successfully.');
    }
}