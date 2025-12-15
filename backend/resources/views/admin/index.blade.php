<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Pulse Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7c3aed',
                            800: '#6b21a8',
                            900: '#581c87',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes slide-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-spin { animation: spin 1s linear infinite; }
        .animate-slide-in { animation: slide-in 0.3s ease-out; }
    </style>
</head>
<body class="bg-gray-950 min-h-screen text-gray-100">
    <header class="border-b border-gray-800 bg-gray-900/50 backdrop-blur-xl sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Pulse</h1>
                        <p class="text-xs text-gray-500">Admin Panel</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        Sistema attivo
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 flex items-start gap-3 animate-slide-in">
                <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="check" class="w-4 h-4 text-green-400"></i>
                </div>
                <div>
                    <p class="font-medium text-green-400">Operazione completata</p>
                    <p class="text-sm text-green-400/70">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 flex items-start gap-3 animate-slide-in">
                <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400"></i>
                </div>
                <div>
                    <p class="font-medium text-red-400">Errore</p>
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-400/70">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-8">
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 inline-flex">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <i data-lucide="smartphone" class="w-5 h-5 text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $devices->count() }}</p>
                        <p class="text-xs text-gray-500">Device registrati</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 text-white"></i>
                    </div>
                    <h2 class="font-semibold text-white">Invia Notifica</h2>
                </div>
                <form action="{{ route('admin.send-notification') }}" method="POST" id="notificationForm" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-400 mb-2">Titolo</label>
                            <input type="text" name="title" id="title" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="Es: Nuovo aggiornamento disponibile">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-400 mb-2">Messaggio</label>
                            <textarea name="description" id="description" rows="4" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition resize-none"
                                placeholder="Scrivi il contenuto della notifica..."></textarea>
                        </div>
                        <button type="submit" id="submitBtn"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-semibold py-3 px-6 rounded-xl flex items-center justify-center gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="send" id="sendIcon" class="w-4 h-4"></i>
                            <i data-lucide="loader-2" id="loadingIcon" class="w-4 h-4 hidden animate-spin"></i>
                            <span id="btnText">Invia a tutti i device</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <i data-lucide="smartphone" class="w-4 h-4 text-blue-400"></i>
                    </div>
                    <h2 class="font-semibold text-white">Device Registrati</h2>
                </div>
                <div class="p-6">
                    @if($devices->isEmpty())
                        <div class="text-center py-12">
                            <div class="w-16 h-16 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="inbox" class="w-8 h-8 text-gray-600"></i>
                            </div>
                            <p class="text-gray-500">Nessun device registrato</p>
                            <p class="text-sm text-gray-600 mt-1">I device appariranno qui quando si registreranno</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                            @foreach($devices as $device)
                                <div class="p-4 rounded-xl bg-gray-800/50 border border-gray-700/50 hover:border-gray-600 transition group">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="smartphone" class="w-5 h-5 text-purple-400"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            @if($device->socket_id)
                                                <p class="text-sm text-gray-400 font-mono truncate">
                                                    Socket: {{ Str::limit($device->socket_id, 20) }}
                                                </p>
                                            @endif
                                            @if($device->device_info)
                                                <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
                                                    <i data-lucide="monitor" class="w-3 h-3"></i>
                                                    {{ $device->device_info['platform'] ?? 'N/A' }} &bull;
                                                    {{ $device->device_info['browser'] ?? 'N/A' }}
                                                </p>
                                            @endif
                                            <p class="text-xs text-gray-600 mt-2 flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                {{ $device->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        const form = document.getElementById('notificationForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const sendIcon = document.getElementById('sendIcon');
        const loadingIcon = document.getElementById('loadingIcon');

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            sendIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
            btnText.textContent = 'Invio in corso...';
        });

        @if($errors->any() || session('success'))
            submitBtn.disabled = false;
            sendIcon.classList.remove('hidden');
            loadingIcon.classList.add('hidden');
            btnText.textContent = 'Invia a tutti i device';
        @endif
    </script>
</body>
</html>
