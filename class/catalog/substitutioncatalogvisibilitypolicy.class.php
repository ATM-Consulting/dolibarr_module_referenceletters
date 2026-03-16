<?php

/**
 * Controls whether a detected key should be visible in the UI.
 */
class SubstitutionCatalogVisibilityPolicy
{
	/**
	 * Return the visibility level for a detected key.
	 *
	 * @param string $tag Detected substitution key.
	 * @param string $source Discovery source.
	 * @return string `user`, `advanced` or `hidden`.
	 */
	public function getVisibility(string $tag, string $source = 'catalog'): string
	{
		if (!is_string($tag) || $tag === '') {
			return 'hidden';
		}

		if ($this->isSensitiveTag($tag)) {
			return 'hidden';
		}

		if (strpos($tag, '__') === 0 && substr($tag, -2) === '__') {
			return 'hidden';
		}

		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return 'hidden';
		}

		if (strpos($tag, '[...]') !== false) {
			return 'hidden';
		}

		if (strpos($tag, 'objvar_') === 0 || strpos($tag, 'object_array_options_options_') === 0) {
			return 'advanced';
		}

		return 'user';
	}

	protected function isSensitiveTag(string $tag): bool
	{
		$sensitivePatterns = array(
			'/password/i',
			'/passwd/i',
			'/token/i',
			'/secret/i',
			'/api[_]?key/i',
			'/cryptkey/i',
			'/database_pwd/i',
		);

		foreach ($sensitivePatterns as $pattern) {
			if (preg_match($pattern, $tag)) {
				return true;
			}
		}

		return false;
	}
}
