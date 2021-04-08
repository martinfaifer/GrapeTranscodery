<template>
    <div class="mt-12">
        <v-alert
            v-if="transcoderStatus === 'offline'"
            type="error"
            class="mt-1 ml-1 mr-1"
        >
            Transcodér je offline
        </v-alert>
        <!-- Component pro výpis HW informací o daném transcoderu -->
        <sysinfo-component></sysinfo-component>
        <!-- component pro výpis vsech streamů -->
        <transcoderstreams-component></transcoderstreams-component>
    </div>
</template>
<script>
import SysInfoComponent from "./Transcoder/_SysInfoComponent";
import TranscoderStreamsComponent from "./Transcoder/_TranscoderStreamComponent";
export default {
    data() {
        return {
            transcoderStatus: "online",
            loadingInterval: null
        };
    },

    components: {
        "sysinfo-component": SysInfoComponent,
        "transcoderstreams-component": TranscoderStreamsComponent
    },
    created() {
        this.loadTranscoderStatus();
    },

    methods: {
        loadTranscoderStatus() {
            axios
                .post("transcoder/status", {
                    ip: this.$route.params.ip
                })
                .then(response => {
                    this.transcoderStatus = response.data;
                });
        }
    },

    mounted() {
        this.loadingInterval = setInterval(
            function() {
                this.loadTranscoderStatus();
            }.bind(this),
            2000
        );
    },

    watch: {
        $route(to, from) {
            this.loadTranscoderStatus();
        }
    },
    beforeDestroy: function() {
        clearInterval(this.loadingInterval);
    }
};
</script>
