<!DOCTYPE html>
<html>
<head>
    <title>Laporan Aset Dishub KBB</title>
    <style>
        /* Import Font Inter untuk DOMPDF */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            src: url(https://fonts.gstatic.com/s/inter/v12/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2) format('woff2');
        }
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 700;
            src: url(https://fonts.gstatic.com/s/inter/v12/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa1ZL7W0Q5m-W9.woff2) format('woff2');
        }

        /* Mengatur ukuran kertas dan margin agar tidak bocor ke hal 2 */
        @page {
            margin: 0.5cm 1cm;
        }
        
        body {
            /* Font diganti ke Inter */
            font-family: 'Inter', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* Penting: Biarkan tabel mengalir alami */
            page-break-inside: auto;
        }

        th {
            background-color: #f2f2f2;
            border: 1px solid #666;
            padding: 5px;
            font-size: 9px;
        }

        td {
            border: 1px solid #666;
            padding: 5px;
            vertical-align: top;
        }

        /* Mencegah satu baris tabel terpotong jadi dua halaman */
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        /* INI KUNCINYA: Mencegah Footer/Tanda Tangan terpisah sendiri */
        .footer-container {
            width: 100%;
            margin-top: 15px;
            /* Jika tidak cukup di hal 1, pindahkan blok ini semua ke hal 2 */
            page-break-inside: avoid; 
        }

        .badge {
            padding: 2px 4px;
            border-radius: 2px;
            color: white;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h2 style="margin:0;">PEMERINTAH KABUPATEN BANDUNG BARAT</h2>
        <h3 style="margin:0;">DINAS PERHUBUNGAN</h3>
        <p style="margin:2px 0; font-size: 8px;">Jl. Raya Padalarang No.1, Kec. Padalarang, Kabupaten Bandung Barat</p>
    </div>

    <div style="text-align:center; margin-bottom: 10px;">
        <h4 style="margin:0; text-decoration: underline;">LAPORAN DATA INVENTARIS ASET FASILITAS JALAN</h4>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="20%">NAMA ASET</th>
                <th width="25%">KATEGORI & JENIS</th>
                <th width="30%">LOKASI</th>
                <th width="10%">STATUS</th>
                <th width="10%">TGL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $key => $asset)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td><strong>{{ $asset->nama }}</strong></td>
                <td>{{ $asset->kategori }}<br><small style="color:#555">{{ $asset->jenis }}</small></td>
                <td>{{ $asset->alamat ?? '-' }}</td>
                <td class="text-center">
                    @php
                        $color = '#3b82f6'; // Default Proses
                        if($asset->status == 'Baik') $color = '#16a34a';
                        elseif($asset->status == 'Rusak') $color = '#fbbf24';
                        elseif($asset->status == 'Kritis') $color = '#dc2626';
                    @endphp
                    <span class="badge" style="background-color: {{ $color }};">
                        {{ $asset->status }}
                    </span>
                </td>
                <td class="text-center">{{ \Carbon\Carbon::parse($asset->updated_at)->format('d/m/y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-container">
        <div class="text-right" style="margin-right: 20px;">
            <p>Bandung Barat, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <br><br><br>
            <p><strong>( ................................................. )</strong><br>
            Kepala Dinas Perhubungan</p>
        </div>
    </div>

</body>
</html>