<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Semester</title>
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
            background-color: #4338ca;
            padding: 25px 20px;
            border-radius: 0;
            margin-bottom: 25px;
            color: white;
            border: 3px solid #4f46e5;
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
            background: #eef2ff;
            padding: 15px;
            border-radius: 0;
            margin-bottom: 20px;
            border: 2px solid #6366f1;
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
            color: #3730a3;
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
            background-color: #4338ca;
            color: white;
            font-weight: bold;
            font-size: 9pt;
            padding: 12px 5px;
            text-align: center;
            border: 2px solid #4f46e5;
        }

        table.data-table td {
            border: 1px solid #94a3b8;
            padding: 8px 5px;
            font-size: 8.5pt;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #faf5ff;
        }

        table.data-table tbody tr:hover {
            background-color: #f3e8ff;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        /* Column widths */
        .no-col {
            width: 3%;
        }

        .nis-col {
            width: 8%;
        }

        .name-col {
            width: 18%;
        }

        .class-col {
            width: 9%;
        }

        .dept-col {
            width: 9%;
        }

        .stat-col {
            width: 5.5%;
            font-weight: bold;
        }

        .total-col {
            width: 6%;
            font-weight: bold;
        }

        .effective-col {
            width: 6%;
        }

        .percent-col {
            width: 7%;
            font-weight: bold;
        }

        /* Color coding for attendance numbers */
        .hadir {
            color: #15803d;
            font-weight: bold;
        }

        .terlambat {
            color: #ca8a04;
            font-weight: bold;
        }

        .izin {
            color: #1d4ed8;
            font-weight: bold;
        }

        .sakit {
            color: #7c3aed;
            font-weight: bold;
        }

        .dispensasi {
            color: #0891b2;
            font-weight: bold;
        }

        .bolos {
            color: #dc2626;
            font-weight: bold;
        }

        .alpha {
            color: #64748b;
            font-weight: bold;
        }

        .total-kehadiran {
            color: #047857;
            font-weight: bold;
        }

        /* Percentage color coding */
        .percent-excellent {
            color: #047857;
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
            background: #dbeafe;
            border-radius: 0;
            border: 2px solid #3b82f6;
        }

        .legend-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 8px;
            color: #1e3a8a;
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
        <h2>REKAP DATA ABSENSI SEMESTER</h2>
        <h3>{{ $semester->name }} ({{ $semester->academic_year }})</h3>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td>Kelas</td>
                <td>: {{ $class->name }}</td>
                <td>Hari Efektif</td>
                <td>: {{ $effectiveSchoolDays }} hari</td>
            </tr>
            <tr>
                <td>Jurusan</td>
                <td>: {{ $class->department->name ?? '-' }}</td>
                <td>Tanggal Cetak</td>
                <td>: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
            <tr>
                <td>Periode</td>
                <td colspan="3">: {{ $semester->start_date->format('d/m/Y') }} s/d {{ $semester->end_date->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="highlight-box">
        <p><strong>Catatan Penting:</strong> Rekap semester ini menghitung hari efektif sekolah dengan mengecualikan Sabtu, Minggu, dan hari libur dari kalender akademik. Persentase kehadiran dihitung berdasarkan: <strong>Persentase = (Total Kehadiran / Hari Efektif) × 100%</strong></p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="nis-col">NIS</th>
                <th class="name-col">Nama Siswa</th>
                <th class="class-col">Kelas</th>
                <th class="dept-col">Jurusan</th>
                <th class="stat-col">Hadir</th>
                <th class="stat-col">Terlambat</th>
                <th class="stat-col">Izin</th>
                <th class="stat-col">Sakit</th>
                <th class="stat-col">Dispensasi</th>
                <th class="stat-col">Bolos</th>
                <th class="stat-col">Alpha</th>
                <th class="total-col">Total Hadir</th>
                <th class="effective-col">Hari Efektif</th>
                <th class="percent-col">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendanceData as $index => $data)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $data['student']->nis }}</td>
                <td class="text-left">{{ $data['student']->full_name }}</td>
                <td class="text-center">{{ $class->name }}</td>
                <td class="text-center">{{ $class->department->code ?? '-' }}</td>
                <td class="text-center hadir">{{ $data['hadir'] }}</td>
                <td class="text-center terlambat">{{ $data['terlambat'] }}</td>
                <td class="text-center izin">{{ $data['izin'] }}</td>
                <td class="text-center sakit">{{ $data['sakit'] }}</td>
                <td class="text-center dispensasi">{{ $data['dispensasi'] }}</td>
                <td class="text-center bolos">{{ $data['bolos'] }}</td>
                <td class="text-center alpha">{{ $data['alpha'] }}</td>
                <td class="text-center total-kehadiran">{{ $data['total_kehadiran'] }}</td>
                <td class="text-center">{{ $effectiveSchoolDays }}</td>
                <td class="text-center
                    @if($data['percentage'] >= 90) percent-excellent
                    @elseif($data['percentage'] >= 75) percent-good
                    @else percent-poor
                    @endif">
                    {{ number_format($data['percentage'], 2) }}%
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="15" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data siswa untuk periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="legend">
        <div class="legend-title">Keterangan Warna:</div>
        <div class="legend-items">
            <span class="legend-item"><span class="hadir">●</span> Hadir</span>
            <span class="legend-item"><span class="terlambat">●</span> Terlambat</span>
            <span class="legend-item"><span class="izin">●</span> Izin</span>
            <span class="legend-item"><span class="sakit">●</span> Sakit</span>
            <span class="legend-item"><span class="dispensasi">●</span> Dispensasi</span>
            <span class="legend-item"><span class="bolos">●</span> Bolos</span>
            <span class="legend-item"><span class="alpha">●</span> Alpha</span>
            <span class="legend-item">|</span>
            <span class="legend-item"><span class="percent-excellent">●</span> ≥90% (Sangat Baik)</span>
            <span class="legend-item"><span class="percent-good">●</span> 75-89% (Baik)</span>
            <span class="legend-item"><span class="percent-poor">●</span> <75% (Kurang)</span>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Absensi</p>
        <p style="margin-top: 5px;">© {{ $semester->academic_year }} - Semua data bersifat rahasia dan hanya untuk keperluan administrasi sekolah</p>
    </div>
</body>
</html>
