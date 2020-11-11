<template>
    <v-main>
    <v-container class="mt-12 ml-12" fluid>
        <v-row class="ml-12">
            <format-component class="ml-12 mt-12"></format-component>
            <kvality-component class="mt-12"></kvality-component>
        </v-row>
    </v-container>
    </v-main>
</template>
<script>
import FormatComponent from "./FormatComponent";
import KvalityComponent from "./KvalityComponent";
export default {
    data() {
        return {};
    },

    components: {
        "format-component": FormatComponent,
        "kvality-component": KvalityComponent
    }
};
</script>
