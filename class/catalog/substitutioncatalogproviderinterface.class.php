<?php

/**
 * Common contract for UI catalog providers.
 */
interface SubstitutionCatalogProviderInterface
{
	/**
	 * Append catalog keys to the provided UI substitution array.
	 *
	 * @param array<string,mixed> $substArray Catalog groups keyed by label.
	 * @param array<string,mixed> $context Context used to scope catalog generation.
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void;
}
