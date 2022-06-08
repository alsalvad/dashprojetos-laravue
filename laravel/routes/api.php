<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ProjetosController;

Route::prefix('/auth')->name('auth.')->group(function(){
  Route::controller(AuthController::class)->group(function(){
    Route::get('/check-token', 'checkToken')->name('checkToken');
    Route::post('/login', 'login')->name('login');
    Route::post('/alterar-senha', 'alterarSenha')->name('alterarSenha');
    Route::post('/criar-usuario', 'criarUsuario')->name('criarUsuario');
  });
});

Route::prefix('/notes')->name('notes.')->group(function(){
  Route::controller(NotesController::class)->group(function(){
    Route::get('/', 'getGrupos')->name('getGrupos');
    Route::get('/{grupo_id}', 'getNotes')->name('getNotes');
    Route::post('/', 'saveNote')->name('saveNote');
    Route::put('/', 'saveNote');
    Route::delete('/{id}', 'deleteNote')->name('deleteNote');

    Route::post('/grupo', 'saveGrupo')->name('novoGrupo');
    Route::put('/grupo', 'saveGrupo')->name('editarGrupo');
    Route::delete('/grupo/{id}', 'deleteGrupo')->name('deleteGrupo');
  });
});

Route::prefix('/projetos')->name('projetos.')->group(function(){
  Route::controller(ProjetosController::class)->group(function(){
    Route::get('/', 'getProjetos')->name('getProjetos');
    Route::get('/links/{grupo_id}', 'getLinks')->name('getLinks');
    Route::post('/link', 'saveLink')->name('novoLink');
    Route::put('/link', 'saveLink')->name('editarLink');
    Route::delete('/link/{id}', 'deleteLink')->name('deletarLink');

    Route::post('/atualizar-posicao', 'atualizarPosicao')->name('atualizarPosicao');

    Route::post('/grupo', 'saveGrupo')->name('novoGrupo');
    Route::put('/grupo', 'saveGrupo')->name('editarGrupo');
    Route::put('/grupo/publico', 'updatePublico')->name('updatePublico');
    Route::delete('/grupo/{id}', 'deleteGrupo')->name('deleteGrupo');
  });
});
