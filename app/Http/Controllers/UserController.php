<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\at;

class UserController extends Controller
{
    /**
     * @unauthenticated
     */
    public function login(Request $request)
    {
        $request->validate([
            /**
             * @var string{}
             * @example "james@gmail.com"
             */
            'email' => 'required|email',
            /**
             * @var string{}
             * @example "password"
             */
            'password' => 'required',
        ]);


        $credential = $request->only('email', 'password');

        if (Auth::attempt($credential) === false) {
            return response()->json('Unauthorized', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('token');

        return response()->json(['access_token' => $token->plainTextToken], 200);
    }

    /**
     * @response User
     */
    public function index(Request $request)
    {
        if (!$request->has('name')) {
            return User::paginate(5);
        }

        return User::whereName($request->name)->get();
    }

    /**
     * @response User
     */
    public function show(int $id)
    {
        $user = User::find($id);

        if ($user === null) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return $user;
    }

    /**
     * @response User
     */
    public function store(Request $request)
    {
        $request->validate([
            /**
             * @var string{}
             * @example "James"
             */
            'name' => 'required',
            /**
             * @var string{}
             * @example "james@gmail.com"
             */
            'email' => 'required|email',
            /**
             * @var string{}
             * @example "password"
             */
            'password' => 'required',
            /**
             * @var string{}
             * @example "requester"
             */
            'role' => 'required',
        ]);

        $user = User::create($request->all());

        return response()->json($user, 201);
    }

    /**
     * @response User
     */
    public function update(int $id, Request $request)
    {
        $request->validate([
            /**
             * @var string{}
             * @example "James"
             */
            'name' => 'required',
            /**
             * @var string{}
             * @example "password"
             */
            'password' => 'required',
            /**
             * @var string{}
             * @example "requester"
             */
            'role' => 'required',
        ]);

        $user = User::find($id);

        if ($user === null) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->fill($request->all());
        $user->save();

        return $user;
    }

    public function destroy(int $id)
    {
        User::destroy($id);
        return response()->noContent();
    }
}
