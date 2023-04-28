<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

class EtudiantController extends Controller
{

	public function createAccount(Request $request){

		$etudiantExist = ETUDIANT::where('email', $request->email)->exists();
    if ($etudiantExist) {
        return response()->json(['error' => 'The provided email address is already associated with an existing account'], 400);
    }
		$Dep = DB::table('DEPARTEMENT')
				->where('DEPARTEMENT.nom_departement','=',$request->depName)
				->value('id_departement');
	
		$user = ETUDIANT::create([
			'nom_etudiant' => $request->firstName,
			'prenom_etudiant' => $request->lastName,
			'email' => $request->email,
			'password'=>Hash::make($request->pswd),
			'photo_etudiant' => $request->img,
			'date_naissance' => $request->birthDate,
			'lieu_naissance' => $request->birthPlace,
			'tel_etudiant' => $request->tel,
			'num_carte' => $request->cardNumber,
			'diplome' => $request->diplome,
			'specialite' => $request->specialite,
			'id_departement'=>$Dep
		]);
	
		$token = $user->createToken('Token Name')->accessToken;
	
		return response()->json([
			'user' => $user,
			'msg' => 'account created successfully',
			'token' => $token
		]);
	}
	



	public function consultStudentAccount(request $request) {

		return DB::table('ETUDIANT')
				 ->join('DEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'ETUDIANT.id_departement')
				 ->join('FACULTE', 'FACULTE.id_faculte', '=', 'DEPARTEMENT.id_faculte')
				 ->join('UNIVERSITE', 'FACULTE.id_universite', '=', 'UNIVERSITE.id_universite')
				 ->where('id_etudiant', '=',$request->id )
				 ->select(['id_etudiant','nom_etudiant','prenom_etudiant','email',
				 'password','diplome','specialite','photo_etudiant',
				 'date_naissance','lieu_naissance','tel_etudiant','num_carte',
				 'nom_faculte','nom_universite','nom_departement'])
				 ->get();
	}


	public function modifyStudentAccount(request $request) {

		$Etud = Etudiant::where('id_etudiant', $request->id)->get();

		if(Hash::check($request->currentPassword, $Etud[0]['password'])){
			  DB::table('ETUDIANT')
			  ->where('id_etudiant','=',$request->id )
			  ->update(['nom_etudiant'=>$request->firstName,
			  'prenom_etudiant'=>$request->lastName,
			  'email'=>$request->email,
			  'specialite'=>$request->specialite,
			  'tel_etudiant'=>$request->tel,
			  'date_naissance'=>$request->birthDate,
			  'lieu_naissance'=>$request->birthPlace]);

			  if($request->newPassword != "" ) {

				 Etudiant::where('id_etudiant',$request->id)
							 ->update(['password' => Hash::make($request->newPassword)]);
			 }
			 }
			 else  return response()->json([
				 'msg' => 'wrong password',
			 ]);

			 return response()->json([
				 'msg' => 'information updated successfully',
			 ]);

			}

		public function applyForInternship(request $request) {

				DB::table('STAGE')
				->insert(['date_debut'=>$request->dateD,
						  'date_fin'=>$request->dateF,
						  'etat_chef'=>"enAttente",
						  'id_etudiant'=>$request->idStudent,
						  'id_offre'=>$request->idOffre]);

				//Retrieving the chef's ID and full name to insert into the notification.

				 $idChef= DB::table('DEPARTEMENT')
				 ->join('ETUDIANT', 'DEPARTEMENT.id_departement', '=', 'ETUDIANT.id_departement')
				 ->join('CHEFDEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'CHEFDEPARTEMENT.id_departement')
				 ->value('id_chef');

				$fullName= Etudiant::where('id_etudiant', '=',$request->idStudent)
                        ->select('nom_etudiant','prenom_etudiant')
                        ->get();

                $fullName = json_decode($fullName, true);

			  Notification::insert(['destinataire' => 'chef','id_destinataire' => $idChef,
			  'message' => 'you have a new request from '.
			  $fullName[0]['nom_etudiant'].' '.$fullName[0]['prenom_etudiant'].'.']);

				return response()->json([
					'msg' => 'information inserted successfuly',
				]);

		}


		public function createApplication(request $request) {
			$entrepriseExist = ENTREPRISE::where('nom_entreprise', $request->entrName)->exists();
			if ($entrepriseExist) {
			 $entreprise = ENTREPRISE::where('nom_entreprise', $request->entrName)->first();
			 $entrepriseId = $entreprise->id_entreprise;
			}else{
			$entrepriseId = DB::table('ENTREPRISE')
						 ->insertGetId(['nom_entreprise'=>$request->entrName,
						 'addresse_entreprise'=>$request->adrs,
						 'tel_entreprise'=>$request->tel]);
			}
			$responsableId = DB::table('RESPONSABLE')
			->insertGetId(['nom_responsable'=>$request->resLastName,
			'prenom_responsable'=>$request->resFirstName,
			'email'=>$request->resEmail,
			'id_entreprise'=>$entrepriseId]);

			 $offreId = DB::table('OFFRE')
						 ->insertGetId(['theme'=>$request->theme,
						 'duree'=>$request->duree,
						 'id_entreprise'=>$entrepriseId,
						 'id_responsable'=>$responsableId,
						 'createur'=>'etudiant']);

				DB::table('STAGE')
				->insert(['date_debut'=>$request->dateD,
				'date_fin'=>$request->dateF,
				'etat_chef'=>"enAttente",
				'id_etudiant'=>$request->idStudent,
				'id_offre'=>$offreId]);

           //Retrieving the chef's ID and full name to insert into the notification.
			$info= DB::table('DEPARTEMENT')
			     ->where('id_etudiant', '=',$request->idStudent)
				 ->join('ETUDIANT', 'DEPARTEMENT.id_departement', '=', 'ETUDIANT.id_departement')
				 ->join('CHEFDEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'CHEFDEPARTEMENT.id_departement')
				 ->select('nom_etudiant','prenom_etudiant','id_chef')
				 ->get();

                $info = json_decode($info, true);

			  Notification::insert(['destinataire' => 'chef','id_destinataire' => $info[0]['id_chef'],
			  'message' => 'you have a new request from '.
			  $info[0]['nom_etudiant'].' '.$info[0]['prenom_etudiant'].'.']);

			return response()->json([
				'msg' => 'information inserted successfuly',
			]);
	}

		public function modifyApplication(request $request) {
			$etatChef = STAGE::where('id_stage', $request->id)
			->value('etat_chef');

			$etatRes = STAGE::where('id_stage', $request->id)
			->value('etat_responsable');

			if($etatChef != "accepte" || $etatRes==="refuse"){
			$idOffre=DB::table('STAGE')
			->where('id_stage', '=',$request->id )
			->value("id_offre");
			$idResp =DB::table('OFFRE')
			->where('id_offre', '=',$idOffre )
			->value("id_responsable");
			$idEntreprise =DB::table('OFFRE')
			->where('id_offre', '=',$idOffre )
			->value("id_entreprise");


			$ajouterPar = OFFRE::where('id_offre', $idOffre)
			->value('createur');

			DB::table('STAGE')
			->where('id_stage', '=',$request->id )
			->update(['date_debut'=>$request->dateD,'date_fin'=>$request->dateF]);

			if($ajouterPar==="etudiant"){
				DB::table('RESPONSABLE')
			->where('id_responsable', '=',$idResp )
			->update(['nom_responsable'=>$request->resLastName,
				'prenom_responsable'=>$request->resFirstName,
				'email'=>$request->resEmail,
			]);
				DB::table('ENTREPRISE')
			->where('id_entreprise', '=',$idEntreprise )
			->update(['nom_entreprise'=>$request->entrName,
				'addresse_entreprise'=>$request->adrs,
				'tel_entreprise'=>$request->tel,
			]);

			DB::table('OFFRE')
			->where('id_offre', '=',$idOffre )
			->update(['theme'=>$request->theme,
				'duree'=>$request->duree,
			]);
			}
			 //Retrieving the chef's ID and full name to insert into the notification.
			 $idEtud=DB::table('STAGE')
			->where('id_stage', '=',$request->id )
			->value("id_etudiant");

			   $info= DB::table('DEPARTEMENT')
			   ->where('id_etudiant', '=',$idEtud)
			   ->join('ETUDIANT', 'DEPARTEMENT.id_departement', '=', 'ETUDIANT.id_departement')
			   ->join('CHEFDEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'CHEFDEPARTEMENT.id_departement')
			   ->select('nom_etudiant','prenom_etudiant','id_chef')
			   ->get();

			  $info = json_decode($info, true);

			Notification::insert(['destinataire' => 'chef','id_destinataire' => $info[0]['id_chef'],
			'message' =>
			$info[0]['nom_etudiant'].' '.$info[0]['prenom_etudiant'].' has modified his/her application information']);
			return response()->json([
				'msg' => 'information updated successfuly',
			]);
		}

	}

	public function consultAttendance(request $request) {
    	return DB::table('PRESENCE')
    		    ->where('id_etudiant', '=',$request->id )
				->select('id_presence','date','heure_entree','heure_sortie','remarque')
    			->get();
    }

    public function checkMarks(request $request) {
    	return DB::table('NOTATION')
    		    ->where('id_etudiant','=',$request->id)
				->select('discipline','attitude','initiative','capacite','connaissance','note_totale')
    			->get();
    }



	public function consultOffersList(request $request) {
    	return DB::table('OFFRE')
		        ->where('createur','=','responsable')
		        ->select('id_offre','theme','duree','description','deadline','photo_offre')
    			->get();

        }


	public function getStudentNotif(request $request){
	  return Notification::where('id_destinataire', '=',$request->id)
	   ->where('destinataire', '=','etudiant')
	   ->select(['message','timeStamp'])
	   ->orderby('timeStamp' , 'DESC')
	   ->get();
    }

    public function unseenStudentNotifNbr(request $request){
            return Notification::where('id_destinataire', '=',$request->id)
			 ->where('destinataire', '=','etudiant')
             ->where('is_seen', '=',0)
             ->count();
    }

    public function seeStudentNotif(request $request){
            return Notification::where('id_destinataire', '=',$request->id)
			->where('destinataire', '=','etudiant')
             ->update(['is_seen' => 1]);
    }


public function applicationInfo(Request $request) {
    $stage = DB::table('STAGE')
        ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
        ->join('ETUDIANT','STAGE.id_etudiant','=','ETUDIANT.id_etudiant')
        ->select('theme', 'duree', 'date_debut', 'date_fin',
            'nom_etudiant','prenom_etudiant','email','diplome','specialite',
            'date_naissance','lieu_naissance','tel_etudiant','num_carte')
        ->where('id_stage', '=', $request->id)
		->get();
    return response()->json([
        'application_info' => $stage,
    ]);
}


// public function applicationsList(Request $request)
// {
//     $applications = DB::table('STAGE')
//         ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
//         ->where('id_etudiant', '=', $request->id)
//         ->select('STAGE.id_stage', 'theme', 'photo_offre')
//         ->get();

//     foreach ($applications as $application) {
//         $etatChef = STAGE::where('id_stage', $application->id_stage)
//             ->select(['etat_chef'])
//             ->get()
//             ->value('etat_chef');
//         $etatRes = STAGE::where('id_stage', $application->id_stage)
//             ->select(['etat_responsable'])
//             ->get()
//             ->value('etat_responsable');

//         if ($etatChef === "refuse" || $etatRes === "refuse") {
//             $application->status = 'refused';
//         } elseif ($etatRes === "accepte") {
//             $application->status = 'accepted';
//         } else {
//             $application->status = 'pending';
//         }
//     }

//     return response()->json([
//         'applications' => $applications,
//     ]);
// }


public function applicationsList(Request $request)
{
    $applications = DB::table('STAGE')
        ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
        ->where('id_etudiant', '=', $request->id)
        ->select('STAGE.id_stage', 'theme', 'photo_offre')
        ->get();

    $modifiedApplications = [];

    foreach ($applications as $application) {
        $etatChef = STAGE::where('id_stage', $application->id_stage)
            ->select(['etat_chef'])
            ->get()
            ->value('etat_chef');
        $etatRes = STAGE::where('id_stage', $application->id_stage)
            ->select(['etat_responsable'])
            ->get()
            ->value('etat_responsable');

        $modifiedApplication = $application;

        if ($etatChef === "refuse" || $etatRes === "refuse") {
            $modifiedApplication->status = 'refused';
        } elseif ($etatRes === "accepte") {
            $modifiedApplication->status = 'accepted';
        } else {
            $modifiedApplication->status = 'pending';
        }

        $modifiedApplications[] = $modifiedApplication;
    }

    return response()->json($modifiedApplications);
}


		public function deleteDemande(request $request){
			$id =STAGE::where('id_stage',$request->id)
			->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
			->select('OFFRE.id_offre','id_responsable','createur')
			->get();

			//$id = json_decode($id,true);
			if($id[0]['createur']==='etudiant'){
			OFFRE::where('id_offre',$id[0]['id_offre'])
					  ->delete();

		    RESPONSABLE::where('id_responsable',$id[0]['id_responsable'])
					  ->delete();
			}

			STAGE::where('id_stage',$request->id)
					  ->delete();

			return response()->json([
				   'msg' => 'application deleted successfuly ',
			   ]);
		 }
		}


