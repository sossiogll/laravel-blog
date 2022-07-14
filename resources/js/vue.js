import CommentForm from './components/comments/CommentForm'
import CommentList from './components/comments/CommentList'
import Like from './components/Like'
import ImageLoaderForm from './components/imageForm/ImageLoaderForm.vue'
import Vue from 'vue'

Vue.config.productionTip = true

window.Event = 

new Vue({
  el: '#app',

  components: {
    CommentForm,
    CommentList,
    Like,
    ImageLoaderForm
  },

  mounted() {
    $('[data-confirm]').on('click', () => {
      return confirm($(this).data('confirm'))
    })
  }
});
