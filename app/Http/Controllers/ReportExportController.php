<?php

namespace App\Http\Controllers;

use App\Actions\ReportQuery;
use App\Exports\ExportArchiver;
use App\Exports\ReportExporterRegistry;
use App\Exports\ReportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

/**
 * Menangani pembuatan dan pengunduhan berkas ekspor laporan.
 *
 * Controller ini tidak mengetahui format apa pun secara langsung: format
 * diambil dari ReportExporterRegistry dalam bentuk antarmuka ExportableReport,
 * sehingga menambah format baru (misalnya Excel) tidak mengubah berkas ini.
 */
class ReportExportController extends Controller
{
    public function store(
        Request $request,
        ReportQuery $report,
        ReportExportService $service,
        ReportExporterRegistry $registry,
        ExportArchiver $archiver,
    ): RedirectResponse {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'format' => ['required', 'string', 'in:'.implode(',', $registry->formats())],
        ]);

        [$start, $end] = $report->range();
        $exporter = $registry->make($validated['format']);

        try {
            [$header, $rows] = $service->build($report->listed());
            $archived = $archiver->store(
                $exporter,
                "laporan-servis-{$start->toDateString()}-{$end->toDateString()}",
                $header,
                $rows,
            );
        } catch (Throwable $exception) {
            return back()->with('error', 'Ekspor gagal: '.$exception->getMessage());
        }

        return redirect()
            ->route('reports.index', $request->only('start', 'end', 'status'))
            ->with('success', "Laporan diekspor ke {$archived['name']} ({$archived['rows']} baris, ".number_format($archived['size'] / 1024, 1, ',', '.').' KB) dan tersimpan di server.');
    }

    /**
     * Kirim ulang berkas arsip kepada peramban, sekaligus bukti bahwa berkas
     * dapat dibaca kembali dari media penyimpanan.
     */
    public function download(Request $request, string $file, ExportArchiver $archiver, ReportExporterRegistry $registry): Response
    {
        abort_unless($request->user()->isAdmin(), 403);

        $name = basename($file);

        try {
            $contents = $archiver->read($name);
            $exporter = $registry->forFile($name);
        } catch (\InvalidArgumentException $exception) {
            abort(404, $exception->getMessage());
        }

        return response($contents, 200, [
            'Content-Type' => $exporter->mimeType(),
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
            'Content-Length' => (string) strlen($contents),
        ]);
    }
}
