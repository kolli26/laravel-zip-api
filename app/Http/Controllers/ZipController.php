<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\PlaceName;
use App\Models\ZipCode;
use Illuminate\Http\Request;

class ZipController extends Controller
{
    /**
     * @api {get} /zip-codes List zip codes
     * @apiName ZipIndex
     * @apiGroup ZipCodes
     *
     * @apiSuccess {Object[]} data.zip_codes List of zip codes with related placeName and county.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": {
    *         "zip_codes": [
    *           {
    *             "id": 1,
    *             "code": 1000,
    *             "place_name": {
    *               "id": 1,
    *               "name": "Example Town",
    *               "county": { "id": 1, "name": "Example County" }
    *             }
    *           }
    *         ]
    *       }
    *     }
     */
    public function index() {
        return response()->json([
            'data' => [
                'zip_codes' => ZipCode::with('placeName', 'placeName.county')->get(),
            ]
        ]);
    }

    public function show($id) {
    /**
     * @api {get} /zip-codes/:id Get zip code
     * @apiName ZipShow
     * @apiGroup ZipCodes
     *
     * @apiParam {Number} id Zip code id.
     *
     * @apiSuccess {Object} data.zip_code Zip code with placeName and county relations.
     * @apiError (404) NotFound Zip code not found.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": {
    *         "zip_code": {
    *           "id": 1,
    *           "code": 1000,
    *           "place_name": { "id": 1, "name": "Example Town", "county": { "id": 1, "name": "Example County" } }
    *         }
    *       }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     {
    *       "message": "Zip code not found"
    *     }
     */
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
    /**
     * @api {put} /zip-codes/:id Update a zip code
     * @apiName ZipUpdate
     * @apiGroup ZipCodes
     * @apiUse AuthHeader
     *
     * @apiParam {Number} id Zip code id in path.
     * @apiParam {Number} zip_code 4-digit zip code.
     * @apiParam {String} place_name Place name.
     * @apiParam {Number} county_id County id.
     *
     * @apiSuccess {Object} data.zip_code Updated zip code with relations.
     * @apiError (404) NotFound Zip code not found.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": {
    *         "zip_code": {
    *           "id": 1,
    *           "code": 1234,
    *           "place_name": { "id": 2, "name": "Updated Town", "county": { "id": 2, "name": "Updated County" } }
    *         }
    *       }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     {
    *       "message": "Zip code not found"
    *     }
     */
        if (!ZipCode::find($id)) {
            return response()->json([
                'message' => 'Zip code not found',
            ], 404);
        }

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
    /**
     * @api {post} /zip-codes Create a zip code
     * @apiName ZipStore
     * @apiGroup ZipCodes
     * @apiUse AuthHeader
     *
     * @apiParam {Number} zip_code 4-digit unique zip code.
     * @apiParam {String} place_name Place name.
     * @apiParam {String} county County name (will be created if missing).
     *
     * @apiSuccess (201) {Object} data.zip_code Created zip code with placeName and county.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 201 Created
    *     {
    *       "data": {
    *         "zip_code": {
    *           "id": 10,
    *           "code": 4321,
    *           "place_name": { "id": 5, "name": "New Town", "county": { "id": 3, "name": "New County" } }
    *         }
    *       }
    *     }
     */
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
    /**
     * @api {delete} /zip-codes/:id Delete a zip code
     * @apiName ZipDelete
     * @apiGroup ZipCodes
     * @apiUse AuthHeader
     *
     * @apiParam {Number} id Zip code id.
     * @apiSuccess {Object} message Deletion confirmation.
     * @apiError (404) NotFound Zip code not found.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "message": "Zip code deleted"
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     {
    *       "message": "Zip code not found"
    *     }
     */
        $zipCode = ZipCode::find($id);
        if (!$zipCode) {
            return response()->json([
                'message' => 'Zip code not found',
            ], 404);
        }

        $zipCode->delete();

        return response()->json([
            'message' => 'Zip code deleted',
        ], 204);
    }
}
