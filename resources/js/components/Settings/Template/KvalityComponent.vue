<template>
    <v-main>
        <!-- notifikace -->
        <notification-component
            v-if="status != null"
            :status="status"
        ></notification-component>
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
                            Nová kvalita
                        </strong>
                    </v-btn>
                </v-card-title>
                <div v-show="kvality != null">
                    <v-data-table
                        dense
                        :headers="headers"
                        :items="kvality"
                        class="elevation-0 body-2"
                    >
                        <template v-slot:item.akce="{ item }">
                            <!-- mdi-play -->
                            <v-icon
                                @click="editKvality(item.id)"
                                small
                                color="green"
                                >mdi-pencil</v-icon
                            >

                            <!-- mdi-stop -->
                            <v-icon
                                @click="deleteKvality(item.id)"
                                small
                                color="red"
                                >mdi-delete</v-icon
                            >
                        </template>
                    </v-data-table>
                </div>
            </v-card>
        </v-row>

        <!-- dialogy na edit, create a delete -->
        <v-row justify="center">
            <v-dialog v-model="createDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title class="headline text-center">
                        Nová kvalita
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="12" md="12" class="mt-2">
                                <v-col>
                                    <v-autocomplete
                                        v-model="videoFormat"
                                        :items="streamFormats"
                                        item-value="id"
                                        item-text="video"
                                        dense
                                        label="Video formát"
                                    ></v-autocomplete>
                                </v-col>
                            </v-row>
                            <v-row
                                v-show="videoFormat != null"
                                cols="12"
                                sm="6"
                                md="6"
                                class="mt-2"
                            >
                                <v-col>
                                    <v-text-field
                                        v-model="sirka"
                                        label="Šířka"
                                        type="number"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="vyska"
                                        label="Výška"
                                        type="number"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row
                                v-show="videoFormat != null"
                                cols="12"
                                sm="6"
                                md="6"
                                class="mt-2"
                            >
                                <v-col>
                                    <v-text-field
                                        v-model="minBitrate"
                                        label="Minimální bitrate v kbps"
                                        type="number"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="maxBitrate"
                                        label="Maximální bitrate v kbps"
                                        type="number"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="bitrate"
                                        label="Průměrný bitrate v kbps"
                                        type="number"
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
                            @click="saveCreateDialog()"
                        >
                            Uložit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </v-main>
</template>
<script>
import NotificationComponent from "../../Notifications/NotificationComponent";
export default {
    data() {
        return {
            minBitrate: null,
            maxBitrate: null,
            bitrate: null,
            sirka: null,
            vyska: null,
            streamFormats: [],
            videoFormat: null,
            createDialog: false,
            status: null,
            kvality: [],
            headers: [
                {
                    text: "Video formát",
                    align: "start",
                    value: "stream_format"
                },
                { text: "kvalita", value: "kvalita" },
                { text: "Min bitrate", value: "minrate" },
                { text: "Max bitrate", value: "maxrate" },
                { text: "Průměrný bitrate", value: "bitrate" },
                { text: "Poměr", value: "scale" },
                { text: "Akce", sortable: false, value: "akce" }
            ]
        };
    },

    created() {
        this.loadKvality();
    },
    components: {
        "notification-component": NotificationComponent
    },
    methods: {
        loadKvality() {
            let currentObj = this;
            window.axios.get("kvality").then(response => {
                if (response.data.status == "success") {
                    currentObj.kvality = response.data.data;
                } else {
                    currentObj.kvality = null;
                }
            });
        },
        loadStreamFormats() {
            let currentObj = this;
            window.axios.get("formats").then(response => {
                if (response.data.status == "success") {
                    currentObj.streamFormats = response.data.data;
                } else {
                    currentObj.streamFormats = null;
                    // vyvolat alert jelikož neexistuje žádný dostupný video formát, je nutné jej nedirve zalozit
                }
            });
        },

        closeDialog() {
            this.createDialog = false;
            this.streamFormats = [];
            this.minBitrate = null;
            this.maxBitrate = null;
            this.bitrate = null;
            this.sirka = null;
            this.vyska = null;
            this.videoFormat = null;
        },

        saveCreateDialog() {
            let currentObj = this;
            axios
                .post("kvality/create", {
                    videoFormat: this.videoFormat,
                    minBitrate: this.minBitrate,
                    maxBitrate: this.maxBitrate,
                    bitrate: this.bitrate,
                    sirka: this.sirka,
                    vyska: this.vyska
                })
                .then(function(response) {
                    if (response.data.status === "success") {
                        currentObj.loadKvality();
                        currentObj.closeDialog();
                        currentObj.status = response.data;
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 2000);
                    } else {
                        currentObj.status = response.data;
                        currentObj.loadKvality();
                        currentObj.closeDialog();
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 2000);
                    }
                });
        },

        openCreateDialog() {
            this.loadStreamFormats();
            this.createDialog = true;
        },
        deleteKvality(id) {
            let currentObj = this;
            axios
                .post("kvality/delete", {
                    kvalitaId: id
                })
                .then(function(response) {
                    if (response.data.status === "success") {
                        currentObj.loadKvality();
                        currentObj.status = response.data;
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 2000);
                    } else {
                        currentObj.status = response.data;
                        currentObj.loadKvality();
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 2000);
                    }
                });
        }
    }
};
</script>
