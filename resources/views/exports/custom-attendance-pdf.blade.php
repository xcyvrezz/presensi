<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Periode Kustom</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20mm 25mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #1e293b;
        }

        .header {
            background-color: #d97706;
            padding: 25px 20px;
            border-radius: 0;
            margin-bottom: 25px;
            color: white;
            border: 3px solid #f59e0b;
        }

        .header h2 {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: center;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header h3 {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
        }

        .info-box {
            background: #fef3c7;
            padding: 15px;
            border-radius: 0;
            margin-bottom: 20px;
            border: 2px solid #f59e0b;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 5px 10px;
            font-size: 10pt;
        }

        .info-box td:first-child {
            font-weight: bold;
            width: 140px;
            color: #92400e;
        }

        .highlight-box {
            background: #fef3c7;
            padding: 12px 15px;
            border-radius: 0;
            margin-bottom: 20px;
            border: 2px solid #f59e0b;
        }

        .highlight-box p {
            font-size: 9pt;
            color: #78350f;
            line-height: 1.6;
        }

        .highlight-box strong {
            color: #78350f;
            font-weight: bold;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table.data-table th {
            background-color: #d97706;
            color: white;
            font-weight: bold;
            font-size: 9pt;
            padding: 12px 5px;
            text-align: center;
            border: 2px solid #f59e0b;
        }

        table.data-table td {
            border: 1px solid #94a3b8;
            padding: 8px 5px;
            font-size: 8.5pt;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #fffbeb;
        }

        table.data-table tbody tr:hover {
            background-color: #fef3c7;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .badge-hadir {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-terlambat {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-izin {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-sakit {
            background-color: #e0e7ff;
            color: #4338ca;
        }

        .badge-dispensasi {
            background-color: #f3e8ff;
            color: #6b21a8;
        }

        .badge-bolos {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-alpha {
            background-color: #fecaca;
            color: #7f1d1d;
        }

        .col-no {
            width: 3%;
        }

        .col-nis {
            width: 8%;
        }

        .col-name {
            width: 20%;
        }

        .col-class {
            width: 10%;
        }

        .col-dept {
            width: 12%;
        }

        .col-status {
            width: 5%;
        }

        .col-total {
            width: 7%;
        }

        .col-effective {
            width: 7%;
        }

        .col-percentage {
            width: 8%;
        }

        .percent-excellent {
            color: #15803d;
            font-weight: bold;
        }

        .percent-good {
            color: #ca8a04;
            font-weight: bold;
        }

        .percent-poor {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 3px solid #94a3b8;
            font-size: 8.5pt;
            color: #475569;
            text-align: center;
        }

        .legend {
            margin-top: 20px;
            padding: 12px 15px;
            background: #fef3c7;
            border-radius: 0;
            border: 2px solid #f59e0b;
        }

        .legend-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 8px;
            color: #92400e;
        }

        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 8.5pt;
        }

        .legend-item {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP DATA ABSENSI PERIODE KUSTOM</h2>
        <h3>Periode: {{ $startDate }} s/d {{ $endDate }}</h3>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td>Kelas</td>
                <td>: {{ $class->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jurusan</td>
                <td>: {{ $class->department->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Periode</td>
                <td>: {{ $startDate }} s/d {{ $endDate }}</td>
            </tr>
            <tr>
                <td>Hari Efektif</td>
                <td>: {{ $effectiveSchoolDays }} hari (exclude Sabtu, Minggu, dan hari libur)</td>
            </tr>
            <tr>
                <td>Tanggal Export</td>
                <td>: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <div class="highlight-box">
        <p>
            <strong>Catatan Penting:</strong><br>
            Hari efektif adalah hari sekolah aktif (Senin-Jumat) dikurangi hari libur dari kalender akademik.
            Persentase kehadiran dihitung berdasarkan: <strong>(Total Hadir / Hari Efektif) × 100%</strong>
        </p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nis">NIS</th>
                <th class="col-name">Nama Siswa</th>
                <th class="col-class">Kelas</th>
                <th class="col-dept">Jurusan</th>
                <th class="col-status">Hadir</th>
                <th class="col-status">Terlambat</th>
                <th class="col-status">Izin</th>
                <th class="col-status">Sakit</th>
                <th class="col-status">Dispensasi</th>
                <th class="col-status">Bolos</th>
                <th class="col-status">Alpha</th>
                <th class="col-total">Total Hadir</th>
                <th class="col-effective">Hari Efektif</th>
                <th class="col-percentage">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendanceData as $index => $data)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $data['student']->nis }}</td>
                    <td class="text-left">{{ $data['student']->full_name }}</td>
                    <td class="text-center">{{ $class->name ?? '-' }}</td>
                    <td class="text-left">{{ $class->department->name ?? '-' }}</td>
                    <td class="text-center">{{ $data['hadir'] }}</td>
                    <td class="text-center">{{ $data['terlambat'] }}</td>
                    <td class="text-center">{{ $data['izin'] }}</td>
                    <td class="text-center">{{ $data['sakit'] }}</td>
                    <td class="text-center">{{ $data['dispensasi'] }}</td>
                    <td class="text-center">{{ $data['bolos'] }}</td>
                    <td class="text-center">{{ $data['alpha'] }}</td>
                    <td class="text-center font-bold">{{ $data['total_kehadiran'] }}</td>
                    <td class="text-center">{{ $effectiveSchoolDays }}</td>
                    <td class="text-center {{ $data['percentage'] >= 90 ? 'percent-excellent' : ($data['percentage'] >= 75 ? 'percent-good' : 'percent-poor') }}">
                        {{ number_format($data['percentage'], 2) }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding: 20px;">
                        Tidak ada data siswa untuk kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="legend">
        <div class="legend-title">Keterangan Status Kehadiran</div>
        <div class="legend-items">
            <div class="legend-item"><strong>Hadir:</strong> Siswa hadir tepat waktu</div>
            <div class="legend-item"><strong>Terlambat:</strong> Siswa hadir terlambat</div>
            <div class="legend-item"><strong>Izin:</strong> Siswa izin dengan keterangan</div>
            <div class="legend-item"><strong>Sakit:</strong> Siswa sakit dengan surat keterangan</div>
            <div class="legend-item"><strong>Dispensasi:</strong> Siswa dispensasi (tugas sekolah)</div>
            <div class="legend-item"><strong>Bolos:</strong> Siswa tidak hadir tanpa keterangan</div>
            <div class="legend-item"><strong>Alpha:</strong> Siswa tidak hadir (dicatat otomatis)</div>
        </div>
    </div>

    <div class="legend" style="margin-top: 10px;">
        <div class="legend-title">Kategori Persentase Kehadiran</div>
        <div class="legend-items">
            <div class="legend-item"><span class="percent-excellent">≥ 90%</span> = Sangat Baik</div>
            <div class="legend-item"><span class="percent-good">75% - 89%</span> = Baik</div>
            <div class="legend-item"><span class="percent-poor">< 75%</span> = Perlu Perhatian</div>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh Sistem Absensi MIFARE</p>
        <p style="margin-top: 5px; font-size: 7.5pt; color: #64748b;">
            © {{ date('Y') }} Sistem Absensi MIFARE - Semua data bersifat rahasia dan hanya untuk keperluan internal sekolah
        </p>
    </div>
</body>
</html>
