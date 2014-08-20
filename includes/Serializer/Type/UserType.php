<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;

/**
 *
 */
class UserType extends AbstractSerializerType {
	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$builder
			->add( 'id', 'text', array( 'path' => 'id' ) )
			->add( 'wiki', 'text', array( 'path' => 'wiki' ) )
			->add( 'name', 'userNameLookup' )
			// Run the links and gender serializers with the output of children above
			->extend( 'gender', 'userGenderLookup', array( 'path' => 'name' ) )
			->extend( 'links', null, array( 'path' => 'name' ) )
			->get( 'links' )
				->add( 'contribs', 'title', array( 'special' => 'Contributions' ) )
				->add( 'talk', 'title', array( 'namespace' => NS_USER_TALK ) )
				->add( 'block', 'title', array(
					'special' => 'Block',
					'title_text' => wfMessage( 'blocklink' )
				) );
	}
}
