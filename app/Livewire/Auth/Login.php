<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

#[Layout('layouts.guest')]
#[Title('Login - Absensi MIFARE')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function login()
    {
        $this->validate();

        // Rate limiting
        $throttleKey = strtolower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Attempt login
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60); // Block for 60 seconds after 5 failed attempts

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        // Check if user is active
        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Akun Anda sedang tidak aktif. Hubungi administrator.',
            ]);
        }

        // Clear rate limiter
        RateLimiter::clear($throttleKey);

        // Update last login
        $user->updateLastLogin(request()->ip());

        // Regenerate session
        request()->session()->regenerate();

        // Redirect based on role
        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole($user)
    {
        if ($user->isAdmin()) {
            return redirect()->intended('/admin/dashboard');
        }

        if ($user->isKepalaSekolah()) {
            return redirect()->intended('/kepala-sekolah/dashboard');
        }

        if ($user->isWaliKelas()) {
            return redirect()->intended('/wali-kelas/dashboard');
        }

        if ($user->isStudent()) {
            return redirect()->intended('/siswa/dashboard');
        }

        // Default
        return redirect()->intended('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

