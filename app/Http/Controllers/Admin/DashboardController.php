<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Overview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'today' => $overview->today(),
            'greeting' => Overview::greeting(),
            'firstName' => Str::before(trim((string) $request->user()?->name), ' '),
            'waiting' => $overview->waiting(),
            'figures' => $overview->figures(),
            'daily' => $overview->daily(),
            'pipeline' => $overview->pipeline(),
            // Not `wayIn`: every view is already handed the visitor's own.
            'deliveries' => $overview->wayIn(),
            'returnReasons' => $overview->returnReasons(),
            'oldestPending' => $overview->oldestPending(),
            'bestSellers' => $overview->bestSellers(),
            'mostSaved' => $overview->mostSaved(),
            'recentOrders' => $overview->recentOrders(),
        ]);
    }
}
