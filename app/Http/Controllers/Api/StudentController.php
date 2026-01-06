<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Get all students
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'nullable|boolean',
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Student::with(['class', 'class.department']);

        if (isset($validated['class_id'])) {
            $query->where('class_id', $validated['class_id']);
        }

        if (isset($validated['department_id'])) {
            $query->whereHas('class', function ($q) use ($validated) {
                $q->where('department_id', $validated['department_id']);
            });
        }

        if (isset($validated['is_active'])) {
            $query->where('is_active', $validated['is_active']);
        }

        if (isset($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('full_name', 'like', '%' . $validated['search'] . '%')
                  ->orWhere('nis', 'like', '%' . $validated['search'] . '%');
            });
        }

        $limit = $validated['limit'] ?? 50;
        $students = $query->orderBy('full_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'full_name' => $student->full_name,
                    'email' => $student->email,
                    'phone' => $student->phone_number,
                    'gender' => $student->gender,
                    'date_of_birth' => $student->date_of_birth,
                    'address' => $student->address,
                    'class' => [
                        'id' => $student->class->id,
                        'name' => $student->class->name,
                        'department' => [
                            'id' => $student->class->department->id,
                            'name' => $student->class->department->name,
                            'code' => $student->class->department->code,
                        ],
                    ],
                    'is_active' => $student->is_active,
                    'enrollment_year' => $student->enrollment_year,
                ];
            }),
            'count' => $students->count(),
        ]);
    }

    /**
     * Get student by ID
     */
    public function show($id)
    {
        $student = Student::with(['class', 'class.department', 'user'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'full_name' => $student->full_name,
                'email' => $student->email,
                'phone' => $student->phone_number,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth,
                'place_of_birth' => $student->place_of_birth,
                'address' => $student->address,
                'parent_name' => $student->parent_name,
                'parent_phone' => $student->parent_phone,
                'class' => [
                    'id' => $student->class->id,
                    'name' => $student->class->name,
                    'department' => [
                        'id' => $student->class->department->id,
                        'name' => $student->class->department->name,
                        'code' => $student->class->department->code,
                    ],
                ],
                'is_active' => $student->is_active,
                'enrollment_year' => $student->enrollment_year,
                'nfc_uid' => $student->nfc_uid,
            ],
        ]);
    }

    /**
     * Get student by NFC UID
     */
    public function getByNfc(Request $request)
    {
        $validated = $request->validate([
            'nfc_uid' => 'required|string',
        ]);

        $student = Student::with(['class', 'class.department'])
            ->where('nfc_uid', $validated['nfc_uid'])
            ->where('is_active', true)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found with provided NFC UID.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'full_name' => $student->full_name,
                'class' => [
                    'id' => $student->class->id,
                    'name' => $student->class->name,
                ],
                'nfc_uid' => $student->nfc_uid,
            ],
        ]);
    }
}
