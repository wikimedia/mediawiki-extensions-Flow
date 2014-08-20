<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use SpecialPage;
use Title;

/**
 *
 */
class TitleType extends AbstractSerializerType {
	/**
	 * {@inheritDoc}
	 */
	public function getDefaultOptions() {
		return array(
			'title_text' => false,
			'special' => false,
			'namespace' => false,
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		if ( $options['special'] && $options['namespace'] ) {
			throw new \Exception( 'Can only enable `special` or `namespace` but not both' );
		}

		if ( $options['special'] ) {
			$builder->addTransformer( function( $data ) use ( $options ) {
				return $data
					? SpecialPage::getTitleFor( $options['special'], $data )
					: null;
			} );
		} elseif ( $options['namespace'] ) {
			$builder->addTransformer( function( $data ) use ( $options ) {
				return $data
					? Title::newFromText( $data, $options['namespace'] )
					: null;
			} );
		}

		$builder->addTransformer( function( $data ) use ( $options ) {
			if ( !$data instanceof Title ) {
				return null;
			}
			return array(
				'url' => $data->getLinkURL(),
				'title' => $options['title_text'] ?: $data->getText(),
				'exists' => $options['special'] ? true : $data->exists(),
			);
		} );
	}
}
