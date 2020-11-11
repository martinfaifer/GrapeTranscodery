<template>
    <div>
        <v-app-bar class="mt-12" color="transparent" fixed dense flat dark>
            <v-row justify="center" fixed>
                <v-card fixed flat color="transparent">
                    <v-toolbar color="transparent" flat dense dark>
                        <v-spacer></v-spacer>

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    link
                                    to="/settings/prehled"
                                    v-on="on"
                                    icon
                                    color="#777E8B"
                                >
                                    <v-icon
                                        v-if="
                                            $route.path === '/settings/prehled'
                                        "
                                        color="blue"
                                        >mdi-view-dashboard</v-icon
                                    >
                                    <v-icon v-else>mdi-view-dashboard</v-icon>
                                </v-btn>
                            </template>
                            <span>Dashboard</span>
                        </v-tooltip>


                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    link
                                    to="/settings/template"
                                    v-on="on"
                                    icon
                                    color="#777E8B"
                                >
                                    <v-icon
                                        v-if="
                                            $route.path === '/settings/template'
                                        "
                                        color="blue"
                                        >mdi-map</v-icon
                                    >
                                    <v-icon v-else>mdi-map</v-icon>
                                </v-btn>
                            </template>
                            <span>Šablony</span>
                        </v-tooltip>

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    link
                                    to="/settings/streams"
                                    v-on="on"
                                    icon
                                    color="#777E8B"
                                >
                                    <v-icon
                                        v-if="
                                            $route.path === '/settings/streams'
                                        "
                                        color="blue"
                                        >mdi-television-guide</v-icon
                                    >
                                    <v-icon v-else>mdi-television-guide</v-icon>
                                </v-btn>
                            </template>
                            <span>Kanály</span>
                        </v-tooltip>

                        <div>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/users"
                                        v-on="on"
                                        icon
                                        color="#777E8B"
                                    >
                                        <v-icon
                                            v-if="
                                                $route.path ===
                                                    '/settings/users'
                                            "
                                            color="blue"
                                            >mdi-account-multiple-outline</v-icon
                                        >
                                        <v-icon v-else
                                            >mdi-account-multiple-outline</v-icon
                                        >
                                    </v-btn>
                                </template>
                                <span>Uživatelé</span>
                            </v-tooltip>
                        </div>

                        <div>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/alerts"
                                        v-on="on"
                                        icon
                                        color="#777E8B"
                                    >
                                        <v-icon
                                            v-if="
                                                $route.path ===
                                                    '/settings/alerts'
                                            "
                                            color="blue"
                                            >mdi-bell-outline</v-icon
                                        >
                                        <v-icon v-else>mdi-bell-outline</v-icon>
                                    </v-btn>
                                </template>
                                <span>Nastavení Alertingu</span>
                            </v-tooltip>
                        </div>
                        <v-spacer></v-spacer>
                    </v-toolbar>
                </v-card>
            </v-row>
        </v-app-bar>
    </div>
</template>
