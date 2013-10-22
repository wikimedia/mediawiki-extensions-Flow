<?php

namespace Flow\View;

use Flow\Model\PostRevision;
use Flow\Block\Block;
use Flow\UrlGenerator;
use User;
use MWException;
use MWTimestamp;
use FakeResultWrapper;
use Message;

class History extends FakeResultWrapper {
	/**
	 * @var array
	 */
	protected $records = array();

	/**
	 * @var bool
	 */
	protected $sorted = true;

	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var Block
	 */
	protected $block;

	/**
	 * @param array $revisions
	 * @param UrlGenerator $urlGenerator
	 * @param User $user
	 * @param Block $block
	 */
	public function __construct( array $revisions = array(), UrlGenerator $urlGenerator, User $user, Block $block ) {
		$this->urlGenerator = $urlGenerator;
		$this->user = $user;
		$this->block = $block;

		foreach ( $revisions as $revision ) {
			$record = new HistoryRecord( $revision, $this->urlGenerator, $this->user, $this->block );

			/*
			 * Instead of saving the real results in $this->result, we're saving
			 * them in $this->records and save the index in $this->result.
			 * That index is timestamp-based, so we can sort them. A unique
			 * part ($index) is added to make sure no records are lost if
			 * multiple had been on the exact same time.
			 *
			 * The reason we're not using $this->result is the getTimespan()
			 * method, where it's easier/more performant to use default array
			 * functions to determine exactly which records fall in a certain
			 * timestamp.
			 */
			$index = count( $this->records );
			$key = $record->getTimestamp()->getTimestamp( TS_MW ) . '-' . $index;
			$this->records[$key] = $record;

			$this->result[] = $key;
		}
		rsort( $this->result );
	}

	/**
	 * Overrides parent class because we're using a second array which holds
	 * our real results (the parent $this->results only holds an index to the
	 * value in the real $this->records class)
	 *
	 * @return HistoryRecord|bool
	 */
	public function fetchRow() {
		$index = parent::fetchRow();
		if ( $index !== false ) {
			$this->currentRow = $this->records[$index];
		}

		return $this->currentRow;
	}

	/**
	 * Returns a subset of History between 2 points in time.
	 *
	 * @param MWTimestamp[optional] $from
	 * @param MWTimestamp[optional] $to
	 * @return History
	 */
	public function getTimespan(  MWTimestamp $from = null, MWTimestamp $to = null ) {
		if ( $from === null ) {
			// First Flow commit; no history before this point.
			$from = new MWTimestamp( '20130710000000' );
		}
		if ( $to === null ) {
			// Today; no history after this point.
			$to = new MWTimestamp();
		}

		$from = $from->getTimestamp( TS_MW );
		$to = $to->getTimestamp( TS_MW );

		// Fix bounds in case from & to are switched.
		$min = min( $from, $to );
		$max = max( $from, $to );

		/*
		 * Fastest way to find matching records: add bound timestamps in between
		 * the records' timestamps, re-sort them & find indices of those bounds.
		 */
		$keys = $this->result;
		$keys[] = $min;
		$keys[] = $max;
		rsort( $keys );

		// Because $keys is orders DESC, min is actually the end.
		$end = array_search( $min, $keys ) - 1;
		$start = array_search( $max, $keys );

		$records = array();
		for ( $i = $start; $i < $end && isset( $this->result[$i] ); $i++ ) {
			$records[] = $this->records[$this->result[$i]]->getRevision();
		}

		return new History( $records, $this->urlGenerator, $this->user, $this->block );
	}

