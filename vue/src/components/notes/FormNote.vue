<template>
  <form method="post" class="form-notes">
    <ckeditor :editor="editor" v-model="inputConteudo" :config="editorConfig" ></ckeditor>
    <div class="row">
      <div class="form-group">
        <button v-if="note.id" @click="this.$emit('hideForm', {key: chave})" type="button" class="btn btn-sm btn-secondary">Cancelar</button>
        <button type="button" class="btn btn-sm btn-primary" :class="{hide: (hideBtnSubmit)}" @click="salvar($event)" id="btn-save">Salvar</button>
      </div>
    </div>
  </form>
</template>

<script>
import { defineComponent } from "vue";
import api from "@/services/api";
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default defineComponent({
  props: {
    chave: Number,
    note: {
      type: Object,
      required: false,
      default: {
        id: null,
        conteudo: null,
        grupo_id: null,
        created_at: null,
        updated_at: null,
        user_id: null,
      }
    }
  },
  watch: {
    inputConteudo(val){
      this.hideBtnSubmit = (val.length == 0);
    },
    note: {
      handler(val){
        if(val.conteudo)
        this.inputConteudo = val.conteudo;
      },
      deep: true,
      immediate: true
    }
  },
  data(){
    return{
      hideBtnSubmit: true,
      inputConteudo: '',
      editor: ClassicEditor,
      editorConfig: {
        placeholder: 'Escrever anotação...',
        toolbar: {
          items: [ 'heading', '|', 'bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'Link', 'insertTable', '|', 'undo', 'redo' ]
        }
      }
    }
  },
  methods:{
    salvar(e){
      let values = this.$props.note;
      let chave = (typeof this.$props.chave != 'undefined') ? this.$props.chave : null;

      let data = {
        id: values.id,
        conteudo: this.inputConteudo,
        grupo_id: values.grupo_id
      };
      let metodo = values.id ? 'put' : 'post';

      api[metodo]('/notes', data).then(res => {
        res = res.data;
        if(res.error) return;

        this.inputConteudo = '';
        this.hideBtnSubmit = true;

        e.target.blur();
        if(chave != null){
          this.$emit('hideForm', {key: chave, data: res.data});
          return;
        }
        this.$emit('pushNotes', {data: res.data});
      });
    }
  }
});
</script>

<style scoped lang="scss">
  .form-notes{
    display: block;
    max-width: 800px;
    position: relative;

    textarea{
      font-size: 13px;
    }

    button{
      margin-top: 5px;
    }

    #btn-save{
      transition: all .2s;
      float: right;

      &.hide{
        height: 0;
        border: 0;
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        overflow: hidden;
      }
    }
  }
</style>