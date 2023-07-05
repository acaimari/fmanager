<?php

use Illuminate\Support\Facades\Route;
use Caimari\FManager\Controllers\FManagerController;
use Illuminate\Support\Facades\Auth;

Route::group(['middleware' => ['web', 'auth']], function () {
    
//Route::post('/fmanager/navigate', [FManagerController::class, 'navigate']);
Route::get('/fmanager', [FManagerController::class, 'index'])->name('fmanager.index');
Route::get('/fmanager-modal', [FManagerController::class, 'indexModal'])->name('fmanager.modal');
Route::get('/fmanager-modal-adv', [FManagerController::class, 'indexModalAdv'])->name('fmanager.modal.adv');

Route::post('/fmanager/navigate', [FManagerController::class, 'navigate'])->name('fmanager.navigate');
Route::post('/fmanager/delete-file', [FManagerController::class, 'destroyFileStore'])->name('fmanager.delete');
Route::post('/fmanager/upload-files', [FManagerController::class, 'uploadFileStore'])->name('upload.files.store');
Route::post('/fmanager/createdir', [FManagerController::class, 'createDirectory'])->name('fmanager.createdir');




});




