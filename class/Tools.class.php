<?php

namespace referenceletters;

class Tools {

	/**
	 * @param $object
	 * @param string $property
	 * @return bool
	 */
	public static function validateObjectProperty($object, string $property): bool {
		return property_exists($object, $property) && is_object($object->{$property});
	}


}