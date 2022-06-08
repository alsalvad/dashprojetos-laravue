<template>
  <div class="modal" :class="{active: activeModal}">
    <div class="header">
      <div class="input-group">
        <input type="text" id="inputNomegrupo" class="form-control" placeholder="Nome do grupo" v-model="inputNomeGrupo">
        <button class="btn btn-primary" type="button" @click="saveNomeGrupo"><i class="fa fa-check"></i></button>
      </div>

      <label class="checkbox mt-2" v-if="isAdmin && grupo.id">
        <input v-model="inputPublico" type="checkbox" @change="updatePublico">
        Público
      </label>
      <button class="btn btn-danger float-right" @click="excluirGrupo" v-if="grupo.id">Excluir</button>
    </div>
    <div class="body">

      <div class="row" :class="{hide: !this.grupo.id}">
        <label>Novo link</label>
        <div class="input-group col-md-12 mb-4">
          <input type="text" class="form-control" placeholder="Título" v-model="novoLinkTitulo">
          <input type="text" class="form-control" placeholder="Url" v-model="novoLinkUrl">
          <button class="btn btn-primary" type="button" @click="inserirLink"><i class="fa fa-check"></i></button>
        </div>
      </div>

      <h6 v-if="links.length">
        <span>
          Links
        </span>
      </h6>

      <div v-for="(link, key) in links" class="row">
        <div class="input-group col-md-12 mb-3">
          <input type="text" class="form-control" placeholder="Título" v-model="inputTitulo[link.id]">
          <input type="text" class="form-control" placeholder="Url" v-model="inputUrl[link.id]">
          <button class="btn btn-danger" type="button" @click="deletarLink(key, link)"><i class="fa fa-trash"></i></button>
          <button class="btn btn-primary" type="button" @click="updateLink(key, link)"><i class="fa fa-check"></i></button>
        </div>
      </div>

    </div>

    <div class="footer">
      <button class="btn btn-sm btn-secondary" @click="dismissModal">Fechar</button>
    </div>
  </div>
</template>

<style src="./Modal.scss" lang="scss" scoped></style>

<script>
import { defineComponent } from "vue";
import api from "@/services/api";
import { useToast } from "vue-toastification";
import { getCookie } from "@/functions/helpers";

const toast = useToast();

export default defineComponent({
  data(){
    return this.initialState()
  },
  watch :{
    links: {
      handler(val){
        this.grupo.sub = val;
      },
      deep: true
    }
  },
  emits: ['deleteGrupo', 'closeModal', 'novoGrupo', 'updateGrupos'],
  methods: {
    initialState(reset=false){
      let data =  {
        key: null,
        activeModal: false,
        inputNomeGrupo: '',
        grupo: {},
        links: [],
        inputTitulo: [],
        inputUrl: [],
        inputPublico: false,
        novoLinkTitulo: '',
        novoLinkUrl: '',
        isAdmin: (getCookie('isadmin') == 'true')
      }

      if(reset) Object.assign(this.$data, data);

      return data;
    },
    showModal(key, grupo=null){
      if(!grupo) {
        this.initialState(true);
        this.activeModal = true;
        return;
      }

      api.get(`/projetos/links/${grupo.id}`).then(({data}) => {
        if(data.error) return;

        this.grupo = grupo;
        this.inputNomeGrupo = grupo.titulo;
        this.links = data.data;
        this.inputPublico = grupo.publico == 1;

        data.data.forEach((link) => {
          this.inputTitulo[link.id] = link.titulo;
          this.inputUrl[link.id] = link.url;
        });
      });
      this.key = key;
      this.activeModal = true;
    },
    dismissModal(){
      this.activeModal = false;
      this.$emit('closeModal')
    },
    inserirLink(){
      if(!this.grupo.id) return toast.error('Informe o nome do grupo');
      let titulo = this.novoLinkTitulo;
      let url = this.novoLinkUrl;

      if(!titulo.trim()){
        toast.error('Informe um título');
        return;
      }
      if(!url.trim()){
        toast.error('Informe uma url');
        return;
      }

      api.post('/projetos/link', {titulo: titulo, url: url, grupo_id: this.grupo.id}).then(({data}) => {
        if(data.error) return;
        toast.success('Link inserido');
        this.links.push(data.link);

        this.novoLinkTitulo = '';
        this.novoLinkUrl = '';

        this.inputTitulo[data.link.id] = data.link.titulo;
        this.inputUrl[data.link.id] = data.link.url;
      });
    },
    updateLink(key, link){
      let titulo = this.inputTitulo[link.id];
      let url = this.inputUrl[link.id];

      if(!titulo.trim()) {
        toast.error('Informe um título');
        return;
      }
      if(!url.trim()) {
        toast.error('Informe uma url');
        return;
      }

      api.put('/projetos/link', {id: link.id, titulo: titulo, url: url, grupo_id: this.grupo.id}).then(({data}) => {
        if(data.error) return;
        toast.success('Link atualizado');
        this.links[key] = data.link;
      });
    },
    updatePublico(){
      if(!this.grupo.id) return;

      api.put('/projetos/grupo/publico', {id: this.grupo.id, publico: this.inputPublico}).then(({data}) => {
        if(data.error) return;
          this.$emit('updateGrupos', data.grupo);
      });
    },
    deletarLink(key, link){
      api.delete(`/projetos/link/${link.id}`).then(res => {
        if(res.data.error) return;
        this.links.splice(key, 1);
        toast.success('Link removido')
      });
    },
    saveNomeGrupo(){
      let titulo = this.inputNomeGrupo;
      if(!titulo.trim()){
        toast.error('Informe o nome do grupo');
        return;
      }

      let metodo = 'post';
      let data = {titulo: titulo};
      if(this.key != null){
        metodo = 'put';
        data.id = this.grupo.id;
      }

      api[metodo]('/projetos/grupo', data).then(({data}) => {
        if(data.error) return;

        if(this.grupo.id){
          this.grupo.titulo = titulo;
          toast.success('Grupo atualizado');
          return;
        }

        this.grupo = data.grupo;
        this.$emit('novoGrupo', data.grupo);
        toast.success('Grupo adicionado');
      });
    },
    excluirGrupo(e){
      this.$contextmenu({
        x: e.x,
        y: e.y,
        items: [
          {
            icon: 'fa fa-trash',
            label: 'Confirmar',
            onClick: () => {
              api.delete(`/projetos/grupo/${this.grupo.id}`).then(({data}) => {
                if(data.error) return;
                toast.success('Projeto excluído');
                this.dismissModal();
                this.$emit('deleteGrupo', this.grupo);
                this.initialState();
              });
            }
          }
        ]
      });
    }
  }
});
</script>