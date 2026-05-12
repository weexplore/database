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

    /**
     * Fuel types used in UI.
     */
    protected array $fuelTypes = [
        'diesel',
        'petrol',
        'lpg',
        'hybrid',
        'electric',
        'other',
    ];

    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('vehicletype')) {
            $query->where('vehicletype', $request->string('vehicletype')->toString());
        }

        if ($request->filled('status')) {
            $query->where('isactive', (int) $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->string('search')->toString() . '%';

            $query->where(function ($q) use ($search) {
                $q->where('vehiclename', 'like', $search)
                    ->orWhere('registrationnumber', 'like', $search)
                    ->orWhere('make', 'like', $search)
                    ->orWhere('model', 'like', $search)
                    ->orWhere('fueltype', 'like', $search);
            });
        }

        $vehicles = $query
            ->orderBy('vehiclename')
            ->orderBy('id')
            ->get();

        $vehicleTypes = $this->vehicleTypes;
        $fuelTypes = $this->fuelTypes;

        return view('vehicles.index', compact('vehicles', 'vehicleTypes', 'fuelTypes'));
    }

    public function bulkSave(Request $request)
    {
        $vehicleTypes = implode(',', $this->vehicleTypes);
        $fuelTypes = implode(',', $this->fuelTypes);

        $validated = $request->validate([
            'existing' => ['array'],
            'existing.*.vehiclename' => ['required', 'string', 'max:150'],
            'existing.*.vehicletype' => ['required', 'string', 'in:' . $vehicleTypes],
            'existing.*.registrationnumber' => ['nullable', 'string', 'max:50'],
            'existing.*.make' => ['nullable', 'string', 'max:100'],
            'existing.*.model' => ['nullable', 'string', 'max:100'],
            'existing.*.fueltype' => ['nullable', 'string', 'in:' . $fuelTypes],
            'existing.*.defaultfuelconsumptionlper100km' => ['nullable', 'numeric', 'min:0', 'max:99.9999'],
            'existing.*.fueltankcapacitylitres' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'existing.*.notes' => ['nullable', 'string'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new.vehiclename' => ['nullable', 'string', 'max:150'],
            'new.vehicletype' => ['nullable', 'string', 'in:' . $vehicleTypes],
            'new.registrationnumber' => ['nullable', 'string', 'max:50'],
            'new.make' => ['nullable', 'string', 'max:100'],
            'new.model' => ['nullable', 'string', 'max:100'],
            'new.fueltype' => ['nullable', 'string', 'in:' . $fuelTypes],
            'new.defaultfuelconsumptionlper100km' => ['nullable', 'numeric', 'min:0', 'max:99.9999'],
            'new.fueltankcapacitylitres' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'new.notes' => ['nullable', 'string'],
            'new.isactive' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['existing'])) {
            foreach ($validated['existing'] as $id => $data) {
                $vehicle = Vehicle::find($id);

                if (! $vehicle) {
                    continue;
                }

                $vehicle->update([
                    'vehiclename' => $data['vehiclename'],
                    'vehicletype' => $data['vehicletype'],
                    'registrationnumber' => $data['registrationnumber'] ?? null,
                    'make' => $data['make'] ?? null,
                    'model' => $data['model'] ?? null,
                    'fueltype' => $data['fueltype'] ?? null,
                    'defaultfuelconsumptionlper100km' => $data['defaultfuelconsumptionlper100km'] ?? null,
                    'fueltankcapacitylitres' => $data['fueltankcapacitylitres'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'isactive' => isset($data['isactive']) ? (int) $data['isactive'] : 0,
                ]);
            }
        }

        if (!empty($validated['new']['vehiclename'] ?? null)) {
            Vehicle::create([
                'vehiclename' => $validated['new']['vehiclename'],
                'vehicletype' => $validated['new']['vehicletype'] ?? 'other',
                'registrationnumber' => $validated['new']['registrationnumber'] ?? null,
                'make' => $validated['new']['make'] ?? null,
                'model' => $validated['new']['model'] ?? null,
                'fueltype' => $validated['new']['fueltype'] ?? null,
                'defaultfuelconsumptionlper100km' => $validated['new']['defaultfuelconsumptionlper100km'] ?? null,
                'fueltankcapacitylitres' => $validated['new']['fueltankcapacitylitres'] ?? null,
                'notes' => $validated['new']['notes'] ?? null,
                'isactive' => isset($validated['new']['isactive']) ? (int) $validated['new']['isactive'] : 1,
            ]);
        }

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
            $message = 'This vehicle could not be deleted. It may be linked to trip vehicles, trip legs, or other records.';
            $type = 'error';
        }

        $routeParams = $request->only(['vehicletype', 'search', 'status']);

        return redirect()
            ->route('vehicles.index', $routeParams)
            ->with($type, $message);
    }
}