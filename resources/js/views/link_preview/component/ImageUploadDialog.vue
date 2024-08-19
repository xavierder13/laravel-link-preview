<template>
  <v-dialog v-model="dialog" :width="cropImage ? '1000px' : '400px'" persistent>
    <v-card>
      <v-card-title class="pa-4">
        <span class="headline"> {{ formTitle }} </span>
      </v-card-title>
      <v-divider class="mt-0"></v-divider>
      <v-card-text>
        <v-row v-if="cropImage">
          <v-col>
            <cropper
              class="cropper"
              :src="img_url"
              ref="cropper"
            ></cropper>
          </v-col>
        </v-row>
        <v-row v-if="!cropImage">
          <v-col class="my-0 py-0 mb-2">  
            <v-card color="grey lighten-5 mt-2" elevation="2">               
              <v-img
                :src="img_url"
                aspect-ratio="1"
                cover
                class="bg-grey-lighten-2"
                ref="picture"
              >  
                <v-card-title class="fill-height justify-center" v-if="!img_url">
                  <div class="d-flex justify center flex-column">
                    <span class="grey--text text--lighten-1">No Image</span>
                  </div>
                </v-card-title>
              </v-img>                 
            </v-card>              
          </v-col>
        </v-row>  
        <v-row v-if="!cropImage">
          <v-col class="my-0 py-0">
            <v-file-input
              v-model="file"
              show-size
              label="File input"
              prepend-icon="mdi-paperclip"
              required
              :error-messages="fileErrors"
              @input="$v.file.$touch()"
              @blur="$v.file.$touch()"
            >
              <template v-slot:selection="{ index, text }">
                <v-chip small label color="primary">
                  {{ text }}
                </v-chip>
              </template>
            </v-file-input>
          </v-col>
        </v-row>
      </v-card-text>
      <v-divider class="mb-3 mt-0"></v-divider>
      <v-card-actions class="pa-0">
        <v-spacer></v-spacer>
        <v-btn 
          color="#E0E0E0" 
          @click="cropImage ? cropImage = false : closeDialog()" 
          class="mb-3"
        >
          Cancel
        </v-btn>
        <v-btn
          color="primary"
          @click="cropImage = true"
          class="mb-3"
          :disabled="disabled || !img_url"
          v-if="!cropImage"
        >
          Crop
        </v-btn>
        <v-btn
          color="primary"
          @click="save()"
          class="mb-3 mr-4"
          :disabled="disabled"
        >
          Save
        </v-btn>
      </v-card-actions>
    </v-card>
    <v-dialog v-model="dialog_uploading" max-width="500px" persistent>
      <v-card>
        <v-card-text>
          <v-container>
            <v-row
              class="fill-height"
              align-content="center"
              justify="center"
            >
              <v-col class="subtitle-1 font-weight-bold text-center mt-4" cols="12">
                Uploading...
              </v-col>
              <v-col cols="6">
                <v-progress-linear
                  color="primary"
                  indeterminate
                  rounded
                  height="6"
                ></v-progress-linear>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-dialog>
</template>
<script>
import axios from 'axios';
import { validationMixin } from "vuelidate";
import { required, requiredIf } from "vuelidate/lib/validators";
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css';

