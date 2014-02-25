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

include_once __DIR__ . "/../../../../classes/Variable.class.php";

/**
 * Class Vodinfomaniak_config
 *
 * Cette classe permet de sauvegarder et de tester la connection au site VOD Infomaniak
 */
class Vodinfomaniak_config extends Variable {

    const SALT = "Vodinfomaniak_config";

    private $connected;
    private $folder;
    private $player;

    function __construct(){

        parent::__construct();

        $this->connected = $this->lire('vodinfomaniak_connected');
        $this->player = $this->lire('vodinfomaniak_player');
    }

    /**
     * Initialisation des variables nécessaires pour se connecter au site VOD d'Infomaniak et
     * de la variable d'état de la connexion
     *
     * @return  none
     */
    function init() {
        // Variable de connection au compte VOD d'Infomaniak
        foreach (array('vodinfomaniak_id', 'vodinfomaniak_login', 'vodinfomaniak_pwd') as $nom)
		{
            // Si la variable n'existe pas encore
            if(! $this->charger($nom))
                $this->ecrire($nom, '', true);
		}

        // Statut de la connection
        if(! $this->charger(vodinfomaniak_connected)){
            $this->connected = 0;
            $this->ecrire('vodinfomaniak_connected', $this->connected, true);
        }
        // Callback
        if(! $this->charger(vodinfomaniak_callback_key)){
            // On génère une nouvelle clé
            $callback_key = sha1(time() * rand());
            $this->ecrire('vodinfomaniak_callback_key', $callback_key, true);
        }
    }

    /**
     * Méthode utilisée pour retourner l'état de la connexion
     *
     * @return boolean
     * - 1 : On a réussi à se connecter
     * - 0 : Les identifiants de connexion ne sont pas bon
     */
    function is_connected()
    {
        return $this->connected;
    }

