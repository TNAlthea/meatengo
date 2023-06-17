// store.js

import { createStore } from 'vuex';

const store = createStore({
  state: {
    tokenExpirationTimer: null,
  },
  mutations: {
    setTokenExpirationTimer(state, timer) {
      state.tokenExpirationTimer = timer;
    },
    clearTokenExpirationTimer(state) {
      if (state.tokenExpirationTimer) {
        clearTimeout(state.tokenExpirationTimer);
        state.tokenExpirationTimer = null;
      }
    },
  },
  actions: {
    startTokenExpirationTimer({ commit, dispatch }) {
      const timer = setTimeout(() => {
        dispatch('logout');
        alert("Token expired, please login again.");
      }, 30000); // Adjust the timeout value as needed
      
      commit('setTokenExpirationTimer', timer);
    },
    clearTokenExpirationTimer({ commit }) {
      commit('clearTokenExpirationTimer');
    },
    logout({ /* access necessary parameters */ }) {
      // Perform logout logic
    },
  },
});

export default store;
