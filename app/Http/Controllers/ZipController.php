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
                'zip_codes' => ZipCode::with('placeName', 'placeName.county')->get(),
            ]
        ]);
    }

    public function show($id) {
        $zipCode = ZipCode::with('placeName', 'placeName.county')->find($id);
        if (!$zipCode) {
            return response()->json([
                'message' => 'Zip code not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'zip_code' => $zipCode,
            ]
        ]);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'zip_code' => 'required|integer|max_digits:4',
            'place_name' => 'required|string|max:100',
            'county_id' => 'required|integer|exists:counties,id',
        ]);

        $code = $request->input('zip_code');
        $placeName = $request->input('place_name');
        $county = $request->input('county_id');

        $zipCode = ZipCode::with('placeName', 'placeName.county')->find($id);

        $zipCode->code = $code;
        $zipCode->placeName->name = $placeName;
        $zipCode->placeName->county_id = $county;

        $zipCode->placeName->save();
        $zipCode->save();

        $zipCode->load('placeName', 'placeName.county');
        return response()->json([
            'data' => [
                'zip_code' => $zipCode,
            ]
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'zip_code' => 'required|integer|max_digits:4|unique:zip_codes,code',
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
                'county_id' => County::firstOrCreate([
                    'name' => $county,
                ])->id,
            ])->id,
        ]);

        return response()->json([
            'data' => [
                'zip_code' => $zipCode->load('placeName', 'placeName.county'),
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
