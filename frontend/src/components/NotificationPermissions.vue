<script setup>
import { Bell, CheckCircle, XCircle, AlertTriangle, Info } from 'lucide-vue-next'

const props = defineProps({
  notificationPermission: {
    type: String,
    required: true
  },
  isIOS: {
    type: Boolean,
    required: true
  },
  isHTTPS: {
    type: Boolean,
    required: true
  },
  isStandalone: {
    type: Boolean,
    required: true
  },
  iOSVersionNumber: {
    type: Number,
    required: true
  },
  deviceInfo: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['requestPermission'])

const isButtonDisabled = () => {
  return props.notificationPermission === 'denied' ||
    (props.isIOS && !props.isHTTPS) ||
    (props.isIOS && props.iOSVersionNumber > 0 && props.iOSVersionNumber < 16.4) ||
    (props.isIOS && !props.isStandalone)
}

const getButtonText = () => {
  if (props.notificationPermission === 'denied') return 'Permesso negato nelle impostazioni'
  if (props.isIOS && !props.isHTTPS) return 'HTTPS richiesto (vedi sopra)'
  if (props.isIOS && props.iOSVersionNumber > 0 && props.iOSVersionNumber < 16.4) return 'iOS version troppo vecchia'
  if (props.isIOS && !props.isStandalone) return 'Installa app prima (vedi sopra)'
  return 'Richiedi permesso'
}
</script>

<template>
  <section class="bg-slate-800/50 backdrop-blur-md rounded-2xl p-6 sm:p-8 shadow-xl border border-slate-700/50 hover:border-purple-500/50 transition-all">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-purple-500/20 rounded-lg">
        <Bell :size="24" class="text-purple-300" />
      </div>
      <h2 class="text-xl sm:text-2xl font-bold text-white">Permessi Notifiche</h2>
    </div>

    <div class="mb-6">
      <span
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border shadow-lg transition-all"
        :class="{
          'bg-emerald-500/20 text-emerald-200 border-emerald-400/30': notificationPermission === 'granted',
          'bg-red-500/20 text-red-200 border-red-400/30': notificationPermission === 'denied',
          'bg-amber-500/20 text-amber-200 border-amber-400/30': notificationPermission === 'default'
        }"
      >
        <component
          :is="notificationPermission === 'granted' ? CheckCircle :
              notificationPermission === 'denied' ? XCircle : AlertTriangle"
          :size="18"
        />
        <span>
          {{ notificationPermission === 'granted' ? 'Permesso accordato' :
             notificationPermission === 'denied' ? 'Permesso negato' : 'Non richiesto' }}
        </span>
      </span>
    </div>

    <div v-if="isIOS && !isHTTPS" class="alert-box alert-error">
      <div class="alert-header">
        <div class="alert-icon-wrapper alert-icon-error">
          <XCircle :size="20" />
        </div>
        <h3 class="alert-title">HTTPS Richiesto</h3>
      </div>
      <div class="alert-body">
        <p class="mb-3">iOS richiede una connessione <strong>HTTPS sicura</strong> per le notifiche PWA.</p>
        <p class="mb-3">Stai usando: <code class="code-tag code-error">{{ deviceInfo.protocol }}//{{ window.location.host }}</code></p>
        <div class="alert-section">
          <p class="alert-section-title">Soluzione:</p>
          <ol class="alert-list">
            <li>Configura HTTPS sul server di sviluppo</li>
            <li>Accedi tramite <code class="code-tag code-error">https://192.168.1.10:5174</code></li>
            <li>Accetta il certificato self-signed in Safari</li>
            <li>Reinstalla l'app sulla Home screen</li>
          </ol>
        </div>
        <div class="alert-note alert-note-warning">
          <AlertTriangle :size="16" class="flex-shrink-0" />
          <span>Senza HTTPS, Safari blocca sempre le notifiche con "denied".</span>
        </div>
      </div>
    </div>

    <div v-if="isIOS && iOSVersionNumber > 0 && iOSVersionNumber < 16.4" class="alert-box alert-warning">
      <div class="alert-header">
        <div class="alert-icon-wrapper alert-icon-warning">
          <AlertTriangle :size="20" />
        </div>
        <h3 class="alert-title">Versione iOS Non Supportata</h3>
      </div>
      <div class="alert-body">
        <p class="mb-3">Le notifiche PWA richiedono <strong>iOS 16.4 o superiore</strong>.</p>
        <p class="mb-3">La tua versione: <code class="code-tag code-warning">iOS {{ deviceInfo.iOSVersion }}</code></p>
        <div class="alert-section">
          <p class="alert-section-title">Soluzione:</p>
          <p>Aggiorna il tuo iPhone in <strong>Impostazioni → Generali → Aggiornamento Software</strong></p>
        </div>
      </div>
    </div>

    <div v-if="isIOS && !isStandalone && isHTTPS && (iOSVersionNumber === 0 || iOSVersionNumber >= 16.4)" class="alert-box alert-info">
      <div class="alert-header">
        <div class="alert-icon-wrapper alert-icon-info">
          <Info :size="20" />
        </div>
        <h3 class="alert-title">Installazione Richiesta</h3>
      </div>
      <div class="alert-body">
        <p class="mb-3">Le notifiche su iOS funzionano <strong>solo dopo aver installato l'app</strong> sulla Home screen.</p>
        <div class="alert-section">
          <p class="alert-section-title">Procedura:</p>
          <ol class="alert-list">
            <li>Tocca il pulsante <strong>Condividi</strong> in Safari</li>
            <li>Seleziona <strong>"Aggiungi alla schermata Home"</strong></li>
            <li>Chiudi Safari completamente</li>
            <li>Apri l'app dalla Home screen</li>
            <li>Tocca "Richiedi permesso"</li>
          </ol>
        </div>
      </div>
    </div>

    <button
      v-if="notificationPermission !== 'granted'"
      @click="emit('requestPermission')"
      class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:from-slate-600 disabled:to-slate-700 transition-all"
      :disabled="isButtonDisabled()"
    >
      <Bell :size="20" />
      <span>{{ getButtonText() }}</span>
    </button>

    <div v-if="notificationPermission === 'denied'" class="alert-box alert-error mt-4">
      <div class="alert-header">
        <div class="alert-icon-wrapper alert-icon-error">
          <XCircle :size="20" />
        </div>
        <h3 class="alert-title">Come Riattivare i Permessi</h3>
      </div>
      <div class="alert-body">
        <div v-if="isIOS && isStandalone">
          <div class="alert-note alert-note-error mb-4">
            <XCircle :size="16" class="flex-shrink-0" />
            <span>Hai negato il permesso. Safari tiene in cache questa scelta.</span>
          </div>
          <div class="alert-section">
            <p class="alert-section-title">Reset completo:</p>
            <ol class="alert-list">
              <li><strong>Elimina l'app</strong> dalla Home screen</li>
              <li>Vai in <strong>Impostazioni → Safari</strong></li>
              <li>Tocca <strong>"Cancella dati siti web e cronologia"</strong></li>
              <li><strong>Riavvia l'iPhone</strong></li>
              <li>Apri Safari e torna su questo sito</li>
              <li><strong>Reinstalla l'app</strong> dalla Home screen</li>
              <li>Apri l'app e tocca <strong>"Richiedi permesso"</strong></li>
            </ol>
          </div>
          <div class="alert-note alert-note-warning mt-4">
            <AlertTriangle :size="16" class="flex-shrink-0" />
            <span>Il riavvio è fondamentale per svuotare la cache di Safari.</span>
          </div>
        </div>

        <div v-else-if="isIOS && !isStandalone">
          <div class="alert-section">
            <p class="alert-section-title">Da Safari:</p>
            <ol class="alert-list">
              <li>Tocca <strong>aA</strong> nella barra degli indirizzi</li>
              <li>Tocca <strong>"Impostazioni sito web"</strong></li>
              <li>Cambia Notifiche da "Nega" a <strong>"Consenti"</strong></li>
              <li>Ricarica questa pagina</li>
            </ol>
          </div>
        </div>

        <div v-else>
          <div class="alert-section">
            <ol class="alert-list">
              <li>Clicca sull'icona del <strong>lucchetto</strong> nella barra indirizzi</li>
              <li>Trova le impostazioni delle notifiche</li>
              <li>Cambia da "Blocca" a <strong>"Consenti"</strong></li>
              <li>Ricarica questa pagina</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
