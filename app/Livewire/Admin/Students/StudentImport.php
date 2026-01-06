<?php

namespace App\Livewire\Admin\Students;

use App\Imports\StudentsImport;
use App\Exports\StudentsTemplateExport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.admin')]
#[Title('Import Siswa')]
class StudentImport extends Component
{
    use WithFileUploads;

    public $file;
    public $importResults = [];
    public $hasImported = false;

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
    ];

    protected $messages = [
        'file.required' => 'File Excel harus dipilih.',
        'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
        'file.max' => 'Ukuran file maksimal 10MB.',
    ];

    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new StudentsTemplateExport,
                'template-import-siswa-' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            Log::error('Error downloading template: ' . $e->getMessage());
            session()->flash('error', 'Gagal download template: ' . $e->getMessage());
        }
    }

    public function import()
    {
        $this->validate();

        $this->importResults = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            $import = new StudentsImport();

            Excel::import($import, $this->file->getRealPath());

            $this->importResults['success'] = $import->getImportedCount();
            $this->importResults['failed'] = $import->getFailedCount();
            $this->importResults['errors'] = $import->getErrors();

            DB::commit();

            $this->hasImported = true;

            if ($this->importResults['success'] > 0) {
                session()->flash('success',
                    "Import selesai! Berhasil: {$this->importResults['success']}, Gagal: {$this->importResults['failed']}"
                );
            } else {
                session()->flash('error', 'Import gagal! Tidak ada data yang berhasil diimport.');
            }

            // Log import activity
            Log::info('Student import completed', [
                'success' => $this->importResults['success'],
                'failed' => $this->importResults['failed'],
                'user' => auth()->user()->name,
                'user_id' => auth()->id(),
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();

            $failures = $e->failures();
            foreach ($failures as $failure) {
                $this->importResults['errors'][] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            $this->importResults['failed'] = count($failures);
            $this->hasImported = true;

            session()->flash('error', 'Terdapat ' . count($failures) . ' baris dengan error validasi.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Student import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function resetImport()
    {
        $this->reset(['file', 'importResults', 'hasImported']);
        session()->forget(['success', 'error']);
    }

    public function render()
    {
        return view('livewire.admin.students.student-import');
    }
}
