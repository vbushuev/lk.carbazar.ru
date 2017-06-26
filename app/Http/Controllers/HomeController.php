<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $rq)
    {
        return view('home',["user"=>$rq->user()]);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function accounts(Request $rq)
    {
        return view('accounts',["user"=>$rq->user()]);
    }
    public function apikeys(Request $rq)
    {
        return view('apikeys',["user"=>$rq->user()]);
    }
    public function clients(Request $rq)
    {
        return view('clients',["user"=>$rq->user()]);
    }
}
