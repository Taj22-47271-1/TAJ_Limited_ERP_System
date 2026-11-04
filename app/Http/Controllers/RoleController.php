<?php
namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public static function permissionGroups(): array
    {
        return [
            'Dashboard' => ['dashboard.view'=>'View dashboard'],
            'Products & POS' => [
                'products.view'=>'Browse products','products.manage'=>'Add and edit products','pos.use'=>'Use in-house POS','sales.view'=>'View sales/invoices',
            ],
            'Online Orders' => [
                'orders.view'=>'View online orders & pending badge','orders.approve'=>'Approve online orders','orders.progress'=>'Update approved order progress',
            ],
            'Inventory & Purchasing' => [
                'inventory.view'=>'View stock ledger','inventory.manage'=>'Adjust stock & delete products','purchases.manage'=>'Manage purchases','suppliers.manage'=>'Manage suppliers',
            ],
            'Customers' => ['customers.manage'=>'Manage customer records'],
            'Customer Self-Service' => ['shop.use'=>'Browse customer shop','orders.own'=>'Place and track own online orders'],
            'Accounting & Reports' => [
                'accounting.view'=>'View accounting journals','accounting.manage'=>'Manage expenses/accounting','reports.view'=>'View financial reports and total profit',
            ],
            'Storefront & 3D Atelier' => [
                'showcase.manage'=>'Manage 3D Clothing Showcase, Hotspots & Fabric Loupe',
            ],
            'Administration' => [
                'users.manage'=>'Create/deactivate staff and customers','roles.manage'=>'Manage roles and permissions','settings.manage'=>'Manage company settings',
            ],
        ];
    }

    public function index()
    {
        return view('admin.roles', ['roles'=>Role::withCount('users')->orderByDesc('is_system')->orderBy('name')->get(),'permissionGroups'=>self::permissionGroups()]);
    }

    public function store(Request $request)
    {
        $data=$request->validate(['name'=>['required','string','max:80','unique:roles,name'],'permissions'=>['nullable','array'],'permissions.*'=>['string']]);
        $slug=Str::slug($data['name'],'_');
        if($slug===''||Role::where('slug',$slug)->exists()) return back()->withErrors(['name'=>'Please use a unique role name.'])->withInput();
        Role::create(['name'=>$data['name'],'slug'=>$slug,'permissions'=>array_values($data['permissions']??[]),'is_system'=>false]);
        return back()->with('status','New internal staff role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $data=$request->validate(['name'=>['required','string','max:80',Rule::unique('roles','name')->ignore($role->id)],'permissions'=>['nullable','array'],'permissions.*'=>['string']]);
        if ($role->slug === 'admin') $data['permissions'] = ['*'];
        $role->update(['name'=>$data['name'],'permissions'=>array_values($data['permissions']??[])]);
        return back()->with('status','Role permissions updated.');
    }

    public function destroy(Role $role)
    {
        if($role->is_system || $role->users()->exists()) return back()->withErrors(['role'=>'System roles or roles assigned to users cannot be deleted.']);
        $role->delete(); return back()->with('status','Role deleted.');
    }
}
