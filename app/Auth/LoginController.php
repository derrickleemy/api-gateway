<?php

namespace App\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Auth\LoginProxy;
use App\Auth\LoginRequest;

class LoginController extends Controller
{
    private $loginProxy;

    public function __construct(LoginProxy $loginProxy)
    {
        $this->loginProxy = $loginProxy;
    }

    public function login(LoginRequest $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');

        return response()->json($this->loginProxy->attemptLogin($email, $password));
    }

    public function client(Request $request)
    {
        return response()->json($this->loginProxy->attemptClientLogin());
    }

    public function refresh(Request $request)
    {
        return response()->json($this->loginProxy->attemptRefresh());
    }

    public function logout(Request $request)
    {
        $this->loginProxy->logout();

        return response()->json(null, 204);
    }
}
