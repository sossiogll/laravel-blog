<template>
  <div :id="'imageForm-'+this.index">

    <div class="row form-group">

          <div class="col-3">
              <label for ="image">{{imageLabel}}</label>
              <input
                type="text"
                class="form-control"
                :disabled="true"
                :value="fileName"
                >
          </div>

          <div class="col-3">
            <label for="name">{{nameLabel}}</label>
            <input
              name="name"
              type="text"
              id="name"
              class="form-control"
              :disabled="disabled"
              v-model="localImageName"
              v-on:input="updateImageName"
            >
          </div>


          <div class="col-5">

            <label for="description">{{descriptionLabel}}</label>

            <textarea
              name="description"
              class="form-control"
              :placeholder="descriptionPlaceholder"
              :disabled="disabled"
              v-model="localImageDescription"
              v-on:input="updateImageDescription"

            />
          </div>

          <div class="col-1">
                  <br>
                  <a href="#" class="btn btn-danger btn-sm" v-on:click="deleteImageForm">
                    <i class="fa fa-trash" aria-hidden="false" :disabled="disabled"></i></a>
          </div>          

    </div>


  </div>
</template>

<script>
import axios from 'axios'

export default {
  props: {
    index: {
      type: Number
    },
    imageLabel:{
      type: String
    },
    nameLabel:{
      type: String
    },
    descriptionPlaceholder:{
      type: String
    },
    descriptionLabel:{
      type: String
    },
    removeButtonLabel:{
      type: String
    },
    disabled:{
      type: Boolean
    },
    fileName:{
      type: String
    },
    imageDescription:{
      type: String
    },
    imageName:{
      type: String
    }

  },

  data () {
    return {
      localImageDescription: this.imageDescription,
      localImageName: this.imageName
    }
  },


  methods: {
    deleteImageForm(){

      this.$parent.removeImageInfo(this.index);

    },

    updateImageName(){

      this.$parent.updateImageName(this.index, this.$data.localImageName);

    },

    updateImageDescription(){

      this.$parent.updateImageDescription(this.index, this.$data.localImageDescription);

    }



  },
}
</script>
