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
 * Class Vodinfomaniak_folder
 *
 * Cette classe permet de sauvegarder et gérer les dossiers existant sur le compte VOD Infomaniak
 */
class Vodinfomaniak_folder extends Baseobj {

    public $iFolder;
    public $sPath;
    public $sName;
    public $sAccess;
    public $sToken;

    const TABLE = "vodinfomaniak_folder";
    public  $table = self::TABLE;

    public $bddvars = array("iFolder", "sPath", "sName", "sAccess", "sToken");

    /**
     * Constructeur
     *
     * @param int/null $iFolder Possibilité de charger un dossier en passant son identifiant
     */
    function __construct($iFolder = null) {

		parent::__construct();

		if (intval($iFolder) > 0) $this->charger($iFolder);
	}

    /**
     * Méthode utilisée pour chercher un objet de type Vodinfomaniak_folder en fonction de son identifiant
     *
     * @param $iFolder Identifiant du dossier
     *
     * @return object Retourne un object de type Vodinfomaniak_folder
     */
    public function charger($iFolder){
		return $this->getVars("SELECT * FROM $this->table WHERE iFolder=" . intval($iFolder));
	}

    /**
     * Initialisation du plugin, création de la table si elle n'existe pas encore
     *
     * @return  none
     */
    public function init() {

        $query = "CREATE TABLE IF NOT EXISTS `$this->table` (
			 `iFolder` INT UNSIGNED NOT NULL,
			 `sPath` TEXT NOT NULL ,
		 	 `sName` TEXT NOT NULL,
		 	 `sAccess` TEXT NOT NULL,
		  	 `sToken` TEXT NOT NULL
			);";

        $this->query($query);
    }

    /**
     * Méthode utilisée pour initialiser les attributs d'un dossier avant de l'ajouter en bd
     *
     * @param $iFolder Identifiant du dossier
     * @param $sPath Chemin du dossier
     * @param $sName Nom du dossier
     * @param $sAccess Permet de savoir si le répertoire est ouvert à tous (ALL) ou géolocalisé (code pays : CH,FR,BE)
     * @param $sToken Chaîne crypté pour identifier un dossier
     *
     * @return int Retourne l'id de la nouvelle ligne en base
     */
    public function add_folder($iFolder, $sPath, $sName, $sAccess, $sToken)
    {
        $this->iFolder = $iFolder;
        $this->sPath = $sPath;
        $this->sName = $sName;
        $this->sAccess = $sAccess;
        $this->sToken = $sToken;
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