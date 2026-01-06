<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Tambah Jurusan')]
class DepartmentCreate extends Component
{
    public $code = '';
    public $name = '';
    public $description = '';
    public $head_teacher = '';
    public $phone = '';
    public $is_active = true;

    protected $rules = [
        'code' => 'required|string|max:10|unique:departments,code',
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'head_teacher' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'is_active' => 'boolean',
    ];

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
            Department::create([
                'code' => strtoupper($validated['code']),
                'name' => $validated['name'],
                'description' => $validated['description'],
                'head_teacher' => $validated['head_teacher'],
                'phone' => $validated['phone'],
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Jurusan berhasil ditambahkan.');
            return redirect()->route('admin.departments.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan jurusan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.departments.department-create');
    }
}
