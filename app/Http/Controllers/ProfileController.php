<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()->load('branch')]);
    }

    public function update(): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function updatePassword(): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
