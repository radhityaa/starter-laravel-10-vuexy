<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('can:read users');
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $title = 'Users';
        if ($request->ajax()) {
            return $this->userService->datatable();
        }

        return view('users.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Tambah User';
        return view('users.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $result = $this->userService->create($request->all());

        if ($result['success']) {
            notyf()
                ->position('x', 'center')
                ->position('y', 'top')
                ->addSuccess($result['message']);
            return redirect()->route('users.index');
        } else {
            notyf()
                ->position('x', 'center')
                ->position('y', 'top')
                ->addError($result['message']);
            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $title = 'Edit User';

        return view('users.edit', compact('title', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $result = $this->userService->update($request->all(), $user);

        if ($result['success']) {
            notyf()
                ->position('x', 'center')
                ->position('y', 'top')
                ->addSuccess($result['message']);
            return redirect()->route('users.index');
        } else {
            notyf()
                ->position('x', 'center')
                ->position('y', 'top')
                ->addError($result['message']);
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = $this->userService->delete($id);

        return response()->json($result);
    }
}
