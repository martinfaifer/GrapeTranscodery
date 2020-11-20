<template>
    <v-main>
        <!-- notifikace -->
        <notification-component
            v-if="status != null"
            :status="status"
        ></notification-component>
        <v-app>
            <v-app-bar color="#586776" fixed dense>
                <v-app-bar-nav-icon
                    color="white"
                    @click="drawer = true"
                ></v-app-bar-nav-icon>
                <v-spacer></v-spacer>

                <!-- user Part -->

                <template v-if="$vuetify.breakpoint">
                    <v-menu transition="scroll-y-transition">
                        <template v-slot:activator="{ on }">
                            <v-btn class="white--text" fab icon v-on="on">
                                <v-avatar color="transparent" small>
                                    <v-icon>
                                        mdi-account
                                    </v-icon>
                                </v-avatar>
                            </v-btn>
                        </template>
                        <v-list width="250px" class="text-center subtitle-2">
                            <v-list-item @click="editPasswordDialog = true">
                                Editace <v-spacer></v-spacer
                                ><v-icon color="grey" right small
                                    >mdi-account-cog-outline</v-icon
                                >
                            </v-list-item>
                            <v-list-item
                                v-show="userRole != 'nahled'"
                                link
                                to="/settings/streams"
                            >
                                Nastavení App<v-spacer></v-spacer
                                ><v-icon color="grey" right small
                                    >mdi-settings</v-icon
                                >
                            </v-list-item>
                            <v-divider></v-divider>
                            <v-list-item @click="logOut()">
                                Odhlásit se <v-spacer></v-spacer
                                ><v-icon color="grey" right small
                                    >mdi-lock</v-icon
                                >
                            </v-list-item>

                            <v-divider></v-divider>
                            <v-list-item
                                href="http://iptvdoku.grapesc.cz/#/"
                                target="_blink"
                            >
                                IPTV Dokumentace<v-spacer></v-spacer
                                ><v-icon color="grey" right small
                                    >mdi-television</v-icon
                                >
                            </v-list-item>
                            <v-list-item
                                href="http://iptvdohled.grapesc.cz/#/"
                                target="_blink"
                            >
                                IPTV Dohled<v-spacer></v-spacer
                                ><v-icon color="grey" right small
                                    >mdi-television</v-icon
                                >
                            </v-list-item>
                        </v-list>
                    </v-menu>
                </template>
                <!-- end User Part -->
                <v-badge
                    bottom
                    color="red"
                    :content="count"
                    offset-x="10"
                    offset-y="10"
                >
                    <v-icon @click="alertMenu = true" color="white"
                        >mdi-bell-outline</v-icon
                    >
                </v-badge>
            </v-app-bar>

            <!-- bocní navigace -->

            <v-navigation-drawer v-model="drawer" left fixed temporary>
                <v-list nav dense>
                    <v-list-item-group v-model="group">
                        <v-list-item link to="/">
                            <v-list-item-icon>
                                <v-icon>mdi-home</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>Přehled</v-list-item-title>
                        </v-list-item>

                        <v-list-item link to="/streams">
                            <v-list-item-icon>
                                <v-icon>mdi-view-stream</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>Streamy</v-list-item-title>
                        </v-list-item>

                        <v-list-group :value="true" no-action sub-group>
                            <template v-slot:activator>
                                <v-list-item-icon>
                                    <v-icon>mdi-television</v-icon>
                                </v-list-item-icon>
                                <v-list-item-content class="ml-3">
                                    <v-list-item-title
                                        >Transcodéry</v-list-item-title
                                    >
                                </v-list-item-content>
                            </template>

                            <v-list-item
                                v-for="transcoder in transcoders"
                                :key="transcoder.id"
                                link
                                :to="'/transcoder/' + transcoder.ip"
                            >
                                <v-list-item-title
                                    v-text="transcoder.name"
                                ></v-list-item-title>
                            </v-list-item>
                        </v-list-group>
                    </v-list-item-group>
                </v-list>
            </v-navigation-drawer>

            <!-- konec bocni navigace -->

            <!-- alerting -->

            <v-navigation-drawer
                v-model="alertMenu"
                right
                color="transparent"
                fixed
                temporary
            >
                <div v-if="problemticStreams != null">
                    <div
                        id="alerty"
                        class="pl-2 pr-2"
                        v-for="problemticStream in problemticStreams"
                        :key="problemticStream.id"
                    >
                        <v-alert
                            dense
                            border="left"
                            type="error"
                            class="body-2 mt-2"
                        >
                            <strong>{{ problemticStream.nazev }}</strong>
                        </v-alert>
                    </div>
                </div>
            </v-navigation-drawer>

            <!-- konec alertingu -->

            <transition name="fade" mode="out-in">
                <router-view class="mt-0"> </router-view>
            </transition>
        </v-app>
        <!-- dialog -->

        <v-row justify="center">
            <v-dialog v-model="editPasswordDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title class="headline text-center">
                        Změna hesla
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="heslo"
                                        label="Nové heslo"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="closeDialog()">
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="changePassword()"
                        >
                            Změnit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </v-main>
</template>

<script>
import NotificationComponent from "./Notifications/NotificationComponent";
export default {
    data: () => ({
        heslo: null,
        status: null,
        editPasswordDialog: false,
        problemticStreams: null,
        count: 0,
        group: null,
        drawer: null,
        alertMenu: null,
        groupAlert: null,
        userMenu: null,
        userRole: null,
        loggedUser: null,
        transcoders: []
    }),
    components: {
        "notification-component": NotificationComponent
    },
    computed: {},

    created() {
        this.loadUser();
        this.loadTranscoders();
        this.loadProblematicStreams();
    },
    methods: {
        loadProblematicStreams() {
            window.axios.get("streams/issue").then(response => {
                if (response.data.status == "empty") {
                    (this.problemticStreams = null), (this.count = 0);
                } else {
                    this.problemticStreams = response.data.data;
                    this.count = response.data.count;
                }
            });
        },

        loadTranscoders() {
            window.axios.get("transcoders").then(response => {
                this.transcoders = response.data.data;
            });
        },

        logOut() {
            let currentObj = this;
            axios.get("logout").then(response => {
                currentObj.$router.push("/login");
            });
        },

        loadUser() {
            let currentObj = this;
            window.axios.get("user").then(response => {
                if (response.data.status == "error") {
                    currentObj.$router.push("/login");
                } else {
                    currentObj.loggedUser = response.data;
                    currentObj.userRole = response.data.user_role;
                }
            });
        },
        changePassword() {
            let currentObj = this;
            axios
                .post("user/password", {
                    heslo: this.heslo
                })
                .then(function(response) {
                    currentObj.status = response.data;
                    currentObj.editPasswordDialog = false;
                    currentObj.heslo = null;
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                });
        },
        closeDialog() {
            this.editPasswordDialog = false;
            this.heslo = null;
        }
    },

    mounted() {
        setInterval(
            function() {
                try {
                    this.loadUser();
                    this.loadProblematicStreams();
                } catch (error) {}
            }.bind(this),
            2000
        );
    },
    watch: {}
};
</script>
