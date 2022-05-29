<template>
  <Sidebar @showLogin="showLogin" @showHelp="showHelp"/>
  <Notes />
  <Login ref="modalLogin" />
  <Help ref="screenHelp"/>
</template>

<script>
import { defineComponent } from 'vue';
import Sidebar from './components/sidebar/Sidebar.vue'
import Notes from './components/notes/Notes.vue';
import Login from './components/auth/Login.vue';
import Help from './components/help/Help.vue';

export default defineComponent({
  data(){
    return{

    }
  },
  methods: {
    showLogin(){
      this.$refs.modalLogin.showLogin();
    },
    showHelp(){
      this.$refs.screenHelp.showHelp();
    }
  },
  components: {
    Sidebar,
    Notes,
    Login,
    Help
  }
});

</script>

<style src="./assets/custom.scss" lang="scss"></style>