	/**
	 * @return string
	 */
	public function render() {
		// Remember current position & rewind to start of data.
		$pos = $this->pos;
		$this->rewind();

		$bundles = array();
		$output = '';

		while ( $record = $this->next() ) {
			// Build arrays, per type, of records to be bundled.
			if ( $record->isBundled() ) {
				$bundles[$record->getRevision()->getChangeType()][] = $record->getRevision();
			}
		}

		$this->rewind();

		while ( $record = $this->next() ) {
			$changeType = $record->getRevision()->getChangeType();

			// This record is part of an already-rendered bundle.
			if ( $record->isBundled() && !isset( $bundles[$changeType] ) ) {
				continue;

			// This record is part of a bundle, render it.
			} elseif ( $record->isBundled() &&count( $bundles[$changeType] ) > 1 ) {
				$bundle = new History( $bundles[$changeType], $this->urlGenerator, $this->user, $this->block );
				$output .= $this->renderBundle( $bundle );

				// Make sure this bundle is not rendered again.
				unset( $bundles[$changeType] );

			// Check
			} else {
				$output .= $this->renderRecord( $record );
			}
		}

		// Restore pointer that was rewound to render.
		$this->seek( $pos );

		return $output ? '<ul>' . $output . '</ul>' : '';
	}

	protected function renderBundle( History $history ) {
		$bundle = null;
		$records = '';

		foreach ( $history as $record ) {
			// Grab bundle info from first (most recent) record.
			if ( !$bundle ) {
				$bundle = $record;
			}

			// Render individual records.
			$records .= $this->renderRecord( $record );
		}

		return
			'<li class="' . $bundle->getBundleClass() . '">' .
				'<p>' . $record->getBundleMessage()->parse() . '</p>' .
//				'<p class="flow-datestamp">' .
//					'<span class="flow-agotime" style="display: inline">' . htmlspecialchars( $timestamp->getHumanTimestamp() ) . '</span>' .
//					'<span class="flow-utctime" style="display: none">' . htmlspecialchars( $timestamp->getTimestamp( TS_RFC2822 ) ) . '</span>' .
//				'</p>' .
				'<ul>' . $records . '</ul>' .
			'</li>';
	}

	protected function renderRecord( HistoryRecord $record ) {
		$timestamp = $record->getTimestamp();

		return
			'<li class="' . $record->getClass() . '">' .
				'<p>' . $record->getMessage()->parse() . '</p>' .
				'<p class="flow-datestamp">' .
					'<span class="flow-agotime" style="display: inline">' . htmlspecialchars( $timestamp->getHumanTimestamp() ) . '</span>' .
					'<span class="flow-utctime" style="display: none">' . htmlspecialchars( $timestamp->getTimestamp( TS_RFC2822 ) ) . '</span>' .
				'</p>' .
			'</li>';
	}
}

class HistoryRecord {
	/**
	 * @var PostRevision
	 */
	protected $revision;

	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var Block
	 */
	protected $block;

	/**
	 * @param PostRevision $revision
	 * @param UrlGenerator $urlGenerator
	 * @param User $user
	 * @param Block $block
	 */
	public function __construct( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
		$this->urlGenerator = $urlGenerator;
		$this->user = $user;
		$this->block = $block;
		$this->revision = $revision;
	}

