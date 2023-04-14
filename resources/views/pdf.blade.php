<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Attestation de Stage</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
    }
    .cont{
        text-align: center;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      border: 1px solid #ccc;
      padding: 20px;
      
    }
 
  </style>
</head>
<body>
  <div class="container">
    <div class="cont">
    <p>République Algérienne Démocratique et Populaire</p>
    <h2>ATTESTATION DE STAGE</h2>
    </div>
    <p>
       Je, soussigné(e) <b>{{$nomRes}} {{$prenomRes}}</b> responsable de stage  de <b>{{$theme}}</b><br>
      atteste que l'étudiant(e)  <b>  {{$firstName}}  {{$lastName}}  </b>  né(e) le <b> {{$dateNaissance}} </b>  à <b> {{$lieuNaissance}}  </b><br>
      inscrit(e) à <b> l’universite constantine 2</b>,
    </p>
    <p>
      a effectué un stage de formation dans la filière / spécialité <b>{{$diplome}} / {{$specialite}} </b> <br>
      à <b>{{$nomEntr}}</b> <br>
      Durant la période du <b>{{$dateDeb}} </b> au <b>  {{$dateFin}} </b> 
    </p>
    <p style="text-align: right;">
      Fait à <b>{{$addresseEntr}} </b>le <b>{{$currentDate}} </b><br> 
      </p>
      <p>
      Le Représentant de l'Ecole Nationale Polytechnique de Constantine<br>
      Le Responsable de l'établissement <br>
      ou de l'administration d'accueil
      </p>
   
    
  </div>
</body>
</html>