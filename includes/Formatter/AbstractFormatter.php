<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Exception\DataModelException;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Flow\UrlGenerator;
use ChangesList;
use Html;
use IContextSource;
use Language;
use Message;
use Title;
use User;

/**
 * This is a "utility" class that might come in useful to generate
 * some output per Flow entry, e.g. for RecentChanges, Contributions, ...
 * These share a lot of common characteristics (like displaying a date, links to
 * the posts, some description of the action, ...)
 *
 * Just extend from this class to use these common util methods, and make sure
 * to pass the correct parameters to these methods. Basically, you'll need to
 * create a new method that'll accept the objects for your specific
 * implementation (like ChangesList & RecentChange objects for RecentChanges, or
 * ContribsPager and a DB row for Contributions). From those rows, you should be
 * able to derive the objects needed to pass to these utility functions (mainly
 * Workflow, AbstractRevision, Title, User and Language objects) and return the
 * output.
 *
 * For implementation examples, check Flow\RecentChanges\Formatter or
 * Flow\Contributions\Formatter.
 */
abstract class AbstractFormatter {
	/**
	 * @var RevisionActionPermissions
	 */
	protected $permissions;

	/**
	 * @var RevisionFormatter
	 */
	protected $serializer;

	public function __construct( RevisionActionPermissions $permissions, Templating $templating ) {
		$this->permissions = $permissions;
		$this->serializer = new RevisionFormatter( $permissions, $templating );
	}

	/**
	 * @param array &$data Uses reference to unset used links from $data['links']
	 * @return string HTML
	 */
	protected function formatTimestamp( array &$data, $key = 'timeAndDate' ) {
		// Format timestamp: add link
		$formattedTime = $data['dateFormats'][$key];

		if ( isset( $data['links']['topic'] ) ) {
			$formattedTime = $this->apiLinkToAnchor( $data['links']['topic'], $formattedTime );
			// dont re-use link in $linksContent
			unset( $data['links']['topic'] );
		} elseif ( $data['links'] ) {
			$formattedTime = $this->apiLinkToAnchor( end( $data['links'] ), $formattedTime );
		}

		$class = array( 'mw-changeslist-date' );
		if ( $data['isModerated'] ) {
			$class[] = 'history-deleted';
		}

		return Html::rawElement( 'span', array( 'class' => $class ), $formattedTime );
	}

	/**
	 * @param array[] $links
	 * @param IContextSource $ctx
	 * @param string[] $request List of link names to be allowed in result output
	 * @return string Html valid for user output
	 */
	protected function formatLinksAsPipeList( array $links, IContextSource $ctx, array $request = null ) {
		if ( $request === null ) {
			$request = array_keys( $links );
		} elseif ( !$request ) {
			// empty array was passed
			return array();
		}
		$have = array_combine( $request, $request );

		$formatted = array();
		foreach ( $links as $key => $link ) {
			if ( isset( $request[$key] ) ) {
				$formatted[] = $this->apiLinkToAnchor( $link );
			}
		}

		if ( $formatted ) {
			$content = $ctx->getLanguage()->pipeList( $formatted );
			if ( $content ) {
				return $ctx->msg( 'parentheses' )->rawParams( $content )->escaped();
			}
		}

		return '';
	}


	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}

	/**
	 * @param array $data
	 * @param IContextSource $ctx
	 * @return string Html valid for user output
	 */
	protected function formatDescription( array $data, IContextSource $ctx ) {
		// Build description message, piggybacking on history i18n
		$changeType = $data['changeType'];
		$actions = $this->permissions->getActions();
		$msg = $actions->getValue( $changeType, 'history', 'i18n-message' );
		$source = $actions->getValue( $changeType, 'history', 'i18n-params' );
		$workflowId = $data['workflowId'];

		$params = array();
		foreach ( $source as $param ) {
			if ( false && substr( $param, -4 ) === '-url' ) { // @todo
				// source from links attribute
				$params[] = $data['links'][substr( $param, 0, -4 )];
			} else {
				// source from properties attribute
				$params[] = $data['properties'][$param];
			}
		}

		return $ctx->msg( $msg, $params )->parse();
	}

	/**
	 * @param array $link
	 * @return string Html valid for user output
	 */
	protected function apiLinkToAnchor( array $link, $content = null ) {
		list( $href, $msg ) = $link;
		$text = $msg->text();

		return Html::element(
			'a',
			array(
				'href' => $href,
				'title' => $text,
			),
			$content === null ? $text : $content
		);
	}
}
