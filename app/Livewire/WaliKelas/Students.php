<?php

namespace App\Livewire\WaliKelas;

use App\Models\Classes;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.wali-kelas')]
#[Title('Siswa Kelas Saya')]
class Students extends Component
{
    use WithPagination;

    public $class;
    public $search = '';
    public $genderFilter = '';
    public $nfcFilter = '';

    public function mount()
    {
        // Get class yang diampu oleh wali kelas ini
        $this->class = Classes::where('wali_kelas_id', auth()->id())
            ->with(['department'])
            ->first();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingGenderFilter()
    {
        $this->resetPage();
    }

    public function updatingNfcFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $students = collect();

        if ($this->class) {
            $students = $this->class->students()
                ->with(['user'])
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('full_name', 'like', '%' . $this->search . '%')
                          ->orWhere('nis', 'like', '%' . $this->search . '%')
                          ->orWhere('nisn', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->genderFilter !== '', function ($query) {
                    $query->where('gender', $this->genderFilter);
                })
                ->when($this->nfcFilter !== '', function ($query) {
                    if ($this->nfcFilter == '1') {
                        $query->where('nfc_enabled', true);
                    } else {
                        $query->where('nfc_enabled', false);
                    }
                })
                ->orderBy('full_name')
                ->paginate(15);
        }

        return view('livewire.wali-kelas.students', [
            'students' => $students,
        ]);
    }
}
