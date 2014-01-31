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
 * Class Vodinfomaniak_video
 *
 * Cette classe permet de sauvegarder et gérer les vidéos existant sur le compte VOD Infomaniak
 */
class Vodinfomaniak_video extends Baseobj {

	public $iVideo;         // iFileCode
	public $iFolder;        // iFolder
	public $sName;          // sFileName
    public $sPath;          // sPath
    public $sServerCode;    // sFileServerCode
    public $sExtension;     // eConteneur
	public $iDuration;      // fFileDuration
    public $dUpload;        // dFileUpluod

	const TABLE = "vodinfomaniak_video";

	public  $table = self::TABLE;

	public $bddvars = array("iVideo", "iFolder", "sName", "sPath", "sServerCode", "sExtension", "iDuration", "dUpload");

    /**
     * Constructeur
     *
     * @param int/null $iVideo Possibilité de charger une vidéo en passant son identifiant
     */
    function __construct($iVideo = null) {

		parent::__construct();

		if (intval($iVideo) > 0) $this->charger($iVideo);
	}

    /**
     * Méthode utilisée pour chercher un objet de type Vodinfomaniak_video en fonction de son identifiant
     *
     * @param $iVideo Identifiant de la vidéo
     *
     * @return object Retourne un object de type Vodinfomaniak_video
     */
    public function charger($iVideo){
		return $this->getVars("SELECT * FROM $this->table WHERE iVideo=" . intval($iVideo));
	}

    /**
     * Méthode utilisée pour chercher un objet de type Vodinfomaniak_video en fonction du champ sServerCode
     *
     * @param $sServerCode Nom du fichier video permettant de l'identifier sur le serveur
     *
     * @return object Retourne un object de type Vodinfomaniak_video
     */
    public function charger_servercode($sServerCode){
        return $this->getVars("SELECT * FROM $this->table WHERE sServerCode=\"$sServerCode\"");
    }

    /**
     * Initialisation du plugin, création de la table si elle n'existe pas encore
     *
     * @return  none
     */
    public function init() {

        $query = "CREATE TABLE IF NOT EXISTS `$this->table` (
			 `iVideo` INT UNSIGNED NOT NULL,
			 `iFolder` INT UNSIGNED NOT NULL ,
		 	 `sName` TEXT NOT NULL,
		 	 `sPath` TEXT NOT NULL,
		 	 `sServerCode` TEXT NOT NULL,
		 	 `sExtension` VARCHAR(4) NOT NULL,
		  	 `iDuration` INT UNSIGNED NOT NULL,
		  	 `dUpload` DATETIME NOT NULL
			);";

        $this->query($query);
    }

    /**
     * Méthode utilisée pour initialiser les attributs d'une vidéo avant de l'ajouter en bd
     *
     * @param $iVideo Identifiant de la video
     * @param $iFolder Identifiant du dossier contenant cette vidéo
     * @param $sName Titre de la video
     * @param $sPath
     * @param $sServerCode Nom du fichier video permettant de l'identifier sur le serveur
     * @param $sExtenstion Conteneur utilisé pour cet encodage (flv, mp4, ...)
     * @param $iDuration Durée de la vidéo en seconde
     * @param $dUpload Date de téléchargement de la vidéo dans l'espace VOD d'Infomaniak (format %Y-%m-%d)
     *
     * @return int Retourne l'id de la nouvelle ligne en base
     */
    public function add_video($iVideo, $iFolder, $sName, $sPath, $sServerCode, $sExtension, $iDuration, $dUpload)
    {
        $this->iVideo = $iVideo;
        $this->iFolder = $iFolder;
        $this->sName = $sName;
        $this->sPath = $sPath;
        $this->sServerCode = $sServerCode;
        $this->sExtension = $sExtension;
        $this->iDuration = $iDuration;
        $this->dUpload = $dUpload;
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