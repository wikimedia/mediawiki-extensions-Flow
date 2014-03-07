<?php

namespace Flow\Model;

/**
 * All Summarizable entity should implement this interface
 */
interface Summarizable {

	/**
	 * The id of the entity to be summarized
	 * @return UUID
	 */
	public function getId();

}
