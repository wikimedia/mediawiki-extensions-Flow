<?php

namespace Flow\Log;

use Flow\Collection\PostCollection;
use Flow\Container;
use Flow\Formatter\AbstractQuery;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Message;

class ActionFormatter extends \LogFormatter {
	/**
	 * @var UUID[]
	 */
	static $uuids = array();

	/**
	 * @param \LogEntry $entry
	 */
	public function __construct( \LogEntry $entry ) {
		parent::__construct( $entry );

		$params = $this->entry->getParameters();
		// serialized topicId or postId can be stored
		foreach ( $params as $key => $value ) {
			if ( $value instanceof UUID ) {
				static::$uuids[$value->getAlphadecimal()] = $value;
			}
		}
	}

	/**
	 * Formats an activity log entry.
	 *
	 * @return string The log entry
	 */
	protected function getActionMessage() {
		global $wgContLang;

		// at this point, all log entries will already have been created & we've
		// gathered all uuids in constructor: we can now batchload all of them
		static $loaded = false;
		if ( !$loaded ) {
			$query = new LogQuery();
			$query->loadMetadataBatch( static::$uuids );
			$loaded = true;
		}

		$root = $this->getRoot();
		if ( !$root ) {
			// failed to load required data
			return '';
		}

		$type = $this->entry->getType();
		$action = $this->entry->getSubtype();
		$title = $this->entry->getTarget();
		$skin = $this->plaintext ? null : $this->context->getSkin();
		$params = $this->entry->getParameters();

		// @todo: we should probably check if user isAllowed( <this-revision>, 'log' )
		// unlike RC, Contributions, ... this one does not batch-load all Flow
		// revisions & does not use the same Formatter, i18n message text, etc

		// FIXME this is ugly. Why were we treating log parameters as
		// URL GET parameters in the first place?
		if ( isset( $params['postId'] ) ) {
			$title = clone $title;
			$postId = $params['postId'];
			if ( $postId instanceof UUID ) {
				$postId = $postId->getAlphadecimal();
			}
			$title->setFragment( '#flow-post-' . $postId );
			unset( $params['postId'] );
		}

		if ( isset( $params['topicId'] ) ) {
			$title = clone $title;
			$topicId = $params['topicId'];
			if ( $topicId instanceof UUID ) {
				$topicId = $topicId->getAlphadecimal();
			}
			$title->setFragment( '#flow-topic-' . $topicId );
			unset( $params['topicId'] );
		}

		// Give grep a chance to find the usages:
		// logentry-delete-flow-delete-post, logentry-delete-flow-restore-post,
		// logentry-suppress-flow-restore-post, logentry-suppress-flow-suppress-post,
		// logentry-delete-flow-delete-topic, logentry-delete-flow-restore-topic,
		// logentry-suppress-flow-restore-topic, logentry-suppress-flow-suppress-topic,
		$language = $skin === null ? $wgContLang : $skin->getLanguage();
		$message = wfMessage( "logentry-$type-$action" )
			->params( array(
				Message::rawParam( $this->getPerformerElement() ),
				$this->entry->getPerformer()->getId(),
				$title, // link to topic
				$title->getFullUrl( $params ), // link to topic, higlighted post
				$root->getLastRevision()->getContent(), // topic title
				$root->getWorkflow()->getOwnerTitle() // board title object
			) )
			->inLanguage( $language )
			->parse();

		return \Html::rawElement(
			'span',
			array( 'class' => 'plainlinks' ),
			$message
		);
	}

	/**
	 * The native LogFormatter::getActionText provides no clean way of handling
	 * the Flow action text in a plain text format (e.g. as used by CheckUser)
	 *
	 * @return string
	 */
	public function getActionText() {
		$text = $this->getActionMessage();
		return $this->plaintext ? Utils::htmlToPlaintext( $text ) : $text;
	}

	/**
	 * @return PostCollection|bool
	 */
	protected function getRoot() {
		$params = $this->entry->getParameters();

		try {
			if ( isset( $params['topicId'] ) ) {
				$uuid = UUID::create( $params['topicId'] );
				$collection = PostCollection::newFromId( $uuid );

				// see if this post is valid
				$collection->getLastRevision();
				return $collection;
			}

			if ( isset( $params['postId'] ) ) {
				$uuid = UUID::create( $params['postId'] );
				$collection = PostCollection::newFromId( $uuid )->getRoot();

				// see if this post is valid
				$collection->getLastRevision();
				return $collection;
			}
		} catch ( \Exception $e ) {
			// failed finding the expected data in storage
			wfWarn( __METHOD__ . ': Failed to locate root for: ' . serialize( $params ) . ' (potentially storage issue)');
			return false;
		}

		// something wrong with logging data, should have topicId or postId
		wfWarn( __METHOD__ . ': Failed to locate root for: ' . serialize( $params ) . '(potentially invalid log data)' );
		return false;
	}
}

class LogQuery extends AbstractQuery {
	/**
	 * LogQuery is kind of a hack; I want to call loadMetadataBatch from
	 * Flow\Log\ActionFormatter, but that one extends core's way of doing
	 * log formatting already.
	 * I'm just gonna pull in whatever I need from container, not expect these
	 * dependencies to be properly passed along (because core creates the
	 * objects where we'd need this data)
	 */
	public function __construct() {
		$this->storage = Container::get( 'storage' );
		$this->treeRepository = Container::get( 'repository.tree' );
	}

	/**
	 * @param UUID[] $uuids
	 */
	public function loadMetadataBatch( $uuids ) {
		$posts = $this->loadPostsBatch( $uuids );
		parent::loadMetadataBatch( $posts );
	}

	/**
	 * @param UUID[] $uuids
	 * @return PostRevision[]
	 */
	protected function loadPostsBatch( array $uuids ) {
		$queries = array();
		foreach ( $uuids as $uuid ) {
			$queries[] = array( 'rev_type_id' => $uuid );
		}

		$found = $this->storage->findMulti(
			'PostRevision',
			$queries,
			array( 'limit' => 1 )
		);

		$revisions = array();
		foreach ( $found as $result ) {
			/** @var PostRevision $revision */
			$revision = reset( $result );
			$revisions[$revision->getPostId()->getAlphadecimal()] = $revision;
		}

		return $revisions;
	}
}
