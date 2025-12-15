<script setup>
import { ref, onMounted, computed } from 'vue'
import Pusher from 'pusher-js'
import axios from 'axios'

const notifications = ref([])
const isConnected = ref(false)
const socketId = ref(null)
const notificationPermission = ref('Notification' in window ? Notification.permission : 'default')
const showInstallPrompt = ref(false)
const isIOS = ref(false)
const isAndroid = ref(false)
const isStandalone = ref(false)
const deferredPrompt = ref(null)
const forceIOSMode = ref(false)
const displayMode = ref('browser')
const navigatorStandalone = ref(false)
const isHTTPS = ref(window.location.protocol === 'https:')
const iOSVersionNumber = ref(0)

const deviceInfo = computed(() => ({
  platform: navigator.platform,
  userAgent: navigator.userAgent,
  browser: getBrowserName(),
  isIOS: isIOS.value,
  isAndroid: isAndroid.value,
  isStandalone: isStandalone.value,
  iOSVersion: getIOSVersion(),
  isHTTPS: isHTTPS.value,
  protocol: window.location.protocol
}))

function getIOSVersion() {
  const match = navigator.userAgent.match(/OS (\d+)_(\d+)_?(\d+)?/)
  if (match) {
    const major = parseInt(match[1])
    const minor = parseInt(match[2])
    iOSVersionNumber.value = major + (minor / 10)
    return `${match[1]}.${match[2]}${match[3] ? '.' + match[3] : ''}`
  }
  iOSVersionNumber.value = 0
  return 'Unknown'
}

function getBrowserName() {
  const ua = navigator.userAgent
  if (ua.includes('Chrome')) return 'Chrome'
  if (ua.includes('Safari')) return 'Safari'
  if (ua.includes('Firefox')) return 'Firefox'
  if (ua.includes('Edge')) return 'Edge'
  return 'Unknown'
}

function detectDevice() {
  const ua = navigator.userAgent
  const urlParams = new URLSearchParams(window.location.search)
  forceIOSMode.value = urlParams.get('testios') === 'true'

  isIOS.value = forceIOSMode.value || (/iPad|iPhone|iPod/.test(ua) && !window.MSStream)
  isAndroid.value = !forceIOSMode.value && /Android/.test(ua)
  isStandalone.value = window.matchMedia('(display-mode: standalone)').matches ||
                       window.navigator.standalone === true
  displayMode.value = window.matchMedia('(display-mode: standalone)').matches ? 'standalone' : 'browser'
  navigatorStandalone.value = window.navigator.standalone === true
}

function setupInstallPrompt() {
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt.value = e
    showInstallPrompt.value = true
  })
}

async function installApp() {
  if (!deferredPrompt.value) return

  deferredPrompt.value.prompt()
  const { outcome } = await deferredPrompt.value.userChoice

  if (outcome === 'accepted') {
    showInstallPrompt.value = false
  }
  deferredPrompt.value = null
}

async function requestNotificationPermission() {
  if (isIOS.value && !isHTTPS.value) {
    alert('ERRORE: iOS richiede una connessione HTTPS sicura per le notifiche.\n\nDevi accedere all\'app tramite https:// invece di http://')
    return false
  }

  if (isIOS.value && iOSVersionNumber.value > 0 && iOSVersionNumber.value < 16.4) {
    alert(`Le notifiche PWA richiedono iOS 16.4 o superiore.\n\nLa tua versione: iOS ${getIOSVersion()}\n\nAggiorna il tuo iPhone per usare questa funzionalità.`)
    return false
  }

  if (!('Notification' in window)) {
    alert('Le notifiche non sono supportate su questo dispositivo/browser')
    return false
  }

  if (isIOS.value && !isStandalone.value) {
    alert('Su iOS, le notifiche funzionano SOLO dopo aver installato l\'app sulla Home screen.\n\nSegui le istruzioni sopra per installarla.')
    return false
  }

  try {
    const permission = await Notification.requestPermission()
    notificationPermission.value = permission

    if (permission === 'granted') {
      new Notification('Permesso Accordato!', {
        body: 'Ora riceverai le notifiche in tempo reale',
        icon: '/pwa-192x192.png'
      })
    } else if (permission === 'denied') {
      alert('Hai negato il permesso per le notifiche.\n\nSe hai già negato il permesso in passato, Safari lo ricorda. Segui le istruzioni sotto per resettare.')
    }

    return permission === 'granted'
  } catch (error) {
    alert(`Errore: ${error.message}`)
    return false
  }
}

