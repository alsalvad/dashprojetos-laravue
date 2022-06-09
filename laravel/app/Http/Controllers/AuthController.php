<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Response {
  public $request;

  public function __construct(){
    $this->middleware('checkToken', [
      'except' => ['login']
    ]);

    $this->request = new Request;
  }

  public function login(Request $request){
    $data = (object) $request->only('user', 'password', 'token');
    $validator = Validator::make((array) $data, [
      'user' => 'string|required',
      'password' => 'string|required'
    ]);

    if($validator->fails()){
      $this->toast('error', $validator);
      $this->required($validator);
      return $this->response(true);
    }

    $user = User::select('id', 'password', 'admin')->where('user', $data->user)->first();

    if(!$user){
      $this->toast('error', 'Usuário ou senha incorretos');
      return $this->response(true);
    }

    if(!Hash::check($data->password, $user->password)){
      $this->toast('error', 'Usuário ou senha incorretos');
      return $this->response(true);
    }

    Auth::loginUsingId($user->id);
    $this->novoToken($user);
    $this->append(['admin' => $user->admin]);

    return $this->response();
  }

  public function alterarSenha(Request $request){
    $data = (object) $request->only('senhaAtual', 'novaSenha', 'confirmarSenha');
    $validator = Validator::make((array) $data, [
      'senhaAtual' => 'required|string',
      'novaSenha' => 'required|string|min:4',
      'confirmarSenha' => 'required|string|same:novaSenha'
    ]);

    if($validator->fails()){
      $this->toast('error', $validator);
      return $this->response(true);
    }

    if(!Hash::check($data->senhaAtual, Auth::user()->password)){
      $this->toast('error', 'Senha atual incorreta');
      return $this->response(true);
    }

    $user = User::find(Auth::id());
    $user->password = Hash::make($data->novaSenha);

    $this->novoToken($user);
    return $this->response();
  }

  protected function novoToken($user){
    $novoToken = Hash::make(Auth::id() . date('dmYHis').rand(1,99));
    $user->token_dashboard = $novoToken;
    $user->save();
    $this->append(['token' => $novoToken]);
  }

  public function criarUsuario(Request $request){
    if(Auth::user()->admin != 1){
      $this->toast('error', 'Você não tem permissão para adicionar um novo usuário');
      return $this->response(true);
    }

    $data = (object) $request->only('user', 'isAdmin');
    $validator = Validator::make((array) $data, [
      'user' => 'required|string|max:50|unique:users,user',
      'isAdmin' => 'nullable|boolean'
    ]);

    if($validator->fails()){
      $this->toast('error', $validator);
      return $this->response(true);
    }

    try{
      $user = new User;
      $user->name = $data->user;
      $user->email = $data->user;
      $user->user = $data->user;
      $user->admin = $data->isAdmin ? 1 : 0;
      $user->password = Hash::make('123');
      $user->token_dashboard = md5(date('dmYHis').rand(0,99));
      $user->save();

      $grupo = new Grupo;
      $grupo->titulo = 'Geral';
      $grupo->tipo = 'notes';
      $grupo->user_id = $user->id;
      $grupo->default = 1;
      $grupo->save();

      DB::commit();
      $this->toast('success', 'Usuário criado');
      return $this->response();
    }catch(\Exception $e){
      DB::rollback();
      $this->toast('error', $e->getMessage());
      return $this->response(true);
    }
  }
}
