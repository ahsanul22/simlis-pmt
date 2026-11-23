<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Concerns\ListsLicenses;
use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    use ListsLicenses;

    public function index(Request $request): View
    {
        return view('manajemen.licenses.index', $this->licenseIndexData($request));
    }

    public function show(License $license): View
    {
        return view('manajemen.licenses.show', $this->licenseShowData($license));
    }
}
