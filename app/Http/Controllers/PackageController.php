<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\CartService;
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

        // Get cart items to check which packages are already in cart
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems();
        $cartPackageIds = array_keys($cartItems);

        return view('packages.index', compact('packages', 'cartPackageIds'));
    }

    public function show(Package $package)
    {
        if (!$package->is_active) {
            abort(404);
        }

        // Get cart items to check if this package is already in cart
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems();
        $isInCart = array_key_exists($package->id, $cartItems);

        return view('packages.show', compact('package', 'isInCart'));
    }
}
