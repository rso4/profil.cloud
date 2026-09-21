<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenant = current_tenant();

        // Isolasi data: hanya data milik tenant yang sedang login
        $user = $request->user();
        if ($user->tenant_id !== $tenant->id) {
            abort(403, 'Akses lintas tenant ditolak.');
        }

        $stats = [
            'services' => $tenant->services()->count(),
            'galleries' => $tenant->galleries()->count(),
            'contacts' => $tenant->contacts()->count(),
            'pages' => $tenant->pages()->count(),
            'published' => $tenant->posts()->published()->count(),
            'drafts' => $tenant->posts()->where('status', 'draft')->count(),
            'media' => $tenant->media()->count(),
            'categories' => $tenant->categories()->count(),
        ];

        // Artikel terbaru untuk ringkasan aktivitas konten
        $recentPosts = $tenant->posts()->latest('id')->take(5)->get();

        return view('admin.dashboard', compact('tenant', 'stats', 'recentPosts'));
    }
}
