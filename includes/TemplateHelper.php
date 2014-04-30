<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use HTML;
use LightnCandy;

class TemplateHelper {

	protected $renderers;

	static protected $blockMap = array(
		'header' => array(
			'default' => 'flow_block_header',
		),
		'topiclist' => array(
			'default' => 'flow_block_topiclist',
		),
		'topic' => array(
			'default' => 'flow_block_topic',
			'history' => 'flow_block_topic_history',
		),
		'board-history' => array(
			'default' => 'flow_block_board-history',
		),
	);

	/**
	 * @param string $templateDir
	 * @param string|null $tempDir Temporary directory
	 */
	public function __construct( $templateDir, $tempDir ) {
		$this->templateDir = $templateDir;
		$this->tempDir = $tempDir;
	}

	public function getTemplateFilename( $templateName ) {
		return "{$this->templateDir}/{$templateName}.html.handlebars";
	}

	/**
	 * Returns a given template function if found, otherwise throws an exception.
	 * @param string $templateName
	 * @return \Closure
	 * @throws Exception\FlowException
	 * @throws \Exception
	 */
	public function getTemplate( $templateName ) {
		if ( isset( $this->renderers[$templateName] ) ) {
			return $this->renderers[$templateName];
		}

		// @todo remove this is_dev check
		$is_dev = $_SERVER['SCRIPT_FILENAME'] === '/vagrant/mediawiki/index.php';

		$template = $this->getTemplateFilename( $templateName );
		$compiled = "$template.php";

		if ( $is_dev || !file_exists( $compiled ) ) {
			if ( !file_exists( $template ) ) {
				throw new FlowException( "Could not locate template: $template" );
			}

			$code = LightnCandy::compile(
				file_get_contents( $template ),
				array(
					'flags' => LightnCandy::FLAG_ERROR_EXCEPTION
						| LightnCandy::FLAG_EXTHELPER
						| LightnCandy::FLAG_WITH
						| LightnCandy::FLAG_THIS
						| LightnCandy::FLAG_SPVARS
						| LightnCandy::FLAG_PARENT,
					'basedir' => array( $this->templateDir ),
					'fileext' => array( '.html.handlebars' ),
					'helpers' => array(
						'l10n' => 'Flow\TemplateHelper::l10n',
						'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
						'timestamp' => 'Flow\TemplateHelper::timestamp',
						'html' => 'Flow\TemplateHelper::html',
						'block' => 'Flow\TemplateHelper::block',
						'author' => 'Flow\TemplateHelper::author',
						'url' => 'Flow\TemplateHelper::url',
						'formElement' => 'Flow\TemplateHelper::formElement',
						'math' => 'Flow\TemplateHelper::math',
						'post' => 'Flow\TemplateHelper::post',
						'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
						'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
						'historyDescription' => 'Flow\TemplateHelper::historyDescription',
						'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
					),
					'blockhelpers' => array(
						'eachPost' => 'Flow\TemplateHelper::eachPost',
						'pipelist' => 'Flow\TemplateHelper::pipelist',
					),
				)
			);
			if ( !$code ) {
				throw new \Exception( 'Not possible?' );
			}
			file_put_contents( $compiled, $code );
		}

		$renderer = include $compiled;
		return $this->renderers[$templateName] = function( $args, array $scopes = array() ) use ( $templateName, $renderer ) {
			$section = new \ProfileSection( __CLASS__ . " $templateName" );
			return $renderer( $args, $scopes );
		};
	}

	/**
	 * Returns HTML for a given template by calling the template function with the given args.
	 * @param string $templateName
	 * @param mixed $args
	 * @param array $scopes
	 * @return string
	 */
	static public function processTemplate( $templateName, $args, array $scopes = array() ) {
		// Undesirable, but lightncandy helpers have to be static methods
		$template = Container::get( 'lightncandy' )->getTemplate( $templateName );
		return call_user_func( $template, $args, $scopes );
	}

	// Helpers

