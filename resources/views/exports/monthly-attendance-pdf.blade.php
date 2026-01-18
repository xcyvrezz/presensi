<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Bulanan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #1e293b;
        }

        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: white;
        }

        .header h2 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
        }

        .header h3 {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            opacity: 0.95;
        }

        .info-box {
            background: #f1f5f9;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #3b82f6;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 4px 8px;
            font-size: 9pt;
        }

        .info-box td:first-child {
            font-weight: bold;
            width: 120px;
            color: #475569;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table th {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            font-weight: bold;
            font-size: 9pt;
            padding: 10px 6px;
            text-align: center;
            border: 1px solid #312e81;
        }

        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
            font-size: 8pt;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.data-table tbody tr:hover {
            background-color: #e0e7ff;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .no-col {
            width: 3%;
        }

        .nis-col {
            width: 9%;
        }

        .name-col {
            width: 22%;
        }

        .class-col {
            width: 10%;
        }

        .dept-col {
            width: 10%;
        }

        .stat-col {
            width: 7.6%;
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

        .bolos {
            color: #dc2626;
            font-weight: bold;
        }

        .alpha {
            color: #64748b;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #cbd5e1;
            font-size: 8pt;
            color: #64748b;
            text-align: center;
        }

        .legend {
            margin-top: 15px;
            padding: 10px;
            background: #fef3c7;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
        }

        .legend-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
            color: #92400e;
        }

        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 8pt;
        }

        .legend-item {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>📊 REKAP DATA ABSENSI BULANAN</h2>
        <h3>Bulan {{ $month }} Tahun {{ $year }}</h3>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td>Kelas</td>
                <td>: {{ $class->name }}</td>
                <td>Jurusan</td>
                <td>: {{ $class->department->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Periode</td>
                <td>: {{ $month }} {{ $year }}</td>
                <td>Tanggal Cetak</td>
                <td>: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
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
                <th class="stat-col">Bolos</th>
                <th class="stat-col">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendanceData as $index => $data)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $data['student']->nis }}</td>
                <td class="text-left">{{ $data['student']->full_name }}</td>
                <td class="text-center">{{ $class->name }}</td>
                <td class="text-center">{{ $class->department->name ?? '-' }}</td>
                <td class="text-center hadir">{{ $data['hadir'] }}</td>
                <td class="text-center terlambat">{{ $data['terlambat'] }}</td>
                <td class="text-center izin">{{ $data['izin'] }}</td>
                <td class="text-center sakit">{{ $data['sakit'] }}</td>
                <td class="text-center bolos">{{ $data['bolos'] }}</td>
                <td class="text-center alpha">{{ $data['alpha'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data siswa untuk periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="legend">
        <div class="legend-title">📌 Keterangan Warna:</div>
        <div class="legend-items">
            <span class="legend-item"><span class="hadir">●</span> Hadir</span>
            <span class="legend-item"><span class="terlambat">●</span> Terlambat</span>
            <span class="legend-item"><span class="izin">●</span> Izin</span>
            <span class="legend-item"><span class="sakit">●</span> Sakit</span>
            <span class="legend-item"><span class="bolos">●</span> Bolos</span>
            <span class="legend-item"><span class="alpha">●</span> Alpha</span>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Absensi</p>
        <p style="margin-top: 5px;">© {{ $year }} - Semua data bersifat rahasia dan hanya untuk keperluan administrasi sekolah</p>
    </div>
</body>
</html>
