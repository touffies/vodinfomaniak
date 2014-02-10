PLUGIN DE LIVRAISON POUR THELIA 1.5
--------------------------------------

Ce plugin vous permet de vendre des produits dématérialisés de type vidéo
en associant celles-ci à vos produits. Les vidéos seront toutes hébergées par
[Infomaniak][1] et vous pouvez synchroniser votre compte depuis l'administration.

**IMPORTANT :** Ce plugin utilise le plugin [LIVRAISON_ZERO][2] afin d'annuler les frais de livraison pour
les produits de type "vidéo".


> **Auteur**
>
>   Christophe LAFFONT - Openstudio / [www.openstudio.fr][3]


INSTALLATION
---------

Il est nécessaire pour utiliser ce plugin d'avoir un compte VOD dans votre interface d'administration [http://statslive.infomaniak.ch/][4]
Si vous souhaitez obtenir plus d'informations sur la solution d'hébergement vidéo d'Infomaniak, veuillez vous rendre à l'adresse [http://streaming.infomaniak.com/stockage-video-en-ligne][1]

Pour installer ce plugin, il vous faut :

 1. Installer le plugin `vodinfomaniak` dans le dossier `/client/plugins/` de votre site.
 2. Activer ce plugin dans le menu `Configuration -> Activation des plugins`.
 3. Se rendre dans `Modules -> VOD Infomaniak Network -> Connection au site` afin de configurer votre compte avec les identifiants fournis sur l'interface d'administration (Login, Mot de passe et Identifiant VOD).


LES MESSAGES
---------

Vodinfomanial crée un message `Mail de confirmation VOD Infomaniak`, envoyé a vos
clients lorsque leut commande est validée et payée. Les substitutions proposées
dans ce message sont les suivantes :

```
__COMMANDE_REF__, __COMMANDE_DATE__, __COMMANDE_HEURE__
__NOMSITE__, __URLSITE__
__CLIENT_REF__, __CLIENT_RAISON__, __CLIENT_FACTNOM__, __CLIENT_FACTPRENOM__,
__CLIENT_EMAIL__
```

Entre `<VENTEVOD>` et `</VENTEVOD>`, les substitutions spécifiques sont disponibles :

```
__VOD_TITRE__ : le titre du produit contenant le fichier vidéo
__VOD_DATEDEBUT__ : la date de début de location
__VOD_DATEFIN__ : la date de fin de la location
```

LES BOUCLES
---------

Le plugin propose ***trois boucles***. Ces boucles sont accessibles de la façon suivante:

```
<THELIA_<nomboucle> type="VODINFOMANIAK" boucle="<nom_boucle>" paramètres....>
```
Le paramètre "boucle" permet de désigner la boucle a exécuter.

1) **Boucle transport**

Cette boucle doit être utilisée autour de la boucle TRANSPORT de Thelia. Elle
permet de filtrer le mode de livraison proposé au client.

 - si le panier ne comporte que des produits dématérialisés de type vidéo, alors le mode de livraison "N/A" sera uniquement proposé.
 - Sinon les modes de transports traditionels seront proposés.

**Paramètres:**

   Aucun

**Variables:**
```
#VOD_EXCLUSION  : Nom des modes de livraison à exclure
```
Exemple d'utilisation :

```
<div class="choixDeLaLivraison">
    <ul>
        <THELIA_vodtransport type="VODINFOMANIAK" boucle="transport">
            <THELIA_transport type="TRANSPORT" exclusion="#VOD_EXCLUSION">
                <li><a href="#URLCMD"><span class="modeDeLivraison">#TITRE / #PORT €</span><span class="choisir"></span></a></li>
            </THELIA_transport>
        </THELIA_vodtransport>
    </ul>
</div>
```

2) **Boucle commande**

Cette boucle permet d'afficher sur la page `moncompte` du client, toutes les vidéos en cours de location (Sachant que par defaut, une location est d'une durée de 7 jours).

**Paramètres:**

 - client   : identifiant du client
 - commande : identifiant de la commande

**Variables:**
```
#VOD_COMMANDE_ID  : ID de la commande
#VOD_TITRE        : titre du produit associé à la vidéo
#VOD_NOM          : nom de la vidéo
#VOD_DATEDEBUT    : date de la location (d/m/Y)
#VOD_DATEFIN      : date de fin de la location (d/m/Y)
#VOD_URL          : URL de la page pour visualiser la video
```

