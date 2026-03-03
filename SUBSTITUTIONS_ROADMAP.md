# Roadmap Substitutions DocEdit

Ce fichier sert de plan d'execution et de suivi pour le chantier de fiabilisation des substitutions `referenceletters`.

Objectif final :

- toutes les cles existantes pertinentes doivent etre visibles par les utilisateurs dans l'UI DocEdit
- toute cle visible et pertinente doit etre correctement convertie dans le bon contexte
- toute valeur vide doit etre qualifiee explicitement : vide normal ou vide anormal

## Regles de travail

- ne pas corriger des tags au hasard sans les rattacher a une famille d'objets
- traiter separement les 3 couches :
  - catalogue UI des cles
  - substitutions scalaires `{...}`
  - segments / tableaux `line_*`
- toute correction doit etre rattachee a une source de verite :
  - CSV utilisateurs
  - code reel du moteur
  - donnees reellement chargees par l'objet source

## Chantiers

### 0. Cadrage et inventaire

Statut : `done`

But :

- figer une cartographie fiable du moteur avant corrections massives

Actions :

- identifier toutes les sources de cles listees dans l'UI
- identifier toutes les sources de cles substituees au runtime
- identifier toutes les sources de cles de segments
- consolider les CSV `csvFocomed`
- distinguer vrais tags, faux positifs, placeholders et constantes globales
- fiabiliser le harnais de smoke test pour eviter les faux positifs de validation

Livrables :

- matrice `cle -> visible -> substituable -> segment -> contexte -> source`
- classification `vide normal` / `vide anormal`
- rapports prioritaires de correction
- harnais de smoke test `element_type` par `element_type`

Definition of done :

- on peut expliquer pour n'importe quelle cle d'ou elle vient et comment elle est remplacee
- on dispose d'un harnais de smoke test reproductible par `element_type`

Etat courant :

- inventaires UI/runtime/segments en place
- runner `docedit_model_smoke_runner.php` en place
- batch supervisor `docedit_model_smoke_batch.php` en place
- mode chunk en place dans le batch pour reduire les reboots Dolibarr
- faux succes batch corriges :
  - `exit 0` sans `summary.csv` est maintenant detecte comme `runner_no_summary`
  - `type_unavailable_in_runtime` est maintenant distingue d'un vrai echec
- runner multi-types en place :
  - `--types=a,b,c`
  - `--limit`
  - `--offset`
- couverture smoke brute terminee sur les `44` `element_type` inventories
- etat consolide courant :
  - `44 ok`
  - `0 skip`
  - `0 skipped`
- correctifs structurels valides :
  - contexte `Formation` Agefodd charge correctement
  - contexte trainer Agefodd charge correctement
  - sample `order_supplier` cree et valide par smoke
  - modules standards necessaires actives proprement sur l'entite courante
  - samples standards crees pour `order`, `supplier_proposal`, `expedition` / `shipping` et `fichinter`
  - plusieurs gardes de robustesse ont ete ajoutes sur les extrafields et acces partiels
- limite restante :
  - l'entree CLI directe reste moins fiable que l'appel in-process
  - le batch in-process reste donc le chemin officiel de validation
  - le `Failed to connect` direct a ete requalifie comme un sujet infrastructure / sandbox, pas comme un trou fonctionnel `referenceletters`

Conclusion d'etape :

- le cadrage et l'inventaire sont maintenant suffisants pour passer au traitement fonctionnel
- le reliquat non `ok` ne correspond plus a un ecart massif du moteur `referenceletters`
- le prochain travail utile est de traiter les cas reels de donnees manquantes et la validation fonctionnelle fine des cles

### 1. Stabilisation du catalogue utilisateur

Statut : `done` pour le runtime actif

But :

- faire en sorte que la liste DocEdit soit un reflet fiable de ce que l'outil sait faire

Actions :

- auditer `ReferenceLetters::getSubtitutionKey()`
- auditer `completeSubtitutionKeyArrayWithAgefoddData()`
- lister les ecarts entre cles runtime et cles UI
- ajouter les cles manquantes pertinentes
- supprimer ou requalifier les cles trompeuses

Points critiques :

- Agefodd est aujourd'hui documente de facon trop manuelle
- les cles `objvar_*` et les extrafields ne sont pas correctement refletes dans le catalogue

Definition of done :

- une cle pertinente et fonctionnelle est visible
- une cle non pertinente n'est pas mise en avant comme si elle etait garantie

Etat courant :

- verifie par `build_active_type_ui_matrix.php` :
  - les `44` types actifs n'ont plus d'ecart `UI visible -> runtime/segment non detecte`
  - les faux placeholders globaux Agefodd et AgefoddCertificat ont ete retires du catalogue DocEdit lorsqu'ils n'etaient pas supportes par le runtime `referenceletters`
