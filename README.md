# Pulse Notifications - PWA con Notifiche Push Real-time

Una Progressive Web App (PWA) che riceve notifiche push in tempo reale tramite Pusher.

## Stack Tecnologico

- **Frontend**: Vue.js 3 + Vite + PWA
- **Backend**: Laravel 12
- **Database**: SQLite
- **Real-time**: Pusher

## Struttura Progetto

```
pulse/
├── backend/          # Laravel API + Admin Panel
├── frontend/         # Vue.js PWA
└── README.md
```

## Requisiti

- PHP 8.1+
- Composer
- Node.js 18+
- Account Pusher (https://pusher.com)

## Setup

### 1. Configurazione Pusher

1. Vai su https://pusher.com e crea un account
2. Crea una nuova app "Channels"
3. Copia le credenziali: App ID, Key, Secret, Cluster

### 2. Backend (Laravel)

```bash
cd backend

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=eu

php artisan serve
```

Il backend sarà disponibile su: http://localhost:8000

- **Admin Panel**: http://localhost:8000/admin
- **API Devices**: POST http://localhost:8000/api/devices/register

### 3. Frontend (Vue.js PWA)

```bash
cd frontend
npm install

VITE_PUSHER_APP_KEY=your_app_key
VITE_PUSHER_APP_CLUSTER=eu

npm run dev
```

Il frontend sarà disponibile su: http://localhost:5173

### 4. Build per Produzione

```bash
cd frontend
npm run build

# I file saranno in frontend/dist/
```

## Funzionalità

### Frontend PWA
- **Installabile**: Su Android mostra il bottone "Installa App", su iOS mostra le istruzioni manuali
- **Device Detection**: Rileva automaticamente iOS/Android/Desktop
- **Notifiche Real-time**: Riceve notifiche via Pusher e le mostra sia in-app che come notifiche browser
- **Offline Ready**: Service Worker per funzionamento offline

### Backend
- **API REST**: Endpoint per registrare i device
- **Admin Panel**: Interfaccia semplice per inviare notifiche
- **Broadcasting**: Evento Pusher per notifiche real-time

## API Endpoints

### POST /api/devices/register
Registra un nuovo device.

**Body:**
```json
{
  "socket_id": "123.456",
  "device_info": {
    "platform": "MacIntel",
    "browser": "Chrome",
    "isIOS": false,
    "isAndroid": false
  }
}
```

**Response:**
```json
{
  "message": "Device registered successfully",
  "device": { ... }
}
```

## Database Schema

### Tabella `devices`
| Campo | Tipo | Descrizione |
|-------|------|-------------|
| id | bigint | Primary key |
| socket_id | string | Pusher socket ID (unique) |
| device_info | json | Informazioni sul device |
| created_at | timestamp | Data creazione |
| updated_at | timestamp | Data modifica |

## Testing

1. Apri il frontend su http://localhost:5173
2. Verifica che lo stato sia "Connesso"
3. Accorda i permessi per le notifiche
4. Apri l'Admin Panel su http://localhost:8000/admin
5. Invia una notifica con Titolo e Descrizione
6. La notifica apparirà in tempo reale nel frontend

## Note

- Per testare le notifiche push su mobile, è necessario servire il frontend via HTTPS
- Su iOS, le notifiche push sono supportate solo da iOS 16.4+ quando l'app è installata come PWA
- Il Service Worker viene generato automaticamente in fase di build

## Troubleshooting

### "Disconnesso" nel frontend
- Verifica che le credenziali Pusher siano corrette in entrambi i file .env
- Controlla la console del browser per errori

### Notifiche non arrivano
- Verifica che i permessi notifiche siano stati accordati
- Controlla che il backend invii l'evento (verifica logs Laravel)
- Verifica la Pusher Debug Console su pusher.com
