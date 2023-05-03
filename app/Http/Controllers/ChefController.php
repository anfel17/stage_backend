<?php

namespace App\Http\Controllers;
use App\Mail\Email;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Etudiant;
use App\Models\Attestation;
use App\Models\Chef;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Faculte;
use App\Models\Notation;
use App\Models\Notification;
use App\Models\Offre;
use App\Models\Presence;
use App\Models\Responsable;
use App\Models\Stage;
use App\Models\Universite;
use Illuminate\Support\Str;

class ChefController extends Controller
{
    public function studentsList(request $request) {
    	return DB::table('NOTATION')
                ->join('ETUDIANT','ETUDIANT.id_etudiant','=','NOTATION.id_etudiant')
				->select('ETUDIANT.id_etudiant','nom_etudiant','prenom_etudiant','note_totale')
    			->get();
    }


    public function offersList(request $request) {
    	return DB::table('OFFRE')
				->join('ENTREPRISE', 'OFFRE.id_entreprise', '=', 'ENTREPRISE.id_entreprise')
				->join('RESPONSABLE', 'OFFRE.id_responsable', '=', 'RESPONSABLE.id_responsable')
		        ->where('createur','=','responsable')
		        ->select('theme','duree','description','deadline','photo_offre','nom_entreprise','nom_responsable','prenom_responsable')
    			->get();
        }

    public function offerInfo(request $request) {
        return DB::table('OFFRE')
                ->join('ENTREPRISE', 'OFFRE.id_entreprise', '=', 'ENTREPRISE.id_entreprise')
                ->join('RESPONSABLE', 'OFFRE.id_responsable', '=', 'RESPONSABLE.id_responsable')
                ->where('createur','=','responsable')
                ->where('id_offre',$request->id)
                ->select('theme','duree','description','deadline','photo_offre','nom_entreprise','addresse_entreprise','nom_responsable','prenom_responsable')
                ->get();
        }

    public function requestsList(request $request) {
        return DB::table('STAGE')
			->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
            ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
			->select('id_stage','theme','photo_offre','nom_etudiant','prenom_etudiant','email','etat_chef')
			->get();
    }

    public function acceptedRequestList(Request $request) {
			return DB::table('STAGE')
			->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
            ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
			->select('id_stage','theme','photo_offre','nom_etudiant','prenom_etudiant','email','etat_chef')
            ->where('etat_chef','=','accepte')
			->get();
	}

    public function pendingRequestList(Request $request) {
        return DB::table('STAGE')
        ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
        ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
        ->select('id_stage','theme','photo_offre','nom_etudiant','prenom_etudiant','email','etat_chef')
        ->where('etat_chef','=','enAttente')
        ->get();

    }

     public function refusedRequestList(Request $request) {
    return DB::table('STAGE')
    ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
    ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
    ->select('id_stage','theme','photo_offre','nom_etudiant','prenom_etudiant','email','etat_chef')
    ->where('etat_chef','=','refuse')
    ->get();
    }

    public function createResAccount(request $request){
		$password = Str::random(8);
        $hashedPassword = Hash::make($password);

            $responsableExist = RESPONSABLE::where('email', $request->email)->exists();
            if ($responsableExist) {
            return response()->json(['error' => 'The provided email address is already associated with an existing account'], 400);
            }

           $entrepriseExist = ENTREPRISE::where('nom_entreprise', $request->nom)->exists();
           if ($entrepriseExist) {
            $entreprise = ENTREPRISE::where('nom_entreprise', $request->nom)->first();
            $entrepriseId = $entreprise->id_entreprise;
        }else{
            $entrepriseId = DB::table('ENTREPRISE')
            ->insertGetId(['nom_entreprise'=>$request->nom,
            'addresse_entreprise'=>$request->adrs,
            'tel_entreprise'=>$request->tel,
            ]);
        }

            RESPONSABLE::insert(['nom_responsable' => $request->firstName ,
            'prenom_responsable' => $request->lastName,
            'email' => $request->email,
            'password'=>$hashedPassword,
            'photo_responsable' => $request->img,
            'id_entreprise' => $entrepriseId]);

            Mail::to($request->email)->send(new Email($password));

            return response()->json([
            'msg' => 'account created succesfuly',
            ]);

        }


     public function resList(request $request) {
		return DB::table('RESPONSABLE')
				 ->join('ENTREPRISE', 'ENTREPRISE.id_entreprise', '=', 'RESPONSABLE.id_entreprise')
				 ->select(['id_responsable','nom_responsable','prenom_responsable'])
                 ->where('is_active','=','1')
				 ->get();
	}

    public function resInfo(request $request) {
		return DB::table('RESPONSABLE')
                ->where('id_responsable','=',$request->id)
				 ->join('ENTREPRISE', 'ENTREPRISE.id_entreprise', '=', 'RESPONSABLE.id_entreprise')
				 ->select(['id_responsable','nom_responsable','prenom_responsable','nom_entreprise','tel_entreprise','addresse_entreprise'])
				 ->get();
	}

