<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function createTrackedOrder(array $overrides = []): ServiceOrder
    {
        $customer = Customer::create([
            'name' => 'Pelanggan Track',
            'phone' => '081299988877',
        ]);

        return ServiceOrder::create(array_merge([
            'service_code' => 'SRV-202601-0777',
            'customer_id' => $customer->id,
            'device_name' => 'Laptop Acer',
            'issue_description' => 'Tidak bisa charging',
            'status' => 'in_progress',
        ], $overrides));
    }

    public function test_root_url_redirects_to_tracking_portal(): void
    {
        $this->get('/')->assertRedirect(route('tracking.index'));
    }

    public function test_tracking_portal_is_accessible_without_login(): void
    {
        $this->get(route('tracking.index'))->assertOk();
    }

    public function test_tracking_by_exact_service_code_shows_status(): void
    {
        $order = $this->createTrackedOrder();

        $this->get(route('tracking.search', ['query' => 'SRV-202601-0777']))
            ->assertOk()
            ->assertSee('SRV-202601-0777')
            ->assertSee('Laptop Acer');
    }

    public function test_tracking_by_phone_number_redirects_to_order(): void
    {
        $this->createTrackedOrder();

        $this->get(route('tracking.search', ['query' => '081299988877']))
            ->assertRedirect(route('tracking.show', 'SRV-202601-0777'));
    }

    public function test_tracking_with_unknown_code_shows_friendly_error(): void
    {
        $this->get(route('tracking.search', ['query' => 'SRV-199901-9999']))
            ->assertOk()
            ->assertSee('tidak ditemukan');
    }

    public function test_tracking_with_empty_query_redirects_back(): void
    {
        $this->get(route('tracking.search', ['query' => '']))
            ->assertRedirect(route('tracking.index'))
            ->assertSessionHas('error');
    }

    public function test_tracking_show_page_displays_order_details(): void
    {
        $this->createTrackedOrder(['status' => 'ready']);

        $this->get(route('tracking.show', 'SRV-202601-0777'))
            ->assertOk()
            ->assertSee('Laptop Acer')
            ->assertSee('Pelanggan Track');
    }

    public function test_tracking_show_returns_404_for_unknown_code(): void
    {
        $this->get(route('tracking.show', 'SRV-000000-0000'))->assertNotFound();
    }
}
