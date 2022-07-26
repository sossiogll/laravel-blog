<template>
  <div>

    <div class="form-group">

      <label :for="name">{{categoryLabel}}</label>

      <select
        required="required"
        :id="id"
        :name="name"
        class="form-control"
        v-model="currentCategoryId"
        :disabled="isLoading"
        @change="loadCustomFieldsValues">
          <option value="">{{categoryPlaceholder}}</option>
          <option v-for="(category, index) in categoryList" :value="category.id"> {{category.name}} </option>
      </select>

    </div>

    <div v-if="isCurrentCategoryIdDefinited && isCurrentCategoryIndexFounded">
      <div  class="form-group" v-for="(customField, index) in categoryList[currentCategoryIndex].custom_fields">
        <label :for="customField.id">
          {{customField.description}}
        </label>
        <input :name="customField.id" type="text" id="title" class="form-control" v-model="customField.value">
    </div>
</div>



  </div>
</template>

<script>
import axios from 'axios'


const STATUS_IDLE = 0;
const STATUS_LOADING = 1;
const NOT_FOUND = -404;

export default {
  props: {
    name: {
      type: String,
      required: true
    },

    id:{
      type: String,
      required: true
    },

    selected: {
      type: String,
      required: false
    },

    categoriesEndpoint:{
      type: String,
      required: true
    },
    customFieldsEndpoint:{
      type: String,
      required: false
    },
    categoryPlaceholder:{
      type: String,
      required: true
    },
    categoryLabel:{
      type: String,
      required: true
    }
  },

  data () {

    return {
      categoryList: [],
      customFields: [],
      currentStatus: null,
      currentCategoryId: null
    }
  },

  computed:{
    isLoading(){
      return this.currentStatus === STATUS_LOADING;
    },
    isIdle(){
      return this.currentStatus === STATUS_IDLE;
    },
    isSelectDefinited(){
      return (this.selected !=null && this.selected!==undefined && this.selected!="" && this.selected>=0);
    },
    isCurrentCategoryIdDefinited(){
      return (this.currentCategoryId !=null && this.currentCategoryId!==undefined && this.currentCategoryId!="" && this.currentCategoryId>=0);
    },
    isCustomFieldEndpointDefinited(){
      return (this.customFieldsEndpoint && this.customFieldsEndpoint.length!=0);
    },
    currentCategoryIndex(){
      var i = 0;
      while(i < this.categoryList.length && this.currentCategoryId != this.categoryList[i].id ){
        i++;
      };
      if(i==this.categoryList.length)
        i = NOT_FOUND;
      return i;
    },
    isCurrentCategoryIndexFounded(){
      return this.currentCategoryIndex!=NOT_FOUND;
    }
  },

  methods: {

    loadCategories(){

      let component = this;
      
      this.currentCategory = STATUS_LOADING;

      axios.get(this.categoriesEndpoint)
      .then(function (response) {
        // handle success
        component.categoryList = response.data.data;
      })
      .catch(function (error) {
        // handle error
        console.log(error);
      })
      .then(function () {
        component.currentStatus = STATUS_IDLE;
        component.initSelectedCategory();
      });

    },
    
    initSelectedCategory(){
        
    
      if(this.isSelectDefinited){
        this.currentCategoryId = this.selected;
        this.loadCustomFieldsValues();
      }
      else
        this.currentCategoryId = "";
    },

   
    loadCustomFieldsValues(){
      
      console.log(this.customFieldsEndpoint);

      if(this.isCustomFieldEndpointDefinited){

        let component = this;
        this.currentStatus = STATUS_LOADING;

        axios.get(this.customFieldsEndpoint)
        .then(function (response) {
          // handle success
          console.log(response);
          response.data.array.forEach(customField => {
            console.log(customField);
          });
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

    getSelectedCategoryIndex(){

    var i = 0;

      while(i < this.categoryList.length && this.currentCategory != this.categoryList[i].id ){

        i++;

      };

      return i;

    }
  

  },
  mounted() {
      this.currentStatus = STATUS_IDLE;
              console.log(this.customFieldsEndpoint);

      this.loadCategories();
  },

}
</script>
