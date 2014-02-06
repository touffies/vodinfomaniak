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

include_once __DIR__ . "/../../../../classes/Baseobj.class.php";

/**
 * Class Vodinfomaniak_commande
 *
 * Cette classe permet la gestion des commandes de produits dématérialisés de type vidéo
 */
class Vodinfomaniak_commande extends Baseobj {

    public $id;
    public $vodinfomaniak_id;
	public $commande_id;
    public $datedebut;
    public $datefin;

	const TABLE = "vodinfomaniak_commande";

	public $table = self::TABLE;

	public $bddvars = array("id", "vodinfomaniak_id", "commande_id", "datedebut", "datefin");

    /**
     * Constructeur
     *
     * @param int/null $id Possibilité de charger une commande en passant son identifiant
     */
    function __construct($id = null) {

		parent::__construct();

		if (intval($id) > 0) $this->charger_id($id);
	}

    /**
     * Méthode utilisée pour compter le nombre de produits dématérialisés de type vidéo dans une même commande
     *
     * @param $commande_id Identifiant de la commande
     *
     * @return int Retourne le nombre de produits
     */
    public function count_commande($commande_id){
        $res = $this->query("SELECT count(*) AS nb FROM $this->table WHERE id=" . intval($commande_id));
        return $nb = $res ? $this->get_result($res,0,"nb") : 0;
    }

    /**
     * Initialisation du plugin, création de la table si elle n'existe pas encore et
     * Ajout en base d'un email pour informer le client quand sa location est disponible
     *
     * @return  none
     */
    public function init() {

        $query = "CREATE TABLE IF NOT EXISTS `$this->table` (
			 `id` INT NOT NULL auto_increment,
			 `vodinfomaniak_id` INT UNSIGNED NOT NULL,
			 `commande_id` INT UNSIGNED NOT NULL ,
		  	 `datedebut` DATETIME NULL,
		  	 `datefin` DATETIME NULL,
			PRIMARY KEY (  `id` )
			) AUTO_INCREMENT=1 ;";

        $this->query($query);


        // Création du mail de confirmation VOD
        $message = new Message();

