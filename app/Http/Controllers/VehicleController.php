<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Vehicle types used in UI.
     */
    protected array $vehicleTypes = [
        'car',
        'caravan',
        'trailer',
        'motorhome',
        'other',
    ];

    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Filter by type
        if ($request->filled('vehicletype')) {
            $query->where('vehicletype', $request->string('vehicletype'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('isactive', (int) $request->input('status'));
        }

        // Search by name / registration / make / model
        if ($request->filled('search')) {
            $search = '%' . $request->string('search') . '%';

            $query->where(function ($q) use ($search) {
                $q->where('vehiclename', 'like', $search)
                  ->orWhere('registrationnumber', 'like', $search)
                  ->orWhere('make', 'like', $search)
                  ->orWhere('model', 'like', $search);
            });
        }

        $vehicles = $query
            ->orderBy('vehiclename')
            ->orderBy('id')
            ->get();

        $vehicleTypes = $this->vehicleTypes;

        return view('vehicles.index', compact('vehicles', 'vehicleTypes'));
    }

    public function bulkSave(Request $request)
    {
        $vehicleTypes = implode(',', $this->vehicleTypes);

        $validated = $request->validate([
            // Existing rows
            'existing' => ['array'],
            'existing.*.vehiclename' => ['required', 'string', 'max:150'],
            'existing.*.vehicletype' => ['required', 'string', 'in:' . $vehicleTypes],
            'existing.*.registrationnumber' => ['nullable', 'string', 'max:50'],
            'existing.*.make' => ['nullable', 'string', 'max:100'],
            'existing.*.model' => ['nullable', 'string', 'max:100'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            // New row
            'new.vehiclename' => ['nullable', 'string', 'max:150'],
            'new.vehicletype' => ['nullable', 'string', 'in:' . $vehicleTypes],
            'new.registrationnumber' => ['nullable', 'string', 'max:50'],
            'new.make' => ['nullable', 'string', 'max:100'],
            'new.model' => ['nullable', 'string', 'max:100'],
            'new.isactive' => ['nullable', 'boolean'],
        ]);

        // Update existing
        if (!empty($validated['existing'])) {
            foreach ($validated['existing'] as $id => $data) {
                /** @var \App\Models\Vehicle|null $vehicle */
                $vehicle = Vehicle::find($id);
                if (! $vehicle) {
                    continue;
                }

                $vehicle->update([
                    'vehiclename'       => $data['vehiclename'],
                    'vehicletype'       => $data['vehicletype'],
                    'registrationnumber'=> $data['registrationnumber'] ?? null,
                    'make'              => $data['make'] ?? null,
                    'model'             => $data['model'] ?? null,
                    'isactive'          => isset($data['isactive']) ? (int) $data['isactive'] : 0,
                ]);
            }
        }

        // Create new if name present
        if (!empty($validated['new']['vehiclename'] ?? null)) {
            Vehicle::create([
                'vehiclename'       => $validated['new']['vehiclename'],
                'vehicletype'       => $validated['new']['vehicletype'] ?? 'other',
                'registrationnumber'=> $validated['new']['registrationnumber'] ?? null,
                'make'              => $validated['new']['make'] ?? null,
                'model'             => $validated['new']['model'] ?? null,
                'isactive'          => isset($validated['new']['isactive'])
                                        ? (int) $validated['new']['isactive']
                                        : 1,
            ]);
        }

        // Preserve filters on redirect
        $routeParams = $request->only(['vehicletype', 'search', 'status']);

        return redirect()
            ->route('vehicles.index', $routeParams)
            ->with('success', 'Vehicles saved successfully.');
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        try {
            $vehicleName = $vehicle->vehiclename;

            $vehicle->delete();

            $message = "Vehicle '{$vehicleName}' deleted successfully.";
            $type = 'success';
        } catch (\Throwable $e) {
            $message = 'This vehicle could not be deleted. It may be linked to trip legs or other records.';
            $type = 'error';
        }

        $routeParams = $request->only(['vehicletype', 'search', 'status']);

        return redirect()
            ->route('vehicles.index', $routeParams)
            ->with($type, $message);
    }
}