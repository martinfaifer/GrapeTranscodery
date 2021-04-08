<template>
    <v-main>
        <v-container class="mt-12" fluid>
            <v-card flat color="transparent">
                <v-card-title>
                    <v-text-field
                        color="#777E8B"
                        autofocus
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Vyhledat transcoder"
                        single-line
                        hide-details
                    ></v-text-field>
                    <v-spacer></v-spacer>
                    <v-btn
                        @click="openNewTranscoderDialog()"
                        outlined
                        color="green"
                        class="elevation-0"
                        small
                    >
                        <strong>
                            Nový Transcodér
                        </strong>
                    </v-btn>
                </v-card-title>
                <v-data-table
                    dense
                    :headers="headers"
                    :items="transcoders"
                    :search="search"
                    class="elevation-0 body-2"
                >
                    <template v-slot:item.akce="{ item }">
                        <v-icon
                            @click="openTranscoderEditDialog(item.id)"
                            small
                            color="green"
                            >mdi-pencil</v-icon
                        >
                        <v-icon
                            @click="deleteTranscoder(item.id)"
                            small
                            color="red"
                            >mdi-delete</v-icon
                        >
                    </template>

                    <template v-slot:item.status="{ item }">
                        <span v-if="item.status == 'success'">
                            <span class="green--text">
                                Online
                            </span>
                        </span>

                        <span v-if="item.status == 'offline'">
                            <span class="red--text">
                                Offline
                            </span>
                        </span>

                        <span v-if="item.status == 'waiting'">
                            <span class="blue--text">
                                Waiting
                            </span>
                        </span>
                    </template>
                </v-data-table>
            </v-card>
        </v-container>

        <!-- DIALOGY -->

        <!-- DIALOG CREATE -->

        <v-row justify="center">
            <v-dialog
                v-model="newTranscoderDialog"
                persistent
                max-width="800px"
            >
                <v-card>
                    <v-card-title class="text-center headline">
                        Založení transcodéru
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="nazev"
                                        label="Popis transcodéru"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="ip"
                                        label="IPv4 transcodéru"
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
                            @click="saveCreate()"
                        >
                            Vytvořit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- KONEC DIALOGU CREATE -->

        <!-- DIALOG EDIT -->

        <v-row justify="center">
            <v-dialog
                v-model="editTranscoderDialog"
                persistent
                max-width="800px"
            >
                <v-card>
                    <v-card-title class="text-center headline">
                        Editace transcodéru
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="transcoderEdit.name"
                                        label="Popis transcodéru"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="transcoderEdit.ip"
                                        label="IPv4 transcodéru"
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
                        <v-btn color="green darken-1" text @click="saveEdit()">
                            Vytvořit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        transcoderId: null,
        transcoderEdit: [],
        nazev: null,
        ip: null,
        newTranscoderDialog: false,
        editTranscoderDialog: false,
        search: "",
        status: null,
        transcoders: [],
        headers: [
            {
                text: "Název",
                align: "start",
                value: "name"
            },
            { text: "IP", value: "ip" },
            { text: "Status", value: "status" },
            // { text: "Grafy", value: "cacti" },
            { text: "Akce", sortable: false, value: "akce" }
        ]
    }),
    created() {
        this.loadTranscoders();
    },
    methods: {
        loadTranscoders() {
            window.axios.get("transcoders").then(response => {
                this.transcoders = response.data.data;
            });
        },
        openNewTranscoderDialog() {
            this.newTranscoderDialog = true;
        },
        closeDialog() {
            this.newTranscoderDialog = false;
            this.editTranscoderDialog = false;
            this.nazev = null;
            this.transcoderEdit = [];
            this.ip = null;
        },
        saveCreate() {
            axios
                .post("transcoder/create", {
                    name: this.nazev,
                    ip: this.ip
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.closeDialog();
                    this.loadTranscoders();
                });
        },
        openTranscoderEditDialog(trasncoderId) {
            axios
                .post("transcoder/search", {
                    trasncoderId: trasncoderId
                })
                .then(response => {
                    this.transcoderEdit = response.data;
                    this.transcoderId = trasncoderId;
                    this.editTranscoderDialog = true;
                });
        },

        saveEdit() {
            axios
                .post("transcoder/edit", {
                    transcoderId: this.transcoderId,
                    name: this.transcoderEdit.name,
                    ip: this.transcoderEdit.ip
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.closeDialog();
                    this.loadTranscoders();
                });
        },
        deleteTranscoder(transcoderId) {
            axios
                .post("transcoder/delete", {
                    transcoderId: transcoderId
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.loadTranscoders();
                });
        }
    },
    watch: {}
};
</script>
