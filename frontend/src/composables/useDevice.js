import { ref, computed } from 'vue'

const isIOS = ref(false)
const isAndroid = ref(false)
const isStandalone = ref(false)
const forceIOSMode = ref(false)
const displayMode = ref('browser')
const navigatorStandalone = ref(false)
const isHTTPS = ref(window.location.protocol === 'https:')
const iOSVersionNumber = ref(0)
const showInstallPrompt = ref(false)
const deferredPrompt = ref(null)

export function useDevice() {
  function getIOSVersion() {
    const match = navigator.userAgent.match(/OS (\d+)_(\d+)_?(\d+)?/)
    if (match) {
      const major = parseInt(match[1])
      const minor = parseInt(match[2])
      iOSVersionNumber.value = major + (minor / 10)
      return `${match[1]}.${match[2]}${match[3] ? '.' + match[3] : ''}`
    }
    iOSVersionNumber.value = 0
    return 'Unknown'
  }

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
    const urlParams = new URLSearchParams(window.location.search)
    forceIOSMode.value = urlParams.get('testios') === 'true'

    isIOS.value = forceIOSMode.value || (/iPad|iPhone|iPod/.test(ua) && !window.MSStream)
    isAndroid.value = !forceIOSMode.value && /Android/.test(ua)
    isStandalone.value = window.matchMedia('(display-mode: standalone)').matches ||
                         window.navigator.standalone === true
    displayMode.value = window.matchMedia('(display-mode: standalone)').matches ? 'standalone' : 'browser'
    navigatorStandalone.value = window.navigator.standalone === true

    getIOSVersion()
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

  const deviceInfo = computed(() => ({
    platform: navigator.platform,
    userAgent: navigator.userAgent,
    browser: getBrowserName(),
    isIOS: isIOS.value,
    isAndroid: isAndroid.value,
    isStandalone: isStandalone.value,
    iOSVersion: getIOSVersion(),
    isHTTPS: isHTTPS.value,
    protocol: window.location.protocol
  }))

  return {
    isIOS,
    isAndroid,
    isStandalone,
    forceIOSMode,
    displayMode,
    navigatorStandalone,
    isHTTPS,
    iOSVersionNumber,
    showInstallPrompt,
    deferredPrompt,
    deviceInfo,
    getIOSVersion,
    getBrowserName,
    detectDevice,
    setupInstallPrompt,
    installApp
  }
}
