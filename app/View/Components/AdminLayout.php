<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public function __construct(
        public ?string $title = null
    ) {
        $this->title = $title ?? 'Dashboard Admin - Absensi MIFARE';
    }

    public function render(): View
    {
        return view('layouts.admin');
    }
}
