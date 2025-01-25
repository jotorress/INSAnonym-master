<?php

function checkAttackJson($originalJSON, $userJSON) {
  // On met le contenu des fichiers json en variables
  $originalJSON = file_get_contents($originalJSON);
  $userJSON = file_get_contents($userJSON);

  // On teste que les donnees original sont bien au bon format json
  if(json_decode($originalJSON, true)==null){
    return -1; // Il y a une erreur dans le fichier envoyé
  }
  // On teste que les donnees user sont bien au bon format json
  if(json_decode($userJSON, true)==null){
    return -1; // Il y a une erreur dans le fichier envoyé
  }
  
  else{
  // On decode les donnees
    $original = json_decode($originalJSON, true);
    $user = json_decode($userJSON, true); 
  }

  $score_max = 0;
  $score = 0;

  // Pour chaque identifiant:
  foreach($original as $id => $months){
    // Vérification qu'il existe dans le json user
    if(!isset($user[$id])){
      // ... Il y a une erreur dans le fichier envoyé ...
      return -1;
    }

    // Pour chaque mois
    foreach($months as $month => $ids){
      //Détermination du score maximum
      $score_max += 1;

      // Vérification que le mois existe dans le json user
      if(!isset($user[$id][$month])){
      // ... Le mois n'existe pas, on passe au mois suivant ...
        continue;
      }

      // L'identifiant valide est celui du json original
      $valid_id = $original[$id][$month][0]; // 0 car il n'y toujours qu'un seul element

      // Si l'ID à trouver est dans la liste des ID supposé:
      if(in_array($valid_id, $user[$id][$month])){
        // Le score est proportionnel au nombre de proposition
        // Plus il y a de propositions, moins cela rapporte de point
        $score += (1/count($user[$id][$month]));
      }
    }
  }
  //echo $score."\n"; //debug
  return $score/$score_max;
}