Exemples d'utilisation, pour afficher la liste des vidéos en location sur la page `moncompte.html` :

```
<T_vod>
    <div id="vod" class="grid_12">
        <h3>Films en VOD en cours de location</h3>
        <table id="table-vod" class="table-default">
            <thead>
            <tr>
                <th>Titre</th>
                <th>Date</th>
                <th>Voir</th>
            </tr>
            </thead>
            <tbody>
            <THELIA_vod type="VODINFOMANIAK" boucle="commande" client="#CLIENT_ID">
                <tr>
                    <td class="ligne">#VOD_TITRE</td>
                    <td class="ligne">disponible jusqu'au #VOD_DATEFIN</td>
                    <td class="ligne"><a href="#VOD_URL">Voir la vidéo</a></td>
                </tr>
            </THELIA_vod>
            </tbody>
        </table>
    </div>
</T_vod>
<//T_vod>
```

3) **Boucle Player**

Cette boucle permet d'utiliser le player que vous aurez configurer dans votre espace Infomaniak.

**Paramètres:**

 - player : identifiant du player
 - video  : identifiant d"une video

**Variables:**

```
#VOD_URL         : chemin complet de la video avec extension et cle de sécurité (Si nécessaire)
#VOD_WIDTH       : largeur du player
#VOD_HEIGHT      : hauteur du player
#VOD_PLAYER      : identifiant du player
#VOD_CODESERVICE : identifiant du compte vod
#VOD_IMAGE       : thumbnail
```

Exemple d'utilisation, pour visualiser une vidéo sur la page `player.html` :

```
<T_player>
    <THELIA_player type="VODINFOMANIAK" boucle="player">
        <iframe frameborder="0" width="#VOD_WIDTH" height="#VOD_HEIGHT" src="http://vod.infomaniak.com/iframe.php?url=#VOD_URL&player=#VOD_PLAYER&vod=#VOD_CODESERVICE&preloadImage=#VOD_IMAGE"></iframe>
    </THELIA_player>
</T_player>
```

QUESTIONS FREQUENTES
---------

**Est-ce qu'il faut que je fournisse mes identifiants personnels au plugin ?**

Cela fonctionne, mais pour des raisons de sécurités, il est fortement déconseillé de le faire.
Il est nettement plus prudent dans votre interface d'administration VOD de créer un nouvel utilisateur et de ne lui attribuer que les droits "Gestion API".
En cas de problème, il sera bien plus aisé de supprimer l'utilisateur ou de changer son mot de passe que de compromettre tous ses services.


**J'ai ajouté de nouvelles vidéos, mais elles n'apparaissent pas dans la liste des vidéos du site**

Le plugin est prévu pour se synchroniser régulièrement avec votre compte afin de récupérer les dernières modifications automatiquement.

Il peut cependant arriver un problème avec l'adresse de callback. C'est une adresse qu'utilise Infomaniak pour prévenir votre site qu'une nouvelle vidéo est disponible.

Cette adresse doit donc être joignable de façon publique. (Pour plus d'informations, se reporter à la page `Gestion VOD > Configuration`)

Vous pouvez lancer une synchronisation, manuellement, en cliquant sur le bouton `Synchroniser mom compte` qui se trouve dans `Modules -> VOD Infomaniak Network`.


----------

CHANGELOG
---------

- **1.0.1** (10/02/2014) - Ajout du fichier Readme.md (Markdown)
- **1.0.0** (29/01/2014) - Première version du plugin


@TODO
---------

* Améliorer la page Callback.php pour mieux gérer individuellement les modifications
* Revoir toutes les méthodes Destroy
* Sérialiser les paramètres de configuration du plugin dans la table variable
* Ajouter une fonctionnalité de téléchargement de vidéo (via un formulaire HTML ou par ftp)
* Permettre à l'administrateur de renommer ou supprimer une vidéo



[1]: http://streaming.infomaniak.com/stockage-video-en-ligne
[2]: https://github.com/touffies/livraison_zero
[3]: http://www.openstudio.fr
[4]: http://statslive.infomaniak.ch/
