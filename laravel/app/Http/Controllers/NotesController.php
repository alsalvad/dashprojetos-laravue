<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotesController extends Response {
  public function __construct() {
    $this->middleware('checkToken');
  }

  public function getGrupos(){
    $grupos = Grupo::where('user_id', Auth::id())->where('tipo', 'notes')->get();
    $this->data($grupos);
    return $this->response();
  }

  public function getNotes($grupo_id){
    $notes = Note::where('user_id', Auth::id())->where('grupo_id', $grupo_id)->orderBy('id', 'desc')->paginate(10);
    $this->data($notes);
    return $this->response();
  }

  public function saveNote(Request $request){
    $data = (object) $request->only('conteudo', 'grupo_id', 'id');
    $validator = Validator::make((array) $data, [
      'conteudo' => 'required|string',
      'grupo_id' => 'required|exists:grupos,id',
      'id' => 'nullable|integer'
    ], [
      'grupo_id.exists' => 'Selecione um grupo para salvar a anotação'
    ]);

    if($validator->fails()){
      $this->toast('error', $validator);
      return $this->response(true);
    }

    $note = isset($data->id) ? Note::find($data->id) : new Note;
    $note->conteudo = $data->conteudo;
    $note->grupo_id = $data->grupo_id;
    $note->user_id = Auth::id();
    $note->save();

    $this->data($note);
    $this->toast('success', 'Anotação salva');
    return $this->response();
  }

  public function deleteNote($id){
    try{
      Note::where('user_id', Auth::id())->where('id', $id)->delete();
      return $this->response();
    }catch(\Exception $e){
      $this->toast('error', $e->getMessage());
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
    $grupo->tipo = 'notes';
    $grupo->user_id = Auth::id();
    $grupo->save();

    $this->data($grupo);
    return $this->response();
  }

  public function deleteGrupo($id){
    try{
      Grupo::where('id', $id)->where('user_id', Auth::id())->delete();
      Note::where('grupo_id', $id)->where('user_id', Auth::id())->delete();

      DB::commit();
      return $this->response();
    }catch(\Exception $e){
      DB::rollBack();
      $this->toast('error', $e->getMessage());
      return $this->response(true);
    }
  }
}
