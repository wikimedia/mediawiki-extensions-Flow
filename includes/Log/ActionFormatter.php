<?php

namespace Flow\Log;

use Flow\Collection\PostCollection;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;
use Flow\Conversion\Utils;
use Flow\Repository\TreeRepository;
use Flow\UrlGenerator;
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

		/*
		 * At this point, all log entries will already have been created & we've
		 * gathered all uuids in constructor: we can now batch-load all of them.
		 * We won't directly be using that batch-loaded data (nothing will even
		 * be returned) but it'll ensure that everything we need will be
		 * retrieved from cache/storage efficiently & waiting in memory for when
		 * we request it again.
		 */
		static $loaded = false;
		if ( !$loaded ) {
			/** @var ManagerGroup storage */
			$storage = Container::get( 'storage' );
			/** @var TreeRepository $treeRepository */
			$treeRepository = Container::get( 'repository.tree' );

			$query = new LogQuery( $storage, $treeRepository );
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

		if ( isset( $params['postId'] ) ) {
			/** @var UrlGenerator $urlGenerator */
			$urlGenerator = Container::get( 'url_generator' );

			// generate link that highlights the post
			$anchor = $urlGenerator->postLink(
				$title,
				UUID::create( $params['topicId'] ),
				UUID::create( $params['postId'] )
			);
			$title = $anchor->resolveTitle();
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
				$this->entry->getPerformer()->getName(),
				$title, // link to topic
				$title->getFullUrl(), // link to topic, higlighted post
			) );

		// I don't think this is an actual security issue, but this should use Templating->getContent: T119234
		// topic title
		$message->plaintextParams( Utils::htmlToPlaintext( $root->getLastRevision()->getContent( 'topic-title-html' ) ) );

		$message->params( $root->getWorkflow()->getOwnerTitle() ); // board title object

		$message->inLanguage( $language )->parse();

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
			if ( !isset( $params['topicId'] ) ) {
				// failed finding the expected data in storage
				wfWarn( __METHOD__ . ': Failed to locate topicId in log_params for: ' . serialize( $params ) . ' (forgot to run FlowFixLog.php?)' );
				return false;
			}

			$uuid = UUID::create( $params['topicId'] );
			$collection = PostCollection::newFromId( $uuid );

			// see if this post is valid
			$collection->getLastRevision();
			return $collection;
		} catch ( \Exception $e ) {
			// failed finding the expected data in storage
			wfWarn( __METHOD__ . ': Failed to locate root for: ' . serialize( $params ) . ' (potentially storage issue)' );
			return false;
		}
	}
}
