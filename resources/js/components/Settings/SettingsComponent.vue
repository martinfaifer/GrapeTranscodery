<template>
    <div>
        <settingsnavigation-component></settingsnavigation-component>

        <v-main>
            <transition name="fade" mode="out-in">
                <router-view class="mt-12"> </router-view>
            </transition>
        </v-main>
    </div>
</template>
<script>
import SettingsNavigationComponent from "./SettingsNavigation";
export default {
    data() {
        return {};
    },

    components: {
        "settingsnavigation-component": SettingsNavigationComponent
    }
};
</script>
