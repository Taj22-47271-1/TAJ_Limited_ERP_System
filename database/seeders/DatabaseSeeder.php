<?php
namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['Admin','admin',['*'],true],
            ['Cashier','cashier',['dashboard.view','products.view','products.manage','pos.use','sales.view','orders.view','orders.approve','orders.progress','customers.manage'],true],
            ['Customer','customer',['dashboard.view','shop.use','orders.own'],true],
        ];
        foreach ($roles as [$name,$slug,$permissions,$system]) {
            Role::query()->updateOrCreate(['slug'=>$slug], ['name'=>$name,'permissions'=>$permissions,'is_system'=>$system]);
        }

        $admin = User::query()->firstOrCreate(
            ['email'=>'admin@tajlimited.com'],
            ['name'=>'TAJ Administrator','phone'=>'01700000000','password'=>Hash::make('Password123!'),'role'=>'admin','is_active'=>true]
        );
        User::query()->firstOrCreate(
            ['email'=>'cashier@tajlimited.com'],
            ['name'=>'Demo Cashier','phone'=>'01700000001','password'=>Hash::make('Password123!'),'role'=>'cashier','is_active'=>true]
        );
        $customerUser = User::query()->firstOrCreate(
            ['email'=>'customer@tajlimited.com'],
            ['name'=>'Demo Customer','phone'=>'01800000001','password'=>Hash::make('Password123!'),'role'=>'customer','is_active'=>true]
        );

        Setting::query()->firstOrCreate(['id'=>1], [
            'company_name'=>'TAJ Limited','company_email'=>'info@tajlimited.com','company_phone'=>'+880 1XXXXXXXXX','company_address'=>'Dhaka, Bangladesh','currency_symbol'=>'৳',
        ]);

        foreach ([
            ['1000','Assets','asset'],['1100','Cash','asset'],['1200','Accounts Receivable','asset'],['1300','Inventory','asset'],
            ['2000','Liabilities','liability'],['2100','Accounts Payable','liability'],['3000','Equity','equity'],['3100','Owner Capital','equity'],
            ['4000','Revenue','revenue'],['4100','Sales Revenue','revenue'],['5000','Cost of Goods Sold','expense'],['5100','Cost of Goods Sold','expense'],
            ['6000','Expenses','expense'],['6100','Operating Expenses','expense'],
        ] as [$code,$name,$type]) {
            Account::query()->firstOrCreate(['code'=>$code], ['name'=>$name,'type'=>$type,'is_system'=>true]);
        }

        $cats=[];
        foreach (['Electronics','Groceries','Office Supplies','Beverages','Accessories','Cloth','Watch'] as $name) {
            $cats[$name]=Category::query()->firstOrCreate(['name'=>$name],['status'=>true]);
        }

        Supplier::query()->firstOrCreate(['email'=>'supplier1@example.com'],['name'=>'ABC Traders','company'=>'ABC Traders','phone'=>'01710000001','address'=>'Dhaka','status'=>true]);
        Supplier::query()->firstOrCreate(['email'=>'supplier2@example.com'],['name'=>'Global Supply Co.','company'=>'Global Supply Co.','phone'=>'01710000002','address'=>'Gazipur','status'=>true]);
        Customer::query()->firstOrCreate(['user_id'=>$customerUser->id],['name'=>$customerUser->name,'phone'=>$customerUser->phone,'email'=>$customerUser->email,'address'=>'Uttara, Dhaka','customer_type'=>'retail','status'=>true]);
        Customer::query()->firstOrCreate(['phone'=>'01800000002'],['name'=>'Rahman Enterprise','email'=>'customer2@example.com','customer_type'=>'wholesale','status'=>true]);

        $products=[
            ['Wireless Mouse','WM-001','100000000001','Accessories',450,650,55,10,'Comfortable wireless mouse for office and home use.','assets/images/products/wireless-mouse.svg',false,[]],
            ['Mechanical Keyboard','KB-002','100000000002','Accessories',1800,2400,28,5,'Mechanical keyboard with durable switches.','assets/images/products/mechanical-keyboard.svg',false,[]],
            ['USB-C Hub','HUB-003','100000000003','Electronics',1250,1750,18,5,'Multi-port USB-C hub for modern laptops.','assets/images/products/usb-c-hub.svg',false,[]],
            ['A4 Paper Ream','PAPER-004','100000000004','Office Supplies',420,520,80,15,'Premium A4 paper, 500 sheets.','assets/images/products/a4-paper.svg',false,[]],
            ['Mineral Water 500ml','WATER-005','100000000005','Beverages',15,25,150,30,'500ml bottled drinking water.','assets/images/products/mineral-water.svg',false,[]],
            ['Instant Coffee Jar','COFFEE-006','100000000006','Groceries',280,360,45,8,'Instant coffee for office pantry use.','assets/images/products/instant-coffee.svg',false,[]],
            ['HDMI Cable 2m','HDMI-007','100000000007','Electronics',250,400,32,8,'High-speed 2 meter HDMI cable.','assets/images/products/hdmi-cable.svg',false,[]],
            ['Notebook Premium','NOTE-008','100000000008','Office Supplies',70,110,95,20,'Premium ruled notebook.','assets/images/products/notebook.svg',false,[]],
            ['Designer Silk Saree','SAREE-009','100000000009','Cloth',2800,4500,25,5,'Handcrafted Designer Silk Saree with intricate golden zari border, royal drape silhouette and pure Mulberry silk.','assets/images/products/designer-saree.png',true,['Free Size','S','M','L','XL']],
        ];
        foreach($products as [$name,$sku,$barcode,$cat,$buy,$sell,$stock,$min,$description,$imagePath,$hasSizes,$sizes]){
            $product=Product::query()->updateOrCreate(['sku'=>$sku],[
                'category_id'=>$cats[$cat]->id,'name'=>$name,'barcode'=>$barcode,'unit'=>'pcs','purchase_price'=>$buy,'selling_price'=>$sell,
                'stock'=>$stock,'reserved_stock'=>0,'min_stock'=>$min,'status'=>true,'description'=>$description,'image_path'=>$imagePath,
                'has_sizes'=>$hasSizes,'size_options'=>$sizes,
            ]);
            StockMovement::query()->firstOrCreate(
                ['product_id'=>$product->id,'movement_type'=>'opening_stock','reference_type'=>'seed','reference_id'=>$product->id],
                ['quantity_in'=>$product->stock,'quantity_out'=>0,'balance_after'=>$product->stock,'unit_cost'=>$product->purchase_price,'note'=>'Seed opening stock','created_by'=>$admin->id]
            );
        }

        if (Sale::query()->count()===0) {
            $demoProducts=Product::query()->take(4)->get();
            for($i=6;$i>=0;$i--){
                $date=now()->subDays($i)->toDateString(); $subtotal=0;
                $sale=Sale::create(['customer_id'=>null,'invoice_no'=>'DEMO-'.now()->format('ymd').'-'.($i+1),'sale_date'=>$date,'subtotal'=>0,'discount'=>0,'tax'=>0,'total'=>0,'paid_amount'=>0,'due_amount'=>0,'payment_method'=>'cash','status'=>'completed','created_by'=>$admin->id]);
                foreach($demoProducts->take(2) as $index=>$product){$qty=($index+1)+($i%2);$line=$qty*(float)$product->selling_price;$subtotal+=$line;SaleItem::create(['sale_id'=>$sale->id,'product_id'=>$product->id,'quantity'=>$qty,'unit_price'=>$product->selling_price,'unit_cost'=>$product->purchase_price,'line_total'=>$line]);}
                $sale->update(['subtotal'=>$subtotal,'total'=>$subtotal,'paid_amount'=>$subtotal]);
            }
        }

        if (Expense::query()->count()===0) {
            Expense::create(['expense_date'=>now()->toDateString(),'category'=>'Utilities','description'=>'Internet and utilities','amount'=>3500,'payment_method'=>'cash','created_by'=>$admin->id]);
            Expense::create(['expense_date'=>now()->subDays(2)->toDateString(),'category'=>'Transport','description'=>'Local delivery expense','amount'=>1800,'payment_method'=>'cash','created_by'=>$admin->id]);
        }
    }
}
