import Vue from "vue";

import Vuex from "vuex";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        alerts: null,
    },
    actions: {},
    mutations: {
        update(state, alert) {
            state.alerts.push(alert);
        },
    },
    getters: {}
});
