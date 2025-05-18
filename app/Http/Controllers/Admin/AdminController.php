<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin|manager']);
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_rooms' => Room::count(),
            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
        ];

        $recent_reservations = Reservation::with(['user', 'room'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_reservations'));
    }

    public function users()
    {
        $users = User::with('roles')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function rooms()
    {
        $rooms = Room::with('roomType')->paginate(10);
        return view('admin.rooms.index', compact('rooms'));
    }

    public function reservations()
    {
        $reservations = Reservation::with(['user', 'room'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.reservations.index', compact('reservations'));
    }

    public function reports()
    {
        return view('admin.reports.index');
    }
} 