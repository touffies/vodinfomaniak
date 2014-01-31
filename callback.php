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

include_once __DIR__ . "/Vodinfomaniak.class.php";

/**
 *
 * Fichier de callback utilisé pour synchroniser le compte VOD Infomaniak avec ce plugin.
 * Cela permet d'avoir immédiatement accès aux vidéos qui viennent d'être envoyées ou supprimées de l'espace VOD.
 *
 */

// Vérifier authorisation
if (Variable::lire('vodinfomaniak_callback_key') == trim(lireParam("key", "string")))
{
    // On met a jour les tables de liaison
    $vod_config = new Vodinfomaniak_config();
    $vod_config->check_connection();
}

die('callback');
?>
