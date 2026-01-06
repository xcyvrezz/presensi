<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Edit Jurusan')]
class DepartmentEdit extends Component
{
    public Department $department;

    public $code = '';
    public $name = '';
    public $description = '';
    public $head_teacher = '';
    public $phone = '';
    public $is_active = true;

    public function mount(Department $department)
    {
        $this->department = $department;
        $this->code = $department->code;
        $this->name = $department->name;
        $this->description = $department->description;
        $this->head_teacher = $department->head_teacher;
        $this->phone = $department->phone;
        $this->is_active = $department->is_active;
    }

    protected function rules()
    {
        return [
            'code' => 'required|string|max:10|unique:departments,code,' . $this->department->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'head_teacher' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'code.required' => 'Kode jurusan harus diisi.',
        'code.unique' => 'Kode jurusan sudah terdaftar.',
        'code.max' => 'Kode jurusan maksimal 10 karakter.',
        'name.required' => 'Nama jurusan harus diisi.',
        'name.max' => 'Nama jurusan maksimal 100 karakter.',
        'phone.max' => 'Nomor telepon maksimal 20 karakter.',
    ];

    public function save()
    {
        $validated = $this->validate();

        try {
            $this->department->update([
                'code' => strtoupper($validated['code']),
                'name' => $validated['name'],
                'description' => $validated['description'],
                'head_teacher' => $validated['head_teacher'],
                'phone' => $validated['phone'],
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Jurusan berhasil diperbarui.');
            return redirect()->route('admin.departments.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui jurusan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.departments.department-edit');
    }
}
