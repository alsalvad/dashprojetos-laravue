<?php

namespace App\Models;

use App\Models\Grupo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Projeto extends Model
{
  use HasFactory;

  protected $appends = ['url', 'url_alternative'];

  public function getUrlAttribute(){
    $url = $this->attributes['url'];
    $url = str_replace('http://', '', $url);
    $url = str_contains($url, 'https') ? $url : 'http://'.$url;
    return $url;
  }

  public function getUrlAlternativeAttribute(){
    $url = $this->url;
    $url = str_replace(['http://', 'https://'], '', $url);
    $urlArr = explode('/', $url);
    $host = $urlArr[0];
    $url = str_replace($host, 'http://{host}', $url);
    return $url;
  }
}
