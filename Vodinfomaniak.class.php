<?php
/*************************************************************************************/
/*                                                                                   */
/*      Module VOD Infomaniak pour Thelia	                                         */
/*                                                                                   */
/*      Copyright (c) Openstudio 		                                     		 */
/*      Développement : Christophe LAFFONT		                                     */
/*		email : claffont@openstudio.fr	        	                             	 */
/*      web : http://www.openstudio.fr					   							 */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 2 of the License, or            */
/*      (at your option) any later version.                                          */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program; if not, write to the Free Software                  */
/*      Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    */
/*                                                                                   */
/*************************************************************************************/

include_once __DIR__ . "/config.php";

// Classes du plugin
include_once __DIR__ . "/classes/Public_api.class.php";
include_once __DIR__ . "/classes/Vodinfomaniak_config.class.php";
include_once __DIR__ . "/classes/Vodinfomaniak_video.class.php";
include_once __DIR__ . "/classes/Vodinfomaniak_folder.class.php";
include_once __DIR__ . "/classes/Vodinfomaniak_player.class.php";
include_once __DIR__ . "/classes/Vodinfomaniak_commande.class.php";

// Classes de Thelia
include_once __DIR__ . "/../../../classes/PluginsTransports.class.php";
include_once __DIR__ . "/../../../classes/Variable.class.php";
include_once __DIR__ . "/../../../classes/Produit.class.php";
include_once __DIR__ . "/../../../classes/Produitdesc.class.php";
include_once __DIR__ . "/../../../classes/Transzone.class.php";
include_once __DIR__ . "/../../../classes/Modules.class.php";
include_once __DIR__ . "/../../../classes/Mail.class.php";
include_once __DIR__ . "/../../../classes/Messagedesc.class.php";

// Fonctions de thelia
include_once __DIR__ . "/../../../fonctions/lire.php";

/**
 * Class Vodinfomaniak
 *
 * Cette classe permet de gérer l'achat de produit dématérialisé de type vidéo et de modifier
 * le type de livraison en fonction du panier du client.
 */
class Vodinfomaniak extends PluginsTransports {

	const MODULE = "vodinfomaniak";

    public $id;
    public $produit_id;
    public $iVideo;

    const TABLE = "vodinfomaniak";
    public $table = self::TABLE;

    public $bddvars = array("id", "produit_id", "iVideo");

    /**
     * Constructeur
     *
     * @param int/null $id Possibilité de passer un identifiant pour charger un objet Vodinfomaniak
     */
    function __construct($id = null)
    {

        parent::__construct(self::MODULE);

        if (intval($id) > 0) $this->charger($id);
    }

    /**
     * Initialisation du plugin, création des tables si elles n'existent pas encore
     *
     * @return none
     */
    function init()
    {

        // Table de liaison entre le produit et la vidéo
        $query =
            "CREATE TABLE IF NOT EXISTS `$this->table` (
			 `id` INT NOT NULL auto_increment,
			 `produit_id` INT NOT NULL,
		 	 `iVideo` INT UNSIGNED NOT NULL,
			PRIMARY KEY (  `id` )
			) AUTO_INCREMENT=1 ;";
        $this->query($query);

        // Modification de la description du plugin de transport
        $this->ajout_desc("N/A (Location)", "Module VOD Infomaniak pour Thelia", "Ce plugin vous permet de vendre des produits dématérialisés de type vidéos, hébergés par Infomaniak Network.", 1);

        // On associe à une ZONE
        $mod = new Modules();
        $mod->charger(self::MODULE);

        $zone = new Zone();
        $res_zone = $zone->query("SELECT * FROM $zone->table");
        while($res_zone && $row = $this->fetch_object($res_zone)) {
            $transzone = new Transzone();
            $transzone->transport = $mod->id;
            $transzone->zone = $row->id;
            $transzone->add();
        }

        // On initialise
        $vod_commande = new Vodinfomaniak_commande();
        $vod_commande->init();

        $vod_config = new Vodinfomaniak_config();
        $vod_config->init();

        $vod_video = new Vodinfomaniak_video();
        $vod_video->init();

        $vod_folder = new Vodinfomaniak_folder();
        $vod_folder->init();

