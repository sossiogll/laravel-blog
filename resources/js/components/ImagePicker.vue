<template>
  <div>
    
    <div class="form-group">
        
        <label>{{openModalLabel}}</label>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg" :active="isIdle">{{addButtonLabel}}</button>
        <button type="button" class="btn btn-outline-info" disabled>
            <i class="fa fa-info"></i>
            <i class="fa fa-file"></i>
          {{selectedImages.length}}
        </button>
   </div>
    
    <!-- Modal con selettore immagine -->
    <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{modalTitle}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <vue-select-image
                        :dataImages="imagesInfo"
                        @onselectimage="onSelectImage"
                        :is-multiple="isMultiple"
                        @onselectmultipleimage="onSelectMultipleImage"
                        :w="'100'"
                        :h="'100'"
                        :useLabel="true"
                        ref="imagePicker">
                    </vue-select-image>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{closeButtonLabel}}</button>
                </div>

            </div>
        </div>
    </div>

        <input type="hidden" v-for="(selectedImage, index) in selectedImages" :value="selectedImage.id" name="carousel[]"/> 

  </div>
</template>

<script>

import axios from 'axios'
import VueSelectImage from 'vue-select-image'
require('vue-select-image/dist/vue-select-image.css');

const STATUS_IDLE = 0;
const STATUS_LOADING = 1;
const NOT_FOUND = -404;

export default {
  components: {
    VueSelectImage
  },
  props: {
    mediaEndpoint:{
        type: String,
        required: true
    },
    carouselEndpoint:{
        type: String,
        required: false,
        default: null
    },
    addButtonLabel:{
        type: String,
        required: true
    },
    openModalLabel:{
        type: String,
        required: true
    },
    modalTitle:{
        type: String,
        required: true
    },
    closeButtonLabel:{
        type: String,
        required: true
    },
    isMultiple:{
        type: Boolean,
        required: false,
        default: false
    }
  },

  data () {

    return {
      imagesInfo: [],
      selectedImages: [],
      currentStatus: null,
    }
  },

  computed:{
    isLoading(){
      return this.currentStatus === STATUS_LOADING;
    },
    isIdle(){
      return this.currentStatus === STATUS_IDLE;
    },
    isCarouselEndpointDefinited(){
      return (this.carouselEndpoint != null && this.carouselEndpoint !== undefined && this.carouselEndpoint.length >0)
    }
  },

  methods: {

    loadImagesInfo(){

      let component = this;
      
      this.currentStatus = STATUS_LOADING;

      axios.get(this.mediaEndpoint)
      .then(function (response) {
        // handle success
        component.imagesInfo = response.data.data;
      })
      .catch(function (error) {
        // handle error
        console.log(error);
      })
      .then(function () {
        component.currentStatus = STATUS_IDLE;
      });

    },
    loadSelectedImages(){

      if(this.isCarouselEndpointDefinited){
        let component = this;
        
        this.currentStatus = STATUS_LOADING;

        console.log(this.carouselEndpoint);
        axios.get(this.carouselEndpoint)
        .then(function (response) {
          // handle success
          component.selectedImages = response.data.data;
          component.$refs.imagePicker.multipleSelected = response.data.data;

        })
        .catch(function (error) {
          // handle error
          console.log(error);
        })
        .then(function () {
          component.currentStatus = STATUS_IDLE;
        });
      }
    },
    onSelectImage(img) {
      alert(`Selected image: ${img.alt}`);
    },
    onSelectMultipleImage(images) {
      this.selectedImages = images;
    },

  },
  mounted() {
      this.currentStatus = STATUS_IDLE;
      this.loadSelectedImages();
      this.loadImagesInfo();
  },

}
</script>
