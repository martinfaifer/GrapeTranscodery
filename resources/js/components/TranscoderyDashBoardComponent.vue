<template>
    <v-main class="mt-12">
        <v-container fluid>
            <v-row>
                <v-row class="mx-auto mt-1 ma-1 mr-1">
                    <v-col
                        v-for="transcoder in transcoders"
                        :key="transcoder.id"
                        class="mt-2"
                    >
                        <v-hover v-slot:default="{ hover }">
                            <v-card
                                color="#777E8B"
                                link
                                :to="'transcoder/' + transcoder.ip"
                                :elevation="hover ? 24 : 0"
                                class="mx-auto ma-0 transition-fast-in-fast-out"
                                height="250"
                                width="250"
                            >
                                <v-card-text class="text-center ml-4">
                                    <v-row class="headline">
                                        <div class="white--text">
                                            {{ transcoder.name }}
                                        </div>
                                    </v-row>
                                    <v-row class="body-2 mt-3">
                                        <div>
                                            <span class="white--text">
                                                status:
                                                <span class="ml-1">
                                                    <strong
                                                        :class="{
                                                            'green--text':
                                                                transcoder.status ==
                                                                'success',
                                                            'red--text':
                                                                transcoder.status ==
                                                                'offline',
                                                            'orange--text':
                                                                transcoder.status ==
                                                                'waiting'
                                                        }"
                                                    >
                                                        <span
                                                            v-if="
                                                                transcoder.status ==
                                                                    'success'
                                                            "
                                                        >
                                                            funguje
                                                        </span>

                                                        <span
                                                            v-if="
                                                                transcoder.status ==
                                                                    'offline'
                                                            "
                                                        >
                                                            výpadek
                                                        </span>

                                                        <span
                                                            v-if="
                                                                transcoder.status ==
                                                                    'waiting'
                                                            "
                                                        >
                                                            čeká
                                                        </span>
                                                    </strong>
                                                </span>
                                            </span>
                                        </div>
                                    </v-row>
                                    <v-row class="body-2 mt-1">
                                        <div>
                                            <span class="white--text">
                                                Počet streamů:
                                                <span class="ml-1">
                                                    <strong>
                                                        {{
                                                            transcoder.streamCount
                                                        }}
                                                    </strong>
                                                </span>
                                            </span>
                                        </div>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </v-hover>
                    </v-col>
                </v-row>
            </v-row>
        </v-container>
    </v-main>
</template>

<script>
export default {
    data: () => ({
        transcoders: null
    }),

    created() {
        this.getTranscoders();
    },
    methods: {
        getTranscoders() {
            window.axios.get("transcoders").then(response => {
                this.transcoders = response.data.data;
            });
        }
    },

    mounted() {},
    watch: {}
};
</script>
