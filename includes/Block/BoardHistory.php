<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Data\Pager\HistoryPager;
use Flow\Exception\DataModelException;
use Flow\Formatter\BoardHistoryQuery;
use Flow\Formatter\RevisionFormatter;
use Flow\Model\UUID;

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
		global $wgRequest;

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
		/** @var RevisionFormatter $formatter */
		$formatter = Container::get( 'formatter.revision' );
		$formatter->setIncludeHistoryProperties( true );

		list( $limit, /* $offset */ ) = $wgRequest->getLimitOffset();
		// don't use offset from getLimitOffset - that assumes an int, which our
		// UUIDs are not
		$offset = $wgRequest->getText( 'offset' );
		$offset = $offset ? UUID::create( $offset ) : null;

		$pager = new HistoryPager( $query, $this->workflow->getId() );
		$pager->setLimit( $limit );
		$pager->setOffset( $offset );
		$pager->doQuery();
		$history = $pager->getResult();

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
			'navbar' => $pager->getNavigationBar(),
			'links' => array(
			),
		);
	}

	public function getName() {
		return 'board-history';
	}
}
