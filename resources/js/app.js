require("./bootstrap");

window.Vue = require("vue");

import Chartkick from "vue-chartkick";
import VueApexCharts from 'vue-apexcharts'
import Chart from "chart.js";
import Vuetify from "vuetify";
import VueRouter from "vue-router";

Vue.use(Vuetify);
Vue.use(VueRouter);
Vue.use(Chartkick.use(Chart));
Vue.use(VueApexCharts)

Vue.component('apexchart', VueApexCharts)

import LoginComponent from "./components/LoginComponent";
import NavigationComponent from "./components/NavigationComponent";
import TranscoderyDashBoardComponent from "./components/TranscoderyDashBoardComponent";
import StreamsComponent from "./components/StreamsComponent"
import TranscoderComponent from "./components/TranscoderComponent";
import SettingsComponent from "./components/Settings/SettingsComponent";
import MainTamplateComponent from "./components/Settings/Template/MainTamplateComponent"
import StreamsSettingsComponent from "./components/Settings/Streams/StreamsSettingsComponent"
import TranscoderSettingsComponent from "./components/Settings/TranscoderSettingsComponent"
import UsersComponent from "./components/Settings/UsersComponent"


import PageNotFoundComponent from "./components/PageNotFoundComponent";

let routes = [
    {
        path: "/",
        component: NavigationComponent,
        children: [
            {
                path: "",
                component: TranscoderyDashBoardComponent
            },
            {
                path: "streams",
                component: StreamsComponent
            },
            {
                path: "transcoder/:ip",
                component: TranscoderComponent
            },
            {
                path: "settings",
                component: SettingsComponent,
                children: [
                    {
                        path: "/settings/prehled"
                    },
                    {
                        path: "/settings/template",
                        component: MainTamplateComponent
                    },
                    {
                        path: "/settings/streams",
                        component: StreamsSettingsComponent
                    },
                    {
                        path: "/settings/transcoders",
                        component: TranscoderSettingsComponent
                    },
                    {
                        path: "/settings/users",
                        component: UsersComponent
                    },
                    {
                        path: "/settings/alerts"
                    }
                ]
            }
        ]
    },
    {
        path: "/login",
        component: LoginComponent
    },
    {
        path: '*',
        component: PageNotFoundComponent
    },
];

// definice konstant
const router = new VueRouter({
    routes
});
const opts = {};

//module.export
const app = new Vue({
    el: "#app",
    router,
    vuetify: new Vuetify({

    })
});
