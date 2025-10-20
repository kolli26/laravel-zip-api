<?php

namespace App\Http\Controllers;

use App\Models\PlaceName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;

class CountyController extends Controller
{
    /**
     * @apiDefine AuthHeader
     * @apiHeader {String} Authorization Token
     * @apiHeaderExample {json} Header-Example:
     *    {
     *      "Authorization": "{token}"
     *    }
     */
    /**
     * @api {get} /counties List counties
     * @apiName CountyIndex
     * @apiGroup Counties
     *
     * @apiSuccess {Object[]} data.counties List of counties.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": {
    *         "counties": [ { "id": 1, "name": "Example County" } ]
    *       }
    *     }
     */
    public function index() {
        return response()->json([
            'data' => [
                'counties' => \App\Models\County::all(),
            ]
        ]);
    }

    public function show($id) {
    /**
     * @api {get} /counties/:id Get county
     * @apiName CountyShow
     * @apiGroup Counties
     *
     * @apiParam {Number} id County id.
     * @apiSuccess {Object} data.county County object.
     * @apiError (404) NotFound County not found.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": { "county": { "id": 1, "name": "Example County" } }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     {
    *       "message": "County not found"
    *     }
     */
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
    /**
     * @api {get} /counties/:id/abc Place name initials for a county
     * @apiName CountyPlaceInitials
     * @apiGroup Counties
     *
     * @apiParam {Number} id County id.
     * @apiSuccess {String[]} data.place_initials List of initials (first letters) of place names.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": { "place_initials": ["A", "B", "C"] }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     { "message": "County not found" }
     */
        $county = \App\Models\County::with('placeNames')->find($id);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        $placeNameInitials = DB::table('place_names')
            ->select(DB::raw('SUBSTR(name, 1, 1) AS initial'))
            ->where('county_id', $id)
            ->get()
            ->unique()
            ->pluck('initial');

        return response()->json([
            'data' => [
                'place_initials' => $placeNameInitials,
            ]
        ]);
    }

    public function placeNames($id) {
    /**
     * @api {get} /counties/:id/place-names List place names for county
     * @apiName CountyPlaceNames
     * @apiGroup Counties
     *
     * @apiParam {Number} id County id.
     * @apiSuccess {Object[]} data.place_names List of place name objects.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": { "place_names": [ { "id": 1, "name": "Example Town" } ] }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     { "message": "County not found" }
     */
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
    /**
     * @api {get} /counties/:county/place-names/:placeName Get a place name in a county
     * @apiName CountyPlaceNameShow
     * @apiGroup Counties
     *
     * @apiParam {Number} county County id.
     * @apiParam {Number} placeName Place name id.
     * @apiSuccess {Object} data.place_name Place name object.
     * @apiError (404) NotFound County or Place name not found.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": { "place_name": { "id": 1, "name": "Example Town", "county_id": 1 } }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     { "message": "County not found" }
     */
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
    /**
     * @api {post} /counties Create a county
     * @apiName CountyStore
     * @apiGroup Counties
     * @apiUse AuthHeader
     *
     * @apiParam {String} name County name (unique).
     * @apiSuccess (201) {Object} data.county Created county.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 201 Created
    *     {
    *       "data": { "county": { "id": 5, "name": "New County" } }
    *     }
    *
    * @apiErrorExample {json} Error-Response (validation):
    *     HTTP/1.1 422 Unprocessable Entity
    *     {
    *       "errors": { "name": ["The name field is required."] }
    *     }
     */
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
    /**
     * @api {put} /counties/:id Update a county
     * @apiName CountyUpdate
     * @apiGroup Counties
     * @apiUse AuthHeader
     *
     * @apiParam {Number} id County id.
     * @apiParam {String} name County name (unique).
     * @apiSuccess {Object} data.county Updated county.
     * @apiError (404) NotFound County not found.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": { "county": { "id": 1, "name": "Updated County" } }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     { "message": "County not found" }
     */
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
    /**
     * @api {delete} /counties/:id Delete a county
     * @apiName CountyDelete
     * @apiGroup Counties
     * @apiUse AuthHeader
     *
     * @apiParam {Number} id County id.
     * @apiSuccess {Object} message Deletion confirmation.
     * @apiError (404) NotFound County not found.
    *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 204 No Content
    *     {
    *       "message": "County deleted successfully"
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 404 Not Found
    *     { "message": "County not found" }
     */
        $county = \App\Models\County::find($id);
        if (!$county) {
            return response()->json([
                'message' => 'County not found',
            ], 404);
        }

        $county->delete();

        return response()->json([
            'message' => 'County deleted successfully',
        ], 204);
    }
}