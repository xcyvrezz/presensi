<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Animated blobs -->
        <div class="absolute top-0 left-0 w-full h-full opacity-40">
            <div class="absolute top-20 left-10 w-72 h-72 bg-blue-200 rounded-full filter blur-3xl animate-float"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-slate-200 rounded-full filter blur-3xl animate-float-delay"></div>
            <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-blue-100 rounded-full filter blur-3xl animate-float-slow"></div>
        </div>
        
        <!-- Grid Pattern -->
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgb(148 163 184 / 0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
        
        <!-- Decorative shapes -->
        <div class="absolute top-10 right-20 w-20 h-20 border-2 border-blue-200 rounded-lg transform rotate-12 opacity-30"></div>
        <div class="absolute bottom-32 left-16 w-16 h-16 border-2 border-slate-300 rounded-full opacity-20"></div>
        <div class="absolute top-1/3 right-1/4 w-12 h-12 bg-gradient-to-br from-blue-100 to-transparent rounded-lg transform -rotate-45 opacity-40"></div>
    </div>
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-20px) translateX(10px); }
        }
        @keyframes float-delay {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(20px) translateX(-10px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-15px) translateX(-15px); }
        }
        .animate-float {
            animation: float 8s ease-in-out infinite;
        }
        .animate-float-delay {
            animation: float-delay 10s ease-in-out infinite;
        }
        .animate-float-slow {
            animation: float-slow 12s ease-in-out infinite;
        }
    </style>

    <div class="max-w-md w-full space-y-8 relative z-10">
        <!-- Logo & Title -->
        <div class="text-center">
            <!-- Logo with rings -->
            <div class="relative inline-block mb-6">
                <div class="absolute inset-0 bg-blue-500 rounded-3xl blur-xl opacity-20"></div>
                <div class="relative mx-auto h-20 w-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl flex items-center justify-center shadow-lg shadow-blue-500/30 transform transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-blue-500/40">
                    <svg class="h-11 w-11 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            
            <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">
                Absensi MIFARE
            </h2>
            <p class="mt-3 text-base sm:text-lg text-slate-600 font-medium">
                SMK Negeri 10 Pandeglang
            </p>
            <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100">
                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                <span class="text-xs text-blue-700 font-medium">Dual-Method Attendance System</span>
            </div>
        </div>

        <!-- Login Card -->
        <div class="relative">
            <!-- Card decorative elements -->
            <div class="absolute -top-4 -left-4 w-24 h-24 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl opacity-20 blur-2xl"></div>
            <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-gradient-to-br from-slate-400 to-slate-500 rounded-2xl opacity-20 blur-2xl"></div>
            
            <!-- Card pattern overlay -->
            <div class="absolute inset-0 rounded-3xl opacity-5" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgb(59 130 246) 10px, rgb(59 130 246) 11px);"></div>
            
            <div class="relative bg-white py-10 px-6 sm:py-12 sm:px-10 shadow-xl shadow-slate-300/50 rounded-3xl border border-slate-200">
                <!-- Corner decorations -->
                <div class="absolute top-4 right-4 w-8 h-8 border-t-2 border-r-2 border-blue-200 rounded-tr-xl"></div>
                <div class="absolute bottom-4 left-4 w-8 h-8 border-b-2 border-l-2 border-slate-200 rounded-bl-xl"></div>
                <form wire:submit.prevent="login" class="space-y-6">
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-slate-700">
                            Email Address
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200">
                                <svg class="h-5 w-5 text-slate-500 group-focus-within:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                wire:model="email"
                                id="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="appearance-none block w-full pl-12 pr-4 py-3.5 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-slate-400 @error('email') border-red-300 focus:ring-red-500 @enderror"
                                placeholder="nama@smkn10pdg.sch.id"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-slate-700">
                            Password
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200">
                                <svg class="h-5 w-5 text-slate-500 group-focus-within:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                wire:model="password"
                                id="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="appearance-none block w-full pl-12 pr-4 py-3.5 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-slate-400 @error('password') border-red-300 focus:ring-red-500 @enderror"
                                placeholder="••••••••••"
                            >
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center">
                            <input
                                wire:model="remember"
                                id="remember"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 border-slate-300 rounded cursor-pointer transition-all"
                            >
                            <label for="remember" class="ml-2.5 block text-sm text-slate-600 cursor-pointer select-none">
                                Ingat saya
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="#" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                Lupa password?
                            </a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="relative w-full group flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transform hover:-translate-y-0.5 active:translate-y-0"
                        >
                            <span wire:loading.remove class="flex items-center">
                                Masuk ke Dashboard
                                <svg class="ml-2 h-5 w-5 transform transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </span>
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="mt-8 pt-6 border-t border-slate-200 relative">
                    <!-- Decorative dots on divider -->
                    <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 flex gap-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                    </div>
                    
                    <div class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <p class="text-center text-sm text-slate-500">
                            Sistem keamanan tingkat tinggi dengan enkripsi end-to-end
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center space-y-3 relative">
            <!-- Decorative line -->
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-12 h-px bg-gradient-to-r from-transparent to-slate-300"></div>
                <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                <div class="w-12 h-px bg-gradient-to-l from-transparent to-slate-300"></div>
            </div>
            
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/60 backdrop-blur-sm rounded-full border border-slate-200 shadow-sm">
                <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                <p class="text-sm text-slate-600 font-medium">
                    &copy; 2025 SMK Negeri 10 Pandeglang
                </p>
            </div>
            
            <p class="text-xs text-slate-500 inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Powered by MIFARE Technology
            </p>
        </div>
    </div>
</div>