function showBrowserNotification(title, body) {
  if (notificationPermission.value === 'granted') {
    new Notification(title, {
      body,
      icon: '/pwa-192x192.png',
      badge: '/pwa-192x192.png'
    })
  }
}

async function registerDevice(socketId) {
  try {
    await axios.post(`${import.meta.env.VITE_API_URL}/devices/register`, {
      socket_id: socketId,
      device_info: deviceInfo.value
    })
  } catch (error) {

  }
}

function initPusher() {
  const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
  })

  pusher.connection.bind('connected', () => {
    isConnected.value = true
    socketId.value = pusher.connection.socket_id
    registerDevice(pusher.connection.socket_id)

    const channel = pusher.subscribe('notifications')

    channel.bind('notification.sent', (data) => {
      const notification = {
        id: Date.now(),
        title: data.title,
        description: data.description,
        timestamp: new Date().toLocaleTimeString()
      }

      notifications.value.unshift(notification)
      showBrowserNotification(data.title, data.description)
    })
  })

  pusher.connection.bind('disconnected', () => {
    isConnected.value = false
  })
}

onMounted(() => {
  detectDevice()
  setupInstallPrompt()
  initPusher()
})
</script>

<template>
  <div class="app">
    <header class="header">
      <h1>Pulse Notifications</h1>
      <div class="status" :class="{ connected: isConnected }">
        {{ isConnected ? 'Connesso' : 'Disconnesso' }}
      </div>
    </header>

    <main class="main">
      <section class="card install-section" v-if="!isStandalone">
        <h2>Installa App</h2>

        <div v-if="isIOS" class="ios-instructions">
          <p>Per installare l'app su iOS:</p>
          <ol>
            <li>Tocca il pulsante <strong>Condividi</strong> (icona quadrato con freccia)</li>
            <li>Scorri e seleziona <strong>"Aggiungi a Home"</strong></li>
            <li>Conferma toccando <strong>"Aggiungi"</strong></li>
          </ol>
          <div class="ios-icon-hint">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
              <polyline points="16 6 12 2 8 6"/>
              <line x1="12" y1="2" x2="12" y2="15"/>
            </svg>
          </div>
        </div>

        <div v-else-if="isAndroid && showInstallPrompt">
          <p>Installa l'app per una migliore esperienza:</p>
          <button @click="installApp" class="btn btn-primary">
            Installa App
          </button>
        </div>

        <div v-else-if="!isIOS && !isAndroid">
          <p v-if="showInstallPrompt">
            <button @click="installApp" class="btn btn-primary">
              Installa App
            </button>
          </p>
          <p v-else class="text-muted">
            L'app può essere installata dal menu del browser.
          </p>
        </div>
      </section>

      <section class="card">
        <h2>Permessi Notifiche</h2>
        <div class="permission-status">
          <span :class="['badge', notificationPermission]">
            {{ notificationPermission === 'granted' ? 'Permesso accordato' :
               notificationPermission === 'denied' ? 'Permesso negato' : 'Non richiesto' }}
          </span>
        </div>

        <div v-if="isIOS && !isHTTPS" class="https-error-warning">
          <p><strong>ERRORE CRITICO: HTTPS Richiesto</strong></p>
          <p>iOS richiede una connessione <strong>HTTPS sicura</strong> per le notifiche PWA.</p>
          <p>Stai usando: <code>{{ deviceInfo.protocol }}//{{ window.location.host }}</code></p>
          <p><strong>Soluzione:</strong></p>
          <ol>
            <li>Configura HTTPS sul server di sviluppo</li>
  ß          <li>Accedi tramite <code>https://192.168.1.10:5174</code> invece di http://</li>
            <li>Accetta il certificato self-signed in Safari</li>
            <li>Reinstalla l'app sulla Home screen</li>
          </ol>
          <p class="https-note">Senza HTTPS, Safari blocca <code>Notification.requestPermission()</code> sempre con "denied".</p>
        </div>

        <div v-if="isIOS && iOSVersionNumber > 0 && iOSVersionNumber < 16.4" class="ios-version-warning">
          <p><strong>Versione iOS Non Supportata</strong></p>
          <p>Le notifiche PWA richiedono <strong>iOS 16.4 o superiore</strong>.</p>
          <p>La tua versione: <strong>iOS {{ deviceInfo.iOSVersion }}</strong></p>
          <p><strong>Soluzione:</strong> Aggiorna il tuo iPhone a iOS 16.4+ in Impostazioni → Generali → Aggiornamento Software</p>
        </div>

        <div v-if="isIOS && !isStandalone && isHTTPS && (iOSVersionNumber === 0 || iOSVersionNumber >= 16.4)" class="ios-notification-warning">
          <p><strong>Importante per iOS</strong></p>
          <p>Le notifiche su iOS funzionano SOLO dopo aver installato l'app sulla Home screen (richiede iOS 16.4+).</p>
          <p><strong>Procedura:</strong></p>
          <ol>
            <li>Tocca il pulsante <strong>Condividi</strong> in Safari</li>
            <li>Seleziona <strong>"Aggiungi alla schermata Home"</strong></li>
            <li>Chiudi Safari completamente</li>
            <li>Apri l'app "Pulse" dalla Home screen</li>
            <li>Solo ora il pulsante "Richiedi permesso" funzionerà</li>
          </ol>
        </div>

        <button
          v-if="notificationPermission !== 'granted'"
          @click="requestNotificationPermission"
          class="btn btn-secondary"
          :disabled="notificationPermission === 'denied' || (isIOS && !isHTTPS) || (isIOS && iOSVersionNumber > 0 && iOSVersionNumber < 16.4) || (isIOS && !isStandalone)"
        >
          {{ notificationPermission === 'denied' ? 'Permesso negato nelle impostazioni' :
             (isIOS && !isHTTPS) ? 'HTTPS richiesto (vedi sopra)' :
             (isIOS && iOSVersionNumber > 0 && iOSVersionNumber < 16.4) ? 'iOS version troppo vecchia' :
             (isIOS && !isStandalone) ? 'Installa app prima (vedi sopra)' :
             'Richiedi permesso' }}
        </button>

        <div v-if="notificationPermission === 'denied'" class="permission-denied-help">
          <p><strong>Come riattivare i permessi:</strong></p>
          <div v-if="isIOS && isStandalone">
            <p class="ios-error-note">Hai negato il permesso. Safari sta tenendo in cache la tua scelta precedente.</p>
            <p><strong>Reset COMPLETO (unico modo che funziona):</strong></p>
            <ol>
              <li><strong>Elimina l'app</strong> dalla Home screen (tieni premuto l'icona → Rimuovi app)</li>
              <li><strong>Vai in Impostazioni iPhone</strong> → <strong>Safari</strong></li>
              <li>Tocca <strong>"Cancella dati siti web e cronologia"</strong> (in basso)
                <ul class="nested-list">
                  <li>Questo cancellerà cronologia, cookie e cache di Safari</li>
                  <li>Se non vuoi perdere tutto, vai in <strong>Avanzate → Dati dei siti web</strong> e prova a eliminare solo <code>192.168.1.10</code></li>
                </ul>
              </li>
              <li><strong>Riavvia l'iPhone</strong> (tieni premuto power + volume → Scorri per spegnere)
                <ul class="nested-list">
                  <li>Questo è importante per svuotare completamente la cache di Safari</li>
                </ul>
              </li>
              <li><strong>Riaccendi l'iPhone</strong></li>
              <li><strong>Apri Safari</strong> e vai su: <code>http://192.168.1.10:5173</code></li>
              <li><strong>Reinstalla l'app:</strong> Tocca Condividi → Aggiungi alla schermata Home</li>
              <li><strong>Apri l'app</strong> dalla Home screen</li>
              <li><strong>Tocca "Richiedi permesso"</strong> e questa volta scegli <strong>"Consenti"</strong></li>
            </ol>
            <p class="ios-settings-note">Il riavvio è fondamentale: Safari su iOS tiene in cache i permessi negati e solo un riavvio li resetta completamente.</p>
          </div>
          <div v-else-if="isIOS && !isStandalone">
            <p><strong>Da Safari:</strong></p>
            <ol>
              <li>Tocca <strong>aA</strong> nella barra degli indirizzi (in alto a sinistra)</li>
              <li>Tocca <strong>"Impostazioni sito web"</strong></li>
              <li>Trova <strong>Notifiche</strong> e cambia da "Nega" a <strong>"Consenti"</strong></li>
              <li>Ricarica questa pagina</li>
            </ol>
          </div>
          <div v-else>
            <ol>
              <li>Clicca sull'icona del lucchetto/informazioni nella barra degli indirizzi</li>
              <li>Trova le impostazioni delle notifiche</li>
              <li>Cambia da "Blocca" a "Consenti"</li>
              <li>Ricarica questa pagina</li>
            </ol>
          </div>
        </div>
      </section>

      <section class="card">
        <h2>Notifiche Ricevute</h2>
        <div v-if="notifications.length === 0" class="empty-state">
          <p>Nessuna notifica ricevuta.</p>
          <p class="text-muted">Le notifiche appariranno qui in tempo reale.</p>
        </div>
        <ul v-else class="notification-list">
          <li v-for="notification in notifications" :key="notification.id" class="notification-item">
            <div class="notification-header">
              <strong>{{ notification.title }}</strong>
              <span class="timestamp">{{ notification.timestamp }}</span>
            </div>
            <p>{{ notification.description }}</p>
          </li>
        </ul>
      </section>

      <section class="card debug-section">
        <h2>Info Debug</h2>
        <div class="debug-info">
          <p><strong>Socket ID:</strong> {{ socketId || 'N/A' }}</p>
          <p><strong>Platform:</strong> {{ deviceInfo.platform }}</p>
          <p><strong>Browser:</strong> {{ deviceInfo.browser }}</p>
          <p><strong>iOS:</strong> {{ isIOS ? 'Si' : 'No' }}</p>
          <p><strong>iOS Version:</strong> {{ deviceInfo.iOSVersion }} ({{ iOSVersionNumber }})</p>
          <p><strong>Android:</strong> {{ isAndroid ? 'Si' : 'No' }}</p>
          <p><strong>Standalone:</strong> {{ isStandalone ? 'Si' : 'No' }}</p>
          <p><strong>display-mode:</strong> {{ displayMode }}</p>
          <p><strong>navigator.standalone:</strong> {{ navigatorStandalone ? 'true' : 'false' }}</p>
          <p><strong>Protocol:</strong> {{ deviceInfo.protocol }}</p>
          <p><strong>HTTPS:</strong> {{ isHTTPS ? 'Si' : 'No' }}</p>
          <p><strong>Notification API:</strong> Disponibile</p>
          <p><strong>Notification.permission:</strong> {{ notificationPermission }}</p>
          <p v-if="forceIOSMode" class="test-mode-warning">
            <strong>Modalità Test iOS Attiva</strong>
          </p>
        </div>
        <div v-if="!forceIOSMode && !isIOS" class="test-hint">
          <p class="text-muted">
            Per testare la modalità iOS, aggiungi <code>?testios=true</code> all'URL
          </p>
        </div>
      </section>
    </main>
  </div>
