<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StudentsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Return example data
        return [
            [
                '123456',                           // nis
                '0012345678',                       // nisn
                'John Doe',                         // full_name
                'John',                             // nickname
                'L',                                // gender (L/P)
                'Jakarta',                          // birth_place
                '2005-01-15',                       // birth_date (YYYY-MM-DD)
                'Jl. Contoh No. 123, Jakarta',     // address
                '081234567890',                     // phone
                'Bapak John Doe Sr.',               // parent_name
                '081234567891',                     // parent_phone
                'X RPL 1',                          // class_name
                'john.doe@student.smkn10pdg.sch.id', // email
                'password123',                      // password
                '04:AB:CD:EF:12:34:80',            // card_uid (optional)
            ],
            [
                '123457',
                '0012345679',
                'Jane Smith',
                'Jane',
                'P',
                'Padang',
                '2006-03-20',
                'Jl. Sudirman No. 45, Padang',
                '082234567890',
                'Ibu Jane Smith Sr.',
                '082234567891',
                'X TKJ 2',
                'jane.smith@student.smkn10pdg.sch.id',
                'password123',
                '',
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'nis',
            'nisn',
            'full_name',
            'nickname',
            'gender',
            'birth_place',
            'birth_date',
            'address',
            'phone',
            'parent_name',
            'parent_phone',
            'class_name',
            'email',
            'password',
            'card_uid',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row (row 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563eb'], // Blue-600
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
            ],
            // Style example rows
            2 => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0F2FE'], // Light blue
                ],
            ],
            3 => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F9FF'], // Very light blue
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // nis
            'B' => 15,  // nisn
            'C' => 25,  // full_name
            'D' => 15,  // nickname
            'E' => 10,  // gender
            'F' => 15,  // birth_place
            'G' => 15,  // birth_date
            'H' => 30,  // address
            'I' => 15,  // phone
            'J' => 25,  // parent_name
            'K' => 15,  // parent_phone
            'L' => 15,  // class_name
            'M' => 35,  // email
            'N' => 15,  // password
            'O' => 20,  // card_uid
        ];
    }
}
