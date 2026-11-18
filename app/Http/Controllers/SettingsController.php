<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->facilityIdsForPermission('users.manage') === null, 403);

        return view('settings.index', ['permissions' => Permission::orderBy('module')->orderBy('name')->get()]);
    }
}
