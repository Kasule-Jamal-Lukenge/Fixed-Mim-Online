<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller{

    public function allCustomers() {
        $customers = User::where('role', 'buyer')
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($customers);
    }

}
