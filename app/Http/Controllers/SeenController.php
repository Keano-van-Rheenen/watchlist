<?php

namespace App\Http\Controllers;

use App\Concerns\ManagesWatchables;
use Illuminate\Support\Facades\Auth;

class SeenController extends Controller
{
    use ManagesWatchables;

    /**
     * Display the seen watchables index.
     */
    public function index()
    {
        $watchables = $this->getAllWatchablesForUser((int) Auth::id(), true);

        return view('pages.seenPages.index', compact('watchables'));
    }
}