<?php
namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;
class CategoryController extends Controller
{
    public function index(){return view('categories.index',['categories'=>Category::withCount('products')->orderBy('name')->get()]);}
    public function store(Request $request){$d=$request->validate(['name'=>['required','max:100','unique:categories,name'],'description'=>['nullable','string']]);$d['status']=true;Category::create($d);return back()->with('status','Category created.');}
    public function update(Request $request,Category $category){$d=$request->validate(['name'=>['required','max:100','unique:categories,name,'.$category->id],'description'=>['nullable','string'],'status'=>['nullable','boolean']]);$category->update($d);return back()->with('status','Category updated.');}
    public function destroy(Category $category){if($category->products()->exists()) return back()->withErrors(['category'=>'Cannot delete a category that has products.']);$category->delete();return back()->with('status','Category deleted.');}
}
