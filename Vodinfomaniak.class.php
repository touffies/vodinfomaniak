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
include_once __DIR__ . "/../../../classes/Client.class.php";
include_once __DIR__ . "/../../../classes/Produit.class.php";
include_once __DIR__ . "/../../../classes/Produitdesc.class.php";
include_once __DIR__ . "/../../../classes/Venteadr.class.php";
include_once __DIR__ . "/../../../classes/Raisondesc.class.php";
include_once __DIR__ . "/../../../classes/Transzone.class.php";
include_once __DIR__ . "/../../../classes/Modules.class.php";
include_once __DIR__ . "/../../../classes/Mail.class.php";
include_once __DIR__ . "/../../../classes/Message.class.php";
include_once __DIR__ . "/../../../classes/Messagedesc.class.php";

// Fonctions de thelia
include_once __DIR__ . "/../../../fonctions/lire.php";

/**
 * Class Vodinfomaniak
 *
 * Cette classe permet de gérer l'achat de produit dématérialisé de type vidéo et de modifier
 * le type de livraison en fonction du panier du client. (Nécessite le plugin Livraison_zero)
 */
class Vodinfomaniak extends PluginsClassiques {

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

        if (intval($id) > 0) $this->charger_id($id);
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
    function charger_produit($produit_id)
    {
        return $this->getVars("SELECT * FROM $this->table WHERE produit_id=" . intval($produit_id));
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
        if($this->charger_produit($produit->id))
        {
            // Une vidéo est séléctionnée
            if (intval($select_video) > 0) {
                $this->iVideo = $select_video;
                $this->maj();
            } else {
                // Ce produit n'a pas de Vidéo
                $this->delete();
            }
        } else {
            // Une vidéo est séléctionnée
            if (intval($select_video) > 0) {
                // On ajoute une entrée
                $this->produit_id = $produit->id;
                $this->iVideo = $select_video;
                $this->add();
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

            Default:
                return $this->boucleDefaut($texte, $args);

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
    private function boucleCommande($texte, $args)
    {

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
        else {
            // Par défaut, on exclue les statuts : Non Payé et Annulé.
            $statut_exclusion = defined('VODINFOMANIAK_STATUT_EXCLUSION') ? VODINFOMANIAK_STATUT_EXCLUSION : '1,5';
            $where .= " AND cmd.statut NOT IN ($statut_exclusion)";
        }

        $order = ($classement == "inverse") ? " ORDER BY vod_cmd.datedebut ASC" : " ORDER BY vod_cmd.datedebut DESC";

        // Requète
        $vodinfomaniak_commande = new Vodinfomaniak_commande();
        $query = "SELECT * FROM $vodinfomaniak_commande->table AS vod_cmd INNER JOIN ".Commande::TABLE." AS cmd ON vod_cmd.commande_id = cmd.id WHERE DATEDIFF(vod_cmd.datefin, now()) >= 0 $where $order";
        $res_vodinfomaniak_commande = $vodinfomaniak_commande->query_liste($query);

        foreach($res_vodinfomaniak_commande AS $vod_cmd)
        {
            // On recherche l'id du produit, tout en vérifiant que celui-ci est encore en ligne
            $query = "SELECT vod.id, vod.produit_id, vod.iVideo FROM ".Produit::TABLE." AS prod INNER JOIN ".self::TABLE." AS vod ON prod.id = vod.produit_id WHERE vod.id = $vod_cmd->vodinfomaniak_id  AND prod.ligne=1";
            $res_vodinfomaniak = $this->query_liste($query);

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
                $tmp = str_replace("#VOD_NOM", $vod_video->sName, $tmp);
                $tmp = str_replace("#VOD_URL", $this->video_url($vod_folder->sToken, $vod_video->sServerCode, $vod->id), $tmp);

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
     * en fonction du type de produit dans le panier.
     *
     * @param $texte
     * @param $args
     *
     * @return string
     */
    private function boucleTransport($texte, $args)
    {
        $livraison_zero = defined('VODINFOMANIAK_LIVRAISON_ZERO') ? VODINFOMANIAK_LIVRAISON_ZERO :  "livraison_zero";

        // Récupération des arguments
        $id = lireTag($args, "id", "int");
        $exclusion = lireTag($args, "exclusion", "string_list");

        if($id == "")
        {
            // Tableau temporaire
            $arrExclusion = array();
            if($exclusion != "")
                $arrExclusion = explode(",", $exclusion);

            $arrExclusion[] = $livraison_zero;

            // On vérifie si le panier ne contient que des vidéos . Si oui, on propose uniquement $livraison_zero
            $nb_livraison_zero = 0;
            foreach($_SESSION['navig']->panier->tabarticle as &$art) {
                if($art->livraison_zero)
                {
                    $nb_livraison_zero++;

                } else {
                    if($this->charger_produit($art->produit->id))
                    {
                        $nb_livraison_zero++;
                        $art->livraison_zero = true;
                    }
                }
            }

            // On compare le nombre d'article du panier et le nombre de produits de type vod trouvé
            if($_SESSION['navig']->panier->nbart == $nb_livraison_zero)
            {
                // On recherche l'id du module $livraison_zero
                $mod = new Modules();
                if($mod->charger($livraison_zero))
                    $id = $mod->id;

            } else {
                // Substitutions
                $texte = str_replace("#ID", "", $texte);
                $texte = str_replace("#EXCLUSION", implode(",", $arrExclusion), $texte);

                return $texte;
            }

        }

        // Substitutions
        $texte = str_replace("#ID", $id, $texte);
        $texte = str_replace("#EXCLUSION", "", $texte);

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

        // Substitutions
        $texte = str_replace("#VOD_URL", $video_url.".".strtolower($vodinfomaniak_video->sExtension) . $sKey, $texte);
        $texte = str_replace("#VOD_PLAYER", $vodinfomaniak_player->iPlayer, $texte);
        $texte = str_replace("#VOD_CODESERVICE", Variable::lire('vodinfomaniak_icodeservice'), $texte);
        $texte = str_replace("#VOD_WIDTH", $vodinfomaniak_player->iWidth, $texte);
        $texte = str_replace("#VOD_HEIGHT", $vodinfomaniak_player->iHeight, $texte);
        $texte = str_replace("#VOD_IMAGE", $video_url.".jpg", $texte);

        return $texte;
    }

    /**
     * Boucle permettant d'afficher les informations d'une vidéo
     *
     * @param $texte
     * @param $args
     *
     * @return string
     */
    private function boucleDefaut($texte, $args) {

        // Récupération des arguments
        $vod_id = intval(lireTag($args, "id", "int"));

        if($this->charger_id($vod_id))
            $texte = boucleProduit($texte, "id=\"$this->produit_id\"");

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
            if($this->charger_produit($art->produit->id))
            {
                $vodinfomaniak_commande = new Vodinfomaniak_commande();
                $vodinfomaniak_commande->vodinfomaniak_id = $this->id;
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
        if($commande->statut == "2")
        {
            $vodinfomaniak_commande = new Vodinfomaniak_commande();
            if ($vodinfomaniak_commande->count_commande($commande->id) > 0) {
                $vodinfomaniak_commande->valider($commande);
            }
        }
    }

    /**
     * Méhode utilisée pour générer l'url de la page pour visualiser une vidéo
     *
     * @param $sToken clef unique permmettant de limiter l'accès à un répertoire
     * @param $sVideoName Nom de la vidéo (sans l'extension)
     * @param $vod_id Identifiant de l'objet vodinfomaniak
     *
     * @return string Retourne l'url pour visualiser la vidéo
     */
    public function video_url($sToken, $sVideoName, $vod_id)
    {
        // On construit l'url
        $url = defined('VODINFOMANIAK_URL') ? VODINFOMANIAK_URL : "?fond=player&name=__SVIDEONAME__";

        // Répertoire ayant une restriction par clé
        $hash = $sToken ? $this->getTemporaryKey($sToken, $sVideoName) : "";

        // Substitutions
        $url = str_replace("__SKEY__", $hash, $url);
        $url = str_replace("__SVIDEONAME__", $sVideoName, $url);
        $url = str_replace("__STOKEN__", $sToken, $url);
        $url = str_replace("__VODID__", $vod_id, $url);

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
    }
}
?>