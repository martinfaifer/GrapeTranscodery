<template>
    <v-main>
        <notification-component
            v-if="status != null"
            :status="status"
        ></notification-component>
        <v-container class="mt-12" fluid>
            <v-card flat color="transparent">
                <v-card-title>
                    <v-text-field
                        color="#777E8B"
                        autofocus
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Vyhledat stream"
                        single-line
                        hide-details
                    ></v-text-field>
                    <v-spacer></v-spacer>
                    <v-btn
                        @click="openCreateStreamDialog()"
                        outlined
                        color="green"
                        class="elevation-0"
                        small
                    >
                        <strong>
                            Nový stream
                        </strong>
                    </v-btn>
                </v-card-title>
                <v-data-table
                    dense
                    :headers="headers"
                    :items="streams"
                    :search="search"
                    class="elevation-0 body-2"
                >
                    <template v-slot:item.format="{ item }">
                        <span v-if="item.format == 'H.264'">
                            <span class="blue--text">
                                <strong>
                                    {{ item.format }}
                                </strong>
                            </span>
                        </span>
                        <span v-if="item.format == 'H.265'">
                            <span class="purple--text">
                                <strong>
                                    {{ item.format }}
                                </strong>
                            </span>
                        </span>
                    </template>

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
                        <v-icon
                            :disabled="item.status == 'active'"
                            @click="openEditStreamDialog(item.id)"
                            small
                            color="green"
                            >mdi-pencil</v-icon
                        >
                        <v-icon
                            :disabled="item.status == 'active'"
                            @click="openEditScriptEdialog(item.id)"
                            small
                            color="orange"
                            >mdi-tooltip-edit</v-icon
                        >
                        <v-icon
                            :disabled="item.status == 'active'"
                            @click="deleteStream(item.id)"
                            small
                            color="red"
                            >mdi-delete</v-icon
                        >
                    </template>
                </v-data-table>
            </v-card>
        </v-container>

        <!-- DIALOGY -->

        <!-- CREATE DIALOG -->

        <v-row justify="center">
            <v-dialog v-model="createDialog" persistent max-width="1000px">
                <v-card>
                    <v-card-title class="headline text-center">
                        Nový Stream
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="4" md="4" class="mt-2">
                                <v-col>
                                    <v-autocomplete
                                        v-model="transcoderId"
                                        :items="transcoders"
                                        item-value="id"
                                        item-text="name"
                                        dense
                                        label="Výběr transcodéru"
                                    ></v-autocomplete>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        dense
                                        v-show="transcoderId != null"
                                        v-model="stream_src"
                                        label="Zdrojová adresa streamu"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-btn
                                        v-show="stream_src != null"
                                        color="green darken-1"
                                        text
                                        :loading="loadingAnalyze"
                                        :disabled="loadingAnalyze == true"
                                        @click="
                                            analyse_stream(
                                                transcoderId,
                                                stream_src
                                            )
                                        "
                                        >Analýza</v-btn
                                    >
                                </v-col>
                            </v-row>

                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        dense
                                        v-model="stream_name"
                                        label="Název streamu"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="analyseDataVideo != null"
                                        disabled
                                        readonly
                                        dense
                                        v-model="analyseDataVideo[0].codec_name"
                                        label="Formát videa"
                                    ></v-text-field>
                                </v-col>

                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="audioIndex"
                                        :items="analyseDataAudio"
                                        item-value="index"
                                        item-text="popis"
                                        dense
                                        label="Výběr audia"
                                    ></v-select>
                                </v-col>
                            </v-row>
                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        @change="
                                            loadKvalityByFormatCode(formatCode)
                                        "
                                        v-model="formatCode"
                                        :items="formats"
                                        item-value="code"
                                        item-text="video"
                                        dense
                                        label="Výběr výstupního video formátu"
                                    ></v-select>
                                </v-col>
                            </v-row>
                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="dst1_kvality"
                                        :items="kvality"
                                        item-value="id"
                                        item-text="kvalita"
                                        dense
                                        label="Výběr rozlišení"
                                    ></v-select>
                                </v-col>
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="
                                            analyseDataVideo != null &&
                                                formatCode != null
                                        "
                                        dense
                                        v-model="dst1"
                                        label="Dst 1"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="dst2_kvality"
                                        :items="kvality"
                                        item-value="id"
                                        item-text="kvalita"
                                        dense
                                        label="Výběr rozlišení"
                                    ></v-select>
                                </v-col>
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="
                                            analyseDataVideo != null &&
                                                formatCode != null
                                        "
                                        dense
                                        v-model="dst2"
                                        label="Dst 2"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="dst3_kvality"
                                        :items="kvality"
                                        item-value="id"
                                        item-text="kvalita"
                                        dense
                                        label="Výběr rozlišení"
                                    ></v-select>
                                </v-col>
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="
                                            analyseDataVideo != null &&
                                                formatCode != null
                                        "
                                        dense
                                        v-model="dst3"
                                        label="Dst 3"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="dialogClose()">
                            Zavřít
                        </v-btn>
                        <v-btn
                            :loading="loadingCreate"
                            :disabled="dst1 == null"
                            color="green darken-1"
                            text
                            @click="createStream()"
                        >
                            Založit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- KONEC CREATE DIALOGU -->

        <!-- EDIT STREAM DIALOG -->

        <v-row justify="center">
            <v-dialog v-model="editStreamDialog" persistent max-width="1000px">
                <v-card>
                    <v-card-title class="headline text-center">
                        Editace Streamu
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="4" md="4" class="mt-2">
                                <v-col>
                                    <v-autocomplete
                                        v-model="transcoderId"
                                        :items="transcoders"
                                        item-value="id"
                                        item-text="name"
                                        dense
                                        label="Výběr transcodéru"
                                    ></v-autocomplete>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        dense
                                        v-model="streamEdit.src"
                                        label="Zdrojová adresa streamu"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-btn
                                        color="green darken-1"
                                        text
                                        :loading="loadingAnalyze"
                                        :disabled="loadingAnalyze == true"
                                        @click="
                                            analyse_stream(
                                                transcoderId,
                                                streamEdit.src
                                            )
                                        "
                                        >Analýza</v-btn
                                    >
                                </v-col>
                            </v-row>

                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        dense
                                        v-model="streamEdit.nazev"
                                        label="Název streamu"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="analyseDataVideo != null"
                                        disabled
                                        readonly
                                        dense
                                        v-model="analyseDataVideo[0].codec_name"
                                        label="Formát videa"
                                    ></v-text-field>
                                </v-col>

                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="audioIndex"
                                        :items="analyseDataAudio"
                                        item-value="index"
                                        item-text="popis"
                                        dense
                                        label="Výběr audia"
                                    ></v-select>
                                </v-col>
                            </v-row>
                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        @change="
                                            loadKvalityByFormatCode(formatCode)
                                        "
                                        v-model="formatCode"
                                        :items="formats"
                                        item-value="code"
                                        item-text="video"
                                        dense
                                        label="Výběr výstupního video formátu"
                                    ></v-select>
                                </v-col>
                            </v-row>
                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="dst1_kvality"
                                        :items="kvality"
                                        item-value="id"
                                        item-text="kvalita"
                                        dense
                                        label="Výběr rozlišení"
                                    ></v-select>
                                </v-col>
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="
                                            analyseDataVideo != null &&
                                                formatCode != null
                                        "
                                        dense
                                        v-model="streamEdit.dst"
                                        label="Dst 1"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="dst2_kvality"
                                        :items="kvality"
                                        item-value="id"
                                        item-text="kvalita"
                                        dense
                                        label="Výběr rozlišení"
                                    ></v-select>
                                </v-col>
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="
                                            analyseDataVideo != null &&
                                                formatCode != null
                                        "
                                        dense
                                        v-model="streamEdit.dst2"
                                        label="Dst 2"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row
                                v-if="analyseDataVideo != null"
                                sm="4"
                                md="4"
                                class="mt-2"
                            >
                                <v-col sm="4" md="4">
                                    <v-select
                                        v-model="dst3_kvality"
                                        :items="kvality"
                                        item-value="id"
                                        item-text="kvalita"
                                        dense
                                        label="Výběr rozlišení"
                                    ></v-select>
                                </v-col>
                                <v-col sm="4" md="4">
                                    <v-text-field
                                        v-if="
                                            analyseDataVideo != null &&
                                                formatCode != null
                                        "
                                        dense
                                        v-model="streamEdit.dst3"
                                        label="Dst 3"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="dialogClose()">
                            Zavřít
                        </v-btn>
                        <v-btn
                            :loading="loadingEdit"
                            color="green darken-1"
                            text
                            @click="editStream()"
                        >
                            Založit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- KONEC EDIT STREAM DIALOGU   -->

        <!-- EDITACE SCRIPTU -->

        <v-row justify="center">
            <v-dialog v-model="editScriptDialog" persistent max-width="1000px">
                <v-card>
                    <v-card-title class="headline text-center">
                        Editace scriptu
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="4" md="4" class="mt-2">
                                <v-col>
                                    <v-textarea
                                        label="Script, který spouští stream ..."
                                        v-model="streamEditScript"
                                    ></v-textarea>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="dialogClose()">
                            Zavřít
                        </v-btn>
                        <v-btn
                            :loading="loadingEdit"
                            color="green darken-1"
                            text
                            @click="editStreamScript()"
                        >
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- KONEC EDITACE SCRIPTU -->
    </v-main>
