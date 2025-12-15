import { ref } from 'vue'

const toasts = ref([])
let toastId = 0

export function useToast() {
  const show = (options) => {
    const id = ++toastId
    const toast = {
      id,
      show: true,
      type: options.type || 'info',
      title: options.title || '',
      message: options.message || '',
      duration: options.duration !== undefined ? options.duration : 5000
    }

    toasts.value.push(toast)

    return id
  }

  const close = (id) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      toasts.value[index].show = false
      setTimeout(() => {
        toasts.value.splice(index, 1)
      }, 300)
    }
  }

  const success = (message, title = '') => {
    return show({ type: 'success', message, title })
  }

  const error = (message, title = '') => {
    return show({ type: 'error', message, title })
  }

  const warning = (message, title = '') => {
    return show({ type: 'warning', message, title })
  }

  const info = (message, title = '') => {
    return show({ type: 'info', message, title })
  }

  return {
    toasts,
    show,
    close,
    success,
    error,
    warning,
    info
  }
}
