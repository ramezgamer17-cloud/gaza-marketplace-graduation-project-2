<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function userWithRole(string $role, array $attributes = []): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);

        $user = User::factory()->create($attributes + [
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertSuccessful()->assertSee('تسجيل الدخول');
    }

    public function test_admin_is_redirected_to_admin_panel(): void
    {
        $admin = $this->userWithRole('admin');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_vendor_is_redirected_to_vendor_panel(): void
    {
        $vendor = $this->userWithRole('vendor');
        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر',
            'store_type' => 'supermarket',
        ]);

        $this->post('/login', ['email' => $vendor->email, 'password' => 'password'])
            ->assertRedirect('/vendor');
    }

    public function test_customer_is_redirected_home(): void
    {
        $customer = $this->userWithRole('customer');

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect('/');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $customer = $this->userWithRole('customer');

        $this->post('/login', ['email' => $customer->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $customer = $this->userWithRole('customer', ['is_active' => false]);

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
