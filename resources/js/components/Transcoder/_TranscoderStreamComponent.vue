<template>
    <div>
        <v-card class="elevation-0">
            <v-card-title>
                <v-text-field
                    v-model="search"
                    append-icon="mdi-magnify"
                    label="Hledat stream"
                    single-line
                    hide-details
                ></v-text-field>
            </v-card-title>
            <div v-if="streams != null">
                <v-data-table
                    :headers="headers"
                    :items="streams"
                    :search="search"
                >
                    <template v-slot:item.status="{ item }">
                        <span v-if="item.status == 'active'">
                            <span class="green--text">
                                <strong>
                                    funguje
                                </strong>
                            </span>
                        </span>
                        <span v-else-if="item.status == 'issue'">
                            <span class="red--text">
                                <strong>
                                    problém
                                </strong>
                            </span>
                        </span>
                        <span v-else>
                            <span class="blue--text">
                                <strong>
                                    zastaven
                                </strong>
                            </span>
                        </span>
                    </template>

                    <template v-slot:item.akce="{ item }">
                        <!-- mdi-play -->
                        <v-btn
                            v-if="userRole != 'nahled'"
                            icon
                            :loading="loading"
                            small
                            v-show="
                                item.status == 'STOP' || item.status == 'issue'
                            "
                            @click="sendPlay(item.id)"
                        >
                            <v-icon small color="green">mdi-play</v-icon>
                        </v-btn>

                        <!-- mdi-stop -->
                        <v-btn
                            v-if="userRole != 'nahled'"
                            icon
                            small
                            :loading="loading"
                            v-show="item.status == 'active'"
                            @click="sendStop(item.id, item.pid)"
                        >
                            <v-icon small color="red">mdi-stop</v-icon>
                        </v-btn>
                    </template>
                </v-data-table>
            </div>
            <div v-else-if="streams == null">
                <v-alert type="info" class="ml-1 mr-1">
                    Na transcodéru nejsou žádně streamy
                </v-alert>
            </div>
        </v-card>
    </div>
</template>
<script>
export default {
    data() {
        return {
            userRole: null,
            loadingInterval: null,
            loading: false,
            status: null,
            search: "",
            streams: [],
            headers: [
                {
                    text: "Název",
                    align: "start",
                    value: "nazev"
                },
                {
                    text: "Src",
                    value: "src"
                },
                {
                    text: "Dst",
                    value: "dst"
                },
                {
                    text: "Rozlišení",
                    value: "dst1_resolution"
                },
                {
                    text: "Dst 2",
                    value: "dst2"
                },
                {
                    text: "Rozlišení",
                    value: "dst2_resolution"
                },
                {
                    text: "Dst 3",
                    value: "dst3"
                },
                {
                    text: "Rozlišení",
                    value: "dst3_resolution"
                },
                {
                    text: "Dst 4",
                    value: "dst4"
                },
                {
                    text: "Rozlišení",
                    value: "dst4_resolution"
                },
                {
                    text: "Status",
                    value: "status"
                },
                {
                    text: "Akce",
                    value: "akce"
                }
            ]
        };
    },
    created() {
        this.loadStreams();
        this.loadUser();
    },

    methods: {
        loadUser() {
            window.axios.get("user").then(response => {
                if (response.data.status == "error") {
                    this.userRole = [];
                } else {
                    this.userRole = response.data.user_role;
                }
            });
        },
        loadStreams() {
            axios
                .post("transcoder/streams", {
                    transcoderIp: this.$route.params.ip
                })
                .then(response => {
                    if (response.data.status != "success") {
                        this.streams = null;
                    } else {
                        this.streams = response.data.data;
                    }
                });
        },
        sendPlay(id) {
            this.loading = true;
            axios
                .post("transcoder/stream/start", {
                    streamId: id,
                    transcoderIp: this.$route.params.ip,
                    cmd: "START"
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.loading = false;
                    this.loadStreams();
                });
        },

        sendStop(id, pid) {
            this.loading = true;
            axios
                .post("transcoder/stream/stop", {
                    streamPid: pid,
                    streamId: id,
                    transcoderIp: this.$route.params.ip,
                    cmd: "KILL"
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.loading = false;
                    this.loadStreams();
                });
        }
    },
    mounted() {
        this.loadingInterval = setInterval(
            function() {
                this.loadStreams();
            }.bind(this),
            2000
        );
    },
    watch: {
        $route(to, from) {
            this.loadStreams();
            this.loadingInterval = null;
        }
    },

    beforeDestroy: function() {
        clearInterval(this.loadingInterval);
    }
};
</script>
