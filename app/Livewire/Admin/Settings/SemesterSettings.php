<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Semester;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.admin')]
#[Title('Pengaturan Semester')]
class SemesterSettings extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $semesterId;

    public $name;
    public $academic_year;
    public $start_date;
    public $end_date;
    public $semester; // 1 or 2
    public $is_active = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'academic_year' => 'required|string|max:20',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'semester' => 'required|in:1,2',
    ];

    protected $messages = [
        'name.required' => 'Nama semester harus diisi',
        'academic_year.required' => 'Tahun ajaran harus diisi',
        'start_date.required' => 'Tanggal mulai harus diisi',
        'end_date.required' => 'Tanggal selesai harus diisi',
        'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai',
        'semester.required' => 'Semester harus dipilih',
        'semester.in' => 'Semester harus 1 atau 2',
    ];

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;

        // Set default values for new semester
        $currentYear = Carbon::now()->year;
        $this->academic_year = $currentYear . '/' . ($currentYear + 1);
        $this->semester = 1;
        $this->start_date = Carbon::create($currentYear, 7, 1)->format('Y-m-d');
        $this->end_date = Carbon::create($currentYear, 12, 31)->format('Y-m-d');
        $this->name = 'Semester 1 ' . $this->academic_year;
    }

    public function openEditModal($id)
    {
        $semester = Semester::findOrFail($id);

        $this->semesterId = $semester->id;
        $this->name = $semester->name;
        $this->academic_year = $semester->academic_year;
        $this->start_date = $semester->start_date->format('Y-m-d');
        $this->end_date = $semester->end_date->format('Y-m-d');
        $this->semester = $semester->semester;
        $this->is_active = $semester->is_active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'semesterId',
            'name',
            'academic_year',
            'start_date',
            'end_date',
            'semester',
            'is_active',
        ]);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode) {
            $semester = Semester::findOrFail($this->semesterId);
            $semester->update([
                'name' => $this->name,
                'academic_year' => $this->academic_year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'semester' => $this->semester,
            ]);

            session()->flash('success', 'Semester berhasil diupdate');
        } else {
            Semester::create([
                'name' => $this->name,
                'academic_year' => $this->academic_year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'semester' => $this->semester,
                'is_active' => false, // Default non-aktif
            ]);

            session()->flash('success', 'Semester berhasil ditambahkan');
        }

        $this->closeModal();
    }

    public function setActive($id)
    {
        // Deactivate all semesters first
        Semester::query()->update(['is_active' => false]);

        // Activate selected semester
        $semester = Semester::findOrFail($id);
        $semester->update(['is_active' => true]);

        session()->flash('success', 'Semester ' . $semester->name . ' telah diaktifkan');
    }

    public function delete($id)
    {
        $semester = Semester::findOrFail($id);

        if ($semester->is_active) {
            session()->flash('error', 'Tidak dapat menghapus semester yang sedang aktif');
            return;
        }

        $semester->delete();
        session()->flash('success', 'Semester berhasil dihapus');
    }

    public function render()
    {
        $semesters = Semester::orderBy('start_date', 'desc')
            ->orderBy('semester', 'desc')
            ->paginate(10);

        return view('livewire.admin.settings.semester-settings', [
            'semesters' => $semesters,
        ]);
    }
}
