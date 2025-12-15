<script setup>
import { Bell, Wifi, WifiOff } from 'lucide-vue-next'

defineProps({
  isConnected: {
    type: Boolean,
    required: true
  }
})
</script>

<template>
  <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8 sm:mb-10">
    <div class="flex items-center gap-3">
      <div class="p-2 bg-purple-500/20 rounded-xl backdrop-blur-sm border border-purple-400/30">
        <Bell :size="32" class="text-purple-300" />
      </div>
      <div>
        <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
          Pulse Notifications
        </h1>
        <p class="text-purple-300 text-sm mt-1">Sistema notifiche real-time</p>
      </div>
    </div>
    <div
      class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold backdrop-blur-md border shadow-lg transition-all"
      :class="isConnected
        ? 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30 shadow-emerald-500/20'
        : 'bg-red-500/20 text-red-200 border-red-400/30 shadow-red-500/20'"
    >
      <component :is="isConnected ? Wifi : WifiOff" :size="18" />
      <span>{{ isConnected ? 'Connesso' : 'Disconnesso' }}</span>
    </div>
  </header>
</template>
