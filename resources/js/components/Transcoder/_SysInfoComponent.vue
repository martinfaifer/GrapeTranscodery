<template>
    <div>
        <v-container>
            <!-- konec zobrazeni nazvu encoderu -->
            <v-row class="justify-center body-2">
                <!-- Využitá RAM -->
                <v-spacer></v-spacer>
                <v-row>
                    <div v-if="ramPercent < '75'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="ramPercent"
                            color="green"
                        >
                            <small>
                                <strong>
                                    Celkem RAM {{ ramUsage.total }}
                                </strong>

                                <v-divider></v-divider>
                                <strong> Volná RAM {{ ramUsage.free }} </strong>
                            </small>
                        </v-progress-circular>
                    </div>
                    <div v-if="ramPercent >= '75' && ramPercent < '88'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="ramPercent"
                            color="orange"
                        >
                            <small>
                                <strong>
                                    Celkem RAM {{ ramUsage.total }}
                                </strong>

                                <v-divider></v-divider>
                                <strong> Volná RAM {{ ramUsage.free }} </strong>
                            </small></v-progress-circular
                        >
                    </div>
                    <div v-if="ramPercent >= '88'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="ramPercent"
                            color="red"
                        >
                            <small>
                                <strong>
                                    Celkem RAM {{ ramUsage.total }}
                                </strong>

                                <v-divider></v-divider>
                                <strong> Volná RAM {{ ramUsage.free }} </strong>
                            </small></v-progress-circular
                        >
                    </div>
                </v-row>
                <!-- Encoding -->
                <v-spacer></v-spacer>
                <v-row
                    v-for="encoder in gpuStat.gpu"
                    v-bind:key="encoder.id"
                    v-show="encoder.encoder_util"
                >
                    <div v-if="encoder.encoder_util <= '75 %'">
                        <v-progress-circular
                            class="mt-2 "
                            :size="150"
                            :width="4"
                            :value="encoder.encoder_util"
                            color="green"
                        >
                            <strong>
                                Encoder
                                {{ encoder.encoder_util }}
                            </strong>
                        </v-progress-circular>
                    </div>
                    <div
                        v-if="
                            encoder.encoder_util > '75 %' &&
                                encoder.encoder_util < '88 %'
                        "
                    >
                        <v-progress-circular
                            class="mt-2 "
                            :size="150"
                            :width="4"
                            :value="encoder.encoder_util"
                            color="orange"
                            ><strong>
                                Encoder
                                {{ encoder.encoder_util }}
                            </strong></v-progress-circular
                        >
                    </div>
                    <div v-if="encoder.encoder_util >= '88 %'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="encoder.encoder_util"
                            color="red"
                            ><strong>
                                Encoder
                                {{ encoder.encoder_util }}
                            </strong></v-progress-circular
                        >
                    </div>
                </v-row>
                <v-spacer></v-spacer>
                <!-- Decoder -->
                <v-row
                    v-for="decoder in gpuStat.gpu"
                    v-bind:key="decoder.id"
                    v-show="decoder.decoder_util"
                >
                    <div v-if="decoder.decoder_util <= '75 %'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="decoder.decoder_util"
                            color="green"
                        >
                            <strong>
                                Decoder
                                {{ decoder.decoder_util }}
                            </strong>
                        </v-progress-circular>
                    </div>
                    <div
                        v-if="
                            decoder.decoder_util > '75 %' &&
                                decoder.decoder_util < '88 %'
                        "
                    >
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="decoder.decoder_util"
                            color="orange"
                        >
                            <strong>
                                Decoder
                                {{ decoder.decoder_util }}
                            </strong></v-progress-circular
                        >
                    </div>
                    <div v-if="decoder.decoder_util >= '88 %'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="decoder.decoder_util"
                            color="red"
                        >
                            <strong>
                                Decoder
                                {{ decoder.decoder_util }}
                            </strong></v-progress-circular
                        >
                    </div>
                </v-row>
                <v-spacer></v-spacer>
                <!-- GPU -->

                <v-row
                    v-for="gpu in gpuStat.gpu"
                    v-bind:key="gpu.id"
                    v-show="gpu.gpu_util"
                >
                    <div v-if="gpu.gpu_util <= '75 %'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="gpu.gpu_util"
                            color="green"
                        >
                            <strong> GPU {{ gpu.gpu_util }} </strong>
                        </v-progress-circular>
                    </div>
                    <div v-if="gpu.gpu_util > '75 %' && gpu.gpu_util < '88 %'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="gpu.gpu_util"
                            color="orange"
                        >
                            <strong>
                                GPU {{ gpu.gpu_util }}
                            </strong></v-progress-circular
                        >
                    </div>
                    <div v-if="gpu.gpu_util >= '88 %'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="gpu.gpu_util"
                            color="red"
                        >
                            <strong>
                                GPU {{ gpu.gpu_util }}
                            </strong></v-progress-circular
                        >
                    </div>
                </v-row>
                <v-spacer></v-spacer>
                <!-- Teplota GPU -->

                <v-row
                    v-for="gpuTemp in gpuStat.gpu"
                    v-bind:key="gpuTemp.id"
                    v-show="gpuTemp.gpu_temp"
                >
                    <div v-if="gpuTemp.gpu_temp <= '70 C'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="gpuTemp.gpu_temp"
                            color="green"
                        >
                            <strong>
                                GPU Temp
                                {{ gpuTemp.gpu_temp }}
                            </strong>
                        </v-progress-circular>
                    </div>
                    <div
                        v-if="
                            gpuTemp.gpu_temp > '70 C' &&
                                gpuTemp.gpu_temp < '88 C'
                        "
                    >
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="gpuTemp.gpu_temp"
                            color="orange"
                            ><strong>
                                GPU Temp
                                {{ gpuTemp.gpu_temp }}
                            </strong></v-progress-circular
                        >
                    </div>
                    <div v-if="gpuTemp.gpu_temp >= '88 C'">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="gpuTemp.gpu_temp"
                            color="red"
                            ><strong>
                                GPU Temp
                                {{ gpuTemp.gpu_temp }}
                            </strong></v-progress-circular
                        >
                    </div>
                </v-row>
                <v-spacer></v-spacer>
                <!-- Power -->

                <v-row
                    v-for="powerDraw in gpuStat.gpu"
                    v-bind:key="powerDraw.id"
                    v-show="powerDraw.power_draw"
                >
                    <v-progress-circular
                        class="mt-2"
                        :size="150"
                        :width="4"
                        :value="powerDraw.power_draw"
                        color="green"
                    >
                        <strong> Power {{ powerDraw.power_draw }} </strong>
                    </v-progress-circular>
                </v-row>
            </v-row>
        </v-container>
    </div>
