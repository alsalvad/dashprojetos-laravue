<template>
  <div class="help" :class="{active: helpStatus}" @click="hideHelp">
    <div class="help-body">

      <div class="col-lg-12">
        <i class="fa fa-times" tile="Fechar" @click="hideHelp"></i>
        <div class="row py-5">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                Projetos
              </div>
              <div class="card-body">
                <table class="table">
                  <tr>
                    <td>
                      <i class="fa fa-file"></i>
                    </td>
                    <td>
                      Cadastra novo projeto
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <i class="fa fa-edit"></i>
                    </td>
                    <td>
                      Clique para habilitar a edição dos projetos já cadastrados
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <b>::</b> Nome do projeto
                    </td>
                    <td>
                      Ao habilitar a edição de projetos, arraste o projeto segurando o ícone <b>::</b> para reordená-lo
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <b>http</b>
                    </td>
                    <td>
                      Utilize para criar um host alternativo. <br>
                      Útil para integração de apis em que é necessário acessar a url por um serviço de túnel, como Ngrok.
                      Ao informar o host, ao clicar em <i class="fa fa-external-link action"></i>, você acessará o link com o host padrão substituído pelo host informado.
                      <hr>
                      Ex.: Ao informar o host "hotdog-com-pure"<br>
                      O link original acessa <i>http://google.com/endpoint</i> <br>
                      Com host alternativo, acessará <i>http://hotdog-com-pure.com/endpoint</i>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      Senha do dia
                    </td>
                    <td>
                      Clique sobre a senha para copiá-la para a área de transferência
                    </td>
                  </tr>
                </table>
                <hr>
                <h6>Cadastrando novos projetos</h6>
                <p>Ao abrir a tela de cadastro de projetos, primeiro informe o nome do grupo e, em seguida, informe os links para o projeto</p>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                Anotações
              </div>
              <div class="card-body">
                <table class="table">
                  <tr>
                    <td>
                      <button class="btn btn-primary"><i class="fa fa-plus"></i></button>
                    </td>
                    <td>
                      Clique para cadastrar um novo grupo de anotações
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <img src="@/assets/help-notes.png" alt="Help notes">
                    </td>
                    <td>
                      Clique com o botão direito do mouse sobre o grupo para expandir as opções. <br>
                      O grupo pode ter seu nome editado ou pode ser excluído.
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <i class="fa fa-edit"></i>
                    </td>
                    <td>
                      Clique para editar a anotação
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <i class="fa fa-trash text-danger"></i>
                    </td>
                    <td>
                      Clique para excluir a anotação
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
  data(){
    return {
      helpStatus: false
    }
  },
  methods: {
    showHelp(){
      this.helpStatus = true;
    },
    hideHelp(){
      this.helpStatus = false;
    }
  }
});
</script>

<style src="./Help.scss" lang="scss" scoped></style>