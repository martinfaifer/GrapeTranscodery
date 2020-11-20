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
                        label="Vyhledat stream"
                        single-line
                        hide-details
                    ></v-text-field>
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

                    <template v-slot:item.transcoder="{ item }">
                        <router-link target="_blank" :to="'transcoder/' + item.transcoderIp">{{
                            item.transcoder
                        }}</router-link>
                    </template>
                </v-data-table>
            </v-card>
        </v-container>
    </v-main>
</template>
<script>
export default {
    data: () => ({
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
            { text: "Status", value: "status" }
        ]
    }),
    created() {
        this.loadStreams();
    },
    methods: {
        loadStreams() {
            let currentObj = this;
            window.axios.get("streams").then(response => {
                currentObj.streams = response.data;
            });
        }
    }
};
</script>