</template>

<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  background-color: #f5f5f5;
  color: #333;
  line-height: 1.6;
}

.app {
  min-height: 100vh;
  max-width: 600px;
  margin: 0 auto;
  padding: 0 16px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 0;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 20px;
}

.header h1 {
  font-size: 1.5rem;
  color: #4f46e5;
}

.status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.875rem;
  background-color: #fecaca;
  color: #dc2626;
}

.status.connected {
  background-color: #bbf7d0;
  color: #16a34a;
}

.main {
  padding-bottom: 40px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card h2 {
  font-size: 1.125rem;
  margin-bottom: 16px;
  color: #1f2937;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #4f46e5;
  color: white;
}

.btn-primary:hover {
  background-color: #4338ca;
}

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.ios-instructions {
  background-color: #f0f9ff;
  padding: 16px;
  border-radius: 8px;
}

.ios-instructions ol {
  margin: 12px 0;
  padding-left: 20px;
}

.ios-instructions li {
  margin-bottom: 8px;
}

.ios-icon-hint {
  display: flex;
  justify-content: center;
  padding: 16px;
  color: #0284c7;
}

.badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.875rem;
}

.badge.granted {
  background-color: #bbf7d0;
  color: #16a34a;
}

.badge.denied {
  background-color: #fecaca;
  color: #dc2626;
}