- audit inverse ajoute via `build_active_type_runtime_ui_matrix.php` :
  - le runtime expose plus de cles que l'UI actuelle
  - l'ordre de grandeur brut observe est `18833` tags runtime non visibles
  - la majeure partie du volume est constituee de `objvar_*` et de placeholders globaux externes
  - le reliquat vraiment actionnable doit etre qualifie avant exposition UI
  - correctifs deja appliques depuis cet audit :
    - `getSubtitutionKey()` expose desormais `referenceletters_*` meme sans instance DocEdit preexistante
    - `getSubtitutionKey()` injecte maintenant les substitutions contextuelles de `Other` (`objets_lies*`, `tva_detail_*`, etc.) pour l'objet courant
    - `getSubtitutionKey()` ne se limite plus a `RefLtrNoneExists` pour les types standards sans donnees d'exemple quand un objet vide suffit a enumerer les cles
    - `getSubtitutionKey()` injecte aussi un tiers generique `cust_*` pour les types standards sans objet d'exemple, afin de rendre `cust_company_*` visibles
    - les groupes Agefodd de l'UI ont ete renommes avec des libelles metier explicites :
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
    - ces groupes sont maintenant scopes par type de document :
      - `rfltr_agefodd_formation` n'affiche plus que `Agefodd Formation catalogue`
      - les documents session affichent les groupes session/liste des participants/liste des etapes/liste des horaires/liste des formateurs/lignes financieres/objectifs
      - les documents `*_trainee` affichent en plus `Agefodd Stagiaire courant`
      - `rfltr_agefodd_convention` affiche en plus `Agefodd Convention`
      - `rfltr_agefodd_mission_trainer` et `rfltr_agefodd_contrat_trainer` affichent en plus `Agefodd Formateur mission` et `Agefodd Agenda formateur`
      - le groupe standard `RefLtrLines` n'est plus affiche sur les documents Agefodd
    - plusieurs cles Agefodd runtime residuelles ont ete remontees explicitement dans l'UI :
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
      - les temps `time_stagiaire_*`
      - les segments critiques `line_fin_*`, `line_objpeda_*`, `line_statut`, `line_presence_*`, `line_time_*`
    - le groupe standard `RefLtrLines` est de nouveau visible sur les documents standards ; il etait defini au mauvais endroit (dans la construction Agefodd), ce qui masquait toutes les cles de lignes sur `invoice`, `order`, `contract`, `propal`, etc.
    - les contacts externes detailles `cust_contactclient_<CODE>_1_*` sont maintenant remontes pour les types de contacts standards via un contact statique de reference
    - les extrafields tiers `cust_company_options_*` sont maintenant remontes en UI quand le tiers lie du sample les expose
    - un socle generique de cles runtime standard a ete ajoute a l'UI pour eviter du hardcode dispersé :
      - `devise_label`
      - `object_tracking_number`
      - `object_total_weight`
      - `object_total_volume`
      - `object_total_qty_ordered`
      - `object_total_qty_toship`
    - les options de lignes / sous-totaux sont maintenant exposees :
      - `line_options_show_total_ht`
      - `line_options_show_reduc`
      - `line_options_subtotal_show_qty`
    - les suffixes date d'extrafields tiers `cust_company_options_*_locale` et `cust_company_options_*_rfc` sont maintenant generes de facon generique
    - les derniers scalaires Agefodd session encore manquants ont ete rattaches aux bons groupes metier :
      - `stagiaire_presence_*`
      - `stagiaire_temps_*`
      - `trainer_cost_planned`
      - `trainer_datehourtextline`
      - `trainer_datetextline`
      - `date_ouverture`
      - `date_ouverture_prevue`
      - `date_fin_validite`
  - controles cibles valides :
    - `invoice` expose maintenant `referenceletters_*`, `object_date`, `cust_contactclient*`, `objets_lies`, `tva_detail_*`
    - `rfltr_agefodd_attestation` et `rfltr_agefodd_formation` exposent maintenant `referenceletters_*`, `objets_lies` et `tva_detail_*`
  - consequence :
    - l'audit inverse brut historique n'est plus la photo exacte de l'etat courant
    - le wrapper `runtime_ui_matrix_batch.php` existe toujours mais l'entree CLI directe retape encore l'instabilite DB du bootstrap Dolibarr
    - le chemin fiable actuel pour rerouler l'audit inverse complet est l'execution in-process via la bibliotheque du batch
    - dernier etat consolide obtenu ainsi :
      - `44` types actifs audites
      - `69470` tags runtime inventories
      - `23019` tags runtime non visibles en UI
      - `0` candidat reel restant a qualifier pour exposition UI
    - les faux candidats suivants sont maintenant explicitement filtres de la worklist :
      - pseudo-tags de contacts externes contenant `[...]`
      - extrafields tiers fantomes encore presents dans certains objets runtime mais absents de `llx_extrafields`

Point de vigilance :