	// @todo We should get rid of the switch statement in this method and use the appropriate strings in-template
	static public function l10n( $str /*, $args... */ ) {
		$message = null;
		$args = func_get_args();
		// pull $str out of $args
		array_shift( $args );

		switch( $str ) {
		case 'Start_a_new_topic':
			$str = 'flow-newtopic-start-placeholder';
			break;

		case 'Sorting_tooltip':
			$str = 'flow-sorting-tooltip';
			break;

		case 'Toggle_small_topics':
			$str = 'flow-toggle-small-topics';
			break;

		case 'Toggle_topics_only':
			$str = 'flow-toggle-topics';
			break;

		case 'Toggle_topics_and_posts':
			$str = 'flow-toggle-topics-posts';
			break;

		case 'topic_details_placeholder':
			$str = 'flow-newtopic-content-placeholder';
			break;

		case 'Newest_topics':
			$str = 'flow-newest-topics';
			break;

		case 'Add_Topic':
			$str = 'flow-add-topic';
			break;

		case 'Load_More':
			$str = 'flow-load-more';
			break;

		case 'Summarize':
			$str = 'flow-summarize-topic-submit';
			break;

		case 'block':
			$str = 'blocklink';
			break;

		case 'Talk':
			$str = 'talkpagelinktext';
			break;

		case 'Edit':
		case 'edit':
			$str = 'flow-post-action-edit-post';
			break;

		case 'Reply':
			$author = $args[0];
			$message = wfMessage( 'flow-reply-submit', $author['gender'] );
			break;

		case 'Cancel':
			$str = 'flow-cancel';
			break;

		case 'Preview':
			$str = 'flow-preview';
			break;

		case 'Hide':
			$str = 'flow-post-action-hide-post';
			break;

		case 'Delete':
			$str = 'flow-post-action-delete-post';
			break;

		case 'Suppress':
			$str = 'flow-post-action-suppress-post';
			break;

		case 'Topics_n':
			$topiclist = $args[0];
			$message = wfMessage( 'flow-topic-count', count( $topiclist['roots'] ) );
			break;

		case 'started_with_participants':
			$topicPost = $args[0];
			$message = wfMessage(
				'flow-topic-participants-second-try',
				$topicPost['author']['name'],
				$topicPost['author_count'] - 1
			);
			break;

		case 'topic_count_sidebar':
			$topiclist = $args[0];
			$message = wfMessage(
				'flow-topic-count-sidebar',
				count( $topiclist['roots'] ),
				'???' //$topiclist['topic_count']
			);
			break;

		case 'Reply_to_author_name':
			$author = $args[0];
			$message = wfMessage(
				'flow-topic-reply-to-author-name',
				$author['name']
			);
			break;

		case 'comment_count':
			$topicPost = $args[0];
			$message = wfMessage(
				'flow-topic-comment-count',
				$topicPost['reply_count']
			);
			break;

		case 'topic_TOU':
			$str = 'flow-terms-of-use-new-topic';
			break;

		case 'reply_TOU':
			$str = 'flow-terms-of-use-reply';
			break;

		case 'summarize_TOU':
			$str = 'flow-terms-of-use-summarize';
			break;

		case '_time':
			break;

		case 'timeago':
			$ts = new \MWTimestamp( $args[0] );
			return $ts->getHumanTimestamp();

		case 'active_ago':
			$ts = new \MWTimestamp( $args[0] );
			$message = wfMessage(
				'flow-active-ago',
				$ts->getHumanTimestamp()
			);
			break;

		case 'started_ago':
			$ts = new \MWTimestamp( $args[0] );
			$message = wfMessage(
				'flow-started-ago',
				$ts->getHumanTimestamp()
			);
			break;

		case 'edited_ago':
			$ts = new \MWTimestamp( $args[0] );
			$message = wfMessage(
				'flow-edited-ago',
				$ts->getHumanTimestamp()
			);
			break;

		case 'datetime':
			$ts = new \MWTimestamp( $args[0] );
			return $ts->getHumanTimestamp();
		}

		if ( $message ) {
			return $message->text();
		} else {
			static $cache;
			if ( !isset( $cache[$str] ) ) {
				$cache[$str] = wfMessage( $str )->text();
			}
			return $cache[$str];
		}
	}

	static public function uuidTimestamp( $uuid, $str, $timeAgoOnly = false ) {
		$obj = UUID::create( $uuid );
		if ( !$obj ) {
			return null;
		}

		// timestamp helper expects ms timestamp
		$timestamp = $obj->getTimestampObj()->getTimestamp() * 1000;
		return self::timestamp( $timestamp, $str, $timeAgoOnly );
	}

	/**
	 * @param integer $timestamp milliseconds since the unix epoch
	 * @param string $str i18n key name for ago message
	 * @param boolean $timeAgoOnly Only render the 'X minutes ago' portion
	 * @return string
	 */
	static public function timestamp( $timestamp, $str, $timeAgoOnly = false ) {
		global $wgLang, $wgUser;

		if ( !$timestamp || !$str ) {
			return;
		}

		// source timestamps are in ms
		$timestamp /= 1000;
		$secondsAgo = time() - $timestamp;

		if ( $secondsAgo < 2419200 ) {
			$timeAgo = self::l10n( $str, $timestamp );
			if ( $timeAgoOnly === true ) {
				return $timeAgo;
			}
		} elseif ( $timeAgoOnly === true ) {
			return;
		} else {
			$timeAgo = null;
		}

		return self::html( self::processTemplate(
			'timestamp',
			array(
				'time_iso' => $timestamp,
				// do not like
				'time_readable' => $wgLang->userTimeAndDate( $timestamp, $wgUser ),
				'time_ago' => $timeAgo,
				'guid' => null,
			)
		) );
	}

