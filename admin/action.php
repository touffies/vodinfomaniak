<?php 
if(isset($action))
{

	// Sauvegarde du formulaire de configuration
	if ($action == "configuration_save") {

        $vod_config = new vodInfomaniak_config();

        // Sauvegarde
        foreach($_REQUEST['valeur'] as $nom => $valeur) {
            if($nom == 'vodinfomaniak_pwd'){
                $vod_config->ecrire_pwd($nom, $valeur);
            } else {
                $vod_config->ecrire($nom, $valeur);
            }
		}

        // On vérifie la connection
        $vod_config->check_connection();

        // Si on est connecté, on peut retourner sur la page par défaut
        if($vod_config->is_connected()){
            unset($_REQUEST['redirect']);
        }

	}
    elseif ($action == "configuration_update"){

        // On met à jour les tables
        $vod_config = new vodInfomaniak_config();
        $vod_config->check_connection();

    } elseif ($action == "folder_save"){

        $folder = trim(lireParam("select_folder", "int"));
        Variable::ecrire('vodinfomaniak_folder', $folder, true);

        // On remet à jour les tables
        $vod_config = new vodInfomaniak_config();
        $vod_config->check_connection();

    } elseif ($action == "player_save"){

        $player = trim(lireParam("select_player", "int"));
        Variable::ecrire('vodinfomaniak_player', $player, true);

    }

    // Redirection
	redirige($_REQUEST['redirect'] ? $_REQUEST['redirect'] : "module.php?nom=vodinfomaniak");
}
?>