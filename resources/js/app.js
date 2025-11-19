import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import axios from "axios";

// Configurar axios com CSRF token
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Configurar Laravel Echo com Reverb
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ["ws"],
    authEndpoint: "/broadcasting/auth",
    auth: {
        headers: {
            "X-CSRF-TOKEN":
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || "",
        },
    },
});

// Vuetify
import "@mdi/font/css/materialdesignicons.css";
import "vuetify/styles";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import AppLayout from "./Layouts/AppLayout.vue";
import GuestLayout from "./Layouts/GuestLayout.vue";

const vuetify = createVuetify({
    components,
    directives,
});

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./pages/**/*.vue", { eager: true });
        const page = pages[`./pages/${name}.vue`];

        if (name.startsWith("Guest/")) {
            page.default.layout = page.default.layout || GuestLayout;
        } else {
            page.default.layout = page.default.layout || AppLayout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Disponibilizar SweetAlert2 globalmente
        app.config.globalProperties.$swal = Swal;

        app.use(plugin).use(vuetify).mount(el);
    },
});
