<p>
	<a href="accueil.php" class="lien04"><?php echo trad('Accueil', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module_liste.php" class="lien04"><?php echo trad('Modules', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module.php?nom=vodinfomaniak" class="lien04"><?php echo trad('VOD Infomaniak Network', 'vodinfomaniak'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<span style="color: #4E6172; font-size: 12px;margin-left:3px;"><?php echo trad('Connection VOD Infomaniak', 'vodinfomaniak'); ?></span>
</p>

<!-- bloc déclinaisons / colonne gauche -->
<div id="bloc_description">
	<div class="entete_liste_config">
		<div class="titre"><?php echo trad('Paramètres de connection', 'vodinfomaniak'); ?></div>
		<div class="fonction_valider">
			<a onclick="$('#frm_configuration').submit(); $(this).removeAttr('onclick').css( 'cursor', 'progress' ); return false;" href="#"><?php echo trad('VALIDER LES MODIFICATIONS', 'admin'); ?></a>
		</div>
	</div>

	<ul class="Nav_bloc_description">
			<li style="height:25px; width:194px;"><?php echo trad('Nom2', 'admin'); ?></li>
			<li style="height:25px; width:360px; border-left:1px solid #96A8B5;"><?php echo trad('Valeur', 'admin'); ?></li>
	</ul>

	<div class="bordure_bottom">
		<form id="frm_configuration" action="" method="post">
		    <input type="hidden" name="action" value="configuration_save" />
			<input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />

			<?php $vod_config = new vodInfomaniak_config(); ?>
            <ul class="ligne_claire_BlocDescription">
                <li style="width:195px;"><label for="vodinfomaniak_login"><?php echo trad('Login', 'vodinfomaniak'); ?></label></li>
                <li style="width:360px; border-left:1px solid #96A8B5;">
                    <input style="width: 355px;" name="valeur[vodinfomaniak_login]" id="vodinfomaniak_login" type="text" class="form" value="<?php echo  $vod_config->lire('vodinfomaniak_login'); ?>" />
                </li>
            </ul>
            <ul class="ligne_fonce_BlocDescription">
                <li style="width:195px;"><label for="vodinfomaniak_pwd"><?php echo trad('Mot de passe', 'vodinfomaniak'); ?></label></li>
                <li style="width:360px; border-left:1px solid #96A8B5;">
                    <input style="width: 355px;" name="valeur[vodinfomaniak_pwd]" id="vodinfomaniak_pwd" type="text" class="form" value="<?php echo  $vod_config->lire_pwd('vodinfomaniak_pwd'); ?>" />
                </li>
            </ul>
            <ul class="ligne_fonce_BlocDescription">
                <li style="width:195px;"><label for="vodinfomaniak_id"><?php echo trad('Identifiant VOD', 'vodinfomaniak'); ?></label></li>
                <li style="width:360px; border-left:1px solid #96A8B5;">
                    <input style="width: 355px;" name="valeur[vodinfomaniak_id]" id="vodinfomaniak_id" type="text" class="form" value="<?php echo  $vod_config->lire('vodinfomaniak_id'); ?>" />
                </li>
            </ul>
        </form>
		
		<br clear="both" />

		<p>
			<strong><?php echo trad('Connection :', 'vodinfomaniak'); ?></strong>
			<?php
				if ($vod_config->is_connected()) {
					echo "<span style='color: green;'>" . trad('Parfait vous êtes connecté.', 'vodinfomaniak');
					"</span>";
				} else {
					echo "<span style='color: red;'>" . trad('Impossible de se connecter.', 'vodinfomaniak');
					"</span>";
				}
			?>
		</p>

	</div>
</div>

<br clear="both" />

<p><small>
	<?php echo trad('- Pour fonctionner, le plugin a besoin de s\'interfacer avec votre compte VOD Infomaniak.<br />
- Pour des raisons de sécurités, il est fortement conseillé de créer un nouvel utilisateur dédié dans votre admin Infomaniak avec uniquement des droits restreints sur l\'API.<br/>
- Pour plus d\'information, veuillez vous rendre dans la partie "Configuration -> Api & Callback" de votre administration VOD.', 'vodinfomaniak'); ?>
</small></p>
