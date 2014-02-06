<p>
	<a href="accueil.php" class="lien04"><?php echo trad('Accueil', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module_liste.php" class="lien04"><?php echo trad('Modules', 'admin'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module.php?nom=vodinfomaniak" class="lien04"><?php echo trad('VOD Infomaniak Network', 'vodinfomaniak'); ?></a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<span style="color: #4E6172; font-size: 12px;margin-left:3px;"><?php echo trad('Gestion des Vidéos', 'vodinfomaniak'); ?></span>
</p>

<!-- bloc déclinaisons / colonne gauche -->
<div id="bloc_description">
	<div class="entete_liste_config">
		<div class="titre"><?php echo trad('Liste des vidéos', 'vodinfomaniak'); ?></div>
		<!--div class="fonction_valider">
			<a onclick="$('#frm_video').submit(); return false;" href="#"><?php echo trad('VALIDER LES MODIFICATIONS', 'admin'); ?></a>
		</div-->
	</div>

	<ul class="Nav_bloc_description">
		<li style="width:200px;"><?php echo trad('Vidéo', 'vodinfomaniak'); ?></li>
        <li style="width:120px;"><?php echo trad('Durée', 'vodinfomaniak'); ?></li>
        <li style="width:150px;"><?php echo trad('Date de téléchargement', 'vodinfomaniak'); ?></li>
        <!--li style="width:80px; text-align: center;"><?php echo trad('Supprimer', 'admin'); ?></li-->
	</ul>

	<div class="bordure_bottom">
		<?php
        $vodinfomaniak_video = new vodinfomaniak_video();
        $videos = $vodinfomaniak_video->query_liste("SELECT * FROM $vodinfomaniak_video->table ORDER BY dUpload DESC");

        $fond = 'claire';
        $videoListID = array();
        foreach($videos as $video) {
            $videoListID[] = $video->iVideo;
            ?>
            <ul class="ligne_<?php echo $fond; ?>_BlocDescription">
                <li style="width:200px;"><?php echo($video->sName); ?></li>
                <li style="width:120px;"><?php echo($video->iDuration); ?></li>
                <li style="width:150px;"><?php echo($video->dUpload); ?></li>
                <!--li style="width:80px; text-align: center;"><a onclick="return confirm('<?php echo trad('Supprimer définitivement cette vidéo ?', 'vodinfomaniak'); ?>');" href="video.php?action=supprimer&id=<?php echo($video->iVideo); ?>" title="<?php echo trad('Supprimer cette vidéo ?', 'vodinfomaniak'); ?>"><img src="gfx/supprimer.gif" alt="X" /></a></li-->
            </ul>
            <?php
            $fond = ($fond == 'claire') ?  'fonce' : 'claire';
        }
        ?>
	</div>

    <!--ul class="bouton_ajouter ligne_<?php echo $fond; ?>_BlocDescription lignebottom" style="line-height: 28px;">
        <li style="height: 28px; width:277px; border-right: none;"></li>
        <li style="height: 28px; width:280px; border-left: none; text-align: right;"><button onclick="show_form_ajouter(true); return false;"><?php echo trad('Ajouter une Vidéo', 'vodinfomaniak'); ?></button></li>
    </ul-->

</div>


<?php
// On recherche si des produits actifs sont associés à avec des vidéos qui n'existent plus
$prod = new Produit();
$where = count($videoListID) > 0 ? "vod.iVideo NOT IN (".implode(',', $videoListID).") AND" : "" ;
$query = "SELECT prod.id, prod.ref FROM $prod->table AS prod INNER JOIN ".Vodinfomaniak::TABLE." as vod ON prod.id = vod.produit_id WHERE $where prod.ligne=1";
$produits = $prod->query_liste($query);

if(count($produits) > 0)
{
?>

<div id="bloc_colonne_droite">

    <div class="entete_config">
        <div class="titre"><?php echo trad('Produit à vérifier.', 'vodinfomaniak'); ?></div>
    </div>

    <ul class="Nav_bloc_description">
        <li style="width:280px;"><?php echo trad('Nom', 'vodinfomaniak'); ?></li>
    </ul>

    <div class="bordure_bottom">
        <?php
        $fond = 'claire';
        foreach($produits as $produit) {
           $proddesc = new Produitdesc($produit->id);
            ?>
            <ul class="ligne_<?php echo $fond; ?>_BlocDescription">
                <li style="width:280px;"><a href="produit_modifier.php?ref=<?php echo $produit->ref; ?>" class="txt_vert_11"><?php echo($proddesc->titre); ?></a></li>
            </ul>
            <?php
            $fond = ($fond == 'claire') ?  'fonce' : 'claire';
        }
        ?>
    </div>
</div>
<?php
}
?>


