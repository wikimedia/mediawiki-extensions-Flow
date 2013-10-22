<?php

namespace Flow\View;

use Flow\Model\PostRevision;

class History extends \FakeResultWrapper {
	/**
	 * @var bool
	 */
	protected $sorted = true;

	/**
	 * @param array $revisions
	 */
	public function __construct( array $revisions = array() ) {
		foreach ( $revisions as $revision ) {
			$this->addRevision( $revision );
		}
	}

	/**
	 * @param PostRevision $revision
	 */
	public function addRevision( PostRevision $revision ) {
		$record = new HistoryRecord( $revision );
		$this->result[] = $record;

		// Don't sort right away, delay until data is being fetched
		$this->sorted = false;
	}

	/**
	 * @return array|bool
	 */
	public function fetchRow() {
		if ( !$this->sorted ) {
			usort( $this->result, array( $this, 'sort' ) );
			$this->sorted = true;
		}

		return parent::fetchRow();
	}

	/**
	 * Sorts the records by most recent first.
	 *
	 * @param HistoryRecord $a
	 * @param HistoryRecord $b
	 * @return int
	 */
	protected function sort( HistoryRecord $a, HistoryRecord $b ) {
		// yields "+" if $a < $b and "-" of $a > $b
		$diff = $a->getTimestamp()->diff( $b->getTimestamp() )->format( '%R' );

		return $diff === '+' ? 1 : -1;
	}

	/**
	 * @return string
	 */
	public function render() {
		// Remember current position & rewind to start of data.
		$pos = $this->pos;
		$this->rewind();

		$lines = array();
		while ( $record = $this->next() ) {
			$lines[] =
				'<li class="' . $record->getClass() . '">' .
					'<p>' . $record->getMessage()->parse() . '</p>' .
					'<p class="flow-datestamp">' .
					// build history button with timestamp html as content
					\Html::rawElement( 'a',
						array(
							'class' => 'flow-action-history-link',
							'href' => '#',
							'onclick' => 'alert( "@todo: Not yet implemented!" ); return false;' // @todo: should this link somewhere?
						),
						// timestamp html
						'<span class="flow-agotime" style="display: inline">'. $record->getTimestamp()->getHumanTimestamp() .'</span>
						<span class="flow-utctime" style="display: none">'. $record->getTimestamp()->getTimestamp( TS_RFC2822 ) .'</span>'
					) .
					'</p>' .
				'</li>';
		}

		// Restore pointer that was rewound to render.
		$this->seek( $pos );

		return '<ul>'. implode( '', $lines ). '</ul>';
	}
}

class HistoryRecord {
	/**
	 * @var PostRevision
	 */
	protected $revision;

	/**
	 * @var array
	 */
	protected $actions = array(
		'flow-rev-message-edit-post' => array(
			'i18n' => 'flow-rev-message-edit-post',
			'class' => 'flow-rev-message-edit-post',
		),
		'flow-rev-message-reply' => array(
			'i18n' => 'flow-rev-message-reply',
			'class' => 'flow-rev-message-reply',
			'bundle' => true,
			'bundle-message' => 'flow-rev-message-replies'
		),
		'flow-rev-message-new-post' => array(
			'i18n' => 'flow-rev-message-new-post',
			'class' => 'flow-rev-message-new-post',
		),
		'flow-rev-message-edit-title' => array(
			'i18n' => 'flow-rev-message-edit-title',
			'class' => 'flow-rev-message-edit-title',
		),
		'flow-rev-message-create-header' => array(
			'i18n' => 'flow-rev-message-create-header',
			'class' => 'flow-rev-message-create-header',
		),
		'flow-rev-message-edit-header' => array(
			'i18n' => 'flow-rev-message-edit-header',
			'class' => 'flow-rev-message-edit-header',
		),
		'flow-rev-message-restored-post' => array(
			'i18n' => 'flow-rev-message-restored-post',
			'class' => 'flow-rev-message-restored-post',
		),
		'flow-rev-message-hid-post' => array(
			'i18n' => 'flow-rev-message-hid-post',
			'class' => 'flow-rev-message-hid-post',
		),
		'flow-rev-message-deleted-post' => array(
			'i18n' => 'flow-rev-message-deleted-post',
			'class' => 'flow-rev-message-deleted-post',
		),
		'flow-rev-message-censored-post' => array(
			'i18n' => 'flow-rev-message-censored-post',
			'class' => 'flow-rev-message-censored-post',
		),
	);

	public function __construct( PostRevision $revision ) {
		$this->revision = $revision;
	}

	/**
	 * @return PostRevision
	 */
	public function getRevision() {
		return $this->revision;
	}

	/**
	 * @return \MWTimestamp
	 */
	public function getTimestamp() {
		return new \MWTimestamp( $this->getRevision()->getRevisionId()->getTimestampObj() );
	}

	/**
	 * @return string
	 */
	public function getClass() {
		return $this->actions[$this->getRevision()->getChangeType()]['class'];
	}

	/**
	 * @return \Message
	 */
	public function getMessage() {
		$i18n = $this->actions[$this->getRevision()->getChangeType()]['class'];
		// @todo: should automatically add params (title, user, ...) as defined

		return wfMessage( $i18n );
	}

	// @todo: bundled records
}
