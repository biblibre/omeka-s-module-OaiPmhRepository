��    p      �  �         p	  �   q	  �   -
     �
  $   �
  $   �
  �        �  �   �     ^  -   f     �  	   �  4   �  2   �  .     4   >  6   s     �  -   �     �  �        �     �  j   �  '   @     h      z     �     �     �     �     �     �  �   �     �  i   �  "   M     p  
   �     �  
   �  +   �  
   �  }   �     W     f  Q   {     �     �     �  %        ;      Y  !   z     �  $   �     �     �  
  �     �       	        )     =  (   O     x     �     �     �     �     �  �   �     �  |   �  S     f   q  �   �  #   b  $   �  &   �  ;   �  �     W   �  >   �     :  y   X  \   �  3   /  "   c  >   �  %   �  �   �  (   �     �     �  "     "   &     I  7   R  �   �  .   w  �   �  -   /   "   ]   *   �      �      �       �      �      !  _   !!  #  �!  �   �"  �   {#     :$  4   N$  5   �$  �   �$  M  R%  �   �&     C'  1   R'     �'     �'  >   �'  4   �'  >   (  :   P(  ?   �(  &   �(  >   �(     1)  �   E)     �)     *  ~   %*  3   �*     �*  .   �*     +     1+     6+     D+  $   G+     l+  �   ~+     P,  �   n,  *   -     ,-     ?-     M-     S-  2   f-  	   �-  �   �-     &.     F.  �   b.     �.  )   �.  (   )/  6   R/  %   �/  #   �/     �/      �/  4   0     I0     U0  $  n0     �1     �1  	   �1     �1     �1  -   �1  &   2     A2     V2     k2     2     �2  �   �2     v3  �   }3  Z   4  z   g4  �   �4  +   �5  *   �5  (   �5  9   6  �   L6  a   �6  H   `7     �7  �   �7  l   Y8  @   �8  $   9  G   ,9  "   t9  �   �9  -   d:      �:     �:  (   �:  *   �:     ;  M   *;  )  x;  4   �<  �   �<  2   t=  4   �=  ?   �=  )   >  #   F>  '   j>     �>     �>  v   �>           L       e   b   ]           R                 &              C   W      m   @      V   n       /   :   B                       I          d   3      6      G   
   S      $   h       P   i   >   4   +   -      "   g           !          M   ,   .             =   `         0           Z      '   ?           \   9   a   H           l      ;   5   A   7          F      )      o   c       2      X   (   O   U   N   Y   %   *   D   8   E   J       _                  K       #   ^           Q         T   	   <                  p   [   j      f   k   1        A new output metadata format was added, "simple_xml", that contains all the values in a simple xml, not only the dublin core ones. You can disabled it in the %1$sconfig of the module%2$s. A simple mapping of foaf properties to Dublin Core has been added to the default config. It allows to publish, for example, common metadata of people. Absolute site url Add identifier for global repository Add identifier for site repositories An alias (redirect 301) for backward compatibility with Omeka Classic, that used "/oai-pmh-repository/request", or any other old OAI-PMH repository. An identifier may be added to simplify harvests, in particular when there is no unique identifier (ark, noid, call number, etc.). Only one identifier may be added and it can be the api url or a site specific url. Some formats add their own identifier and other ones skip this option. An option was added to append a thumbnail url according to the non-standard %1$srecommandation%2$s of the Bibliothèque nationale de France. Api url Date/time arguments of differing granularity. Disabled Disabled. Dublin Core terms: Add the class as Dublin Core type Dublin Core terms: Append the url of the thumbnail Dublin Core: Add the class as Dublin Core type Dublin Core: Append the url of the thumbnail for BnF Dublin Core: Table to use when option above is "table" Duplicate arguments in request. Dynamic sets based on advanced search queries Expose media For compliance with the non-standard recommandations of the Bibliothèque nationale de France, the url of the main thumbnail may be automatically included to records. Format of linked resources Format of uri Futhermore, a new option allows to map any term to any other term, so any values can be exposed if needed. Genericize dcterms for specific formats Global repository Global repository redirect route Hide empty oai sets Html Human interface Id Identifier (property below) Identifier or id In minutes, the length of time a resumption token is valid for. This means harvesters can re-try old partial list requests for this amount of time. Larger values will make the tokens table grow somewhat larger. Invalid date/time argument. It is now possible to define oai sets with a specific list of item sets or with a list of search queries. Label as text and uri as attribute Label if any else uri Label only Large List limit List of item sets for the global repository Local name Map any property to any other property, so they will be available in other formats, in particular "oai_dcterms" and "oai_dc". Map properties Map via module Table Map via module Table (for example map class to "text", "image", "sound", "video") Metadata formats Mets: data format for item Mets: data format for media Missing metadata format configuration Missing required argument %s. Missing set format configuration Name for this OAI-PMH repository. Namespace identifier No records match the given criteria. No site. No verb specified. Number of individual records that can be returned in a response at once. Larger values will increase memory usage but reduce the number of database queries and HTTP requests. Smaller values will reduce memory usage but increase the number of DB queries and requests. OAI-PMH Repository Oai set format Omeka url Omeka url and title Omeka url as text Omeka url as text and title as attribute Property for linked resources Relative site url Repository name Select formats Select item sets… Site repositories Some new options were added for compliance with non-standard requirements of BnF (Bibliothèque nationale de France): thumbnail, uri without attribute, class as main type. Square The OAI-PMH pages can be displayed with a themable responsive human interface based on Bootstrap (https://getbootstrap.com). The OAI-PMH protocol version 2.0 supports only "GET" and "POST" requests, not "%s". The deprecated event "oaipmhrepository.values" was removed. Use "oaipmhrepository.values.pre" instead. The event "oaipmhrepository.values" that may be used by other modules was deprecated and replaced by event "oaipmhrepository.values.pre". The format of the metadata of item. The format of the metadata of media. The format of the oai set identifiers. The format that will be made available. oai_dc is required. The global repository contains all the resources of Omeka S, in one place. Note that the oai set identifiers are different (item set id or site id). The module "%s" was automatically deactivated because the dependencies are unavailable. The module removed tables "%s" from a previous broken install. The set "%s" doesn’t exist. The site repositories simulate multiple oai servers, with the site pools of items and the attached item sets as oai sets. This module cannot install its tables, because they exist already. Try to remove them first. This module has resources that cannot be installed. This module requires modules "%s". This module requires the module "%1$s", version %2$s or above. This module requires the module "%s". This will be used to form globally unique IDs for the exposed metadata items. This value is required to be a domain name you have registered. Using other values will generate invalid identifiers. Title as text and Omeka url as attribute Token expiration time Unknown argument %s. Uri and label separated by a space Uri as text and label as attribute Uri only Uri only as text (BnF compliance: no attribute for uri) Use refined terms for Dublin Core elements, for example dcterms:abstract will be merged with dc:description. It allows to expose all metadata in the standard oai_dc. For other merges, the event "oaipmhrepository.values.pre" can be used. Whether the module should hide empty oai sets. Whether the plugin should include identifiers for the files associated with items. This provides harvesters with direct access to files. With a specific list of item sets as oai sets With dynamic oai sets from queries With dynamic sets defined by queries below With item sets as oai sets With sites as oai sets With the list of item sets below Without oai sets Without oai sets. You can copy the %1$sdefault mapping foaf to dcterms%2$s in the config of the module if needed. Project-Id-Version: 
Report-Msgid-Bugs-To: 
PO-Revision-Date: 2023-07-24 00:00+0000
Last-Translator: Daniel Berthereau <Daniel.fr@Berthereau.net>
Language-Team: 
Language: fr
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Generator: Poedit 2.4.2
 Un nouveau format de métadonnées a été ajouté, « simple_xml ». Il contient toutes les valeurs et pas seulement celles du Dublin Core. Vous pouvez le désactiver dans la %1$s configuration du module%2$s. Un alignement simple entre les propriétés foaf et Dublin Core a été ajouté à la configuration par défaut. Cela permet de publier, par exemple, les métadonnées couramment utilisées. Url absolue du site Ajouter l’identifiant pour l’entrepôt général Ajouter l’identifiant pour les entrepôts des sites Un alias (redirect 301) pour compatibilité ascendante avec Omeka Classic, qui utilisait "/oai-pmh-repository/request", ou tout autre entrepôt OAI-PMH. Un identifiant peut être ajouté pour simplifier les moissonnages, notamment quand il n’y a pas d’identifiant unique (ark, noid, cote, etc.). Seul un identifiant peut être ajouté et ce peut être l’url api ou l’url spécifique du site. Certains formats ajoutent leur propre identifiant et d’autres ignorent cette option. Une option a été ajoutée pour inclure une url vers la vignette, conformément aux %1$srecommandations%2$s non standard de la Bibliothèque nationale de France. Url de l’api Arguments date/heure de granularité différente. Désactivé Désactivé. Dublin Core terms : ajouter la classe comme Dublin Core type Dublin Core terms : ajouter l’url de la vignette Dublin Core : ajouter la classe en tant que type Dublin Core Dublin Core : ajouter l’url de la vignette pour la BnF Dublin Core : Table pour l’option « table » ci-dessus Arguments en doublon dans la requête. Lots dynamiques basés sur des requêtes de recherche avancée Exposer les médias En conformité avec les recommandations non standard de la Bibliothèque nationale de France, l’url de la vignette principale peut être ajoutée automatiquement aux notices. Format des ressources liées Format de l’uri De plus, une nouvelle option permet de faire correspondre n'importe quels termes afin d'exposer toutes les valeurs, si besoin. Générer des dcterms pour des formats spécifiques Entrepôt général Route spécifique pour l’entrepôt général Cacher les lots vides Html Interface web Id Identifiant (propriété ci-dessous) Identifiant ou id La durée de validité d’un jeton de poursuite du moissonnage en minutes. Il permet aux moissonneurs de reprendre un ancien moissonnage. Une grande valeur implique que la table des jetons grossira davantage. Argument date/heure invalide. Il est désormais possible de définir des lots oai avec un liste spécifique de collections ou avec une liste de requêtes de recherche avancée. Libellé comme texte et uri comme attribut Libellé sinon uri Libellé seul Grand Limite de la liste Liste des collections pour l’entrepôt général Nom local Correspondance entre toutes propriétés afin de les rendre disponibles dans tous les formats, notamment "oai_dcterms" et "oa_dc". Correspondance des propriétés Aligner via le module Table Aligner via le module Table (par exemple aligner la classe avec « text », « image », « sound », « video ») Formats des métadonnées Mets : format des données des contenus Mets : format des données des médias Il manque la configuration du format des métadonnées Il manque un argument obligatoire %s. Il manque la configuation du format Nom de cette entrepôt OAI-PMH. Identifiant de l’espace de nom Aucune notice ne correspond aux critères demandés. Aucun site. Pas de verbe spécifié. Le nombre de notices individuelles qui peuvent être retournée en une seule réponse. Une valeur plus grande implique une utilisation plus intensive de la mémoire, mais réduit le requêtes http. Une valeur plus petite réduit l’usage de la mémoire, mais augmente le nombre de requêtes. Entrepôt OAI-PMH Format des lots Oai Url Omeka Url Omeka et titre Url Omeka comme texte Url Omeka comme texte et titre comme attribut Propriété pour les ressources liées Url relative du site Nom de l’entrepôt Choisir les formats Choisir des collections… Entrepôts par site De nouvelles options ont été ajoutées en conformité avec les recommandations non standard de la Bibliothèque nationale de France : vignette, uri sans attribut, classe en tant que type général. Carré Les pages OAI-PMH peuvent être affichées avec un thème adaptatif lisible par les personnes, basé sur Bootstrap (https://getbootstrap.com). Le protocole OAI-PMH version 2.0 gère uniquement les requêtes "GET" et "POST", pas "%s". L’événement obsolète "oaipmhrepository.values" a été supprimé. Utiliser "oaipmhrepository.values.pre" à la place. L’événement "oaipmhrepository.values" qui pouvait être utilisé par d'autres modules est obsolète et a été remplacé par l’événement "oaipmhrepository.values.pre". Le format des métadonnées d’un contenu. Le format des métadonnées d’un média. Le format des identifiants des lots oai. Format qui sera rendu disponible. oai_dc est obligatoire. L’entrepôt général contient toutes les ressources d’Omeka S, dans une seule url. Noter que les identifiants des lots oai sont différents (id de collection ou de site). Le module "%s" a été automatiquement désactivé car ses dépendances ne sont plus disponibles. Le module a supprimé les tables "%s" depuis une installation échouée. Le lot "%s" n’existe pas. Les entrepôts des sites simulent plusieurs serveurs OAI, avec le réservoir de contenus du site et les collections attachées en tant que lot oai. Ce module ne peut pas installer ses tables car elles existent déjà. Essayez de les supprimer manuellement. Ce module a des ressources qui ne peuvent pas être installées. Ce module requiert les modules "%s". Ce module requiert le module « %1$s », version %2$s ou supérieure. Ce module requiert le module "%s". Cette valeur est utilisée pour créer des IDs globalement uniques pour exposer les notices. Elle doit être liée à un domaine enregistrée. Utiliser une autre valeur créera des identifiants invalides. Titre comme texte et url Omeka comme attribut Durée avant expiration du jeton Argument inconnu %s. Uri et libellé séparés par une espace Uri comme texte et libellé comme attribut Uri seulement Uri seulement comme texte (conformité BnF : pas d’attribut pour l’uri) Utiliser les termes raffinés pour le Dublin Core Éléments, par exemple dcterms:abstract sera fusionné dans dc:description. Cela permet d’exposer toutes les métadonnées ans le format standard oai_dc. Pour les autres fusion, l’événement "oaipmhrepository.values.pre" peut être utilisé. Indique si le module doit cacher les lots oai vides. Indique si le module doit inclure les identifiants pour les fichiers associés aux contenus. Cela donne un accès direct aux fichiers pour les moissonneurs. Avec une liste de collections en tant que lots oai Avec des lots oai dynamiques à partir de recherches Avec des lots dynamiques définis avec les requêtes ci-dessous Avec les collections en tant que lots oai Avec les sites en tant que lots oai Avec la liste de collections ci-dessous Sans les lots oai Sans les lots oai. Vous pouvez copier %1$scorrespondance par défaut entre foaf et dcterms%2$s dans la configuration du module si besoin. 