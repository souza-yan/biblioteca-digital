<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildDashboard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, BuildDashboard $buildDashboard): View
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        return view('dashboard', $buildDashboard->handle(
            $actor,
            $request->string('downloadPeriod', '30')->toString(),
        ));
    }
}
