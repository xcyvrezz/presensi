<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class StudentsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure
{
    use SkipsFailures;

    protected $importedCount = 0;
    protected $failedCount = 0;
    protected $errors = [];

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Remove 'id' field if exists (to prevent primary key conflict)
        unset($row['id']);
        unset($row['created_at']);
        unset($row['updated_at']);
        unset($row['deleted_at']);

        // Find class
        $class = Classes::where('name', $row['class_name'])->first();

        if (!$class) {
            $this->failedCount++;
            $this->errors[] = "Baris " . ($this->importedCount + $this->failedCount + 2) . ": Kelas '{$row['class_name']}' tidak ditemukan";
            return null;
        }

        // Get siswa role
        $siswaRole = Role::where('name', 'siswa')->first();

        // Normalize card_uid (convert to string if integer)
        $cardUid = null;
        if (isset($row['card_uid']) && !empty($row['card_uid'])) {
            // Convert to string (handles both string and numeric types)
            $cardUid = (string) $row['card_uid'];
            $cardUid = trim($cardUid);

            // Validate max length
            if (strlen($cardUid) > 50) {
                $this->failedCount++;
                $this->errors[] = "Baris " . ($this->importedCount + $this->failedCount + 2) . ": Card UID terlalu panjang (maksimal 50 karakter)";
                return null;
            }

            // Check for duplicate card_uid
            if (Student::where('card_uid', $cardUid)->exists()) {
                $this->failedCount++;
                $this->errors[] = "Baris " . ($this->importedCount + $this->failedCount + 2) . ": Card UID '{$cardUid}' sudah terdaftar";
                return null;
            }
        }

        // Check for duplicate NIS
        if (Student::where('nis', $row['nis'])->exists()) {
            $this->failedCount++;
            $this->errors[] = "Baris " . ($this->importedCount + $this->failedCount + 2) . ": NIS '{$row['nis']}' sudah terdaftar";
            return null;
        }

        // Check for duplicate email
        if (User::where('email', $row['email'])->exists()) {
            $this->failedCount++;
            $this->errors[] = "Baris " . ($this->importedCount + $this->failedCount + 2) . ": Email '{$row['email']}' sudah terdaftar";
            return null;
        }

        try {
            // Create user
            $user = User::create([
                'name' => $row['full_name'],
                'email' => $row['email'],
                'password' => Hash::make($row['password'] ?? 'password123'),
                'role_id' => $siswaRole->id,
                'is_active' => true,
            ]);

            // Create student
            $student = Student::create([
                'user_id' => $user->id,
                'class_id' => $class->id,
                'nis' => $row['nis'],
                'nisn' => $row['nisn'] ?? null,
                'card_uid' => $cardUid,
                'full_name' => $row['full_name'],
                'nickname' => $row['nickname'] ?? null,
                'gender' => $row['gender'],
                'birth_date' => $row['birth_date'],
                'birth_place' => $row['birth_place'],
                'address' => $row['address'] ?? null,
                'phone' => $row['phone'] ?? null,
                'parent_phone' => $row['parent_phone'],
                'parent_name' => $row['parent_name'],
                'nfc_enabled' => !empty($cardUid),
                'is_active' => true,
            ]);

            $this->importedCount++;
            return $student;

        } catch (\Exception $e) {
            $this->failedCount++;
            $this->errors[] = "Baris " . ($this->importedCount + $this->failedCount + 2) . ": " . $e->getMessage();
            return null;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nis' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'nis'),
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'nisn'),
            ],
            'full_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'class_name' => 'required|string',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => 'nullable|string|min:6',
            // Changed: Accept both string and numeric for card_uid
            'card_uid' => [
                'nullable',
                // Remove 'string' validation - accept any scalar type
            ],
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nis.required' => 'NIS wajib diisi',
            'nis.unique' => 'NIS sudah terdaftar',
            'nisn.unique' => 'NISN sudah terdaftar',
            'full_name.required' => 'Nama lengkap wajib diisi',
            'gender.required' => 'Jenis kelamin wajib diisi',
            'gender.in' => 'Jenis kelamin harus L atau P',
            'birth_place.required' => 'Tempat lahir wajib diisi',
            'birth_date.required' => 'Tanggal lahir wajib diisi',
            'birth_date.date' => 'Format tanggal lahir tidak valid',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini',
            'parent_name.required' => 'Nama orang tua wajib diisi',
            'parent_phone.required' => 'No HP orang tua wajib diisi',
            'class_name.required' => 'Nama kelas wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal 6 karakter',
        ];
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getFailedCount(): int
    {
        return $this->failedCount + count($this->failures());
    }

    public function getErrors(): array
    {
        $errors = $this->errors;

        // Add validation errors from failures
        foreach ($this->failures() as $failure) {
            $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }

        return $errors;
    }
}
