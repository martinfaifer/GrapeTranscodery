<template>
    <v-main class="mt-12">
        <v-row v-show="transcoders === null" class="justify-center">
            <span class="mt-12">
                <i
                    style="color:#596776"
                    class="fas fa-spinner fa-pulse fa-5x"
                ></i>
            </span>
        </v-row>
        <v-container v-if="transcoders != null" fluid>
            <span v-for="transcoder in transcoders" :key="transcoder.id">
                <v-alert
                    v-if="transcoder.status === 'offline'"
                    type="error"
                    class="mx-auto"
                >
                    {{ transcoder.name }} nefunguje
                </v-alert>
            </span>
        </v-container>
        <v-container fluid>
            <v-row>
                <v-row class="mx-auto mt-1 mr-1 ma-1">
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
                                :class="{ 'on-hover': hover }"
                                height="250"
                                width="550"
                            >
                                <v-card-text class="ml-4 text-center">
                                    <v-row class="headline">
                                        <div class="white--text">
                                            {{ transcoder.name }}
                                        </div>
                                    </v-row>
                                    <v-row class="mt-3 body-2">
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
                                    <v-row class="mt-1 body-2">
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
                                    <v-row class="mt-1 body-2">
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
                                    <!-- telemetrie -->
                                    <v-row class="mt-1 body-2">
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
                                                            convert_string_to_int_and_remove_percent(
                                                                encoder.encoder_util
                                                            ) <= 75
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
                                                            convert_string_to_int_and_remove_percent(
                                                                encoder.encoder_util
                                                            ) > 75 &&
                                                                convert_string_to_int_and_remove_percent(
                                                                    encoder.encoder_util
                                                                ) < 88
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
                                                            convert_string_to_int_and_remove_percent(
                                                                encoder.encoder_util
                                                            ) >= 88
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
                                                            convert_string_to_int_and_remove_percent(
                                                                decoder.decoder_util
                                                            ) <= 75
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
                                                            convert_string_to_int_and_remove_percent(
                                                                decoder.decoder_util
                                                            ) > 75 &&
                                                                convert_string_to_int_and_remove_percent(
                                                                    decoder.decoder_util
                                                                ) < 88
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
                                                            convert_string_to_int_and_remove_percent(
                                                                decoder.decoder_util
                                                            ) >= 88
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
                                                            convert_string_to_int_and_remove_percent(
                                                                gpu.gpu_util
                                                            ) <= 75
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
                                                            convert_string_to_int_and_remove_percent(
                                                                gpu.gpu_util
                                                            ) > 75 &&
                                                                convert_string_to_int_and_remove_percent(
                                                                    gpu.gpu_util
                                                                ) < 88
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
                                                            convert_string_to_int_and_remove_percent(
                                                                gpu.gpu_util
                                                            ) >= 88
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

                                                <v-spacer></v-spacer>
                                                <!-- RAM -->

                                                <v-row
                                                    class="ml-12"
                                                    v-for="ram in transcoder
                                                        .telemetrie.gpu"
                                                    v-bind:key="ram.id"
                                                    v-show="ram.used"
                                                >
                                                    <div
                                                        v-if="
                                                            percent(
                                                                ram.used,
                                                                ram.total
                                                            ) < 75
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="50"
                                                            color="green"
                                                        >
                                                            <small
                                                                class="ml-3 mr-3"
                                                            >
                                                                Volná RAM
                                                                {{ ram.free }}
                                                            </small>
                                                        </v-progress-circular>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            percent(
                                                                ram.used,
                                                                ram.total
                                                            ) >= 75 &&
                                                                percent(
                                                                    ram.used,
                                                                    ram.total
                                                                ) < 88
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="90"
                                                            color="orange"
                                                        >
                                                            <small
                                                                class="ml-3 mr-3"
                                                            >
                                                                Volná RAM
                                                                {{ ram.free }}
                                                            </small>
                                                        </v-progress-circular>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            percent(
                                                                ram.used,
                                                                ram.total
                                                            ) >= 88
                                                        "
                                                    >
                                                        <v-progress-circular
                                                            class="mt-2"
                                                            :size="100"
                                                            :width="2"
                                                            :value="50"
                                                            color="red"
                                                        >
                                                            <small
                                                                class="ml-3 mr-3"
                                                            >
                                                                Volná RAM
                                                                {{ ram.free }}
                                                            </small>
                                                        </v-progress-circular>
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
            });
        },

        percent(used, total) {
            if (used && total) {
                let ramUsage = used;
                let ramTotal = total;
                let ramPercent;
                ramUsage = ramUsage.replace(" MiB", "");
                ramTotal = ramTotal.replace(" MiB", "");

                ramPercent = (parseInt(ramUsage) * 100) / parseInt(ramTotal);
                return parseInt(ramPercent);
            }
        },

        convert_string_to_int_and_remove_percent(string) {
            if (string) {
                string = string.replace(" %", "");
                return parseInt(string);
            }
        }
    },

    mounted() {
        this.loadingInterval = setInterval(
            function() {
                this.getTranscoders();
            }.bind(this),
            3000
        );
    },

    watch: {},
    beforeDestroy: function() {
        clearInterval(this.loadingInterval);
    }
};
</script>
