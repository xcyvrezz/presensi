<?php

namespace App\Livewire\WaliKelas;

use App\Models\Classes;
use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\Semester;
use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.wali-kelas')]
#[Title('Persetujuan Izin')]
class AbsenceRequestApproval extends Component
{
    use WithPagination;

    public $class;
    public $statusFilter = 'pending';
    public $search = '';

    // For approval/rejection modal
    public $selectedRequest = null;
    public $showApprovalModal = false;
    public $rejectionReason = '';

    public function mount()
    {
        // Get class yang diampu oleh wali kelas ini
        $this->class = Classes::where('wali_kelas_id', auth()->id())
            ->with(['department'])
            ->first();

        if (!$this->class) {
            session()->flash('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openApprovalModal($requestId)
    {
        $this->selectedRequest = AbsenceRequest::with(['student', 'approvedBy'])->findOrFail($requestId);
        $this->showApprovalModal = true;
        $this->rejectionReason = '';
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->selectedRequest = null;
        $this->rejectionReason = '';
    }

    public function approve($requestId)
    {
        $request = AbsenceRequest::findOrFail($requestId);

        // Validate that the request belongs to a student in this class
        if ($request->student->class_id !== $this->class->id) {
            session()->flash('error', 'Anda tidak memiliki akses untuk menyetujui permintaan ini.');
            return;
        }

        // Update request status
        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        // Auto-create attendance record for the absence date
        $this->createAttendanceFromRequest($request);

        // Send notification to student
        NotificationService::absenceRequestApproved($request, auth()->user());

        session()->flash('success', 'Pengajuan izin dari ' . $request->student->full_name . ' telah disetujui dan tercatat sebagai ' . $request->type_label . '.');
        $this->closeApprovalModal();
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:10|max:500',
        ], [
            'rejectionReason.required' => 'Alasan penolakan harus diisi.',
            'rejectionReason.min' => 'Alasan penolakan minimal 10 karakter.',
            'rejectionReason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        if (!$this->selectedRequest) {
            return;
        }

        // Validate that the request belongs to a student in this class
        if ($this->selectedRequest->student->class_id !== $this->class->id) {
            session()->flash('error', 'Anda tidak memiliki akses untuk menolak permintaan ini.');
            return;
        }

        $this->selectedRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        // Send notification to student
        NotificationService::absenceRequestRejected($this->selectedRequest, auth()->user(), $this->rejectionReason);

        session()->flash('success', 'Pengajuan izin dari ' . $this->selectedRequest->student->full_name . ' telah ditolak.');
        $this->closeApprovalModal();
    }

    public function bulkApprove()
    {
        if (!$this->class) {
            return;
        }

        $pendingRequests = AbsenceRequest::whereHas('student', function ($q) {
            $q->where('class_id', $this->class->id);
        })
        ->where('status', 'pending')
        ->get();

        $count = 0;
        foreach ($pendingRequests as $request) {
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => Carbon::now(),
            ]);

            // Auto-create attendance record
            $this->createAttendanceFromRequest($request);
            $count++;
        }

        session()->flash('success', $count . ' pengajuan izin telah disetujui dan tercatat dalam absensi.');
    }

    /**
     * Create attendance record from approved absence request
     */
    private function createAttendanceFromRequest($request)
    {
        // Get active semester
        $activeSemester = Semester::where('is_active', true)->first();

        if (!$activeSemester) {
            session()->flash('error', 'Tidak ada semester aktif. Hubungi admin untuk mengaktifkan semester.');
            return;
        }

        // Check if attendance already exists for this date
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->whereDate('date', $request->absence_date)
            ->first();

        if ($existingAttendance) {
            // Update existing attendance
            $existingAttendance->update([
                'status' => $request->type, // 'izin' or 'sakit'
                'check_out_time' => '16:00:00',
                'notes' => 'Disetujui oleh Wali Kelas: ' . $request->reason,
                'check_in_method' => 'manual',
                'check_out_method' => 'manual',
                'percentage' => 50, // Izin/Sakit dapat 50%
            ]);
        } else {
            // Create new attendance record
            Attendance::create([
                'student_id' => $request->student_id,
                'class_id' => $request->student->class_id,
                'semester_id' => $activeSemester->id,
                'date' => $request->absence_date,
                'check_in_time' => '07:00:00',
                'check_out_time' => '16:00:00',
                'status' => $request->type, // 'izin' or 'sakit'
                'percentage' => 50, // Izin/Sakit dapat 50%
                'check_in_method' => 'manual',
                'check_out_method' => 'manual',
                'notes' => 'Disetujui oleh Wali Kelas: ' . $request->reason,
            ]);
        }
    }

    public function render()
    {
        if (!$this->class) {
            return view('livewire.wali-kelas.absence-request-approval', [
                'requests' => collect(),
            ]);
        }

        $requests = AbsenceRequest::whereHas('student', function ($q) {
            $q->where('class_id', $this->class->id);

            if ($this->search) {
                $q->where(function ($query) {
                    $query->where('full_name', 'like', '%' . $this->search . '%')
                          ->orWhere('nis', 'like', '%' . $this->search . '%');
                });
            }
        })
        ->with(['student', 'approvedBy'])
        ->when($this->statusFilter, function ($query) {
            $query->where('status', $this->statusFilter);
        })
        ->orderBy('absence_date', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('livewire.wali-kelas.absence-request-approval', [
            'requests' => $requests,
        ]);
    }
}
