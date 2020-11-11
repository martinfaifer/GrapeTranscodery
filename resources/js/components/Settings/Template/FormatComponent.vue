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
                    <v-btn outlined color="green" class="elevation-0" small>
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
                                @click="editFormat(item.id)"
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
    </v-main>
</template>
<script>
import NotificationComponent from "../../Notifications/NotificationComponent";
export default {
    data() {
        return {
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
    components: {
        "notification-component": NotificationComponent
    },
    created() {
        this.loadFormats();
    },

    methods: {
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
        editFormat(id) {},
        deleteFormat(id) {}
    }
};
</script>
