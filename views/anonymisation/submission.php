<div class="jumbotron">
	<h1> Soumission de la phase d'anonymisation </h1>
	<p><u>Rappel:</u> vous disposez de 3 tentatives maximum. </p>
</div>

<div class=container>
	<div class="text-primary">
		<h2>Soumissions pour la phase d'anonymisation: </h2>
	</div>
	<div class=container>
			<table class="table">
				<tr scope="row">
					<th scope="col-8">
							<div class="custom-file" id="container">
								<input type="file" id="custom-file-input" href="javascript:;" class="custom-file-input" name="customFile">
								<label class="custom-file-label" for="customFile">Choisir un fichier ZIP</label>
							</div>
							<th scope="col-sm-4">

									<button id="uploadfiles" class="btn btn-primary "/>Envoyer</button>
							</th>
					</th>
				</tr>
			</table>
<ul id="filelist"></ul>
<br />
<pre id="console"></pre>
<script>
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>
<script type="text/javascript">
// Custom example logic

var uploader = new plupload.Uploader({
    browse_button : 'custom-file-input',
    container: document.getElementById('container'),
    chunk_size: '2MB',
    url : '/anonymisation/upload',

    init: {
        PostInit: function() {
            document.getElementById('filelist').innerHTML = '';
            document.getElementById('uploadfiles').onclick = function() {
                uploader.start();
                return false;
            };
        },

        FilesAdded: function(up, files) {
            plupload.each(files, function(file) {
                document.getElementById('filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
            });
        },

        UploadProgress: function(up, file) {
            document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
        },

        UploadComplete: function(up, files) {
            window.location = window.location.href;
        },

        Error: function(up, err) {
            document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
        }
    }
});

uploader.init();

</script>
<br>
<br>
<table class="table">
  <thead class="table-info">
    <tr>
      <th scope="col">Vos soumissions:</th>
	  	<th scope="col">Utilité:</th>
		<th scope="col">Score attaque naïve:</th>
	  	<th scope="col">Status:</th>
                <th scope="col">Renommer:</th>
		<th scope="col">Supprimer:</th>
		<th scope="col">Publier:</th>
    </tr>
  </thead>
  <tbody>
		<?php foreach($submissions as $submission): ?>
					<tr>
					<th scope="row">#<?=$submission->submissionId ?> <?=$submission->name ?></th>
						<td> <?=round($submission->utility,4)?> </td>
						<td> <?=round($submission->naiveAttack,4)?>  </td>
						<td> Status : <?= $submission->status?>  </td>
                                                <td>
                                                    <form method="post" class="form-inline">
                                                        <input class="form-control mr-sm-2" name="newName" placeholder="Nom de soumission">
                                                        <input type=hidden class="form-control" name="subToRename" value="<?=$submission->submissionId ?>">
                                                        <button type="submit" class="btn btn-primary">Modifier</button>
                                                    </form>
                                                </td>
						<td>
							<form method="post">
								<input type="hidden"  class="form-control"  name="subToDelete" value=<?=$submission->submissionId?> >
								<input type="submit" name="submit" value="Supprimer" class="btn btn-danger">
							</form>
				  	        </td>
					</th>
					<?php
					if($submission->naiveAttack >= 0){
					if($submission->isPublished==0){
							echo'<th scope="col-8">';
							echo'<form method="post">';
								echo'<input type="hidden"  class="form-control"  name="subToPublish" value='.$submission->submissionId.'>';
								echo'<input type="submit" name="submit" value="Publier" class="btn btn-success">';
								echo'	</form>';
								echo'</th>';
						}else{
							echo'<th scope="col-8">';
							echo'<form method="post">';
								echo'<input type="hidden"  class="form-control"  name="subToUnpublish" value='.$submission->submissionId.'>';
								echo'<input type="submit" name="submit" value="Retirer la publication" class="btn btn-success">';
								echo'	</form>';
								echo'</th>';
						}
					}
					?>
				</tr>
					</tr>
		<?php endforeach?>
  </tbody>
</table>
