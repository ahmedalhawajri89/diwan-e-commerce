<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_users' => User::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'recent_orders' => Order::with('user')->latest()->take(5)->get(),

            // The fit system only works on measured pieces, so the one number
            // an operator can act on is how many are still unmeasured. It was
            // being computed in the view; it is a statistic like the rest.
            'unmeasured' => ProductVariant::whereNull('width_cm')
                ->orWhereNull('depth_cm')
                ->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
