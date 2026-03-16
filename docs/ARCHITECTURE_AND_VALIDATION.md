# ReferenceLetters Architecture And Validation

This document keeps the detailed technical notes that do not belong in the
module entrypoint README.

## Delivery Snapshot

- see [DELIVERY_EVIDENCE.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/DELIVERY_EVIDENCE.md) for the current delivery-oriented status, proof sources, local test dataset, and remaining gaps versus the original substitution spec.
- see [LIVRABLE_CDP.md](/home/client/forcomed/dolibarr/htdocs/custom/referenceletters/LIVRABLE_CDP.md) for the short functional-facing delivery summary intended for a PM/CDP audience.

## Delivery Posture

- the target is an exhaustive and defensible compliance proof
- every accessible field must be visible in the popup for the current context
- every visible field must be really substituted in the correct context
- repeated lists and their fields are part of the same requirement
- technical and legacy substitutable fields must stay visible

## Module Objective

The module allows:

- defining document models stored in database
- binding a model to an object type (`invoice`, `order`, `contract`, `rfltr_agefodd_*`, etc.)
- generating a document instance from a source object
- injecting standard Dolibarr substitutions, nested objects, extrafields and line arrays
- delegating PDF generation to a common engine, including Agefodd entry points

## Useful Structure

- `class/referenceletters.class.php`
- `class/referenceletters_tools.class.php`
- `class/referenceletterselements.class.php`
- `class/referenceletterschapters.class.php`
- `class/commondocgeneratorreferenceletters.class.php`
- `class/catalog/*`
- `core/modules/referenceletters/modules_referenceletters.php`
- `core/modules/referenceletters/pdf/pdf_rfltr_agefodd.modules.php`
- `class/actions_referenceletters.class.php`

## Scripts

### Durable Validation Scripts

- `script/docedit_model_smoke_runner.php`
- `script/docedit_model_smoke_batch.php`
- `script/validate_real_models.php`
- `script/catalog_non_regression.php`
- `script/catalog_non_regression_batch.php`
- `script/report_unresolved_placeholders.php`

### Audit Scripts

- `script/audit_substitutions.php`
- `script/inventory_element_types.php`
- `script/inventory_ui_keys.php`
- `script/inventory_runtime_keys.php`
- `script/inventory_segment_keys.php`
- `script/build_gap_matrix.php`
- `script/build_priority_reports.php`
- `script/build_not_covered_worklist.php`
- `script/build_active_type_ui_matrix.php`
- `script/build_active_type_runtime_ui_matrix.php`
- `script/build_runtime_ui_candidate_reports.php`
- `script/build_smoke_followup_worklist.php`
- `script/aggregate_smoke_batch_results.php`
- `script/compare_initial_csv_docs.php`
- `script/runtime_ui_matrix_batch.php`
- `script/substitution_inventory_lib.php`

### Support Scripts

- `script/activate_required_smoke_modules.php`
- `script/ensure_standard_smoke_samples.php`
- `script/ensure_supplier_order_sample.php`
- `script/migrate_model_to_extrafields.php`
- `script/create-maj-base.php`
- `script/interface.php`
- `script/urlMover.php`

## Current Catalog Posture

Current state:

- main business groups are clearer than before
- technical `__[]__` keys remain available in a dedicated section
- `line_*` keys are qualified as list-only keys
- repeated loops are exposed in a dedicated section
- loop hints are now aligned with the loops really available for the current type

Limit:

- the catalog remains dense
- UX is functional, not minimal
- business descriptions must keep priority over generic labels

## Recommended Next Steps

1. Freeze the proof perimeter.
2. Keep the UI/runtime/substitution matrix explicit.
3. Attach every historical CSV line to a qualified status.
4. Separate runtime fixes from proof claims.
