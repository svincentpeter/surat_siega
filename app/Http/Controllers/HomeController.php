<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Notifikasi;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        $unreadCount  = Notifikasi::where('pengguna_id', $user->id)
            ->where('dibaca', false)
            ->count();

        $recentNotifs = Notifikasi::where('pengguna_id', $user->id)
            ->orderByDesc('dibuat_pada')
            ->limit(5)
            ->get();

        return view('home', compact('unreadCount', 'recentNotifs'));
    }
}
