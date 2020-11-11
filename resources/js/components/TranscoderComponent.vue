<template>
    <div class="mt-12">

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

        };
    },

    components: {
        "sysinfo-component": SysInfoComponent,
        "transcoderstreams-component": TranscoderStreamsComponent
    },
    created() {},

    methods: {},

    watch: {
        $route(to, from) {}
    }
};
</script>
