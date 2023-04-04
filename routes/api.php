<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\ChefController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Student Functions

Route::post('/createAccount', [EtudiantController::class, 'createAccount']);
//createAccount(firstName,lastName,pswd,email,birthDate,birthPlace,tel,cardNumber,diplome,specialite,depName)
Route::post('/consultStudentAccount', [EtudiantController::class, 'consultStudentAccount']);
//consultStudentAccount(id)
Route::post('/modifyStudentAccount', [EtudiantController::class, 'modifyStudentAccount']);


Route::post('/applyForInternship', [EtudiantController::class, 'applyForInternship']);
//applyForInternship(dateD,dateF,idOffre,idStudent) existing internship 
Route::post('/createApplication', [EtudiantController::class, 'createApplication']);
Route::post('/modifyApplication', [EtudiantController::class, 'modifyApplication']);

Route::post('/consultAttendance', [EtudiantController::class, 'consultAttendance']);
Route::post('/checkMarks', [EtudiantController::class, 'checkMarks']);
Route::get('/consultOffersList', [EtudiantController::class, 'consultOffersList']);

Route::post('/getStudentNotif', [EtudiantController::class, 'getStudentNotif']);
Route::post('/unseenStudentNotifNbr', [EtudiantController::class, 'unseenStudentNotifNbr']);
Route::post('/seeStudentNotif', [EtudiantController::class, 'seeStudentNotif']);

Route::post('/applicationInfo', [EtudiantController::class, 'applicationInfo']);
Route::post('/EtatDemande', [EtudiantController::class, 'EtatDemande']);
Route::post('/consulterDemande', [EtudiantController::class, 'consulterDemande']);
Route::delete('/deleteDemande', [EtudiantController::class, 'deleteDemande']);

//chef functions

Route::get('/studentsList', [ChefController::class, 'studentsList']);
Route::get('/offersList', [ChefController::class, 'offersList']);
Route::get('/acceptedRequestList', [ChefController::class, 'acceptedRequestList']);
Route::get('/refusedRequestList', [ChefController::class, 'refusedRequestList']);
Route::get('/pendingRequestList', [ChefController::class, 'pendingRequestList']);

Route::post('/createResAccount', [ChefController::class, 'createResAccount']);
Route::get('/ResList', [ChefController::class, 'ResList']);
Route::post('/resInfo', [ChefController::class, 'resInfo']);
Route::post('/changeResInfo', [ChefController::class, 'changeResInfo']);
Route::delete('/deleteRes', [ChefController::class, 'deleteRes']);

Route::get('/studentList', [ChefController::class, 'studentList']);
Route::post('/getStudentInfo', [ChefController::class, 'getStudentInfo']);
Route::post('/changeStudentInfo', [ChefController::class, 'changeStudentInfo']);
Route::delete('/deleteStudent', [ChefController::class, 'deleteStudent']);

Route::post('/getChefInfo', [ChefController::class, 'getChefInfo']);
Route::post('/changeChefInfo', [ChefController::class, 'changeChefInfo']);

Route::post('/acceptRequest', [ChefController::class, 'acceptRequest']);
Route::post('/refuseRequest', [ChefController::class, 'refuseRequest']);

Route::post('/confirmCreation', [ChefController::class, 'confirmCreation']);
Route::post('/sendMotif', [ChefController::class, 'sendMotif']);

Route::post('/getChefNotif', [ChefController::class, 'getChefNotif']);
Route::post('/unseenChefNotifNbr', [ChefController::class, 'unseenChefNotifNbr']);
Route::post('/seeChefNotif', [ChefController::class, 'seeChefNotif']);

//responsable de stage 

Route::post('/pendingRequests', [ResponsableController::class,'pendingRequests']);
Route::post('/acceptedRequests', [ResponsableController::class,'acceptedRequests']);
Route::post('/refusedRequests', [ResponsableController::class,'refusedRequests']);
Route::post('/InfoResp', [ResponsableController::class,'InfoResp']);
Route::post('/changeInfoResp', [ResponsableController::class,'changeInfoResp']);

Route::post('/acceptRequestRes', [ResponsableController::class,'acceptRequestRes']);
Route::post('/refuseRequestRes', [ResponsableController::class,'refuseRequestRes']);
Route::post('/sendMotifRes', [ResponsableController::class,'sendMotifRes']);

Route::post('/marquerNotes', [ResponsableController::class,'marquerNotes']);
Route::post('/marquerPresence', [ResponsableController::class,'marquerPresence']);

Route::post('/creerOffreRes', [ResponsableController::class,'creerOffreRes']);

Route::post('/getResNotif', [ChefController::class, 'getResfNotif']);
Route::post('/unseenResNotifNbr', [ChefController::class, 'unseenResNotifNbr']);
Route::post('/seeResNotif', [ChefController::class, 'seeResNotif']);
