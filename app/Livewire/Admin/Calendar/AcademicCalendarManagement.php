<?php

namespace App\Livewire\Admin\Calendar;

use App\Models\AcademicCalendar;
use App\Models\Semester;
use App\Models\Department;
use App\Models\Classes;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.admin')]
#[Title('Kalender Akademik')]
class AcademicCalendarManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $calendarId;

    // Filter
    public $filterType = '';
    public $filterSemester = '';
    public $filterMonth = '';

    // Form fields
    public $semester_id;
    public $title;
    public $description;
    public $start_date;
    public $end_date;
    public $type = 'event';
    public $is_holiday = false;
    public $color = '#3B82F6';

    // Custom times
    public $use_custom_times = false;
    public $custom_check_in_start;
    public $custom_check_in_end;
    public $custom_check_in_normal;
    public $custom_check_out_start;
    public $custom_check_out_end;
    public $custom_check_out_normal;

    // Affected scope
    public $affected_departments = [];
    public $affected_classes = [];

    protected $rules = [
        'semester_id' => 'nullable|exists:semesters,id',
        'title' => 'required|string|max:200',
        'description' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'type' => 'required|in:holiday,event,exam,other',
        'is_holiday' => 'boolean',
        'color' => 'required|string|max:7',
        'use_custom_times' => 'boolean',
        'custom_check_in_start' => 'nullable|date_format:H:i',
        'custom_check_in_end' => 'nullable|date_format:H:i|after:custom_check_in_start',
        'custom_check_in_normal' => 'nullable|date_format:H:i',
        'custom_check_out_start' => 'nullable|date_format:H:i',
        'custom_check_out_end' => 'nullable|date_format:H:i|after:custom_check_out_start',
        'custom_check_out_normal' => 'nullable|date_format:H:i',
        'affected_departments' => 'nullable|array',
        'affected_departments.*' => 'exists:departments,id',
        'affected_classes' => 'nullable|array',
        'affected_classes.*' => 'exists:classes,id',
    ];

    protected $messages = [
        'title.required' => 'Judul harus diisi',
        'start_date.required' => 'Tanggal mulai harus diisi',
        'end_date.required' => 'Tanggal selesai harus diisi',
        'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai',
        'type.required' => 'Tipe harus dipilih',
        'color.required' => 'Warna harus dipilih',
        'custom_check_in_end.after' => 'Jam akhir check-in harus setelah jam mulai',
        'custom_check_out_end.after' => 'Jam akhir check-out harus setelah jam mulai',
    ];

    public function mount()
    {
        $this->filterMonth = now()->format('Y-m');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;

        // Set defaults
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        $this->semester_id = Semester::active()->first()?->id;
    }

    public function openEditModal($id)
    {
        $calendar = AcademicCalendar::with('semester')->findOrFail($id);

        $this->calendarId = $calendar->id;
        $this->semester_id = $calendar->semester_id;
        $this->title = $calendar->title;
        $this->description = $calendar->description;
        $this->start_date = $calendar->start_date->format('Y-m-d');
        $this->end_date = $calendar->end_date->format('Y-m-d');
        $this->type = $calendar->type;
        $this->is_holiday = $calendar->is_holiday;
        $this->color = $calendar->color;

        $this->use_custom_times = $calendar->use_custom_times;
        $this->custom_check_in_start = $calendar->custom_check_in_start;
        $this->custom_check_in_end = $calendar->custom_check_in_end;
        $this->custom_check_in_normal = $calendar->custom_check_in_normal;
        $this->custom_check_out_start = $calendar->custom_check_out_start;
        $this->custom_check_out_end = $calendar->custom_check_out_end;
        $this->custom_check_out_normal = $calendar->custom_check_out_normal;

        $this->affected_departments = $calendar->affected_departments ?? [];
        $this->affected_classes = $calendar->affected_classes ?? [];

        $this->editMode = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'calendarId',
            'semester_id',
            'title',
            'description',
            'start_date',
            'end_date',
            'type',
            'is_holiday',
            'color',
            'use_custom_times',
            'custom_check_in_start',
            'custom_check_in_end',
            'custom_check_in_normal',
            'custom_check_out_start',
            'custom_check_out_end',
            'custom_check_out_normal',
            'affected_departments',
            'affected_classes',
        ]);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'semester_id' => $this->semester_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'type' => $this->type,
            'is_holiday' => $this->is_holiday,
            'color' => $this->color,
            'use_custom_times' => $this->use_custom_times,
            'custom_check_in_start' => $this->use_custom_times ? $this->custom_check_in_start : null,
            'custom_check_in_end' => $this->use_custom_times ? $this->custom_check_in_end : null,
            'custom_check_in_normal' => $this->use_custom_times ? $this->custom_check_in_normal : null,
            'custom_check_out_start' => $this->use_custom_times ? $this->custom_check_out_start : null,
            'custom_check_out_end' => $this->use_custom_times ? $this->custom_check_out_end : null,
            'custom_check_out_normal' => $this->use_custom_times ? $this->custom_check_out_normal : null,
            'affected_departments' => count($this->affected_departments) > 0 ? $this->affected_departments : null,
            'affected_classes' => count($this->affected_classes) > 0 ? $this->affected_classes : null,
        ];

        if ($this->editMode) {
            $calendar = AcademicCalendar::findOrFail($this->calendarId);
            $calendar->update($data);
            session()->flash('success', 'Kalender berhasil diupdate');
        } else {
            $data['created_by'] = auth()->id();
            AcademicCalendar::create($data);
            session()->flash('success', 'Kalender berhasil ditambahkan');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $calendar = AcademicCalendar::findOrFail($id);
        $calendar->delete();
        session()->flash('success', 'Kalender berhasil dihapus');
    }

    public function updatedType($value)
    {
        // Auto-set holiday flag and color based on type
        if ($value === 'holiday') {
            $this->is_holiday = true;
            $this->color = '#EF4444'; // Red
        } elseif ($value === 'exam') {
            $this->is_holiday = false;
            $this->color = '#F59E0B'; // Orange
        } elseif ($value === 'event') {
            $this->is_holiday = false;
            $this->color = '#3B82F6'; // Blue
        }
    }

    public function render()
    {
        $query = AcademicCalendar::with(['semester', 'creator']);

        // Apply filters
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterSemester) {
            $query->where('semester_id', $this->filterSemester);
        }

        if ($this->filterMonth) {
            $month = Carbon::parse($this->filterMonth);
            $query->whereYear('start_date', $month->year)
                  ->whereMonth('start_date', $month->month);
        }

        $calendars = $query->orderBy('start_date', 'desc')->paginate(15);

        return view('livewire.admin.calendar.academic-calendar-management', [
            'calendars' => $calendars,
            'semesters' => Semester::orderBy('start_date', 'desc')->get(),
            'departments' => Department::where('is_active', true)->get(),
            'classes' => Classes::with('department')->where('is_active', true)->get(),
        ]);
    }
}
