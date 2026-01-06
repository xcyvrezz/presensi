<?php

namespace App\Livewire\Admin\Settings;

use App\Models\AttendanceSetting;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Pengaturan Sistem')]
class AttendanceSettings extends Component
{
    public $activeTab = 'time_windows';
    public $settings = [];
    public $editMode = [];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $allSettings = AttendanceSetting::editable()
            ->ordered()
            ->get()
            ->groupBy('group');

        $this->settings = $allSettings->map(function ($group) {
            return $group->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });
        })->toArray();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function save()
    {
        try {
            foreach ($this->settings as $group => $groupSettings) {
                foreach ($groupSettings as $key => $value) {
                    AttendanceSetting::setValue($key, $value, auth()->id());
                }
            }

            session()->flash('success', 'Pengaturan berhasil disimpan.');
            $this->loadSettings();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        try {
            $settings = AttendanceSetting::editable()->get();

            foreach ($settings as $setting) {
                $setting->value = $setting->default_value;
                $setting->last_modified_by = auth()->id();
                $setting->save();
            }

            session()->flash('success', 'Pengaturan berhasil direset ke default.');
            $this->loadSettings();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mereset pengaturan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $groupedSettings = AttendanceSetting::editable()
            ->ordered()
            ->get()
            ->groupBy('group');

        return view('livewire.admin.settings.attendance-settings', [
            'groupedSettings' => $groupedSettings,
        ]);
    }
}
