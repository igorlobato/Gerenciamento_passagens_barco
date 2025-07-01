<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Role::all();
    }

    protected function verificarPermissaoRoles(): void
    {
        if (!Auth::user()->hasPermissionTo('papel_permissao')) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('roles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $role = Role::find($id);
            if(!$role) {
                return response()->json(['error' => 'Perfil não encontrado', 404]);
            }

            $authUser = JWTAuth::user();
            if (!$authUser || $authUser->id != $id) {
                return response()->json(['error' => 'Acesso não autorizado.'], 403);
            }

            return response()->json([$role], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar perfil: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar perfil.'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $role->update($validated);

        return redirect(route('roles.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('roles.index');
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        $role->givePermissionTo($permission);

        return back()->with('success', 'Permissão atribuída com sucesso!');
    }

    public function revokePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        $role->revokePermissionTo($permission);

        return back()->with('success', 'Permissão removida com sucesso!');
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        $user->assignRole($role);

        return back()->with('success', 'Papel atribuído com sucesso!');
    }

    public function revokeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        $user->removeRole($role);

        return back()->with('success', 'Papel removido com sucesso!');
    }
}
