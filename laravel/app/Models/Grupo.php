<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grupo extends Model {
  use HasFactory;

  public function sub(){
    return $this->hasMany(Projeto::class)->orderBy('posicao');
  }
}
