<?php

namespace App\Livewire\Admin\Classes;

use App\Models\Classes;
use App\Models\Department;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Edit Kelas')]
class ClassEdit extends Component
{
    public Classes $class;

    public $department_id = '';
    public $wali_kelas_id = '';
    public $name = '';
    public $grade = '';
    public $academic_year = '';
    public $capacity = 36;
    public $description = '';
    public $is_active = true;

    public function mount(Classes $classes)
    {
        $this->class = $classes;

        $this->department_id = $this->class->department_id;
        $this->wali_kelas_id = $this->class->wali_kelas_id;
        $this->name = $this->class->name;
        $this->grade = $this->class->grade;
        $this->academic_year = $this->class->academic_year;
        $this->capacity = $this->class->capacity;
        $this->description = $this->class->description;
        $this->is_active = $this->class->is_active;
    }

    protected function rules()
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:50|unique:classes,name,' . $this->class->id,
            'grade' => 'required|integer|min:10|max:12',
            'academic_year' => 'required|string|max:20',
            'capacity' => 'required|integer|min:' . $this->class->current_students . '|max:50',
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
        'capacity.min' => 'Kapasitas tidak boleh kurang dari jumlah siswa saat ini.',
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
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
            ];

            $this->class->update($data);

            session()->flash('success', 'Kelas berhasil diperbarui.');

            return redirect()->route('admin.classes.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui kelas: ' . $e->getMessage());
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

        return view('livewire.admin.classes.class-edit', [
            'departments' => $departments,
            'teachers' => $teachers,
        ]);
    }
}
