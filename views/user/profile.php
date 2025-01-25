<div class="bs-component">
              <div class="card mb-3">
                <h3 class="card-header">Profile</h3>
                <div class="card-body">
                  <h5 class="card-title"><?php echo($_SESSION['user']->username); ?></h5>
                  <h6 class="card-subtitle text-muted">Identifiant utilisateur: <?php echo($_SESSION['user']->id); ?></h6>
                </div>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item"><p class="card-text"><u>Équipe:</u> <?php echo($_SESSION['user']->team); ?></p></li>
                  <li class="list-group-item"><p class="card-text"><u>Rôle:</u>
                      <?php if($admin_connected) : ?>
                        <span class="badge badge-pill badge-danger">Administrateur</span>
                      <?php else : ?>
                        <span class="badge badge-pill badge-primary">Participant</span>
                      <?php endif; ?>
                    </li>
                  <li class="list-group-item"><p class="card-text"><u>Changer le mot de passe:</u>
                    <form method="post" class="form-inline my-2 my-lg-0">
                      <input class="form-control mr-sm-2" type="password" name="newPasswd" placeholder="Mot de passe">
                      <input class="form-control mr-sm-2" type="password" name="newPasswdConfirm" placeholder="Confirmer le mot de passe">
                      <button type="submit" class="btn btn-primary">Modifier votre mot de passe</button>
                    </form>
                  </li>
                </ul>
                <div class="card-footer text-muted">
                  <?php echo(date("Y-m-d H:i:s")); ?>
                </div>
              </div>
    <form action="/user/loggout" method="get" class="form-inline my-2 my-lg-0">
                    <button class="btn btn-danger my-2 my-sm-0" type="submit">Déconnexion</button>
                  </form>
</div>
