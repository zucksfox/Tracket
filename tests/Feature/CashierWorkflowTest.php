<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_is_complete_and_never_overwrites_or_duplicates_existing_data(): void
    {
        $this->seed();
        $this->assertSame(5, ServiceOrder::count());
        $this->assertSame(5, ServiceOrder::distinct()->count('status'));
        $this->assertSame(10, Sparepart::count());
        $this->assertSame(5, Customer::count());
        $cashier = User::where('role', 'cashier')->firstOrFail();
        $cashier->update(['password' => 'changed-password']);
        $hash = $cashier->fresh()->password;
        $part = Sparepart::first();
        $part->update(['stock' => 99]);
        $order = ServiceOrder::first();
        $order->update(['technician_notes' => 'Do not overwrite']);
        $this->seed();
        $this->assertSame(5, ServiceOrder::count());
        $this->assertSame(99, $part->fresh()->stock);
        $this->assertSame($hash, $cashier->fresh()->password);
        $this->assertSame('Do not overwrite', $order->fresh()->technician_notes);
    }

    public function test_cashier_ui_and_prints_match_permissions(): void
    {
        $this->seed();
        $cashier = User::where('role', 'cashier')->firstOrFail();
        $this->actingAs($cashier);
        $ready = ServiceOrder::where('status', 'ready')->firstOrFail();
        $pending = ServiceOrder::where('status', 'pending')->firstOrFail();
        $this->get(route('dashboard'))->assertOk()->assertSee('Kasir')->assertSee('+ Servis Baru')->assertDontSee('Pendapatan Bulan Ini');
        $this->get(route('services.show', $pending))->assertOk()->assertDontSee('Mulai Diagnosa')->assertDontSee('Pasang ke Unit')->assertDontSee('name="technician_notes"', false);
        $this->get(route('services.show', $ready))->assertOk()->assertSee('name="payment_method"', false)->assertSee('name="confirm_payment"', false);
        $this->get(route('services.print-receipt', $ready))->assertOk();
        $this->get(route('customers.lookup', ['phone' => $ready->customer->phone]))->assertOk();
        $this->post(route('services.checkout', $ready), ['payment_method' => 'cash', 'warranty_days' => 30])->assertRedirect();
        $this->get(route('services.print-invoice', $ready))->assertOk()->assertSee('LUNAS')->assertSee('Tunai')->assertSee($ready->fresh()->paid_at->format('d/m/Y H:i'));
    }

    public function test_master_phone_must_be_numeric_and_cashier_cannot_change_technical_notes_at_checkout(): void
    {
        $this->seed();
        $this->actingAs(User::where('role', 'admin')->firstOrFail());
        $this->post(route('customers.store'), ['name' => 'Invalid', 'phone' => 'not-a-number'])->assertSessionHasErrors('phone');
        $this->actingAs(User::where('role', 'cashier')->firstOrFail());
        $ready = ServiceOrder::where('status', 'ready')->firstOrFail();
        $notes = $ready->technician_notes;
        $this->post(route('services.checkout', $ready), ['payment_method' => 'cash', 'warranty_days' => 30, 'technician_notes' => 'Tampered'])->assertRedirect();
        $this->assertSame($notes, $ready->fresh()->technician_notes);
    }

    public function test_cashier_demo_login_uses_cashier_role(): void
    {
        $cashier = User::factory()->create(['email' => 'kasir@tracket.test', 'role' => 'cashier']);
        $this->get(route('quick-login', 'cashier'))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($cashier);
        $this->get(route('quick-login', 'unknown'))->assertNotFound();
    }

    public function test_admin_can_create_cashier_and_assign_admin_but_not_cashier_as_repairer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->post(route('technicians.store'), ['name' => 'Kasir', 'email' => 'kasir@example.test', 'password' => 'password', 'role' => 'cashier'])->assertRedirect()->assertSessionHasNoErrors();
        $cashier = User::where('email', 'kasir@example.test')->firstOrFail();
        $payload = ['customer_name' => 'Uji', 'customer_phone' => '081234567899', 'device_name' => 'Laptop', 'issue_description' => 'Mati', 'technician_id' => $cashier->id];
        $this->post(route('services.store'), $payload)->assertSessionHasErrors('technician_id');
        $payload['technician_id'] = $admin->id;
        $this->post(route('services.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('service_orders', ['technician_id' => $admin->id]);
        $this->get(route('services.create'))->assertSee($admin->name);
    }
}
