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

class EtudiantController extends Controller
{
	
	public function createAccount(request $request){
		
		$Dep = DB::table('DEPARTEMENT')
				->where('DEPARTEMENT.nom_departement','=',$request->depName)
				->value('id_departement');
		

		 ETUDIANT::insert(['nom_etudiant' => $request->firstName,
		 'prenom_etudiant' => $request->lastName,
		 'email_etudiant' => $request->email,
		 'mdps_etudiant'=>$request->pswd,
		 'photo_etudiant' => $request->img,
		 'date_naissance' => $request->birthDate,
		 'lieu_naissance' => $request->birthPlace,
		 'tel_etudiant' => $request->tel,
		 'num_carte' => $request->cardNumber,
		 'diplome' => $request->diplome,
		 'specialite' => $request->specialite ,
		 'id_departement'=>$Dep]);


		 return response()->json([
					'msg' => 'account created succesfuly',
			 ]);
	}


	public function consultStudentAccount(request $request) {
		
		return DB::table('ETUDIANT')
				 ->join('DEPARTEMENT', 'DEPARTEMENT.id_departement', '=', 'ETUDIANT.id_departement')
				 ->join('FACULTE', 'FACULTE.id_faculte', '=', 'DEPARTEMENT.id_faculte')
				 ->join('UNIVERSITE', 'FACULTE.id_universite', '=', 'UNIVERSITE.id_universite')
				 ->where('id_etudiant', '=',$request->id )
				 ->select(['nom_etudiant','prenom_etudiant','email_etudiant',
				 'mdps_etudiant','diplome','specialite','photo_etudiant',
				 'date_naissance','lieu_naissance','tel_etudiant','num_carte',
				 'nom_faculte','nom_universite','nom_departement'])
				 ->get();
	}

	
	public function modifyStudentAccount(request $request) {

		$Etud = Etudiant::where('id_etudiant', $request->id)->get();
         
		 if($request->currentPassword === $Etud[0]['mdps_etudiant']){
			  DB::table('ETUDIANT')
			  ->where('id_etudiant','=',$request->id )
			  ->update(['nom_etudiant'=>$request->firstName,
			  'prenom_etudiant'=>$request->lastName,
			  'email_etudiant'=>$request->email,
			  'specialite'=>$request->specialite,
			  'tel_etudiant'=>$request->tel,
			  'date_naissance'=>$request->birthDate,
			  'lieu_naissance'=>$request->birthPlace]);

			  if($request->newPassword != "" ) {

				 Etudiant::where('id_etudiant',$request->id)
							 ->update(['mdps_etudiant' => $request->newPassword]);
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

		//add verification in case entreprise already exist in DB
		public function createApplication(request $request) {

			$entrepriseId = DB::table('ENTREPRISE') 
						 ->insertGetId(['nom_entreprise'=>$request->entrName,
						 'addresse_entreprise'=>$request->adrs,
						 'tel_entreprise'=>$request->tel]);

			 $offreId = DB::table('OFFRE') 
						 ->insertGetId(['theme'=>$request->theme,
						 'duree'=>$request->duree,
						 'nom_res'=>$request->resLastName,
						 'prenom_res'=>$request->resFirstName,
						 'email_res'=>$request->resEmail,
						 'id_entreprise'=>$entrepriseId,
						 'createur'=>'etudiant']);
	
				DB::table('STAGE')
				->insert(['date_debut'=>$request->dateD,
				'date_fin'=>$request->dateF,
				'etat_chef'=>"enAttente",
				'id_etudiant'=>$request->idStudent,
				'id_offre'=>$offreId]);

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

		public function modifyApplication(request $request) {
			$etatChef = STAGE::where('id_stage', $request->id)
			->value('etat_chef');
		
			$etatRes = STAGE::where('id_stage', $request->id)
			->value('etat_responsable');

			if($etatChef != "accepte" || $etatRes==="refuse"){
			$idOffre=DB::table('STAGE')
			->where('id_stage', '=',$request->id )
			->value("id_offre");

			$ajouterPar = OFFRE::where('id_offre', $idOffre)
			->value('createur');

			DB::table('STAGE')
			->where('id_stage', '=',$request->id )
			->update(['date_debut'=>$request->dateD,'date_fin'=>$request->dateF]);
		     
			if($ajouterPar==="etudiant"){
			DB::table('OFFRE') 
			->where('id_offre', '=',$idOffre )
			->update(['theme'=>$request->theme,
				'duree'=>$request->duree,
				'nom_res'=>$request->resLastName,
				'prenom_res'=>$request->resFirstName,
			     'email_res'=>$request->resEmail]);
			}
			return response()->json([
				'msg' => 'information updated successfuly',
			]);
		}
		
	}

	public function consultAttendance(request $request) {
    	return DB::table('PRESENCE')
    		    ->where('id_etudiant', '=',$request->id )
				->select('date','heure_entree','heure_sortie','remarque')
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
				->join('ENTREPRISE', 'OFFRE.id_entreprise', '=', 'ENTREPRISE.id_entreprise')
				->join('RESPONSABLE', 'OFFRE.id_responsable', '=', 'RESPONSABLE.id_responsable')
		        ->where('createur','=','responsable')
		        ->select('theme','duree','description','deadline','photo_offre','nom_entreprise','nom_responsable','prenom_responsable')
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

			return DB::table('STAGE')
				->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
				->join('ETUDIANT','STAGE.id_etudiant','=','ETUDIANT.id_etudiant')
				->select('theme', 'duree', 'date_debut', 'date_fin',
				'nom_etudiant','prenom_etudiant','email_etudiant','diplome','specialite',
			    'date_naissance','lieu_naissance','tel_etudiant','num_carte') 
				->where('id_stage', '=', $request->id)
				->get();
	}
    
	public function applicationsList(Request $request) {
			return DB::table('STAGE')
			->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
			->where('id_etudiant', '=',$request->id)
			->select('theme','photo_offre')
			->get();
	}


	public function EtatDemande(Request $request) {

			$etatChef = STAGE::where('id_stage', $request->id)
			       ->select(['etat_chef'])
			       ->get()
				   ->value('etat_chef');
		    $etatRes = STAGE::where('id_stage', $request->id)
			       ->select(['etat_responsable'])
			       ->get()
				   ->value('etat_responsable');
				   
			if($etatChef==="refuse" || $etatRes==="refuse"){
				return response()->json([
					'msg' => 'refused',
				]);
			}
			elseif($etatRes==="accepte"){
				return response()->json([
					'msg' => 'accepted',
				]);
			}
			else{
				return response()->json([
					'msg' => 'pending',
				]);
			}
		}
	
		public function deleteDemande(request $request){
			STAGE::where('id_stage',$request->id)
					  ->delete();
			return response()->json([
				   'msg' => 'demande supprimé',
			   ]);
		 }
		}
	
	
