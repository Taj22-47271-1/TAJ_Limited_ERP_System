<?php
namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_customer_only(): void
    {
        $this->seed();
        $response = $this->post('/register', [
            'name'=>'New Customer','email'=>'newcustomer@example.com','phone'=>'01711111111',
            'password'=>'Password123!','password_confirmation'=>'Password123!',
        ]);
        $response->assertRedirect('/dashboard');
        $user = User::where('email','newcustomer@example.com')->firstOrFail();
        $this->assertSame('customer',$user->role);
        $this->assertTrue(Customer::where('user_id',$user->id)->exists());
    }

    public function test_user_can_login(): void
    {
        $this->seed();
        $response = $this->post('/login', [
            'email' => 'admin@tajlimited.com',
            'password' => 'Password123!',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_cashier_cannot_open_accounting_or_profit_report(): void
    {
        $this->seed();
        $cashier = User::where('email','cashier@tajlimited.com')->firstOrFail();
        $this->actingAs($cashier)->get('/accounting/journals')->assertForbidden();
        $this->actingAs($cashier)->get('/reports/profit-loss')->assertForbidden();
    }
}
