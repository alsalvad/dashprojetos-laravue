<template>
  <main @click="toggleNovoGrupo(false)">
    <nav>
      <div class="nav nav-tabs" id="notes-tab" role="tablist">
        <button
          v-for="(grupo, key) in grupos"
          @click="loadNotes(grupo.id)"
          :class="{active: (key == 0)}"
          :id="`nav-${grupo.id}-tab`"
          :data-bs-target="`#nav-${grupo.id}`"
          :aria-controls="`nav-${grupo.id}`"
          :aria-selected="(key == 0)"
          class="nav-link"
          role="tab"
          data-bs-toggle="tab"
          type="button"
          @contextmenu="grupoContext($event, key, grupo)"
        >
          <template v-if="editGrupo[key]">
            <div class="box edit-grupo-notes">
              <input
                v-model="editGrupoInput"
                @keyup.esc="editGrupo[key] = false"
                @keyup.enter="editarGrupo(key, grupo.id)"
                type="text"
                class="form-control form-control-sm"
                placeholder="Informe o título"
              >
              <i class="fa fa-check btn btn-sm btn-primary" @click="editarGrupo(key, grupo.id)"></i>
            </div>
          </template>
          <template v-else>
            {{grupo.titulo}}
          </template>
        </button>
        <button type="button" class="nav-link novo-grupo" :class="{expand: novoGrupoExpanded}" @click.stop="toggleNovoGrupo(true)" aria-controls="novo-grupo" aria-selected="true">
          <i class="fa fa-plus"></i>
          <div class="box novo-grupo-notes">
            <input
              v-model="novoGrupoInput"
              @keyup.esc="toggleNovoGrupo(false)"
              @keyup.enter="novoGrupo"
              type="text"
              class="form-control"
              placeholder="Novo Grupo"
              name="titulo"
            >
            <i class="fa fa-check btn btn-sm btn-primary" @click="novoGrupo"></i>
          </div>
        </button>
      </div>
    </nav>

    <div class="tab-content" id="notes-tabContent">
      <FormNote :note="{grupo_id: grupoAtivo}" @pushNotes="pushNotes"/>
      <br>
      <div
        v-for="(grupo, key) in grupos"
        :tabindex="key"
        :id="`nav-${grupo.id}`"
        :aria-labelledby="`nav-${grupo.id}-tab`"
        :class="{active: (key == 0), show: (key == 0)}"
        role="tabpanel"
        class="tab-pane fade"
      >

        <Pagination :pages="pages" :source="grupo.id" @changePage="loadNotes(grupo.id, $event)" v-if="pages.length > 3"/>
          <div class="loading-notes" :class="{active: loading}">
            <i class="loading-icon"></i>
          </div>
          <div class="alert" :class="{hide: notes.length}">Nenhuma anotação encontrada</div>
          <div
            v-for="(note, key) in notes"
            class="note-item"
            :class="{hide: loading}"
          >
            <div class="header">
              <div class="info">
                {{mask.dateBR(note.created_at, true)}}
              </div>
              <div class="actions">
                <i class="fa fa-edit" @click="toggleForm({key: key}, true)"></i>
                <i class="fa fa-trash" @click="deleteNote(note.id, key)"></i>
              </div>
            </div>
            <FormNote :note="note" :chave="key" @hideForm="toggleForm($event, false)" v-if="editNote[key]"/>
            <div class="conteudo" v-html="note.conteudo" v-else></div>
          </div>
        <Pagination :pages="pages" :source="grupo.id" @changePage="loadNotes(grupo.id, $event)" v-if="pages.length > 3"/>
      </div>

    </div>
  </main>
</template>

<script>
  import { defineComponent } from 'vue';
  import api from '@/services/api';
  import FormNote from './FormNote.vue';
  import { mask } from '@/functions/helpers';
  import { useToast } from 'vue-toastification';
  import Pagination from '@/components/utils/pagination/Pagination.vue';

  const toast = useToast();

  export default defineComponent({
    data(){
      return{
        grupos: [],
        novoGrupoExpanded: false,
        notes: [],
        editNote: [],
        editGrupo: [],
        editGrupoVal: [],
        grupoAtivo: null,
        pages: {},
        novoGrupoInput: '',
        editGrupoInput: '',
        mask: mask,
        loading: true
      };
    },
    mounted(){
      let $this = this;
      api.get('/notes').then(res => {
        res = res.data;
        if(res.error) return;

        this.grupos = res.data;
        res.data.map(function(note){
          if(note.default){
            $this.loadNotes(note.id);
            return;
          }
        });
      });
    },
    methods: {
      grupoContext(e, key, grupo) {
        if(grupo.default) return;
        e.preventDefault();
        this.$contextmenu({
          x: e.x,
          y: e.y,
          items: [
            {
              icon: 'fa fa-edit',
              label: 'Editar',
              onClick: () => {
                this.editGrupo[key] = !this.editGrupo[key];
                this.editGrupoInput = grupo.titulo;
              }
            },
            {
              label: "Mais",
              children: [
                {
                  icon: 'fa fa-trash',
                  label: 'Excluir',
                  onClick: () => {
                    api.delete(`/notes/grupo/${grupo.id}`).then(res => {
                      res = res.data;
                      if(res.error) return;

                      this.grupos.splice(key, 1);
                    });
                  }
                }
              ]
            }
          ]
        });
      },
      toggleNovoGrupo(val){
        this.novoGrupoExpanded = val;
      },
      novoGrupo(){
        let titulo = this.novoGrupoInput;
        if(titulo.length == 0)
          return toast.error('Informe um nome para o grupo');

        api.post('/notes/grupo', { titulo: titulo }).then(res => {
          res = res.data;
          if(res.error) return;

          this.grupos.push(res.data);
          this.novoGrupoInput = '';
          this.toggleNovoGrupo(false);
        });
      },
      editarGrupo(key, id){
        let titulo = this.editGrupoInput;

        if(titulo.length == 0)
          toast.info('Informe um nome para o grupo');

        api.put(`/notes/grupo/`, {titulo: titulo, id: id}).then(res => {
          res = res.data;
          if(res.error) return;
          this.grupos[key] = res.data;
          this.editGrupo[key] = false;
        });
      },
      loadNotes(id, page=1){
        this.grupoAtivo = id;
        this.loading = true;
        api.get(`/notes/${id}?page=${page}`).then(res => {
          if(res.data.error) return;
          this.loading = false;
          this.notes = res.data.data.data;
          this.pages = res.data.data.links;
        });
      },
      pushNotes(e){
        if(e.data)
          this.notes.unshift(e.data);
      },
      toggleForm(e, showOrHide){
        this.editNote[e.key] = showOrHide;
        if(e.data)
          this.notes[e.key] = e.data;
      },
      deleteNote(id, key){
        api.delete(`/notes/${id}`).then(res => {
          if(res.data.error) return;
          this.notes.splice(key, 1);
          toast.warning('Anotação excluída');
        });
      }
    },
    components: {
      FormNote,
      Pagination
    }
  });
</script>

<style src="./Notes.scss" lang="scss" scoped></style>