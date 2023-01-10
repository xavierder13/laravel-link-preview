<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <v-card>
          <v-card-title class="mb-0 pb-0">
            <span class="headline">Role and Permission</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="name"
                  v-model="editedItem.name"
                  :error-messages="roleErrors + roleError.name"
                  label="Role"
                  @input="$v.editedItem.name.$touch() + (roleError.name = [])"
                  @blur="$v.editedItem.name.$touch()"
                  :readonly="roleid == 1 ? true : false"  
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <v-card>
                  <v-card-title class="py-0">
                    <v-switch
                      v-model="switchFilter"
                      label="Filter Selected Permission"
                      class="pa-3"
                      hide-details=""
                    ></v-switch>
                    <v-spacer></v-spacer>
                    <v-text-field
                      v-model="search"
                      append-icon="mdi-magnify"
                      label="Search"
                      single-line
                      hide-details=""
                    ></v-text-field>
                    <v-spacer></v-spacer>
                  </v-card-title>
                  <!-- <v-data-table
                    :headers="headers"
                    :items="permissions"
                    :search="search"
                    :loading="loading"
                    loading-text="Loading... Please wait"
                  >
                    <template v-slot:item.actions="{ item }">
                    </template>
                  </v-data-table> -->
                  <v-data-table
                    v-model="rolePermissions"
                    :headers="headers"
                    :items="filteredPermissions"
                    item-key="name"
                    show-select
                    :search="search"
                    :loading="loading"
                    loading-text="Loading... Please wait"
                  >
                    <template v-slot:header.data-table-select="{ props, on }">
                      <v-simple-checkbox
                        v-model="selectAll"
                        :value="props.value || props.indeterminate"
                        v-on="on"
                        :indeterminate="props.indeterminate"
                        color="primary"
                        v-if="roleid != 1"
                      />
                    </template>
                  
                    <template v-slot:item.data-table-select="{ isSelected, select }">
                      <v-simple-checkbox color="primary" v-ripple :value="isSelected" @input="select($event)" :disabled="roleid == 1 ? true : false"></v-simple-checkbox>
                    </template>
                  </v-data-table>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="mb-3 mt-4"></v-divider>
          <v-card-actions class="pa-0">
            <v-btn
              color="primary"
              @click="save"
              :disabled="disabled"
              class="ml-6 mb-4 mr-1"
               v-if="roleid != 1 && hasChanges"
            >
              Update
            </v-btn>
            <v-btn color="#E0E0E0" to="/role/index" :class=" !hasChanges ? 'ml-6 mb-4' : 'mb-4'"> Back </v-btn>
          </v-card-actions>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState } from 'vuex';

export default {

  mixins: [validationMixin],

  validations: {
    editedItem: {
      name: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Permission", value: "name" },
      ],
      disabled: false,
      switchFilter: false,
      rolePermissions: [],
      currRolePermissions: [],
      selectAll: false,
      permissions: [],
      editedIndex: -1,
      currItem: {
        name: "",
      },
      editedItem: {
        name: "",
      },
      defaultItem: {
        name: "",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Role Lists",
          disabled: false,
          link: "/role/index",
        },
        {
          text: "Edit role",
          disabled: true,
        },
      ],
      loading: true,
      roleError: {
        name: [],
      },
      roleid: "",
      hasChanges: false,
    };
  },

  methods: {
    getRole() {
      this.loading = true;
      this.roleid =  this.$route.params.roleid
      const data = {
        roleid: this.roleid,
      };

      axios.post("/api/role/edit", data).then(
        (response) => {
          // console.log(response.data);
          this.editedItem = response.data.role;
          this.currItem = { name: response.data.role.name };
          this.permissions = response.data.permissions;
          let rolePermissions = response.data.rolePermissions;

          this.permissions.forEach(value => {
            rolePermissions.forEach(val => {
              if(value.id === val)
              {
                this.rolePermissions.push(value);
                this.currRolePermissions.push(value);
                // console.log(this.rolePermissions);
              }
            });
          });

          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    showAlert() {
      this.$swal({
        position: "center",
        icon: "success",
        title: "Record has been saved",
        showConfirmButton: false,
        timer: 2500,
      });
    },

    save() {

      this.$v.$touch();
      this.roleError = {
        name: []
      };

      if (!this.$v.$error) {
        this.disabled = true;

        
        const data = { name: this.editedItem.name, permission: this.permission};
        const roleid = this.editedItem.id;

        axios.post("/api/role/update/" + roleid, data).then(
          (response) => {
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "role-edit" });

              this.showAlert();
              this.hasChanges = false;
              this.currItem = { name: response.data.role.name };
              this.currRolePermissions = [];
              this.rolePermissions = [];
              let rolePermissions = response.data.role.permissions;

              this.permissions.forEach(value => {
                rolePermissions.forEach(val => {
                  if(value.id === val.id)
                  {
                    this.rolePermissions.push(value);
                    this.currRolePermissions.push(value);
                    // console.log(this.rolePermissions);
                  }
                });
              });

            }
            else
            {
              let errors = response.data;
              let errorNames = Object.keys(response.data);

              errorNames.forEach(value => {
                this.roleError[value].push(errors[value]);
              });
            }

            this.disabled = false;
          },
          (error) => {
            this.isUnauthorized(error);
            this.disabled = false;
          }
        );
      } 
    },

    clear() {
      this.$v.$reset();
      this.editedItem.name = "";
      this.roleError = {
        name: []
      };
    },
    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    }, 
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;
      
        if (action == "role-edit") {
          this.getRole();
        }
      };
    },
  },
  computed: {
    roleErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required &&
        errors.push("Role is required.");
      return errors;
    },
    filteredPermissions() {
      let filteredPermissions = [];

      filteredPermissions = this.permissions;

      // if switch is true then filter all checked permissions
      if(this.switchFilter)
      {
        filteredPermissions = [];

        this.rolePermissions.forEach(value => {
          
          filteredPermissions.push(value);

        });
        
      }
      
      return filteredPermissions;
    },
    permission(){
      let permission  = [];
      this.rolePermissions.forEach(value => {
          
        permission.push(value.id);

      });

      return permission;
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  watch: {
    selectAll()
    {
      if(this.selectAll)
      {
        this.rolePermissions = this.permissions;
      }
    },
    rolePermissions()
    { 
      this.hasChanges = false;
      let editedItem = {
        name: this.editedItem.name,
        permissions: this.rolePermissions,
      };

      let currItem = {
        name: this.currItem.name,
        permissions: this.currRolePermissions,
      };
      
      // check if Permissions has changes 
      if(JSON.stringify(currItem) !== JSON.stringify(editedItem)){
        this.hasChanges = true;
      }

    },
    "editedItem.name"(){
      
      this.hasChanges = false;
      let editedItem = {
        name: this.editedItem.name,
        permissions: this.rolePermissions,
      };
      let currItem = {
        name: this.currItem.name,
        permissions: this.currRolePermissions,
      };

      console.log('editedItem', editedItem);
      console.log('currItem', currItem);
      
      // check if Permissions has changes 
      if(JSON.stringify(currItem) !== JSON.stringify(editedItem)){
        this.hasChanges = true;
      }
    }
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getRole();
    // this.websocket();
  },
};
</script>