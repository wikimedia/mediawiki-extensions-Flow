<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Formatter\BoardHistoryQuery;
use Flow\Formatter\RevisionFormatter;
use Flow\Exception\DataModelException;

class BoardHistoryBlock extends AbstractBlock {
	protected $supportedGetActions = array( 'history' );

	// @Todo - fill in the template names
	protected $templates = array(
		'history' => '',
	);

	/**
	 * Board history is read-only block which should not invoke write action
	 */
	public function validate() {
		throw new DataModelException( __CLASS__ . ' should not invoke validate()', 'process-data' );
	}

	/**
	 * Board history is read-only block which should not invoke write action
	 */
	public function commit() {
		throw new DataModelException( __CLASS__ . ' should not invoke commit()', 'process-data' );
	}

	public function renderApi( array $options ) {
		if ( $this->workflow->isNew() ) {
			return array(
				'type' => $this->getName(),
				'revisions' => array(),
				'links' => array(
				),
			);
		}

		/** @var BoardHistoryQuery $query */
		$query = Container::get( 'query.board-history' );
		$history = $query->getResults( $this->workflow );
		/** @var RevisionFormatter $formatter */
		$formatter = Container::get( 'formatter.revision' );
		$formatter->setIncludeHistoryProperties( true );

		$revisions = array();
		foreach ( $history as $row ) {
			$serialized = $formatter->formatApi( $row, $this->context );
			if ( $serialized ) {
				$revisions[$serialized['revisionId']] = $serialized;
			}
		}

		return array(
			'type' => $this->getName(),
			'revisions' => $revisions,
			'links' => array(
			),
		);
	}

	public function getName() {
		return 'board-history';
	}
}
