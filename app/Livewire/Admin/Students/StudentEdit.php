<?php

namespace App\Livewire\Admin\Students;

use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
#[Title('Edit Siswa')]
class StudentEdit extends Component
{
    use WithFileUploads;

    public Student $student;

    // User fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    // Student fields
    public $class_id = '';
    public $nis = '';
    public $nisn = '';
    public $card_uid = '';
    public $full_name = '';
    public $nickname = '';
    public $gender = '';
    public $birth_date = '';
    public $birth_place = '';
    public $address = '';
    public $phone = '';
    public $parent_phone = '';
    public $parent_name = '';
    public $photo;
    public $existing_photo = '';
    public $nfc_enabled = true;
    public $is_active = true;

    public function mount(Student $student)
    {
        $this->student = $student->load('user');

        // Load user data
        if ($this->student->user) {
            $this->name = $this->student->user->name;
            $this->email = $this->student->user->email;
        }

        // Load student data
        $this->class_id = $this->student->class_id;
        $this->nis = $this->student->nis;
        $this->nisn = $this->student->nisn;
        $this->card_uid = $this->student->card_uid;
        $this->full_name = $this->student->full_name;
        $this->nickname = $this->student->nickname;
        $this->gender = $this->student->gender;
        $this->birth_date = $this->student->birth_date?->format('Y-m-d');
        $this->birth_place = $this->student->birth_place;
        $this->address = $this->student->address;
        $this->phone = $this->student->phone;
        $this->parent_phone = $this->student->parent_phone;
        $this->parent_name = $this->student->parent_name;
        $this->existing_photo = $this->student->photo;
        $this->nfc_enabled = $this->student->nfc_enabled;
        $this->is_active = $this->student->is_active;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->student->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'class_id' => 'required|exists:classes,id',
            'nis' => 'required|string|unique:students,nis,' . $this->student->id,
            'nisn' => 'nullable|string|unique:students,nisn,' . $this->student->id,
            'card_uid' => 'nullable|string|unique:students,card_uid,' . $this->student->id,
            'full_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:100',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'parent_phone' => 'required|string|max:20',
            'parent_name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'nfc_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama user harus diisi.',
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'class_id.required' => 'Kelas harus dipilih.',
        'nis.required' => 'NIS harus diisi.',
        'nis.unique' => 'NIS sudah terdaftar.',
        'nisn.unique' => 'NISN sudah terdaftar.',
        'card_uid.unique' => 'Card UID sudah terdaftar.',
        'full_name.required' => 'Nama lengkap harus diisi.',
        'gender.required' => 'Jenis kelamin harus dipilih.',
        'birth_date.required' => 'Tanggal lahir harus diisi.',
        'birth_place.required' => 'Tempat lahir harus diisi.',
        'address.required' => 'Alamat harus diisi.',
        'parent_phone.required' => 'Telepon orang tua harus diisi.',
        'parent_name.required' => 'Nama orang tua harus diisi.',
        'photo.image' => 'File harus berupa gambar.',
        'photo.max' => 'Ukuran gambar maksimal 2MB.',
    ];

    public function save()
    {
        $validated = $this->validate();

        // Update user account
        if ($this->student->user) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $this->student->user->update($userData);
        }

        // Handle photo upload
        $photoPath = $this->existing_photo;
        if ($this->photo) {
            // Delete old photo if exists
            if ($this->existing_photo) {
                Storage::disk('public')->delete($this->existing_photo);
            }
            $photoPath = $this->photo->store('students', 'public');
        }

        // Update student record
        $this->student->update([
            'class_id' => $validated['class_id'],
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'] ?? null,
            'card_uid' => $validated['card_uid'] ?? null,
            'full_name' => $validated['full_name'],
            'nickname' => $validated['nickname'] ?? null,
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'birth_place' => $validated['birth_place'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'parent_phone' => $validated['parent_phone'],
            'parent_name' => $validated['parent_name'],
            'photo' => $photoPath,
            'nfc_enabled' => $validated['nfc_enabled'],
            'is_active' => $validated['is_active'],
        ]);

        session()->flash('success', 'Siswa berhasil diperbarui.');

        return redirect()->route('admin.students.index');
    }

    public function render()
    {
        $classes = Classes::with('department')->orderBy('name')->get();

        return view('livewire.admin.students.student-edit', [
            'classes' => $classes,
        ]);
    }
}
