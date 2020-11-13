<template>
    <v-main>
        <!-- notifikace -->
        <notification-component
            v-if="status != null"
            :status="status"
        ></notification-component>
        <v-container class="mt-12" fluid>
            <v-card flat color="transparent">
                <v-card-title>
                    <v-text-field
                        color="#777E8B"
                        autofocus
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Vyhledat uživatele"
                        single-line
                        hide-details
                    ></v-text-field>
                    <v-spacer></v-spacer>
                    <v-btn
                        @click="openCreateDialog()"
                        outlined
                        color="green"
                        class="elevation-0"
                        small
                    >
                        <strong>
                            Nový uživatel
                        </strong>
                    </v-btn>
                </v-card-title>
                <div v-show="users != null">
                    <v-data-table
                        dense
                        :search="search"
                        :headers="headers"
                        :items="users"
                        class="elevation-0 body-2"
                    >
                        <template v-slot:item.akce="{ item }">
                            <!-- mdi-play -->
                            <v-icon
                                @click="openEditDialog(item.id)"
                                small
                                color="green"
                                >mdi-pencil</v-icon
                            >

                            <!-- mdi-stop -->
                            <v-icon
                                @click="deleteUser(item.id)"
                                small
                                color="red"
                                >mdi-delete</v-icon
                            >
                        </template>
                    </v-data-table>
                </div>
            </v-card>
        </v-container>

        <!-- DIALOGY -->

        <!-- CREATE DIALOG -->
        <v-row justify="center">
            <v-dialog v-model="createDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title class="headline text-center">
                        Založení uživatele
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="jmeno"
                                        label="Jméno"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="mail"
                                        label="E-mail"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="heslo"
                                        label="Heslo"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-autocomplete
                                        v-model="role"
                                        :items="roles"
                                        item-value="role"
                                        item-text="role"
                                        label="Uživatelská role"
                                    ></v-autocomplete>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="closeDialog()">
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="saveCreateDialog()"
                        >
                            Vytvořit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- KONEC CREATE DIALOGU -->

        <!-- EDIT DIALOG -->

        <v-row justify="center">
            <v-dialog v-model="editDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title class="headline text-center">
                        Editace uživatele
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-text-field
                                        v-model="editUser.name"
                                        label="Jméno"
                                    ></v-text-field>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        v-model="editUser.email"
                                        label="E-mail"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-checkbox
                                        v-model="editHeslo"
                                        label="Editace hesla"
                                    ></v-checkbox>
                                </v-col>
                                <v-col>
                                    <v-text-field
                                        :disabled="editHeslo == false"
                                        v-model="heslo"
                                        label="Heslo"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-autocomplete
                                        v-model="role"
                                        :items="roles"
                                        item-value="role"
                                        item-text="role"
                                        label="Uživatelská role"
                                    ></v-autocomplete>
                                </v-col>
                            </v-row>
                            <v-row cols="12" sm="6" md="6" class="mt-2">
                                <v-col>
                                    <v-autocomplete
                                        v-model="userStatus"
                                        :items="usersStatus"
                                        item-value="status"
                                        label="Status uživatele"
                                    ></v-autocomplete>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="closeDialog()">
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="saveEditDialog()"
                        >
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- KONEC EDIT DIALOGU -->

        <!-- KONEC DIALOGU -->
    </v-main>
</template>
<script>
import NotificationComponent from "../Notifications/NotificationComponent";
export default {
    data() {
        return {
            userRole: null,
            editHeslo: false,
            userId: null,
            usersStatus: ["active", "blocket"],
            userStatus: null,
            editDialog: false,
            editUser: [],
            heslo: null,
            roles: [],
            role: null,
            jmeno: null,
            mail: null,
            createDialog: false,
            search: "",
            status: null,
            users: [],
            headers: [
                {
                    text: "Jméno",
                    align: "start",
                    value: "name"
                },
                { text: "Email", value: "email" },
                { text: "Role", value: "user_role" },
                { text: "Status", value: "user_status" },
                { text: "Akce", sortable: false, value: "akce" }
            ]
        };
    },

    created() {
        this.loadUsers();
        this.loadUser();
    },
    components: {
        "notification-component": NotificationComponent
    },
    methods: {
        loadUser() {
            let currentObj = this;
            window.axios.get("user").then(response => {
                if (response.data.status == "error") {
                    currentObj.userRole = 'nahled';
                } else {
                    if(response.data.user_role != 'admin') {
                        currentObj.$router.push("/");
                    }
                }
            });
        },
        deleteUser(userId) {
            let currentObj = this;
            axios
                .post("user/delete", {
                    userId: userId
                })
                .then(function(response) {
                    currentObj.loadUsers();
                    currentObj.status = response.data;
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                });
        },

        saveEditDialog() {
            let currentObj = this;
            axios
                .post("user/edit", {
                    userId: this.userId,
                    heslo: this.heslo,
                    role: this.role,
                    jmeno: this.editUser.name,
                    mail: this.editUser.email,
                    userStatus: this.userStatus,
                    editHeslo: this.editHeslo
                })
                .then(function(response) {
                    currentObj.editUser = response.data;
                    currentObj.status = response.data;
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                    currentObj.loadUsers();
                    currentObj.loadRoles();
                    currentObj.closeDialog();
                });
        },
        openEditDialog(userId) {
            let currentObj = this;
            axios
                .post("user/search", {
                    userId: userId
                })
                .then(function(response) {
                    currentObj.editUser = response.data;
                    currentObj.userId = userId;
                    currentObj.loadRoles();
                    currentObj.editDialog = true;
                });
        },
        openCreateDialog() {
            this.createDialog = true;
            this.loadRoles();
        },

        loadUsers() {
            let currentObj = this;
            window.axios.get("users").then(response => {
                currentObj.users = response.data;
            });
        },

        loadRoles() {
            let currentObj = this;
            window.axios.get("users/role").then(response => {
                currentObj.roles = response.data;
            });
        },

        closeDialog() {
            this.editHeslo = false;
            this.createDialog = false;
            this.editDialog = false;
            this.editUser = [];
            this.heslo = null;
            this.role = null;
            this.jmeno = null;
            this.mail = null;
        },

        saveCreateDialog() {
            let currentObj = this;
            axios
                .post("user/create", {
                    heslo: this.heslo,
                    role: this.role,
                    jmeno: this.jmeno,
                    mail: this.mail
                })
                .then(function(response) {
                    currentObj.loadUsers();
                    currentObj.status = response.data;
                    currentObj.closeDialog();
                    setTimeout(function() {
                        currentObj.status = null;
                    }, 2000);
                });
        }
    }
};
</script>
