<?php

namespace App\Http\Controllers;

class TypesController extends Controller
{
    public function index()
    {
        return response()->json([
            'levels'    => config('types/course.levels'),
            'roles'     => config('types/user.roles'),
            'statuses'  => config('types/order.statuses'),
            'affiliate' => config('types.affiliate'),
        ]);
    }
}