    public function getStudentInfo(request $request) {
		return DB::table('ETUDIANT')
				 ->join('DEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'ETUDIANT.id_departement')
				 ->join('FACULTE', 'FACULTE.id_faculte', '=', 'DEPARTEMENT.id_faculte')
				 ->join('UNIVERSITE', 'FACULTE.id_universite', '=', 'UNIVERSITE.id_universite')
				 ->where('id_etudiant', '=',$request->id )
				 ->select(['id_etudiant','nom_etudiant','prenom_etudiant','email','password','diplome','specialite','photo_etudiant',
				 'date_naissance','lieu_naissance','tel_etudiant','num_carte','nom_faculte','nom_universite','nom_departement'])
				 ->get();
	}

    public function studentList(request $request) {
		return DB::table('ETUDIANT')
				 ->select(['id_etudiant','nom_etudiant','prenom_etudiant'])
				 ->get();
	}
    public function listeStagiairs(request $request) {
        return DB::table('STAGE')
                ->where('etat_responsable','accepte')
                ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=','STAGE.id_etudiant')
                 ->select('nom_etudiant','prenom_etudiant')
                 ->orderby('nom_etudiant', 'asc')
                 ->get();
    }



    public function changeStudentInfo(request $request) {

		if($request->password != "" ){
            Etudiant::where('id_etudiant',$request->id)
							 ->update(['password' => $request->password]);
        }
			  DB::table('ETUDIANT')
			  ->where('id_etudiant', '=',$request->id )
			  ->update(['nom_etudiant'=>$request->newName,
			  'prenom_etudiant'=>$request->newLastName,
			  'email'=>$request->newEmail,
              'specialite'=>$request->newSpecialite,
			  'tel_etudiant'=>$request->newTel,
              'date_naissance'=>$request->newDate,
              'lieu_naissance'=>$request->newLieu,
              'num_carte'=>$request->newCartNum]);

			 return response()->json([
				 'msg' => 'informations updated successfully',
			 ]);

			}
    public function changeResInfo(request $request) {

                if($request->password != "" ){
                    Responsable::where('id_responsable',$request->id)
                                     ->update(['password' => $request->password]);
                }

                $EntrepriseId=DB::table('RESPONSABLE')
                ->where('id_responsable', '=',$request->id )
                ->value('id_entreprise');

                DB::table('ENTREPRISE')
                ->where('id_entreprise', '=',$EntrepriseId )
                ->update(['nom_entreprise' => $request->nom,
                'addresse_entreprise'=>$request->adrs,
                'tel_entreprise'=>$request->tel]);

                 DB::table('RESPONSABLE')
                 ->where('id_responsable', '=',$request->id )
                 ->update(['nom_responsable' => $request->firstName ,
                 'prenom_responsable' => $request->lastName,
                 'email' => $request->email,
                 'photo_responsable' => $request->img,
                 'id_entreprise' => $EntrepriseId]);


                     return response()->json([
                         'msg' => 'informations updated successfully',
                     ]);

    }

    public function deleteStudent(request $request){

             ETUDIANT::where('id_etudiant',$request->id)
                                  ->delete();
                        return response()->json([
                               'msg' => 'student deleted successfully',
                           ]);
     }
     public function deleteRes(request $request){

             $EntrepriseId=DB::table('RESPONSABLE')
                ->where('id_responsable', '=',$request->id )
                ->value('id_entreprise');

           ENTREPRISE::where('id_entreprise',$EntrepriseId)
                       ->delete();

           RESPONSABLE::where('id_responsable',$request->id)
                                  ->delete();

          return response()->json([
                               'msg' => 'responsable deleted successfully',
                           ]);
    }
    public function getChefNotif(request $request){
               return Notification::where('id_destinataire', '=',$request->id)
                 ->where('destinataire', '=','chef')
                 ->select(['message','timeStamp'])
                 ->orderby('timeStamp' , 'DESC')
                 ->get();
     }

    public function unseenChefNotifNbr(request $request){

            return Notification::where('id_destinataire', '=',$request->id)
                 ->where('destinataire', '=','chef')
                 ->where('is_seen', '=',0)
                 ->count();
     }

    public function seeChefNotif(request $request){
            return Notification::where('id_destinataire', '=',$request->id)
                ->where('destinataire', '=','chef')
                ->update(['is_seen' => 1]);
    }

    public function getChefInfo(request $request) {
        return DB::table('CHEFDEPARTEMENT')
             ->join('DEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'CHEFDEPARTEMENT.id_departement')

             ->select('id_chef','nom_chef','prenom_chef','email','photo_chef','nom_departement')
            ->where('id_chef', '=',$request->id )
            ->get();
 }


    public function changeChefInfo(request $request) {
       $ChefD = DB::table('CHEFDEPARTEMENT')->
                 where('id_chef', $request->id)
                ->get();
      $ChefD = json_decode($ChefD, true);
          if(Hash::check($request->currentPassword, $ChefD[0]['password'])){
           DB::table('CHEFDEPARTEMENT')
                ->where('id_chef', '=',$request->id )
                ->join('DEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'CHEFDEPARTEMENT.id_departement')
                ->update(['nom_chef'=> $request->lastName,
                         'prenom_chef'=> $request->firstName,
                         'email'=> $request->email,
                         'photo_chef'=> $request->img]);
                         if($request->newPassword != "" ) {
                            DB::table('CHEFDEPARTEMENT')->where('id_chef',$request->id)
                                        ->update(['password' => Hash::make($request->newPassword)]);
                        }

             return response()->json(['msg' => 'informations updated successfully',]);
                         }else  return response()->json([
                            'msg' => 'wrong password',
                      ]);
          }

    public function acceptRequest(request $request){
            STAGE::where('id_stage', '=',$request->id)
                 ->update(['etat_chef' =>'accepte']);

             $fullName= STAGE::where('id_stage', '=',$request->id)
                        ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
                        ->select('nom_etudiant','prenom_etudiant')
                        ->get();
            $fullName = json_decode($fullName, true);

            $id=DB::table('STAGE')
                      ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
                      ->where('id_stage', '=',$request->id)
                      ->select('OFFRE.id_offre','id_responsable')
                      ->get();

            $id = json_decode($id, true);

            $email=DB::table('RESPONSABLE')
                        ->where('id_responsable', '=',$id[0]['id_responsable'])
                        ->value('email');

           $ResExist = RESPONSABLE::where('email', $email)
                       ->where('is_active','=','1')
                       ->first();

         if($ResExist){
                    $idResp=$ResExist-> id_responsable;
                    $idEntr=$ResExist-> id_entreprise;

                    DB::table('RESPONSABLE')
                      ->where('id_responsable','=', $id[0]['id_responsable'])
                      ->delete();

                    OFFRE::where('OFFRE.id_offre', '=',$id[0]['id_offre'])
                    ->update(['id_responsable' =>$idResp,
                    'id_entreprise'=>$idEntr]);

        Notification::insert(['destinataire' => 'responsable','id_destinataire' => $idResp,'message' => 'You have a new request from '.$fullName[0]['nom_etudiant'].' '.$fullName[0]['prenom_etudiant'].'.']);

            return   response()->json(['msg' => 'information updated successfully',]);
                          }
        else{

            return   response()->json(['msg' => 'confirm creation',]);

                        }

     }

     public function confirmCreation(request $request) {

            $password = Str::random(8);

            $idOffre =DB::table('STAGE')
                    ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
                    ->where('id_stage', '=',$request->id)
                    ->value('OFFRE.id_offre');

            $idRes = DB::table('OFFRE')
                    ->where('id_offre','=',$idOffre)
                    ->value('id_responsable');

                    $email = RESPONSABLE::where('id_responsable','=', $idRes )
                    ->value('email');

                        RESPONSABLE::where('id_responsable','=', $idRes )
                        ->update(['password'=>Hash::make($password),
                        'is_active' =>  '1']);


                        Mail::to($email)->send(new Email($password));

                        $fullName= STAGE::where('id_stage', '=',$request->id)
                        ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
                        ->select('nom_etudiant','prenom_etudiant')
                        ->get();

                        $fullName = json_decode($fullName, true);
                        Notification::insert(['destinataire' => 'responsable','id_destinataire' => $idRes,
                        'message' => 'You have a new request from '.$fullName[0]['nom_etudiant'].' '.$fullName[0]['prenom_etudiant'].'.']);


                        return response()->json([
                        'msg' => 'account created succesfuly',
                        ]);
                       }


    public function refuseRequest(request $request) {
            STAGE::where('id_stage', '=',$request->id)
                 ->update(['etat_chef' =>'refuse']);

            $info = STAGE::where('id_stage', '=',$request->id)
                ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
                ->join('OFFRE','OFFRE.id_offre','=','STAGE.id_offre')
                ->select('ETUDIANT.id_etudiant','theme')
                ->get();

                $info = json_decode($info, true);
                Notification::insert(['destinataire' => 'etudiant',
                'id_destinataire' =>$info[0]['id_etudiant'],
                'message' => 'your request of '.$info[0]['theme'].' has been rejected by the department Manager']);
                // return response()->json([
                //     'msg' => 'request has been rejected ',
                //     ]);
    }

    public function sendMotif(request $request) {

            STAGE::where('id_stage', '=',$request->id)
                ->update(['motif' =>$request->motif]);


             return response()->json([
                                'msg' => 'motif sent succesfuly',
                                ]);
                       }

}
