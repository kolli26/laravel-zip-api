<?php

namespace App\Http\Controllers;

use App\Models\PlaceName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountyController extends Controller
{
    public function index() {
        return response()->json([
            'data' => [
                'counties' => \App\Models\County::all(),
            ]
        ]);
    }

    public function show($id) {
        $county = \App\Models\County::find($id);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'county' => $county,
            ]
        ]);
    }

    public function placeInitials($id) {
        $county = \App\Models\County::with('placeNames')->find($id);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        // $placeNameInitials = $county->placeNames->map(function($placeName) {
        //     $valami = strtoupper(substr($placeName->name, 0, 1));
        //     return $valami;
        // })->unique()->sort()->values();
        $placeNameInitials = PlaceName::where('county_id', $id)
            ->selectRaw('LEFT(name, 1) as "abc"')
            ->distinct()
            ->pluck('abc');

        return response()->json([
            'data' => [
                'place_initials' => $placeNameInitials,
            ]
        ]);
    }

    public function placeNames($id) {
        $county = \App\Models\County::with('placeNames')->find($id);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'place_names' => $county->placeNames,
            ]
        ]);
    }

    public function placeName($countyId, $placeNameId) {
        $county = \App\Models\County::find($countyId);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        $placeName = $county->placeNames()->where('id', $placeNameId)->first();
        if (!$placeName) {
            return response()->json([
                'message' => 'Place name not found in this county',
            ], 404);
        }

        return response()->json([
            'data' => [
                'place_name' => $placeName,
            ]
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100|unique:counties,name',
        ]);

        $county = \App\Models\County::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'data' => [
                'county' => $county,
            ]
        ], 201);
    }

    public function update(Request $request, $id) {
        $county = \App\Models\County::find($id);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:counties,name,' . $county->id,
        ]);

        $county->name = $request->name;
        $county->save();

        return response()->json([
            'data' => [
                'county' => $county,
            ]
        ]);
    }

    public function destroy($id) {
        $county = \App\Models\County::find($id);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        $county->delete();

        return response()->json([
            'message' => 'County deleted successfully',
        ], 201);
    }
}