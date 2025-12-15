<script setup>
import { Bell } from 'lucide-vue-next'

defineProps({
  notifications: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <section class="bg-slate-800/50 backdrop-blur-md rounded-2xl p-6 sm:p-8 shadow-xl border border-slate-700/50 hover:border-purple-500/50 transition-all">
    <div class="flex items-center justify-between gap-3 mb-6">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-purple-500/20 rounded-lg">
          <Bell :size="24" class="text-purple-300" />
        </div>
        <h2 class="text-xl sm:text-2xl font-bold text-white">Notifiche Ricevute</h2>
      </div>
      <span v-if="notifications.length > 0" class="px-3 py-1.5 bg-purple-500/20 text-purple-200 rounded-lg text-sm font-semibold border border-purple-400/30">
        {{ notifications.length }}
      </span>
    </div>

    <div v-if="notifications.length === 0" class="text-center py-16">
      <div class="mb-4 flex justify-center">
        <div class="p-4 bg-slate-700/50 rounded-2xl">
          <Bell :size="48" class="text-slate-500" />
        </div>
      </div>
      <p class="text-slate-300 font-semibold mb-2">Nessuna notifica ricevuta</p>
      <p class="text-slate-400 text-sm">Le notifiche appariranno qui in tempo reale</p>
    </div>

    <ul v-else class="space-y-3">
      <li
        v-for="notification in notifications"
        :key="notification.id"
        class="p-5 bg-gradient-to-br from-slate-700/50 to-slate-800/50 border border-slate-600/50 rounded-xl hover:border-purple-500/50 hover:shadow-lg transition-all"
      >
        <div class="flex justify-between items-start mb-2">
          <strong class="text-white font-semibold text-base">{{ notification.title }}</strong>
          <span class="text-xs text-slate-400 flex-shrink-0 ml-3 px-2 py-1 bg-slate-900/50 rounded">{{ notification.timestamp }}</span>
        </div>
        <p class="text-slate-300 text-sm leading-relaxed">{{ notification.description }}</p>
      </li>
    </ul>
  </section>
</template>
