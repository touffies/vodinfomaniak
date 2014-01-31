<p>
	<a href="accueil.php" class="lien04"><?php echo trad('Accueil', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module_liste.php" class="lien04"><?php echo trad('Modules', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module.php?nom=vodinfomaniak" class="lien04"><?php echo trad('VOD Infomaniak Network', 'vodinfomaniak'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<span style="color: #4E6172; font-size: 12px;margin-left:3px;"><?php echo trad('Gestion des players', 'vodinfomaniak'); ?></span>
</p>

<!-- bloc déclinaisons / colonne gauche -->
<div id="bloc_description">
	<div class="entete_liste_config">
		<div class="titre"><?php echo trad('Liste des players' , 'vodinfomaniak'); ?></div>
		<div class="fonction_valider">
			<a onclick="$('#frm_player').submit(); return false;" href="#"><?php echo trad('VALIDER LES MODIFICATIONS', 'admin'); ?></a>
		</div>
	</div>

    <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: separate;margin: 0;">
        <tbody>
        <tr class="claire">
            <td class="designation"><?php echo trad('PLayer', 'vodinfomaniak'); ?><br> <span class="note"><?php echo trad('Player par défaut', 'vodinfomaniak'); ?></span></td>
            <td>
                <form id="frm_player" action="" method="post">
                    <input type="hidden" name="action" value="player_save" />
                    <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />

                    <select name="select_player" id="select_player" class="form_long">
                        <?php
                        $vodinfomaniak_player = new vodinfomaniak_player();
                        $players = $vodinfomaniak_player->query_liste("SELECT * FROM $vodinfomaniak_player->table ORDER BY dEdit DESC");

                        if (empty($players)) {
                            echo "<option value=\"\">" . trad("Aucun player de disponible", 'vodinfomaniak') . "</option>";
                        } else {
                            // Dossier courant
                            $player_current = Variable::lire('vodinfomaniak_player');

                            foreach($players as $player)
                            { ?>
                                <option value="<?php echo($player->iPlayer); ?>"<?php if ($player_current == $player->iPlayer) { ?> selected="selected"<?php } ?>>
                                    <?php echo($player->sName) . " (" . $player->iWidth ."x". $player->iHeight .")"; ?>
                                </option>
                            <?php }
                        } ?>
                    </select>
                </form>
            </td>
        </tr>
        </tbody>
    </table>
</div>



