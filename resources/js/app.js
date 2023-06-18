import "./bootstrap";
import { createApp } from "vue";
import app from "./front-end/app.vue";
import store from "./util/store.js";
import router from "./router/index.js/";

router.beforeEach(async (to, from, next) => {
    const signature = store.state.signature;
    const entity = store.state.entity;
    const route = store.state.route;

    if (signature && entity && route) {
        store.dispatch("startTokenExpirationTimer", {
            signature,
            entity,
            route,
        });
    }
    next();
});

createApp(app).use(router, store).mount("#app");
