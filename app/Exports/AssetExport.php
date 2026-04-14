<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * Mengambil data dari database
    */
    public function collection()
    {
        // Mengambil kolom spesifik agar rapi di Excel
        return Asset::select('id_asset', 'nama', 'kategori', 'jenis', 'status', 'alamat')->get();
    }

    /**
    * Membuat judul kolom (Header) di baris pertama Excel
    */
    public function headings(): array
    {
        return [
            'ID ASET',
            'NAMA ASET',
            'KATEGORI',
            'JENIS',
            'STATUS',
            'ALAMAT LOKASI'
        ];
    }
}