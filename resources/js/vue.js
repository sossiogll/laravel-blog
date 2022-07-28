import CommentForm from './components/comments/CommentForm'
import CommentList from './components/comments/CommentList'
import Like from './components/Like'
import ImageLoaderForm from './components/imageForm/ImageLoaderForm.vue'
import CategorySelector from './components/CategorySelector.vue'
import ImagePicker from './components/ImagePicker.vue'
import Vue from 'vue'

Vue.config.productionTip = true

window.Event = 

new Vue({
  el: '#app',

  components: {
    CommentForm,
    CommentList,
    Like,
    ImageLoaderForm,
    ImagePicker,
    CategorySelector
  },

  mounted() {
    $('[data-confirm]').on('click', () => {
      return confirm($(this).data('confirm'))
    })
  }
});
