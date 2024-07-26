# Change Log
All notable changes to this project will be documented in this file.

### UNRELEASED
- NEW : Compat V20 et php 8.2 - *26/07/2024* - 2.22.0


## Release 2.21 - 13/05/2024

- FIX : Retour sécuridis BUG enregeistrement PDF lié à PropalHistory et son changement de ref propal - *14/06/2024* - 2.21.4
- FIX : DA025049 Erreur d'affichage sur l'onglet modéle de document sur fiche commande fournisseur - *12/06/2024* - 2.21.3
- FIX : Clés manquantes : financeurs et financeur_alternatif - *05/06/2024* - 2.21.2
- FIX : DA024980 SQL Error : renamed $obj to $object - *24/05/2024* - 2.21.1
- NEW : Ajout de boucles TSteps, TStepsPrésentiel, TStepsDistanciel - *03/05/2024* - 2.21.0
  - Création des clés permettant l'affichage des étapes avec/sans créneaux horaires présentielles et/ou distancielles
- NEW : Ajout de la gestion d'un modèle de formation orphelin (non rattaché à une session) - *30/04/2024* - 2.21.0  
- NEW : Hooks enabling other modules to add new DocEdit-managed document 
  types (and one day perhaps get rid of Agefodd-specific code in DocEdit) 
  *15/09/2022* 2.20.0
- NEW : Add certificat_completion  generation for trainee docs / docs  - 
*16/04/2024* - 2.20.0  

## Release 2.19 - 12/02/2024
- FIX : DA024939 - Handle break page with subtotal - *17/05/2024* - 2.19.7
- FIX : DA024875 some keys were missing  - *02/05/2024* - 2.19.6
- FIX : DA024820 trainee extrafields wasn't working properly  - *02/05/2024* - 2.19.5
- FIX : DA024774 missing phone_perso & phone_mobile key  - *23/04/2024* - 2.19.3
- FIX : DA024698/DA024701 docedit pdf generation when creating objects  - *21/03/2024* - 2.19.2
- FIX : DA024630 warning  - *21/03/2024* - 2.19.2  
- FIX : fix line mail to line_email  in referenceletter.class  - *12/03/2024* - 2.19.1  
- NEW : Compatibility with agefodd step - *08/02/2024* - 2.19.0


## Release 2.18 - 10/01/2024
- NEW : COMPATV19 - *11/12/2023* - 2.18.0  
    - Changed Dolibarr compatibility range to 12 min - 19 max  
    - Change PHP compatibility range to 7.0 min - 8.2 max
- NEW : Add substitution in agefodd to be able to use trainer cost planned *21/03/2022* - 2.18.0

## Release 2.17 - 29/11/2023
- FIX : DA024954 - Certif Code de type price - *07/05/2024* 2.17.2

- FIX : DA024195 - z-index select ajout de modele pdf à concatener *29/11/2023* 2.17.1
- NEW : Compatibilité agefoddcertificat 1.12.0 *15/11/2023* 2.17.0
- NEW : move script url mover from abricot *11/05/2022* 2.16.0



## Release 2.15 - 21/07/2022
- FIX : Total TVA doesn't have $langs->trans - *22/01/2024* - 2.15.10
- FIX : DA024017 tms doesn't have a default value - *24/10/2023* - 2.15.9
- FIX : remove strip tags - *19/07/2023* - 2.15.8
- FIX : Compat V17 - *21/04/2023* - 2.15.7
- FIX : !empty - *29/03/2023* - 2.15.6
- FIX : Handle agefodd img training - *20/01/2023* - 2.15.5
- FIX : TEST ARRAY  - *23/11/2022* - 2.15.4
- FIX : TEST ARRAY  - *23/11/2022* - 2.15.3
- FIX : TEST ARRAY  - *23/11/2022* - 2.15.2  
- FIX : Compatibilité V16 : Ajout des tokens manquants pour bon fonctionnement des listes et des actions relatives à la création de nouveaux modèles de document *10/11/2022* 2.15.1
- NEW : Modification de l'icône du module *13/06/2022* 2.15.0
- NEW : Ajout de la class TechATM pour l'affichage de la page "A propos" *11/05/2022* 2.14.0

## Release 2.13 - 15/04/2022