- cette etape est consideree comme terminee pour le runtime actif et le catalogue DocEdit courant
- elle ne remplace pas la validation fonctionnelle metier fine sur les modeles clients reels
- elle ne signifie pas que l'UI liste deja toutes les cles accessibles via les objets

### 2. Stabilisation du moteur scalaire

Statut : `pending`

But :

- fiabiliser toutes les substitutions `{...}` hors segments

Actions :

- auditer `setSubstitutions()`
- couvrir proprement :
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
- identifier les cles listees mais jamais peuplees

Definition of done :

- plus aucune cle scalaire visible ne reste non convertie quand la donnee pertinente existe

### 3. Stabilisation des segments et boucles

Statut : `pending`

But :

- fiabiliser les tags de tableaux et les blocs repetes

Actions :

- auditer `merge_array()`
- auditer `get_substitutionarray_lines()`
- auditer `get_substitutionarray_lines_agefodd()`
- cartographier tous les tableaux iterables :
  - lignes standard Dolibarr
  - participants
  - horaires
  - formateurs
  - objectifs pedagogiques

## Etat de sortie actuel

Etat honnete du chantier a ce stade :

- le moteur et le catalogue sont beaucoup plus propres qu'au depart
- la distinction `champ direct / dans une liste / technique` est maintenant visible dans la popin
- les boucles `BEGIN/END` sont exposees par type de document dans une section dediee
- les constantes techniques `__[... ]__` restent disponibles, mais hors des groupes metier
- la presentation n'ecrase plus aveuglement certaines descriptions metier Agefodd deja plus justes que le fallback generique

Limites restantes :

- la popin reste dense
- l'UX est fonctionnelle, pas encore "finition produit"
- certaines familles Agefodd restent lourdes a lire
- l'entree CLI directe reste non fiable comme chemin officiel de validation

Decision recommandee de maintenance :

- arreter les grosses refontes structurelles
- garder les scripts de validation durables
- considerer les scripts d'audit comme one-shot / historiques
- concentrer la suite sur :
  - la verification UI reelle sur quelques documents critiques
  - les derniers ajustements de libelles metier
  - la stabilisation, pas une nouvelle refacto lourde

## Classement recommande des scripts

### Validation durable

- `docedit_model_smoke_runner.php`
- `docedit_model_smoke_batch.php`
- `validate_real_models.php`
- `catalog_non_regression.php`
- `catalog_non_regression_batch.php`
- `report_unresolved_placeholders.php`

### Audit ponctuel

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

### Support / preparation

- `activate_required_smoke_modules.php`
- `ensure_standard_smoke_samples.php`
- `ensure_supplier_order_sample.php`
- `migrate_model_to_extrafields.php`
- `create-maj-base.php`
- `interface.php`
- `urlMover.php`

### Debug historique

- `debug_cli_bootstrap.php`
  - lignes financieres
  - etapes
- identifier les tableaux charges mais non merges

Definition of done :

- les segments affichent correctement les cles `line_*`
- les tableaux attendus sont reels et exploitables dans les templates

### 4. Couverture extrafields

Statut : `pending`

But :

- rendre exhaustive la couverture des champs complementaires reellement presents en base

Actions :

- auditer le support existant des extrafields dans :
  - core `CommonDocGenerator`
  - `fill_substitutionarray_with_extrafields()`
  - `get_substitutionarray_each_var_object()`
  - `get_substitutionarray_lines_agefodd()`
- verifier objet par objet :

### 9. Refacto cible du catalogue UI

Statut : `in_progress`

But :

- rendre le catalogue DocEdit durable face aux evolutions du core, d'Agefodd et des extrafields
- eviter de devoir enrichir `getSubtitutionKey()` a la main a chaque evolution

Pourquoi ce n'etait pas traite immediatement :

- la fiabilisation fonctionnelle etait prioritaire sur la refonte
- melanger correction du perimetre et redesign augmenterait fortement le risque de regression
- il fallait d'abord obtenir un etat fonctionnel propre et une base de non-regression credible

Decision lead dev :

- la lancer de facon progressive, sans remplacer brutalement le catalogue actuel
- garder les groupes metier existants comme socle stable
- utiliser la decouverte runtime comme fallback automatique

Direction d'architecture recommandee :

- conserver le runtime comme source de verite
- separer clairement :
  - la decouverte des cles disponibles
  - leur classification
  - leur presentation dans l'UI
- limiter le hardcode aux regroupements metier utiles a l'utilisateur final

Principe cible :

- `discovery` :
  - partir des tableaux reellement exposes par le moteur (`get_substitutionarray_*`, segments, extrafields, etc.)
- `grouping` :
  - classer automatiquement par familles (`object_*`, `cust_company_*`, `line_*`, `formation_*`, etc.)
- `visibility policy` :
  - distinguer ce qui doit etre :
    - visible par defaut
    - visible en mode avance
    - masque car trop technique
