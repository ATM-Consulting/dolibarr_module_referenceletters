# ReferenceLetters

Delivery snapshot:

- see [DELIVERY_EVIDENCE.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/DELIVERY_EVIDENCE.md) for the current delivery-oriented status, proof sources, local test dataset, and remaining gaps versus the original substitution spec.
- see [LIVRABLE_CDP.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/LIVRABLE_CDP.md) for the short functional-facing delivery summary intended for a PM/CDP audience.

Delivery posture after PM/CDP clarification:

- the target is now an exhaustive and defensible compliance proof
- every accessible field must be visible in the popup for the current context
- every visible field must be really substituted in the correct context
- repeated lists and their fields are part of the same requirement
- technical and legacy substitutable fields must stay visible
- the next phase is therefore a proof phase, not a new refactor phase

Module de generation de documents personnalises pour Dolibarr.

Ce README documente le module `referenceletters` avec un angle volontairement centre sur son fonctionnement propre et sur son integration avec `agefodd`, sans chercher a documenter le reste d'Agefodd.

Objectif de maintenance du chantier substitutions :

- toutes les cles pertinentes doivent etre visibles dans l'UI DocEdit
- toutes les cles visibles et pertinentes doivent etre effectivement converties dans l'outil
- toute valeur vide doit etre interpretable : vide normal ou vide anormal

Critere de conformite actuellement retenu :

- tout champ accessible dans `referenceletters` doit etre visible dans la popin correspondant au contexte courant
- tout champ visible dans la popin doit etre substitue dans le bon contexte
- les listes repeteees et leurs champs font partie du meme contrat
- les champs techniques / legacy / `objvar_*` restent visibles s'ils sont substituables
- la preuve attendue doit etre tracable et defendable

## Objectif du module

Le module permet de :

- definir des modeles de documents parametrables en base
- associer un modele a un type d'objet (`invoice`, `order`, `contract`, `rfltr_agefodd_*`, etc.)
- generer une instance de document a partir d'un objet source
- injecter des variables standard Dolibarr, des sous-objets, des extrafields et des tableaux
- deleguer la production PDF a un moteur commun, y compris pour les documents appeles depuis Agefodd

## Structure utile

- `class/referenceletters.class.php`
  definit l'objet modele, la liste des types supportes et la liste des substitutions exposees dans l'UI
- `class/referenceletters_tools.class.php`
  charge l'objet metier source, en particulier pour le pont avec `agefodd`
- `class/referenceletterselements.class.php`
  represente une instance generee d'un document
- `class/referenceletterschapters.class.php`
  represente les chapitres HTML d'un modele
- `class/commondocgeneratorreferenceletters.class.php`
  construit les tableaux de substitutions et expose les proprietes publiques des objets
- `class/catalog/substitutioncatalogbuilder.class.php`
  orchestrateur du catalogue UI : compose les providers et remonte automatiquement les cles disponibles manquantes dans des groupes avances
- `class/catalog/substitutioncatalogproviderinterface.class.php`
  contrat commun des providers de catalogue
- `class/catalog/substitutioncatalogstandardprovider.class.php`
  orchestrateur des familles standard
- `class/catalog/substitutioncatalogstandardscalarprovider.class.php`
  provider des cles standard scalaires
- `class/catalog/substitutioncatalogdocumentlineprovider.class.php`
  provider des cles de lignes standard
- `class/catalog/substitutioncatalogcontactprovider.class.php`
  provider des cles de contacts externes
- `class/catalog/substitutioncatalogthirdpartyprovider.class.php`
  provider des extrafields tiers et cles associees
- `class/catalog/substitutioncatalogreferenceletterprovider.class.php`
  provider du groupe DocEdit `referenceletters_*`
- `class/catalog/substitutioncatalogagefoddprovider.class.php`
  orchestrateur Agefodd : delegue les familles `formation`, `session`, `trainee`, `trainer` et `convention`
- `class/catalog/substitutioncatalogagefoddformationprovider.class.php`
  provider Agefodd pour le catalogue formation
- `class/catalog/substitutioncatalogagefoddsessionprovider.class.php`
  provider Agefodd pour la session courante et les listes session
- `class/catalog/substitutioncatalogagefoddtraineeprovider.class.php`
  provider Agefodd pour le stagiaire courant et les documents participant
- `class/catalog/substitutioncatalogagefoddtrainerprovider.class.php`
  provider Agefodd pour les groupes formateur
- `class/catalog/substitutioncatalogagefoddconventionprovider.class.php`
  provider Agefodd pour les groupes convention
- `class/catalog/substitutioncataloggroupingpolicy.class.php`
  politique de regroupement des cles auto-detectees
- `class/catalog/substitutioncatalogvisibilitypolicy.class.php`
  politique de visibilite : `user`, `advanced`, `hidden`
- `class/catalog/substitutioncatalogpresentationbuilder.class.php`
  couche de presentation UI du catalogue : produit des libelles metier et des formats attendus a partir des tags detectes
- `core/modules/referenceletters/modules_referenceletters.php`
  moteur PDF principal des documents `referenceletters`
- `core/modules/referenceletters/pdf/pdf_rfltr_agefodd.modules.php`
  adaptateur de generation pour les documents `Agefodd`
- `class/actions_referenceletters.class.php`
  hooks de generation/copie et integration avec les objets Dolibarr
- `script/audit_substitutions.php`
  premier audit CSV/code des tags vus dans les fichiers clients
- `script/inventory_element_types.php`
  inventaire runtime des `element_type` et de leur mapping effectif
- `script/inventory_ui_keys.php`
  inventaire source-based des cles visibles dans l'UI DocEdit
- `script/inventory_runtime_keys.php`
  inventaire source-based des cles substituables au runtime
- `script/inventory_segment_keys.php`
  inventaire source-based des cles de segments et des tableaux merges
- `script/build_gap_matrix.php`
  croisement CSV/UI/runtime/segments pour produire une matrice d'ecart exploitable
- `script/build_priority_reports.php`
  extraction des cibles prioritaires de correction et des `element_type` suspects
- `script/docedit_model_smoke_runner.php`
  harnais de smoke test qui cree des modeles DocEdit temporaires, injecte toutes les cles runtime detectees pour un type, rend le contenu via le moteur reel de substitutions/segments et scanne les placeholders non resolus
- `script/docedit_model_smoke_batch.php`
  superviseur batch qui reutilise le runner dans le meme process PHP, consolide les CSV, distingue les types indisponibles du runtime, detecte les faux succes sans rapport genere et sait reprendre une campagne partielle
- `script/aggregate_smoke_batch_results.php`
  consolide les derniers resultats disponibles par `element_type` a partir des batchs reels deja executes
- `script/build_smoke_followup_worklist.php`
  transforme les statuts non `ok` en worklist qualifiee avec action recommandee
- `script/debug_cli_bootstrap.php`
  script de diagnostic du bootstrap CLI Dolibarr pour isoler les problemes de session, pre-connexion MySQL et chargement de `main.inc.php`
- `script/build_active_type_runtime_ui_matrix.php`
  audit inverse par type actif : compare les tags reellement accessibles au runtime avec les cles effectivement visibles dans l'UI DocEdit
- `script/compare_initial_csv_docs.php`
  comparaison exploitable avec les CSV transmis au debut du chantier, en separant les vraies cles couvertes des faux positifs, constantes d'environnement historiques et tags legacy
- `script/activate_required_smoke_modules.php`
  active proprement les modules standards necessaires au smoke (`commande`, `expedition`, `supplier_proposal`, `ficheinter`) via leurs classes `mod*.class.php`
- `script/ensure_standard_smoke_samples.php`
  cree des objets d'exemple minimaux pour `order`, `supplier_proposal`, `expedition`/`shipping` et `fichinter`
- `script/ensure_supplier_order_sample.php`
  cree un objet d'exemple stable pour `order_supplier`
- `script/validate_real_models.php`
  valide les modeles DocEdit reels actifs de l'entite avec le moteur runtime et remonte les placeholders non resolus / warnings

## Etat actuel du catalogue UI

Le catalogue DocEdit a ete fortement remanie, mais il faut distinguer clairement ce qui est maintenant acquis et ce qui reste seulement "acceptable".

Points acquis :

- les groupes metier principaux sont plus clairs qu'au depart
- les cles techniques `__[... ]__` restent disponibles, mais dans une section dediee `Constantes techniques`
- les cles `line_*` sont maintenant qualifiees comme des champs utilisables uniquement dans une liste repetee
- une section `Listes repeteees disponibles` expose les boucles `BEGIN/END` pertinentes pour le type de document courant
- les boucles ne sont plus listees sur des documents qui ne les supportent pas

Point critique :

- le catalogue reste dense
- l'UX est aujourd'hui correcte et fonctionnelle, mais pas encore elegante
- les libelles generiques ne doivent pas ecraser les descriptions metier fournies par les providers

Correctif important applique :

- la couche de presentation donne maintenant priorite aux vraies descriptions metier du catalogue quand elles existent deja, en particulier sur les familles Agefodd (`stagiaire_*`, `time_stagiaire_*`, `line_*`, `formation_*`, etc.)
- cela evite des regressions de lisibilite sur des couples proches comme :
  - `time_stagiaire_temps_att_total`
  - `stagiaire_temps_att_total`

## Gouvernance des scripts

