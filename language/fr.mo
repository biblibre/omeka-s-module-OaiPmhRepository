��    M      �  g   �      �     �  $   �  $   �  �   �  �   z     �  -        2  	   ;     E     e  F   r  j   �  '   $	     L	      ^	     	     �	  ?   �	     �	  8   �	  e   8
  >   �
  
   �
  }   �
     f     u     �     �  %   �     �        !   "     D  $   Y     ~     �  I   �  H   �     -     @  R   O     �     �     �     �  [   �  F   A  |   �  S     �   Y  #   �  $     &   ,  ;   S  J   �  W   �     2  y   P  S   �  3     "   R  %   u  5   �  ?   �  M        _     u  l   �  5   �  .   -  R   \     �     �     �     �  #       (  4   <  5   q  �   �  �   @     �  3   �            &   ,     S  p   g  ~   �  L   W     �  .   �     �       @        Q  I   q  q   �  K   -     y  �   �          (  )   B  (   l  6   �  )   �  3   �     *      J  *   k     �     �  \   �  W        p     �  f   �               ,     @  p   T  W   �  �      Z   �   �   !  +   �!  *   �!  -   "  9   <"  Y   v"  `   �"  !   1#  �   S#  m   �#  @   \$  $   �$  "   �$  ?   �$  =   %%  n   c%      �%     �%  ~   &  F   �&  9   �&  \   '  .   i'  (   �'     �'     �'        F   )   I   "                 A   D            2         ;          E         #           G      H   &              <   :   9                  /      0      (       6   7       %   K       J          +   ?      @             4   .       B   M   !                 >   $       '       5              *       ,       -   L      C          
   	   1          3      =          8    Absolute site url Add identifier for global repository Add identifier for site repositories An alias (redirect 301) for backward compatibility with Omeka Classic, that used "/oai-pmh-repository/request", or any other old OAI-PMH repository. An identifier may be added to simplify harvests, in particular when there is no unique identifier (ark, noid, call number, etc.). Api url Date/time arguments of differing granularity. Disabled Disabled. Duplicate arguments in request. Expose media For other merges, the event "oaipmhrepository.values.pre" can be used. Futhermore, a new option allows to map any term to any other term, so any values can be exposed if needed. Genericize dcterms for specific formats Global repository Global repository redirect route Hide empty oai sets Human interface In minutes, the length of time a resumption token is valid for. Invalid date/time argument. It allows to expose all metadata in the standard oai_dc. Larger values will increase memory usage but reduce the number of database queries and HTTP requests. Larger values will make the tokens table grow somewhat larger. List limit Map any property to any other property, so they will be available in other formats, in particular "oai_dcterms" and "oai_dc". Map properties Metadata formats Mets: data format for item Mets: data format for media Missing metadata format configuration Missing required argument %s. Missing set format configuration Name for this OAI-PMH repository. Namespace identifier No records match the given criteria. No site. No verb specified. Note that the oai set identifiers are different (item set id or site id). Number of individual records that can be returned in a response at once. OAI-PMH Repository Oai set format Only one identifier may be added and it can be the api url or a site specific url. Relative site url Repository name Select formats Site repositories Smaller values will reduce memory usage but increase the number of DB queries and requests. Some formats add their own identifier and other ones skip this option. The OAI-PMH pages can be displayed with a themable responsive human interface based on Bootstrap (https://getbootstrap.com). The OAI-PMH protocol version 2.0 supports only "GET" and "POST" requests, not "%s". The event "oaipmhrepository.values" that may be used by other modules was deprecated and replaced by event "oaipmhrepository.values.pre". The format of the metadata of item. The format of the metadata of media. The format of the oai set identifiers. The format that will be made available. oai_dc is required. The global repository contains all the resources of Omeka S, in one place. The module "%s" was automatically deactivated because the dependencies are unavailable. The set "%s" doesn’t exist. The site repositories simulate multiple oai servers, with the site pools of items and the attached item sets as oai sets. This means harvesters can re-try old partial list requests for this amount of time. This module has resources that connot be installed. This module requires modules "%s". This module requires the module "%s". This provides harvesters with direct access to files. This value is required to be a domain name you have registered. This will be used to form globally unique IDs for the exposed metadata items. Token expiration time Unknown argument %s. Use refined terms for Dublin Core elements, for example dcterms:abstract will be merged with dc:description. Using other values will generate invalid identifiers. Whether the module should hide empty oai sets. Whether the plugin should include identifiers for the files associated with items. With item sets as oai sets With sites as oai sets Without oai sets Without oai sets. Project-Id-Version: 
Report-Msgid-Bugs-To: 
PO-Revision-Date: 2020-12-07 00:00+0000
Language: fr
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Last-Translator: Daniel Berthereau <Daniel.fr@Berthereau.net>
Language-Team: 
X-Generator: Poedit 2.4.2
 Url absolue du site Ajouter l’identifiant pour l’entrepôt général Ajouter l’identifiant pour les entrepôts des sites Un alias (redirect 301) pour compatibilité ascendante avec Omeka Classic, qui utilisait "/oai-pmh-repository/request", ou tout autre entrepôt OAI-PMH. Un identifiant peut être ajouté pour simplier les moissonages, notamment quand il n’y a pas d’identifiant unique (ark, noid, cote, etc.). Url de l’api Arguments date / heure de granularité différente. Désactivé Désactivé. Arguments en doublon dans la requête. Exposer les médias Pour les autres correspondances, l’événement "oaipmhrepository.values.pre" can be used" peut être utilisé. De plus, une nouvelle option permet de faire correspondre n'importe quels termes afin d'exposer toutes les valeurs, si besoin. Convertir le Dublin Core Terms en Dublin Core Elements pour certains formats Entrepôt général Route spécifique pour l’entrepôt général Cacher les ensembles vides Interface web En minutes, la durée de validité d’un jeton de continuation. Argument date / heure invalide. Cela permet d’exposer toutes les métadonnées dans le standard oai_dc. Les valeurs plus importantes utiliseront plus de mémoire mais réduiront les requêtes à la base et au réseau. Les valeurs plus importantes augmenteront la taille de la table des jetons. Limite de la liste Correspondance entre toutes propriétés afin de les rendre disponibles dans tous les formats, notamment "oai_dcterms" et "oa_dc". Propriétés de la carte Formats des métadonnées Mets : format des données des contenus Mets : format des données des médias Il manque la configuration du format des métadonnées Il manque un argument obligatoire : %s. Il manque la configuation du format de l’ensemble Nom de cette entrepôt OAI-PMH. Identifiant de l’espace de nom Aucune notice ne correspond aux arguments. Aucun site. Pas de verbe spécifié. Noter que les identifiants des ensembles oai sont différents (id de collection ou de site). Nombre de notices individuelles qui peuvent être renvoyées dans une page de réponse. Entrepôt OAI-PMH Format des ensembles Oai Un seul identifiant peut être ajouté, soit l’url de l’api, soit l’url d’un site spécifique. Url relative du site Nom de l’entrepôt Choisir les formats Entrepôts par site Les valeurs plus petites utiliseront moins de mémoire mais augmenteront les requêtes à la base et au réseau. Certains formats ajoutent leurs propres identifiants et d’autres ignorent l’option. Les pages OAI-PMH peuvent être affichées avec un thème adaptatif lisible par les personnes, basé sur Bootstrap (https://getbootstrap.com). Le protocole OAI-PMH version 2.0 gère uniquement les requêtes "GET" et "POST", pas "%s". L’événement "oaipmhrepository.values" qui pouvait être utilisé par d'autres modules est obsolète et a été remplacé par l’événement "oaipmhrepository.values.pre". Le format des métadonnées d’un contenu. Le format des métadonnées d’un média. Le format des identifiants des ensembles oai. Format qui sera rendu disponible. oai_dc est obligatoire. L’entrepôt général contient toutes les ressources d’Omeka S, dans une seule url. Le module %s" a été automatiquement désactivé car ses dépendances ne sont plus disponibles. L’ensemble "%s" n’existe pas. Les entrepôts des sites simulent plusieurs serveurs OAI, avec le réservoir de contenus du site et les collections attachées en tant qu’ensembles oai. Cela signfie que les moissonneurs peuvent réessayer les requêtes de listes partielles pendant cette durée. Ce module a des ressources qui ne peuvent pas être installées. Ce module requiert les modules "%s". Ce module requiert le module "%s". Cela donne un accès direct aux fichiers pour les moissonneurs. Cette valeur doit être un nom de domaine que vous possédez. Elle sera utilisée pour former l’identifiant général unique pour les métadonnées des contenus exposés. Durée avant expiration du jeton Argument inconnu : %s. Utiliser les termes raffinés pour le Dublin Core Éléments, par exemple dcterms:abstract sera fusionné dans dc:description. Utiliser les autres valeurs pour générer des identifiants invalides. Indique si le module doit cacher les ensembles oai vides. Indique si le module doit inclure les identifiants pour les fichiers associés aux contenus. Avec les collections en tant que ensembles oai Avec les sites en tant que ensembles oai Sans les ensembles oai Sans les ensembles oai. 