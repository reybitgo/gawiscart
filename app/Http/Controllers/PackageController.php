<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::active()->available()->ordered();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            switch ($request->get('sort')) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'points_high':
                    $query->orderBy('points_awarded', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
            }
        }

        $packages = $query->paginate(12);

        return view('packages.index', compact('packages'));
    }

    public function show(Package $package)
    {
        if (!$package->is_active) {
            abort(404);
        }

        return view('packages.show', compact('package'));
    }
}
