<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class PermissionService
{
    public function __construct()
    {
        //
    }

    public function dataTable()
    {
        $roles = Role::where('name', '!=', 'admin')->with('permissions')->get();

        return DataTables::of($roles)
            ->addColumn('permissions', function ($role) {
                $permissions = '';
                foreach ($role->permissions as $permission) {
                    $permissions .= '<span class="badge bg-light text-primary">' . $permission->name . ' <a href="#" class="delete-permission" data-permission-id="' . $permission->id . '" data-role-id="' . $role->id . '"><i class="fas fa-times"></i></a></span> ';
                }
                return $permissions;
            })
            ->addColumn('action', function ($role) {
                $actionBtn = '';
                if (Gate::allows('update konfigurasi/permissions')) {
                    $actionBtn .= '<button type="button" name="edit" data-id="' . $role->id . '" class="editRole btn btn-primary btn-sm me-2"><i class="ti ti-plus"></i></button>';
                }
                return '<div class="d-flex">' . $actionBtn . '</div>';
            })
            ->rawColumns(['permissions', 'action'])
            ->make(true);
    }

    public function getRolePermission($roleId)
    {
        // get role permission
        $rolePermissions = Role::findOrFail($roleId)->permissions()->pluck('id');

        $permissions = Permission::whereNotIn('id', $rolePermissions)->get();

        return $permissions;
    }

    public function destroy(Permission $permission, Role $role)
    {
        try {
            // check permission
            if ($role->permissions()->where('id', $permission->id)->exists()) {

                // remove permission
                $role->permissions()->detach($permission->id);

                // remove permission from cache
                Artisan::call('permission:cache-reset');

                return [
                    'status' => true,
                    'message' => 'Permission berhasil dihapus.'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Permission tidak ditemukan.'
                ];
            }
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ];
        }
    }

    public function createPermission($requestData)
    {
        try {
            $permissions = $requestData['permissions'];
            if (!empty($permissions)) {
                $roleId = $requestData['roleId'];

                // check role
                $role = Role::findOrFail($roleId);
                $success = true;

                // add permission in loop
                foreach ($permissions as $permission) {
                    $permissionName = Permission::find($permission)->name;

                    if ($permissionName) {
                        $role->givePermissionTo($permissionName);
                        Artisan::call('permission:cache-reset');
                    } else {
                        $success = false;
                    }
                }

                if ($success) {
                    return [
                        'status' => true,
                        'message' => 'Semua permission berhasil ditambahkan.'
                    ];
                } else {
                    return [
                        'status' => false,
                        'message' => 'Beberapa permission gagal ditambahkan.'
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan permission: ' . $e->getMessage()
            ];
        }
    }
}