Le dossier `script/` contient aujourd'hui `30` scripts. Ils ne doivent pas tous etre consideres comme des outils de meme niveau.

### Scripts de validation durable

Ce sont les scripts a conserver comme outillage principal du module :

- `docedit_model_smoke_runner.php`
- `docedit_model_smoke_batch.php`
- `validate_real_models.php`
- `catalog_non_regression.php`
- `catalog_non_regression_batch.php`
- `report_unresolved_placeholders.php`

Usage :

- validation technique du rendu DocEdit
- validation du catalogue UI
- verification de placeholders non resolus

### Scripts d'audit ponctuel

Ils sont utiles pour rejouer une analyse ou recalculer un etat, mais ne doivent pas etre confondus avec la validation courante :

- `audit_substitutions.php`
- `inventory_element_types.php`
- `inventory_ui_keys.php`
- `inventory_runtime_keys.php`
- `inventory_segment_keys.php`
- `build_gap_matrix.php`
- `build_priority_reports.php`
- `build_not_covered_worklist.php`
- `build_active_type_ui_matrix.php`
- `build_active_type_runtime_ui_matrix.php`
- `build_runtime_ui_candidate_reports.php`
- `build_smoke_followup_worklist.php`
- `aggregate_smoke_batch_results.php`
- `compare_initial_csv_docs.php`
- `runtime_ui_matrix_batch.php`
- `substitution_inventory_lib.php`

Usage :

- qualification d'ecarts
- comparaison avec les CSV historiques
- photographie ponctuelle du catalogue / runtime

### Scripts de support / preparation d'environnement

Ils restent utiles, mais plutot comme utilitaires de preparation ou d'administration ponctuelle :

- `activate_required_smoke_modules.php`
- `ensure_standard_smoke_samples.php`
- `ensure_supplier_order_sample.php`
- `migrate_model_to_extrafields.php`
- `create-maj-base.php`
- `interface.php`
- `urlMover.php`

### Scripts a considerer comme debug historique

Ils ne doivent pas etre pris comme chemin officiel de validation :

- `debug_cli_bootstrap.php`

Point important :

- le chemin officiel de validation reste l'execution in-process / bibliotheque
- l'entree CLI directe reste sujette a des problemes d'environnement hors module

Recommendation de maintenance :

- ne pas supprimer ces scripts sans tri explicite
- ne pas considerer les scripts d'audit ponctuel comme des verites permanentes
- si un nettoyage physique du dossier est entrepris plus tard, la cible recommandee est :
  - `script/validation/`
  - `script/audit/`
  - `script/support/`

## Ce qu'il faut faire maintenant

Le bon travail restant n'est plus une nouvelle vague de dev generique. Le bon travail restant est une phase de preuve de conformite.

Ordre recommande :

1. figer le perimetre de preuve "a date" :
   - tout ce que le code actuel rend substituable doit etre visible
   - tout ce que l'UI affiche doit etre substitue
2. produire une matrice exhaustive de conformite :
   - `element_type`
   - `groupe UI`
   - `tag`
   - `champ direct / dans une liste / technique`
   - `visible_dans_ui`
   - `accessible_runtime`
   - `substitution_verifiee`
   - `preuve`
   - `jeu_de_donnees`
   - `statut`
   - `commentaire`
3. rattacher chaque ligne des CSV clients a un statut explicite
4. rejouer les validations finales sur les objets critiques
5. corriger uniquement les ecarts reveles
6. figer le dossier de preuve de livraison

Campagne finale disponible :

- [final_validation_campaign.php](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/script/final_validation_campaign.php)

Cette campagne :

- regenere le modele complet par type
- recharge le contexte reel du type
- rend le modele via le moteur `referenceletters`
- produit une preuve par champ, par liste repetee, et par type

## Recommandation de pilotage

Position recommandee :

- ne plus lancer de grosse refacto
- ne plus corriger au ressenti
- produire une preuve exhaustive et tracable
- livrer ensuite :
  - synthese CDP
  - note de preuve detaillee
  - annexes CSV requalifiees
  - jeux de donnees de test
  - liste des scripts utilises

Point critique :

- dans un contexte client tatillon ou conflictuel, le README et quelques scripts ne suffisent pas
- le vrai livrable de protection est une matrice exhaustive champ par champ
  - `script/archive/`

## Concepts fonctionnels

### 1. Modele

Le modele est stocke dans `llx_referenceletters`.

Il contient notamment :

- `element_type`
- `title`
- `header` / `footer`
- `use_custom_header` / `use_custom_footer`
- `use_landscape_format`
- `default_doc`

Les chapitres du modele sont stockes dans `llx_referenceletters_chapters`.

### 2. Instance

Lors d'une generation, le module cree ou manipule un objet `ReferenceLettersElements` qui porte :

- le lien vers le modele source
- l'objet source (`srcobject`)
- le contenu des chapitres resolves
- les metadonnees de sortie

### 3. Objet source

L'objet source est soit :

- un objet Dolibarr standard (`Facture`, `Commande`, `Propal`, `Societe`, `Contact`, ...)
- un objet `Agefodd` (`Agsession`, `Formation`)

## Architecture reelle des substitutions

Le point critique de ce module est qu'il ne faut pas parler d'un seul "moteur de substitutions".

En pratique, `referenceletters` repose sur 3 couches distinctes :

1. un catalogue de cles visibles dans l'UI DocEdit
2. un moteur de substitutions scalaires pour les tags `{...}`
3. un moteur de segments / boucles pour les tableaux et blocs repetes

Cette distinction est indispensable pour comprendre pourquoi certaines cles :

- sont visibles mais ne se convertissent pas
- se convertissent sans etre visibles
- ne fonctionnent que dans un segment de boucle
- retournent vide alors que le moteur n'est pas reellement en faute

### 1. Catalogue UI

Le catalogue utilisateur est construit par `ReferenceLetters::getSubtitutionKey()`.

Cette methode assemble :

- les substitutions standard utilisateur
- les substitutions de la societe emettrice
- les substitutions "Other"
- les substitutions de l'objet principal pour le type courant
- les substitutions du tiers prefixees en `cust_*`
- les substitutions de l'instance `referenceletters`
- un catalogue Agefodd manuel
- des ajouts eventuels via hook

Point critique :

- le catalogue UI n'est pas une projection automatique du moteur runtime
- surtout cote Agefodd, il est en partie maintenu a la main
- il doit donc etre verifie par script et non seulement par lecture du code
- les groupes Agefodd sont maintenant regroupes par famille metier et limites aux documents pertinents pour rester lisibles dans DocEdit
- depuis la refacto en cours, un builder complete aussi ce catalogue avec des groupes avances auto-detectes a partir du runtime courant

### 2. Substitutions scalaires

Les tags simples `{...}` sont remplaces par `ModelePDFReferenceLetters::setSubstitutions()`.

Cette methode gere notamment :

- `myuser_*`
- `mycompany_*`
- `cust_*`
- `current_*`
- `__[CONST]__`
- `object_*`
- `referenceletters_*`
- `contact_*`
- tags explicites Agefodd
- `objvar_*`

Point critique :

- le runtime supporte plus de cles que l'UI, notamment via `objvar_*`

### 3. Segments et boucles

Les tableaux et blocs repetes ne passent pas par `setSubstitutions()`.

Ils passent par `merge_array()` qui applique :

- `get_substitutionarray_lines()`
- ou `get_substitutionarray_lines_agefodd()`

Point critique :

- une cle `line_*` peut etre valide techniquement sans jamais apparaitre dans le catalogue UI standard

## Probleme structurel actuel

Le coeur du probleme fonctionnel de la spec est ici :

- le listing des cles
- la substitution scalaire
- la substitution des segments

ne sont pas alignes par construction.

Cela implique :

- des ecarts entre ce que l'utilisateur voit et ce qui fonctionne vraiment
- des faux negatifs dans les CSV de tests
- des faux positifs quand une cle est listée mais hors contexte
- une couverture extrafields dispersee entre plusieurs couches

En clair :

- rendre l'outil "complet" ne consiste pas a corriger quelques tags avec accolades
- il faut traiter le contrat complet `cles visibles -> donnees chargees -> remplacement effectif`

Un exemple concret deja verifie :

- le script `script/inventory_element_types.php` montre qu'au runtime plusieurs types `rfltr_agefodd_*` pointent aujourd'hui sur `Formation`
- ce resultat vient de la construction effective de `element_type_list`
- il faut donc auditer le comportement reel, pas seulement l'intention du code

## Refacto du catalogue UI

Le chantier de refacto du catalogue UI a demarre, mais il est volontairement hybride a ce stade.

Principe retenu :

- conserver les groupes metier manuels utiles a l'utilisateur final
- utiliser le runtime comme source de verite pour les nouvelles cles
- remonter automatiquement les cles non encore gouvernees dans des groupes `avance`
- masquer les cles sensibles ou purement techniques

Socle actuellement en place :

- `SubstitutionCatalogBuilder`
- `SubstitutionCatalogStandardProvider`
- `SubstitutionCatalogStandardScalarProvider`
- `SubstitutionCatalogDocumentLineProvider`
- `SubstitutionCatalogContactProvider`
- `SubstitutionCatalogThirdpartyProvider`
- `SubstitutionCatalogReferenceLetterProvider`
- `SubstitutionCatalogAgefoddProvider`
- `SubstitutionCatalogAgefoddFormationProvider`
- `SubstitutionCatalogAgefoddSessionProvider`
- `SubstitutionCatalogAgefoddTraineeProvider`
- `SubstitutionCatalogAgefoddTrainerProvider`
- `SubstitutionCatalogAgefoddConventionProvider`

