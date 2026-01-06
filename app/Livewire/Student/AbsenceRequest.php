<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\AbsenceRequest as AbsenceRequestModel;
use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.student')]
#[Title('Pengajuan Izin')]
class AbsenceRequest extends Component
{
    use WithFileUploads, WithPagination;

    public $student;
    public $absenceDate;
    public $type = 'izin';
    public $reason = '';
    public $document;

    // For editing/viewing
    public $selectedRequest = null;
    public $showDetailModal = false;

    protected $rules = [
        'absenceDate' => 'required|date|after_or_equal:today',
        'type' => 'required|in:izin,sakit',
        'reason' => 'required|string|min:10|max:500',
        'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ];

    protected $messages = [
        'absenceDate.required' => 'Tanggal izin harus diisi.',
        'absenceDate.after_or_equal' => 'Tanggal izin tidak boleh di masa lalu.',
        'type.required' => 'Jenis izin harus dipilih.',
        'reason.required' => 'Alasan harus diisi.',
        'reason.min' => 'Alasan minimal 10 karakter.',
        'reason.max' => 'Alasan maksimal 500 karakter.',
        'document.mimes' => 'Dokumen harus berupa PDF, JPG, JPEG, atau PNG.',
        'document.max' => 'Ukuran dokumen maksimal 2MB.',
    ];

    public function mount()
    {
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.waliKelas'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        // Set default date to today
        $this->absenceDate = Carbon::today()->format('Y-m-d');
    }

    public function submit()
    {
        $this->validate();

        // Check if already has pending or approved request for this date
        $existingRequest = AbsenceRequestModel::where('student_id', $this->student->id)
            ->where('absence_date', $this->absenceDate)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            session()->flash('error', 'Anda sudah memiliki pengajuan izin untuk tanggal ini dengan status: ' . $existingRequest->status_label);
            return;
        }

        $documentPath = null;
        if ($this->document) {
            $documentPath = $this->document->store('absence-documents', 'public');
        }

        $request = AbsenceRequestModel::create([
            'student_id' => $this->student->id,
            'absence_date' => $this->absenceDate,
            'type' => $this->type,
            'reason' => $this->reason,
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        // Send notification to wali kelas
        NotificationService::absenceRequestSubmitted($request);

        session()->flash('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan wali kelas.');

        // Reset form
        $this->reset(['absenceDate', 'type', 'reason', 'document']);
        $this->absenceDate = Carbon::today()->format('Y-m-d');
    }

    public function viewRequest($requestId)
    {
        $this->selectedRequest = AbsenceRequestModel::with(['student', 'approvedBy'])
            ->findOrFail($requestId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedRequest = null;
    }

    public function cancelRequest($requestId)
    {
        $request = AbsenceRequestModel::where('student_id', $this->student->id)
            ->where('id', $requestId)
            ->where('status', 'pending')
            ->first();

        if ($request) {
            $request->delete();
            session()->flash('success', 'Pengajuan izin berhasil dibatalkan.');
        } else {
            session()->flash('error', 'Pengajuan izin tidak dapat dibatalkan.');
        }
    }

    public function render()
    {
        $requests = AbsenceRequestModel::where('student_id', $this->student->id)
            ->orderBy('absence_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.student.absence-request', [
            'requests' => $requests,
        ]);
    }
}
