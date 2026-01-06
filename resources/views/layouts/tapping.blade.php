<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Tapping Station' }} - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @stack('styles')

    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            min-height: 100vh;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen">
        {{ $slot }}
    </div>

    @livewireScripts

    <!-- Audio for feedback -->
    <audio id="sound-success" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIFmS57OihUBELTKXh8bllHAU2jdXvxnkqBSl+zPLaizsKGGm98OukUhEMTqfh8bZjHAU5j9fyz3ksBS1+zPDajT0HFWq+8OylUhELTqjh8bZjHAU5kdXvx3kqBSt+zPDajT0HF2rA8OylUhENT6jh8bRjHAU6kdXvyHkqBS5+y/DajT4HGGvA8OylURENUKjh8bRjHAU8kdXvyHkqBS5+y+/ajT4HGWvB8OylURENUKjg8LRjHAU8kdTux3kqBTB+yu/Zjj4HGmvB8OulURENUKfg77RjHAU+kdTtyHkqBTB+y+/Zjj4HGmzB7+ulUREOUKfg77NjGwU+kdTuyHgpBTF+y+/Yjj4HGmzB7+ulUBEOUKff7rJiGwU+ktTuyHgpBTF+yu7Yjj4HG23B7+ulUBEOUKbf7rJhGgU/ktPux3gpBTJ+yu7Xjj0HGm3B7uqlUBEOUKbe7rJhGgVAktPux3gpBTJ+ye7Xjj0HGm3B7uqlUBEOT6be7rJgGgVAktPuwHgpBTJ+ye7XjT0GGm3A7uqlUBEOT6be7rJgGgVBktPuwHgoBTJ+ye7XjT0GGm3A7uqlUBEOT6bd7rJgGgVBktPuwHgoBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bc7bFgGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVB" type="audio/wav">
    </audio>
    <audio id="sound-error" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIFmS57OihUBELTKXh8bllHAU2jdXvxnkqBSl+zPLaizsKGGm98OukUhEMTqfh8bZjHAU5j9fyz3ksBS1+zPDajT0HFWq+8OylUhELTqjh8bZjHAU5kdXvx3kqBSt+zPDajT0HF2rA8OylUhENT6jh8bRjHAU6kdXvyHkqBS5+y/DajT4HGGvA8OylURENUKjh8bRjHAU8kdXvyHkqBS5+y+/ajT4HGWvB8OylURENUKjg8LRjHAU8kdTux3kqBTB+yu/Zjj4HGmvB8OulURENUKfg77RjHAU+kdTtyHkqBTB+y+/Zjj4HGmzB7+ulUREOUKfg77NjGwU+kdTuyHgpBTF+y+/Yjj4HGmzB7+ulUBEOUKff7rJiGwU+ktTuyHgpBTF+yu7Yjj4HG23B7+ulUBEOUKbf7rJhGgU/ktPux3gpBTJ+yu7Xjj0HGm3B7uqlUBEOUKbe7rJhGgVAktPux3gpBTJ+ye7Xjj0HGm3B7uqlUBEOT6be7rJgGgVAktPuwHgpBTJ+ye7XjT0GGm3A7uqlUBEOT6be7rJgGgVBktPuwHgoBTJ+ye7XjT0GGm3A7uqlUBEOT6bd7rJgGgVBktPuwHgoBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bc7bFgGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVB" type="audio/wav">
    </audio>
    <audio id="sound-warning" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIFmS57OihUBELTKXh8bllHAU2jdXvxnkqBSl+zPLaizsKGGm98OukUhEMTqfh8bZjHAU5j9fyz3ksBS1+zPDajT0HFWq+8OylUhELTqjh8bZjHAU5kdXvx3kqBSt+zPDajT0HF2rA8OylUhENT6jh8bRjHAU6kdXvyHkqBS5+y/DajT4HGGvA8OylURENUKjh8bRjHAU8kdXvyHkqBS5+y+/ajT4HGWvB8OylURENUKjg8LRjHAU8kdTux3kqBTB+yu/Zjj4HGmvB8OulURENUKfg77RjHAU+kdTtyHkqBTB+y+/Zjj4HGmzB7+ulUREOUKfg77NjGwU+kdTuyHgpBTF+y+/Yjj4HGmzB7+ulUBEOUKff7rJiGwU+ktTuyHgpBTF+yu7Yjj4HG23B7+ulUBEOUKbf7rJhGgU/ktPux3gpBTJ+yu7Xjj0HGm3B7uqlUBEOUKbe7rJhGgVAktPux3gpBTJ+ye7Xjj0HGm3B7uqlUBEOT6be7rJgGgVAktPuwHgpBTJ+ye7XjT0GGm3A7uqlUBEOT6be7rJgGgVBktPuwHgoBTJ+ye7XjT0GGm3A7uqlUBEOT6bd7rJgGgVBktPuwHgoBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bc7bFgGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVB" type="audio/wav">
    </audio>
    <audio id="sound-info" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIFmS57OihUBELTKXh8bllHAU2jdXvxnkqBSl+zPLaizsKGGm98OukUhEMTqfh8bZjHAU5j9fyz3ksBS1+zPDajT0HFWq+8OylUhELTqjh8bZjHAU5kdXvx3kqBSt+zPDajT0HF2rA8OylUhENT6jh8bRjHAU6kdXvyHkqBS5+y/DajT4HGGvA8OylURENUKjh8bRjHAU8kdXvyHkqBS5+y+/ajT4HGWvB8OylURENUKjg8LRjHAU8kdTux3kqBTB+yu/Zjj4HGmvB8OulURENUKfg77RjHAU+kdTtyHkqBTB+y+/Zjj4HGmzB7+ulUREOUKfg77NjGwU+kdTuyHgpBTF+y+/Yjj4HGmzB7+ulUBEOUKff7rJiGwU+ktTuyHgpBTF+yu7Yjj4HG23B7+ulUBEOUKbf7rJhGgU/ktPux3gpBTJ+yu7Xjj0HGm3B7uqlUBEOUKbe7rJhGgVAktPux3gpBTJ+ye7Xjj0HGm3B7uqlUBEOT6be7rJgGgVAktPuwHgpBTJ+ye7XjT0GGm3A7uqlUBEOT6be7rJgGgVBktPuwHgoBTJ+ye7XjT0GGm3A7uqlUBEOT6bd7rJgGgVBktPuwHgoBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bd7bFgGgVBktPuv3goBTN+ye7WjT0GGm3A7uqlUBEOT6bc7bFgGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuv3goBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbc7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVBktPuvngoBTN+ye7WjT0GGm2/7uqlUBEOTqbb7bFfGgVB" type="audio/wav">
    </audio>

    <script>
        // Play sound function
        window.addEventListener('play-sound', event => {
            const soundType = event.detail.sound || 'success';
            const audio = document.getElementById('sound-' + soundType);
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(e => console.log('Audio play failed:', e));
            }
        });

        // Schedule clear after delay
        window.addEventListener('schedule-clear', () => {
            setTimeout(() => {
                Livewire.dispatch('clearDisplay');
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>
</html>
