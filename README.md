# ReferenceLetters

ReferenceLetters is a custom Dolibarr module for database-backed document
templates and runtime substitutions.

It provides:

- document models attached to supported object types
- runtime substitutions for standard Dolibarr objects and custom Agefodd flows
- PDF generation through the module document engine
- a DocEdit popup exposing scalar tags, repeated loops and advanced technical keys

## Main Entry Points

- [referenceletters.class.php](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/class/referenceletters.class.php)
- [referenceletters_tools.class.php](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/class/referenceletters_tools.class.php)
- [commondocgeneratorreferenceletters.class.php](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/class/commondocgeneratorreferenceletters.class.php)
- [modules_referenceletters.php](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/core/modules/referenceletters/modules_referenceletters.php)
- [pdf_rfltr_agefodd.modules.php](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/core/modules/referenceletters/pdf/pdf_rfltr_agefodd.modules.php)

## Detailed Documentation

Detailed technical and validation notes were moved out of the README:

- [ARCHITECTURE_AND_VALIDATION.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/docs/ARCHITECTURE_AND_VALIDATION.md)
- [CURRENT_STATE.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/CURRENT_STATE.md)
- [DELIVERY_EVIDENCE.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/DELIVERY_EVIDENCE.md)
- [LIVRABLE_CDP.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/LIVRABLE_CDP.md)
- [SUBSTITUTIONS_CLOSURE_AUDIT.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/SUBSTITUTIONS_CLOSURE_AUDIT.md)

## Maintenance Rule

The README stays as the module entrypoint.
Long-form audit logs, batch outputs and proof material must live in dedicated
documents or under `docs/`, not here.