    /**
     * Méthode utilisée pour tester la connection et pour mettre à jour les tables de liaison (Video et Folder) si
     * la connection est bonne.
     *
     * @return none
     */
    function check_connection()
    {
        $this->connected = 0;

        try {
            // On décode le mot de passe
            $vodinfomaniak_pwd = $this->decrypt($this->lire('vodinfomaniak_pwd'));

            // On crée une instance VOD pour utiliser l'API
            $vod_api = new vod_api($this->lire('vodinfomaniak_login'), $vodinfomaniak_pwd, $this->lire('vodinfomaniak_id'));

            // On vérifie la connection
            if($vod_api->ping())
            {
                $this->connected = 1;
                $this->ecrire('vodinfomaniak_icodeservice', $vod_api->getServiceItemID(), true);
                $lastUpdate = $this->lire('vodinfomaniak_lastUpdate');

                // Player
                if ($vod_api->playerModifiedSince($lastUpdate))
                {
                    // On vide la table pour les players
                    $vod_player = new vodInfomaniak_player();
                    $vod_player->purge();

                    // On met à jour la liste des players
                    $aListPlayer = $vod_api->getPlayers();
                    if (!empty($aListPlayer))
                    {
                        foreach ($aListPlayer as $oPlayer) {
                            $vod_player->add_player($oPlayer['iPlayerCode'], $oPlayer['sName'], $oPlayer['iWidth'], $oPlayer['iHeight'], $oPlayer['bAutoStart'], $oPlayer['bLoop'], $oPlayer['bSwitchQuality'], $oPlayer['dEdit']);
                        }
                    }
                }

                // Folder
                $vod_folder = new vodInfomaniak_folder();
                $vod_folder->purge();

                // On met à jour la liste des dossiers
                $aListFolder = $vod_api->getFolders();

                $folderListID = array();
                if (!empty($aListFolder)) {
                    foreach ($aListFolder as $oFolder) {

                        // On veut uniquement des dossiers avec une restriction par clé
                        if(!(defined('VODINFOMANIAK_STOKEN') && VODINFOMANIAK_STOKEN == 1 && empty($oFolder['sToken']))) {
                            $vod_folder->add_folder($oFolder['iFolderCode'], $oFolder['sFolderPath'], $oFolder['sFolderName'], $oFolder['sAccess'], $oFolder['sToken']);
                            $folderListID[] = $oFolder['iFolderCode'];
                        }
                    }
                }

                // On recherche tous les dossiers autorisés
                if(intval($this->folder) > 0){
                    $folderListID = array(); // On reconstruit le tableau
                    $vod_folder->charger($this->folder);
                    $folderList = $vod_folder->query_liste("SELECT * FROM $vod_folder->table WHERE sPath LIKE '%".$vod_folder->sPath."%' ORDER BY sPath ASC");
                    foreach ($folderList as $folder) {
                        $folderListID[] = $folder->iFolder;
                    }
                }

                // Video
                $vod_video = new vodInfomaniak_video();
                $vod_video->purge();

                // On met à jour la liste des vidéos
                $iLimit = defined('VODINFOMANIAK_VIDEO_MAX') ? VODINFOMANIAK_VIDEO_MAX : 200;
                $aVideos = $vod_api->getLastVideo($iLimit, 0);
                foreach ($aVideos as $oVideo) {
                    // On filtre les vidéos
                    if(count($folderListID) > 0) {
                        if(in_array($oVideo['iFolder'], $folderListID)) {
                            $vod_video->add_video($oVideo['iFileCode'], $oVideo['iFolder'], $oVideo['sFileName'], $oVideo['aEncodes'][0]['sPath'], $oVideo['sFileServerCode'], $oVideo['aEncodes'][0]['eConteneur'], $oVideo['fFileDuration'], $oVideo['dFileUpload']);
                        }
                    }
                }

                // On gère la différence de temps entre les serveurs
                $serveurTime = $vod_api->time();
                $localTime = time();
                $diff = ($serveurTime - $localTime);
                $this->ecrire('vodinfomaniak_serverTime', $diff, true);
                $this->ecrire('vodinfomaniak_lastUpdate', time(), true);

                // On va essayer d'ajouter une URL de Callback
                try {
                    // Url du site
                    $urlsite = $this->lire('urlsite');
                    $vodinfomaniak_callback_key = $this->lire('vodinfomaniak_callback_key');

                    // On vérifie que la clé existe
                    if(!$vodinfomaniak_callback_key) {
                        $vod_api->setCallbackV2('');
                        throw new Exception("Impossible de charger la clé de sécurité pour le callback.");
                    }

                    // On charge tous les objets callbacks
                    $sUrl = $vod_api->getCallbackV2();

                    // On vérifie si on a déja créé le lien de callback pour ce site
                    foreach ($sUrl as $oCallback) {
                        if (strpos($oCallback['sUrl'], $urlsite) !== false) {
                            throw new Exception("Le lien de callback existe déjà !");
                        }
                    }

                    // Let's go, on envoie le lien de callback
                    $vod_api->setCallbackV2(rtrim($urlsite, '/') . "/client/plugins/vodinfomaniak/callback.php?key=" . $this->lire('vodinfomaniak_callback_key'));

                    } catch (Exception $oException) {
                        // Oups, il faudra faire les mises à jour manuellement
                    }


            }
        } catch (Exception $oException) {
            $this->connected = 0;
        }

        // On sauvegarde l'état de la connection
        $this->ecrire('vodinfomaniak_connected', $this->connected);
    }

    /**
     * Méthode utilisée pour rechercher la valeur d'un mot de passe
     *
     * @param string $nom Nom de la variable en base de donnée
     * @param string $defaut Valeur par defaut
     *
     * @return string Retourne le mot de passe décrypté ou ""
     */
    function lire_pwd($nom, $defaut = "")
    {
        $pwd = $this->lire($nom, $defaut);
        return $pwd ? $this->decrypt($pwd) : $pwd;
    }

    /**
     * Méthode utilisée pour sauvegarder le mot de passe
     *
     * @param string $nom Nom de la variable en base de donnée
     * @param string $valeur Valeur a sauvegarder
     *
     * @return none
     */
    function ecrire_pwd($nom, $valeur)
    {
        $valeur = $valeur ? $this->encrypt($valeur) : $valeur;
        $this->ecrire($nom, $valeur);
    }

    /**
     * Méthode utilisée pour encrypter une chaîne de caractères
     *
     * @param string $text Chaîne de caractères
     *
     * @return string Retourne la chaîne de caratères crypté
     */
    function encrypt($text) {
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

    /**
     * Méthode utilisée pour décrypter une chaîne de caractères
     *
     * @param string $text Chaîne de caractères crypté
     *
     * @return string Retourne la chaîne de caratères décrypté
     */
    function decrypt($text) {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SALT, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }


    /**
     * Méthode appelée quand on désactive le plugin
     *
     * @return none
     */
    public function destroy() {
        // On supprime certaines variables
        foreach(array('vodinfomaniak_serverTime', 'vodinfomaniak_lastUpdate') as $nom)
        {
            if($this->charger($nom))
            {
                $this->delete();
            };
        }
    }

}
?>