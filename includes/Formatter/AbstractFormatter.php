<?php

namespace Flow\Formatter;

use Flow\RevisionActionPermissions;
use Flow\Templating;
use Html;
use IContextSource;
use Message;

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
	 * @see RevisionFormatter::buildActionLinks
	 * @see RevisionFormatter::getDateFormats
	 *
	 * @param array &$data Uses reference to unset used links from $data['links']
	 *  Expects an array with keys 'dateFormats' and 'links'. The former should
	 *  be an array having the key $key being tossed in here; the latter an array
	 *  of links in the [href, msg] format.
	 * @param string $key Date format to use - any of the keys in the array
	 *  returned by RevisionFormatter::getDateFormats
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
	 * Generate HTML for "(foo | bar | baz)"  based on the links provided by
	 * RevisionFormatter.
	 *
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
			if ( isset( $have[$key] ) ) {
				if ( is_array( $link ) ) {
					$formatted[] = $this->apiLinkToAnchor( $link );
				} elseif( $link instanceof Message ) {
					$formatted[] = $link->escaped();
				} else {
					// plain text
					$formatted[] = htmlspecialchars( $have[$key] );
				}
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

	/**
	 * Generate HTML for "(diff | hist)".  This will always contain both
	 * elements, they will be linked if the result from RevisionFormatter
	 * contains relevant links.
	 *
	 * @param array[][] Associative array containing (url, message) tuples
	 * @param IContextSource $ctx
	 * @return string Html valid for user output
	 */
	protected function formatDiffHistPipeList( array $input, IContextSource $ctx ) {
		$links = array();
		if ( isset( $input['diff'] ) ) {
			$links[] = $input['diff'];
		} else {
			// plain text with no link
			$links[] = $ctx->msg( 'diff' );
		}

		if ( isset( $input['post-history'] ) ) {
			$links[] = $input['post-history'];
		} elseif ( isset( $input['topic-history'] ) ) {
			$links[] = $input['topic-history'];
		} elseif ( isset( $input['board-history'] ) ) {
			$links[] = $input['board-history'];
		} else {
			// plain text with no link
			$links[] = $ctx->msg( 'hist' );
		}

		return $this->formatLinksAsPipeList( $links, $ctx );
	}

	/**
	 * @return string Html valid for user output
	 */
	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}

	/**
	 * Generate an HTML revision description.
	 *
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

		$params = array();
		foreach ( $source as $param ) {
			// source from properties attribute
			$params[] = $data['properties'][$param];
		}

		return '<span class="plainlinks">' . $ctx->msg( $msg, $params )->parse() . '</span>';
	}

	/**
	 * Convert a (url,message) tuple from RevisionFormatter into an
	 * html anchor element.
	 *
	 * @param array $link
	 * @param string|null $content Optional link content
	 * @return string Html valid for user output
	 */
	protected function apiLinkToAnchor( array $link, $content = null ) {
		/** @var Message $msg */
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