</template>
<script>
import NotificationComponent from "../../Notifications/NotificationComponent";
export default {
    data: () => ({
        streamEditScript: null,
        editScriptDialog: false,
        streamId: null,
        loadingEdit: false,
        streamEdit: [],
        editStreamDialog: false,
        inputCodec: null,
        loadingCreate: false,
        videoIndex: null,
        dst3: null,
        dst3_kvality: null,
        dst2: null,
        dst2_kvality: null,
        dst1_kvality: null,
        dst1: null,
        formatCode: null,
        audioIndex: null,
        videoInputFormat: null,
        stream_name: null,
        status: null,
        formats: [],
        kvality: [],
        loadingAnalyze: false,
        analyseDataAudio: [],
        analyseDataVideo: null,
        stream_src: null,
        transcoderId: null,
        transcoders: [],
        createDialog: false,
        search: "",
        streams: [],
        headers: [
            {
                text: "Název",
                align: "start",
                value: "nazev"
            },
            { text: "SRC", value: "src" },
            { text: "DST", value: "dst" },
            { text: "Rozlišení", value: "dst1_resolution" },
            { text: "DST 2", value: "dst2" },
            { text: "Rozlišení", value: "dst2_resolution" },
            { text: "DST 3", value: "dst3" },
            { text: "Rozlišení", value: "dst3_resolution" },
            { text: "DST 4", value: "dst4" },
            { text: "Rozlišení", value: "dst4_resolution" },
            { text: "Formát", value: "format" },
            { text: "Transcodér", value: "transcoder" },
            { text: "Status", value: "status" },
            { text: "Akce", sortable: false, value: "akce" }
        ]
    }),

    components: {
        "notification-component": NotificationComponent
    },
    created() {
        this.loadStreams();
    },
    methods: {
        deleteStream(streamId) {
            let currentObj = this;
            axios
                .post("stream/delete", {
                    streamId: streamId
                })
                .then(response => {
                    currentObj.loadStreams();
                    currentObj.status = response.data;
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                });
        },

        openEditStreamDialog(streamId) {
            // načtení informací o streamu z db
            let currentObj = this;
            axios
                .post("stream/info", {
                    streamId: streamId
                })
                .then(response => {
                    currentObj.getTranscoders();
                    currentObj.streamEdit = response.data;
                    currentObj.streamId = response.data.id;
                    currentObj.editStreamDialog = true;
                });
        },

        openEditScriptEdialog(streamId) {
            let currentObj = this;
            axios
                .post("stream/script", {
                    streamId: streamId
                })
                .then(response => {
                    currentObj.streamEditScript = response.data;
                    currentObj.streamId = streamId;
                    currentObj.editScriptDialog = true;
                });
        },
        editStreamScript() {
            let currentObj = this;
            axios
                .post("stream/script/edit", {
                    streamId: this.streamId,
                    script: this.streamEditScript
                })
                .then(response => {
                    currentObj.resetDialog();
                    currentObj.loadStreams();
                    currentObj.status = response.data;
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                });
        },

        loadStreams() {
            let currentObj = this;
            window.axios.get("streams").then(response => {
                currentObj.streams = response.data;
            });
        },

        openCreateStreamDialog() {
            // nactení transcodérů transcoders
            this.getTranscoders();

            this.createDialog = true;
        },
        // nactení transcodérů
        getTranscoders() {
            window.axios.get("transcoders").then(response => {
                this.transcoders = response.data.data;
            });
        },
        loadKvalityByFormatCode(formatCode) {
            let currentObj = this;
            axios
                .post("kvality/search", {
                    formatCode: formatCode
                })
                .then(response => {
                    currentObj.kvality = response.data;
                });
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

        // Analýza streamu na daném transcodéru
        analyse_stream(transcoderId, stream_src) {
            this.loadingAnalyze = true;
            let currentObj = this;
            axios
                .post("stream/analyze", {
                    transcoderId: transcoderId,
                    stream_src: stream_src,
                    cmd: "PROBE"
                })
                .then(function(response) {
                    console.log(response.data);
                    currentObj.loadingAnalyze = false;
                    if (response.data.status === "success") {
                        currentObj.analyseDataVideo = response.data.video;
                        currentObj.videoIndex = response.data.video[0].index;
                        currentObj.inputCodec =
                            response.data.video[0].input_codec;
                        currentObj.analyseDataAudio = response.data.audio;
                        currentObj.loadFormats();
                    } else {
                        currentObj.status = response.data.status;
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 2000);
                    }
                });
        },

        createStream() {
            this.loadingCreate = true;
            let currentObj = this;
            axios
                .post("stream/create", {
                    transcoderId: this.transcoderId,
                    stream_src: this.stream_src,
                    videoIndex: this.videoIndex,
                    inputCodec: this.inputCodec,
                    dst3: this.dst3,
                    dst3_kvality: this.dst3_kvality,
                    dst2: this.dst2,
                    dst2_kvality: this.dst2_kvality,
                    dst1_kvality: this.dst1_kvality,
                    dst1: this.dst1,
                    formatCode: this.formatCode,
                    audioIndex: this.audioIndex,
                    stream_name: this.stream_name
                })
                .then(function(response) {
                    currentObj.loadingCreate = false;
                    currentObj.resetDialog();
                    currentObj.loadStreams();
                    currentObj.status = response.data;
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                });
        },

        // editace streamu
        editStream() {
            this.loadingEdit = true;
            let currentObj = this;
            axios
                .post("stream/edit", {
                    streamId: this.streamId,
                    transcoderId: this.transcoderId,
                    stream_src: this.streamEdit.src,
                    videoIndex: this.videoIndex,
                    inputCodec: this.inputCodec,
                    dst3: this.streamEdit.dst3,
                    dst3_kvality: this.dst3_kvality,
                    dst2: this.streamEdit.dst2,
                    dst2_kvality: this.dst2_kvality,
                    dst1_kvality: this.dst1_kvality,
                    dst1: this.streamEdit.dst,
                    formatCode: this.formatCode,
                    audioIndex: this.audioIndex,
                    stream_name: this.streamEdit.nazev
                })
                .then(function(response) {
                    currentObj.loadingEdit = false;
                    currentObj.resetDialog();
                    currentObj.loadStreams();

                    currentObj.status = response.data;
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                });
        },
        dialogClose() {
            this.editScriptDialog = false;
            this.createDialog = false;
            this.editStreamDialog = false;
        },
        resetDialog() {
            this.streamEditScript = null;
            this.editScriptDialog = false;
            this.createDialog = false;
            this.editStreamDialog = false;
            this.streamEdit = [];
            this.videoIndex = null;
            this.stream_name = null;
            this.dst3 = null;
            this.dst3_kvality = null;
            this.dst2 = null;
            this.dst2_kvality = null;
            this.dst1_kvality = null;
            this.dst1 = null;
            this.formatCode = null;
            this.audioIndex = null;
            this.videoInputFormat = null;
            this.stream_name = null;
        }
    },
    watch: {}
};
</script>
