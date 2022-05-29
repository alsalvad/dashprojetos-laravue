
<template>
  <div class="login" :class="{active: loginStatus}">
    <form @submit.prevent="">
      <div class="col-lg-12 form-login" :class="{active: formLoginStatus}">
        <div class="row">
          <div class="col-md-12" style="position: relative;">
            <button
              v-if="isLogado"
              @click="showTrocarSenha"
              class="btn btn-sm btn-primary alterar-senha"
              type="button"
              title="Alterar senha"
            ><i class="fa fa-lock"></i></button>
            <button
              v-if="(isLogado && isAdmin)"
              @click="showNovoUsuario"
              class="btn btn-sm btn-primary novo-user"
              type="button"
              title="Novo usuário"
            ><i class="fa fa-user-plus"></i></button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 form-group">
            <label for="inputUsuario">Usuário</label>
            <input
              v-model="inputUsuario"
              id="inputUsuario"
              type="text"
              class="form-control"
              autocomplete="off"
              :readonly="inputUsuarioReadonly"
              @focus="inputUsuarioReadonly = false"
            >
          </div>
          <div class="col-md-12 form-group">
            <label for="inputSenha">Senha</label>
            <input
              v-model="inputSenha"
              @keyup.enter="Logar"
              id="inputSenha"
              type="password"
              class="form-control"
              autocomplete="off"
            >
          </div>
        </div>
        <div class="row actions mb-3">
          <div class="col-md-6">
            <button class="btn btn-sm btn-secondary" type="button" @click="hideLogin">Fechar</button>
          </div>
          <div class="col-md-6">
            <button class="btn btn-sm btn-primary" type="button" @click="Logar">Logar</button>
          </div>
        </div>
      </div>
    </form>

    <form @submit.prevent="">
      <div class="col-lg-12 form-alterar-senha" :class="{hide: !trocarSenhaStatus}" v-if="isLogado">
        <div class="row">
          <div class="col-md-12" style="position: relative;">
            <button
              @click="showLogin"
              class="btn btn-sm btn-primary alterar-senha"
              type="button"
              title="Login"
            ><i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 form-group">
            <label>Senha atual</label>
            <input v-model="inputSenhaAtual" type="password" id="senha-atual" class="form-control" autocomplete="off">
          </div>
          <div class="col-md-12 form-group">
            <label>Nova senha</label>
            <input v-model="inputNovaSenha" type="password" id="nova-senha" class="form-control" autocomplete="off">
          </div>
          <div class="col-md-12 form-group">
            <label>Confirmar nova senha</label>
            <input v-model="inputConfirmarNovaSenha" @keyup.enter="AlterarSenha" type="password" id="confirmar-senha" class="form-control" autocomplete="off">
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 mb-2">
            <button class="btn btn-sm btn-primary submit" type="button" @click="AlterarSenha">Alterar senha</button>
          </div>
        </div>
      </div>
    </form>

    <form @submit.prevent="">
      <div class="col-lg-12 form-novo-usuario" :class="{hide: !novoUsuarioStatus}" v-if="(isLogado && isAdmin)">
        <div class="row">
          <div class="col-md-12" style="position: relative;">
            <button
              @click="showLogin"
              class="btn btn-sm btn-primary alterar-senha"
              type="button"
              title="Login"
            ><i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 form-group">
            <label>Novo usuário</label>
            <input v-model="inputNovoUsuario" type="text" id="novo-user" class="form-control" autocomplete="off">
          </div>
          <div class="col-md-12 form-group">
            <label class="checkbox">
              <input type="checkbox" v-model="inputIsAdmin">
              Administrador
            </label>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 mb-2">
            <button class="btn btn-sm btn-primary submit" type="button" @click="CriarUsuario">Criar usuário</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import { setCookie, getCookie } from "@/functions/helpers";
import api from "@/services/api";
import { useToast } from "vue-toastification";
const toast = useToast();
const isLogado = getCookie('dashboardtoken');
const isAdmin = getCookie('isadmin') == 'true';

export default defineComponent({
  data() {
    return {
      loginStatus: false,
      inputUsuario: '',
      inputSenha: '',
      inputSenhaAtual: '',
      inputNovaSenha: '',
      inputConfirmarNovaSenha: '',
      inputUsuarioReadonly: true,
      inputNovoUsuario: '',
      inputIsAdmin: false,
      formLoginStatus: true,
      trocarSenhaStatus: false,
      novoUsuarioStatus: false,
      isLogado: isLogado,
      isAdmin: isAdmin,
    }
  },
  methods: {
    Logar(){
      let user = this.inputUsuario;
      let senha = this.inputSenha;

      if(!user.trim().length)
        return toast.error('Informe o usuário');

      if(!senha.trim().length)
        return toast.error('Informe a senha');

      api.post('/auth/login', {user: user, password: senha}).then(res => {
        res = res.data;
        // if(!res.token) return toast.error('Algo deu errado ao gerar o novo token.\nPor favor, tente novamente.');
        if(res.error) return;
        setCookie('dashboardtoken', res.token, 600);
        setCookie('isadmin', res.admin == 1, 600);
        location.reload();
      });
    },
    AlterarSenha(){
      let senhaAtual = this.inputSenhaAtual;
      let novaSenha = this.inputNovaSenha;
      let confirmar = this.inputConfirmarNovaSenha;

      if(!senhaAtual.trim().length)
        return toast.error('Informe a senha atual');

      if(!novaSenha.trim().length)
        return toast.error('Informe a nova senha');

      if(novaSenha.trim().length < 4)
        return toast.error('A nova senha deve conter, pelo menos, 4 caracteres');

      if(!confirmar.trim().length)
        return toast.error('Confirme a nova senha');

      if(novaSenha != confirmar) return toast.error('A nova senha e a senha de confirmação devem ser iguais');

      api.post('/auth/alterar-senha', {senhaAtual: senhaAtual, novaSenha: novaSenha, confirmarSenha: confirmar}).then(res => {
        res = res.data;
        if(res.error) return;
        setCookie('dashboardtoken', res.token, 600);
        toast.success('Senha alterada');
        setTimeout(function(){
          location.reload();
        }, 500);
      });
    },
    CriarUsuario(){
      let user = this.inputNovoUsuario;
      let isAdmin = this.inputIsAdmin;
      if(!user.trim().length) return toast.error('Informe o nome do novo usuário');
      api.post('/auth/criar-usuario', {user:user, isAdmin:isAdmin}).then(res => {
        if(res.data.error) return;
        this.inputNovoUsuario = '';
        this.inputIsAdmin = false;
      });
    },
    showLogin(){
      this.loginStatus = true;
      this.hideAllForms();
      this.formLoginStatus = true;
    },
    hideLogin(e){
      this.loginStatus = false;
      this.hideAllForms();
    },
    showTrocarSenha(){
      this.hideAllForms();
      this.trocarSenhaStatus = true;
    },
    showNovoUsuario(){
      this.hideAllForms();
      this.novoUsuarioStatus = true;
    },
    hideAllForms(){
      this.formLoginStatus = false;
      this.trocarSenhaStatus = false;
      this.novoUsuarioStatus = false;
    }
  },
});
</script>

<style src="./Login.scss" lang="scss" scoped></style>