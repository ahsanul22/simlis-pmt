<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('notifications.index', [
            'notifications' => auth()->user()->notifications()->latest()->paginate(per_page()),
        ]);
    }

    /** Tandai dibaca lalu arahkan ke URL tujuan notifikasi. */
    public function show(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }

    public function readAll(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
