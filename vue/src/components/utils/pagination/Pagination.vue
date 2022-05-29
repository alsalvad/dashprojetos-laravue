<template>
  <ul class="pagination">
    <li
      v-for="(page, key) in pages"
      :class="{active: page.active, disabled: !page.url}"
      class="page"
      @click="changePage(key, page)"
      v-html="pageLabel(key, page.label)"
    >
    </li>
  </ul>
</template>

<style src="./Pagination.scss" lang="scss" scoped></style>

<script>
import { defineComponent, reactive } from "vue";
  const store = reactive({
    currentPage: []
  });

  export default defineComponent({
    data(){
      return {
        currentPage: [],
        showFirstPage: false,
        showLastPage: false,
        showPrevPage: false,
      }
    },
    props: {
      source: {
        type: Number,
        required: true
      },
      pages: {
        type: Object,
        required: true
      }
    },
    watch: {
      pages(){
        store.currentPage[this.source] = 1;
      }
    },
    methods: {
      changePage(key, page){
        let p = null;
        if(key == 0){
          p = this.currentPage[this.source] - 1;
        }else if((key+1) == this.pages.length){
          p = this.currentPage[this.source] + 1;
        }else{
          p = page.label;
        }
        if(p == '...') return;
        if(p == 0) return;
        if(this.pages.length <= 3) return;
        if(p == this.currentPage[this.source]) return;

        this.currentPage[this.source] = parseInt(p);
        this.$emit('changePage', p);
      },
      pageLabel(key, label){
        if(key == 0){
          return '<i class="fa fa-angle-double-left"></i>';
        }
        if((key+1) == this.pages.length){
          return '<i class="fa fa-angle-double-right"></i>';
        }
        return label;
      }
    }
  });
</script>