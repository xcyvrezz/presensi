<?php

namespace App\Livewire\Components;

use Livewire\Component;

class OfflineDetector extends Component
{
    public $isOnline = true;

    public function render()
    {
        return view('livewire.components.offline-detector');
    }
}
