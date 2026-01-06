<?php

namespace App\Livewire\Admin\Classes;

use App\Models\Classes;
use App\Models\Department;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Tambah Kelas')]
class ClassCreate extends Component
{
    public $department_id = '';
    public $wali_kelas_id = '';
    public $name = '';
    public $grade = '';
    public $academic_year = '';
    public $capacity = 36;
    public $description = '';
    public $is_active = true;

    protected function rules()
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:50|unique:classes,name',
            'grade' => 'required|integer|min:10|max:12',
            'academic_year' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'department_id.required' => 'Jurusan harus dipilih.',
        'name.required' => 'Nama kelas harus diisi.',
        'name.unique' => 'Nama kelas sudah terdaftar.',
        'grade.required' => 'Tingkat kelas harus dipilih.',
        'grade.min' => 'Tingkat kelas minimal 10.',
        'grade.max' => 'Tingkat kelas maksimal 12.',
        'academic_year.required' => 'Tahun ajaran harus diisi.',
        'capacity.required' => 'Kapasitas harus diisi.',
        'capacity.min' => 'Kapasitas minimal 1 siswa.',
        'capacity.max' => 'Kapasitas maksimal 50 siswa.',
    ];

    public function save()
    {
        $validated = $this->validate();

        try {
            // Handle nullable fields - convert empty strings to null
            $data = [
                'department_id' => $validated['department_id'],
                'wali_kelas_id' => $this->wali_kelas_id ?: null,
                'name' => $validated['name'],
                'grade' => $validated['grade'],
                'academic_year' => $validated['academic_year'],
                'capacity' => $validated['capacity'],
                'current_students' => 0,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
            ];

            Classes::create($data);

            session()->flash('success', 'Kelas berhasil ditambahkan.');

            return redirect()->route('admin.classes.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan kelas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $departments = Department::orderBy('name')->get();

        // Get users with wali_kelas role
        $waliKelasRole = \App\Models\Role::where('name', 'wali_kelas')->first();
        $teachers = collect();

        if ($waliKelasRole) {
            $teachers = User::where('role_id', $waliKelasRole->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.admin.classes.class-create', [
            'departments' => $departments,
            'teachers' => $teachers,
        ]);
    }
}
