<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    public function handleCallback(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
