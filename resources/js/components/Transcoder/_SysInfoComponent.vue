<template>
    <div>
        <v-container fluid>
            <!-- konec zobrazeni nazvu encoderu -->
            <v-row class="justify-center body-2">
                <!-- Využitá RAM -->
                <v-spacer></v-spacer>
                <v-row>
                    <div v-if="parseInt(ramPercent) < 75">
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
                    <div v-if="parseInt(ramPercent) >= 75 && parseInt(ramPercent) < 88">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="parseInt(ramPercent)"
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
                    <div v-if="parseInt(ramPercent) >= 88">
                        <v-progress-circular
                            class="mt-2"
                            :size="150"
                            :width="4"
                            :value="parseInt(ramPercent)"
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
                    <div v-if="encoder.encoder_util <= '75 %' ">
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
                    <div v-if="decoder.decoder_util <= '75 %' ">
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
                    <div v-if="gpu.gpu_util <= '75 %' ">
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

            <!-- CPU per core blok -->
            <v-btn v-if="!cpuStat" class="mt-3" text outlined small color="info" @click="cpuStat = !cpuStat">
                Zobrazit využití CPU
            </v-btn>
            <v-btn v-if="cpuStat" class="mt-3" text outlined small color="info" @click="cpuStat = !cpuStat">
                Skrýt využití CPU
            </v-btn>

            <div class="justify-center body-2 mt-3 ml-3 mt-3" v-show="cpuStat">
                <div v-for="(item, index) in cpus" :key="index">
                    <v-row class="text-center">
                        <v-col cols="12" sm="12" md="3" lg="3">
                            <strong>
                                {{ index }} - uživatel - {{ item.user }} %
                            </strong>
                            <v-progress-linear
                                v-if="parseInt(item.user) <= 50"
                                class="mt-3"
                                color="success"
                                :buffer-value="parseInt(item.user)"
                                :value="parseInt(item.user)"
                                stream
                            ></v-progress-linear>
                            <v-progress-linear
                                v-if="parseInt(item.user) >= 51 && parseInt(item.user) <= 90"
                                class="mt-3"
                                color="orange"
                                :buffer-value="parseInt(item.user)"
                                :value="parseInt(item.user)"
                                stream
                            ></v-progress-linear>

                            <v-progress-linear
                                v-if="parseInt(item.user) >= 91"
                                class="mt-3"
                                color="error"
                                :buffer-value="parseInt(item.user)"
                                :value="parseInt(item.user)"
                                stream
                            ></v-progress-linear>
                        </v-col>

                        <v-col cols="12" sm="12" md="3" lg="3">
                            <strong>
                                {{ index }} - nice - {{ item.nice }} %
                            </strong>
                            <v-progress-linear
                                v-if="parseInt(item.nice) <= 50"
                                class="mt-3"
                                color="success"
                                :buffer-value="parseInt(item.nice)"
                                :value="parseInt(item.nice)"
                                stream
                            ></v-progress-linear>
                            <v-progress-linear
                                v-if="parseInt(item.nice) >= 51 && parseInt(item.nice) <= 90"
                                class="mt-3"
                                color="orange"
                                :buffer-value="parseInt(item.nice)"
                                :value="parseInt(item.nice)"
                                stream
                            ></v-progress-linear>

                            <v-progress-linear
                                v-if="parseInt(item.nice) >= 91"
                                class="mt-3"
                                color="error"
                                :buffer-value="parseInt(item.nice)"
                                :value="parseInt(item.nice)"
                                stream
                            ></v-progress-linear>
                        </v-col>

                        <v-col cols="12" sm="12" md="3" lg="3">
                            <strong>
                                {{ index }} - systém - {{ item.sys }} %
                            </strong>
                            <v-progress-linear
                                v-if="parseInt(item.sys) <= 50"
                                class="mt-3"
                                color="success"
                                :buffer-value="parseInt(item.sys)"
                                :value="parseInt(item.sys)"
                                stream
                            ></v-progress-linear>
                            <v-progress-linear
                                v-if="parseInt(item.sys) >= 51 && parseInt(item.sys) <= 90"
                                class="mt-3"
                                color="orange"
                                :buffer-value="parseInt(item.sys)"
                                :value="parseInt(item.sys)"
                                stream
                            ></v-progress-linear>

                            <v-progress-linear
                                v-if="parseInt(item.sys) >= 91"
                                class="mt-3"
                                color="error"
                                :buffer-value="parseInt(item.sys)"
                                :value="parseInt(item.sys)"
                                stream
                            ></v-progress-linear>
                        </v-col>

                        <v-col cols="12" sm="12" md="3" lg="3">
                            <strong>
                                {{ index }} - idle - {{ item.idle }} %
                            </strong>
                            <v-progress-linear
                                v-if="parseInt(item.idle) === 100"
                                class="mt-3"
                                color="success"
                                :buffer-value="parseInt(item.idle)"
                                :value="parseInt(item.idle)"
                                stream
                            ></v-progress-linear>
                            <v-progress-linear
                                v-if="parseInt(item.idle) < 100 && parseInt(item.idle) >= 30"
                                class="mt-3"
                                color="green lighten-2"
                                :buffer-value="parseInt(item.idle)"
                                :value="parseInt(item.idle)"
                                stream
                            ></v-progress-linear>
                            <v-progress-linear
                                v-if="parseInt(item.idle) < 30"
                                class="mt-3"
                                color="error"
                                :buffer-value="parseInt(item.idle)"
                                :value="parseInt(item.idle)"
                                stream
                            ></v-progress-linear>
                        </v-col>
                    </v-row>
                </div>
            </div>
        </v-container>
    </div>
</template>
<script>
export default {
    data() {
        return {
            cpuSparklines: new Array(),
            cpus: new Array(),
            gpuStat: "",
            ramUsage: "",
            ramTotal: "",
            ramUsed: "",
            ramPercent: "",
            loadingInterval: null,
            cpuStat: false
        };
    },
    created() {
        this.loadUtilisation();
    },
    methods: {
        async loadUtilisation() {
            await axios
                .post("gpuStat", {
                    transcoderIp: this.$route.params.ip
                })
                .then(response => {
                    if (response.data.cpu) {
                        this.cpus = response.data.cpu;
                    }

                    if (response.data.gpu) {
                        if (
                            typeof response.data.gpu.fb_memory_usage !==
                            "undefined"
                        ) {
                            this.gpuStat = response.data;
                            if (
                                typeof response.data.gpu.fb_memory_usage !==
                                "undefined"
                            ) {
                                this.ramUsage =
                                    response.data.gpu.fb_memory_usage;
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
