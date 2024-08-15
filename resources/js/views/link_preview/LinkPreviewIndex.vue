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
                <v-dialog v-model="dialog" max-width="1000px" persistent>
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col class="my-0 py-0" cols="8">
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
                                  rows="7"
                                  required
                                  outlined
                                  :error-messages="descriptionErrors + linkPreviewError.description"
                                  @input="$v.editedItem.description.$touch() + (linkPreviewError.description = [])"
                                  @blur="$v.editedItem.description.$touch()"
                                ></v-textarea>
                              </v-col>
                            </v-row>
                          </v-col>
                          <v-divider vertical></v-divider>
                          <v-col>
                            <v-row>
                              <v-col>
                                <div class="text-center">
                                  <span class="text-h6 font-weight-bold">Display Photo</span>
                                </div>
                                <div class="text-center" v-if="$v.img_src.$error">
                                  <span class=" ml-2 font-italic font-weight-medium red--text">
                                    Image is required!
                                  </span>
                                </div >
                                <v-hover v-slot="{ hover }" open-delay="5">
                                  <v-card 
                                    color="grey lighten-5 mt-2" 
                                    elevation="2"
                                    :style="$v.img_src.$error ? 'border: 1px solid red !important;' : ''"
                                  >               
                                    <v-fab-transition>
                                      <v-tooltip top v-if="img_src">
                                        <template v-slot:activator="{ on, attrs }">
                                          <v-btn
                                            color="error"
                                            fab
                                            dark
                                            x-small
                                            absolute  
                                            right
                                            class="mt-4 ml-8"
                                            @click="removeDP()"
                                            
                                            v-bind="attrs" v-on="on"
                                          >
                                            <v-icon small>mdi-delete</v-icon>
                                          </v-btn>
                                        </template>
                                        <span>Remove</span>
                                      </v-tooltip>   
                                      <v-tooltip top v-if="!img_src">
                                        <template v-slot:activator="{ on, attrs }">
                                          <v-btn
                                            color="primary"
                                            fab
                                            dark
                                            x-small
                                            absolute  
                                            right
                                            class="mt-4 ml-8"
                                            @click="dialog_upload = true"
                                            
                                            v-bind="attrs" v-on="on"
                                          >
                                            <v-icon small>mdi-plus</v-icon>
                                          </v-btn>
                                        </template>
                                        <span>Add Image</span>
                                      </v-tooltip> 
                                    </v-fab-transition>
                                    <v-img
                                      :src="img_src"
                                      aspect-ratio="1"
                                      cover
                                      class="bg-grey-lighten-2"
                                    >  
                                      <v-card-title class="fill-height justify-center" v-if="hover && img_src">
                                        <div class="d-flex justify center flex-column">
                                          <v-btn class="mt-12 image-btn" x-small @click="viewImage(img_src)">View Image </v-btn>
                                        </div>
                                      </v-card-title>
                                      <v-card-title class="fill-height justify-center" v-if="!img_src">
                                        <div class="d-flex justify center flex-column">
                                          <span class="grey--text text--lighten-1">No Image</span>
                                        </div>
                                      </v-card-title>
                                    </v-img> 
                                  </v-card>
                                </v-hover>
                              </v-col>
                            </v-row>
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
                        @click=" btnText != 'OK' ? save() : close()"
                        class="mb-3 mr-4"
                        :disabled="disabled"
                      >
                        {{ btnText }}
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
        <!-- loader-dialog -->
        <v-dialog
          v-model="save_loading"
          hide-overlay
          persistent
          width="300"
        >
          <v-card
            color="primary"
            dark
          >
            <v-card-text>
              <p class="text-center pt-2">
                Saving Record...
              </p>
              <v-progress-linear
                indeterminate
                color="white"
                class="mb-0"
              ></v-progress-linear>
            </v-card-text>
          </v-card>
        </v-dialog>
        <ImageUploadDialog
          :dialog="dialog_upload"
          :mode="actionMode"
          :data="editedItem"
          @uploadImage="uploadImage"
          @closeDialog="closeImageUploadDialog"
        />
        <div v-show="dialog_image_view" class="fullscreen-image">
          <div style="height: 100vh;">
            <v-btn
              fab
              dark
              x-small
              absolute  
              right
              class="mt-4 mr-4"
              @click="dialog_image_view = false"
            >
              <v-icon small>mdi-close-circle</v-icon>
            </v-btn>
          </div>
          <img :src="img_src" alt="Full screen Image" />
        </div>
      </v-main>
    </div>
  </div>
</template>
<style scoped>
.fullscreen-image {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }
</style>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from 'vuex';
import ImageUploadDialog from './component/ImageUploadDialog.vue';

