<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'rol', 'active', 'can_access_children_module', 'codigo_medico', 'servicio']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        User::create($validated);

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('modify-user', $user);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->only(['id', 'name', 'email', 'rol', 'codigo_medico', 'servicio', 'can_access_children_module']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('modify-user', $user);

        $validated = $this->validateUser($request, $user);

        $user->update($validated);

        return redirect()->route('admin.users.index');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        Gate::authorize('modify-user', $user);

        $newPassword = Str::password(12);

        // El cast 'hashed' del modelo User se encarga de hashear el valor.
        $user->update(['password' => $newPassword]);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('generatedPassword', $newPassword);
    }

    /**
     * Activa o desactiva la cuenta. Misma protección de super_admin que el
     * resto de acciones sobre usuarios, más una regla propia de esta acción:
     * nadie puede desactivarse a sí mismo (para no quedar fuera por error) —
     * esa restricción no aplica a editar el propio perfil, así que vive acá
     * y no en la Gate 'modify-user' genérica.
     */
    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('modify-user', $user);

        abort_if($request->user()->is($user), 403, 'No puedes desactivar tu propia cuenta.');

        $user->update(['active' => ! $user->active]);

        return redirect()->route('admin.users.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUser(Request $request, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => $user ? ['sometimes'] : ['required', 'string', 'min:8'],
            'rol' => ['required', Rule::in(['admin', 'medico', 'odontologo', 'voluntario'])],
            'codigo_medico' => ['nullable', 'string', 'max:255'],
            'servicio' => ['nullable', 'string', 'max:255'],
            'can_access_children_module' => ['sometimes', 'boolean'],
        ]);

        if ($validated['rol'] === 'admin') {
            $validated['codigo_medico'] = null;
            $validated['servicio'] = null;
        }

        if ($user) {
            unset($validated['password']);
        }

        return $validated;
    }
}
