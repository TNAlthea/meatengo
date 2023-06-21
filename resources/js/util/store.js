import { createStore } from "vuex";
import { logout } from "./authUtils";

let logoutTimeout = null;

// Get the initial state from local storage if available
const storedState = localStorage.getItem("storeState");
const initialState = storedState
    ? JSON.parse(storedState)
    : {
          signature: null,
          entity: null,
          route: null,
          loginTime: null,
      };

const store = createStore({
    state: {
        signature: initialState.signature,
        entity: initialState.entity,
        route: initialState.route,
        loginTime: initialState.loginTime,
    },
    mutations: {
        setSignature(state, signature) {
            state.signature = signature;
            persistState(state);
        },
        setEntity(state, entity) {
            state.entity = entity;
            persistState(state);
        },
        setRoute(state, route) {
            state.route = route;
            persistState(state);
        },
        setLoginTime(state, time) {
            state.loginTime = time;
            persistState(state);
        },
        clearAllState(state) {
            state.signature = null;
            state.entity = null;
            state.route = null;
            state.loginTime = null;
            persistState(state);
        },
    },
    getters: {
        getLoginTime(state) {
            return state.loginTime;
        },
    },
    actions: {
        startTokenExpirationTimer({ commit, dispatch, state }) {
            if (logoutTimeout) {
                clearTimeout(logoutTimeout);
            }

            const timeout = 6 * 60 * 60 * 1000;

            logoutTimeout = setTimeout(() => {
                logout(state.entity, state.signature, state.route);
                dispatch("clearTokenExpirationTimer");
                alert("Inactivity detected, please login again.");

                commit("clearAllState");
            }, timeout);
        },
        clearTokenExpirationTimer({ commit }) {
            if (logoutTimeout) {
                clearTimeout(logoutTimeout);
            }
            commit("clearAllState");
        },
    },
});

function persistState(state) {
    localStorage.setItem("storeState", JSON.stringify(state));
}

export default store;
