<template >
<div class="hero">
  <v-container>
    <v-toolbar class="rounded-xl">
      <v-text-field 
        v-model="search" 
        label="Search" 
        single-line
        hide-details=""
        prepend-icon="mdi-magnify"
      ></v-text-field>
      
    </v-toolbar>
    <v-row class="py-12">
      <template v-for="item in filteredLinkPreviews">
        <!-- <v-col cols="12" xs="12" sm="6" md="4" lg="3" xl="3">
          <v-hover
            v-slot="{ hover }"
          >
            <v-card
              class="mx-auto rounded-lg"
              max-width="250"
              :elevation="hover ? 16 : 2"
            >
              <v-card-text @click="pageRedirect(item.url)" class="ma-0 pa-0" style="cursor:pointer;">
                <template slot="progress">
                  <v-progress-linear
                    color="deep-purple"
                    height="10"
                    indeterminate
                  ></v-progress-linear>
                </template>
                <v-img
                  class="rounded-t-lg"
                  height="150"
                  :src="item.file_path+'/'+item.file_name"
                  :alt="item.title "
                ></v-img>
                <v-card-title class="my-0 py-0 mt-1 text-subtitle-1 font-weight-bold">
                  <div class="ellipsis-single-line">
                    {{ item.title }}
                  </div>
                </v-card-title>
                <v-card-text class="my-0 py-0">
                  <div class="my-0 text-subtitle-1 ellipsis-single-line">
                    {{ item.url }}
                  </div>
                </v-card-text>
              </v-card-text>
              <v-card-actions class="mt-0 pt-0 pr-4">
                <v-spacer></v-spacer>
                <v-btn small @click="viewDetails(item)">info</v-btn>
              </v-card-actions>
            </v-card>
          </v-hover> -->
        <v-col cols="12" xs="12" sm="12" md="6" lg="6" xl="6">
          <v-menu offset-y min-width="20px">
            <template v-slot:activator="{ on }">
              <v-hover v-slot="{ hover }">
                <v-card
                  class="mx-auto rounded-lg my-2"
                  max-width="550"
                  max-height="155"
                  :elevation="hover ? 16 : 2"
                  @click="pageRedirect(item.url)"
                  @contextmenu.prevent="on.click"
                >
                  <v-row>
                    <v-col cols="4" class="ma-0 py-0 pr-0">
                      <v-img
                        class="rounded-l-lg ma-0 pa-0"
                        height="155"
                        :src="item.file_path+'/'+item.file_name"
                        :alt="item.title "
                      ></v-img>
                    </v-col>
                    <v-col cols="8" class="ml-0 pl-0">
                      <v-card-text  class="ma-0 pa-0">
                        <template slot="progress">
                          <v-progress-linear
                            color="deep-purple"
                            height="10"
                            indeterminate
                          ></v-progress-linear>
                        </template>
                        <v-card-text class="my-0 py-0">
                          <div class="my-0 py-0 mb-1 text-subtitle-1 font-weight-bold ellipsis-single-line">
                            {{ item.title }}
                          </div>
                          <div class="my-0 text-subtitle-2 mb-2 font-weight-medium ellipsis-multiple-line">
                            {{ item.description }}
                          </div>
                          <div class="my-0 text-subtitle-2 font-weight-medium grey--text ellipsis-single-line">
                            {{ item.url }}
                          </div>
                        </v-card-text>
                      </v-card-text>
                    </v-col>
                  </v-row>
                </v-card>
              </v-hover>
            </template>
            <v-list width="100px" class="pa-1">
              <v-list-item class="ma-0 pa-0" style="min-height: 25px">
                <v-list-item-title>
                  <v-btn width="90px" color="primary" x-small @click="viewDetails(item)">info</v-btn>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </v-col>
      </template>
    </v-row>
    <v-dialog v-model="dialog" max-width="500px" persistent>
      <v-card>
        <v-card-title>
          <span class="headline">Details</span>
        </v-card-title>
        <v-divider class="my-0 py-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="tilte"
                  v-model="editedItem.title"
                  label="Title"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="url"
                  v-model="editedItem.url"
                  label="URL"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="mt-2 py-0">
                <v-textarea
                  name="description"
                  v-model="editedItem.description"
                  label="Description"
                  outlined
                  readonly
                  rows="10"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-divider class="mb-3 mt-0"></v-divider>
        <v-card-actions class="pa-0">
          <v-spacer></v-spacer>
          <v-btn color="#E0E0E0" @click="dialog = false" class="mb-3 mr-4">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</div>
  
</template>
<style>
  .hero {
    background-color:rgb(190, 221, 207);;
    height: 100%;
  }
  .ellipsis-single-line {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  .ellipsis-multiple-line {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;  
    overflow: hidden;
  }
</style>
<script>
import axios from 'axios'

export default {
  data() {
    return {
      link_previews: [],
      editedItem: {},
      dialog: false,
      search: "",
    }
  },
  methods: {
    getLinkPreview() {
      this.loading = true;
      axios.get("/api/link_preview").then(
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
    pageRedirect(url) {
      window.open(url, '_blank');
    },
    viewDetails(item) {
      this.dialog = true;
      this.editedItem = item;
    }
  },
  computed: {
    filteredLinkPreviews() {
      return this.link_previews.filter(item => {
          return (
              item.title.toLowerCase().includes(this.search.toLowerCase()) ||
              item.description.toLowerCase().includes(this.search.toLowerCase()) ||
              item.url.toLowerCase().includes(this.search.toLowerCase())
          );
      });
    }
  },
  mounted() {
    this.getLinkPreview();
  }
}
</script>