export default {
  name: 'SpeciesImageUploadDialog',
  components: {
    Cropper,
  },
  props: [
    'dialog',
    'data',
    'mode',
  ],
  mixins: [validationMixin],

  validations: {
    file: { 
      // required: requiredIf(function () {
      //     return this.fileIsRequired;
      //   }),
      required
    },
  },
  data() {
    return {
      file: [],
      disabled: false,
      fileInvalid: false,
      img_url: "",
      dialog_uploading: false,
      cropImage: false,
      img: null,
    }
  },
  methods: {
    save() {
      this.$v.$touch();
      if (!this.formHasError) {
        
        if(this.mode == 'Edit')// if mode is Edit then from parent component
        {
          this.dialog_uploading = true;
          // const data = Object.assign(this.data, { width: this.width, height: this.height })

          if(this.cropImage)
          {
            if(this.cropImage)
            {
              this.saveCropImage('/api/link_preview/img_upload');
            }
          }
          else
          {
           
            const formData = new FormData();
            
            formData.append('file', this.file);
            formData.append('link_preview_id', this.data.id);

            axios.post('/api/link_preview/img_upload', formData, {
              headers: {
                Authorization: "Bearer " + localStorage.getItem("access_token"),
                "Content-Type": "multipart/form-data",
              }
            }).then(
              (response) => {
                let data = response.data;
                
                this.showAlert(data.success);
                this.$emit('uploadImage', data.link_preview);
                this.closeDialog();
                this.reset();
              },
              (error) => {
                console.log(error);
              }
            )
          }
        
        }
        else
        {
          if(this.cropImage)
          {
            const { canvas } = this.$refs.cropper.getResult();
            if(canvas) 
            {
              canvas.toBlob(async blob => {
                this.$emit('uploadImage', blob);
              }, this.file.type);
            }
          }
          else
          {
            this.$emit('uploadImage', this.file);
          }

          this.closeDialog();
          this.reset();
        }
        
      }
    },

    saveCropImage() {
      const { canvas } = this.$refs.cropper.getResult();

      if (canvas) {
        
        canvas.toBlob(async blob => {
          const formData = new FormData();
          formData.append("file", blob, this.file.name);
          formData.append('width', canvas.width);
          formData.append('height', canvas.height);
          formData.append('link_preview_id', this.data.id);
          formData.append('file_type', this.data.file_type);

          axios.post('/api/link_preview/img_upload', formData, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem("access_token"),
              "Content-Type": "multipart/form-data",
            }
          })
          .then((response) => {
            let data = response.data;
            
            this.showAlert(data.success);
            this.$emit('uploadImage', data.link_preview);  
            this.closeDialog();
            this.reset();
          },
          (error) => {
            console.log(error);
          });
          
        }, this.file.type);
      }
    },
    
    closeDialog() {
      this.reset();
      this.$emit('closeDialog');
    },
    showAlert(msg) {
      this.$swal({
        position: "center",
        icon: "success",
        title: msg,
        showConfirmButton: false,
        timer: 2500,
      });
    },
    reset() {
      this.file = [];
      this.width = "";
      this.height = "";
      this.dialog_uploading = false;
      this.cropImage = false;
      this.$v.$reset();
    },
  },
  computed: {
    formTitle() {
      return this.cropImage ? 'Crop Image' : 'Upload Image';
    },
    fileIsRequired() {
      return this.action != 'crop_image'; // required only if action is upload image with file. note: crop_image will use the existing image
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
    formHasError() {
      return this.$v.$error || this.widthInvalid || this.heightInvalid || this.fileInvalid;
    },
    
  },

  watch: {
    file() {
      let url = "";
      let file = this.file;
      this.width = "";
      this.height = "";
     
      if(file)
      {
        if(file.name && !this.fileInvalid)
        {
          url = URL.createObjectURL(file);
          const img = new Image();
          img.src = url;
          img.onload = () => {
              this.width = img.width;
              this.height = img.height;
          };
        }
      }

      this.img_url = url;

    },
  },

  mounted() {
      console.log(this.data);
      
    if(this.data.file_name){
      this.img_url = this.data.file_path + '/' + this.data.file_name;
    }

    const interval = setInterval(() => {      
      if (this.$refs.picture) {        
        const img = new Image();
        img.src = this.$refs.picture.src;
        img.onload = () => {
            this.width = img.width;
            this.height = img.height;
        };
        clearInterval(interval)      
        }    
      }, 50)

    this.cropImage = this.action == 'crop_image' ? true : false;

  }
}
</script>
