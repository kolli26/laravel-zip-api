<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @api {post} /users/login User login
     * @apiName UserLogin
     * @apiGroup Users
     *
     * @apiParam {String} email User's email.
     * @apiParam {String} password User's password.
     *
     * @apiSuccess {Object} data User object with `token` property attached as `token`.
     *
    * @apiSuccessExample {json} Success-Response:
    *     HTTP/1.1 200 OK
    *     {
    *       "data": {
    *         "id": 1,
    *         "name": "Admin",
    *         "email": "admin@example.com",
    *         "token": "plain-text-token-here"
    *       }
    *     }
    *
    * @apiErrorExample {json} Error-Response:
    *     HTTP/1.1 401 Unauthorized
    *     {
    *       "message": "Invalid credentials"
    *     }
    *
    * @apiError (401) Unauthorized Invalid credentials.
     */
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user->tokens()->delete();

        $user->token = $user->createToken('access')->plainTextToken;

        return response()->json([
            'data' => $user,
        ]);
    }
}