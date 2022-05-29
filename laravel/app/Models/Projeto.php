<?php

namespace App\Models;

use App\Models\Grupo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Projeto extends Model
{
  use HasFactory;

  protected $appends = ['url_alternative'];

  public function getUrlAlternativeAttribute(){
    $url = $this->url;
    $url = str_replace(['http://', 'https://'], '', $url);
    $urlArr = explode('/', $url);
    $host = $urlArr[0];
    $url = str_replace($host, 'http://{host}', $url);
    return $url;
  }

  public static function padroes($user_id){
    $grupos = [
      [
        'titulo' => 'MarketWeb (253)',
        'projetos' => [
          [
            'titulo' => 'Master',
            'url' => 'http://192.168.254.253/fdcmarketweb_master'
          ],
          [
            'titulo' => 'Sprint',
            'url' => 'http://192.168.254.253/fdcmarketweb_sprint'
          ],
        ]
      ],
      [
        'titulo' => 'Giro (253)',
        'projetos' => [
          [
            'titulo' => 'Master',
            'url' => 'http://192.168.254.253/fdcmarketweb_giro_master'
          ],
          [
            'titulo' => 'Sprint',
            'url' => 'http://192.168.254.253/fdcmarketweb_giro_sprint'
          ],
        ]
      ],
      [
        'titulo' => 'Competição (253)',
        'projetos' => [
          [
            'titulo' => 'Master',
            'url' => 'http://192.168.254.253/fdcmarketweb_competicao_master'
          ],
          [
            'titulo' => 'Sprint',
            'url' => 'http://192.168.254.253/fdcmarketweb_competicao_sprint'
          ],
        ]
      ],
      [
        'titulo' => 'Suporte (253)',
        'projetos' => [
          [
            'titulo' => 'Master',
            'url' => 'http://192.168.254.253/fdcSuporte_master'
          ],
          [
            'titulo' => 'Sprint',
            'url' => 'http://192.168.254.253/fdcSuporte_sprint'
          ],
        ]
      ],
    ];

    try{
      foreach($grupos as $item){
        $grupo = new Grupo();
        $grupo->titulo = $item['titulo'];
        $grupo->user_id = $user_id;
        $grupo->tipo = 'projetos';
        $grupo->save();

        foreach($item['projetos'] as $proj){
          $newProj = new self;
          $newProj->titulo = $proj['titulo'];
          $newProj->url = $proj['url'];
          $newProj->user_id = $user_id;
          $newProj->grupo_id = $grupo->id;
          $newProj->save();
        }
      }
      DB::commit();
    }catch(\Exception $e){
      DB::rollBack();
    }
  }

}



// $grupos = [
//   [
//     'titulo' => 'MarketWeb (253)',
//     'projetos' => [
//       [
//         'titulo' => 'Master',
//         'url' => 'http://192.168.254.253/fdcmarketweb_master'
//       ],
//       [
//         'titulo' => 'Sprint',
//         'url' => 'http://192.168.254.253/fdcmarketweb_sprint'
//       ],
//     ]
//   ],
//   [
//     'titulo' => 'Giro (253)',
//     'projetos' => [
//       [
//         'titulo' => 'Master',
//         'url' => 'http://192.168.254.253/fdcmarketweb_giro_master'
//       ],
//       [
//         'titulo' => 'Sprint',
//         'url' => 'http://192.168.254.253/fdcmarketweb_giro_sprint'
//       ],
//     ]
//   ],
//   [
//     'titulo' => 'Competição (253)',
//     'projetos' => [
//       [
//         'titulo' => 'Master',
//         'url' => 'http://192.168.254.253/fdcmarketweb_competicao_master'
//       ],
//       [
//         'titulo' => 'Sprint',
//         'url' => 'http://192.168.254.253/fdcmarketweb_competicao_sprint'
//       ],
//     ]
//   ],
//   [
//     'titulo' => 'Suporte (253)',
//     'projetos' => [
//       [
//         'titulo' => 'Master',
//         'url' => 'http://192.168.254.253/fdcSuporte_master'
//       ],
//       [
//         'titulo' => 'Sprint',
//         'url' => 'http://192.168.254.253/fdcSuporte_sprint'
//       ],
//     ]
//   ],
// ];
