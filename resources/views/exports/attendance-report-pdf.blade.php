<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran Siswa</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 5px 0;
            color: #4F46E5;
        }
        .header h2 {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #666;
            font-weight: normal;
        }
        .header .school-name {
            font-size: 14px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 3px;
        }
        .info-box {
            background: #F3F4F6;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #4F46E5;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #374151;
        }
        .info-value {
            color: #1F2937;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1F2937;
            margin: 25px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #E5E7EB;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #F9FAFB;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #E5E7EB;
        }
        .stat-label {
            font-size: 10px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937;
        }
        .stat-desc {
            font-size: 9px;
            color: #9CA3AF;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #4F46E5;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background: #F9FAFB;
        }
        table tr:hover {
            background: #F3F4F6;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background: #D1FAE5;
            color: #065F46;
        }
        .badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }
        .badge-danger {
            background: #FEE2E2;
            color: #991B1B;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #E5E7EB;
            text-align: right;
            font-size: 10px;
            color: #6B7280;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="school-name">SMK NEGERI 1 EXAMPLE</div>
        <h1>LAPORAN KEHADIRAN SISWA</h1>
        <h2>Periode: {{ $startDate->isoFormat('D MMMM Y') }} - {{ $endDate->isoFormat('D MMMM Y') }}</h2>
    </div>

    <!-- Report Information -->
    <div class="info-box">
        <div class="info-row">
            <div class="info-label">Jenis Laporan:</div>
            <div class="info-value">
                @if($reportType === 'monthly')
                    Bulanan
                @elseif($reportType === 'semester')
                    Semesteran
                @else
                    Custom
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Generate:</div>
            <div class="info-value">{{ $generatedAt->isoFormat('dddd, D MMMM Y - HH:mm:ss') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Hari Kerja:</div>
            <div class="info-value">{{ $data['statistics']['working_days'] }} hari</div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="section-title">RINGKASAN STATISTIK</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Siswa</div>
            <div class="stat-value">{{ $data['statistics']['total_students'] }}</div>
            <div class="stat-desc">Siswa aktif</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Kehadiran</div>
            <div class="stat-value">{{ number_format($data['statistics']['attendance_percentage'], 1) }}%</div>
            <div class="stat-desc">{{ $data['statistics']['total_present'] }} dari {{ $data['statistics']['expected_attendances'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Hadir</div>
            <div class="stat-value">{{ $data['statistics']['total_hadir'] }}</div>
            <div class="stat-desc">Tepat waktu</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Terlambat</div>
            <div class="stat-value">{{ $data['statistics']['total_terlambat'] }}</div>
            <div class="stat-desc">Datang terlambat</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Izin/Sakit</div>
            <div class="stat-value">{{ $data['statistics']['total_izin'] + $data['statistics']['total_sakit'] }}</div>
            <div class="stat-desc">{{ $data['statistics']['total_izin'] }} izin, {{ $data['statistics']['total_sakit'] }} sakit</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Alpha</div>
            <div class="stat-value">{{ $data['statistics']['total_alpha'] }}</div>
            <div class="stat-desc">Tanpa keterangan</div>
        </div>
    </div>

    <!-- Department Statistics -->
    <div class="section-title">STATISTIK PER JURUSAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Jurusan</th>
                <th style="width: 10%;" class="text-center">Siswa</th>
                <th style="width: 10%;" class="text-center">Hadir</th>
                <th style="width: 10%;" class="text-center">Terlambat</th>
                <th style="width: 10%;" class="text-center">Izin</th>
                <th style="width: 10%;" class="text-center">Sakit</th>
                <th style="width: 10%;" class="text-center">Alpha</th>
                <th style="width: 10%;" class="text-center">Persentase</th>
                <th style="width: 10%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['department_stats'] as $index => $dept)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $dept['name'] }}</strong> ({{ $dept['code'] }})</td>
                    <td class="text-center">{{ $dept['total_students'] }}</td>
                    <td class="text-center">{{ $dept['hadir'] }}</td>
                    <td class="text-center">{{ $dept['terlambat'] }}</td>
                    <td class="text-center">{{ $dept['izin'] }}</td>
                    <td class="text-center">{{ $dept['sakit'] }}</td>
                    <td class="text-center">{{ $dept['alpha'] }}</td>
                    <td class="text-center"><strong>{{ $dept['percentage'] }}%</strong></td>
                    <td class="text-center">
                        @if($dept['percentage'] >= 90)
                            <span class="badge badge-success">Sangat Baik</span>
                        @elseif($dept['percentage'] >= 75)
                            <span class="badge badge-warning">Baik</span>
                        @else
                            <span class="badge badge-danger">Perlu Perhatian</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Class Statistics -->
    <div class="page-break"></div>
    <div class="section-title">STATISTIK PER KELAS</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Kelas</th>
                <th style="width: 10%;" class="text-center">Siswa</th>
                <th style="width: 10%;" class="text-center">Hadir</th>
                <th style="width: 10%;" class="text-center">Terlambat</th>
                <th style="width: 10%;" class="text-center">Izin</th>
                <th style="width: 10%;" class="text-center">Sakit</th>
                <th style="width: 10%;" class="text-center">Alpha</th>
                <th style="width: 15%;" class="text-center">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['class_stats'] as $index => $class)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $class['name'] }}</strong> - {{ $class['department'] }}</td>
                    <td class="text-center">{{ $class['total_students'] }}</td>
                    <td class="text-center">{{ $class['hadir'] }}</td>
                    <td class="text-center">{{ $class['terlambat'] }}</td>
                    <td class="text-center">{{ $class['izin'] }}</td>
                    <td class="text-center">{{ $class['sakit'] }}</td>
                    <td class="text-center">{{ $class['alpha'] }}</td>
                    <td class="text-center"><strong>{{ $class['percentage'] }}%</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div>Dokumen ini digenerate otomatis oleh Sistem Absensi MIFARE</div>
        <div>{{ $generatedAt->isoFormat('dddd, D MMMM Y - HH:mm:ss') }}</div>
    </div>
</body>
</html>