- `manual overrides` :
  - garder uniquement des exceptions UX/metier (notamment pour Agefodd)

Composants cibles proposes :

- `SubstitutionCatalogBuilder`
- `SubstitutionGroupingPolicy`
- `SubstitutionVisibilityPolicy`

Socle deja en place :

- `class/catalog/substitutioncatalogproviderinterface.class.php`
- `class/catalog/substitutioncatalogbuilder.class.php`
- `class/catalog/substitutioncatalogstandardprovider.class.php`
- `class/catalog/substitutioncatalogstandardscalarprovider.class.php`
- `class/catalog/substitutioncatalogdocumentlineprovider.class.php`
- `class/catalog/substitutioncatalogcontactprovider.class.php`
- `class/catalog/substitutioncatalogthirdpartyprovider.class.php`
- `class/catalog/substitutioncatalogreferenceletterprovider.class.php`
- `class/catalog/substitutioncatalogagefoddprovider.class.php`
- `class/catalog/substitutioncatalogagefoddformationprovider.class.php`
- `class/catalog/substitutioncatalogagefoddsessionprovider.class.php`
- `class/catalog/substitutioncatalogagefoddtraineeprovider.class.php`
- `class/catalog/substitutioncatalogagefoddtrainerprovider.class.php`
- `class/catalog/substitutioncatalogagefoddconventionprovider.class.php`
- `class/catalog/substitutioncataloggroupingpolicy.class.php`
- `class/catalog/substitutioncatalogvisibilitypolicy.class.php`
- `ReferenceLetters::getSubtitutionKey()` appelle maintenant le builder en fin de construction
- le builder orchestre maintenant une liste de providers conformes a `SubstitutionCatalogProviderInterface`
- le builder expose maintenant une metadonnee minimale par cle detectee (`source`, `classification`, `visibility`, `group_label`)
- le builder delegue les familles standard a `SubstitutionCatalogStandardProvider`, lui-meme decoupe en sous-providers
- le builder delegue les groupes Agefodd stables a `SubstitutionCatalogAgefoddProvider`, qui orchestre maintenant `formation`, `session`, `trainee`, `trainer` et `convention` via des sous-providers dedies
- les cles disponibles non encore gouvernees remontent maintenant dans des groupes `avance`
- les cles sensibles / purement techniques sont filtrees par la policy de visibilite
- le socle `class/catalog/*` est maintenant type dans ses signatures compatibles PHP 7.4, documente avec PHPDoc et commentaires courts en anglais
- une premiere famille standard a deja bascule vers le builder :
  - `devise_label`
  - `object_tracking_number`
  - `object_total_weight`
  - `object_total_volume`
  - `object_total_qty_ordered`
  - `object_total_qty_toship`
- d'autres familles generiques ont aussi bascule :
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

Benefices attendus :

- nouvelles cles runtime visibles sans retouche manuelle systematique
- moins de dette dans `ReferenceLetters::getSubtitutionKey()`
- meilleure resilience aux evolutions d'Agefodd, du core et des extrafields
- separation plus propre des responsabilites (`SOLID`, `DRY`, `KISS`)

Reste a faire :

1. extraire progressivement la logique historique de `getSubtitutionKey()` vers le builder
2. faire basculer davantage de familles standards vers la decouverte runtime
3. mieux separer `user` / `advanced` sur les familles Agefodd et `objvar_*`
4. figer une non-regression sur les groupes/libelles actuels utiles

Definition of done :

- tous les extrafields reellement disponibles en base sur les objets cibles sont exploitables

### 5. Couverture des objets Agefodd

Statut : `pending`

But :

- fiabiliser le contrat de donnees entre Agefodd et `referenceletters`

Actions :

- auditer `load_all_data_agefodd()`
- auditer `Formation::load_all_data_agefodd()`
- verifier pour chaque famille de document :
  - session / convention
  - participant
  - formateur
  - formation
  - certificats
- identifier les donnees chargees mais jamais exposees
- identifier les donnees exposees mais jamais chargees

Definition of done :

- les objets exposes a `referenceletters` portent bien les donnees necessaires aux cles promises

### 6. Variables calculees et regles metier

Statut : `pending`

But :

- couvrir proprement les substitutions qui ne sont pas de simples lectures de champ

Actions :

- identifier les compteurs et valeurs filtrees par statut
- traiter explicitement les cas du type :
  - effectif inscrit
  - effectif present
  - effectif confirme
  - temps realise
  - temps attendu
- valider les cas "vide normal"

Definition of done :

- les variables metier importantes sont calculees selon une regle explicite et verifiable

### 7. Validation croisee avec les CSV

Statut : `done` pour la qualification technique

But :

- fermer l'ecart entre le moteur, l'UI et les tests utilisateurs

Actions :

- croiser les cles avec :
  - `Variables de substitution Doc Edic - Liste de toutes les variables.csv`
  - `Variables de substitution Doc Edic - Forcomed.csv`
  - `Variables de substitution Doc Edic - Forco FMC.csv`
