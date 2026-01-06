<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            margin: 20px;
        }
        h1 {
            text-align: center;
            font-size: 16pt;
            margin-bottom: 5px;
            color: #1f2937;
        }
        h2 {
            text-align: center;
            font-size: 11pt;
            margin-top: 0;
            margin-bottom: 20px;
            color: #6b7280;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-section table {
            width: 100%;
        }
        .info-section td {
            padding: 3px 0;
        }
        .statistics {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .statistics td {
            padding: 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .stat-label {
            font-size: 8pt;
            color: #6b7280;
        }
        .stat-value {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 3px;
        }
        .stat-hadir { background-color: #d1fae5; color: #059669; }
        .stat-terlambat { background-color: #fef3c7; color: #d97706; }
        .stat-izin { background-color: #dbeafe; color: #2563eb; }
        .stat-sakit { background-color: #e9d5ff; color: #9333ea; }
        .stat-alpha { background-color: #fee2e2; color: #dc2626; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #2563eb;
            color: white;
            padding: 8px 5px;
            font-size: 8pt;
            text-align: left;
            border: 1px solid #1d4ed8;
        }
        table.data-table td {
            padding: 6px 5px;
            border: 1px solid #e5e7eb;
            font-size: 8pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            display: inline-block;
        }
        .status-hadir { background-color: #d1fae5; color: #059669; }
        .status-terlambat { background-color: #fef3c7; color: #d97706; }
        .status-izin { background-color: #dbeafe; color: #2563eb; }
        .status-sakit { background-color: #e9d5ff; color: #9333ea; }
        .status-alpha { background-color: #fee2e2; color: #dc2626; }
        .status-pulang_cepat { background-color: #fed7aa; color: #ea580c; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 8pt;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <h1>LAPORAN REKAP ABSENSI SISWA</h1>
    <h2>SMK Negeri 10 Pandeglang</h2>

    <!-- Info Section -->
    <div class="info-section">
        <table>
            <tr>
                <td style="width: 100px;"><strong>Periode</strong></td>
                <td>: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Semua' }} s/d {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Semua' }}</td>
                <td style="width: 150px; text-align: right;"><strong>Tanggal Cetak</strong></td>
                <td style="width: 120px;">: {{ now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <!-- Statistics -->
    <table class="statistics">
        <tr>
            <td class="stat-hadir">
                <div class="stat-label">Hadir</div>
                <div class="stat-value">{{ $totalPresent }}</div>
            </td>
            <td class="stat-terlambat">
                <div class="stat-label">Terlambat</div>
                <div class="stat-value">{{ $totalLate }}</div>
            </td>
            <td class="stat-izin">
                <div class="stat-label">Izin</div>
                <div class="stat-value">{{ $totalPermit }}</div>
            </td>
            <td class="stat-sakit">
                <div class="stat-label">Sakit</div>
                <div class="stat-value">{{ $totalSick }}</div>
            </td>
            <td class="stat-alpha">
                <div class="stat-label">Alpha</div>
                <div class="stat-value">{{ $totalAbsent }}</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 8%;">NIS</th>
                <th style="width: 15%;">Nama Siswa</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 12%;">Jurusan</th>
                <th style="width: 7%;">Check-In</th>
                <th style="width: 7%;">Check-Out</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 6%;">Late</th>
                <th style="width: 6%;">%</th>
                <th style="width: 8%;">Metode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ $attendance->check_in_time ? $attendance->check_in_time->format('d/m/Y') : '-' }}</td>
                    <td>{{ $attendance->student->nis ?? '-' }}</td>
                    <td>{{ $attendance->student->full_name ?? '-' }}</td>
                    <td>{{ $attendance->student->class->name ?? '-' }}</td>
                    <td>{{ $attendance->student->class->department->name ?? '-' }}</td>
                    <td style="text-align: center;">{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</td>
                    <td style="text-align: center;">{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</td>
                    <td style="text-align: center;">
                        <span class="status-badge status-{{ $attendance->status }}">
                            {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                        </span>
                    </td>
                    <td style="text-align: center;">{{ $attendance->late_minutes ?? 0 }}</td>
                    <td style="text-align: center;">{{ $attendance->percentage }}%</td>
                    <td style="text-align: center;">{{ strtoupper($attendance->method) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 20px; color: #6b7280;">
                        Tidak ada data absensi
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Total Data: {{ $attendances->count() }} records</strong></p>
        <p style="margin-top: 40px;">
            _________________________<br>
            Kepala Sekolah
        </p>
    </div>
</body>
</html>
