<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Collection;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('machines')
            ->withCount(['machines as collections_count' => fn ($q) =>
                $q->join('collections', 'collections.machine_id', '=', 'machines.id')])
            ->get();

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255', 'unique:companies,name'],
            'slug'     => ['nullable', 'regex:/^[a-z0-9-]+$/', 'unique:companies,slug'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (Company::where('slug', $data['slug'])->exists()) {
            return back()->withInput()->withErrors([
                'slug' => "Derived slug \"{$data['slug']}\" already exists. Please provide a unique slug.",
            ]);
        }

        $company = Company::create($data);
        AuditLog::record(auth()->user(), 'created', $company);

        // Pre-create unknown-client fallback if requested
        if ($request->boolean('precreate_fallback')) {
            Client::firstOrCreate(
                ['name' => "unknown-client-{$company->slug}"],
                [
                    'phone'       => null,
                    'address'     => "Default client for {$company->name} collectors",
                    'description' => "Auto-generated default client for {$company->name} collector operations",
                ]
            );
        }

        return redirect()->route('admin.companies.index')->with('success', 'Company created.');
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company->id)],
            'slug'     => ['nullable', 'regex:/^[a-z0-9-]+$/', Rule::unique('companies', 'slug')->ignore($company->id)],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $before = $company->only(['name', 'slug', 'location']);
        $company->update($data);
        AuditLog::record(auth()->user(), 'updated', $company, ['before' => $before, 'after' => $data]);

        return redirect()->route('admin.companies.index')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $machineCount    = $company->machines()->count();
        $collectionCount = Collection::whereHas('machine', fn ($q) => $q->where('company_id', $company->id))->count();

        if ($machineCount > 0 || $collectionCount > 0) {
            return back()->withErrors([
                'delete' => "Cannot delete: has {$machineCount} machine(s) and {$collectionCount} collection(s).",
            ]);
        }

        AuditLog::record(auth()->user(), 'deleted', $company);
        $company->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Company deleted.');
    }
}