	/**
	 * Takes in HTML string, returns array that tells lightncandy to skip escaping.
	 * Only works for values returned from helpers, does not work when passing
	 * variable into a template or helper.
	 *
	 * @param string $string
	 * @return array (html, 'raw')
	 */
	static public function html( $string ) {
		return array( $string, 'raw' );
	}

	static public function block( $block ) {
		$template = "flow_block_" . $block['type'];
		if ( $block['block-action-template'] ) {
			$template .= '_' . $block['block-action-template'];
		}
		return self::html( self::processTemplate(
			$template,
			$block
		) );
	}

	static public function pipelist( $context, $arguments, $options ) {
		global $wgLang;

		if ( count( $arguments ) !== 1 ) {
			throw new FlowException( 'Expected exactly 1 argument' );
		}
		$fn = $options['fn'];
		$ret = array();
		foreach ( $arguments[0] as $item ) {
			$cx['scopes'][] = $item;
			$ret[] = call_user_func( $fn, $options['cx'], $item );
			array_pop( $cx['scopes'] );
		}

		// Block helpers must always return safe content
		return wfMessage( 'parentheses' )->rawParams( $wgLang->pipelist( $ret ) )->escaped();
	}

	/**
	 * @param array $context The 'this' value of the calling context
	 * @param array $arguments Arguments passed into the helper
	 * @param array $options blockhelper specific invocation options
	 * @return string HTML
	 */
	static public function eachPost( $context, $arguments, $options ) {
		list( $data, $postIds ) = $arguments;
		if ( $data === null ) {
			var_dump( $arguments ); throw new \Exception;
		}
		if ( count( $postIds ) === 0 ) {
			return call_user_func( $options['inverse'], $options['cx'], array() );
		}
		$fn = $options['fn'];
		$ret = array();
		$i = 0;
		$last = count( $postIds ) - 1;
		foreach ( $postIds as $id ) {
			$revId = $data['posts'][$id][0];

			// iteration variables
			$options['cx']['sp_vars'] = array(
				'index' => $i,
				'first' => $i === 0,
				'last' => $i === $last
			);
			if ( !isset( $data['revisions'][$revId] ) ) {
				throw new FlowException( "Revision not available: $revId" );
			}

			$cx['scopes'][] = $data['revisions'][$revId];
			// $fn is always safe return value, its the inner template content
			$ret[] = call_user_func( $fn, $options['cx'], $data['revisions'][$revId] );
			array_pop( $cx['scopes'] );
			$i++;
		}

		return implode( '', $ret );
	}

	static public function formElement() {
	}

	static public function math( $lvalue, $op, $rvalue ) {
		switch( $op ) {
		case '+':
			return $lvalue + $rvalue;

		case '-':
			return $lvalue - $rvalue;

		case '*':
			return $lvalue * $rvalue;

		case '/':
			return $lvalue / $rvalue;

		case '%':
			return $lvalue % $rvalue;

		default:
			throw new FlowException( "Unknown math operand: $op" );
		}
	}

	// Required to prevent recursion loop
	static public function post( $rootBlock, $revision ) {
		return self::html( self::processTemplate( 'flow_post', array(
			'revision' => $revision,
			'rootBlock' => $rootBlock,
		) ) );
	}

	static public function historyTimestamp( array $revision, $key = 'timeAndDate' ) {
		$raw = false;
		$formattedTime = $revision['dateFormats']['timeAndDate'];
		$linkKeys = array( 'header-revision', 'topic-revision', 'post-revision' );
		foreach ( $linkKeys as $linkKey ) {
			if ( isset( $revision['links'][$linkKey] ) ) {
				$link = $revision['links'][$linkKey];
				$formattedTime = Html::element(
					'a',
					array(
						'href' => $link['url'],
						'title' => $link['title'],
					),
					$formattedTime
				);
				$raw = true;
				break;
			}
		}

		if ( $raw === false ) {
			$formattedTime = htmlspecialchars( $formattedTime );
		}

		$class = array( 'mw-changeslist-date' );
		if ( $revision['isModerated'] ) {
			$class[] = 'history-deleted';
		}

		return self::html(
			'<span class="plainlinks">'
			. Html::rawElement( 'span', array( 'class' => $class ), $formattedTime )
			. '</span>'
		);
	}

	static public function historyDescription( array $revision ) {
		$changeType = $revision['changeType'];
		$i18nKey = $revision['properties']['_key'];
		unset( $revision['properties']['_key'] );

		// a variety of the i18n history messages contain wikitext and require ->parse()
		return self::html( wfMessage( $i18nKey, $revision['properties'] )->parse() );
	}

	static public function showCharacterDifference( $old, $new ) {
		return self::html( \ChangesList::showCharacterDifference( $old, $new ) );
	}

	static public function progressiveEnhancement( $context, $insertionType, $sectionId, $templateName ) {
		return self::html(
			'<script type="text/x-handlebars-template-progressive-enhancement" data-type="' . $insertionType . '" id="' . $sectionId . '">'
			. self::processTemplate( $templateName, $context )
			.'</script>'
		);
	}
}
