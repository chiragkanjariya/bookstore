<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceableZipcode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ZipcodeController extends Controller
{
    /**
     * Autocomplete zipcodes
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        // Require at least 3 characters
        if (strlen($query) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least 3 characters',
                'data' => []
            ]);
        }

        $results = ServiceableZipcode::autocomplete($query, 5);

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Validate zipcode
     */
    public function validateZipcode(Request $request): JsonResponse
    {
        $request->validate([
            'pincode' => 'required|string|min:6|max:6'
        ]);

        $pincode = $request->input('pincode');
        $isServiceable = ServiceableZipcode::isServiceable($pincode);
        $details = ServiceableZipcode::getDetails($pincode);

        return response()->json([
            'success' => true,
            'is_serviceable' => $isServiceable,
            'details' => $details,
            'message' => $isServiceable
                ? 'This pincode is serviceable by Shree Maruti Courier'
                : 'This pincode requires manual shipping arrangement'
        ]);
    }
}
