<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames();

        return view('dashboard.index', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }
}
