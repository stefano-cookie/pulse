<script setup>
import { ref, onMounted } from 'vue'
import Toast from './components/Toast.vue'
import AppHeader from './components/AppHeader.vue'
import InstallSection from './components/InstallSection.vue'
import NotificationPermissions from './components/NotificationPermissions.vue'
import NotificationsList from './components/NotificationsList.vue'
import DeviceInfo from './components/DeviceInfo.vue'
import { useToast } from './composables/useToast'
import { useDevice } from './composables/useDevice'
import { usePusher } from './composables/usePusher'

const { toasts, success, error, warning } = useToast()
const {
  isIOS,
  isAndroid,
  isStandalone,
  forceIOSMode,
  isHTTPS,
  iOSVersionNumber,
  showInstallPrompt,
  deviceInfo,
  getIOSVersion,
  detectDevice,
  setupInstallPrompt,
  installApp
} = useDevice()
const { isConnected, notifications, initPusher } = usePusher()

const notificationPermission = ref('Notification' in window ? Notification.permission : 'default')

function showBrowserNotification(title, body) {
  if (notificationPermission.value === 'granted') {
    new Notification(title, {
      body,
      icon: '/pwa-192x192.png',
      badge: '/pwa-192x192.png'
    })
  }
}

async function requestNotificationPermission() {
  if (isIOS.value && !isHTTPS.value) {
    error(
      'Devi accedere all\'app tramite https:// invece di http://',
      'ERRORE: HTTPS Richiesto per iOS'
    )
    return false
  }

  if (isIOS.value && iOSVersionNumber.value > 0 && iOSVersionNumber.value < 16.4) {
    warning(
      `La tua versione: iOS ${getIOSVersion()}. Aggiorna il tuo iPhone per usare questa funzionalità.`,
      'iOS 16.4+ Richiesto'
    )
    return false
  }

  if (!('Notification' in window)) {
    error('Le notifiche non sono supportate su questo dispositivo/browser', 'Notifiche Non Supportate')
    return false
  }

  if (isIOS.value && !isStandalone.value) {
    warning(
      'Su iOS, le notifiche funzionano SOLO dopo aver installato l\'app sulla Home screen. Segui le istruzioni sopra.',
      'Installazione Richiesta'
    )
    return false
  }

  try {
    const permission = await Notification.requestPermission()
    notificationPermission.value = permission

    if (permission === 'granted') {
      success('Ora riceverai le notifiche in tempo reale!', 'Permesso Accordato!')
      new Notification('Permesso Accordato!', {
        body: 'Ora riceverai le notifiche in tempo reale',
        icon: '/pwa-192x192.png'
      })
    } else if (permission === 'denied') {
      warning(
        'Se hai già negato il permesso in passato, Safari lo ricorda. Segui le istruzioni sotto per resettare.',
        'Permesso Negato'
      )
    }

    return permission === 'granted'
  } catch (err) {
    error(err.message, 'Errore Permessi')
    return false
  }
}

onMounted(() => {
  detectDevice()
  setupInstallPrompt()
  initPusher(deviceInfo.value, showBrowserNotification)
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <Toast
      v-for="(toast, index) in toasts"
      :key="toast.id"
      :show="toast.show"
      :type="toast.type"
      :title="toast.title"
      :message="toast.message"
      :duration="toast.duration"
      :index="index"
      @close="() => toasts.splice(toasts.indexOf(toast), 1)"
    />

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <AppHeader :is-connected="isConnected" />

      <main class="space-y-6 pb-10">
        <InstallSection
          v-if="!isStandalone"
          :is-i-o-s="isIOS"
          :is-android="isAndroid"
          :show-install-prompt="showInstallPrompt"
          @install="installApp"
        />

        <NotificationPermissions
          :notification-permission="notificationPermission"
          :is-i-o-s="isIOS"
          :is-h-t-t-p-s="isHTTPS"
          :is-standalone="isStandalone"
          :i-o-s-version-number="iOSVersionNumber"
          :device-info="deviceInfo"
          @request-permission="requestNotificationPermission"
        />

        <NotificationsList :notifications="notifications" />

        <DeviceInfo
          :is-i-o-s="isIOS"
          :is-android="isAndroid"
          :is-standalone="isStandalone"
          :is-h-t-t-p-s="isHTTPS"
          :force-i-o-s-mode="forceIOSMode"
          :notification-permission="notificationPermission"
          :device-info="deviceInfo"
        />
      </main>
    </div>
  </div>
</template>

<style>
@reference "tailwindcss";

.alert-box {
  @apply rounded-2xl p-5 mb-4 border backdrop-blur-sm overflow-hidden;
}

.alert-error {
  @apply bg-gradient-to-br from-red-500/10 to-rose-600/10 border-red-500/30;
}

.alert-warning {
  @apply bg-gradient-to-br from-amber-500/10 to-orange-500/10 border-amber-500/30;
}

.alert-info {
  @apply bg-gradient-to-br from-blue-500/10 to-indigo-500/10 border-blue-500/30;
}

.alert-header {
  @apply flex items-center gap-3 mb-4;
}

.alert-icon-wrapper {
  @apply p-2 rounded-xl;
}

.alert-icon-error {
  @apply bg-red-500/20 text-red-400;
}

.alert-icon-warning {
  @apply bg-amber-500/20 text-amber-400;
}

.alert-icon-info {
  @apply bg-blue-500/20 text-blue-400;
}

.alert-title {
  @apply text-white font-bold text-base;
}

.alert-body {
  @apply text-slate-300 text-sm leading-relaxed;
}

.alert-body strong {
  @apply text-white;
}

.alert-section {
  @apply bg-slate-900/40 rounded-xl p-4 mt-3;
}

.alert-section-title {
  @apply text-white font-semibold mb-2;
}

.alert-list {
  @apply list-decimal list-inside space-y-2 text-slate-300;
}

.alert-list li {
  @apply leading-relaxed;
}

.code-tag {
  @apply px-2 py-0.5 rounded-md text-xs font-mono border;
}

.code-error {
  @apply bg-red-500/20 text-red-300 border-red-500/30;
}

.code-warning {
  @apply bg-amber-500/20 text-amber-300 border-amber-500/30;
}

.alert-note {
  @apply flex items-start gap-2 p-3 rounded-xl text-sm font-medium;
}

.alert-note-warning {
  @apply bg-amber-500/20 text-amber-300 border border-amber-500/30;
}

.alert-note-error {
  @apply bg-red-500/20 text-red-300 border border-red-500/30;
}
</style>
