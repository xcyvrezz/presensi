<?php

namespace App\Livewire\Student;

use App\Models\Student;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.student')]
#[Title('Profil Saya')]
class Profile extends Component
{
    public $student;

    public function mount()
    {
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.department'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }
    }

    public function render()
    {
        return view('livewire.student.profile');
    }
}
