<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\District;
use App\Models\Taluka;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all states
     */
    public function getStates()
    {
        $states = State::active()->ordered()->get(['id', 'name', 'code']);
        
        return response()->json([
            'success' => true,
            'data' => $states
        ]);
    }

    /**
     * Get districts by state
     */
    public function getDistricts(Request $request)
    {
        $request->validate([
            'state_id' => 'required|integer|exists:states,id'
        ]);

        $districts = District::active()
            ->byState($request->state_id)
            ->ordered()
            ->get(['id', 'name', 'code', 'state_id']);

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    /**
     * Get talukas by district
     */
    public function getTalukas(Request $request)
    {
        $request->validate([
            'district_id' => 'required|integer|exists:districts,id'
        ]);

        $talukas = Taluka::active()
            ->byDistrict($request->district_id)
            ->ordered()
            ->get(['id', 'name', 'code', 'district_id', 'state_id']);

        return response()->json([
            'success' => true,
            'data' => $talukas
        ]);
    }

    /**
     * Get talukas by state (for cases where district is not selected)
     */
    public function getTalukasByState(Request $request)
    {
        $request->validate([
            'state_id' => 'required|integer|exists:states,id'
        ]);

        $talukas = Taluka::active()
            ->byState($request->state_id)
            ->ordered()
            ->get(['id', 'name', 'code', 'district_id', 'state_id']);

        return response()->json([
            'success' => true,
            'data' => $talukas
        ]);
    }

    /**
     * Search locations (for autocomplete)
     */
    public function searchLocations(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'nullable|in:state,district,taluka'
        ]);

        $query = $request->query;
        $type = $request->type;

        $results = [];

        if (!$type || $type === 'state') {
            $states = State::active()
                ->where('name', 'like', "%{$query}%")
                ->ordered()
                ->limit(10)
                ->get(['id', 'name', 'code', 'type' => \DB::raw("'state'")]);
            $results = $results->merge($states);
        }

        if (!$type || $type === 'district') {
            $districts = District::active()
                ->where('name', 'like', "%{$query}%")
                ->with('state:id,name')
                ->ordered()
                ->limit(10)
                ->get(['id', 'name', 'code', 'state_id', 'type' => \DB::raw("'district'")]);
            $results = $results->merge($districts);
        }

        if (!$type || $type === 'taluka') {
            $talukas = Taluka::active()
                ->where('name', 'like', "%{$query}%")
                ->with(['state:id,name', 'district:id,name'])
                ->ordered()
                ->limit(10)
                ->get(['id', 'name', 'code', 'district_id', 'state_id', 'type' => \DB::raw("'taluka'")]);
            $results = $results->merge($talukas);
        }

        return response()->json([
            'success' => true,
            'data' => $results->sortBy('name')->values()
        ]);
    }
}