- qualifier chaque ligne :
  - OK
  - vide normal
  - vide anormal
  - non pertinente
  - non listee
  - non supportee

Definition of done :

- chaque cle des CSV a une qualification technique claire

Etat courant :

- rapport technique exploitable :
  - `custom/referenceletters/csvFocomed/referenceletters_initial_docs_comparison.csv`
  - `custom/referenceletters/csvFocomed/referenceletters_initial_docs_comparison_summary.csv`
- dernier etat consolide :
  - `1207` entrees CSV analysees
  - `1116` `covered_ui`
  - `58` `excluded_environment_constant`
  - `10` `excluded_false_positive`
  - `1` `excluded_legacy`
  - `18` `excluded_legacy_dynamic`
  - `4` `excluded_mail_placeholder`
- conclusion :
  - plus de reliquat `missing_review` dans cette comparaison
  - les CSV initiaux sont maintenant qualifies de facon exploitable
  - l'ancien `audit_substitutions_report.csv` reste utile comme photo brute historique, mais ne doit plus etre pris comme source de verite finale

### 8. Documentation finale et maintenance

Statut : `pending`

But :

- laisser une doc exploitable pour la maintenance continue

Actions :

- maintenir ce fichier a jour
- maintenir le README
- ajouter si besoin des scripts d'audit reproductibles
- consigner les decisions sur les cles non pertinentes ou volontairement vides

Definition of done :

- une equipe peut reprendre le chantier sans reverse engineering supplementaire

## Suivi des lots

### Lot 1

Statut : `in_progress`

Fait :

- cartographie UI/runtime/segments produite par scripts
- rapports CSV d'ecarts et de priorisation disponibles
- harnais `script/docedit_model_smoke_runner.php` ajoute
- generation automatique de modeles DocEdit temporaires de smoke
- rendu reel via `setSubstitutions()` + `merge_array()`
- scan automatique des placeholders non resolus
- validation ciblee confirmee sur `thirdparty` : `0` placeholder non resolu

Reste a faire dans ce lot :

- rendre le run global batch totalement exploitable sans blocage runtime Agefodd
- qualifier et corriger les warnings/fatals heritage qui apparaissent en mode CLI
- ajouter ensuite la tentative de PDF reelle sur les types batchables sans bruit parasite

Points durs identifies :

- warnings multicompany au bootstrap CLI
- warnings extrafields dans `get_substitutionarray_each_var_object()`
- chargements Agefodd qui restent fragiles en batch
- certaines classes PDF legacy standard incompatibles PHP 8 pour un audit batch si on les charge directement

Perimetre :

- analyse structurelle du module
- distinction listing / runtime / segments
- creation du socle documentaire et du plan de correction

Fait :

- README technique du module
- audit du flux Agefodd -> ReferenceLetters
- identification du moteur de listing
- identification du moteur de substitution
- identification du moteur de segments
- creation de ce fichier de suivi
- script d'inventaire runtime des `element_type` disponibles
- scripts d'inventaire des cles UI, runtime et segments
- script de matrice d'ecart CSV/UI/runtime/segments
- rapport prioritaire des cles a traiter
- rapport des `element_type` Agefodd suspects
- batch smoke DocEdit repris en execution in-process via `smoke_run_from_args()`
- mode chunk en place dans le batch pour reduire les reboots Dolibarr
- mode reprise en place dans le batch : `--start-type`, `--offset`, `--resume-from`, `--skip-done`
- script de diagnostic `debug_cli_bootstrap.php` ajoute pour isoler les erreurs avant/apres `main.inc.php`

Reste pour cloturer le lot :

- qualifier manuellement les `not_covered` entre faux positifs, non pertinents et vraies lacunes
- lancer le premier lot de corrections structurelles

Etat chiffre actuel :

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

Rapports de travail immediat :

- `custom/referenceletters/csvFocomed/referenceletters_priority_keys.csv`
- `custom/referenceletters/csvFocomed/referenceletters_suspicious_element_types.csv`
- `custom/referenceletters/csvFocomed/referenceletters_not_covered_worklist.csv`
- `custom/referenceletters/csvFocomed/referenceletters_not_covered_summary.csv`

Priorites confirmees par les rapports :

- ajouter des smoke tests documentaires a base de scan de placeholders non resolus
- arbitrer les `7` entrees `not_covered` restantes
  - `4` placeholders mail Agefodd hors perimetre `referenceletters`
  - `2` tokens de format CSV `mm` / `yy`
  - `1` ancien tag legacy `line_civilitel`

## Journal de mise a jour

