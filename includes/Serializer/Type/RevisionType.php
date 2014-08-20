<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use Flow\Serializer\SerializerTypeInterface;

/**
 *
 */
class RevisionType implements SerializerTypeInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getParentType( array $options ) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultOptions() {
		return array(
			'content_format' => 'html',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$builder
			->add( 'workflowId', 'uuid', array( 'path' => 'workflow.id' ) )
			->add( 'revisionId', 'uuid', array( 'path' => 'revision.revisionId' ) )
			->add( 'timestamp', 'uuid', array(
				'path' => 'revision.revisionId',
				'timestamp' => TS_MW,
			) )
			->add( 'changeType', 'text', array( 'path' => 'revision.changeType' ) )
			->add( 'dateFormats', 'dateFormats', array( 'path' => 'revision.revisionId' ) )
			->add( 'properties', 'revisionProperties' )
			->add( 'isModerated', 'bool', array( 'path' => 'moderatedRevision.isModerated' ) )
			->add( 'links', 'revisionLinks' )
			->add( 'actions', 'revisionActions' )
			->add( 'author', 'user', array( 'path' => 'revision.userTuple' ) )
			->add( 'previousRevisionId', 'uuid', array( 'path' => 'revision.prevRevisionId' ) )
			->add( 'moderator', 'user', array( 'path' => 'moderatedRevision.moderatedBy' ) )
			->add( 'moderationState', 'text', array( 'path' => 'moderatedRevisoin.moderationState' ) )
			->add( 'content', 'content', array(
				'path' => 'revision',
				'content_format' => $options['content_format'],
			) )
			->add( 'size' );

		// add nested size properties
		$builder->get( 'size' )
			->add( 'old', 'text', array(
				'path' => function( $data ) {
					return $data->previousRevision
						? strlen( $data->previousRevision->getContentRaw() )
						: null;
				}
			) )
			->add( 'new', 'text', array(
				'path' => function( $data ) { return strlen( $data->revision->getContentRaw() ); }
			) );

		// arguments for the revisionProperties type
		$builder->get( 'properties' )
			->add( 'workflowId', 'text', array( 'path' => 'workflow.id' ) )
			->add( 'revision', 'text', array( 'path' => 'revision' ) );
	}
}
