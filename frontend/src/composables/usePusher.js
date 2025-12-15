import { ref } from 'vue'
import Pusher from 'pusher-js'
import axios from 'axios'

const isConnected = ref(false)
const socketId = ref(null)
const notifications = ref([])

export function usePusher() {
  async function registerDevice(id, deviceInfo) {
    try {
      await axios.post(`${import.meta.env.VITE_API_URL}/devices/register`, {
        socket_id: id,
        device_info: deviceInfo
      })
    } catch (error) {
      console.error('Errore registrazione device:', error.message)
    }
  }

  function initPusher(deviceInfo, onNotification) {
    const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
      forceTLS: true
    })

    pusher.connection.bind('connected', () => {
      isConnected.value = true
      socketId.value = pusher.connection.socket_id
      registerDevice(pusher.connection.socket_id, deviceInfo)

      const channel = pusher.subscribe('notifications')

      channel.bind('notification.sent', (data) => {
        const notification = {
          id: Date.now(),
          title: data.title,
          description: data.description,
          timestamp: new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' })
        }

        notifications.value.unshift(notification)

        if (onNotification) {
          onNotification(data.title, data.description)
        }
      })
    })

    pusher.connection.bind('disconnected', () => {
      isConnected.value = false
    })

    return pusher
  }

  return {
    isConnected,
    socketId,
    notifications,
    initPusher
  }
}
