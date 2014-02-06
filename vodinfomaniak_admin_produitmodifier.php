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

// Vérifier authorisation
include_once __DIR__ . "/../../../fonctions/authplugins.php";
autorisation("vodinfomaniak");
?>

<?php
// On vérifie qu'on a bien une référence produit
$ref = trim(lireParam("ref", "string"));

if( $ref != "")
{
    // On charge les infos du produit
    $prod = new Produit();
    $prod->charger($ref);

    // On vérifie si une vidéo est associée au produit
    $vodinfomaniak = new Vodinfomaniak();
    $vodinfomaniak->charger_produit($prod->id);
?>

<!-- début du bloc de vidéo VOD Infomaniak -->
<a name="vodinfomaniak"></a>

<div class="entete">
    <div class="titre" style="cursor:pointer" onclick="$('#pliantvodinfomaniak').show('slow');"><?php echo trad('VOD Infomaniak', 'vodinfomaniak'); ?></div>
</div>
<div id="pliantvodinfomaniak" class="blocs_pliants_prod">
    <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: separate;margin: 0;">
        <tbody>
            <tr class="claire" style="height: auto;">
                <td class="designation"><?php echo trad('Vidéo', 'vodinfomaniak'); ?><br> <span class="note"><?php echo trad('Associer une vidéo à ce produit.', 'vodinfomaniak'); ?></span></td>
                <td>
                    <select name="select_video" id="select_video" class="form_long">
                        <option value=""><?php echo trad('Séléctionner une vidéo', 'vodinfomaniak'); ?></option>
                        <?php
                        $vodinfomaniak_video = new vodinfomaniak_video();
                        $videos = $vodinfomaniak_video->query_liste("SELECT * FROM $vodinfomaniak_video->table ORDER BY dUpload DESC");
                        $selected_found = false;
                        foreach($videos as $video)
                        {
                            if ($vodinfomaniak->iVideo == $video->iVideo){
                                $selected_found = true;
                                $selected = ' selected="selected"';
                            }else{
                                $selected = '';
                            }

                            echo '<option value="'. $video->iVideo .'"'.$selected.'>'.$video->sName.'</option>';
                        }
                        ?>
                    </select>
                    <?php
                    // Afficher un message si le produit est associé à une vidéo qui n'existe plus
                    if(intval($vodinfomaniak->iVideo) > 0 && !$selected_found){
                        echo trad('<small style="color:red">Attention : ce produit est associé à une vidéo qui n\'existe plus.</small>');
                    }
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="bloc_fleche" style="cursor:pointer" onclick="$('#pliantvodinfomaniak').hide();"><img src="gfx/fleche_accordeon_up.gif" /></div>
</div>
<!-- fin du bloc de vidéo VOD Infomaniak -->
<?php } ?>