export default {

  mixins: [validationMixin],
  components: {
    ImageUploadDialog,
  },
  validations: {
    editedItem: {
      title: { required },
      url: { required },
      description: { required },
    },
    img_src: { required },
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
      originalItem: {
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
        description: [],
      },
      overlay: false,
      save_loading: false,
      dialog_upload: false,
      dialog_image_view: false,
      img_src: "",
      file: [],
      btnText: "Add",
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

    save() {

      this.$v.$touch();
      this.linkPreviewError = {
        title: [],
        url: [],
        description: []
      };

      if (!this.$v.$error) {
        this.disabled = true;
        this.save_loading = true;

        const data = this.editedItem;
        const link_preview_id = this.editedItem.id;

        let url = this.editedIndex > -1 ? 'update/' + link_preview_id : 'store';
        const formData = new FormData();
            
        formData.append('file', this.file);
        formData.append('link_preview_id', link_preview_id);
        formData.append('title', this.editedItem.title);
        formData.append('url', this.editedItem.url);
        formData.append('description', this.editedItem.description);

        axios.post("/api/link_preview/" + url, formData, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem("access_token"),
            "Content-Type": "multipart/form-data",
          }
        }).then(
          (response) => {
            console.log(response.data);
            
            this.save_loading = false;
            let data = response.data;

            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "link-preview-edit" });

              // edit
              if(this.editedIndex > -1)
              {
                  Object.assign(
                  this.link_previews[this.editedIndex],
                  this.editedItem
                );  
              }
              else //insert
              {
                //push recently added data from database
                this.link_previews.push(data.link_preview);
              }

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
 
      }
    },

    editLinkPreview(item) {
    
      this.editedIndex = this.link_previews.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.originalItem = Object.assign({}, item);
      this.img_src = item.file_path ? item.file_path + '/' + item.file_name : "";
      this.dialog = true;
    },

    deleteLinkPreview(item) {
      const index = this.link_previews.indexOf(item);
      const data = { link_preview_id: item.id };
      
      this.loading = true;
      axios.post("/api/link_preview/delete", data).then(
        (response) => {
          this.loading = false;
          this.overlay = false;
          
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "link-preview-delete" });
            //Remove item from array link_previews
            this.link_previews.splice(index, 1);
            this.showAlert('success', response.data.success)
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

          //Call delete LinkPreview function
          this.deleteLinkPreview(item);

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
      this.file = [];
      this.img_src = "";
    },

    closeImageUploadDialog() {
      this.dialog_upload = false;
    },  

    removeDP() {
      
      if(this.editedIndex > -1)
      {

        this.$swal({
          title: "Delete Photo",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Delete",
        }).then((result) => {
          if (result.value) {

            const data = { link_preview_id: this.editedItem.id }
            
            axios.post("/api/link_preview/img_delete", data).then(
              (response) => {
                this.dialog_delete_loading = false;
                if (response.data.success) {
                  this.showAlert('success', response.data.success);
                  this.file = [];
                  this.img_src = "";
                  
                  const item = { file_name: "", file_path: "", file_path: "" };
                  Object.assign(this.link_previews[this.editedIndex], item) ;
                }
                this.loading = false;
              },
              (error) => {
                this.isUnauthorized(error);
              }
            );
            
          }
        });
      }
      else
      {
        this.display_photo = [];
        this.img_src = "";
      }
      
    },

    uploadImage(file) {
      this.file = file;
      let url = "";

      if(this.editedIndex > -1) //update mode
      {
        url = file.file_path + '/' + file.file_name;
      }
      else
      {
        url = URL.createObjectURL(file);
      }

      this.img_src = url;

      // if edit mode
      if(this.editedIndex > -1)
      {
        const data = { file_name: file.file_name, file_path: file.file_path, file_type: file.file_type };
        Object.assign(this.link_previews[this.editedIndex], data); 
      }

    },

    viewImage(src) {

      // window.open(image.file_path + '/' + image.file_name, '_blank');
      this.img_src = src;
      this.dialog_image_view = true;
      
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.originalItem = Object.assign({}, this.defaultItem);
      this.linkPreviewError = {
        title: [],
        url: [],
        description: []
      };
      this.file = [];
      this.img_src = "";
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
    imageErrors() {
      const errors = [];
      if (!this.$v.img_src.$dirty) return errors;
      !this.$v.img_src.required &&
        errors.push("Image is required.");
      return errors;
    },
    fileErrors() {
      const errors = [];
      let file = this.file;
      let errorMsg ="File type must be 'jpg', 'jpeg', 'png'.";
      this.fileInvalid = false;
    
      if(file)
      {
        if(file.name)
        {
          let split_arr = file.name.split('.');
          let split_ctr = split_arr.length;
          let extension = split_arr[split_ctr - 1].toLowerCase();
          let extensions = ['jpg', 'jpeg', 'png'];
          
          if(!extensions.includes(extension))
          {
            this.fileInvalid = true;
            errorMsg = "File size maximum is 20MB";
            errors.push("File type must be 'jpg', 'jpeg', 'png'.");
          }

          if(file.size > 20000000) // 20000000 bytes or 20MB
          {
            errorMsg = "File size maximum is 20MB";
            this.fileInvalid = true;
          }
        }
      }

      this.fileInvalid && errors.push(errorMsg);

      return errors;  

    },
    actionMode() {
      return this.editedIndex > -1 ? 'Edit' : 'Add';
    },
    editedValues() {
      let edited = this.editedItem;
      let editedItem = { title: edited.title, url: edited.url, description: edited.description };

      return JSON.stringify(editedItem)
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  watch: {
    editedValues() {
      let edited = this.editedItem;
      let original = this.originalItem;
      let editedItem = { title: edited.title, url: edited.url, description: edited.description };
      let originalItem = { title: original.title, url: original.url, description: original.description };

      if(this.editedIndex > -1)
      {
        this.btnText = "OK";
        if(JSON.stringify(editedItem) != JSON.stringify(originalItem))
        {
          this.btnText = "Update"; 
        }
      }
      
      
    },
    editedIndex() {
      if(this.editedIndex == -1)
      {
        this.btnText = "Add";
      }
      else
      {
        // btnText will be update to "Update" if there are changes on editedItem
        this.btnText = "OK"; 
      }
    }
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getLinkPreview();
    this.websocket();
    let self = this; 
    window.addEventListener('keyup', function(ev) {
      if (event.keyCode === 27) { //press Esc
        self.dialog_image_view = false;
      }
    });
  },
};
</script>