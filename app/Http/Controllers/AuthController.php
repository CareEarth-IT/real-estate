<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CareEarthAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function logout(Request $request): RedirectResponse
    {
        CareEarthAuth::logout($request);

        return redirect()->route('properties.index');
    }
}
