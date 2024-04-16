

SET FOREIGN_KEY_CHECKS=0;

/**  update du champs element_type dans la table  llx_referenceLetter à 100 **/
ALTER TABLE llx_referenceletters
MODIFY COLUMN element_type VARCHAR(150);

SET FOREIGN_KEY_CHECKS=1;
