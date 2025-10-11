<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\PlaceName;
use App\Models\ZipCode;
use Illuminate\Http\Request;

class ZipController extends Controller
{
    public function index() {
        return response()->json([
            'data' => [
                'zip_codes' => ZipCode::with('county', 'placeName')->get(),
            ]
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'zip_code' => 'required|int|max_digits:4',
            'place_name' => 'required|string|max:100',
            'county' => 'required|string|max:100',
        ]);

        $zipCode = $request->input('zip_code');
        $placeName = $request->input('place_name');
        $county = $request->input('county');

        $zipCode = ZipCode::create([
            'code' => $request->zip_code,
            'place_name_id' => PlaceName::firstOrCreate([
                'name' => $placeName,
            ])->id,
            'county_id' => County::firstOrCreate([
                'name' => $county,
            ])->id,
        ]);

        return response()->json([
            'data' => [
                'zip_code' => $zipCode->load('county', 'placeName'),
            ]
        ], 201);
    }

    public function destroy($id) {
        $zipCode = ZipCode::find($id);
        if (!$zipCode) {
            return response()->json([
                'message' => 'Zip code not found',
            ], 404);
        }

        $zipCode->delete();

        return response()->json([
            'message' => 'Zip code deleted',
        ]);
    }
}
