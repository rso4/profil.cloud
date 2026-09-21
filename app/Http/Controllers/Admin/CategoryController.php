<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCategoryRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * CRUD Kategori artikel milik tenant.
 */
class CategoryController extends Controller
{
    use AuthorizesTenant;

    public function index(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $categories = $tenant->categories()
            ->withCount('posts')
            ->orderBy('name')
            ->get();

        return view('admin.categories', compact('tenant', 'categories'));
    }

    public function store(SaveCategoryRequest $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $data = $request->validated();

        $name = $data['name'];
        $tenant->categories()->create([
            'name' => $name,
            'description' => $data['description'] ?? null,
            'slug' => $this->uniqueSlug($tenant, Str::slug($name)),
        ]);

        return back()->with('success', "Kategori '{$name}' berhasil ditambahkan.");
    }

    public function update(SaveCategoryRequest $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $category = $tenant->categories()->findOrFail($id);
        $data = $request->validated();

        $slug = Str::slug($data['name']);
        if ($slug === '') {
            $slug = 'kategori';
        }

        $exists = $tenant->categories()
            ->where('slug', $slug)
            ->where('id', '!=', $category->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Kategori dengan nama tersebut sudah ada.'])->withInput();
        }

        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'slug' => $slug,
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $category = $tenant->categories()->findOrFail($id);
        $name = $category->name;
        $category->delete(); // artikel terkait hanya kehilangan kategori (FK nullOnDelete)

        return back()->with('success', "Kategori '{$name}' berhasil dihapus.");
    }

    private function uniqueSlug(Tenant $tenant, string $slug): string
    {
        if ($slug === '') {
            $slug = 'kategori';
        }

        $base = $slug;
        $i = 1;

        while ($tenant->categories()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
