<script setup>
import { ref, watch, computed, onUnmounted } from 'vue'
import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    required: true
  },
  duration: {
    type: Number,
    default: 5000
  },
  index: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['close'])

const visible = ref(false)
const progress = ref(100)
let timeoutId = null
let progressInterval = null

const topOffset = computed(() => {
  return 20 + (props.index * 90)
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    visible.value = true
    progress.value = 100

    if (props.duration > 0) {
      clearTimeout(timeoutId)
      clearInterval(progressInterval)

      const startTime = Date.now()
      progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime
        progress.value = Math.max(0, 100 - (elapsed / props.duration) * 100)
      }, 16)

      timeoutId = setTimeout(() => {
        close()
      }, props.duration)
    }
  }
})

onUnmounted(() => {
  clearTimeout(timeoutId)
  clearInterval(progressInterval)
})

function close() {
  clearInterval(progressInterval)
  visible.value = false
  setTimeout(() => {
    emit('close')
  }, 400)
}

const iconComponent = {
  success: CheckCircle,
  error: XCircle,
  warning: AlertTriangle,
  info: Info
}

const colors = {
  success: {
    gradient: 'from-emerald-500/95 to-green-600/95',
    icon: 'text-white',
    text: 'text-white',
    progressBg: 'bg-white/20',
    progress: 'bg-white/80',
    glow: 'shadow-emerald-500/30'
  },
  error: {
    gradient: 'from-red-500/95 to-rose-600/95',
    icon: 'text-white',
    text: 'text-white',
    progressBg: 'bg-white/20',
    progress: 'bg-white/80',
    glow: 'shadow-red-500/30'
  },
  warning: {
    gradient: 'from-amber-500/95 to-orange-500/95',
    icon: 'text-white',
    text: 'text-white',
    progressBg: 'bg-white/20',
    progress: 'bg-white/80',
    glow: 'shadow-amber-500/30'
  },
  info: {
    gradient: 'from-blue-500/95 to-indigo-600/95',
    icon: 'text-white',
    text: 'text-white',
    progressBg: 'bg-white/20',
    progress: 'bg-white/80',
    glow: 'shadow-blue-500/30'
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="toast">
      <div
        v-if="visible"
        class="toast-container"
        :class="[colors[type].glow]"
        :style="{ top: topOffset + 'px' }"
        role="alert"
        aria-live="polite"
      >
        <div
          class="toast-bg"
          :class="['bg-gradient-to-r', colors[type].gradient]"
        />

        <div class="toast-content">
          <div class="toast-icon-wrapper">
            <component
              :is="iconComponent[type]"
              :size="22"
              :class="colors[type].icon"
              stroke-width="2.5"
            />
          </div>

          <div class="toast-text">
            <h4 v-if="title" class="toast-title" :class="colors[type].text">
              {{ title }}
            </h4>
            <p class="toast-message" :class="[colors[type].text, title ? 'opacity-90' : '']">
              {{ message }}
            </p>
          </div>

          <button
            @click="close"
            class="toast-close"
            aria-label="Chiudi notifica"
          >
            <X :size="18" stroke-width="2.5" />
          </button>
        </div>

        <div v-if="duration > 0" class="toast-progress-container" :class="colors[type].progressBg">
          <div
            class="toast-progress"
            :class="colors[type].progress"
            :style="{ width: progress + '%' }"
          />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
@import "tailwindcss";

.toast-container {
  @apply fixed right-5 z-[9999] min-w-[340px] max-w-md rounded-2xl overflow-hidden shadow-2xl;
  backdrop-filter: blur(12px);
}

@media (max-width: 640px) {
  .toast-container {
    @apply left-4 right-4 min-w-0;
  }
}

.toast-bg {
  @apply absolute inset-0;
}

.toast-content {
  @apply relative flex items-start gap-3 p-4;
}

.toast-icon-wrapper {
  @apply flex-shrink-0 p-1.5 bg-white/20 rounded-xl backdrop-blur-sm;
}

.toast-text {
  @apply flex-1 min-w-0 py-0.5;
}

.toast-title {
  @apply font-bold text-[0.95rem] mb-0.5 leading-tight tracking-tight;
}

.toast-message {
  @apply text-sm leading-snug font-medium;
}

.toast-close {
  @apply flex-shrink-0 text-white/70 bg-transparent border-0 cursor-pointer p-2 -mr-1 -mt-1 rounded-xl transition-all duration-200;
  @apply hover:text-white hover:bg-white/20;
}

.toast-progress-container {
  @apply h-1 w-full;
}

.toast-progress {
  @apply h-full transition-[width] duration-100 ease-linear;
}

/* Animazioni */
.toast-enter-active {
  animation: toast-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-leave-active {
  animation: toast-out 0.4s cubic-bezier(0.36, 0, 0.66, -0.56);
}

@keyframes toast-in {
  0% {
    opacity: 0;
    transform: translateX(120%) scale(0.8);
  }
  100% {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
}

@keyframes toast-out {
  0% {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
  100% {
    opacity: 0;
    transform: translateX(120%) scale(0.8);
  }
}
</style>
