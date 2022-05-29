<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Response extends Controller {
  private $error = 0;
  private $url=null;
  private $required = null;
  private $alert = null;
  private $toast = null;
  private $data = null;
  private $append = null;

  /**
   * $type - success, info, warning, error.
   * $message = String, array ou instancia de Validator
   */
  public function toast($type, $message){
    if(is_object($message) and get_class($message) == 'Illuminate\Validation\Validator') $message = $message->errors()->all();
    if(is_array($message)) $message = implode('\n', $message);
    $this->toast = compact('type', 'message');
  }

  /**
   * $type - success, info, warning, error.
   * $message = String, array ou instancia de Validator
   */
  public function alert($type, $message){
    if(is_object($message) and get_class($message) == 'Illuminate\Validation\Validator') $message = $message->errors()->all();
    if(is_string($message)) $message = [$message];
    $this->alert = compact('type', 'message');
  }

  /** $url - String */
  public function url($url){
    $this->url = $url;
  }

  /**
   * $fields - Array ou instancia de Validator.
   * Se array, informe a index com o nome do campo (como escrito no formulário) e o valor com a mensagem a ser exibida
   */
  public function required($fields){
    if(is_object($fields) and get_class($fields) == 'Illuminate\Validation\Validator')
      $fields = $fields->errors();

    $this->required = $fields;
  }

  /** $data = Array */
  public function data($data=array()){
    $this->data = $data;
  }

  /** $error = Boolean */
  public function error($error=false){
    $this->error = $error;
  }

  /** Insere novos campos na resposta */
  public function append(...$vars){
    foreach($vars as $val){
      $this->append[] = $val;
    }
  }

  public function response($error=null, $status=200){
    $this->error = ($error) ? true : $this->error;
    $fields = [
      'url' => $this->url,
      'data' => $this->data,
      'alert' => $this->alert,
      'toast' => $this->toast,
      'required' => $this->required,
      'error' => ($this->error) ? 1 : 0,
      'success' => (!$this->error) ? 1 : 0
    ];

    if($this->append) {
      foreach($this->append as $var){
        $fields = array_merge($fields, $var);
      }
    }

    return response()->json($fields, $status);
  }
}