- FIX : prod diff - add product label to substitution keys *28/02/2024* - 2.13.3
- FIX : Ajout de clés manquantes liés aux exped alors qu'elles sont dans le modele de base *19/07/2022* - 2.13.2
- FIX : Infinite loop and fail to generate good doc *26/04/2022* - 2.13.1
- NEW : Add new document manage by docedit : Intervention *15/04/2022* - 2.13.0
- NEW : Select the previous docedit document used by fiche *15/04/2022* - 2.12.0

## Release 2.11 - 25/03/2022
- FIX : Add substitution in agefodd to be able to use commercial mobile phone *04/04/2023* - 2.11.1
- NEW : Add substitution in agefodd to be able to use all presta soc std data *21/03/2022* - 2.11.0
- NEW : Add substitution in agefodd to be able to use presta firstname, lastname and soc *15/02/2022* - 2.10.0

## Release 2.9 - 14/06/2021

- FIX : Fix error to upload background - *28/09/2022* - 2.9.9
- FIX : Date de naissance sur attestation de fin de formation - *27/07/2022* - 2.9.8
- FIX : unlink docedit from core odf lib to prevent html conversion to odt *23/02/2022* - 2.9.7
- FIX : Fix selection refletters default model *17/02/2022* - 2.9.7
- FIX : Compatibility V15 : token CSRF on model header and footer form *20/12/2021* - 2.9.6
- FIX : Affichage sur des documents généré docedit qui ne se faisait pas a cause de mise en forme <strong> - *13/12/2021* - 2.9.5
- FIX : In v14, select_salesrepresentatives uses -1 as empty value, sql filters adjusted accordingly *08/09/2021* - 2.9.4 
- FIX : Change default rights to 0 *01/07/2021* - 2.9.3
- FIX : Compatibility V13 *17/05/2021* - 2.9.2
- FIX - Compatibility V14 : Edit the descriptor: family - *2021-06-10* - 2.9.1
- NEW : TK2003-0572 - Qualiopi Référents Ajouter les tags DocEdit qui permettent d'y avoir acces *04/06/2021* - 2.9

## Release 2.8 - 14/04/2021

-FIX : Remove dead links *14/04/2021* - 2.8.1
-NEW : Dolibarr V13 Box compatibility *02/04/2021* - 2.8.0

## Release 2.7 - 26/03/2021

-FIX : compatibity with Dolibarr V12 (supplier_order model path changed from "core/modules/supplier_order/pdf" to "core/modules/supplier_order/doc")
-FIX : Ajout tags formation_nb_place, formation_type_public, formation_moyens_pedagogique et formation_sanction + gestion tags extrafields multiselect - 2.7.2
-FIX : Generate "Fiche Pedago" custom for an agefodd session - 2.7.1

-NEW : Add idprof1 and idprof2 tag helper

# Release 2.0 - 06/04/2017

-FIX : Preselect model on documents if default - *09/02/2022* - 2.6.6
-FIX : Specify the condition that allows the use of agefodd substitution keys only in the case of an agefodd pdf *30/06/2021* - 2.6.5
-NEW : Add mass generation for invoice model letters
-FIX : V13 compatibility add newToken to some triggers links [2021-03-03]

## Release 1.9 - 14/11/2016

-NEW : Only for 4.0

***** ChangeLog for 1.8 compared to 1.7 *****
-FIX : Add customer order letters

***** ChangeLog for 1.7 compared to 1.5 *****
-FIX : PHP warining for PHP 7 and dolibarr 4.0

***** ChangeLog for 1.5 compared to 1.3 *****
-NEW : Admin option to create calendar event on each letter creation
-NEW : Admin option to copy generated letters as attachement of event
-NEW : Admin option to select label type of event 
-NEW : Admin option to output in PDF ref end title of letters
-NEW : Add option to output in PDF ref end title of letters on letter création
-NEW : Add background PDF in models 
-NEW : Add read only chapter option (cannot be seen/modified during letter creation)
-NEW : Add new tags : title, ref, model title of current letter
-NEW : Letters list with filter


***** ChangeLog for 1.3 compared to 1.2 *****
-NEW : Review french/english/spanish translation
-NEW : Add french/english/spanish documentation

***** ChangeLog for 1.2 compared to 1.1 *****
-NEW : Add break page option without repeat head
-FIX : tag type {cust_...} on contact letter works 

***** ChangeLog for 1.0 compared to 1.1 *****
-FIX : Cannot create models without title 

***** ChangeLog for 0.9 compared to 1.0 *****
-NEW : Add break page option
-FIX : Contact models output a contact adress
