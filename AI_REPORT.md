# AI Report

## Utilizzo
Sì

## Strumenti
Claude Code

## Contesto
L'utilizzo maggiore è stato per: documentazione, per rendere l'applicazione webapp (non mi capita generalmente di lavorare con pwa, è stato semplice dando il giusto contesto capire come svilupparla). Nonostante ancora manchino tutta una serie di implementazioni che non ho aggiunto proprio per la durata del test, per esempio salvare in memoria (db o localStorage) i messaggi, oppure ottimizzare per dispositivi Android (avendo solo un iPhone non ho potuto testare e non mi sono avvalso di emulatori), infatti quando poi ho avuto modo il giorno seguente di testare con un Android, ho visto che manca una guida per installare l'app. Sicuramente ho usato l'AI per svolgere i compiti che occupano più tempo come refactoring per dividere l'app Vue in piccoli componenti, inoltre ho delegato l'intera configurazione iniziale una volta scelti gli strumenti e le librerie da utilizzare. Per questioni di tempo è stato necessario usarla anche per automatizzare logiche di business.

## Verifica
Tutto quello che faccio generare viene committato solo previo controllo manuale mio. A volte il risultato è subito convincente (solo grazie all'IDE nel mio caso di Claude Code che può avere l'intero progetto come contesto, se necessario) e quindi mi basta dare una piccola pulizia se non dovesse seguire il prompt di sistema per evitare emoji e commenti inutili, per esempio. Il lavoro che svolgo avviene per micro task che puntualmente testo e verifico. Creo dei checkpoint che poi nei fatti diventeranno i commit. Quando i progetti sono più complessi e ho maggiore tempo di operatività tendo a seguire invece una rigorosa e manuale progettazione. Spesso mi capita anche di usare LLM solo per trovare falle in quello che propongo così da fixare ragionamenti incompleti. Ci sono anche situazioni in cui per esempio è necessaria una UI px perfect, in tal caso faccio tutto custom magari in scss.
In generale ritengo che a livello concorrenziale per tutta una nicchia di lavori, sia fondamentale adeguarsi ai nuovi tempi di mercato dettati dal vibecoding, ma con una preparazione qualificata.

### Esempio concreto
Nel componente `NotificationPermissions.vue` ho chiesto a Claude di generare la logica per disabilitare il pulsante delle notifiche su iOS in base a varie condizioni (HTTPS, versione iOS, modalità standalone). La prima versione generata era corretta ma eccessivamente verbosa:

```javascript
const isButtonDisabled = () => {
  // Check if permission was denied by user
  if (props.notificationPermission === 'denied') {
    return true
  }
  // iOS requires HTTPS for PWA notifications
  if (props.isIOS && !props.isHTTPS) {
    return true
  }
  // iOS 16.4+ required for Web Push
  if (props.isIOS && props.iOSVersionNumber > 0 && props.iOSVersionNumber < 16.4) {
    return true
  }
  // Must be installed as PWA on iOS
  if (props.isIOS && !props.isStandalone) {
    return true
  }
  return false
}
```

Ho ottimizzato manualmente in un'unica espressione booleana più concisa:

```javascript
const isButtonDisabled = () => {
  return props.notificationPermission === 'denied' ||
    (props.isIOS && !props.isHTTPS) ||
    (props.isIOS && props.iOSVersionNumber > 0 && props.iOSVersionNumber < 16.4) ||
    (props.isIOS && !props.isStandalone)
}
```
