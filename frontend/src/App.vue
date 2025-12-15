<script setup>
import { ref, onMounted, computed } from 'vue'
import Pusher from 'pusher-js'
import axios from 'axios'

const notifications = ref([])
const isConnected = ref(false)
const socketId = ref(null)
const notificationPermission = ref(Notification.permission)
const showInstallPrompt = ref(false)
const isIOS = ref(false)
const isAndroid = ref(false)
const isStandalone = ref(false)
const deferredPrompt = ref(null)

const deviceInfo = computed(() => ({
  platform: navigator.platform,
  userAgent: navigator.userAgent,
  browser: getBrowserName(),
  isIOS: isIOS.value,
  isAndroid: isAndroid.value
}))

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
  isIOS.value = /iPad|iPhone|iPod/.test(ua) && !window.MSStream
  isAndroid.value = /Android/.test(ua)
  isStandalone.value = window.matchMedia('(display-mode: standalone)').matches ||
                       window.navigator.standalone === true
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
  if ('Notification' in window) {
    const permission = await Notification.requestPermission()
    notificationPermission.value = permission
    return permission === 'granted'
  }
  return false
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
    // Silent fail - device registration is not critical for app functionality
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
        <button
          v-if="notificationPermission !== 'granted'"
          @click="requestNotificationPermission"
          class="btn btn-secondary"
          :disabled="notificationPermission === 'denied'"
        >
          {{ notificationPermission === 'denied' ? 'Permesso negato nelle impostazioni' : 'Richiedi permesso' }}
        </button>
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
          <p><strong>Android:</strong> {{ isAndroid ? 'Si' : 'No' }}</p>
          <p><strong>Standalone:</strong> {{ isStandalone ? 'Si' : 'No' }}</p>
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
</style>
