<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\LaporanMasyarakat;
use App\Models\LaporanPetugas;
use App\Models\MaintenanceTicket;
use App\Models\User;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EksekutifController extends Controller
{
    public function index()
    {
        // Statistik Utama
        $data['total_aset'] = Asset::count();
        $data['total_petugas'] = User::where('role', 'seksi')->count();
        
        // PENGADUAN MASUK (Semua pengaduan dari berbagai sumber)
        $data['laporan_masyarakat'] = LaporanMasyarakat::count();
        $data['laporan_petugas'] = LaporanPetugas::count();
        $data['pengaduan_masuk'] = Pengaduan::count();
        $data['total_pengaduan'] = $data['laporan_masyarakat'] + $data['laporan_petugas'] + $data['pengaduan_masuk'];

        // STATUS PERBAIKAN (Monitoring Progress)
        $data['perbaikan_proses'] = MaintenanceTicket::where('status', 'proses')->count();
        $data['perbaikan_selesai'] = MaintenanceTicket::where('status', 'selesai')->count();
        
        // SELESAI DITANGANI (Laporan yang sudah selesai dari semua sumber)
        $laporanMasyarakatSelesai = LaporanMasyarakat::where('status', 'selesai')->count();
        $laporanPetugasSelesai = LaporanPetugas::where('status', 'selesai')->count();
        $pengaduanSelesai = Pengaduan::where('status', 'selesai')->count();
        $data['total_selesai'] = $laporanMasyarakatSelesai + $laporanPetugasSelesai + $pengaduanSelesai;
        
        // Data untuk Grafik Status Aset
        $data['status_aset'] = Asset::select('status', DB::raw('count(*) as total'))
                                    ->groupBy('status')
                                    ->get();

        // Data Kategori & Jumlah Jenis Aset
        $data['kategori_stats'] = Asset::select('kategori', DB::raw('count(distinct(jenis)) as jumlah_jenis'))
                                    ->groupBy('kategori')
                                    ->get();

        return view('admin.eksekutif.dashboard', $data);
    }

    public function exportPDF()
    {
        $assets = Asset::all();
        
        $pdf = Pdf::loadView('admin.assets.pdf', compact('assets'));
        
        // Mengatur ukuran kertas A4 Portrait
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('laporan-aset-dishub-kbb.pdf');
    }

    public function exportExcel()
    {
        $assets = Asset::all();
        
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul sheet
        $sheet->setTitle('Laporan Aset Dishub KBB');
        
        // Header tabel
        $headers = ['NO', 'NAMA ASET', 'KATEGORI', 'JENIS', 'LOKASI', 'STATUS', 'TANGGAL DIBUAT'];
        
        // Style untuk header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        
        // Isi header
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        
        // Apply style header
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(20);
        
        // Isi data
        $row = 2;
        $no = 1;
        foreach ($assets as $asset) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $asset->nama ?? '-');
            $sheet->setCellValue('C' . $row, $asset->kategori ?? '-');
            $sheet->setCellValue('D' . $row, $asset->jenis ?? '-');
            $sheet->setCellValue('E' . $row, $asset->alamat ?? '-');
            
            $status = $asset->status ?? '-';
            $sheet->setCellValue('F' . $row, $status);
            
            $statusColor = [
                'Baik' => '16a34a',
                'Rusak' => 'f97316',
                'Kritis' => 'dc2626',
                'Proses Perbaikan' => '3b82f6'
            ];
            if (isset($statusColor[$status])) {
                $sheet->getStyle('F' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($statusColor[$status]);
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            }
            
            $sheet->setCellValue('G' . $row, $asset->created_at ? date('d/m/Y', strtotime($asset->created_at)) : '-');
            
            $row++;
            $no++;
        }
        
        $sheet->getStyle('A1:G' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->setAutoFilter('A1:G1');
        
        $writer = new Xlsx($spreadsheet);
        
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });
        
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="daftar-aset-dishub-kbb.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        
        return $response;
    }
}