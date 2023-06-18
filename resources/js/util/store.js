import { createStore } from "vuex";
import { logout } from "./authUtils";

let logoutTimeout = null;
let intervalTime = null;

const store = createStore({
    state: {
        signature: null,
        entity: null,
        route: null,
    },
    mutations: {
        setSignature(state, signature) {
            state.signature = signature;
        },
        clearSignature(state) {
            state.signature = null;
        },
        setEntity(state, entity) {
            state.entity = entity;
        },
        clearEntity(state) {
            state.entity = null;
        },
        setRoute(state, route) {
            state.route = route;
        },
        clearRoute(state) {
            state.route = null;
        },
    },
    actions: {
        startTokenExpirationTimer({ commit, dispatch, state }) {
            if (logoutTimeout) {
                clearTimeout(logoutTimeout);
                clearInterval(intervalTime);
            }

            logoutTimeout = setTimeout(() => {
                logout(state.entity, state.signature, state.route);
                dispatch('clearTokenExpirationTimer');
                alert("Token expired, please login again.");
                
                commit('clearSignature'); commit('clearEntity'); commit('clearRoute');
            }, 10000);

            intervalTime = setInterval(() => {
                console.log("pls berhasil");
            }, 1000);
        },
        clearTokenExpirationTimer({}) {
            if (logoutTimeout) {
                clearTimeout(logoutTimeout);
                clearInterval(intervalTime);
            }
        },
    },
});

export default store;
