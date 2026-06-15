<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PembayaranEmailController extends Controller
{
    public function show($id)
    {
        return abort(404, 'Payment email preview is not available.');
    }

    public function send(Request $request, $id)
    {
        return abort(404, 'Payment email sending is not available.');
    }
}