        if (! $message->charger("vodinfomaniak_confirmation")) {

            $message->nom = "vodinfomaniak_confirmation";
            $lastid = $message->add();

            $messagedesc = new Messagedesc();
            $messagedesc->message = $lastid;
            $messagedesc->lang = 1;
            $messagedesc->intitule = "Mail de confirmation VOD Infomaniak";
            $messagedesc->titre    = "Votre vidéo commandée sur __NOMSITE__";
            $messagedesc->descriptiontext =
                "__CLIENT_FACTPRENOM__ __CLIENT_FACTNOM__,\n\n"
                . "Votre commande __COMMANDE_REF__ du __COMMANDE_DATE__ incluait la location de vidéo, vous pouvez accéder à celle-ci depuis votre espace client sur notre site __URLSITE__. \n\n"
                . "Attention : la location d'une vidéo est pour une durée de __VOD_DUREE__ jours.\n\n"
                . "Nous restons à votre disposition pour toute information complémentaire.\n"
                . "Cordialement,\n"
                . "L'équipe __NOMSITE__.\n";

            $messagedesc->description = nl2br($messagedesc->descriptiontext);

            $messagedesc->add();
        }

    }

    /**
     * Méthode utilisée pour ajouter une nouvelle entrée en base et permet de définir la date de début et de fin de la location
     *
     * @return int Retourne l'id de la nouvelle ligne en base
     */
    public function add()
    {
        $duree = defined('VODINFOMANIAK_TIME_MAX') ? VODINFOMANIAK_TIME_MAX : 7;

        // On sauvegarde la date de début et de fin de location au moment de l'achat
        $this->datedebut = date("Y-m-d H:i:s");
        $this->datefin = date('Y-m-d H:i:s', date(strtotime("+".$duree." day", strtotime($this->datedebut))));

        $this->id = parent::add();
        return $this->id;
    }

    /**
     * Méthode utilisée pour envoyer un email et informer que la vidéo est disponible dans l'espace personnel du client
     *
     * @param object $commande Objet de type commande
     *
     * @return none
     */
    public function valider($commande) {

        // On essaie d'envoyer un email si celui-ci existe
        try {
            $message = new Message("vodinfomaniak_confirmation");
            $messagedesc = new Messagedesc($message->id, $commande->lang);

            $client = new Client($commande->client);

            $sujet = $this->substitutions($messagedesc->titre, $client, $commande);
            $texte = $this->substitutions($messagedesc->descriptiontext, $client, $commande);
            $html  = $this->substitutions($messagedesc->description, $client, $commande);

            // Envoi du mail au client
            Mail::envoyer(
                "$client->prenom $client->nom", $client->email,
                Variable::lire('nomsite'), Variable::lire('emailcontact'),
                $sujet,
                $html, $texte);
        } catch (Exception $e) {
            // TODO: Echec d'envoi de mail
        }
    }

    /**
     * Méthode utilisée pour remplacer les variables dans le message
     *
     * @param string $texte Le message complet
     * @param objet $client Objet de type client
     * @param objet $commande Objet de type commande
     *
     * @return string Retourne le message avec les variables remplacées
     */
    private function substitutions($texte, $client, $commande) {

        /* SITE */
        $texte = str_replace("__URLSITE__", Variable::lire('urlsite'), $texte);
        $texte = str_replace("__NOMSITE__", Variable::lire('nomsite'), $texte);

        /* CLIENT */
        $venteadr = new Venteadr($commande->adrfact);
        $raisondesc = new Raisondesc();
        $raisondesc->charger($venteadr->raison);

        $texte = str_replace("__CLIENT_REF__", $client->ref, $texte);
        $texte = str_replace("__CLIENT_RAISON__", $raisondesc->court, $texte);
        $texte = str_replace("__CLIENT_NOM__", mb_strtoupper($client->nom, 'UTF-8'), $texte);
        $texte = str_replace("__CLIENT_PRENOM__", ucfirst(strtolower($client->prenom)), $texte);
        $texte = str_replace("__CLIENT_FACTNOM__", mb_strtoupper($venteadr->nom, 'UTF-8'), $texte);
        $texte = str_replace("__CLIENT_FACTPRENOM__", ucfirst(strtolower($venteadr->prenom)), $texte);
        $texte = str_replace("__CLIENT_EMAIL__", strtolower($client->email), $texte);

        /* COMMANDE */
        $datecommande = strtotime($commande->date);
        $texte = str_replace("__COMMANDE_REF__", $commande->ref, $texte);
        $texte = str_replace("__COMMANDE_DATE__", strftime("%d/%m/%Y", $datecommande), $texte);
        $texte = str_replace("__COMMANDE_HEURE__", strftime("%H:%M:%S", $datecommande), $texte);

        /* VOD */
        $duree = defined('VODINFOMANIAK_TIME_MAX') ? VODINFOMANIAK_TIME_MAX : 7;
        $texte = str_replace("__VOD_DUREE__", $duree, $texte);

        preg_match("`<VENTEVOD>([^<]+)</VENTEVOD>`", $texte, $cut);
        $texte = str_replace("<VENTEVOD>", "", $texte);
        $texte = str_replace("</VENTEVOD>", "", $texte);

        $vodinfomaniak_commande = new Vodinfomaniak_commande();
        $query = "SELECT * FROM $vodinfomaniak_commande->table WHERE commande_id=" . intval($commande->id);
        $res_vodinfomaniak_commande = $vodinfomaniak_commande->query($query);
        while($res_vodinfomaniak_commande && $vod_cmd = $vodinfomaniak_commande->fetch_object($res_vodinfomaniak_commande)) {
            $vodinfomaniak = new Vodinfomaniak($vod_cmd->vodinfomaniak_id);
            $proddesc = new Produitdesc($vodinfomaniak->produit_id, $commande->lang);

            $texte = str_replace("__VOD_TITRE__", $proddesc->titre, $texte);

            $vod_datedebut = strtotime($vod_cmd->datedebut);
            $vod_datefin = strtotime($vod_cmd->datefin);
            $texte = str_replace("__VOD_DATEDEBUT__", strftime("%d/%m/%Y", $vod_datedebut), $texte);
            $texte = str_replace("__VOD_DATEFIN__", strftime("%d/%m/%Y", $vod_datefin), $texte);
        }

        return $texte;
    }


    /**
     * Méthode appelée quand on désactive le plugin
     *
     * @return none
     */
    public function destroy() {
		// Suppression du mail de confirmation
        /*$message = new Message();

        if ($message->charger("vodinfomaniak_confirmation")) {
            $message->delete();
        }*/
	}
}
?>