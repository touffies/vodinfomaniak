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

// Limitation d'upload via PHP
//define("CONST_VODINFOMANIAK_UPLOAD_FILE_MAX", 300);

// Nombre de vidéo maximum que l'API doit retourner
define("VODINFOMANIAK_VIDEO_MAX", 200);

// Dossier sécurisé uniquement (0 : tous les dossiers ou 1 : uniquement les dossiers sécurisés)
define("VODINFOMANIAK_STOKEN", 1);

// Limitation de temps (7 jours)
define("VODINFOMANIAK_TIME_MAX", 7);

// Lien pour visualiser les vidéos (Variables possible : __SKEY__, __STOKEN__, __SVIDEONAME__, __SUSERIP__)
define("VODINFOMANIAK_URL", "?fond=player&key=__SKEY__&name=__SVIDEONAME__");

// Nom du module à utiliser si on veut avoir aucun frais de livraison
define("VODINFOMANIAK_LIVRAISON_ZERO", "livraison_zero");

?>