	/**
	 * Returns action details.
	 *
	 * @todo: more info on what keys are needed (in particular about i18n-params)
	 *
	 * @param string $action
	 * @return array|bool Array of action details or false if invalid
	 */
	protected function getActionDetails( $action ) {
		$actions = array(
			'flow-rev-message-edit-post' => array(
				'i18n-message' => 'flow-rev-message-edit-post',
				'class' => 'flow-rev-message-edit-post',
			),
			'flow-rev-message-reply' => array(
				'i18n-message' => 'flow-rev-message-reply',
				'i18n-params' => array(
					function () {
						return $this->revision->getUserText( $this->user );
					},
					function () {
						$data = array( $this->block->getName() . '[postId]' => $this->revision->getPostId()->getHex() );
						return $this->urlGenerator->generateUrl( $this->block->getWorkflowId(), 'view', $data );
					},
				),
				'class' => 'flow-rev-message-reply',
				'bundle' => array(
					'i18n-message' => 'flow-rev-message-reply-bundle',
					'i18n-params' => array(
						function () {
							// @todo: somehow, the amount of bundled items needs to get in here :D
							return array( 'num' => 3 );
						}
					),
					'class' => 'flow-history-bundle',
				),
			),
			'flow-rev-message-new-post' => array(
				'i18n-message' => 'flow-rev-message-new-post',
				'i18n-params' => array(
					function () {
						return $this->revision->getUserText( $this->user );
					},
					function () {
						return $this->urlGenerator->generateUrl( $this->revision->getPostId() );
					},
					function () {
						return $this->revision->getContent( $this->user, 'wikitext' );
					},
				),
				'class' => 'flow-rev-message-new-post',
			),
			'flow-rev-message-edit-title' => array(
				'i18n-message' => 'flow-rev-message-edit-title',
				'i18n-params' => array(
					function () {
						return $this->revision->getUserText( $this->user );
					},
					function () {
						return $this->urlGenerator->generateUrl( $this->revision->getPostId() );
					},
					function () {
						return $this->revision->getContent( $this->user, 'wikitext' );
					},
					// @todo: find previous revision & return title of that revision
				),
				'class' => 'flow-rev-message-edit-title',
			),
			'flow-rev-message-create-header' => array(
				'i18n-message' => 'flow-rev-message-create-header',
				'class' => 'flow-rev-message-create-header',
			),
			'flow-rev-message-edit-header' => array(
				'i18n-message' => 'flow-rev-message-edit-header',
				'class' => 'flow-rev-message-edit-header',
			),
			'flow-rev-message-restored-post' => array(
				'i18n-message' => 'flow-rev-message-restored-post',
				'class' => 'flow-rev-message-restored-post',
			),
			'flow-rev-message-hid-post' => array(
				'i18n-message' => 'flow-rev-message-hid-post',
				'class' => 'flow-rev-message-hid-post',
			),
			'flow-rev-message-deleted-post' => array(
				'i18n-message' => 'flow-rev-message-deleted-post',
				'class' => 'flow-rev-message-deleted-post',
			),
			'flow-rev-message-censored-post' => array(
				'i18n-message' => 'flow-rev-message-censored-post',
				'class' => 'flow-rev-message-censored-post',
			),
		);

		if ( !isset( $actions[$action] ) ) {
			throw new MWException( "History action '$action' is not defined." );
		}

		return $actions[$action];
	}

	/**
	 * @return PostRevision
	 */
	public function getRevision() {
		return $this->revision;
	}

	/**
	 * @return MWTimestamp
	 */
	public function getTimestamp() {
		return new MWTimestamp( $this->getRevision()->getRevisionId()->getTimestampObj() );
	}

	/**
	 * @return string
	 */
	public function getClass() {
		$details = $this->getActionDetails( $this->getRevision()->getChangeType() );
		return $details['class'];
	}

	/**
	 * @return Message
	 */
	public function getMessage() {
		$details = $this->getActionDetails( $this->getRevision()->getChangeType() );
		$params = isset( $details['i18n-params'] ) ? $details['i18n-params'] : array();
		return $this->buildMessage( $details['i18n-message'], $params );
	}

	/**
	 * @return bool
	 */
	public function isBundled() {
		$details = $this->getActionDetails( $this->getRevision()->getChangeType() );
		return isset( $details['bundle'] );
	}

	/**
	 * @return string
	 */
	public function getBundleClass() {
		if ( !$this->isBundled() ) {
			return '';
		}

		$details = $this->getActionDetails( $this->getRevision()->getChangeType() );
		$details = $details['bundle'];
		return $details['class'];
	}

	/**
	 * @return Message|bool
	 */
	public function getBundleMessage() {
		if ( !$this->isBundled() ) {
			return false;
		}

		$details = $this->getActionDetails( $this->getRevision()->getChangeType() );
		$details = $details['bundle'];
		$params = isset( $details['i18n-params'] ) ? $details['i18n-params'] : array();
		return $this->buildMessage( $details['i18n-message'], $params );
	}

	/**
	 * Returns i18n message for $msg.
	 *
	 * Complex parameters can be injected in the i18n messages. Anything in
	 * $params will be call_user_func'ed. Those results will be used as
	 * message parameters.
	 *
	 * Note: return array( 'raw' => $value ) or array( 'num' => $value ) for
	 * raw or numeric parameter input.
	 *
	 * @param string $msg i18n key
	 * @param array[optional] $params Callbacks for parameters
	 * @return Message
	 */
	protected function buildMessage( $msg, array $params = array() ) {
		foreach ( $params as &$param ) {
			$param = call_user_func( $param );
		}

		return wfMessage( $msg, $params );
	}
}
