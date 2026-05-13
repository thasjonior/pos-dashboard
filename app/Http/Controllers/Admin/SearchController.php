<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Collection;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        if ($q === '') {
            return back();
        }

        $machines = Machine::with('company')
            ->where(fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('serial_number', 'like', "%{$q}%"))
            ->limit(10)->get();

        $collectors = User::collectors()
            ->where(fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%"))
            ->limit(10)->get();

        $clients = Client::where(fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%"))
            ->limit(10)->get();

        $collections = Collection::with(['machine.company'])
            ->where('receipt_id', 'like', "%{$q}%")
            ->limit(10)->get();

        if ($request->get('format') === 'json') {
            return response()->json(compact('machines', 'collectors', 'clients', 'collections'));
        }

        return view('admin.search.index', compact('q', 'machines', 'collectors', 'clients', 'collections'));
    }
}
