<p>
	<a href="accueil.php" class="lien04"><?php echo trad('Accueil', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module_liste.php" class="lien04"><?php echo trad('Modules', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module.php?nom=vodinfomaniak" class="lien04"><?php echo trad('VOD Infomaniak Network', 'vodinfomaniak'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<span style="color: #4E6172; font-size: 12px;margin-left:3px;"><?php echo trad('Gestion des dossiers', 'vodinfomaniak'); ?></span>
</p>

<!-- bloc déclinaisons / colonne gauche -->
<div id="bloc_description">
	<div class="entete_liste_config">
		<div class="titre"><?php echo trad(CONST_VODINFOMANIAK_STOKEN == 1 ? 'Liste des dossiers' : 'Liste des dossiers sécurisés' , 'vodinfomaniak'); ?></div>
		<div class="fonction_valider">
			<a onclick="$('#frm_folder').submit(); return false;" href="#"><?php echo trad('VALIDER LES MODIFICATIONS', 'admin'); ?></a>
		</div>
	</div>

    <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: separate;margin: 0;">
        <tbody>
        <tr class="claire">
            <td class="designation"><?php echo trad('Dossier', 'vodinfomaniak'); ?><br> <span class="note"><?php echo trad('Filtrer l\'accès à l\'espace VOD', 'vodinfomaniak'); ?></span></td>
            <td>
                <form id="frm_folder" action="" method="post">
                    <input type="hidden" name="action" value="folder_save" />
                    <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />

                    <select name="select_folder" id="select_folder" class="form_long">
                        <?php
                        $vodinfomaniak_folder = new vodinfomaniak_folder();
                        $folders = $vodinfomaniak_folder->query_liste("SELECT * FROM $vodinfomaniak_folder->table ORDER BY sPath, sName ASC");

                        if (empty($folders)) {
                            echo "<option value=\"\">" . trad("Aucun dossier disponible", 'vodinfomaniak') . "</option>";
                        } else {
                            // Dossier courant
                            $folder_current = Variable::lire('vodinfomaniak_folder');

                            echo "<option value=\"\">" . trad("-- Tous les dossiers --", 'vodinfomaniak') . "</option>";
                            foreach($folders as $folder)
                            { ?>
                                <option value="<?php echo($folder->iFolder); ?>"<?php if ($folder_current == $folder->iFolder) { ?> selected="selected"<?php } ?>>
                                    <?php echo trad('Dossier :', 'vodinfomaniak'); ?>/<?php echo($folder->sPath); ?>,
                                    <?php echo trad('Nom : ', 'vodinfomaniak'); ?><?php echo($folder->sName); ?>
                                </option>
                            <?php }
                        } ?>
                    </select>
                </form>
            </td>
        </tr>
        </tbody>
    </table>

    <p><small>
            <?php echo trad('- Par défaut, ce plugin permet d\'accéder a l\'integralité des vidéos/dossiers présentes sur votre espace VOD.<br />
- L\'option ci-dessus permet de restreindre l\'accès de ce site à un dossier ainsi que tous ses dossiers fils.', 'vodinfomaniak'); ?>
    </small></p>
</div>



