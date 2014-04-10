<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use LightnCandy;
use LCSafeString;

class TemplateHelper {

	protected $renderers;

	/**
	 * @param string $templateDir
	 * @param string|null $tempDir Temporary directory
	 */
	public function __construct( $templateDir, $tempDir ) {
		$this->templateDir = $templateDir;
		$this->tempDir = $tempDir;
	}

	public function getTemplate( $templateName ) {
		if ( isset( $this->renderers[$templateName] ) ) {
			return $this->renderers[$templateName];
		}

		$template = "{$this->templateDir}/{$templateName}.html.handlebars";
		$compiled = "$template.php";

		if ( !file_exists( $compiled ) ) {
			if ( !file_exists( $template ) ) {
				throw new FlowException( "Could not locate template: $template" );
			}

			$code = LightnCandy::compile(
				file_get_contents( $template ),
				array(
					'flags' => LightnCandy::FLAG_ERROR_EXCEPTION
						| LightnCandy::FLAG_EXTHELPER
						| LightnCandy::FLAG_WITH
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
					),
					'blockhelpers' => array(
						'eachPost' => 'Flow\TemplateHelper::eachPost',
					),
				)
			);
			if ( !$code ) {
				throw new \Exception( 'Not possible?' );
			}
			file_put_contents( $compiled, $code );
		}

		return $this->renderers[$templateName] = include $compiled;
	}

	static public function processTemplate( $templateName, $args, array $scopes = array() ) {
		// Undesirable, but lightncandy helpers have to be static methods
		$template = Container::get( 'lightncandy' )->getTemplate( $templateName );
		return call_user_func( $template, $args, $scopes );
	}

	static public function l10n( $str /*, $args... */ ) {
		$message = null;
		$args = func_get_args();
		// pull $str out of $args
		array_shift( $args );

		switch( $str ) {
		case 'Start_a_new_topic':
			$message = wfMessage( 'flow-newtopic-start-placeholder' );
			break;

		case 'Sorting_tooltip':
			$message = wfMessage( 'flow-sorting-tooltip' );
			break;

		case 'Toggle_small_topics':
			$message = wfMessage( 'flow-toggle-small-topics' );
			break;

		case 'Toggle_topics_only':
			$message = wfMessage( 'flow-toggle-topics' );
			break;

		case 'Toggle_topics_and_posts':
			$message = wfMessage( 'flow-toggle-topics-posts' );
			break;

		case 'topic_details_placeholder':
			$message = wfMessage( 'flow-newtopic-content-placeholder' );
			break;

		case 'Newest_topics':
			$message = wfMessage( 'flow-newest-topics' );
			break;

		case 'Add_Topic':
			$message = wfMessage( 'flow-add-topic' );
			break;

		case 'Load_More':
			$message = wfMessage( 'flow-load-more' );
			break;

		case 'block':
			$message = wfMessage( 'blocklink' );
			break;

		case 'Talk':
			$message = wfMessage( 'talkpagelinktext' );
			break;

		case 'Edit':
		case 'edit':
			$message = wfMessage( 'flow-post-action-edit-post' );
			break;

		case 'Reply':
			$author = $args[0];
			$message = wfMessage( 'flow-reply-submit', $author['gender'] );
			break;

		case 'Cancel':
			$message = wfMessage( 'flow-cancel' );
			break;

		case 'Preview':
			$message = wfMessage( 'flow-preview' );
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
			$message = wfMessage( 'flow-terms-of-use-new-topic' );
			break;

		case 'reply_TOU':
			$message = wfMessage( 'flow-terms-of-use-reply' );
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
			return self::html( $message->escaped() );
		} else {
			wfDebugLog( 'Flow', __METHOD__ . ": No translation for $str" );
			return "<$str>";
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
			$timeAgo = self::html( wfMessage( $str, $ts->getHumanTimestamp() )->escaped() );
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

	static public function html( $string ) {
		return new LCSafeString( $string );
	}

	static public function block( $block ) {
		return self::html( self::processTemplate(
			"flow_block_" . $block['type'],
			$block
		) );
	}

	/**
	 * @param array $context The 'this' value of the calling context
	 * @param array $arguments Arguments passed into the helper
	 * @param array $options blockhelper specific invocation options
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
}