.badge.default {
  background-color: #fef3c7;
  color: #d97706;
}

.permission-status {
  margin-bottom: 16px;
}

.empty-state {
  text-align: center;
  padding: 32px 0;
  color: #6b7280;
}

.text-muted {
  color: #9ca3af;
  font-size: 0.875rem;
}

.notification-list {
  list-style: none;
}

.notification-item {
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 12px;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.timestamp {
  font-size: 0.75rem;
  color: #9ca3af;
}

.debug-section {
  background-color: #f9fafb;
}

.debug-info p {
  font-size: 0.875rem;
  margin-bottom: 4px;
}

.test-mode-warning {
  color: #d97706;
  background-color: #fef3c7;
  padding: 8px;
  border-radius: 4px;
  margin-top: 8px;
}

.test-hint {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.test-hint code {
  background-color: #f3f4f6;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.813rem;
  font-family: 'Monaco', 'Courier New', monospace;
}

.ios-notification-warning {
  background-color: #fef3c7;
  border: 1px solid #fbbf24;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.ios-notification-warning p {
  margin-bottom: 8px;
  font-size: 0.875rem;
  color: #92400e;
}

.ios-notification-warning p:last-child {
  margin-bottom: 0;
}

.ios-notification-warning ol {
  margin: 8px 0 12px 0;
  padding-left: 20px;
  color: #92400e;
}

.ios-notification-warning li {
  margin-bottom: 6px;
  font-size: 0.875rem;
}

.permission-denied-help {
  margin-top: 16px;
  padding: 16px;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
}

.permission-denied-help p {
  margin-bottom: 12px;
  color: #991b1b;
  font-weight: 600;
}

.permission-denied-help ol {
  margin: 8px 0;
  padding-left: 20px;
  color: #7f1d1d;
}

.permission-denied-help li {
  margin-bottom: 8px;
  font-size: 0.875rem;
}

.ios-settings-note {
  background-color: #fefce8;
  border-left: 3px solid #eab308;
  padding: 8px 12px;
  margin: 12px 0;
  font-size: 0.875rem;
  color: #854d0e;
}

.ios-error-note {
  background-color: #fef2f2;
  border-left: 3px solid #ef4444;
  padding: 8px 12px;
  margin: 12px 0;
  font-size: 0.875rem;
  color: #991b1b;
  font-weight: 600;
}

.nested-list {
  list-style-type: circle;
  margin-top: 8px;
  padding-left: 20px;
}

.nested-list li {
  margin-bottom: 4px;
  font-size: 0.875rem;
}

.permission-denied-help code {
  background-color: #f3f4f6;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.813rem;
  font-family: 'Monaco', 'Courier New', monospace;
  color: #1f2937;
}

.https-error-warning {
  background-color: #fee;
  border: 2px solid #dc2626;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.https-error-warning p {
  margin-bottom: 12px;
  font-size: 0.875rem;
  color: #7f1d1d;
}

.https-error-warning p:first-child {
  font-size: 1rem;
  color: #991b1b;
}

.https-error-warning ol {
  margin: 8px 0 12px 0;
  padding-left: 20px;
  color: #7f1d1d;
}

.https-error-warning li {
  margin-bottom: 6px;
  font-size: 0.875rem;
}

.https-error-warning code {
  background-color: #fef2f2;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.813rem;
  font-family: 'Monaco', 'Courier New', monospace;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.https-note {
  background-color: #fefce8;
  border-left: 3px solid #dc2626;
  padding: 8px 12px;
  margin: 12px 0 0 0;
  font-size: 0.875rem;
  color: #854d0e;
  font-weight: 600;
}

.ios-version-warning {
  background-color: #fef3c7;
  border: 2px solid #f59e0b;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.ios-version-warning p {
  margin-bottom: 12px;
  font-size: 0.875rem;
  color: #92400e;
}

.ios-version-warning p:first-child {
  font-size: 1rem;
  color: #78350f;
}
</style>
