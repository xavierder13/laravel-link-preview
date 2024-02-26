<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-overlay absolute :value="overlay">
        <v-progress-circular
          :size="70"
          :width="7"
          color="primary"
          indeterminate
        ></v-progress-circular>
      </v-overlay>
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <v-card>
          <v-card-title>
            Link Preview Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('link-preview-list')"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="clear() + (dialog = true)"
                  v-if="hasPermission('link-preview-create')"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
                <v-dialog v-model="dialog" max-width="500px" persistent>
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="tilte"
                              v-model="editedItem.title"
                              label="Title"
                              required
                              :error-messages="titleErrors + linkPreviewError.title"
                              @input="$v.editedItem.title.$touch() + (linkPreviewError.title = [])"
                              @blur="$v.editedItem.title.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="url"
                              v-model="editedItem.url"
                              label="URL"
                              required
                              :error-messages="URLErrors + linkPreviewError.url"
                              @input="$v.editedItem.url.$touch() + (linkPreviewError.url = [])"
                              @blur="$v.editedItem.url.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-2 py-0">
                            <v-textarea
                              name="description"
                              v-model="editedItem.description"
                              label="Description"
                              required
                              outlined
                              :error-messages="descriptionErrors + linkPreviewError.description"
                              @input="$v.editedItem.description.$touch() + (linkPreviewError.description = [])"
                              @blur="$v.editedItem.description.$touch()"
                            ></v-textarea>
                          </v-col>
                        </v-row>
                      </v-container>
                    </v-card-text>
                    <v-divider class="mb-3 mt-0"></v-divider>
                    <v-card-actions class="pa-0">
                      <v-spacer></v-spacer>
                      <v-btn color="#E0E0E0" @click="close" class="mb-3">
                        Cancel
                      </v-btn>
                      <v-btn
                        color="primary"
                        @click="save"
                        class="mb-3 mr-4"
                        :disabled="disabled"
                      >
                        Save
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-toolbar>
            </template>
          </v-card-title>
          <v-data-table
            :headers="headers"
            :items="link_previews"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('link-preview-list')"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editLinkPreview(item)"
                v-if="hasPermission('link-preview-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('link-preview-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from 'vuex';

export default {

  mixins: [validationMixin],

  validations: {
    editedItem: {
      title: { required },
      url: { required },
      description: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Title", value: "title" },
        { text: "URL", value: "url" },
        { text: "Description", value: "description" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      link_previews: [],
      editedIndex: -1,
      editedItem: {
        title: '',
        url: '',
        description: ''
      },
      defaultItem: {
        title: '',
        url: '',
        description: ''
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Link Preview Lists",
          disabled: true,
        },
      ],
      loading: true,
      linkPreviewError: {
        title: [],
        url: [],
        description: []
      },
      overlay: false,
    };
  },

  methods: {
    getLinkPreview() {
      this.loading = true;
      axios.get("/api/link_preview/index").then(
        (response) => {
          this.link_previews = response.data.link_previews;
          console.log(response);
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editLinkPreview(item) {
      this.editedIndex = this.link_previews.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteLinkPreview(link_preview_id) {
      const data = { link_preview_id: link_preview_id };
      this.loading = true;
      axios.post("/api/link_preview/delete", data).then(
        (response) => {
          this.loading = false;
          this.overlay = false;
        
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "link-preview-delete" });
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    showAlert(icon, title, text) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        text: text,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Delete record!",
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed
          this.overlay = true;
          const link_preview_id = item.id;
          const index = this.link_previews.indexOf(item);

          //Call delete LinkPreview function
          this.deleteLinkPreview(link_preview_id);

          //Remove item from array link_previews
          this.link_previews.splice(index, 1);

          this.$swal({
            position: "center",
            icon: "success",
            title: "Record has been deleted",
            showConfirmButton: false,
            timer: 2500,
          });
        }
      });
    },

    close() {
      this.dialog = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {

      this.$v.$touch();
      this.linkPreviewError = {
        title: [],
        url: [],
        description: []
      };

      if (!this.$v.$error) {
        this.disabled = true;
        this.overlay = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const link_preview_id = this.editedItem.id;

          axios.post("/api/link_preview/update/" + link_preview_id, data).then(
            (response) => {
              this.overlay = false;
              let data = response.data;

              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "link-preview-edit" });

                Object.assign(
                  this.link_previews[this.editedIndex],
                  this.editedItem
                );
                this.showAlert('success', data.success);
                this.close();

              }
              else if(data.error)
              {
                this.showAlert('error', 'Error', 'Error on creating link preview. Please check the URL.');
              }
              else
              {
                let errors = data;
                let errorNames = Object.keys(data);

                errorNames.forEach(value => {
                  this.linkPreviewError[value].push(errors[value]);
                });
              }

              this.disabled = false;
            },
            (error) => {
              this.overlay = false;
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        } else {
          const data = this.editedItem;
        
          axios.post("/api/link_preview/store", data).then(
            (response) => {
              this.overlay = false;
              this.disabled = false;
              let data = response.data;

              if (data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "link-preview-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.link_previews.push(data.link_preview);
              }
              else if(data.error)
              {
                this.showAlert('error', 'Error', 'Error on creating link preview. Please check the URL.');
              }
              else
              { 
                let errors = data;
                let errorNames = Object.keys(data);

                errorNames.forEach(value => {
                  this.linkPreviewError[value].push(errors[value]);
                });
                
              }
              
            },
            (error) => {
              this.overlay = false;
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        }
      }
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.linkPreviewError = {
        title: [],
        url: [],
        description: []
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
      
        if (
          action == "link-preview-create" ||
          action == "link-preview-edit" ||
          action == "link-preview-delete"
        ) {
          this.getLinkPreview();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Link Preview" : "Edit Link Preview";
    },
    titleErrors() {
      const errors = [];
      if (!this.$v.editedItem.title.$dirty) return errors;
      !this.$v.editedItem.title.required &&
        errors.push("Title is required.");
      return errors;
    },
    URLErrors() {
      const errors = [];
      if (!this.$v.editedItem.url.$dirty) return errors;
      !this.$v.editedItem.url.required &&
        errors.push("URL is required.");
      return errors;
    },
    descriptionErrors() {
      const errors = [];
      if (!this.$v.editedItem.description.$dirty) return errors;
      !this.$v.editedItem.description.required &&
        errors.push("Description is required.");
      return errors;
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getLinkPreview();
    this.websocket();
  },
};
</script>