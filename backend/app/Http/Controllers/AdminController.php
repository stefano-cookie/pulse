<?php

namespace App\Http\Controllers;

use App\Events\NotificationSent;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        $devices = Device::latest()->get();
        return view('admin.index', compact('devices'));
    }

    public function sendNotification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        event(new NotificationSent($validated['title'], $validated['description']));

        return redirect()->back()->with('success', 'Notifica inviata con successo!');
    }
}
