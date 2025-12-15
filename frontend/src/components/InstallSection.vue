<script setup>
import { Download, Share2, Smartphone, Monitor, CheckCircle } from 'lucide-vue-next'

defineProps({
  isIOS: {
    type: Boolean,
    required: true
  },
  isAndroid: {
    type: Boolean,
    required: true
  },
  showInstallPrompt: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['install'])
</script>

<template>
  <section class="bg-slate-800/50 backdrop-blur-md rounded-2xl p-6 sm:p-8 shadow-xl border border-slate-700/50 hover:border-purple-500/50 transition-all">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-purple-500/20 rounded-lg">
        <Download :size="24" class="text-purple-300" />
      </div>
      <h2 class="text-xl sm:text-2xl font-bold text-white">Installa App</h2>
    </div>

    <div v-if="isIOS" class="bg-gradient-to-br from-blue-900/30 to-blue-800/30 rounded-xl p-5 border border-blue-500/20">
      <div class="flex items-center gap-3 mb-4">
        <div class="p-1.5 bg-blue-500/20 rounded-lg">
          <Smartphone :size="20" class="text-blue-300" />
        </div>
        <p class="font-semibold text-white text-base">Per installare l'app su iOS:</p>
      </div>
      <ol class="space-y-3 ml-1">
        <li class="flex items-start gap-3 text-sm text-blue-100">
          <div class="p-1 bg-blue-500/20 rounded mt-0.5">
            <Share2 :size="14" class="flex-shrink-0 text-blue-300" />
          </div>
          <span>Tocca il pulsante <strong class="text-white">Condividi</strong> (icona quadrato con freccia)</span>
        </li>
        <li class="flex items-start gap-3 text-sm text-blue-100">
          <span class="w-6 h-6 flex items-center justify-center bg-blue-500/20 rounded text-blue-300 mt-0.5 flex-shrink-0">•</span>
          <span>Scorri e seleziona <strong class="text-white">"Aggiungi a Home"</strong></span>
        </li>
        <li class="flex items-start gap-3 text-sm text-blue-100">
          <div class="p-1 bg-emerald-500/20 rounded mt-0.5">
            <CheckCircle :size="14" class="flex-shrink-0 text-emerald-300" />
          </div>
          <span>Conferma toccando <strong class="text-white">"Aggiungi"</strong></span>
        </li>
      </ol>
      <div class="flex justify-center mt-5 text-blue-300">
        <Share2 :size="48" />
      </div>
    </div>

    <div v-else-if="isAndroid && showInstallPrompt">
      <p class="flex items-center gap-2 text-purple-200 mb-4">
        <Smartphone :size="20" />
        Installa l'app per una migliore esperienza
      </p>
      <button
        @click="emit('install')"
        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all"
      >
        <Download :size="20" />
        <span>Installa App</span>
      </button>
    </div>

    <div v-else-if="!isIOS && !isAndroid">
      <button
        v-if="showInstallPrompt"
        @click="emit('install')"
        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all"
      >
        <Download :size="20" />
        <span>Installa App</span>
      </button>
      <p v-else class="flex items-center gap-2 text-slate-400 text-sm">
        <Monitor :size="18" />
        L'app può essere installata dal menu del browser.
      </p>
    </div>
  </section>
</template>
