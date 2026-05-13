<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualCourier;
use Illuminate\Http\Request;

class ManualCourierController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display manual couriers with CRUD form and grid.
     */
    public function index(Request $request)
    {
        $couriers = ManualCourier::orderBy('created_at', 'desc')->paginate(20);

        $editCourier = null;
        if ($request->filled('edit')) {
            $editCourier = ManualCourier::find($request->edit);
        }

        return view('admin.manual-couriers.index', compact('couriers', 'editCourier'));
    }

    /**
     * Store a new manual courier.
     */
    public function store(Request $request)
    {
        $request->validate([
            'courier_service' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'tracking_url' => 'nullable|string|max:500',
        ]);

        ManualCourier::create([
            'courier_service' => $request->courier_service,
            'name' => $request->name,
            'tracking_url' => $request->tracking_url,
            'is_active' => true,
        ]);

        return redirect()->route('admin.manual-couriers.index')
            ->with('success', 'Manual courier created successfully.');
    }

    /**
     * Update an existing manual courier.
     */
    public function update(Request $request, ManualCourier $manual_courier)
    {
        $request->validate([
            'courier_service' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'tracking_url' => 'nullable|string|max:500',
        ]);

        $manual_courier->update([
            'courier_service' => $request->courier_service,
            'name' => $request->name,
            'tracking_url' => $request->tracking_url,
        ]);

        return redirect()->route('admin.manual-couriers.index')
            ->with('success', 'Manual courier updated successfully.');
    }

    /**
     * Delete a manual courier.
     */
    public function destroy(ManualCourier $manual_courier)
    {
        $manual_courier->delete();

        return redirect()->route('admin.manual-couriers.index')
            ->with('success', 'Manual courier deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(ManualCourier $manual_courier)
    {
        $manual_courier->update([
            'is_active' => !$manual_courier->is_active,
        ]);

        return redirect()->route('admin.manual-couriers.index')
            ->with('success', 'Courier status updated successfully.');
    }

    /**
     * Get active couriers as JSON (for AJAX popup).
     */
    public function getActive()
    {
        $couriers = ManualCourier::active()->orderBy('name')->get(['id', 'courier_service', 'name']);

        return response()->json($couriers);
    }
}
