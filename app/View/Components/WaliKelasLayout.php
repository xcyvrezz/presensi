<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class WaliKelasLayout extends Component
{
    public function __construct(public ?string $title = null)
    {
        $this->title = $title ?? 'Dashboard Wali Kelas - Absensi MIFARE';
    }

    public function render(): View
    {
        return view('layouts.wali-kelas');
    }
}
