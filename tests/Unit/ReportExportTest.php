<?php

namespace Tests\Unit;

use App\Exports\CsvReportExporter;
use App\Exports\JsonReportExporter;
use App\Exports\ReportColumnMap;
use App\Exports\ReportExporterRegistry;
use App\Exports\ReportExportService;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, string|int|float|null>>}
     */
    private function sampleTable(): array
    {
        return [
            ['No', 'Nomor Nota'],
            [
                [1, 'SRV-202609-0001'],
                [2, 'SRV-202609-0002'],
            ],
        ];
    }

    public function test_csv_exporter_implements_contract_and_declares_its_format(): void
    {
        $exporter = new CsvReportExporter;

        $this->assertSame('csv', $exporter->extension());
        $this->assertSame('text/csv; charset=UTF-8', $exporter->mimeType());
        $this->assertSame('laporan.csv', $exporter->fileName('laporan'));
    }

    public function test_json_exporter_implements_contract_and_declares_its_format(): void
    {
        $exporter = new JsonReportExporter;

        $this->assertSame('json', $exporter->extension());
        $this->assertSame('application/json; charset=UTF-8', $exporter->mimeType());
        $this->assertSame('laporan.json', $exporter->fileName('laporan'));
    }

    public function test_csv_render_writes_header_and_all_rows_with_semicolon_delimiter(): void
    {
        [$header, $rows] = $this->sampleTable();

        $contents = (new CsvReportExporter)->render($header, $rows);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $contents, 'Berkas harus diawali BOM UTF-8 untuk Excel.');
        // fputcsv mengapit kolom yang berisi spasi dengan tanda kutip — itu
        // perilaku CSV yang benar, bukan cacat.
        $this->assertStringContainsString('No;"Nomor Nota"', $contents);
        $this->assertStringContainsString('1;SRV-202609-0001', $contents);
        $this->assertStringContainsString('2;SRV-202609-0002', $contents);
        $this->assertSame(3, substr_count(trim($contents), "\n") + 1, 'Dua baris data plus satu baris judul.');
    }

    public function test_json_render_produces_column_named_records(): void
    {
        [$header, $rows] = $this->sampleTable();

        $decoded = json_decode((new JsonReportExporter)->render($header, $rows), true);

        $this->assertSame(['No', 'Nomor Nota'], $decoded['columns']);
        $this->assertSame(2, $decoded['total']);
        $this->assertSame(['No' => '1', 'Nomor Nota' => 'SRV-202609-0001'], $decoded['rows'][0]);
    }

    public function test_cell_normalisation_keeps_numbers_machine_readable_and_nulls_empty(): void
    {
        $contents = (new CsvReportExporter)->render(
            ['Angka', 'Kosong'],
            [[1250.5, null]],
        );

        $this->assertStringContainsString('1250.50;', $contents);
        $this->assertStringNotContainsString('null', $contents);
    }

    public function test_registry_resolves_supported_formats_and_rejects_unknown_ones(): void
    {
        $registry = app(ReportExporterRegistry::class);

        $this->assertSame(['csv', 'json'], $registry->formats());
        $this->assertInstanceOf(CsvReportExporter::class, $registry->make('CSV'));
        $this->assertInstanceOf(JsonReportExporter::class, $registry->make('json'));
        $this->assertInstanceOf(CsvReportExporter::class, $registry->forFile('laporan-servis-2026-09-01.csv'));
        $this->assertTrue($registry->supports('json'));

        $this->expectException(InvalidArgumentException::class);
        $registry->make('pdf');
    }

    public function test_registry_optional_fallback_is_used_when_format_unknown(): void
    {
        $registry = app(ReportExporterRegistry::class);
        $fallback = new CsvReportExporter;

        $this->assertSame($fallback, $registry->make('xlsx', $fallback));
    }

    public function test_column_map_header_matches_every_row_length(): void
    {
        $customer = Customer::create(['name' => 'Kolom', 'phone' => '081200000001']);
        $part = Sparepart::create([
            'part_code' => 'KOL-1',
            'name' => 'Part Kolom',
            'category' => 'Uji',
            'stock' => 5,
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $order = ServiceOrder::create([
            'service_code' => 'SRV-202609-9001',
            'customer_id' => $customer->id,
            'device_name' => 'Laptop Kolom',
            'issue_description' => 'Uji panjang kolom',
            'status' => 'completed',
            'labor_cost' => 100000,
            'total_cost' => 102000,
        ]);

        $order->orderParts()->create([
            'sparepart_id' => $part->id,
            'quantity' => 1,
            'unit_price' => 2000,
            'subtotal' => 2000,
        ]);

        $query = ServiceOrder::query()->with(['customer', 'technician'])->withSum('orderParts', 'subtotal');
        [$header, $rows] = app(ReportExportService::class)->build($query);

        $this->assertSame(count(ReportColumnMap::definitions()), count($header));
        $this->assertCount(1, $rows);
        $this->assertSame(count($header), count($rows[0]), 'Setiap baris wajib punya jumlah sel sebanyak jumlah kolom.');
        $this->assertContains('SRV-202609-9001', $rows[0]);
        $this->assertContains('Laptop Kolom', $rows[0]);
        $this->assertContains('Belum Dibayar', $rows[0]);
        $this->assertContains('Selesai & Diambil', $rows[0]);
    }
}
