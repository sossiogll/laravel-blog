<template>
    <form id="imageFormContainer" method="POST" :action="action" accept-charset="UTF-8" enctype="multipart/form-data" >

      <div v-if="isInitial" >

        <div v-if="imagesInfo.length==0" class="row">
          <div class="col">
            <div class="alert alert-info" role="alert">
              <i class="fa fa-info"></i>
              {{uploadInitMessage}}
            </div>
          </div>
        </div>

        <ImageLoaderItem v-if="isInitial" v-for="(imageInfo, index) in this.imagesInfo" :key='index'
        :imageLabel="imageLabel"
        :nameLabel="nameLabel"
        :descriptionPlaceholder="descriptionPlaceholder"
        :descriptionLabel="descriptionLabel"
        :disabled="isSaving"
        :removeButtonLabel="removeButtonLabel"
        :fileName="imageInfo.fileName"
        :imageName="imageInfo.imageName"
        :imageDescription="imageInfo.imageDescription"
        :index="imageInfo.index"
        ref="imageFormItems"
        ></ImageLoaderItem>
      </div>


    <div v-if="isSaving" class="row">
      <div class="col">
        <div class="alert alert-info" role="alert">
          <i class="fa fa-info"></i>
          {{uploadedFiles.length}}/{{imagesInfo.length}}
        </div>
      </div>
    </div>


    <div v-if="isFailed" class="row">
      <div class="col">
          <div class="alert alert-danger" role="alert">
            <div v-for="uploadError in uploadErrors">
              <i class="fa fa-exclamation"></i>
              {{uploadError}}
            </div>
            {{uploadWarningMessage}}
          </div>
      </div>
    </div>

    <div v-if="isSuccess" class="row">
      <div class="col">
          <div class="alert alert-success" role="alert">
            <i class="fa fa-check"></i>
              {{uploadSuccessMessage}}
          </div>
      </div>
    </div>

  <div class="row">
      <div class="col">
        <a :href="backButtonLink" :disabled="isSaving" class="btn btn-secondary">
          <i class="fa fa-chevron-left"></i>
          {{backButtonLabel}}
        </a>
        <button type="button" :disabled="isSaving" class="btn btn-secondary" @click="reset">
          <i class="fa fa-undo"></i>
          {{resetButtonLabel}}
        </button>

        <label type="button" class="btn btn-secondary" style="margin:0;" :disabled="!isInitial">
          <i class="fa fa-image"></i>
          {{addButtonLabel}}
          <input type="file" ref="multipleImageInput" multiple style="display: none;" @change="filesChange($event.target.files)" :disabled="!isInitial">
        </label>

        <button type="button" :disabled="!isInitial || imagesInfo.length==0" class="btn btn-primary" @click="save">
          <i class="fa fa-floppy-o"></i>
          {{saveButtonLabel}}
        </button>

        <button type="button" href="#" class="btn btn-outline-info" disabled>
          <i class="fa fa-info"></i>
          <i class="fa fa-file"></i>
          {{imagesInfo.length}}
        </button>
      </div>
  </div>

    </form>


</template>

<script>
import ImageLoaderItem from './ImageLoaderItem'
import axios from 'axios';

const STATUS_INITIAL = 0;
const STATUS_SAVING = 1;
const STATUS_SUCCESS = 2
const STATUS_FAILED = 3;

const NOT_FOUND = -404;

export default {
  components: {
    ImageLoaderItem
  },
  props: {
    action:{
      type: String
    },
    imageLabel: {
      type: String
    },
    nameLabel: {
      type: String
    },
    descriptionPlaceholder: {
      type: String
    },
    descriptionLabel: {
      type: String
    },
    saveButtonLabel:{
      type: String
    },
    addButtonLabel:{
      type: String
    },
    backButtonLabel:{
      type: String
    },
    backButtonLink:{
      type: String
    },
    importButtonLabel:{
      type: String
    },
    removeButtonLabel:{
      type: String
    },
    resetButtonLabel:{
      type: String
    },
    uploadWarningMessage:{
      type: String
    },
    uploadSuccessMessage:{
      type: String
    },
    uploadInitMessage:{
      type: String
    }
  },

  data () {
    return {
      imagesInfo: [],
      uploadedFiles: [],
      uploadErrors: [],
      currentStatus: null,
      count: 0,
    }
  },
  computed: {
    isInitial() {
      return this.currentStatus === STATUS_INITIAL;
    },
    isSaving() {
      return this.currentStatus === STATUS_SAVING;
    },
    isSuccess() {
      return this.currentStatus === STATUS_SUCCESS;
    },
    isFailed() {
      return this.currentStatus === STATUS_FAILED;
    }
  },
  methods: {

    searchImageInfo(index){

    var i = 0;

      while(i < this.imagesInfo.length && index != this.imagesInfo[i].index ){

        i++;

      };

      if(i == this.imagesInfo.length)
        i=NOT_FOUND;

      return i;

    },

    addImageInfo(imageName, imageDescription, file){

      var tempImageInfo = {};
      tempImageInfo["imageName"] = imageName;
      tempImageInfo["imageDescription"] = imageDescription,
      tempImageInfo["fileName"] = file.name;
      tempImageInfo["file"]= file;
      tempImageInfo["index"] = this.count;

      this.imagesInfo.push(tempImageInfo);
      this.count++;

    },

    removeImageInfo(index){

      let position = this.searchImageInfo(index);
      if(position!==NOT_FOUND)
          this.imagesInfo.splice(position, 1);
      
    },

    updateImageDescription(index, newImageDescription){
      
      let position = this.searchImageInfo(index);
      if(position!==NOT_FOUND)
        this.imagesInfo[position].imageDescription = newImageDescription;

    },

    updateImageName(index, newImageName){

      let position = this.searchImageInfo(index);
      if(position!==NOT_FOUND)
        this.imagesInfo[position].imageName = newImageName;

    },

    reset() {
    // reset form to initial state
      this.currentStatus = STATUS_INITIAL;
      this.imagesInfo = [];
      this.uploadedFiles = [];
      this.uploadError = [];
    },

    filesChange(fileList) {
      // handle file changes

      if (!fileList.length) return;
      var component = this;

        Array.from(fileList).forEach(function(file){
          component.addImageInfo("","", file)
        });
      },

      save() {
        // upload data to the server
        this.currentStatus = STATUS_SAVING;
        var component = this;

        this.imagesInfo.forEach(function(imageInfo){

          var formData = new FormData();

          formData.append("image", imageInfo.file, imageInfo.fileName);
          formData.append("name", imageInfo.imageName);
          formData.append("description", imageInfo.imageDescription);

          axios.post(component.action, formData)
          .then(response => {
            component.uploadedFiles.push(response);
            component.currentStatus = STATUS_SUCCESS;
          })
          .catch(err => {
            component.uploadErrors.push(err.message + " - " + imageInfo.fileName);
            component.currentStatus = STATUS_FAILED;
          });
        });

      },
    },
    
    mounted() {
      this.reset();
    },

  }

</script>
