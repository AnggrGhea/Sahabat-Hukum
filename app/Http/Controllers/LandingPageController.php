<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    /**
     * Display the public landing page for Sahabat Hukum.
     */
    public function index()
    {
        try {
            // Retrieve active advocates with their profiles
            $lawyers = User::where('role', 'advokat')
                ->where('status', 'aktif')
                ->with('lawyerProfile')
                ->orderBy('id')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('LandingPageController: Gagal memuat data advokat: ' . $e->getMessage());
            $lawyers = collect();
        }

        return view('landing', compact('lawyers'));
    }
}
