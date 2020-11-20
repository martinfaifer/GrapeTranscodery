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
                                color="#586776"
                                link
                                :to="'transcoder/' + transcoder.ip"
                                :elevation="hover ? 24 : 0"
                                class="mx-auto ma-0 transition-fast-in-fast-out"
                                height="250"
                                width="450"
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
                                     <v-row class="body-2 mt-1">
                                        <div>
                                            <span class="white--text">
                                                Počet nefunkčních streamů:
                                                <span class="ml-1">
                                                    <strong class="red--text">
                                                        {{
                                                            transcoder.streamIssueCount
                                                        }}
                                                    </strong>
                                                </span>
                                            </span>
                                        </div>
                                    </v-row>
                                    <!-- telemtrie -->
                                    <v-row class="body-2 mt-1">
                                        <div>
                                            <v-row
                                                class="justify-center body-2"
                                            >
                                                <!-- Encoding -->
                                                <v-row
                                                    class="ml-1"
                                                    v-for="encoder in transcoder
                                                        .telemetrie.gpu"
                                                    v-bind:key="encoder.id"
                                                    v-show="
                                                        encoder.encoder_util
                                                    "
                                                >
                                                    <div
                                                        v-if="
                                                            encoder.encoder_util <=
                                                                '75 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2 "
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                encoder.encoder_util
                                                            "
                                                            color="green"
                                                        >
                                                            <small>
                                                                Encoder
                                                                {{
                                                                    encoder.encoder_util
                                                                }}
                                                            </small>
                                                        </v-progress-circular>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            encoder.encoder_util >
                                                                '75 %' &&
                                                                encoder.encoder_util <
                                                                    '88 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2 "
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                encoder.encoder_util
                                                            "
                                                            color="orange"
                                                            ><small>
                                                                Encoder
                                                                {{
                                                                    encoder.encoder_util
                                                                }}
                                                            </small></v-progress-circular
                                                        >
                                                    </div>
                                                    <div
                                                        v-if="
                                                            encoder.encoder_util >=
                                                                '88 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                encoder.encoder_util
                                                            "
                                                            color="red"
                                                            ><small>
                                                                Encoder
                                                                {{
                                                                    encoder.encoder_util
                                                                }}
                                                            </small></v-progress-circular
                                                        >
                                                    </div>
                                                </v-row>
                                                <v-spacer></v-spacer>
                                                <!-- Decoder -->
                                                <v-row
                                                    class="ml-12"
                                                    v-for="decoder in transcoder
                                                        .telemetrie.gpu"
                                                    v-bind:key="decoder.id"
                                                    v-show="
                                                        decoder.decoder_util
                                                    "
                                                >
                                                    <div
                                                        v-if="
                                                            decoder.decoder_util <=
                                                                '75 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                decoder.decoder_util
                                                            "
                                                            color="green"
                                                        >
                                                            <small>
                                                                Decoder
                                                                {{
                                                                    decoder.decoder_util
                                                                }}
                                                            </small>
                                                        </v-progress-circular>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            decoder.decoder_util >
                                                                '75 %' &&
                                                                decoder.decoder_util <
                                                                    '88 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                decoder.decoder_util
                                                            "
                                                            color="orange"
                                                        >
                                                            <small>
                                                                Decoder
                                                                {{
                                                                    decoder.decoder_util
                                                                }}
                                                            </small></v-progress-circular
                                                        >
                                                    </div>
                                                    <div
                                                        v-if="
                                                            decoder.decoder_util >=
                                                                '88 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                decoder.decoder_util
                                                            "
                                                            color="red"
                                                        >
                                                            <small>
                                                                Decoder
                                                                {{
                                                                    decoder.decoder_util
                                                                }}
                                                            </small></v-progress-circular
                                                        >
                                                    </div>
                                                </v-row>
                                                <!-- GPU -->

                                                <v-row
                                                    class="ml-12"
                                                    v-for="gpu in transcoder
                                                        .telemetrie.gpu"
                                                    v-bind:key="gpu.id"
                                                    v-show="gpu.gpu_util"
                                                >
                                                    <div
                                                        v-if="
                                                            gpu.gpu_util <=
                                                                '75 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                gpu.gpu_util
                                                            "
                                                            color="green"
                                                        >
                                                            <small>
                                                                GPU
                                                                {{
                                                                    gpu.gpu_util
                                                                }}
                                                            </small>
                                                        </v-progress-circular>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            gpu.gpu_util >
                                                                '75 %' &&
                                                                gpu.gpu_util <
                                                                    '88 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                gpu.gpu_util
                                                            "
                                                            color="orange"
                                                        >
                                                            <small>
                                                                GPU
                                                                {{
                                                                    gpu.gpu_util
                                                                }}
                                                            </small></v-progress-circular
                                                        >
                                                    </div>
                                                    <div
                                                        v-if="
                                                            gpu.gpu_util >=
                                                                '88 %'
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="
                                                                gpu.gpu_util
                                                            "
                                                            color="red"
                                                        >
                                                            <small>
                                                                GPU
                                                                {{
                                                                    gpu.gpu_util
                                                                }}
                                                            </small></v-progress-circular
                                                        >
                                                    </div>
                                                </v-row>
                                            </v-row>
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
        telemetrie: null,
        transcoders: null,
        gpuStat: "",
        ramUsage: "",
        ramTotal: "",
        ramUsed: "",
        ramPercent: "",
        loadingInterval: null
    }),

    created() {
        this.getTranscoders();
    },
    methods: {
        getTranscoders() {
            window.axios.get("transcoders/telemetrie").then(response => {
                this.transcoders = response.data.data;
                if (this.transcoders[0].telemetrie.gpu) {
                    if (
                        typeof this.transcoders[0].telemetrie.gpu
                            .fb_memory_usage !== "undefined"
                    ) {
                        this.gpuStat = response.data;
                        if (
                            typeof this.transcoders[0].telemetrie.gpu
                                .fb_memory_usage !== "undefined"
                        ) {
                            this.ramUsage = this.transcoders[0].telemetrie.gpu.fb_memory_usage;
                            this.ramTotal = this.ramUsage.total.replace(
                                " MiB",
                                ""
                            );
                            this.ramUsed = this.ramUsage.used.replace(
                                " MiB",
                                ""
                            );
                            this.ramPercent =
                                (this.ramUsed * 100) / this.ramTotal;
                        }
                    }
                }
            });
        }
    },

    mounted() {
        this.loadingInterval = setInterval(
            function() {
                this.getTranscoders();
            }.bind(this),
            2000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.loadingInterval);
    }
};
</script>
