import { onBeforeUnmount, onMounted, ref } from 'vue';

// El navegador solo dispara 'beforeinstallprompt' si prevenimos su
// comportamiento default (que es no mostrar nada hasta que el usuario abra
// el menú del navegador); guardamos el evento para poder disparar el
// diálogo nativo nosotros mismos, desde un botón propio.
export function useInstallPrompt() {
    const deferredPrompt = ref(null);
    const canInstall = ref(false);

    // El evento 'beforeinstallprompt' no existe en iOS Safari (Apple no lo
    // soporta), así que ahí canInstall nunca pasa a true.
    const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    const isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;
    const showIosInstallHint = isIos && !isStandalone;

    function handleBeforeInstallPrompt(event) {
        event.preventDefault();
        deferredPrompt.value = event;
        canInstall.value = true;
    }

    function handleAppInstalled() {
        deferredPrompt.value = null;
        canInstall.value = false;
    }

    onMounted(() => {
        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.addEventListener('appinstalled', handleAppInstalled);
    });

    onBeforeUnmount(() => {
        window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.removeEventListener('appinstalled', handleAppInstalled);
    });

    async function promptInstall() {
        if (!deferredPrompt.value) {
            return;
        }

        deferredPrompt.value.prompt();
        await deferredPrompt.value.userChoice;

        // El evento guardado solo se puede usar una vez, sin importar si el
        // usuario aceptó o rechazó el diálogo.
        deferredPrompt.value = null;
        canInstall.value = false;
    }

    return {
        canInstall,
        promptInstall,
        showIosInstallHint,
    };
}
