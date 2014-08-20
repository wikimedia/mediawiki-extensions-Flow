<?php

namespace Flow\Serializer;

/**
 * Enum defines concrete values for transformation priority.
 * Transformations with the lowest numerical priority are run first.
 */
class SerializerPriority {
	const PRE_CHILDREN = 0;
	const CHILDREN = 1000;
	const STANDARD = 10000;
	const EXTEND = 100000;
	const LAST = 1000000;
}
