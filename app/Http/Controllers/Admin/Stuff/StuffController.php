<?php

namespace App\Http\Controllers\Admin\Stuff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Validation\ValidatesRequests;

class StuffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('id', 'DESC');

        $query->where('type', '=', config('app.user_type.admin'));
        $data = $query->paginate(20);
        return view('stuff.index', compact('data'))->with('i', ($request->input('page', 1) - 1) * 20);
    }
}
