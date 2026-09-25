<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportExportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->technician = User::factory()->create(['role' => 'technician']);
    }

    private function completedOrder(string $code = 'SRV-202609-9101'): ServiceOrder
    {
        $customer = Customer::create(['name' => 'Ekspor Pelanggan', 'phone' => '08'.random_int(100000000, 999999999)]);
        $part = Sparepart::create([
            'part_code' => 'EXP-'.uniqid(),
            'name' => 'Part Ekspor',
            'category' => 'Uji',
            'stock' => 10,
            'buy_price' => 5000,
            'sell_price' => 7500,
        ]);

        $order = ServiceOrder::create([
            'service_code' => $code,
            'customer_id' => $customer->id,
            'device_name' => 'Perangkat Ekspor',
            'issue_description' => 'Uji ekspor laporan',
            'status' => 'completed',
            'labor_cost' => 120000,
            'total_cost' => 127500,
        ]);

        $order->orderParts()->create([
            'sparepart_id' => $part->id,
            'quantity' => 1,
            'unit_price' => 7500,
            'subtotal' => 7500,
        ]);

        return $order;
    }

    private function export(string $format = 'csv')
    {
        return $this->post(route('reports.export'), [
            'format' => $format,
            'start' => now()->startOfMonth()->toDateString(),
            'end' => now()->toDateString(),
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_export_report_and_file_is_written_to_disk(): void
    {
        $this->actingAs($this->admin);
        $order = $this->completedOrder();

        $response = $this->export('csv');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $files = Storage::disk('local')->files('exports');
        $this->assertCount(1, $files, 'Satu berkas arsip harus tertulis di media penyimpanan.');

        $contents = Storage::disk('local')->get($files[0]);
        $this->assertStringContainsString($order->service_code, $contents);
        $this->assertStringContainsString('Perangkat Ekspor', $contents);
        $this->assertStringContainsString('Nomor Nota', $contents);
    }

    public function test_exported_file_can_be_read_back_through_download_route(): void
    {
        $this->actingAs($this->admin);
        $this->completedOrder();
        $this->export('csv');

        $name = basename(Storage::disk('local')->files('exports')[0]);

        $this->get(route('reports.export.download', ['file' => $name]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertSee('Nomor Nota');
    }

    public function test_json_export_is_written_with_column_named_records(): void
    {
        $this->actingAs($this->admin);
        $order = $this->completedOrder('SRV-202609-9102');

        $this->export('json')->assertRedirect();

        $files = Storage::disk('local')->files('exports');
        $this->assertStringEndsWith('.json', $files[0]);

        $decoded = json_decode(Storage::disk('local')->get($files[0]), true);
        $this->assertSame(1, $decoded['total']);
        $this->assertSame($order->service_code, $decoded['rows'][0]['Nomor Nota']);
    }

    public function test_report_page_lists_archived_export_files(): void
    {
        $this->actingAs($this->admin);
        $this->completedOrder();
        $this->export('csv');

        $name = basename(Storage::disk('local')->files('exports')[0]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Ekspor &amp; Arsip Berkas', false)
            ->assertSee($name);
    }

    public function test_technician_cannot_export_or_download_reports(): void
    {
        $this->actingAs($this->technician);

        $this->export('csv')->assertForbidden();
        $this->get(route('reports.export.download', ['file' => 'apa-saja.csv']))->assertForbidden();
        $this->assertEmpty(Storage::disk('local')->files('exports'));
    }

    public function test_unknown_format_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $this->export('xlsx')->assertSessionHasErrors('format');
        $this->assertEmpty(Storage::disk('local')->files('exports'));
    }

    public function test_download_of_missing_file_returns_not_found(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('reports.export.download', ['file' => 'tidak-ada.csv']))->assertNotFound();
    }

    public function test_download_rejects_path_traversal_attempt(): void
    {
        $this->actingAs($this->admin);

        // Segmen dengan garis miring tidak lolos batasan pola rute.
        $this->get('/reports/export/..%2F..%2F.env')->assertNotFound();
    }

    public function test_activity_log_uses_polymorphic_relation_back_to_subject(): void
    {
        $customer = Customer::create(['name' => 'Audit Pelanggan', 'phone' => '081299999001']);

        $log = ActivityLog::where('subject_type', 'customer')->where('subject_id', $customer->id)->firstOrFail();

        $this->assertSame('created', $log->action);
        $this->assertInstanceOf(Customer::class, $log->subject, 'morphTo harus mengembalikan model sumbernya.');
        $this->assertSame($customer->id, $log->subject->id);
        $this->assertSame(1, $customer->activityLogs()->count());
    }

    public function test_activity_log_records_only_changed_attributes_on_update(): void
    {
        $customer = Customer::create(['name' => 'Audit Update', 'phone' => '081299999002']);
        $customer->update(['name' => 'Audit Update Baru']);

        $update = ActivityLog::where('subject_id', $customer->id)->where('action', 'updated')->firstOrFail();

        $this->assertArrayHasKey('name', $update->properties['new']);
        $this->assertSame('Audit Update Baru', $update->properties['new']['name']);
        $this->assertSame('Audit Update', $update->properties['old']['name']);
        $this->assertArrayNotHasKey('updated_at', $update->properties['new']);
    }
}
