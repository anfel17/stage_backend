<?php

namespace App\Http\Controllers;
use App\Mail\Email;
use Illuminate\Support\Facades\Mail;

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
				->select('nom_etudiant','prenom_etudiant','note_totale')
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


    public function acceptedRequestList(Request $request) {
			return DB::table('STAGE')
			->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
            ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
			->select('theme','photo_offre','nom_etudiant','prenom_etudiant')
            ->where('etat_chef','=','accepte')
			->get();
	}

    public function pendingRequestList(Request $request) {
        return DB::table('STAGE')
        ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
        ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
        ->select('theme','photo_offre','nom_etudiant','prenom_etudiant')
        ->where('etat_chef','=','enAttente')
        ->get();
    }

     public function refusedRequestList(Request $request) {
    return DB::table('STAGE')
    ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
    ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
    ->select('theme','photo_offre','nom_etudiant','prenom_etudiant')
    ->where('etat_chef','=','refuse')
    ->get();
    }
  

    //(send email to res after account creation)
    public function createResAccount(request $request){
		
	
            $responsableExist = RESPONSABLE::where('email_responsable', $request->email)->exists();
            if ($responsableExist) {
            return response()->json(['error' => 'The provided email address is already associated with an existing account'], 400);
            }

           $entrepriseExist = ENTREPRISE::where('nom_entreprise', $request->nom)->exists();
           if ($entrepriseExist) {
            $entreprise = ENTREPRISE::where('nom_entreprise', $request->nom)->first();
            $entreprise_id = $entreprise->id_entreprise;

            RESPONSABLE::insert(['nom_responsable' => $request->firstName ,
            'prenom_responsable' => $request->lastName,
            'email_responsable' => $request->email,
            'mdps_responsable'=>$request->password,
            'photo_responsable' => $request->img,
            'id_entreprise' => $entreprise_id]);
            return response()->json([
            'msg' => 'account created succesfuly',
            ]);
        }else{
            $EntrepriseId = DB::table('ENTREPRISE') 
            ->insertGetId(['nom_entreprise'=>$request->nom,
            'addresse_entreprise'=>$request->adrs,
            'tel_entreprise'=>$request->tel,
            ]);

            RESPONSABLE::insert(['nom_responsable' => $request->firstName ,'prenom_responsable' => $request->lastName,
            'email_responsable' => $request->email,'mdps_responsable'=>$request->password,
            'photo_responsable' => $request->img,'id_entreprise' => $EntrepriseId]);
            return response()->json([
            'msg' => 'account created succesfuly',
            ]);
        }
	}
   
     public function resList(request $request) {
		return DB::table('RESPONSABLE')
				 ->join('ENTREPRISE', 'ENTREPRISE.id_entreprise', '=', 'RESPONSABLE.id_entreprise')
				 ->select(['nom_responsable','prenom_responsable'])
				 ->get();
	}

    public function GetResInfo(request $request) {
		return DB::table('RESPONSABLE')
                ->where('id_responsable','=',$request->id)
				 ->join('ENTREPRISE', 'ENTREPRISE.id_entreprise', '=', 'RESPONSABLE.id_entreprise')
				 ->select(['nom_responsable','prenom_responsable','nom_entreprise','tel_entreprise','addresse_entreprise'])
				 ->get();
	}

    public function getStudentInfo(request $request) {
		return DB::table('ETUDIANT')
				 ->join('DEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'ETUDIANT.id_departement')
				 ->join('FACULTE', 'FACULTE.id_faculte', '=', 'DEPARTEMENT.id_faculte')
				 ->join('UNIVERSITE', 'FACULTE.id_universite', '=', 'UNIVERSITE.id_universite')
				 ->where('id_etudiant', '=',$request->id )
				 ->select(['nom_etudiant','prenom_etudiant','email_etudiant','mdps_etudiant','diplome','specialite','photo_etudiant',
				 'date_naissance','lieu_naissance','tel_etudiant','num_carte','nom_faculte','nom_universite','nom_departement'])
				 ->get();
	}

    public function studentList(request $request) {
		return DB::table('ETUDIANT')
				 ->select(['nom_etudiant','prenom_etudiant'])
				 ->get();
	}


  
    public function changeStudentInfo(request $request) {

		if($request->password != "" ){
            Etudiant::where('id_etudiant',$request->id)
							 ->update(['mdps_etudiant' => $request->password]);
        }
			  DB::table('ETUDIANT')
			  ->where('id_etudiant', '=',$request->id )
			  ->update(['nom_etudiant'=>$request->newName,
			  'prenom_etudiant'=>$request->newLastName,
			  'email_etudiant'=>$request->newEmail,
              'specialite'=>$request->newSpecialite,
			  'tel_etudiant'=>$request->newTel,
              'date_naissance'=>$request->newDate,
              'lieu_naissance'=>$request->newLieu]);

			 return response()->json([
				 'msg' => 'informations updated successfully',
			 ]);
			 
			}
    public function changeResInfo(request $request) {

                if($request->password != "" ){
                    Responsable::where('id_responsable',$request->id)
                                     ->update(['mdps_responsable' => $request->password]);
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
                 'email_responsable' => $request->email,
                 'mdps_responsable'=>$request->pswd,
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
                                    
                 ->select('nom_chef','prenom_chef','email_chef','photo_chef','nom_departement')
                ->where('id_chef', '=',$request->id )
                ->get(); 
     }
                        
                      
    public function changeChefInfo(request $request) {
           DB::table('CHEFDEPARTEMENT')
                ->where('id_chef', '=',$request->id )
                ->join('DEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'CHEFDEPARTEMENT.id_departement')
                ->update(['nom_chef'=> $request->nom,'prenom_chef'=> $request->prenom,
                         'email_chef'=> $request->email,'photo_chef'=> $request->img]);
             return response()->json(['msg' => 'informations updated successfully',]);
                         }
                     
    public function acceptRequest(request $request){
            STAGE::where('id_stage', '=',$request->id)
                 ->update(['etat_chef' =>'accepte']);
                        
             $fullName= STAGE::where('id_stage', '=',$request->id)
                        ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
                        ->select('nom_etudiant','prenom_etudiant')
                        ->get();
            $fullName = json_decode($fullName, true);
                        
            $idOffre=DB::table('STAGE')
                      ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
                      ->where('id_stage', '=',$request->id)
                      ->value('OFFRE.id_offre');
                       
            $email=DB::table('OFFRE')
                        ->where('id_offre', '=',$idOffre)
                        ->value('email_res');
                       
           $ResExist = RESPONSABLE::where('email_responsable', $email)
                       ->first();
                       
         if($ResExist){
                    $idResp=$ResExist-> id_responsable;
                    $idEntr=$ResExist-> id_entreprise;
                           
                    OFFRE::where('OFFRE.id_offre', '=',$idOffre)
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

            $idOffre=DB::table('STAGE')
                    ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
                    ->where('id_stage', '=',$request->id)
                    ->value('OFFRE.id_offre');

            $infoRes = DB::table('OFFRE') 
                    ->select(['nom_res', 'prenom_res','email_res','id_entreprise'])
                    ->where('id_offre','=',$idOffre)
                    ->get();
                         $info = json_decode($infoRes, true);
            
                        $idRes = RESPONSABLE::insertGetId(['nom_responsable' =>  $info[0]['nom_res'] ,
                        'prenom_responsable' =>  $info[0]['prenom_res'],
                        'email_responsable' =>  $info[0]['email_res'],
                        'mdps_responsable'=>$password,
                        'id_entreprise' =>  $info[0]['id_entreprise']]);

                        OFFRE::where('id_offre','=',$idOffre)
                           ->update(['id_responsable' => $idRes]);

                        Mail::to($info[0]['email_res'])->send(new Email($password));

                        $fullName= STAGE::where('id_stage', '=',$request->id)
                        ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
                        ->select('nom_etudiant','prenom_etudiant')
                        ->get();

                        $fullName = json_decode($fullName, true);
                        Notification::insert(['destinataire' => 'responsable','id_destinataire' => $idRes,'message' => 'You have a new request from '.$fullName[0]['nom_etudiant'].' '.$fullName[0]['prenom_etudiant'].'.']);


                        return response()->json([
                        'msg' => 'account created succesfuly',
                        ]);
                       }


    public function refuseRequest(request $request) {
            STAGE::where('id_stage', '=',$request->id)
                 ->update(['etat_chef' =>'refuse']);

            $studentId = STAGE::where('id_stage', '=',$request->id)
                ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
                ->value('ETUDIANT.id_etudiant');
    
                Notification::insert(['destinataire' => 'etudiant','id_destinataire' => $studentId,'message' => 'One request has been rejected']);
    }

    public function sendMotif(request $request) {

            STAGE::where('id_stage', '=',$request->id)
                ->update(['motif' =>$request->motif]);

                        
             return response()->json([
                                'msg' => 'motif sent succesfuly',
                                ]);
                       }
                    
}