- 2026-03-03 : debut de refacto du catalogue UI avec ajout de `SubstitutionCatalogBuilder`, `SubstitutionCatalogGroupingPolicy` et `SubstitutionCatalogVisibilityPolicy`
- 2026-03-03 : `getSubtitutionKey()` complete maintenant automatiquement les cles disponibles manquantes dans des groupes `avance`
- 2026-03-03 : premiere extraction d'une famille standard hors de `ReferenceLetters` vers `SubstitutionCatalogBuilder` (`devise_label`, `object_tracking_number`, `object_total_*`)
- 2026-03-03 : extraction supplementaire vers `SubstitutionCatalogBuilder` des lignes standard, contacts externes et extrafields tiers
- 2026-03-03 : extraction supplementaire vers `SubstitutionCatalogBuilder` du groupe DocEdit `referenceletters_*` et du nettoyage des placeholders globaux exclus
- 2026-03-03 : extraction d'une premiere sous-famille Agefodd stable vers `SubstitutionCatalogBuilder` (`Agefodd Formation catalogue`, `Agefodd Convention`, `Agefodd Agenda formateur`)
- 2026-03-03 : extraction complementaire de `Agefodd Formateur mission` vers `SubstitutionCatalogBuilder`
- 2026-03-03 : extraction complementaire des groupes Agefodd stables `Agefodd Liste des etapes`, `Agefodd Etape courante`, `Agefodd Liste des horaires`, `Agefodd Liste des formateurs`, `Agefodd Lignes financieres session` et `Agefodd Liste des objectifs pedagogiques` vers `SubstitutionCatalogBuilder`
- 2026-03-03 : extraction complementaire des groupes `Agefodd Liste des participants` et `Agefodd Stagiaire courant` vers `SubstitutionCatalogBuilder`, avec conservation des extrafields stagiaire/societe et des variantes certificat
- 2026-03-03 : extraction partielle du groupe `Agefodd Session courante` vers `SubstitutionCatalogBuilder` pour le noyau stable (identite session/formation, dates, commercial, lieu, accessibilite, referents)
- 2026-03-03 : extraction complementaire dans `Agefodd Session courante` des agrégats `stagiaire_*` / `time_stagiaire_*`, du bloc prestataire `presta_*` et des textes d'etapes `objvar_object_steps_*`
- 2026-03-03 : extraction complementaire dans `Agefodd Session courante` des textes formateur / horaires / identifiant de session (`objvar_object_trainer_*`, `trainer_*`, `objvar_object_dthour_text`, `objvar_object_id`)
- 2026-03-03 : introduction de `SubstitutionCatalogAgefoddProvider` et delegation du catalogue Agefodd stable hors de `SubstitutionCatalogBuilder`
- 2026-03-03 : introduction de `SubstitutionCatalogStandardProvider` et delegation des familles standard hors de `SubstitutionCatalogBuilder`
- 2026-03-03 : introduction de `SubstitutionCatalogProviderInterface` et passage du builder en orchestrateur de providers, sans rupture de l'API historique de `ReferenceLetters`
- 2026-03-03 : decoupage de `SubstitutionCatalogStandardProvider` en sous-providers par famille (`standard scalar`, `document line`, `contact`, `thirdparty`, `referenceletter`)
- 2026-03-03 : ajout d'une metadonnee minimale de catalogue dans `SubstitutionCatalogBuilder` pour les cles detectees (`source`, `classification`, `visibility`, `group_label`)
- 2026-03-03 : ajout de PHPDoc types et commentaires courts en anglais sur le socle `class/catalog/*`, plus clarification interne de `SubstitutionCatalogAgefoddProvider` par helpers de sous-familles
- 2026-03-03 : conservation de la compatibilite PHP 7.4 sur le socle catalogue, suppression du `mixed` natif, et extraction des sous-providers Agefodd `formation`, `trainer`, `convention`
- 2026-03-03 : extraction finale des sous-providers Agefodd `session` et `trainee`, `SubstitutionCatalogAgefoddProvider` devient un orchestrateur complet des familles Agefodd
- 2026-03-03 : validation apres ce lot : `php -l` OK sur tout `class/catalog/*` et smoke batch in-process `contact`, `contract`, `expedition` = `ok`
- 2026-03-03 : ajout du garde-fou `catalog_non_regression.php` / `catalog_non_regression_batch.php` pour figer et comparer snapshots de catalogue ; `catalog_non_regression_batch.php` travaille maintenant par chunks, mais l'execution reste encore limitee par l'instabilite DB CLI de l'environnement
- 2026-03-03 : correction de robustesse Agefodd sur les etapes vides (`step_*`) dans `get_substitutionsarray_agefodd()`
- 2026-03-03 : correction du warning Agefodd sur `$agfStep` non initialise dans `get_substitutionsarray_agefodd()`
- 2026-03-03 : correction de robustesse dans `commondocgeneratorreferenceletters.class.php` sur le calcul facture (`multicurrency`, `deja_regle`, `creditnoteamount`, `depositsamount`)
- 2026-03-03 : ajout du script `validate_real_models.php` pour valider les vrais modeles DocEdit actifs avec le vrai runtime `referenceletters`
- 2026-03-03 : durcissement de `validate_real_models.php` avec capture des warnings dans des rapports dedies (`warnings.csv`, `warnings.log`) pour separer le bruit technique du resultat de substitution
- 2026-03-03 : correction de robustesse generique dans `CommonObject::fetch_optionals()` pour ne pas supposer l'existence du bloc d'extrafields d'un element
- 2026-03-03 : correction d'un faux positif majeur du validateur reel : restriction aux modeles visibles depuis l'entite courante, afin d'eviter les fallbacks multi-entity trompeurs
- 2026-03-03 : correction des alias legacy reveles par la validation reelle :
  - `formation_prerequis`
  - `formation_programme`
  - `formation_type_stagiaire`
  - `formation_moyens_pedagogique`
  - `objvar_object_stagiaire_rpps`
  - `objvar_object_stagiaire_soc_options_rpps`
  - `objvar_object_stagiaire_soc_options_adeli`
