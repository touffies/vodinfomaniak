<p>
	<a href="accueil.php" class="lien04"><?php echo trad('Accueil', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module_liste.php" class="lien04"><?php echo trad('Modules', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<span style="color: #4E6172; font-size: 12px;margin-left:3px;"><?php echo trad('VOD Infomaniak Network', 'vodinfomaniak'); ?></span>
</p>

<div id="bloc_informations">
	<ul style="width: 50%">
		<li class="entete_configuration" style="width: 445px"><?php echo trad('VOD Infomaniak', 'vodinfomaniak'); ?></li>
 		<li class="claire" style="width:390px; background-color:#9eb0be;border-bottom: 1px dotted #FFF;"><?php echo trad('Connection au site', 'vodinfomaniak'); ?></li>
		<li class="claire" style="width:50px;"><a href="module.php?nom=vodinfomaniak&action_vodinfomaniak=configuration"><?php echo trad('éditer'); ?> </a></li>
 		<?php if (variable::lire('vodinfomaniak_connected')) { ?>
        <li class="fonce" style="width:390px; background-color:#9eb0be;border-bottom: 1px dotted #FFF;"><?php echo trad('Gestion des players', 'vodinfomaniak'); ?></li>
        <li class="fonce" style="width:50px;"><a href="module.php?nom=vodinfomaniak&action_vodinfomaniak=player"><?php echo trad('éditer'); ?> </a></li>
        <li class="claire" style="width:390px; background-color:#9eb0be;border-bottom: 1px dotted #FFF;"><?php echo trad('Gestion des dossiers', 'vodinfomaniak'); ?></li>
        <li class="claire" style="width:50px;"><a href="module.php?nom=vodinfomaniak&action_vodinfomaniak=folder"><?php echo trad('éditer'); ?> </a></li>
        <li class="fonce" style="width:390px; background-color:#9eb0be;border-bottom: 1px dotted #FFF;"><?php echo trad('Gestion des vidéos', 'vodinfomaniak'); ?></li>
		<li class="fonce" style="width:50px;"><a href="module.php?nom=vodinfomaniak&action_vodinfomaniak=video"><?php echo trad('éditer'); ?> </a></li>
        <li class="claire" style="height:auto;width:445px;">
            <form id="frm_update" action=""  method="post" style="text-align: center;">
                <input type="hidden" name="action" value="configuration_update" />
                <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />
                <button type="submit"><?php echo trad('## Synchroniser mon compte<sup>*</sup> ##'); ?></button>
            </form>
            <p><sup>*</sup><small>Synchroniser vos dossiers et vidéos disponibles avec votre espace VOD.</small></p>
        </li>
        <?php } ?>
	</ul>
</div>
