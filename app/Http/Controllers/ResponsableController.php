<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Hash;

use Barryvdh\DomPDF\Facade\Pdf;
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

class ResponsableController extends Controller
{
    public function pendingRequests(Request $request) {
        return DB::table('STAGE')
            ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
            ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
            ->select('theme','photo_offre','nom_etudiant','prenom_etudiant')
            ->where('etat_chef','=','accepte')
            ->where('etat_responsable','=','enAttente')
            ->where('id_responsable',$request->id)
            ->get();
    }

  //consulter list des demandes acceptees
  public function acceptedRequests(Request $request) {
    return DB::table('STAGE')
    ->join('OFFRE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
    ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
    ->select('theme','photo_offre','nom_etudiant','prenom_etudiant')
    ->where('etat_responsable','=','accepte')
    ->where('id_responsable',$request->id)
    ->get();
}
//consulter liste des demandes refuse
public function refusedRequests(Request $request) {
    return DB::table('OFFRE')
        ->join('STAGE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
        ->join('ETUDIANT','ETUDIANT.id_etudiant','=','STAGE.id_etudiant')
        ->select('theme','photo_offre','nom_etudiant','prenom_etudiant')
        ->where('etat_responsable','=','refuse')
        ->where('id_responsable',$request->id)
        ->get();
}

public function listeStagiairs(request $request) {
    $idOffre= DB::table('OFFRE')
    ->join('STAGE', 'OFFRE.id_offre', '=', 'STAGE.id_offre')
    ->join('RESPONSABLE', 'RESPONSABLE.id_responsable','=','OFFRE.id_responsable')
    ->where('RESPONSABLE.id_responsable',$request->id)
    ->value('STAGE.id_offre');

    return DB::table('STAGE')
            ->where('etat_responsable','accepte')
            ->where('id_offre', $idOffre)
            ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=','STAGE.id_etudiant')
             ->select('nom_etudiant','prenom_etudiant')
             ->orderby('nom_etudiant', 'asc')
             ->get();
}

public function InfoResp(request $request) {
    return DB::table('RESPONSABLE')
            ->join('ENTREPRISE', 'RESPONSABLE.id_entreprise', '=',
             'ENTREPRISE.id_entreprise')
		    ->select('nom_entreprise','tel_entreprise','addresse_entreprise',
            'nom_responsable','prenom_responsable','email','photo_responsable')
            ->where('RESPONSABLE.id_responsable','=',$request->id)
            ->get();
 }


public function changeInfoResp(request $request){

      $Resp = RESPONSABLE::where('id_responsable', $request->id)->get();

	    if(Hash::check($request->currentPassword, $Resp[0]['password'])){

           $ENTREPRISE= DB::table('RESPONSABLE')
          ->where('id_responsable', '=',$request->id )
          ->value("id_entreprise");

       DB::table('ENTREPRISE')
       ->where('id_entreprise','=',$ENTREPRISE)
       ->update(['nom_entreprise'=>$request->nameEntr,'tel_entreprise'=>$request->tel,
       'addresse_entreprise'=>$request->addresse]);

      RESPONSABLE::where('id_responsable','=', $request->id)
      ->update(['nom_responsable' => $request->lastName ,'prenom_responsable' => $request->firstName,
      'email' => $request->email,
      'photo_responsable' => $request->img, "id_entreprise"=>$ENTREPRISE]);


      if($request->newPassword != "" ) {
         // The new password is valid
         RESPONSABLE::where('id_responsable',$request->id)
                    ->update(['password' => Hash::make($request->newPassword)]);
     }
    }

    else  return response()->json(['msg' => 'wrong password']);
	return response()->json(['msg' => 'information updated successfully',]);

}

public function acceptRequestRes(request $request){
    STAGE::where('id_stage', '=',$request->id)
        ->update(['etat_responsable' =>'accepte']);

        $info = STAGE::where('id_stage', '=',$request->id)
        ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
        ->join('OFFRE','OFFRE.id_offre','=','STAGE.id_offre')
        ->select('ETUDIANT.id_etudiant','theme')
        ->get();

        $info = json_decode($info, true);

    Notification::insert(['destinataire' => 'etudiant',
    'id_destinataire' =>$info[0]['id_etudiant'],
    'message' => 'your request of '.$info[0]['theme'].' has been accepted']);
   }

public function refuseRequestRes(request $request) {
    STAGE::where('id_stage', '=',$request->id)
        ->update(['etat_responsable' =>'refuse']);

        $info = STAGE::where('id_stage', '=',$request->id)
        ->join('ETUDIANT', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
        ->join('OFFRE','OFFRE.id_offre','=','STAGE.id_offre')
        ->select('ETUDIANT.id_etudiant','theme')
        ->get();

        $info = json_decode($info, true);

        Notification::insert(['destinataire' => 'etudiant',
        'id_destinataire' => $info[0]['id_etudiant'],
        'message' => 'your request of '.$info[0]['theme'].' has been rejected by the internship Manager']);
   }

   public function sendMotifRes(request $request) {
    STAGE::where('id_stage', '=',$request->id)
        ->update(['motif' =>$request->motif]);

     return response()->json([
            'msg' => 'motif sent succesfuly',
            ]);
   }

   public function marquerNotes(request $request) {
    $idStage = STAGE::where('id_etudiant', $request->id)->value('id_stage');
    $data = [
        'id_etudiant' => $request->id,
        'id_stage' => $idStage,
        'discipline' => $request->discipline,
        'attitude' => $request->attitude,
        'initiative' => $request->initiative,
        'capacite' => $request->capacite,
        'connaissance' => $request->connaissance,
        'note_totale' => $request->discipline + $request->attitude + $request->initiative +
         $request->capacite + $request->connaissance,
    ];

    Notation::insert($data);

     return response()->json([
            'msg' => 'information inserted succesfuly',
            ]);
   }

   public function marquerPresence(request $request) {
    $idStage = STAGE::where('id_etudiant', $request->id)->value('id_stage');

    PRESENCE::insert(['id_etudiant' => $request->id,
    'id_stage' => $idStage,
    'date' => $request->date,
    'heure_entree' => $request->heureE,
    'heure_sortie' => $request->heureS,
    'remarque' => $request->remarque]);

     return response()->json([
            'msg' => 'information inserted succesfuly',
            ]);
   }

   public function creerOffreRes(request $request) {
    $entrepriseId = DB::table('RESPONSABLE')
                 ->where('id_responsable', $request->id)
                 ->value('id_entreprise');

    $offreId = DB::table('OFFRE')
                 ->insert(['theme'=>$request->theme,'duree'=>$request->duree,
                 'deadline'=>$request->deadline,
                 'description'=>$request->description,
                 'photo_offre'=>$request->photo,
                 'id_entreprise'=>$entrepriseId,
                 'createur'=>'responsable','id_responsable'=>$request->id]);

    return response()->json([
        'msg' => 'information inserted successfuly',
    ]);
}
public function deleteOffer(request $request){

        OFFRE::where('id_offre',$request->id)
                         ->delete();
               return response()->json([
                      'msg' => 'offer deleted successfully',
                  ]);
}
public function modifyOffer(request $request){
            DB::table('OFFRE')
			->where('id_offre', '=',$idOffre )
			->update(['theme'=>$request->theme,
				'duree'=>$request->duree,
				'deadline'=>$request->deadline]);

           return response()->json([
                    'msg' => 'offer deleted successfully',
                ]);
}
public function getResNotif(request $request){
    return Notification::where('id_destinataire', '=',$request->id)
     ->where('destinataire', '=','responsable')
     ->select(['message','timeStamp'])
     ->orderby('timeStamp' , 'DESC')
     ->get();
}

      public function unseenResNotifNbr(request $request){
          return Notification::where('id_destinataire', '=',$request->id)
          ->where('destinataire', '=','responsable')
           ->where('is_seen', '=',0)
           ->count();
      }

      public function seeResNotif(request $request){
          return Notification::where('id_destinataire', '=',$request->id)
          ->where('destinataire', '=','responsable')
           ->update(['is_seen' => 1]);
      }

      public function generatePDF(request $request) {

        $array = DB::table('ETUDIANT')
				 ->where('ETUDIANT.id_etudiant', '=',$request->id )
                 ->join('STAGE', 'ETUDIANT.id_etudiant', '=', 'STAGE.id_etudiant')
                 ->join('OFFRE','OFFRE.id_offre', '=','STAGE.id_offre')
                 ->join('RESPONSABLE','RESPONSABLE.id_responsable', '=','OFFRE.id_responsable')
                 ->join('ENTREPRISE','OFFRE.id_entreprise', '=','ENTREPRISE.id_entreprise')
				 ->select(['nom_etudiant','prenom_etudiant','date_debut','date_fin',
                 'date_naissance','lieu_naissance','specialite',
                 'nom_responsable','prenom_responsable','addresse_entreprise','theme','diplome','nom_entreprise'])
				 ->get();

        $array = json_decode($array, true);
        $currentDate= Carbon::now()->format('Y-m-d');

        $data_array = ['firstName' => $array[0]['nom_etudiant'],
        'theme' => $array[0]['theme'],
        'lastName' => $array[0]['prenom_etudiant'] ,
        'dateDeb' => $array[0]['date_debut'] ,
        'dateFin' => $array[0]['date_fin'] ,
        'dateNaissance' => $array[0]['date_naissance'],
        'lieuNaissance' => $array[0]['lieu_naissance'],
        'specialite' => $array[0]['specialite'],
        'diplome' => $array[0]['diplome'],
        'nomRes' => $array[0]['nom_responsable'],
        'prenomRes' => $array[0]['prenom_responsable'],
        'addresseEntr' => $array[0]['addresse_entreprise'],
        'nomEntr' => $array[0]['nom_entreprise'],
        'currentDate' => $currentDate

    ];


        $pdf = PDF::loadView('pdf',$data_array);


        // Output the generated PDF to Browser
        return $pdf->stream();
                            }

}
