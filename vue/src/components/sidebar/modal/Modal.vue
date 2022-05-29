<template>
  <div class="modal" :class="{active: activeModal}">
    <div class="header">
      <div class="input-group">
        <input type="text" id="inputNomegrupo" class="form-control" placeholder="Nome do grupo" v-model="inputNomeGrupo">
        <button class="btn btn-primary" type="button" @click="saveNomeGrupo"><i class="fa fa-check"></i></button>
      </div>

      <button class="btn btn-danger float-right" @click="excluirGrupo" v-if="grupoInfo.id">Excluir</button>
    </div>
    <div class="body">

      <div class="row">
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

const toast = useToast();

export default defineComponent({
  data(){
    return this.initialState()
  },
  watch :{
    links: {
      handler(val){
        this.grupoInfo.sub = val;
      },
      deep: true
    }
  },
  methods: {
    initialState(reset=false){
      let data =  {
        key: null,
        activeModal: false,
        inputNomeGrupo: '',
        grupoInfo: {},
        links: [],
        inputTitulo: [],
        inputUrl: [],
        novoLinkTitulo: '',
        novoLinkUrl: '',
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

      api.get(`/projetos/links/${grupo.id}`).then(res => {
        if(res.data.error) return;

        this.grupoInfo = grupo;
        this.inputNomeGrupo = grupo.titulo;
        this.links = res.data.data;

        res.data.data.forEach((link) => {
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
      if(!this.grupoInfo.id) return toast.error('Informe o nome do grupo');
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

      api.post('/projetos/link', {titulo: titulo, url: url, grupo_id: this.grupoInfo.id}).then(res => {
        if(res.data.error) return;
        let data = res.data.data;
        toast.success('Link inserido');
        this.links.push(res.data.data);
        this.novoLinkTitulo = '';
        this.novoLinkUrl = '';

        this.inputTitulo[data.id] = data.titulo;
        this.inputUrl[data.id] = data.url;
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

      api.put('/projetos/link', {id: link.id, titulo: titulo, url: url, grupo_id: this.grupoInfo.id}).then(res => {
        if(res.data.error) return;
        toast.success('Link atualizado')
        this.links[key] = res.data.data;
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
        data.id = this.grupoInfo.id;
      }

      api[metodo]('/projetos/grupo', data).then(res => {
        if(res.data.errorMsg){
          toast.error(res.data.errorMsg);
          return;
        }

        this.grupoInfo = res.data.data;
        if(this.key != null){
          toast.success('Grupo atualizado');
          this.$emit('updateProjetos', {key: this.key, titulo: this.inputNomeGrupo});
          return;
        }
        toast.success('Grupo adicionado');
        this.$emit('updateProjetos', {novo: true, grupo: res.data.data});
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
              api.delete(`/projetos/grupo/${this.grupoInfo.id}`).then(res => {
                if(res.data.error) return;
                toast.success('Projeto excluído');
                this.dismissModal();
                this.$emit('updateProjetos', {key: this.key, delete: true});
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