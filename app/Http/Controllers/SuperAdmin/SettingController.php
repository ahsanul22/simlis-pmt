<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('super-admin.settings.index', [
            'thresholds' => Setting::expiryThresholds(),
            'reminderDays' => Setting::reminderDays(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
