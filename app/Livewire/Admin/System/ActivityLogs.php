<?php

namespace App\Livewire\Admin\System;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.admin')]
#[Title('Activity Logs & Audit Trail')]
class ActivityLogs extends Component
{
    use WithPagination;

    public $dateRange = '7days';
    public $customStartDate;
    public $customEndDate;
    public $selectedUser = '';
    public $selectedAction = '';
    public $selectedCategory = '';
    public $selectedSeverity = '';
    public $search = '';

    public $showDetailModal = false;
    public $selectedLog = null;

    public function mount()
    {
        $this->customStartDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $this->customEndDate = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedUser()
    {
        $this->resetPage();
    }

    public function updatingSelectedAction()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatingSelectedSeverity()
    {
        $this->resetPage();
    }

    public function updatedDateRange()
    {
        $this->setDateRangeFromPreset();
        $this->resetPage();
    }

    public function applyCustomDate()
    {
        $this->dateRange = 'custom';
        $this->resetPage();
    }

    private function setDateRangeFromPreset()
    {
        switch ($this->dateRange) {
            case '24hours':
                $this->customStartDate = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');
                $this->customEndDate = Carbon::now()->format('Y-m-d H:i:s');
                break;
            case '7days':
                $this->customStartDate = Carbon::now()->subDays(7)->format('Y-m-d');
                $this->customEndDate = Carbon::now()->format('Y-m-d');
                break;
            case '30days':
                $this->customStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
                $this->customEndDate = Carbon::now()->format('Y-m-d');
                break;
            case '90days':
                $this->customStartDate = Carbon::now()->subDays(90)->format('Y-m-d');
                $this->customEndDate = Carbon::now()->format('Y-m-d');
                break;
        }
    }

    public function viewDetails($logId)
    {
        $this->selectedLog = ActivityLog::with('user')->find($logId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedLog = null;
    }

    public function clearFilters()
    {
        $this->reset([
            'selectedUser',
            'selectedAction',
            'selectedCategory',
            'selectedSeverity',
            'search'
        ]);
    }

    public function render()
    {
        $startDate = Carbon::parse($this->customStartDate);
        $endDate = Carbon::parse($this->customEndDate);

        $logs = ActivityLog::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($this->selectedUser, fn($q) => $q->where('user_id', $this->selectedUser))
            ->when($this->selectedAction, fn($q) => $q->where('action', $this->selectedAction))
            ->when($this->selectedCategory, fn($q) => $q->where('category', $this->selectedCategory))
            ->when($this->selectedSeverity, fn($q) => $q->where('severity', $this->selectedSeverity))
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('description', 'like', '%' . $this->search . '%')
                          ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                          ->orWhere('url', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $users = User::orderBy('name')->get();

        // Get distinct actions and categories for filters
        $actions = ActivityLog::distinct()->pluck('action')->sort();
        $categories = ActivityLog::distinct()->pluck('category')->sort();

        // Statistics
        $stats = [
            'total_logs' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])->count(),
            'critical_logs' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])->where('severity', 'critical')->count(),
            'unique_users' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])->distinct('user_id')->count('user_id'),
            'login_attempts' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])->where('action', 'login')->count(),
        ];

        return view('livewire.admin.system.activity-logs', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
