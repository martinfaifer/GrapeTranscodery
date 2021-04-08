<template>
    <v-main>
        <transition name="slide-fade" mode="out-in">
            <v-snackbar
                v-if="alert != null"
                :timeout="-1"
                :value="true"
                bottom
                :color="alert.status"
                right
                transition="slide-x-transition"
            >
                <v-icon v-if="alert.status === 'success'">
                    mdi-check
                </v-icon>

                <v-icon v-if="alert.status === 'error'">
                    mdi-close
                </v-icon>

                <v-icon
                    v-if="alert.status === 'warning' || alert.status === 'info'"
                >
                    mdi-exclamation
                </v-icon>
                <strong>
                    {{ alert.msg }}
                </strong>
            </v-snackbar>
        </transition>
    </v-main>
</template>

<script>
export default {
    computed: {
        alert() {
            return this.$store.state.alerts;
        }
    },
    data() {
        return {};
    },

    created() {},
    watch: {
        alert() {
            if (this.alert != null) {
                setTimeout(
                    function() {
                        this.$store.state.alerts = null;
                    }.bind(this),
                    4000
                );
            } else {
                return;
            }
        }
    }
};
</script>
