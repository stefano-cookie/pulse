<?php

namespace App\Http\Controllers;

use App\Events\NotificationSent;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index');
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
