<?php
    function printedRank($rank) {
      switch ($rank) {
            case 1:
                return '<span class="badge badge-warning">'.$rank.'</span>';
                break;
            case 2:
                return '<span class="badge badge-secondary" style="background-color: #cecece;">'.$rank.'</span>';
                break;
            default:
               return '<span class="badge badge-dark">'.$rank.'</span>';
        }
    }
?>

<div class=container>

<div class="jumbotron">
    <div class="row">
        <div class="col-md-6">
            <h1>Classement des équipes: </h1>
        </div>
        <div class="col-md-6">
                     <form class="form-inline float-right">
                         <div class="mr-sm-2">
                         <select name="team[]" class="selectpicker" multiple data-style="btn-outline-primary mr-sm-2" data-live-search="true">
                             <?php foreach($users_all as $user): ?>
                                <option value="<?=$user->id?>" <?=(!isset($list_id) || in_array(intval($user->id), $list_id))?'selected':''?>><?=$user->team?></option>
                             <?php endforeach ?>
                        </select>
                        </div>
                          <br><br>
                          <input class="btn btn-primary" type="submit" value="filtrer">
                    </form>
        </div>
    </div>
</div>

<style>
.collapsing {
  transition: none !important;
}
</style>
    
<div class="bs-docs-section">

        <div class="row">
          <div class="col-lg-12">
            <div class="bs-component">
              <table class="table table-sm table-bordered">
                <thead class="black">
                  <tr class="table-danger">
                    <th scope="col">Résultat général</th>
                    <th scope="col">Nom de l'équipe</th>
                    <th scope="col">Score de défense</th>
                    <th scope="col">Score d'attaque</th>
                  </tr>
                </thead>
                <tbody>
                    <?php foreach($rank_general as $user_rank): ?>
                        <tr class="table-light">
                            <th scope="row"></th>
                            <td><u><?=$user_rank[0]->team ?></u></td>
                            <td><strong><?=round($user_rank[0]->getDefenseScore()*100,2) ?>%</strong> <?=printedRank($user_rank[1])?></td>
                            <td><strong><?=round($user_rank[0]->getAttackScore(),4)?>/<?=count($user_rank[0]->getAllUsers())-1?></strong> <?=printedRank($user_rank[2])?></td>
                        </tr>
                      <?php endforeach ?>
                </tbody>
              </table>
          </div>
        </div>
      </div>

<div class="bs-docs-section">
    <table class="table">
      <thead class="table-success">
        <tr>
          <th scope="col">#</th>
          <th scope="col">Nom d'équipe</th>
          <th scope="col">Soumission</th>
          <th scope="col">Utilité</th>
          <th scope="col">Meilleur attaque</th>
          <th scope="col">Score de défense</th>
          <th scope="col">Détails</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($rank_by_defense as $tab): ?>
        <tr>
            <th scope="row"><?=printedRank($rank_by_defense_start++) ?></th>
            <td><u><?=$tab[0]->team ?></u> </td>
            <td></td>
            <td></td>
            <td></td>
            <td><strong><?=round($tab[1]*100, 2) ?>%</strong></td>
            <td><button data-toggle="collapse" data-target="#IDDefend<?=$tab[0]->team ?>" class="accordion-toggle btn btn-success">+</button></td>
        </tr>
        <tr class="hiddenRow">
                <?php foreach($tab[0]->getDetailedDefenseScore() as $line): ?>
                <tr id="IDDefend<?=$tab[0]->team ?>" class="accordian-body collapse table-secondary">
                <td></td>
                <td></td>
                <td><strong>#<?=$line['SubmissionId'] ?> <?=$line['name'] ?></strong></td>
                <td><?=round($line['utility'], 4) ?></td>
                <td><?=round($line['best_attack'], 4) ?> par <u><?=$tab[0]->getUserByID($line['best_attack_team'])->team?></u></td>
                <td><?=round($line['score']*100, 2) ?>%</td>
                <td></td>
              </tr>

            <?php endforeach ?>


      </tr>
      <?php endforeach ?>

      </tbody>
    </table>
</div>

    <div class="bs-docs-section">
    <table class="table">
      <thead class="table-warning">
        <tr>
          <th scope="col">#</th>
          <th scope="col">Nom d'équipe</th>

          <th scope="col">Attaque minimal</th>
          <th scope="col">Attaqué</th>
          <th scope="col">Score d'attaque</th>
          <th scope="col">Détails:</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($rank_by_attack as $tab): ?>
        <tr>
            <th scope="row"><?=printedRank($rank_by_attack_start++) ?></th>
            <td><u><?=$tab[0]->team ?></u> </td>

            <td></td>
            <td></td>
            <td><strong><?=round($tab[1], 4)?>/<?=count($user->getAllUsers())-1?></strong></td>
            <td> <button data-toggle="collapse" data-target="#IDAttack<?=$tab[0]->team ?>" class="accordion-toggle btn btn-warning">+</button></td>

            <?php foreach($tab[0]->getDetailedAttackScore() as $line): ?>
                  <tr id="IDAttack<?=$tab[0]->team ?>" class="accordian-body collapse table-secondary">
                        <td></td>
                        <td></td>
                        <td><?=round($line['result'], 4) ?>  sur <strong>#<?=$line['SubmissionId'] ?> <?=$line['name'] ?></strong></td>
                        <td><u><?=$tab[0]->getUserByID($line['UserId'])->team ?></u></td>
                        <td><button data-toggle="collapse" data-target="#IDAttack<?=$tab[0]->team ?>-<?=$line['SubmissionId']?>" class="accordion-toggle btn btn-secondary">+</button></td>
                        <td></td>
                          <?php foreach($tab[0]->getDetailedUserIdAttackScore($line['UserId']) as $subline): ?>
                                  <tr id="IDAttack<?=$tab[0]->team ?>-<?=$line['SubmissionId']?>" class="accordian-body collapse table-active">
                                        <td></td>
                                        <td></td>
                                        <td> <?=round($subline['result'], 4) ?>  sur <strong>#<?=$subline['SubmissionId'] ?> <?=$subline['name'] ?></strong></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                 </tr>
                            <?php endforeach ?>
                 </tr>
            <?php endforeach ?>
      <?php endforeach ?>

      </tbody>
    </table>
</div>
</div>