Couche de presentation UI maintenant en place :

- l'UI DocEdit ne s'appuie plus directement sur la colonne `Value` comme pseudo-documentation
- les traductions `reflettershortcode_*` du module sont maintenant la source prioritaire des libelles metier
- le fallback genere n'est utilise que pour les cles encore non documentees dans `langs`
- le tableau utilisateur est construit a partir de :
  - `description`
  - `tag`
  - `format_hint`
- les exemples bruts comme `0`, `0,00`, `BILLING_1` ne sont plus la base de lecture de l'utilisateur
- les libelles sont generes de facon generique a partir des prefixes de tags, avec quelques conventions metier :
  - `object_*` -> `Document - ...`
  - `cust_company_*` -> `Tiers client - ...`
  - `cust_contactclient_BILLING_1_*` -> `Contact facturation 1 - ...`
  - `line_*` -> `Ligne - ...`
  - `formation_*` / `stagiaire_*` / `trainer_*` -> familles Agefodd lisibles

Objectif :

- garder une liste exhaustive
- la rendre plus lisible sans reintroduire une dette de descriptions gerees a la main
- `SubstitutionCatalogGroupingPolicy`
- `SubstitutionCatalogVisibilityPolicy`

Fonctionnement actuel :

- `getSubtitutionKey()` continue de construire les groupes historiques
- le builder orchestre maintenant une liste de providers conformes a `SubstitutionCatalogProviderInterface`
- le builder expose maintenant une metadonnee minimale par cle detectee : `source`, `classification`, `visibility`, `group_label`
- le builder delegue les familles standard a un provider dedie, lui-meme decoupe en sous-providers par famille
- le builder delegue les groupes Agefodd stables a un provider dedie
- en fin de construction, le builder ajoute les cles disponibles manquantes dans des groupes `avance`
- les cles sensibles (`password`, `token`, `api_key`, `secret`, constantes d'environnement, etc.) restent masquees
- le socle `class/catalog/*` porte maintenant des signatures typees compatibles PHP 7.4, des PHPDoc precis et des commentaires courts en anglais sur les points non triviaux
- cote Agefodd, `formation`, `session`, `trainee`, `trainer` et `convention` sont maintenant tous delegues a des sous-providers dedies
- une premiere famille standard est deja sortie du hardcode de `ReferenceLetters` vers le builder :
  - `devise_label`
  - `object_tracking_number`
  - `object_total_weight`
  - `object_total_volume`
  - `object_total_qty_ordered`
  - `object_total_qty_toship`
- d'autres familles generiques ont aussi bascule dans le builder :
  - lignes standard `line_*`
  - contacts externes `cust_contactclient_*`
  - extrafields tiers `cust_company_options_*`
  - groupe DocEdit `referenceletters_*`
  - nettoyage des placeholders globaux hors perimetre DocEdit
- premiere sous-famille Agefodd stable basculee :
  - `Agefodd Formation catalogue`
  - `Agefodd Convention`
  - `Agefodd Agenda formateur`
  - `Agefodd Formateur mission`
  - `Agefodd Liste des etapes`
  - `Agefodd Etape courante`
  - `Agefodd Liste des horaires`
  - `Agefodd Liste des formateurs`
  - `Agefodd Lignes financieres session`
  - `Agefodd Liste des objectifs pedagogiques`

Garde-fous ajoutes :

- `script/catalog_non_regression.php`
  genere un snapshot du catalogue UI et des cles detectees, puis peut comparer groupe et visibilite avec une baseline
- `script/catalog_non_regression_batch.php`
  wrapper batch du garde-fou de non-regression, capable de travailler par tranches de types
- limitation connue : sur cette machine, le bootstrap DB CLI Dolibarr reste intermittent ; le garde-fou est en place, sait travailler par chunks, mais ses runs complets peuvent encore retomber sur un `Failed to connect` d'infra

Decision d'exploitation :

- le chemin officiel de validation reste l'execution in-process / bibliotheque
- l'entree CLI directe ne doit pas etre prise comme source de verite pour juger `referenceletters`
- le `Failed to connect` restant est a traiter comme un sujet environnement / execution, pas comme un bug metier du module
  - `Agefodd Liste des participants`
  - `Agefodd Stagiaire courant`
  - `Agefodd Session courante` quasi complet :
    - identite de session / formation
    - dates
    - commercial
    - lieu
    - accessibilite
    - referents
    - agrégats de presence / temps
    - prestataire `presta_*`
    - textes d'etapes `objvar_object_steps_*`
    - textes formateur / horaires / identifiant de session

Limite actuelle :

- la refacto n'a pas encore retire toute la logique historique de `getSubtitutionKey()`
- les sous-providers Agefodd restent encore couples a des conventions de champs metier
- on est donc dans un etat transitoire volontaire :
  - faible risque de regression
  - mais code encore hybride

## Regroupement UI Agefodd

Le catalogue DocEdit Agefodd a ete nettoye pour l'utilisateur final.

Principes appliques :

- utiliser des libelles courts et metier
- ne plus afficher de groupes hors contexte
- rattacher les cles runtime importantes a des groupes compréhensibles

Groupes utilises :

- `Agefodd Formation catalogue`
- `Agefodd Session courante`
- `Agefodd Liste des participants`
- `Agefodd Liste des etapes`
- `Agefodd Etape courante`
- `Agefodd Liste des horaires`
- `Agefodd Liste des formateurs`
- `Agefodd Lignes financieres session`
- `Agefodd Liste des objectifs pedagogiques`
- `Agefodd Stagiaire courant`
- `Agefodd Convention`
- `Agefodd Formateur mission`
- `Agefodd Agenda formateur`

Regles de visibilite actuellement appliquees :

- tous les documents `rfltr_agefodd_*` affichent `Agefodd Formation catalogue`
- les documents session affichent les groupes session/liste des participants/liste des etapes/liste des horaires/liste des formateurs/lignes financieres/objectifs
- les documents `*_trainee` affichent en plus `Agefodd Stagiaire courant`
- `rfltr_agefodd_convention` affiche en plus `Agefodd Convention`
- `rfltr_agefodd_mission_trainer` et `rfltr_agefodd_contrat_trainer` affichent en plus `Agefodd Formateur mission` et `Agefodd Agenda formateur`
- `rfltr_agefodd_formation` n'affiche plus que `Agefodd Formation catalogue` en plus des groupes generiques
- le groupe standard `Lignes de documents` n'est plus affiche sur les documents Agefodd

## Harnais de smoke test DocEdit

Le script `script/docedit_model_smoke_runner.php` sert a sortir du controle "a l'oeil".

Il fait, pour chaque `element_type` cible :

1. resolution d'un objet d'exemple reel en base
2. creation d'un modele `referenceletters` temporaire
3. creation de chapitres de smoke contenant toutes les cles runtime detectees pour ce contexte
4. rechargement du modele via `RfltrTools::load_object_refletter()`
5. rendu du contenu via `setSubstitutions()` puis `merge_array()`
6. scan des placeholders `{...}` et segments encore visibles apres rendu
7. en option, tentative de generation PDF reelle

Sorties :

- rapports CSV :
  - `csvFocomed/referenceletters_docedit_smoke_summary.csv`
  - `csvFocomed/referenceletters_docedit_smoke_unresolved.csv`
- rendus HTML intermediaires :
  - `generatedDocEditSmoke/<run-id>/rendered/*.html`

Le batch `script/docedit_model_smoke_batch.php` est aujourd'hui le chemin de validation de reference.

Etat consolide courant sur les `44` `element_type` inventories :

- `44 ok`
- `0 skip`
- `0 skipped`

Interpretation actuelle :

- tous les `element_type` actuellement inventories sont passes en smoke technique sur ce runtime
- les modules standards necessaires ont ete actives sur l'entite courante :
  - `commande`
  - `expedition`
  - `supplier_proposal`
  - `ficheinter`
- les objets d'exemple manquants ont ete crees pour :
  - `order`
  - `order_supplier`
  - `supplier_proposal`
  - `expedition` / `shipping`
  - `fichinter`
- le reliquat fonctionnel n'est plus un trou de couverture smoke du module
- la limite restante est une limite de profondeur fonctionnelle :
  - le smoke prouve la prise en charge technique et l'absence de placeholder non resolu
  - il ne prouve pas a lui seul que chaque cle renvoie toujours la bonne valeur metier sur tous les modeles clients reels

Corrections structurelles validees par le smoke :

- les types `rfltr_agefodd_fiche_pedago`, `rfltr_agefodd_fiche_pedago_modules` et `rfltr_agefodd_formation` passent maintenant correctement
- les types `rfltr_agefodd_contrat_trainer` et `rfltr_agefodd_mission_trainer` passent maintenant correctement
- la cause etait double :
  - passage d'une chaine vide au lieu de `null` vers `Formation::load_all_data_agefodd()`
  - selection de contexte trainer insuffisamment robuste dans le runner de smoke

Rapports de reference a utiliser maintenant :

- `csvFocomed/referenceletters_docedit_smoke_batch_latest.csv`
- `csvFocomed/referenceletters_docedit_smoke_batch_latest_summary.csv`
- `csvFocomed/referenceletters_docedit_smoke_followup_worklist.csv`

Validation UI active :

- script : `script/build_active_type_ui_matrix.php`
- rapports :
  - `csvFocomed/referenceletters_active_type_ui_keys.csv`
  - `csvFocomed/referenceletters_active_type_ui_summary.csv`
  - `csvFocomed/referenceletters_active_type_ui_worklist.csv`
- etat courant :
  - `44` types actifs audites
  - `0` cle visible non detectee au runtime/segments
  - les faux placeholders globaux Agefodd / AgefoddCertificat ont ete retires du catalogue DocEdit lorsqu'ils ne sont pas supportes par le runtime `referenceletters`

Validation inverse runtime vers UI :

- script : `script/build_active_type_runtime_ui_matrix.php`
- rapports :
  - `csvFocomed/referenceletters_active_type_runtime_ui_keys.csv`
  - `csvFocomed/referenceletters_active_type_runtime_ui_summary.csv`
  - `csvFocomed/referenceletters_active_type_runtime_ui_worklist.csv`
- constat actuel :
  - le sens `UI -> runtime` est propre
  - le sens `runtime -> UI` n'est pas exhaustif
  - le runtime expose aujourd'hui beaucoup plus de cles que ce que l'UI DocEdit liste
- volume brut observe sur les `44` types actifs :
  - `18833` tags runtime non visibles en UI
  - dont `15195` en `dynamic_object`
  - `396` placeholders globaux externes
  - `396` cles `thirdparty`
  - `2846` cles `scalar`
- interpretation lead-dev :
  - le chiffre brut ne veut pas dire qu'il faut exposer `18833` cles aux utilisateurs
  - il melange :
    - des placeholders globaux externes non pertinents pour `referenceletters`
    - des cles dynamiques `objvar_*` trop techniques pour etre promises telles quelles en UI
    - de vraies cles scalaires / tiers aujourd'hui accessibles mais non listees
  - la conclusion correcte est donc :
    - l'UI actuelle n'est pas exhaustive
    - il faut maintenant qualifier le reliquat pour decider ce qui doit vraiment etre expose
- correctifs UI deja appliques depuis cet audit brut :
  - `getSubtitutionKey()` n'attend plus l'existence d'une instance `ReferenceLettersElements` en base pour exposer `referenceletters_*`
  - `getSubtitutionKey()` merge maintenant les substitutions `Other` dependantes de l'objet courant, y compris sur objet vide, ce qui remonte correctement `objets_lies*`, `tva_detail_*` et les champs contextuels associes
  - `getSubtitutionKey()` ne tombe plus sur un simple message `RefLtrNoneExists` pour les types standards sans enregistrement d'exemple ; il construit le catalogue sur un objet vide quand c'est techniquement possible
  - les types standards sans echantillon reel exposent maintenant aussi un tiers generique `cust_*`, ce qui remonte correctement `cust_company_*`
  - les groupes Agefodd DocEdit ont ete renommes et scopes par contexte documentaire pour etre lisibles cote utilisateur final
  - les cles Agefodd runtime les plus importantes encore absentes ont ete remontees explicitement dans le catalogue :
    - `formation_commentaire`
    - `formation_commercial_invert`
    - `formation_lieu_phone`
    - `formation_moyens_pedagogique`
    - `formation_nb_place`
    - `formation_nb_stagiaire_convention`
    - `formation_obj_peda`
    - `formation_prerequis`
    - `formation_prix`
    - `formation_ref_produit`
    - `formation_refint`
    - `formation_stagiaire_convention`
    - `formation_type`
    - les temps scalarises `time_stagiaire_*`
    - les segments Agefodd restants critiques (`line_fin_*`, `line_objpeda_*`, `line_statut`, `line_presence_*`, `line_time_*`, `line_place_birth`, etc.)
- etat pragmatique apres ces corrections :
  - les verifications ciblees confirment maintenant la visibilite UI des familles suivantes sur des types representatifs (`invoice`, `rfltr_agefodd_attestation`, `rfltr_agefodd_formation`) :
    - `referenceletters_*`
    - `objets_lies*`
    - `tva_detail_*`
    - `object_*` standards comme `object_date`
    - `cust_contactclient*` sur les objets qui les supportent
    - `cust_company_*` sur les types standards sans sample reel
    - les principaux tags Agefodd runtime cites ci-dessus
    - le groupe standard `Lignes de documents` sur les documents standards (`contract`, `order`, `invoice`, etc.)
    - les contacts externes detailles `cust_contactclient_<CODE>_1_*` pour les types de contact standards (`BILLING`, `SERVICE`, `SHIPPING`, ...)
    - les extrafields tiers visibles sous forme `cust_company_options_*` lorsqu'ils existent en base
  - le reliquat inverse n'est plus celui du premier audit brut
  - le batch global inverse reste encore handicape par l'entree CLI directe
  - en revanche, l'execution in-process via la bibliotheque de batch fonctionne et permet maintenant de regenerer une photo exploitable du reliquat
  - dernier etat consolide obtenu ainsi :
    - `44` types actifs audites
    - `69470` tags runtime inventories
    - `23019` tags runtime non visibles en UI
    - `0` candidat reel restant a exposer pour l'UI utilisateur apres filtrage des tags techniques / fantomes
  - interpretation lead-dev du reliquat courant :
    - le volume `runtime non visible` restant est volontairement majoritairement compose de cles techniques ou contextuelles
    - il comprend surtout :
      - `objvar_*`
      - des placeholders runtime non destines a l'UI finale
      - des patterns de contacts externes sous forme pseudo-generique `[...]`
      - des extrafields tiers encore presents dans certains objets runtime mais non definis dans `llx_extrafields`
    - ces cas ne doivent pas etre exposes en UI par defaut, sinon on degrade la lisibilite et on promet des cles non gouvernees
  - derniers correctifs structurels ayant permis d'atteindre cet etat :
    - ajout d'un socle generique standard dans l'UI pour les cles runtime stables :
      - `devise_label`
      - `object_tracking_number`
      - `object_total_weight`
      - `object_total_volume`
      - `object_total_qty_ordered`
      - `object_total_qty_toship`
    - ajout des options de lignes / sous-totaux :
      - `line_options_show_total_ht`
      - `line_options_show_reduc`
      - `line_options_subtotal_show_qty`
    - ajout des suffixes date extrafields tiers `cust_company_options_*_locale` et `cust_company_options_*_rfc` de facon generique
    - ajout des scalaires Agefodd session encore manquants dans les groupes metier :
      - `stagiaire_presence_*`
      - `stagiaire_temps_*`
      - `trainer_cost_planned`
      - `trainer_datehourtextline`
      - `trainer_datetextline`
      - `date_ouverture`
      - `date_ouverture_prevue`
      - `date_fin_validite`
    - filtrage dans l'audit inverse des faux candidats non exposes volontairement :
      - tags contenant `[...]`
      - extrafields tiers non declares dans `llx_extrafields`
  - etat courant sur les `44` types actifs :
  - `0` cle visible restante non detectee cote runtime/segments

Correctif applique pour atteindre cet etat :

- les 4 placeholders mail Agefodd `__AGENDATOKEN__`, `__FORMDATESESSION__`, `__FORMINTITULE__`, `__TRAINER_1_EXTRAFIELD_XXXX__` ne sont plus proposes dans l'UI DocEdit `referenceletters`
- ces placeholders etaient valides pour les mails/hors DocEdit, mais trompeurs dans le catalogue de documents

Sample de validation fournisseur :

- script : `script/ensure_supplier_order_sample.php`

Comparaison avec les CSV initiaux :

- script : `script/compare_initial_csv_docs.php`
- rapports :
  - `csvFocomed/referenceletters_initial_docs_comparison.csv`
  - `csvFocomed/referenceletters_initial_docs_comparison_summary.csv`
- dernier etat consolide :
  - `1207` entrees CSV analysees
  - `1116` `covered_ui`
  - `58` `excluded_environment_constant`
  - `10` `excluded_false_positive`
  - `1` `excluded_legacy`
  - `18` `excluded_legacy_dynamic`
  - `4` `excluded_mail_placeholder`
- interpretation lead-dev :
  - il ne reste plus de reliquat `missing_review` dans ce rapport
  - les CSV initiaux contenaient a la fois :
    - des cles actuellement bien visibles/couvertes
    - des constantes globales historiques absentes du runtime courant
    - des faux positifs de parsing
    - des tags legacy `objvar_*` qui ne doivent pas etre remis en avant tels quels pour l'utilisateur final
    - des placeholders mail hors perimetre DocEdit
  - l'ancien `audit_substitutions_report.csv` doit donc etre considere comme un audit brut historique, pas comme la photo de verite actuelle
- role :
  - cree un `order_supplier` minimal, idempotent, identifie par `import_key=REFERENCELETTERS_SMOKE_SUPPLIER_ORDER`
  - permet de fermer proprement le cas `order_supplier` dans les smoke tests

Points importants :

- le script decoupe automatiquement les modeles de smoke en plusieurs chapitres pour ne pas depasser la taille SQL de `content_text`
- le rendu de smoke n'instancie pas les classes PDF concretes standard quand ce n'est pas necessaire, afin d'eviter de bloquer l'audit sur des signatures legacy PHP 8
- le scan des placeholders ignore les faux positifs numeriques du type `P{0000}` issus de certaines constantes globales

Etat de validation actuel :

- validation reelle faite sur `thirdparty` : OK en rendu, `0` placeholder non resolu
- validation batch courte refaite apres correction du harnais :
  - `contact` : `ok`, `0` placeholder non resolu
  - `contract` : `ok`, `0` placeholder non resolu
  - `expedition` : `skip`, `type_unavailable_in_runtime`
  - snapshots conserves :
    - `csvFocomed/referenceletters_docedit_smoke_contact_summary_ok.csv`
    - `csvFocomed/referenceletters_docedit_smoke_contract_summary_ok.csv`
    - `csvFocomed/referenceletters_docedit_smoke_expedition_summary_skip.csv`
- correction technique integree dans le runner :
  - prechargement de `Product` pour eviter le fatal sur les segments de contrat
  - gestion explicite des types absents du runtime au lieu d'un faux echec
  - definition de `SYSLOG_FILE_NO_ERROR=1` pour neutraliser le bruit parasite `Failed to open log file` pendant les campagnes smoke
- correction technique integree dans le batch :
  - un `exit 0` sans rapport n'est plus considere comme un succes
  - le batch ne spawn plus un sous-processus PHP Dolibarr par type
  - il reutilise `smoke_run_from_args()` dans le meme process
  - cela elimine une grosse partie de la fragilite precedente du superviseur
  - une regression de refactor a aussi ete corrigee : le runner ne doit pas etre `require` depuis l'interieur d'une fonction du batch, sinon le bootstrap Dolibarr se fait dans une portee PHP locale et casse la creation de la connexion DB
- mode chunk ajoute dans le batch :
  - option `--chunk-size=N`
  - option `--types=a,b,c` ajoutee dans le runner pour traiter plusieurs `element_type` dans un seul bootstrap
  - objectif : reduire le nombre de bootstraps Dolibarr complets pendant les campagnes larges
- mode reprise ajoute dans le batch :
  - option `--start-type=...` pour reprendre a partir d'un type donne
  - option `--offset=N` pour decaler dans la liste triee
  - option `--resume-from=/path/summary.csv` pour sauter les types deja `completed` ou `skipped`
  - option `--skip-done=1` active automatiquement quand `--resume-from` est fourni
  - si toute la selection est deja couverte, le batch termine proprement avec un message explicite au lieu d'un faux echec
  - objectif : pouvoir derouler la campagne reelle par tranches sans reexecuter ce qui est deja valide
- le runner sait maintenant aussi fonctionner en mode bibliotheque via `smoke_run_from_args()`
- la campagne batch plus large (`--limit=10`) reste encore instable sur cette machine
- le batch court de reference est a nouveau valide apres correction de cette regression :
  - `php custom/referenceletters/script/docedit_model_smoke_batch.php --limit=3 --skip-pdf=1`
  - resultat courant : `contact=ok`, `contract=ok`, `expedition=skip`
- une premiere tranche large est maintenant validee via lancement in-process en mode bibliotheque :
  - `php -r 'define("REFERENCELETTERS_SMOKE_BATCH_LIB_ONLY",1); require "custom/referenceletters/script/docedit_model_smoke_batch.php"; $r=smoke_batch_run_from_args(["--limit=10","--skip-pdf=1"], true); echo json_encode($r["status_counts"] ?? []),"\n";'`
  - resultat courant : `6 completed:ok`, `1 completed:skipped`, `3 skipped:skip`
- une deuxieme tranche large est aussi validee de la meme facon :
  - `offset=10`, `limit=10`
  - resultat courant : `6 completed:ok`, `4 skipped:skip`
- une troisieme tranche large est validee :
  - `offset=20`, `limit=10`
  - resultat courant : `8 completed:ok`, `2 completed:skipped`
- une quatrieme tranche termine la liste actuelle des types :
  - `offset=30`, `limit=10` puis `offset=40`, `limit=10`
  - resultats courants :
    - `offset=30` : `8 completed:ok`, `2 completed:skipped`
    - `offset=40` : `1 completed:ok`, `1 completed:skipped`, `2 skipped:skip`
- au total, les `44` types actuellement inventories ont donc ete passes par une campagne smoke reelle, par tranches, sans `error` runtime bloquant sur le moteur de substitutions
- la consolidation courante donne maintenant :
  - `34 ok`
  - `9 skip type_unavailable_in_runtime`
  - `1 skipped` lie a l'absence de donnees d'echantillon
- les `9 skip type_unavailable_in_runtime` ne sont pas aujourd'hui des crashes du moteur :
  - `expedition`, `shipping` : module expedition desactive dans ce runtime
  - `fichinter` : module fichinter desactive
  - `order` : module commande client desactive
  - `supplier_proposal` : module supplier proposal desactive
  - `rfltr_agefodd_certificate*` : module `agefoddcertificat` desactive
- les cas auparavant bloques sur `formation`, `trainer` et `order_supplier` ont ete corriges et valides par rerun cible
- le catalogue UI des types actifs est maintenant aligne avec le runtime detecte sur les types `ok`
- le reliquat principal n'est plus un ecart de substitutions DocEdit, mais :
  - des warnings de robustesse encore visibles pendant les audits lourds

Derniers correctifs de robustesse appliques :

- `custom/referenceletters/class/commondocgeneratorreferenceletters.class.php`
  - gardes supplementaires sur les metadonnees extrafields et sur certaines valeurs `datetime`
- `core/class/commondocgenerator.class.php`
  - gardes sur `mode_reglement_code`, `cond_reglement_code`, labels associes et compte bancaire
- le point bloquant n'est plus `exec()`, mais un bootstrap CLI Dolibarr encore fragile sur les campagnes plus longues
- ce bootstrap fragile reste intermittent meme sur certains relaunchs cibles (`--start-type=...`) : il faut donc traiter l'infrastructure/runtime separement du moteur `referenceletters`
- le diagnostic `script/debug_cli_bootstrap.php` a revele un autre point dur sur cette machine : `main.inc.php` essaie encore d'ouvrir les sessions dans `/var/lib/php/sessions` en CLI, avec `Permission denied`
- ce point session a ete neutralise dans les scripts CLI `referenceletters` en forcant `session.save_path=/tmp`
- le warning `multicompany` sur `Undefined array key \"type\"` a ete corrige dans `custom/multicompany/class/actions_multicompany.class.php`
- warning de robustesse corrige dans `custom/agefodd/core/substitutions/functions_agefodd.lib.php` : acces a `needforkey` maintenant garde
- warning de robustesse corrige dans `custom/referenceletters/class/commondocgeneratorreferenceletters.class.php` : acces aux extrafields `array_options` maintenant garde quand la cle n'existe pas
- autres warnings de robustesse corriges dans `custom/referenceletters/class/commondocgeneratorreferenceletters.class.php` :
  - resolution `select` quand la valeur n'existe pas dans les options
  - acces a `remise_percent` sur des objets qui ne l'exposent pas
  - acces a `ref_fourn`, `rang`, `libelle`, `label` sur des lignes qui ne portent pas toujours ces proprietes
- il reste cependant une asymetrie runtime a expliquer : le batch court in-process passe, alors que certains lancements CLI directs du runner ou du script de debug tombent encore en `Failed to connect`
- l'appel bibliotheque du runner (`require .../docedit_model_smoke_runner.php` puis `smoke_run_from_args(...)`) passe, y compris avec `emitOutput=true`
- l'appel bibliotheque du batch (`require .../docedit_model_smoke_batch.php` puis `smoke_batch_run_from_args(...)`) passe aussi sur des tranches de `10`, alors qu'un lanceur CLI direct equivalent reste expose a l'instabilite de bootstrap
- en l'etat, l'entree CLI directe `php custom/referenceletters/script/docedit_model_smoke_runner.php ...` n'est donc pas un chemin fiable de validation, contrairement au batch in-process
- le point faible n'est donc plus la couverture brute des `element_type`, mais la qualite de qualification des cas `skip` / `skipped` et, ensuite, la verification fonctionnelle plus fine des donnees et cas vides dans les modeles reels
- les rapports de travail pour cette phase sont maintenant :
  - `csvFocomed/referenceletters_docedit_smoke_batch_latest.csv`
  - `csvFocomed/referenceletters_docedit_smoke_batch_latest_summary.csv`
  - `csvFocomed/referenceletters_docedit_smoke_followup_worklist.csv`
- donc le harnais est utile et plus fiable qu'avant, mais le passage "tous types en un run propre" demande encore un lot de stabilisation runtime/infrastructure

Conclusion exigeante :

- ce script est maintenant un bon outil d'audit pour avancer proprement
- ce n'est pas encore une preuve que "tout le module est propre"
- au contraire, il met en lumiere les zones runtime qui cassent encore en batch et qu'il faudra traiter

## Flux de generation

### Cas standard

1. Un document `referenceletters` est demande pour un objet.
2. `RfltrTools::load_object_refletter()` charge le modele et l'objet source.
3. Les chapitres sont charges et prepares.
4. Le moteur PDF applique les substitutions.
5. Le PDF est genere dans l'arborescence `referenceletters`.

### Cas Agefodd

Le point d'entree ne part pas directement de `referenceletters`, mais de `agefodd` via `agf_pdf_create()`, qui bascule vers `pdf_rfltr_agefodd` quand un modele DocEdit est selectionne.

Flux reel :

1. `agefodd` determine qu'un modele externe `referenceletters` doit etre utilise.
2. `custom/agefodd/core/modules/agefodd/modules_agefodd.php`
   appelle `agf_pdf_create(...)`
3. `agf_pdf_create(...)` charge `pdf_rfltr_agefodd`
4. `pdf_rfltr_agefodd::write_file_custom_agefodd(...)`
   appelle `RfltrTools::load_object_refletter(...)`
5. `RfltrTools::load_agefodd_object(...)`
   instancie `Agsession` ou `Formation`
6. `load_agefodd_object(...)`
   appelle `load_all_data_agefodd(...)`
7. l'objet Agefodd enrichi est repasse au moteur `referenceletters`
8. les substitutions et merges de tableaux sont appliques
9. le PDF final est ecrit dans le repertoire de sortie Agefodd

Point critique :

- pour Agefodd, la donnee ne vient pas directement du template
- elle vient d'abord de l'objet source enrichi par `load_all_data_agefodd()`
- ensuite seulement elle est exposee en tags explicites ou en `objvar_*`

## Integration avec Agefodd

### Types supportes

Les types `Agefodd` sont declares dans `ReferenceLetters::__construct()`.

Exemples :

- `rfltr_agefodd_convention`
- `rfltr_agefodd_convocation`
- `rfltr_agefodd_attestation`
- `rfltr_agefodd_convocation_trainee`
- `rfltr_agefodd_attestation_trainee`
- `rfltr_agefodd_attestationendtraining_trainee`
- `rfltr_agefodd_linked_certificate_completion_trainee`
- `rfltr_agefodd_certificate_completion_trainee`
- `rfltr_agefodd_formation`

### Chargement des donnees Agefodd

Le chargement central se fait via :

- `RfltrTools::load_agefodd_object()`
- `Agsession::load_all_data_agefodd()`
- `Formation::load_all_data_agefodd()`

`load_all_data_agefodd()` enrichit massivement l'objet session avec :

- participants
- participants presents / confirmes
- participants filtres par societe
- conventions et lignes financieres
- horaires de session
- etapes
- formateurs
- lieu
- formation et objectifs pedagogiques
- donnees de stagiaire pour les documents par participant
- extrafields et variables de configuration `AGF_*`

### Documents par participant

Pour les modeles `_trainee`, le parametre `socid` transporte selon le cas :

- l'id de la ligne session/stagiaire
- ou l'id du stagiaire pour certains certificats

Le comportement depend du `element_type` du modele. Le routage est gere dans `Agsession::load_all_data_agefodd()`.

## Matrice des types `rfltr_agefodd_*`

Les types declares dans `ReferenceLetters::__construct()` sont les suivants.

### Types session / convention / documents lies

- `rfltr_agefodd_convention`
- `rfltr_agefodd_fiche_pedago`
- `rfltr_agefodd_fiche_pedago_modules`
- `rfltr_agefodd_conseils`
- `rfltr_agefodd_fiche_presence`
- `rfltr_agefodd_fiche_presence_direct`
- `rfltr_agefodd_fiche_presence_empty`
- `rfltr_agefodd_fiche_presence_trainee`
- `rfltr_agefodd_fiche_presence_trainee_direct`
- `rfltr_agefodd_fiche_presence_landscape`
- `rfltr_agefodd_fiche_evaluation`
- `rfltr_agefodd_fiche_remise_eval`
- `rfltr_agefodd_attestationendtraining_empty`
- `rfltr_agefodd_chevalet`
- `rfltr_agefodd_convocation`
- `rfltr_agefodd_attestationendtraining`
- `rfltr_agefodd_attestationpresencetraining`
- `rfltr_agefodd_attestationpresencecollective`
- `rfltr_agefodd_attestation`
- `rfltr_agefodd_contrat_presta`
- `rfltr_agefodd_courrier`

### Types formateur

- `rfltr_agefodd_mission_trainer`
- `rfltr_agefodd_contrat_trainer`

Effet de contexte attendu :

- `socid` est interprete comme identifiant de session formateur
- `load_all_data_agefodd()` charge en plus `formateur_session`, `formateur_session_societe`, `trainer_datehourtextline`, `trainer_datetextline`

### Types participant

- `rfltr_agefodd_convocation_trainee`
- `rfltr_agefodd_attestation_trainee`
- `rfltr_agefodd_attestationendtraining_trainee`
- `rfltr_agefodd_linked_certificate_completion_trainee`
- `rfltr_agefodd_certificate_completion_trainee`

Effet de contexte attendu :

- `socid` est interprete comme identifiant de ligne `agefodd_session_stagiaire`
- `load_all_data_agefodd()` charge en plus `stagiaire`, `stagiaire_presence_*`, `stagiaire_comment`, `document_societe`

### Types formation

- `rfltr_agefodd_formation`

Effet de contexte attendu :

- le chargement passe par `Formation::load_all_data_agefodd()`
- les donnees attendues sont principalement celles de la formation et de ses objectifs pedagogiques

### Types certificats additionnels

Si `agefoddcertificat` est actif :

- `rfltr_agefodd_certificateA4`
- `rfltr_agefodd_certificatecard`
- `rfltr_agefodd_certificateA4_trainee`
- `rfltr_agefodd_certificatecard_trainee`

Point de vigilance :

- les types `_trainee` de cette famille n'utilisent pas le meme sens de `socid` que les autres documents participant
- dans ce cas, `socid` est interprete comme identifiant de stagiaire

## Matrice des donnees injectees

Cette section documente les principales donnees chargees pour les templates `referenceletters`.

Elle ne remplace pas l'inspection du code, mais donne la carte utile pour la maintenance.

### Donnees communes de session

Chargees par `Agsession::load_all_data_agefodd()` pour la plupart des modeles session :

- `TStagiairesSession`
  liste complete des participants de la session
- `TStagiairesSessionPresent`
  participants avec statut present / partiellement present
- `THorairesSession`
  lignes de calendrier de session
- `TFormateursSession`
  contributeurs / formateurs de la session
- `TSteps`
  toutes les etapes
- `TStepsPresentiel`
  etapes presentiel
- `TStepsDistanciel`
  etapes distanciel
- `formation`
  objet formation ou copie de formation associee
- `TFormationObjPeda`
  objectifs pedagogiques
- `lieu`
  objet lieu
- `trainer_text`
  formateurs concaténés `Nom Prenom`
- `trainer_text_invert`
  formateurs concaténés `Prenom Nom`
- `dthour_text`
  horaires formates
- `date_text`
  plage de dates session
- `date_text_formated`
  plage de dates formatee
- `session_nb_days`
  nombre de jours de session

Exemples de tags derives :

- `{objvar_object_trainer_text}`
- `{objvar_object_dthour_text}`
- `{objvar_object_date_text}`
- `{objvar_object_session_nb_days}`
- `{objvar_object_lieu_nom}` selon les proprietes publiques presentes

### Donnees convention / financement

Chargees si un objet convention est fourni :

- `convention_id`
- `convention_notes`
- `TConventionFinancialLine`
- `TStagiairesSessionConvention`
- `stagiaire_convention`
- `nb_stagiaire_convention`
- `conv_amount_ht`
- `conv_amount_tva`
- `conv_amount_ttc`
- `conv_qty`
- `conv_products`
- `ref_findoc`

Exemples de tags derives :

- `{objvar_object_convention_notes}`
- `{objvar_object_conv_amount_ht}`
- `{objvar_object_ref_findoc}`

### Donnees filtrees par societe

Chargees quand `socid` est utilise comme societe cible :

- `TStagiairesSessionSoc`
  participants lies a la societe
- `TStagiairesSessionSocPresent`
  participants presents pour cette societe
- `TStagiairesSessionSocConfirm`
  participants confirmes pour cette societe
- `TStagiairesSessionSocMore`
  participants avec donnees de signature / relation plus larges
- `document_societe`
  objet `Societe` charge pour le document
- `signataire_intra`
- `signataire_intra_poste`
- `signataire_intra_mail`
- `signataire_intra_phone`
- `signataire_inter`
- `signataire_inter_poste`
- `signataire_inter_mail`
- `signataire_inter_phone`

Exemples de tags derives :

- `{objvar_object_document_societe_name}`
- `{objvar_object_signataire_intra}`
- `{objvar_object_signataire_inter}`

### Donnees formateur

Chargees pour `rfltr_agefodd_mission_trainer` et `rfltr_agefodd_contrat_trainer` :

- `formateur_session`
- `formateur_session_societe`
- `trainer_datehourtextline`
- `trainer_datetextline`

Exemples de tags derives :

- `{objvar_object_formateur_session_lastname}`
- `{objvar_object_formateur_session_firstname}`
- `{objvar_object_formateur_session_societe_name}`
- `{objvar_object_trainer_datehourtextline}`

### Donnees participant

Chargees pour les documents `_trainee` :

- `stagiaire`
  objet stagiaire cible
- `stagiaire_presence_bloc`
- `stagiaire_presence_total`
- `time_stagiaire_temps_realise_total`
- `stagiaire_temps_realise_total`
- `time_stagiaire_temps_att_total`
- `stagiaire_temps_att_total`
- `time_stagiaire_temps_realise_att_total`
- `stagiaire_temps_realise_att_total`
- `stagiaire_comment`

Exemples de tags derives :

- `{objvar_object_stagiaire_nom}`
- `{objvar_object_stagiaire_prenom}`
- `{objvar_object_stagiaire_presence_total}`
- `{objvar_object_stagiaire_comment}`

### Extrafields exposes

Le moteur peut exposer :

- extrafields de l'objet session / formation / stagiaire via `array_options`
- extrafields de la societe du stagiaire via les proprietes `stagiaire_soc_*`

Exemples de tags :

- `{objvar_object_options_xxx}`
- `{objvar_object_array_options_options_xxx}`
- `{objvar_object_stagiaire_options_xxx}` si la propriete publique correspondante existe
- `{objvar_object_stagiaire_soc_options_xxx}`

### Variables de configuration `AGF_*`

Toutes les constantes globales dont le nom commence par `AGF_` sont recopiees comme proprietes publiques de l'objet.

Exemple :

- `AGF_NB_HOUR_IN_DAYS` devient exploitable via la mecanique `objvar_object_AGF_NB_HOUR_IN_DAYS`

Point de vigilance :

- ces variables sont injectees globalement, donc elles augmentent le couplage entre configuration et templates

## Systeme de substitutions

Le moteur de substitutions combine plusieurs sources :

- variables utilisateur
- societe emettrice
- tiers
- objet principal Dolibarr
- instance `referenceletters`
- donnees Agefodd
- proprietes publiques de l'objet source et de ses sous-objets
- extrafields

### Source de verite reelle

Pour auditer ou corriger une cle, il faut toujours verifier dans cet ordre :

1. la cle est-elle listee dans l'UI
2. la donnee est-elle reellement chargee sur l'objet source
3. la cle passe-t-elle par le moteur scalaire ou par un segment
4. le format final attendu est-il coherent avec le contexte

### Familles de tags

Exemples :

- `{referenceletters_title}`
- `{cust_name}`
- `{object_ref}`
- `{objvar_object_ref}`
- `{objvar_object_stagiaire_nom}`
- `{objvar_object_document_societe_name}`

### Exposition automatique des proprietes

`CommonDocGeneratorReferenceLetters::get_substitutionarray_each_var_object()` parcourt les proprietes publiques de l'objet et de certains sous-objets.

Regles importantes :

- les proprietes non publiques ne sont pas exposees
- les champs `array_options` sont transformes en substitutions exploitables
- certains scalaires sont formats automatiquement
- les sous-objets sont prefixés recursivement

Exemple :

- `$object->stagiaire->nom` devient `objvar_object_stagiaire_nom`
- `$object->document_societe->name` devient `objvar_object_document_societe_name`

### Extrafields

Les extrafields sont exposes de deux manieres :

- `object_options_xxx`
- `object_array_options_options_xxx`

Le moteur contient aussi un traitement specifique pour les extrafields de societe lies au stagiaire :

- `stagiaire_soc_options_*`

## Merges de tableaux

Le moteur PDF `pdf_rfltr_agefodd` sait iterer sur plusieurs tableaux Agefodd pour produire des blocs repetes dans les chapitres.

Tableaux merges explicitement :

- `THorairesSession`
- `TFormationObjPeda`
- `TStagiairesSession`
- `TStagiairesSessionPresent`
- `TStagiairesSessionSoc`
- `TStagiairesSessionSocPresent`
- `TStagiairesSessionSocConfirm`
- `TStagiairesSessionSocMore`
- `TStagiairesSessionConvention`
- `TFormateursSession`
- `TConventionFinancialLine`
- `TFormateursSessionCal`
- `TSteps`
- `TStepsDistanciel`
- `TStepsPresentiel`

Point critique :

- un tableau peut etre present sur l'objet final sans etre merge automatiquement
- dans ce cas, les cles `line_*` associees ne sortiront jamais dans le template

## Sortie des fichiers

Deux logiques coexistent :

- sortie native `referenceletters` dans son propre repertoire
- sortie adaptee `Agefodd` via `pdf_rfltr_agefodd`, qui ecrit dans `conf->agefodd->dir_output`

Pour les documents Agefodd, c'est le second comportement qui est utilise.

Les etapes (`fk_step`) modifient aussi le repertoire cible via les helpers Agefodd.

## Checklist de debug

Quand un document `referenceletters` Agefodd ne sort pas correctement, appliquer cette checklist dans l'ordre.

### 1. Confirmer qu'on est bien sur un modele `referenceletters`

- verifier la valeur de `model`
- verifier `id_external_model`
- verifier si `agf_pdf_create()` part bien sur `pdf_rfltr_agefodd`

Si ce n'est pas le cas, le probleme n'est pas dans `referenceletters` mais dans le routage Agefodd.

### 2. Identifier le type exact du modele

- verifier `element_type` du modele charge
- confirmer s'il s'agit d'un type session, participant, formateur ou formation

Sans cette etape, on ne peut pas interpreter correctement `socid`.

### 3. Verifier le sens reel de `socid`

Selon le type, `socid` peut etre :

- une societe
- un id de session formateur
- un id de ligne `agefodd_session_stagiaire`
- un id de stagiaire

Si cette hypothese est fausse, toutes les donnees injectees ensuite peuvent etre incoherentes.

### 4. Verifier l'objet source final

Le point cle est l'objet retourne apres :

- `RfltrTools::load_object_refletter()`
- puis `load_all_data_agefodd()`

Il faut controler en priorite :

- presence des proprietes attendues
- presence des tableaux attendus
- valeur effective de `stagiaire`, `document_societe`, `formation`, `lieu`

### 5. Verifier les substitutions, pas seulement les donnees

Une propriete peut exister mais ne pas etre exposée comme attendu.

Verifier :

- nom public exact de la propriete
- prefixe final du tag `objvar_object_*`
- presence d'`array_options`
- cas particulier `stagiaire_soc_options_*`

### 6. Verifier les merges de tableaux

Si un bloc repetitif ne sort pas :

- verifier que le tableau est bien rempli
- verifier qu'il fait partie des tableaux merges explicitement par `pdf_rfltr_agefodd`

Un tableau present dans l'objet mais absent de la liste de merge ne sera pas iteré automatiquement.

### 7. Verifier le contexte `fk_step`

Si les donnees semblent partielles :

- verifier si `fk_step` est transmis
- verifier l'effet sur `THorairesSession`
- verifier l'effet sur `TFormateursSession`
- verifier le repertoire de sortie

### 8. Verifier les incoherences de nommage

En cas de modele "introuvable" ou de comportement incoherent :

- verifier les differences `certificate` / `certificat`
- verifier la convention `rfltr_agefodd_<type>`
- verifier les correspondances UI / `element_type`

### 9. Verifier si le probleme est dans le template ou dans le contrat de donnees

Question simple a se poser :

- la propriete n'existe pas sur l'objet final -> probleme de chargement
- la propriete existe mais le tag ne sort pas -> probleme de substitution
- le tag sort mais la structure repetee est vide -> probleme de merge / template
- le PDF ne se genere pas -> probleme de routage ou de moteur PDF

## Strategie de correction recommandee

Pour atteindre l'objectif "toutes les cles visibles + toutes les cles converties", il faut traiter le module dans cet ordre :

1. cartographier les cles listees dans l'UI
2. cartographier les cles substituees au runtime
3. cartographier les cles de segments `line_*`
4. croiser ces resultats avec les CSV utilisateurs
5. corriger par familles d'objets et non tag par tag
6. maintenir un suivi de chantier dedie

Le fichier de suivi de ce chantier est :

- `custom/referenceletters/SUBSTITUTIONS_ROADMAP.md`

Scripts deja disponibles :

- `php custom/referenceletters/script/audit_substitutions.php`
- `php custom/referenceletters/script/inventory_element_types.php`
- `php custom/referenceletters/script/inventory_ui_keys.php`
- `php custom/referenceletters/script/inventory_runtime_keys.php`
- `php custom/referenceletters/script/inventory_segment_keys.php`
- `php custom/referenceletters/script/build_gap_matrix.php`
- `php custom/referenceletters/script/build_priority_reports.php`
- `php custom/referenceletters/script/report_unresolved_placeholders.php <fichier> [fichier2 ...]`

Rapports generes par les scripts :

- `custom/referenceletters/csvFocomed/referenceletters_element_types.csv`
- `custom/referenceletters/csvFocomed/referenceletters_ui_keys.csv`
- `custom/referenceletters/csvFocomed/referenceletters_runtime_keys.csv`
- `custom/referenceletters/csvFocomed/referenceletters_segment_keys.csv`
- `custom/referenceletters/csvFocomed/referenceletters_gap_matrix.csv`
- `custom/referenceletters/csvFocomed/referenceletters_priority_keys.csv`
- `custom/referenceletters/csvFocomed/referenceletters_suspicious_element_types.csv`
- `custom/referenceletters/csvFocomed/referenceletters_not_covered_worklist.csv`
- `custom/referenceletters/csvFocomed/referenceletters_not_covered_summary.csv`

Etat chiffre du chantier a date :

- `44` types de documents/objets inventories
- `0` mappings Agefodd suspects
- `483` cles/patterns UI inventories
- `270` cles/patterns runtime inventories
- `165` elements segments inventories
- `1207` entrees CSV analysees
- matrice d'ecart :
  - `1192` `covered`
  - `7` `not_covered`
  - `0` `listed_but_not_detected_runtime`
  - `0` `runtime_only`
  - `8` `false_positive_candidate`

Priorites techniques immediates :

- utiliser `report_unresolved_placeholders.php` sur les contenus de test pour verifier les placeholders encore visibles apres substitution
- arbitrer les `7` entrees `not_covered` restantes a partir de `referenceletters_not_covered_summary.csv`
  - `4` placeholders mail Agefodd hors perimetre `referenceletters`
  - `2` tokens de format CSV `mm` / `yy`
  - `1` ancien tag legacy `line_civilitel`

Etat du premier lot structurel :

- le mapping `element_type_list` Agefodd a ete corrige
- les types `rfltr_agefodd_*` derives pointent a nouveau vers `Agsession`
- les rapports de priorite ne remontent plus de `listed_but_not_detected_runtime`
- les rapports de priorite ne remontent plus non plus de `runtime_only`
- le socle moteur / catalogue / inventaire est maintenant beaucoup plus coherent
- le volume brut `not_covered` a ete reduit de `817` a `7` apres alignement des patterns dynamiques et des placeholders globaux

## Hooks utiles

Hooks explicitement utilises par le module :

- `referencelettersConstruct`
  pour ajouter des types de documents
- `referencelettersCompleteSubstitutionArray`
  pour completer la liste des substitutions disponibles
- `afterPDFCreation`
  apres generation du PDF

Cote Agefodd, le chargement documentaire s'appuie aussi sur :

- `loadAllDataAgefoddSession`

## Points de vigilance techniques

### 1. Fallback historique Agefodd

`RfltrTools::load_agefodd_object()` tente encore un fallback sur `load_all_data_agefodd_session`.

Dans ce depot, cette methode n'existe pas. Le chemin reel utilise est `load_all_data_agefodd()`.

### 2. Declaration des types Agefodd dans `referenceletters.class.php`

Le probleme structurel principal de la construction de `element_type_list` pour `rfltr_agefodd_*` a ete corrige :

- un premier bloc declare `rfltr_agefodd_convention`
- un second bloc duplique les cles a partir du type convention
- un troisieme bloc declare `rfltr_agefodd_formation`

La reaffectation finale des types derives vers `rfltr_agefodd_formation` a ete retiree.

Consequence directe :

- les types derives `rfltr_agefodd_*` remontent a nouveau correctement sur `Agsession`
- l'inventaire des types ne remonte plus de mapping Agefodd suspect
- cette zone reste sensible parce qu'elle conditionne encore le catalogue utilisateur et les objets de test utilises pour l'analyse

Nuance importante :

- le runtime PDF Agefodd contourne partiellement ce probleme via `RfltrTools::load_agefodd_object()`, qui instancie directement `Agsession` pour les documents session/participant/formateur et `Formation` seulement quand `fk_training > 0`
- en revanche, `ReferenceLetters::getSubtitutionKey()` s'appuie bien sur `element_type_list` pour construire le catalogue utilisateur
- avant correction, le mapping cassait donc d'abord la fiabilite du catalogue UI et de l'analyse par type, meme si certains PDFs continuaient a se generer

### 3. Nommage incoherent de certains types

Il existe des incoherences `certificate` / `certificat` dans certaines zones de l'UI Agefodd. Cela peut fausser la detection de l'existence d'un modele DocEdit.

### 4. Logique de donnees tres concentree dans `Agsession`

`load_all_data_agefodd()` joue a la fois le role de :

- chargeur de donnees
- agregateur de sous-objets
- adaptateur pour les modeles

Avant toute refactorisation, il faut considerer cette methode comme un contrat d'interface utilise par les modeles.

### 5. Ecart structurel UI / runtime

Le probleme central du chantier en cours est un ecart structurel entre :

- `ReferenceLetters::getSubtitutionKey()`
- `ModelePDFReferenceLetters::setSubstitutions()`
- `merge_array()`

Tant que cet ecart n'est pas reduit, l'utilisateur ne peut pas se fier completement a la liste des cles affichees.

## Comment etendre proprement le module

Pour ajouter un nouveau type de document :

1. ajouter un `element_type` dans la construction du module ou via hook `referencelettersConstruct`
2. s'assurer que l'objet source peut etre charge
3. enrichir l'objet source avec les donnees necessaires
4. exposer les variables utiles
5. si besoin, completer les substitutions documentees dans l'UI
6. verifier le repertoire de sortie et le nommage du fichier

Pour un besoin `Agefodd`, privilegier :

- l'enrichissement de `load_all_data_agefodd()`
- ou le hook `loadAllDataAgefoddSession`

plutot qu'une logique dispersee dans les templates.

## Conseils de maintenance

- verifier d'abord le flux d'entree: standard Dolibarr ou Agefodd
- tracer la valeur de `element_type`
- identifier si le modele est standard ou `rfltr_agefodd_<id>`
- confirmer la nature exacte du parametre `socid`
- regarder l'objet final enrichi avant substitution
- ne pas supposer que tous les tags Agefodd sont statiques: beaucoup viennent des proprietes publiques chargees dynamiquement

## Resume

`referenceletters` est un moteur de documents base sur :

- des modeles stockes en base
- un chargeur d'objet source
- un systeme large de substitutions
- un moteur PDF commun

Son integration avec `Agefodd` repose principalement sur :

- `agf_pdf_create()`
- `pdf_rfltr_agefodd::write_file_custom_agefodd()`
- `RfltrTools::load_object_refletter()`
- `Agsession::load_all_data_agefodd()`

Si un document Agefodd semble "magique", le plus souvent la donnee ne vient pas du template lui-meme, mais de l'objet `Agsession` enrichi juste avant la phase de substitution.

Dans le cadre du chantier substitutions DocEdit, le vrai objectif n'est donc pas seulement de "remplacer toutes les accolades", mais de garantir ensemble :

- la visibilite des cles dans l'UI
- la presence des donnees sur l'objet source
- la conversion effective des tags et segments

## Validation metier reelle

Une campagne de validation sur les vrais modeles DocEdit actifs de l'entite courante est maintenant disponible via :

- `custom/referenceletters/script/validate_real_models.php`

Cette campagne :

- charge les modeles actifs de `llx_referenceletters`
- reutilise le vrai chargement runtime `referenceletters`
- rend le contenu reel du modele
- detecte les placeholders non resolus
- separe les warnings techniques dans un rapport dedie

Limite volontaire :

- la campagne travaille dans le contexte de l'entite courante (`entity IN getEntity('referenceletters')`)
- les modeles d'autres entites ne doivent pas etre valides avec ce contexte, sinon on fabrique de faux resultats via les fallbacks multi-entity

### Etat valide au 2026-03-03

Resultat sur l'entite courante :

- `13` modeles actifs traites
- `13` modeles `ok`
- `0` placeholder non resolu
- `0` warning

Rapports de reference :

- `custom/referenceletters/csvFocomed/referenceletters_real_models_validation_summary.csv`
- `custom/referenceletters/csvFocomed/referenceletters_real_models_validation_unresolved.csv`
- `custom/referenceletters/csvFocomed/referenceletters_real_models_validation_warnings.csv`

Repertoire de rendu :

- `custom/referenceletters/generatedRealModelValidation/20260303-130226`

### Ce que cette campagne prouve

- les cles effectivement utilisees dans les modeles actifs de l'entite courante sont bien converties par le moteur
- les vrais modeles actifs `referenceletters` ne laissent plus de tags bruts dans ce perimetre
- le lot de robustesse `Agefodd` / `load_all_data_agefodd()` / renderer a supprime le bruit technique restant sur cette campagne reelle

### Ce que cette campagne ne prouve pas encore

- que les valeurs metier sont semantiquement justes dans tous les cas fonctionnels possibles
- que les autres entites multi-company ont le meme niveau de proprete sans relancer la campagne dans leur contexte

### Correctifs appliques pour fermer le reliquat

Les derniers warnings ont ete fermes en combinant les bons niveaux de correction :

- cote `agefodd` :
  - normalisation des lignes stagiaires chargees par `Agsession::load_all_data_agefodd()`
  - normalisation des lignes etapes (`place`) pour les documents DocEdit
  - normalisation des lignes formateurs (`labelstatut`, coordonnees)
  - normalisation des lignes financieres (`special_code`) pour la compatibilite `subtotal`
- cote `referenceletters` :
  - gardes defensifs dans `get_substitutionarray_lines_agefodd()` pour les lignes heterogenes (`stagiaire`, `horaire`, `formateur`, `objectif`, `financier`, `etape`)
  - gardes sur les extrafields partiels
  - gardes sur les alias formation et sur les lignes de contrat
- cote core :
  - gardes dans `CommonDocGenerator::get_substitutionarray_thirdparty()` et `get_substitutionarray_contact()` quand aucun objet n'est disponible
  - gardes dans `subtotal` quand une ligne n'expose pas `special_code`

Conclusion :

- la campagne reelle courante ne laisse plus ni placeholder non resolu ni warning technique
- le prochain enjeu n'est plus la robustesse technique brute, mais la verification semantique des valeurs metier sur les documents critiques