- 2026-03-03 : resultat de validation metier reelle sur l'entite courante :
  - `13/13` modeles actifs `ok`
  - `0` placeholder non resolu
  - `5376` warnings non bloquants captures, principalement sur `rfltr_agefodd_convocation`
- 2026-03-03 : lot de fermeture du reliquat `Agefodd` / renderer :
  - normalisation cote `Agsession::load_all_data_agefodd()` des lignes stagiaires, etapes, formateurs et lignes financieres pour DocEdit
  - durcissement de `get_substitutionarray_lines_agefodd()` contre les objets de lignes heterogenes
  - durcissement de `subtotal` sur `special_code` absent
  - durcissement de `CommonDocGenerator::get_substitutionarray_thirdparty()` et `get_substitutionarray_contact()` quand aucun objet n'est disponible
  - gardes complementaires sur `formation_obj_peda` et sur les extrafields de formation absents
- 2026-03-03 : revalidation metier reelle apres ce lot :
  - `13/13` modeles actifs `ok`
  - `0` placeholder non resolu
  - `0` warning
- 2026-03-03 : ajout d'une couche de presentation UI du catalogue (`SubstitutionCatalogPresentationBuilder`) pour remplacer l'usage trompeur de la colonne `Value` par des metadonnees `description` / `format_hint`
- 2026-03-03 : branchement de cette presentation dans `FormReferenceLetters` :
  - tableau avance : `Description / Tag / Format`
  - tableau simple : `Description / Tag / Format`
