<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', [
            'users' => User::with('roleDefinition')->orderBy('name')->get(),
            'roles' => Role::whereNotIn('slug',['admin','customer'])->orderByDesc('is_system')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $allowedRoles = Role::whereNotIn('slug',['admin','customer'])->pluck('slug')->all();
        $data = $request->validate([
            'name'=>['required','string','max:100'], 'email'=>['required','email','unique:users,email'], 'phone'=>['nullable','string','max:30'],
            'password'=>['required','min:8'], 'role'=>['required', Rule::in($allowedRoles)],
        ]);
        $data['is_active'] = true;
        User::create($data);
        return back()->with('status', 'Staff account created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $rules = ['name'=>['required','string','max:100'],'phone'=>['nullable','string','max:30'],'is_active'=>['required','boolean']];
        if ($user->role !== 'customer' && $user->role !== 'admin') {
            $rules['role'] = ['required', Rule::exists('roles','slug')];
        }
        $data = $request->validate($rules);
        if ($user->id === $request->user()->id && (int)$data['is_active'] === 0) return back()->withErrors(['is_active'=>'You cannot disable your own account.']);
        if ($user->role === 'admin') unset($data['role']);
        if ($user->role === 'customer') unset($data['role']);
        $user->update($data);
        return back()->with('status', 'User updated successfully.');
    }
}
