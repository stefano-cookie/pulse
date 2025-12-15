<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Notifiche Push</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Admin Panel - Notifiche Push</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Form Invio Notifica -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Invia Notifica</h2>
                <form action="{{ route('admin.send-notification') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="title" class="block text-gray-600 mb-2">Titolo</label>
                        <input type="text" name="title" id="title" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Inserisci il titolo">
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-600 mb-2">Descrizione</label>
                        <textarea name="description" id="description" rows="4" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Inserisci la descrizione"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        Invia Notifica
                    </button>
                </form>
            </div>

            <!-- Lista Device Registrati -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Device Registrati ({{ $devices->count() }})</h2>
                @if($devices->isEmpty())
                    <p class="text-gray-500">Nessun device registrato.</p>
                @else
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($devices as $device)
                            <div class="border border-gray-200 rounded p-3">
                                <p class="text-sm text-gray-600"><strong>Socket ID:</strong> {{ Str::limit($device->socket_id, 30) }}</p>
                                @if($device->device_info)
                                    <p class="text-sm text-gray-500">
                                        <strong>Info:</strong>
                                        {{ $device->device_info['platform'] ?? 'N/A' }} -
                                        {{ $device->device_info['browser'] ?? 'N/A' }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">Registrato: {{ $device->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
