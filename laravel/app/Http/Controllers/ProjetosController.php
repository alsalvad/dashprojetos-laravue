<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use App\Models\Projeto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjetosController extends Response{
  public function __construct() {
    $this->middleware('checkToken');
  }

  public function getProjetos(){
    $projetos = Grupo::where('user_id', Auth::id())->where('tipo', 'projetos')->where('publico', 0)->with('sub')->orderBy('posicao')->orderBy('id', 'asc')->get();
    $publicos = Grupo::where('tipo', 'projetos')->where('publico', 1)->with('sub')->orderBy('posicao')->orderBy('id', 'asc')->get();
    $this->append(compact('projetos', 'publicos'));
    return $this->response();
  }

  public function getLinks($grupo_id){
    try{
      $links = Projeto::where('user_id', Auth::id())->where('grupo_id', $grupo_id)->orderBy('posicao')->get();
      $this->data($links);
      return $this->response();
    }catch(\Exception $e){
      $this->toast('error', $e->getMessage());
      return $this->response(true);
    }
  }

  public function saveLink(Request $request){
    $data = (object) $request->only('titulo', 'url', 'grupo_id', 'id');
    $validator = Validator::make((array) $data, [
      'titulo' => 'required|string|max:50',
      'url' => 'required|string',
      'grupo_id' => 'required|integer|exists:grupos,id',
      'id' => 'nullable|integer|exists:projetos,id',
    ]);

    if($validator->fails()){
      $this->toast('error', $validator);
      return $this->response(true);
    }

    $link = isset($data->id) ? Projeto::find($data->id) : new Projeto;
    $link->titulo = $data->titulo;
    $link->url = $data->url;
    $link->user_id = Auth::id();
    $link->grupo_id = $data->grupo_id;
    $link->save();

    $this->append(compact('link'));
    return $this->response();
  }

  public function atualizarPosicao(Request $request){
    $data = (object) $request->only('tipo', 'campos');

    if(!count($data->campos) || !$data->tipo){
      $this->toast('error', 'Houve um erro ao tentar atualizar as posições');
      return $this->response(true);
    }

    foreach($data->campos as $campo){
      if($data->tipo == 'grupos')
        Grupo::where('user_id', Auth::id())->where('id', $campo['id'])->update(['posicao' => $campo['posicao']]);

      if($data->tipo == 'projetos')
        Projeto::where('user_id', Auth::id())->where('id', $campo['id'])->update(['posicao' => $campo['posicao']]);
    }
    $this->data($data);
    return $this->response();
  }

  public function deleteLink($id){
    try{
      Projeto::where('user_id', Auth::id())->where('id', $id)->delete();
      DB::commit();
      return $this->response();
    }catch(\Exception $e){
      DB::rollBack();
      $this->toast('error', $e->getMesage());
      return $this->response(true);
    }
  }

  public function saveGrupo(Request $request){
    $data = (object) $request->only('titulo', 'id');
    $validator = Validator::make((array) $data, [
      'titulo' => 'required|string|max:100',
      'id' => 'nullable|integer'
    ]);

    if($validator->fails()){
      $this->toast('error', $validator);
      return $this->response(true);
    }

    $grupo = isset($data->id) ? Grupo::find($data->id) : new Grupo;
    $grupo->titulo = $data->titulo;
    $grupo->tipo = 'projetos';
    $grupo->user_id = Auth::id();
    $grupo->save();

    $this->append(compact('grupo'));
    return $this->response();
  }

  public function updatePublico(Request $request){
    $data = (object) $request->only('id', 'publico');
    $validator = Validator::make((array)$data,[
      'id' => 'required|integer|exists:grupos,id',
      'publico' => 'required|boolean'
    ]);

    if($validator->fails()){
      $this->toast('error', $validator);
      return $this->response(true);
    }

    $grupo = Grupo::find($data->id);
    $grupo->publico = $data->publico;
    $grupo->save();

    $this->append(compact('grupo'));
    return $this->response();
  }

  public function deleteGrupo($id){
    try{
      Grupo::where('user_id', Auth::id())->where('id', $id)->delete();
      Projeto::where('user_id', Auth::id())->where('grupo_id', $id)->delete();
      DB::commit();
      return $this->response();
    }catch(\Exception $e){
      DB::rollBack();
      $this->toast('error', $e->getMessage());
      return $this->response(true);
    }
  }
}
