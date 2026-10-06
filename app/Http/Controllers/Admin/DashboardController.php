<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Overview;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * The overview: what is waiting for a person, how the chosen window sold
     * against the one before, where the orders stand, and what moves. Every
     * figure comes from App\Support\Overview; the view only draws them.
     */
    public function index(Request $request)
    {
        $overview = new Overview(Overview::window($request->query('days')));

        return view('admin.dashboard', [
            'overview' => $overview,
            'waiting' => $overview->waiting(),
            'figures' => $overview->figures(),
            'daily' => $overview->daily(),
            'pipeline' => $overview->pipeline(),
            'oldestPending' => $overview->oldestPending(),
            'bestSellers' => $overview->bestSellers(),
            'mostSaved' => $overview->mostSaved(),
            'recentOrders' => $overview->recentOrders(),
        ]);
    }
}
