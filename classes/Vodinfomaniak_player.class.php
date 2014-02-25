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

// On s'assure que la constante __DIR__ est définie pour les versions de PHP antérieur à 5.3
(@__DIR__ == '__DIR__') && define('__DIR__', realpath(dirname(__FILE__)));

include_once __DIR__ . "/../../../../classes/Baseobj.class.php";

/**
 * Class Vodinfomaniak_player
 *
 * Cette classe permet de sauvegarder et gérer les players existant sur le compte VOD Infomaniak
 */
class Vodinfomaniak_player extends Baseobj {

    public $iPlayer;
    public $sName;
    public $iWidth;
    public $iHeight;
    public $bAutoPlay;
    public $bLoop;
    public $bSwitchQuality;
    public $dEdit;

    const TABLE = "vodinfomaniak_player";
    public  $table = self::TABLE;

    public $bddvars = array("iPlayer", "sName", "iWidth", "iHeight", "bAutoPlay", "bLoop", "bSwitchQuality", "dEdit");

    /**
     * Constructeur
     *
     * @param int/null $iPlayer Possibilité de charger un player en passant son identifiant
     */
    function __construct($iPlayer = null) {

		parent::__construct();

		if (intval($iPlayer) > 0) $this->charger($iPlayer);
	}

    /**
     * Méthode utilisée pour chercher un objet de type Vodinfomaniak_player en fonction de son identifiant
     *
     * @param $iPlayer Identifiant du player
     *
     * @return object Retourne un object de type Vodinfomaniak_player
     */
    public function charger($iPlayer){
		return $this->getVars("SELECT * FROM $this->table WHERE iPlayer=" . intval($iPlayer));
	}

    /**
     * Initialisation du plugin, création de la table si elle n'existe pas encore
     *
     * @return  none
     */
    public function init() {

        $query = "CREATE TABLE IF NOT EXISTS `$this->table` (
			 `iPlayer` INT UNSIGNED NOT NULL,
			 `sName` TEXT NOT NULL,
			 `iWidth` INT UNSIGNED NOT NULL,
		 	 `iHeight` INT UNSIGNED NOT NULL,
		  	 `bAutoPlay` SMALLINT(6) UNSIGNED NOT NULL,
		  	 `bLoop` SMALLINT(6) UNSIGNED NOT NULL,
		  	 `bSwitchQuality` SMALLINT(6) UNSIGNED NOT NULL,
		  	 `dEdit` DATETIME NOT NULL
			);";

        $this->query($query);
    }

    /**
     * Méthode utilisée pour initialiser les attributs d'un dossier avant de l'ajouter en bd
     *
     * @param $iPlayer Identifiant du player
     * @param $sName Nom du player
     * @param $iWidth Largeur du player
     * @param $iHeight Hauteur du player
     * @param $bAutoPlay Démarrage automatique de la vidéo au chargement du player
     * @param $bLoop Lecture en boucle
     * @param $bSwitchQuality Option permettant de changer la qualité de la vidéo en cours de lecture
     * @param $dEdit Date de la dernière modification du player
     *
     * @return int Retourne l'id de la nouvelle ligne en base
     */
    public function add_player($iPlayer, $sName, $iWidth, $iHeight, $bAutoPlay, $bLoop, $bSwitchQuality, $dEdit)
    {
        $this->iPlayer = $iPlayer;
        $this->sName = $sName;
        $this->iWidth = $iWidth;
        $this->iHeight = $iHeight;
        $this->bAutoPlay = $bAutoPlay;
        $this->bLoop = $bLoop;
        $this->bSwitchQuality = $bSwitchQuality;
        $this->dEdit = $dEdit;
        return  $this->add();
    }

    /**
     * Méthode appelée quand on désactive le plugin
     *
     * @return none
     */
    public function destroy() {
        // On supprime la table
        $query = "DROP TABLE `$this->table` ";
        $result = $this->query($query);
	}
}
?>