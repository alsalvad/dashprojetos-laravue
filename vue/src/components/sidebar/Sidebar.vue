<template>
  <Modal ref="modalProjeto" @updateProjetos="updateProjetos($event)" @closeModal="dragDisabled=false"/>
  <nav class="sidebar">

    <p>
      Senha do dia:
      <div class="senha-do-dia">
        <span class="full" @click="copiarSenha(false)">{{senhaDoDiaInicio}}</span>
        <span class="final" @click="copiarSenha(true)">{{senhaDoDiaFim}}</span>
      </div>
      <i class="fa fa-question-circle ajuda action" @click="$emit('showHelp', true)" title="Ajuda"></i>
      <i class="fa fa-sign-in login action" @click="$emit('showLogin', true);" title="Login"></i>
    </p>
    <h2>
      Projetos
      <div class="actions">
        <i class="fa set-host action" @click="toggleSetHost" title="Editar host">http</i>
        <i class="fa fa-edit action" @click="toggleEditProjects" title="Editar projetos"></i>
        <i class="fa fa-file action" @click="novoProjeto" title="Novo projeto"></i>
      </div>
    </h2>
    <input type="text" v-model="inputHost" class="form-control input-host" :class="{active: editHostStatus}" placeholder="Insira o host">
    <hr>

    <ul class="root">
      <draggable
        v-model="projetos"
        item-key="id"
        :disabled="dragDisabled"
        handle=".handle"
        group="root"
        ghost-class="ghost"
        @change="atualizarPosicao($event, false)"
      >
        <template #item="{element, index}">
          <li :class="{edit: editProjects}" class="group-item">
            <i class="handle"></i>
            <a class="root" v-on:click.prevent="toggleExpand(index)">
              {{ element.titulo }}
              <span>
                <i class="fa fa-edit action" v-on:click.stop="editProjeto(index, element)"></i>
                <i v-if="element.sub && element.sub.length" class="fa fa-chevron-right" :class="{active: isExpanded(index)}"></i>
              </span>
            </a>

            <ul v-if="element.sub && element.sub.length" :class="{active: isExpanded(index)}">
              <draggable
                v-model="element.sub"
                item-key="id"
                :disabled="dragDisabled"
                handle=".handle"
                group="sub"
                ghost-class="ghost"
                @change="atualizarPosicao($event, index)"
              >
                <template #item="{element, index}">
                  <li class="sub">
                    <i class="handle"></i>
                    <a :href="element.url" target="_blank">
                      {{element.titulo}}
                      <a :href="element.url_alternative" target="_blank" title="Acessar com host atual" v-if="inputHost">
                        <i class="fa fa-external-link action"></i>
                      </a>
                    </a>
                  </li>
                </template>
              </draggable>
            </ul>
          </li>
        </template>
      </draggable>
    </ul>
  </nav>
</template>


<script>
import { defineComponent } from "vue";
import api from '@/services/api.js';
import Modal from './modal/Modal.vue';
import draggable from 'vuedraggable'
import { useToast } from "vue-toastification";
import { deleteCookie, getCookie, setCookie } from "../../functions/helpers";
const toast = useToast();

let senhaDoDiaInicio = '04420';
let date = new Date();
let d = (date.getDate() + 20);
let m = ((date.getMonth() + 1) + 11);
let senhaDoDiaFim = `${d}${m}`;
let host = getCookie('host_url_alternative')

export default defineComponent({
  data() {
    return {
      projetos: [],
      expandedGroup: [],
      editProjects: false,
      dragging: false,
      dragDisabled: false,
      senhaDoDiaInicio: senhaDoDiaInicio,
      senhaDoDiaFim: senhaDoDiaFim,
      inputHost: host,
      editHostStatus: false
    };
  },
  components:{
    Modal,
    draggable
  },
  mounted() {
    api.get('/projetos').then(res => {
      res = res.data;
      if(res.error) {
        if(res.errorLogin){
          deleteCookie('dashboardtoken');
          this.$emit('showLogin', true);
        }
        return;
      }
      this.projetos = res.data;
    });
  },
  emits: ['showLogin', 'showHelp'],
  watch: {
    projetos(projetos){
      this.applyUrlAlternative();
    }
  },
  methods: {
    applyUrlAlternative(oldHost='{host}', newHost=null){
      newHost = newHost || getCookie('host_url_alternative');
      this.projetos.map(p => {
        p.sub.map(sub => {
          if(newHost)
          sub.url_alternative = sub.url_alternative.replace(oldHost, newHost);
        });
      });
    },
    copiarSenha(fim=false){
      toast.success('Senha copiada!');
      if(fim){
        navigator.clipboard.writeText(senhaDoDiaFim);
        return;
      }
      navigator.clipboard.writeText(senhaDoDiaInicio + senhaDoDiaFim);
    },
    atualizarPosicao(e, sub) {
      let data = [];
      let tipo = 'grupos';
      let array = this.projetos;

      if(sub != false){
        tipo = 'projetos';
        array = this.projetos[sub].sub;
      }

      array.map(function(item, index){
        data.push({id: item.id, posicao: (index + 1)});
      });

      api.post(`/projetos/atualizar-posicao/`, {tipo: tipo, campos: data}).then(res => {
        if(res.data.errorMsg) toast.error(res.data.errorMsg)
      });
    },
    updateProjetos(e){
      if(e.titulo){
        this.projetos[e.key].titulo = e.titulo;
        return;
      }
      if(e.delete) {
        this.projetos.splice(e.key, 1);
        return;
      }
      if(e.novo){
        this.projetos.push(e.grupo);
        let key = this.projetos.length;
        this.$refs.modalProjeto.showModal(key, e.grupo);
        return;
      }
    },
    novoProjeto(){
      this.$refs.modalProjeto.showModal();
    },
    editProjeto(key, grupo){
      this.$refs.modalProjeto.showModal(key, grupo);
      this.dragDisabled = true;
    },
    toggleEditProjects(){
      this.editProjects = !this.editProjects;
    },
    isExpanded(key){
      return this.expandedGroup.indexOf(key) !== -1 ? 'active' : '';
    },
    toggleExpand(key){
      if(this.isExpanded(key)){
        this.expandedGroup.splice(this.expandedGroup.indexOf(key), 1);
      }else{
        this.expandedGroup.push(key);
      }
    },
    toggleSetHost(){
      this.editHostStatus = !this.editHostStatus;
      this.applyUrlAlternative(getCookie('host_url_alternative'), this.inputHost);
      setCookie('host_url_alternative', this.inputHost, 600);
      if(!this.editHostStatus){
        toast.success('Host salvo');
      }
    }
  }
})
</script>

<style src="./Sidebar.scss" lang="scss" scoped></style>