- 2026-03-03 : la presentation est generee par regles a partir des tags (`object_*`, `cust_company_*`, `cust_contactclient_*`, `line_*`, `formation_*`, etc.) pour eviter une dette de libelles geres manuellement cle par cle
- 2026-03-03 : le builder de presentation charge explicitement `refflettersubtitution@referenceletters` et privilegie les traductions `reflettershortcode_*` avant le fallback genere
- 2026-03-03 : ajout du script `compare_initial_csv_docs.php` pour comparer les CSV initiaux a l'etat reel UI/runtime courant
- 2026-03-03 : comparaison CSV initiale consolidee : `1116` `covered_ui`, `58` constantes d'environnement historiques, `10` faux positifs, `1` legacy, `18` legacy dynamiques, `4` placeholders mail hors perimetre
- 2026-03-03 : ajout du script `build_active_type_ui_matrix.php` pour auditer le catalogue UI reel par `element_type` actif
- 2026-03-03 : ajout des patterns runtime dynamiques `cust_company_options_*` et `cust_contactclient_*` dans `inventory_runtime_keys.php`
- 2026-03-03 : retrait des 4 placeholders mail Agefodd du catalogue DocEdit `referenceletters` (`__AGENDATOKEN__`, `__FORMDATESESSION__`, `__FORMINTITULE__`, `__TRAINER_1_EXTRAFIELD_XXXX__`)
- 2026-03-03 : validation du catalogue UI actif apres rerun : `34` types `ok`, `0` cle visible restante non detectee cote runtime/segments
- 2026-03-03 : ajout du script `ensure_supplier_order_sample.php` puis creation/validation d'un sample `order_supplier`
- 2026-03-03 : durcissement du core `commondocgenerator.class.php` sur les acces non gardes aux modes/conditions de reglement et aux coordonnees bancaires
- 2026-03-02 : creation du plan de suivi et cadrage des chantiers
- 2026-03-02 : ajout du script `script/inventory_element_types.php`
- 2026-03-02 : ajout des scripts `inventory_ui_keys.php`, `inventory_runtime_keys.php`, `inventory_segment_keys.php`
- 2026-03-02 : ajout du script `build_gap_matrix.php`
- 2026-03-02 : ajout du script `build_priority_reports.php`
- 2026-03-02 : ajout du script `report_unresolved_placeholders.php`
- 2026-03-02 : correction du mapping `element_type_list` Agefodd dans `referenceletters.class.php`
- 2026-03-02 : correction du catalogue Agefodd `line_civilite` / `line_civilite_short`
- 2026-03-02 : ajout de `line_step_lieu_notes` dans `get_substitutionarray_lines_agefodd()`
- 2026-03-02 : mise a jour des scripts d'inventaire pour couvrir `complete_substitutions_array()` et refleter le mapping Agefodd reel
- 2026-03-02 : mise a jour des scripts d'inventaire UI/runtime pour couvrir les familles dynamiques `formation_options_*`, `line_options_*`, `line_societe_options_*`, `objvar_object_stagiaire_*`
- 2026-03-02 : alignement des patterns globaux `__[XXX]__` dans les inventaires UI/runtime
- 2026-03-02 : ajout du script `build_not_covered_worklist.php` pour qualifier le reliquat `not_covered`
- 2026-03-02 : refonte du batch smoke DocEdit pour reutiliser le runner dans le meme process PHP
- 2026-03-02 : ajout du mode reprise batch `--start-type`, `--offset`, `--resume-from`, `--skip-done`
- 2026-03-02 : correction du mode reprise batch pour sortir proprement quand toute la selection est deja couverte
- 2026-03-02 : validation du mode reprise sur CSV existant, avec blocage runtime toujours intermittent au bootstrap Dolibarr CLI (`Failed to connect`)
- 2026-03-02 : ajout du script `debug_cli_bootstrap.php`, avec confirmation d'un probleme de session CLI sur `/var/lib/php/sessions` en plus du `Failed to connect`
- 2026-03-02 : correction du warning `multicompany` sur `Undefined array key "type"` pour les partages externes Agefodd sans metadonnees `type`
- 2026-03-02 : forçage de `session.save_path=/tmp` dans les scripts CLI `referenceletters`, suppression du bruit session, mais `Failed to connect` encore present sur certains lancements directs
- 2026-03-02 : verification que `smoke_run_from_args()` fonctionne bien via inclusion bibliotheque du runner, y compris avec sortie active ; l'asymetrie restante porte donc sur l'entree CLI directe du fichier runner
- 2026-03-02 : correction d'une regression du batch smoke DocEdit ; le runner doit etre charge au scope fichier et non depuis l'interieur d'une fonction, sinon le bootstrap Dolibarr se fait dans une portee PHP locale et casse la creation de la connexion DB
- 2026-03-02 : revalidation du batch court apres cette correction : `contact=ok`, `contract=ok`, `expedition=skip`
- 2026-03-02 : definition de `SYSLOG_FILE_NO_ERROR=1` dans les scripts smoke/debug pour supprimer le bruit parasite `Failed to open log file`
- 2026-03-02 : validation d'une premiere tranche large en mode bibliotheque batch (`limit=10`) : `6 completed:ok`, `1 completed:skipped`, `3 skipped:skip`
- 2026-03-02 : validation d'une deuxieme tranche large en mode bibliotheque batch (`offset=10`, `limit=10`) : `6 completed:ok`, `4 skipped:skip`
- 2026-03-02 : validation d'une troisieme tranche large en mode bibliotheque batch (`offset=20`, `limit=10`) : `8 completed:ok`, `2 completed:skipped`
- 2026-03-02 : validation d'une quatrieme tranche pour finir la liste courante (`offset=30`, `limit=10` puis `offset=40`, `limit=10`) : `8 completed:ok`, `2 completed:skipped`, puis `1 completed:ok`, `1 completed:skipped`, `2 skipped:skip`
- 2026-03-02 : les `44` `element_type` inventories ont ete passes par une campagne smoke reelle, sans `error` runtime sur le moteur de substitutions ; le prochain sujet n'est plus la couverture brute, mais la qualification des `skip`/`skipped` et la validation fonctionnelle plus fine
- 2026-03-02 : ajout du script `aggregate_smoke_batch_results.php` pour consolider le dernier statut disponible par `element_type`
- 2026-03-02 : ajout du script `build_smoke_followup_worklist.php` pour qualifier les cas non `ok`
- 2026-03-02 : qualification actuelle des 15 cas non `ok` :
  - `9` types absents du runtime courant car modules parents desactives (`expedition`, `shipping`, `fichinter`, `commande`, `supplier_proposal`, `agefoddcertificat`)
  - `6` cas lies a l'absence de donnees ou de contexte d'echantillon (`order_supplier`, formations Agefodd, contextes trainer)
- 2026-03-02 : correction d'un warning Agefodd sur `needforkey` non defini dans `functions_agefodd.lib.php`
- 2026-03-02 : correction d'un warning `referenceletters` sur acces a des cles `array_options` absentes dans `commondocgeneratorreferenceletters.class.php`
- 2026-03-02 : correction de warnings `referenceletters` sur les extrafields `select`, sur `remise_percent` absent et sur certaines proprietes de lignes de contrat non garanties
