<template>
    <v-main>
        <v-row>
            <v-card flat color="transparent">
                <v-card-title>
                    <v-spacer></v-spacer>
                    <v-btn
                        @click="openCreateDialog()"
                        outlined
                        color="green"
                        class="elevation-0"
                        small
                    >
                        <strong>
                            Nový formát
                        </strong>
                    </v-btn>
                </v-card-title>
                <div v-show="formats != null">
                    <v-data-table
                        dense
                        :headers="headers"
                        :items="formats"
                        class="elevation-0 body-2"
                    >
                        <template v-slot:item.akce="{ item }">
                            <!-- mdi-play -->
                            <v-icon
                                @click="editFormatDialog(item.id)"
                                small
                                color="green"
                                >mdi-pencil</v-icon
                            >

                            <!-- mdi-stop -->
                            <v-icon
                                @click="deleteFormat(item.id)"
                                small
                                color="red"
                                >mdi-delete</v-icon
                            >
                        </template>
                    </v-data-table>
                </div>
            </v-card>
        </v-row>

        <!-- DIALOGY -->
        <!-- DIALOG NA CREATE -->
        <v-row justify="center">
            <v-dialog v-model="createDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title class="text-center headline">
                        Založení formátu
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="videoFormat"
                                        label="Cílový formát videa"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="videoCode"
                                        label="Kódové označení pro ffmpeg"
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
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- KONEC DIALOGU NA CREATE -->
        <!-- DIALOG EDIT -->
        <v-row justify="center">
            <v-dialog v-model="editDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title class="text-center headline">
                        Editace formátu
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="formatEdit.video"
                                        label="Cílový formát videa"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="formatEdit.code"
                                        label="Kódové označení pro ffmpeg"
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
                        <v-btn color="green darken-1" text @click="edit()">
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- KONEC DIALOGU EDIT -->
        <!-- KONEC DIALOGU -->
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            formatId: null,
            editDialog: false,
            formatEdit: [],
            videoFormat: null,
            videoCode: null,
            createDialog: false,
            status: null,
            formats: [],
            headers: [
                {
                    text: "Video formát",
                    align: "start",
                    value: "video"
                },
                { text: "Označení", value: "code" },
                { text: "Akce", sortable: false, value: "akce" }
            ]
        };
    },
    created() {
        this.loadFormats();
    },

    methods: {
        openCreateDialog() {
            this.createDialog = true;
        },

        editFormatDialog(formatId) {
            axios
                .post("format/get", {
                    formatId: formatId
                })
                .then(response => {
                    this.editDialog = true;
                    this.formatId = formatId;
                    this.formatEdit = response.data;
                });
        },
        saveCreate() {
            axios
                .post("format/create", {
                    videoFormat: this.videoFormat,
                    videoCode: this.videoCode
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.loadFormats();
                    this.closeDialog();
                });
        },

        edit() {
            axios
                .post("format/edit", {
                    formatId: this.formatId,
                    video: this.formatEdit.video,
                    code: this.formatEdit.code
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.loadFormats();
                    this.closeDialog();
                });
        },
        closeDialog() {
            this.videoFormat = null;
            this.videoCode = null;
            this.createDialog = false;
            this.editDialog = false;
            this.formatEdit = [];
        },
        loadFormats() {
            let currentObj = this;
            window.axios.get("formats").then(response => {
                if (response.data.status == "success") {
                    currentObj.formats = response.data.data;
                } else {
                    currentObj.formats = null;
                }
            });
        },
        deleteFormat(formatId) {
            axios
                .post("format/delete", {
                    formatId: formatId
                })
                .then(response => {
                    this.loadFormats();
                    this.$store.state.alerts = response.data.alert;
                });
        }
    }
};
</script>
