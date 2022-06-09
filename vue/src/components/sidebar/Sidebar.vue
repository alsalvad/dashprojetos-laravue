<template>
  <Modal ref="modalProjeto" @updateGrupos="updateGrupos" @deleteGrupo="deleteGrupo" @novoGrupo="novoGrupo" @closeModal="dragDisabled=false"/>
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
    <div class="input-group input-host" :class="{active: editHostStatus}">
      <input type="text" v-model="inputHost" class="form-control " placeholder="Insira o host" @keyup.enter="toggleSetHost">
      <button type="button" class="btn btn-sm btn-primary" @click="toggleSetHost"><i class="fa fa-check"></i></button>
    </div>
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
        <template #item="{element, index}" :key="element.id">
          <li :class="{edit: editProjects}" class="group-item">
            <i class="handle"></i>
            <a class="root" v-on:click.prevent="toggleExpand(element.id)">
              {{ element.titulo }}
              <span>
                <i class="fa fa-edit action" v-on:click.stop="editProjeto(element.id, element)"></i>
                <i v-if="element.sub && element.sub.length" class="fa fa-chevron-right" :class="{active: isExpanded(element.id)}"></i>
              </span>
            </a>

            <ul v-if="element.sub && element.sub.length" :class="{active: isExpanded(element.id)}">
              <draggable
                v-model="element.sub"
                item-key="id"
                :disabled="dragDisabled"
                handle=".handle"
                group="sub"
                ghost-class="ghost"
                @change="atualizarPosicao($event, index)"
              >
                <template #item="{element, index}" :key="element.id">
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

    <hr v-if="publicos.length">
    <span v-if="publicos.length">Links Públicos</span>
    <ul class="root">
      <draggable
        v-model="publicos"
        item-key="id"
        :disabled="dragDisabled"
        handle=".handle"
        group="publico"
        ghost-class="ghost"
        @change="atualizarPosicao($event, false, true)"
      >
        <template #item="{element, index}" :key="element.id">
          <li :class="{edit: (editProjects && isAdmin)}" class="group-item">
            <i class="handle" v-if="isAdmin"></i>
            <a class="root" v-on:click.prevent="toggleExpand(element.id)">
              {{ element.titulo }}
              <span>
                <i class="fa fa-edit action" v-on:click.stop="editProjeto(element.id, element)" v-if="isAdmin"></i>
                <i v-if="element.sub && element.sub.length" class="fa fa-chevron-right" :class="{active: isExpanded(element.id)}"></i>
              </span>
            </a>

            <ul v-if="element.sub && element.sub.length" :class="{active: isExpanded(element.id)}">
              <draggable
                v-model="element.sub"
                item-key="id"
                :disabled="dragDisabled"
                handle=".handle"
                group="sub_publico"
                ghost-class="ghost"
                @change="atualizarPosicao($event, index, true)"
              >
                <template #item="{element, index}" :key="element.id">
                  <li class="sub">
                    <i class="handle" v-if="isAdmin"></i>
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

const isAdmin = (getCookie('isadmin') == 'true');


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
      publicos: [],
      expandedGroup: [],
      editProjects: false,
      dragging: false,
      dragDisabled: false,
      senhaDoDiaInicio: senhaDoDiaInicio,
      senhaDoDiaFim: senhaDoDiaFim,
      inputHost: host,
      editHostStatus: false,
      isAdmin: isAdmin
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
      this.projetos = res.projetos;
      this.publicos = res.publicos;
      this.applyUrlAlternative();
    });
  },
  emits: ['showLogin', 'showHelp'],
  methods: {
    applyUrlAlternative(oldHost='{host}', newHost){
      newHost = newHost || getCookie('host_url_alternative');
        setTimeout(() => {
          this.projetos.map(p => {
            p.sub.map(sub => {
              if(newHost)
                sub.url_alternative = sub.url_alternative.replace(oldHost, newHost);
            });
          });

          this.publicos.map(p => {
            p.sub.map(sub => {
              if(newHost)
                sub.url_alternative = sub.url_alternative.replace(oldHost, newHost);
            });
        });
      },500);
    },
    copiarSenha(fim=false){
      toast.success('Senha copiada!');
      if(fim){
        navigator.clipboard.writeText(senhaDoDiaFim);
        return;
      }
      navigator.clipboard.writeText(senhaDoDiaInicio + senhaDoDiaFim);
    },
    atualizarPosicao(e, idSub, publico = false) {
      let data = [];
      let tipo = 'grupos';
      let grupo = publico ? 'publicos' : 'projetos';
      let array = this[grupo];

      if(typeof idSub == 'number'){
        tipo = 'projetos';
        array = this[grupo][idSub].sub;
      }

      array.map(function(item, index){
        data.push({id: item.id, posicao: (index + 1)});
      });

      api.post(`/projetos/atualizar-posicao`, {tipo: tipo, campos: data}).then(res => {
        if(res.data.errorMsg) toast.error(res.data.errorMsg)
      });
    },
    novoGrupo(grupo){
      this.projetos.push(grupo);
    },
    deleteGrupo(grupo){
      if(grupo.publico){
        let key = this.publicos.findIndex(item => item.id == grupo.id);
        this.publicos.splice(key, 1);
        return;
      }

      let key = this.projetos.findIndex(item => item.id == grupo.id);
      this.projetos.splice(key, 1);
    },
    updateGrupos(grupo){
      if(grupo.publico){
        let key = this.projetos.findIndex(item => item.id == grupo.id);
        this.projetos.splice(key, 1);
        this.publicos.push(grupo);
        return;
      }

      let key = this.publicos.findIndex(item => item.id == grupo.id);
      this.publicos.splice(key, 1);
      this.projetos.push(grupo);
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
      let old = getCookie('host_url_alternative');
      this.applyUrlAlternative(old || '{host}', this.inputHost);
      setCookie('host_url_alternative', this.inputHost, 600);
      if(!this.editHostStatus){
        toast.success('Host salvo');
      }
    }
  }
})
</script>

<style src="./Sidebar.scss" lang="scss" scoped></style>