</template>
<script>
export default {
    data() {
        return {
            gpuStat: "",
            ramUsage: "",
            ramTotal: "",
            ramUsed: "",
            ramPercent: "",
            loadingInterval: null
        };
    },
    created() {
        this.loadUtilisation();
    },

    methods: {
        loadUtilisation() {
            // total used
            let currentObj = this;
            axios
                .post("gpuStat", {
                    transcoderIp: this.$route.params.ip
                })
                .then(function(response) {
                    if (response.data.gpu) {
                        if (
                            typeof response.data.gpu.fb_memory_usage !==
                            "undefined"
                        ) {
                            currentObj.gpuStat = response.data;
                            if (
                                typeof response.data.gpu.fb_memory_usage !==
                                "undefined"
                            ) {
                                currentObj.ramUsage =
                                    response.data.gpu.fb_memory_usage;
                                currentObj.ramTotal = currentObj.ramUsage.total.replace(
                                    " MiB",
                                    ""
                                );
                                currentObj.ramUsed = currentObj.ramUsage.used.replace(
                                    " MiB",
                                    ""
                                );
                                currentObj.ramPercent =
                                    (currentObj.ramUsed * 100) /
                                    currentObj.ramTotal;
                            }
                        }
                    }
                });
        },

        // parent fn pro reset vsech hodnot do defaultu
        destroyEveryThing() {
            this.gpuStat = "";
            this.ramUsage = "";
            this.ramTotal = "";
            this.ramUsed = "";
            this.ramPercent = "";
        }
    },

    mounted() {
        this.loadingInterval = setInterval(
            function() {
                this.loadUtilisation();
            }.bind(this),
            2000
        );
    },
    watch: {
        $route(to, from) {
            this.loadUtilisation();
            this.destroyEveryThing();
            this.loadingInterval = null;
        }
    },

    // fn pro "zniceni" intervalu pro reactivitu
    beforeDestroy: function() {
        clearInterval(this.loadingInterval);
    }
};
</script>
