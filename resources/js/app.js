import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

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
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        const page = pages[`./Pages/${name}.vue`];

        if (name.startsWith("Guest/")) {
            page.default.layout = page.default.layout || GuestLayout;
        } else {
            page.default.layout = page.default.layout || AppLayout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .mount(el);
    },
});
