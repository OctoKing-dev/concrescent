<template>
<v-tabs-items :value="subTabIx"
              touchless>
    <v-tab-item value="0">
        <simpleList apiPath="AdminUser"
                    :AddHeaders="listAddHeaders"
                    :RemoveHeaders="listRemoveHeaders"
                    :isEditingItem="uEdit"
                    :actions="listActions"
                    :footerActions="listFooterActions"
                    @edit="editUser"
                    @create="createUser" >
                    
                    <template v-slot:[`item.username`]="{ item }">
                        <v-tooltip right>
                            <template v-slot:activator="{ on, attrs }">
                                <span v-bind="attrs"
                                      v-on="on">
                                    <div :class="{'font-weight-black': item.IsGlobalAdmin, 'font-italic':!item.HasPermsForEvent && !item.IsGlobalAdmin}">
                                        {{item.username}}
                                    </div>                                     
                                </span>
                            </template>
                            {{item.IsGlobalAdmin ? 'Global Administrator.' :'' }}
                            {{!item.HasPermsForEvent && !item.IsGlobalAdmin ?'Doesn\'t have permissions for this event.':'Has permissions for this event.' }}
                        </v-tooltip>
                    </template>
                </simpleList>

        <v-dialog v-model="uEdit"
                  scrollable>
            <v-card tile
                    v-if="uEdit">
                <v-card-title class="headline">Edit User</v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-expansion-panels>
                        <v-expansion-panel>
                            <v-expansion-panel-header>
                                <v-list-item-title>{{uSelected.contact.real_name}}</v-list-item-title>
                                <v-list-item-subtitle>{{uSelected.contact.email_address}}</v-list-item-subtitle>
                            </v-expansion-panel-header>
                            <v-expansion-panel-content>
                                <profileForm v-model="uSelected.contact"
                                            readonly />
                            </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-expansion-panels>
                    <editAdminUser v-model="uSelected" />
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="default"
                           @click="uEdit = false">Cancel</v-btn>
                    <v-btn color="primary"
                           @click="saveUser">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="uCreate"
                  scrollable>
            <v-card tile>
                <v-card-title class="headline">Create User</v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <simpleDropdown apiPath="Contact"
                                    valueDisplay="real_name"
                                    valueSubDisplay="email_address"
                                    label="Search contacts"
                                    v-model="uNew_contact_id" />
                    <editAdminUser v-model="uSelected" />
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="default"
                           @click="uCreate = false">Cancel</v-btn>
                    <v-btn color="primary"
                           @click="saveUser">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-tab-item>
    <v-dialog v-model="loading"
              width="200"
              height="200"
              close-delay="1200"
              content-class="elevation-0"
              persistent>
        <v-card-text class="text-center overflow-hidden">
            <v-progress-circular :size="150"
                                 class="mb-0"
                                 indeterminate />
        </v-card-text>
    </v-dialog>
</v-tabs-items>
<!-- <v-container fluid
             fill-height>

    <v-row>
        <v-col align-self="start">
        </v-col>
    </v-row>
</v-container> -->
</template>
<script>
import {
    mapActions
} from 'vuex';
import admin from '../../api/admin';
import {
    debounce
} from '@/plugins/debounce';
import simpleList from '@/components/simpleList.vue';
import simpleDropdown from '@/components/simpleDropdown.vue';
import editAdminUser from '@/components/editAdminUser.vue';
import profileForm from '@/components/profileForm.vue';

export default {
    components: {
        simpleList,
        simpleDropdown,
        editAdminUser,
        profileForm
    },
    props: [
        'subTabIx'
    ],
    data: () => ({
        listRemoveHeaders: [
            'id'
        ],
        listAddHeaders: [{
            text: 'ID',
            value: 'contact_id'
        }, {
            text: 'Username',
            value: 'username'
        }, {
            text: 'Real Name',
            value: 'real_name'
        }, {
            text: 'Email Address',
            value: 'email_address'
        }, {
            text: 'Active',
            value: 'active'
        }],
        uSelected: {},
        uEdit: false,
        uCreate: false,
        uNew_contact_id: null,
        loading: false,

    }),
    computed: {
        authToken: function() {
            return this.$store.getters['mydata/getAuthToken'];
        },
        listActions: function() {
            var result = [];
            result.push({
                name: 'edit',
                text: 'Edit',
                icon: 'edit-pencil'
            });
            return result;
        },
        listFooterActions: function() {
            var result = [];
            result.push({
                name: 'create',
                text: 'Add',
                icon: 'plus'
            });
            return result;
        }
    },
    methods: {
        checkPermission: () => {
            console.log('Hey! Listen!');
        },
        editUser: function(selectedUser) {
            console.log("Edit user", selectedUser);
            let that = this;
            that.loading = false;
            admin.genericGet(this.authToken, 'AdminUser/' + selectedUser.contact_id, null, function(editUser) {
                console.log('loaded user', editUser)
                that.uSelected = editUser;
                that.loading = false;
                that.uEdit = true;
            }, function() {
                that.loading = false;
            })
        },
        createUser: function() {
            this.uCreate = true;
            this.uSelected = {
                active: true
            };
        },
        editBadgeType: function(selectedBadgeType) {
            this.loading = true;
            this.btDialog = true;
            var that = this;
            admin.genericGet(this.authToken, 'Staff/BadgeType/' + selectedBadgeType.id, null, function(editBt) {

                that.btSelected = editBt;
                that.loading = false;
            }, function() {
                that.loading = false;
            })
        },
        saveUser: function() {
            var url = 'AdminUser';
            var data = {
                ...this.uSelected,
                contact_id: this.uCreate ? this.uNew_contact_id : this.uSelected.contact_id,
            };
            if (this.uEdit)
                url = url + '/' + data.contact_id;
            console.log("Saving user", this.uSelected)
            this.loading = true;
            var that = this;
            admin.genericPost(this.authToken, url, data, function(editBt) {

                that.loading = false;
                that.uCreate = false;
                that.uEdit = false;
            }, function() {
                that.loading = false;
            })
        }
    },
    watch: {
        $route() {
            this.$nextTick(this.checkPermission);
        },
    },
    created() {
        this.checkPermission();
        //this.doSearch();
        this.$emit('updateSubTabs', [{
                key: '0',
                text: 'Users',
                title: 'Users'
            },
            {
                key: '1',
                text: 'Permissions Info',
                title: 'Permissions Info'
            },
        ]);
    }
};
</script>
