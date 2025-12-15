<script setup>
import { Smartphone, AlertTriangle } from 'lucide-vue-next'

defineProps({
  isIOS: {
    type: Boolean,
    required: true
  },
  isAndroid: {
    type: Boolean,
    required: true
  },
  isStandalone: {
    type: Boolean,
    required: true
  },
  isHTTPS: {
    type: Boolean,
    required: true
  },
  forceIOSMode: {
    type: Boolean,
    required: true
  },
  notificationPermission: {
    type: String,
    required: true
  },
  deviceInfo: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <section class="bg-slate-800/30 backdrop-blur-md rounded-2xl p-6 sm:p-8 shadow-xl border border-slate-700/30">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-slate-500/20 rounded-lg">
        <Smartphone :size="24" class="text-slate-300" />
      </div>
      <h2 class="text-xl sm:text-2xl font-bold text-white">Info</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
      <div class="flex justify-between items-center p-3 bg-slate-900/40 rounded-lg border border-slate-700/50">
        <span class="text-slate-400 font-medium">Dispositivo:</span>
        <span class="text-slate-200">{{ isIOS ? 'iOS' : isAndroid ? 'Android' : 'Desktop' }}</span>
      </div>
      <div class="flex justify-between items-center p-3 bg-slate-900/40 rounded-lg border border-slate-700/50">
        <span class="text-slate-400 font-medium">Browser:</span>
        <span class="text-slate-200">{{ deviceInfo.browser }}</span>
      </div>
      <div class="flex justify-between items-center p-3 bg-slate-900/40 rounded-lg border border-slate-700/50">
        <span class="text-slate-400 font-medium">Modalità:</span>
        <span class="text-slate-200 font-semibold" :class="isStandalone ? 'text-purple-400' : ''">{{ isStandalone ? 'App installata' : 'Browser' }}</span>
      </div>
      <div class="flex justify-between items-center p-3 bg-slate-900/40 rounded-lg border border-slate-700/50">
        <span class="text-slate-400 font-medium">HTTPS:</span>
        <span class="text-slate-200 font-semibold" :class="isHTTPS ? 'text-emerald-400' : 'text-red-400'">{{ isHTTPS ? 'Si' : 'No' }}</span>
      </div>
      <div class="flex justify-between items-center p-3 bg-slate-900/40 rounded-lg border border-slate-700/50 sm:col-span-2">
        <span class="text-slate-400 font-medium">Notifiche:</span>
        <span class="text-slate-200 font-semibold" :class="{
          'text-emerald-400': notificationPermission === 'granted',
          'text-red-400': notificationPermission === 'denied',
          'text-amber-400': notificationPermission === 'default'
        }">{{ notificationPermission === 'granted' ? 'Abilitate' : notificationPermission === 'denied' ? 'Bloccate' : 'Non richieste' }}</span>
      </div>
    </div>

    <div v-if="forceIOSMode" class="mt-4 bg-yellow-100 border border-yellow-400 rounded-lg p-3">
      <p class="text-yellow-900 font-bold text-sm flex items-center gap-2">
        <AlertTriangle :size="16" />
        Modalità Test iOS Attiva
      </p>
    </div>
  </section>
</template>
