<?php

namespace App\Http\Controllers;
use App\Models\Event;

class HomeController extends Controller
{
    /**
     * Shows the about us page.
     *
     * @return Response
     */
    public function home()
    {
        $events = Event::where('isprivate', 0)->orderBy('avg_rating', 'desc')->get();

        return view('pages.home', ['events' => $events]);
    }

    /**
     * Shows the login page.
     *
     * @return Response
     */
    public function login()
    {
        return view('auth.login');
    }

}