        $vod_player = new Vodinfomaniak_player();
        $vod_player->init();

    }

    /**
     * Chargé un objet Vodinfomaniak en fonction de l'identifiant d'un produit
     *
     * @param int $produit_id  Identifiant d'un produit
     *
     * @return objet Un objet Vodinfomaniak
     */
    function charger_produit($produit_id){
        return $this->getVars("SELECT * FROM $this->table WHERE produit_id=" . intval($produit_id));
    }

    /**
     * Méthode utilisée pour le calcul des frais de livraison
     *
     * @return int  Aucun frais de transport
     */
    function calcule()
    {
        return 0;
    }

    /**
     * Méthode appelée lors de la modification d'une fiche produit
     *
     * @param Produit $produit Objet de type produit
     *
     * @return none
     */
    public function modprod(Produit $produit)
    {
        $select_video = trim(lireParam("select_video", "int"));

        // On met à jour la table de liaison vodinfomaniak
        $vodinfomaniak = new Vodinfomaniak();
        if($vodinfomaniak->charger_produit($produit->id))
        {
            // Une vidéo est séléctionnée
            if (intval($select_video) > 0) {
                $vodinfomaniak->iVideo = $select_video;
                $vodinfomaniak->maj();
            } else {
                // Ce produit n'a pas de Vidéo
                $vodinfomaniak->delete();
            }
        } else {
            // Une vidéo est séléctionnée
            if (intval($select_video) > 0) {
                // On ajoute une entrée
                $vodinfomaniak->produit_id = $produit->id;
                $vodinfomaniak->iVideo = $select_video;
                $vodinfomaniak->add();
            }
        }
    }

    /**
     * Gestion des boucles utiles pour le plugin

     * @param $texte
     * @param $args

     * @return string
     */
    public function boucle($texte, $args)
    {

        $boucle = strtolower(lireTag($args, 'boucle'));

        // Sécurité
        if(empty($boucle)) return;

        switch($boucle)
        {
            case 'commande':
                return $this->boucleCommande($texte, $args);
                break;

            case 'transport':
                return $this->boucleTransport($texte, $args);
                break;

            case 'player':
                return $this->bouclePlayer($texte, $args);
                break;

        }
    }

    /**
     * Boucle d'affichage des commandes VOD
     *
     * @param $texte
     * @param $args
     *
     * @return string
     */
    private function boucleCommande($texte, $args) {

        // Récupération des arguments
        $commande_id = lireTag($args, "commande", "int");
        $client_id = lireTag($args, "client", "int");
        $classement = lireTag($args, "classement", "string");
        $statut = lireTag($args, "statut", "string");
        $statutexcl = lireTag($args, "statutexcl", "int_list");


        // Préparation de la requète
        $where = "";
        $return = "";

        if (intval($commande_id) > 0) $where .= " AND cmd.id=" . intval($commande_id);
        if (intval($client_id) > 0) $where .= " AND cmd.client=" . intval($client_id);
        if ($statutexcl != "") $where .= " AND cmd.statut NOT IN ($statutexcl)";
        if ($statut != "" && $statut != "paye") $where .= " AND cmd.statut=\"$statut\"";
        else $where .= " AND cmd.statut > \"1\" AND cmd.statut <> \"5\""; // Par défaut, on recherche pour les statuts : Payé, Traitement et Envoyé.

        $order = ($classement == "inverse") ? " ORDER BY vod_cmd.datedebut ASC" : " ORDER BY vod_cmd.datedebut DESC";

        // Requète
        $vodinfomaniak_commande = new Vodinfomaniak_commande();
        $query = "SELECT * FROM $vodinfomaniak_commande->table AS vod_cmd INNER JOIN ".Commande::TABLE." AS cmd ON vod_cmd.commande_id = cmd.id WHERE DATEDIFF(vod_cmd.datefin, now()) >= 0 $where $order";
        $res_vodinfomaniak_commande = $vodinfomaniak_commande->query_liste($query);

        foreach($res_vodinfomaniak_commande AS $vod_cmd)
        {
            // On recherche l'id du produit, tout en vérifiant que celui-ci est encore en ligne
            $vodinfomaniak = new Vodinfomaniak();
            $query = "SELECT vod.produit_id, vod.iVideo FROM ".Produit::TABLE." AS prod INNER JOIN $vodinfomaniak->table AS vod ON prod.id = vod.produit_id WHERE vod.id = $vod_cmd->vodinfomaniak_id  AND prod.ligne=1";
            $res_vodinfomaniak = $vodinfomaniak->query_liste($query);

            // On loupe car une commande pourrait avoir plusieurs vidéos
            foreach($res_vodinfomaniak AS $vod)
            {
                $proddesc = new Produitdesc($vod->produit_id);
                $vod_video = new Vodinfomaniak_video($vod->iVideo);
                $vod_folder = new Vodinfomaniak_folder($vod_video->iFolder);

                // Cache
                $tmp = $texte;

                $tmp = str_replace("#VOD_COMMANDE_ID", $vod_cmd->commande_id, $tmp);
                $tmp = str_replace("#VOD_TITRE", $proddesc->titre, $tmp);
                $tmp = str_replace("#VOD_NAME", $vod_video->sName, $tmp);
                $tmp = str_replace("#VOD_URL", $this->video_url($vod_folder->sToken, $vod_video->sServerCode), $tmp);

                $vod_datedebut = strtotime($vod_cmd->datedebut);
                $vod_datefin = strtotime($vod_cmd->datefin);
                $tmp = str_replace("#VOD_DATEDEBUT", strftime("%d/%m/%Y", $vod_datedebut), $tmp);
                $tmp = str_replace("#VOD_DATEFIN", strftime("%d/%m/%Y", $vod_datefin), $tmp);

                $return .= $tmp;
            }
        }

        return $return;
    }

    /**
     * Boucle permettant de filtrer le type de transport à proposer au client
     *         en fonction du type de produit dans le panier.
     *
     * @param $texte
     * @param $args
     *
     * @return string
     */
    private function boucleTransport($texte, $args) {

        $exclusion = "";

        // Compter le nombre de produits dématérialisés de type vidéo & de produits physiques
        $nb_prod_dematerialise = 0;
        $nb_prod_physique = 0;

        foreach($_SESSION['navig']->panier->tabarticle as &$art) {
            $vodinfomaniak = new Vodinfomaniak();
            if($vodinfomaniak->charger_produit($art->produit->id))
                $nb_prod_dematerialise++;
            else
                $nb_prod_physique++;
        }

        // La commande ne conitent que des produits dématérialisés de type vidéo
        if($nb_prod_dematerialise > 0 &&  $nb_prod_physique == 0) {
            // on doit exclure tous les autres modes de livraison
            $modules = new Modules();
            $modulesListe = $modules->query_liste("SELECT nom FROM $modules->table WHERE actif=1 AND type=2 AND nom <> '".self::MODULE."'");
            foreach($modulesListe as $mod)
            {
                $exclusion .= $mod->nom.",";
            }
        } else { $exclusion = self::MODULE.","; }

        // Substitutions
        $texte = str_replace("#VOD_EXCLUSION", $exclusion, $texte);

        return $texte;
    }

    /**
     * Boucle permettant d'envoyer les paramètres du player par défaut
     *
     * @param $texte
     * @param $args
     *
     * @return string
     */
    private function bouclePlayer($texte, $args) {

        // Récupération des arguments
        $player_id = intval(lireTag($args, "player", "int")) > 0 ? lireTag($args, "player", "int") : Variable::lire('vodinfomaniak_player');
        $video_id = lireTag($args, "video", "int");
        $classement = lireTag($args, "classement", "string");

        // Vidéo
        $vodinfomaniak_video = new Vodinfomaniak_video();
        if (intval($video_id) > 0)
        {
            $vodinfomaniak_video->charger($video_id);
        } else {
            $sServerCode = trim(lireParam("name", "string"));
            $vodinfomaniak_video->charger_servercode($sServerCode);
        }

        // Sécutité (Il faut une vidéo pour continuer)
         if(intval($vodinfomaniak_video->iVideo) == 0) return;

        // Folder
        $vodinfomaniak_folder = new Vodinfomaniak_folder($vodinfomaniak_video->iFolder);

        // Sécurité : on vérifie si le répertoire est sécurisé
        $sKey = "";
        if($vodinfomaniak_folder->sToken)
        {
            $sToken = trim(lireParam("key", "string"));
            if($sToken !== $this->getTemporaryKey($vodinfomaniak_folder->sToken, $vodinfomaniak_video->sServerCode)) return;

            $sKey = $sToken ? "&sKey=$sToken" : '';
        }

        // Construction de l'url pour visualiser la vidéo
        $video_url = Variable::lire('vodinfomaniak_id') . $vodinfomaniak_video->sPath . $vodinfomaniak_video->sServerCode;

        // Préparation de la requète
        $where = "";
        if (intval($player_id) > 0) $where .= " WHERE iPlayer=" . intval($player_id);

        $order = ($classement == "inverse") ? " ORDER BY dEdit ASC" : " ORDER BY dEdit DESC";

        // Player
        $vodinfomaniak_player = new Vodinfomaniak_player();
        $query = "SELECT * FROM $vodinfomaniak_player->table $where $order LIMIT 1";
        $vodinfomaniak_player->getVars($query);

        $texte = str_replace("#VOD_URL", $video_url.".".strtolower($vodinfomaniak_video->sExtension) . $sKey, $texte);
        $texte = str_replace("#VOD_PLAYER", $vodinfomaniak_player->iPlayer, $texte);
        $texte = str_replace("#VOD_CODESERVICE", Variable::lire('vodinfomaniak_icodeservice'), $texte);
        $texte = str_replace("#VOD_WIDTH", $vodinfomaniak_player->iWidth, $texte);
        $texte = str_replace("#VOD_HEIGHT", $vodinfomaniak_player->iHeight, $texte);
        $texte = str_replace("#VOD_IMAGE", $video_url.".jpg", $texte);

        return $texte;
    }


    /**
     * Méthode appelée lors de la sauvegarde d'une commande
     *
     * @param $commande Objet de type commande (Info de la commande courante)
     *
     * @return none
     */
    public function aprescommande($commande){

        foreach($_SESSION['navig']->panier->tabarticle as &$art) {

            // On vérifie s'il y a des produits dématérialisés de type vidéo
            $vodinfomaniak = new Vodinfomaniak();
            if($vodinfomaniak->charger_produit($art->produit->id))
            {
                $vodinfomaniak_commande = new Vodinfomaniak_commande();
                $vodinfomaniak_commande->vodinfomaniak_id = $vodinfomaniak->id;
                $vodinfomaniak_commande->commande_id = $commande->id;
                $vodinfomaniak_commande->add();
            }
        }
    }

    /**
     * Méthode appelée lors du changement de statut d'une commande et permet d'envoyer un mail de confirmation
     *
     * @param $commande Objet de type commande
     *
     * @return none
     */
    public function statut($commande) {

        // Sécurité - Pour envoyer un email, la commande doit être en statut payé.
        if($commande->statut != "2")
            return;

         $vodinfomaniak_commande = new Vodinfomaniak_commande();
        if ($vodinfomaniak_commande->count_commande($commande->id) > 0) {
            $vodinfomaniak_commande->valider($commande);
        }
    }

    /**
     * Méhode utilisée pour générer l'url de la page pour visualiser une vidéo
     *
     * @param $sToken clef unique permmettant de limiter l'accès à un répertoire
     * @param $sVideoName Nom de la vidéo (sans l'extension)
     *
     * @return string Retourne l'url pour visualiser la vidéo
     */
    public function video_url($sToken, $sVideoName)
    {
        // On construit l'url
        $url = CONST_VODINFOMANIAK_URL;

        // Répertoire ayant une restriction par clé
        $hash = $sToken ? $this->getTemporaryKey($sToken, $sVideoName) : "";

        // Substitutions
        $url = str_replace("__SKEY__", $hash, $url);
        $url = str_replace("__SVIDEONAME__", $sVideoName, $url);
        $url = str_replace("__STOKEN__", $sToken, $url);

        return $url;
    }

    /**
     * Méhode utilisée pour générer le hash pour accéder à une vidéo
     *
     * @param $sKey clef unique permmettant de limiter l'accès à un répertoire
     * @param $sVideoName Nom de la vidéo (sans l'extension)
     *
     * @return string Retourne le hash
     */
    function getTemporaryKey($sKey, $sVideoName) {

        // Adresse IPv4 de l'utilisateur
        $sUserIP = $_SERVER['REMOTE_ADDR']; // Attention, votre adresse IP en local pourrait

        // on gère la différence de temps
        $iTime = time() + intval(Variable::lire('vodinfomaniak_serverTime'));

        //return md5( $sKey . $sVideoName . $sUserIP . date("YmdH", $iTime) );
        return md5( $sKey . $sVideoName . $sUserIP . date("YmdH") );
    }


    /**
     * Méthode appelée quand on désactive le plugin
     *
     * @return none
     */
    function destroy()
    {
        // Désactivation
        $vod_commande = new Vodinfomaniak_commande();
        $vod_commande->destroy();

        $vod_video = new Vodinfomaniak_video();
        $vod_video->destroy();

        $vod_folder = new Vodinfomaniak_folder();
        $vod_folder->destroy();

        $vod_player = new Vodinfomaniak_player();
        $vod_player->destroy();

        $vod_config = new Vodinfomaniak_config();
        $vod_config->destroy();

        // On supprime l'association à une ZONE
        $mod = new Modules();
        $mod->charger(self::MODULE);

        $this->query("DELETE FROM " . Transzone::TABLE . " WHERE transport = $mod->id");
    }
}
?>