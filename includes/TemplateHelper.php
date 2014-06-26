<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Closure;
use HTML;
use LightnCandy;
use RequestContext;
use Title;

class TemplateHelper {

	/**
	 * @var string
	 */
	protected $templateDir;

	/**
	 * @var callable[]
	 */
	protected $renderers;

	/**
	 * @var bool Always compile template files
	 */
	protected $forceRecompile = false;

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
	 * @param boolean $forceRecompile
	 */
	public function __construct( $templateDir, $forceRecompile = false ) {
		$this->templateDir = $templateDir;
		$this->forceRecompile = $forceRecompile;
	}

	/**
	 * Constructs the location of the the source handlebars template
	 * and the compiled php code that goes with it.
	 *
	 * @param $templateName
	 * @return array
	 * @throws FlowException Disallows upwards directory traversal via $templateName
	 */
	public function getTemplateFilenames( $templateName ) {
		// Prevent upwards directory traversal using same methods as Title::secureAndSplit,
		// which is implemented in MediaWikiTitleCodec::splitTitleString.
		if (
			strpos( $templateName, '.' ) !== false &&
			(
				$templateName === '.' || $templateName === '..' ||
				strpos( $templateName, './' ) === 0 ||
				strpos( $templateName, '../' ) === 0 ||
				strpos( $templateName, '/./' ) !== false ||
				strpos( $templateName, '/../' ) !== false ||
				substr( $templateName, -2 ) === '/.' ||
				substr( $templateName, -3 ) === '/..'
			)
		) {
			throw new FlowException( "Malformed \$templateName: $templateName" );
		}

		return array(
			'template' => "{$this->templateDir}/{$templateName}.handlebars",
			'compiled' => "{$this->templateDir}/compiled/{$templateName}.handlebars.php",
		);
	}

	/**
	 * Returns a given template function if found, otherwise throws an exception.
	 * @param string $templateName
	 * @return Closure
	 * @throws Exception\FlowException
	 * @throws \Exception
	 */
	public function getTemplate( $templateName ) {
		if ( isset( $this->renderers[$templateName] ) ) {
			return $this->renderers[$templateName];
		}

		$filenames = $this->getTemplateFilenames( $templateName );

		if ( $this->forceRecompile ) {
			if ( !file_exists( $filenames['template'] ) ) {
				throw new FlowException( "Could not locate template: {$filenames['template']}" );
			}

			$code = self::compile( file_get_contents( $filenames['template'] ), $this->templateDir );

			if ( !$code ) {
				throw new \Exception( 'Not possible?' );
			}
			file_put_contents( $filenames['compiled'], $code );
		}

		/** @var callable $renderer */
		$renderer = require $filenames['compiled'];
		return $this->renderers[$templateName] = function( $args, array $scopes = array() ) use ( $templateName, $renderer ) {
			$section = new \ProfileSection( __CLASS__ . " $templateName" );
			return $renderer( $args, $scopes );
		};
	}

	static public function compile( $code, $templateDir ) {
		return LightnCandy::compile(
			$code,
			array(
				'flags' => LightnCandy::FLAG_ERROR_EXCEPTION
					| LightnCandy::FLAG_EXTHELPER
					| LightnCandy::FLAG_SPVARS
					| LightnCandy::FLAG_HANDLEBARS, // FLAG_THIS + FLAG_WITH + FLAG_PARENT + FLAG_JSQUOTE + FLAG_ADVARNAME + FLAG_NAMEDARGS
				'basedir' => array( $templateDir ),
				'fileext' => array( '.handlebars' ),
				'helpers' => array(
					'l10n' => 'Flow\TemplateHelper::l10n',
					'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
					'timestamp' => 'Flow\TemplateHelper::timestamp',
					'html' => 'Flow\TemplateHelper::html',
					'block' => 'Flow\TemplateHelper::block',
					'author' => 'Flow\TemplateHelper::author',
					'url' => 'Flow\TemplateHelper::url',
					'math' => 'Flow\TemplateHelper::math',
					'post' => 'Flow\TemplateHelper::post',
					'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
					'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
					'historyDescription' => 'Flow\TemplateHelper::historyDescription',
					'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
					'l10nParse' => 'Flow\TemplateHelper::l10nParse',
					'diffRevision' => 'Flow\TemplateHelper::diffRevision',
					'moderationAction' => 'Flow\TemplateHelper::moderationAction',
					'moderationActionText' => 'Flow\TemplateHelper::moderationActionText',
					'user' => 'Flow\TemplateHelper::user',
					'addReturnTo' => 'Flow\TemplateHelper::addReturnTo',
					'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
					'escapeContent' => 'Flow\TemplateHelper::escapeContent',
					'previewButton' => 'Flow\TemplateHelper::previewButton',
					'plaintextSnippet' => 'Flow\TemplateHelper::plaintextSnippet',
				),
				'hbhelpers' => array(
					'eachPost' => 'Flow\TemplateHelper::eachPost',
					'ifEquals' => 'Flow\TemplateHelper::ifEquals',
					'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
					'ifCond' => 'Flow\TemplateHelper::ifCond',
					'tooltip' => 'Flow\TemplateHelper::tooltip',
				),
			)
		);
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

	/**
	 * Localize message.
	 * If given a simple MW message key this will convert it using the usual wfMessage() function,
	 * storing it in a cache. It may also perform special processing of other messages such as
	 * timestamps and topic counts.
	 */
	// @todo: Maybe the straight message lookup should be a separate msg helper function, for clarity?
	static public function l10n( $str /*, $args... */ ) {
		$message = null;
		$args = func_get_args();
		// pull $str out of $args
		array_shift( $args );

		switch( $str ) {
		case 'Reply':
			$author = $args[0];
			$message = wfMessage( 'flow-reply-submit', $author['gender'] );
			break;

		case 'Moderate': // @todo: Unused?
			$type = $args[0];
			$str = "flow-post-action-$type-post";
			break;

		case 'post_moderation_state':
			$type = $args[0];
			$replyToId = $args[1];
			$moderator = $args[2];
			if ( !$replyToId ) {
				$str = "flow-$type-title-content";
			} else {
				$str = "flow-$type-post-content";
			}
			$message = wfMessage( $str, $moderator );
			break;

		case 'Topics_n':
			$topiclist = $args[0];
			$message = wfMessage( 'flow-topic-count', count( $topiclist['roots'] ) );
			break;

		case 'started_with_participants':
			$topicPost = $args[0];
			$message = wfMessage(
				'flow-topic-participants-second-try',
				$topicPost['creator']['name'],
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

		case 'comment_count':
			$topicPost = $args[0];
			$message = wfMessage(
				'flow-topic-comment-count',
				$topicPost['reply_count']
			);
			break;

		case '_time': // ???
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
			return wfMessage( $str )->params( $args )->text();
		}
	}

	/**
	 * Generates a timestamp using the UUID, then calls the timestamp helper with it.
	 * @param string $uuid
	 * @param string $str
	 * @param bool $timeAgoOnly
	 * @return null|string
	 */
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
	 * This server-side version of timestamp does not render time-ago.
	 * @param integer $timestamp milliseconds since the unix epoch
	 * @param string $str i18n key name for ago message
	 * @param boolean $timeAgoOnly Only render the 'X minutes ago' portion
	 * @return string|false
	 */
	static public function timestamp( $timestamp, $str, $timeAgoOnly = false ) {
		global $wgLang, $wgUser;

		if ( !$timestamp || !$str || $timeAgoOnly === true ) {
			return false;
		}

		// source timestamps are in ms
		$timestamp /= 1000;

		return self::html( self::processTemplate(
			'timestamp',
			array(
				'time_iso' => $timestamp,
				// do not like
				'time_readable' => $wgLang->userTimeAndDate( $timestamp, $wgUser ),
				'time_ago' => true, //generated client-side
				'time_str' => $str,
				'time_ago_only' => $timeAgoOnly ? 1 : 0,
				'guid' => null, //generated client-side
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

	/**
	 * Unstrict comparison if.
	 * @example {{#ifEquals one two}}...{{/ifEquals}}
	 * @param mixed $left
	 * @param mixed $right
	 * @param array $options
	 * @return string|null
	 * @throws FlowException Fails when callbacks are not Closure instances
	 */
	static public function ifEquals( $left, $right, $options ) {
		/** @var callable $inverse */
		$inverse = isset( $options['inv'] ) ? $options['inv'] : null;
		/** @var callable $fn */
		$fn = $options['fn'];

		if ( $left == $right ) {
			if ( !$fn instanceof Closure ) {
				throw new FlowException( 'Invalid callback, expected Closure' );
			}
			return $fn();
		} elseif ( $inverse ) {
			if ( !$inverse instanceof Closure ) {
				throw new FlowException( 'Invalid inverse callback, expected Closure' );
			}
			return $inverse();
		}

		return null;
	}

	/**
	 * @param array $block
	 * @return array
	 */
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

	/**
	 * @param array $context The 'this' value of the calling context
	 * @param array $postIds List of ids (roots)
	 * @param array $options blockhelper specific invocation options
	 *
	 * @throws Exception\FlowException
	 * @internal param array $arguments Arguments passed into the helper
	 * @return null|string HTML
	 * @throws FlowException When callbacks are not Closure instances
	 */
	static public function eachPost( $context, $postIds, $options ) {
		/** @var callable $inverse */
		$inverse = isset( $options['inv'] ) ? $options['inv'] : null;
		/** @var callable $fn */
		$fn = $options['fn'];

		if ( $postIds && !is_array( $postIds ) ) {
			$postIds = array( $postIds );
		} elseif ( count( $postIds ) === 0 ) {
			// Failure callback, if any
			if ( !$inverse ) {
				return null;
			}
			if ( !$inverse instanceof Closure ) {
				throw new FlowException( 'Invalid inverse callback, expected Closure' );
			}
			return $inverse( $options['cx'], array() );
		} else {
			return null;
		}

		if ( !$fn instanceof Closure ) {
			throw new FlowException( 'Invalid callback, expected Closure' );
		}
		$html = array();
		foreach ( $postIds as $id ) {
			$revId = $context['posts'][$id][0];

			if ( !isset( $context['revisions'][$revId] ) ) {
				throw new FlowException( "Revision not available: $revId" );
			}

			// $fn is always safe return value, it's the inner template content.
			$html[] = $fn( $context['revisions'][$revId] );
		}

		// Return the resulting HTML
		return implode( '', $html );
	}

	/**
	 * @param int $lvalue
	 * @param string $op
	 * @param int $rvalue
	 *
	 * @return float|int
	 * @throws Exception\FlowException
	 */
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

	/**
	 * Required to prevent recursion loop
	 * @param array $rootBlock
	 * @param array $revision
	 *
	 * @return array
	 */
	static public function post( $rootBlock, $revision ) {
		return self::html( self::processTemplate( 'flow_post', array(
			'revision' => $revision,
			'rootBlock' => $rootBlock,
		) ) );
	}

	/**
	 * @param array $revision
	 * @param string $key
	 * @return array
	 */
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

	/**
	 * @param array $revision
	 *
	 * @return array
	 */
	static public function historyDescription( array $revision ) {
		$i18nKey = $revision['properties']['_key'];
		unset( $revision['properties']['_key'] );

		// a variety of the i18n history messages contain wikitext and require ->parse()
		return self::html( wfMessage( $i18nKey, $revision['properties'] )->parse() );
	}

	/**
	 * @param string $old
	 * @param string $new
	 * @return array
	 */
	static public function showCharacterDifference( $old, $new ) {
		return self::html( \ChangesList::showCharacterDifference( $old, $new ) );
	}

	/**
	 * @param array $input
	 *
	 * @return array
	 */
	static public function progressiveEnhancement( array $input ) {
		$context = $input['context'];
		$insertionType = htmlspecialchars( $input['insertionType'] );
		$sectionId = htmlspecialchars( $input['sectionId'] );
		$templateName = $input['templateName'];

		return self::html(
			'<script name="handlebars-template-progressive-enhancement"
				type="text/x-handlebars-template-progressive-enhancement" data-type="' . $insertionType . '" id="' . $sectionId . '">'
			. self::processTemplate( $templateName, $context )
			.'</script>'
		);
	}

	/**
	 * @param string $str
	 *
	 * @return array
	 */
	static public function l10nParse( $str /*, $args... */ ) {
		$args = func_get_args();
		array_shift( $args );
		return self::html( wfMessage( $str, $args )->parse() );
	}

	/**
	 * @param string $diffContent Plain text output of DifferenceEngine::getDiffBody
	 * @param string $oldTimestamp Time when the `old` content was created
	 * @param string $newTimestamp Time when the `new` content was created
	 * @param string $oldAuthor Creator of the `old` content
	 * @param string $newAuthor Creator of the `new` content
	 * @param string $oldLink Url pointing to `old` content
	 * @param string $newLink Url pointing to `new` content
	 * @return array HTML wrapped in array to prevent lightncandy from escaping
	 */
	static public function diffRevision( $diffContent, $oldTimestamp, $newTimestamp, $oldAuthor, $newAuthor, $oldLink, $newLink ) {
		$differenceEngine = new \DifferenceEngine();
		$multi = $differenceEngine->getMultiNotice();
		// Display a message when the diff is empty
		$notice = '';
		if ( $diffContent === '' ) {
			$notice .= '<div class="mw-diff-empty">' .
				wfMessage( 'diff-empty' )->parse() .
				"</div>\n";
		}
		$differenceEngine->showDiffStyle();

		return self::html( $differenceEngine->addHeader(
			$diffContent,
			self::generateDiffViewTitle( $oldTimestamp, $oldAuthor, $oldLink ),
			self::generateDiffViewTitle( $newTimestamp, $newAuthor, $newLink ),
			$multi,
			$notice
		) );
	}

	/**
	 * @param string $timestamp
	 * @param string $user
	 * @param string $link
	 *
	 * @return string
	 */
	static public function generateDiffViewTitle( $timestamp, $user, $link ) {
		$message = wfMessage( 'flow-compare-revisions-revision-header' )
			->params( $timestamp )
			->params( $user );

		return \Html::rawElement( 'a',
			array(
				'class' => 'flow-diff-revision-link',
				'href' => $link,
			),
			$message->parse()
		);
	}

	/**
	 * @param array $actions
	 * @param string $moderationState
	 *
	 * @return string
	 */
	static public function moderationAction( array $actions, $moderationState ) {
		return isset( $actions[$moderationState] ) ? $actions[$moderationState]['url'] : '';
	}

	/**
	 * @param array $actions
	 * @param string $moderationState
	 *
	 * @return string
	 */
	static public function moderationActionText( array $actions, $moderationState ) {
		return isset( $actions[$moderationState] ) ? $actions[$moderationState]['title'] : '';
	}

	/**
	 * Return information about given user
	 * @param string $feature key of property to retrieve e.g. name, id
	 *
	 * @return string value of property
	 */
	static public function user( $feature = 'name' ) {
		$user = RequestContext::getMain()->getUser();
		$userInfo = array(
			'id' => $user->getId(),
			'name' => $user->getName(),
		);

		return $userInfo[$feature];
	}

	/**
	 * Runs a callback when user is anonymous
	 * @param array $options which must contain fn and inverse key mapping to functions.
	 * @return mixed result of callback
	 * @throws FlowException Fails when callbacks are not Closure instances
	 */
	static public function ifAnonymous( $options ) {
		if ( RequestContext::getMain()->getUser()->isAnon() ) {
			$fn = $options['fn'];
			if ( !$fn instanceof Closure ) {
				throw new FlowException( 'Expected callback to be Closuire instance' );
			}
		} elseif ( isset( $options['inverse'] ) ) {
			$fn = $options['inverse'];
			if ( !$fn instanceof Closure ) {
				throw new FlowException( 'Expected inverse callback to be Closuire instance' );
			}
		} else {
			return '';
		}

		return $fn();
	}

	/**
	 * Adds returnto parameter pointing to current page to existing URL
	 * @param string $url to modify
	 *
	 * @return string modified url
	 */
	static public function addReturnTo( $url ) {
		$ctx = RequestContext::getMain();
		$returnTo = $ctx->getTitle();
		// We can't get only the query parameters from
		$returnToQuery = $ctx->getRequest()->getQueryValues();

		unset( $returnToQuery['title'] );

		$args = array(
			'returnto' => $returnTo->getPrefixedUrl(),
		);
		if ( $returnToQuery ) {
			$args['returntoquery'] = wfArrayToCgi( $returnToQuery );
		}
		return wfAppendQuery( $url, wfArrayToCgi( $args ) );
	}

	/**
	 * Adds returnto parameter pointing to given Title to an existing URL
	 * @param string $title
	 *
	 * @return string modified url
	 */
	static public function linkWithReturnTo( $title ) {
		$title = Title::newFromText( $title );
		// FIXME: This should use local url to avoid redirects on mobile. See bug 66746.
		$url = $title->getFullUrl();

		return self::addReturnTo( $url );
	}

	/**
	 * Accepts the contentType and content properties returned from the api
	 * for individual revisions and ensures that content is included in the
	 * final html page in an xss safe maner.
	 *
	 * It is expected that all content with contentType of html has been
	 * processed by parsoid and is safe for direct output into the document.
	 *
	 * @param string $contentType
	 * @param string $content
	 * @return string
	 */
	static public function escapeContent( $contentType, $content ) {
		return $contentType === 'html' ? self::html( $content ) : $content;
	}

	/**
	 * @param string $templateName
	 * @return string button
	 */
	static public function previewButton( $templateName ) {
		return self::html(
			self::processTemplate( 'flow_preview_button', array( 'templateName' => $templateName ) )
		);
	}

	/**
	 * Only perform action when conditions match
	 * @param string value
	 * @param string operator e.g. 'or'
	 * @param string value2 to compare with
	 * @return mixed result of callback
	 * @throws FlowException Fails when callbacks are not Closure instances
	 * @param array @options
	 */
	static public function ifCond( $value, $operator, $value2, $options ) {
		// Perform operator
		if ( $operator === 'or' ) {
			if ( $value || $value2 ) {
				$fn = $options['fn'];
				if ( !$fn instanceof Closure ) {
					throw new FlowException( 'Expected callback to be Closure instance' );
				}

				return $fn();
			} elseif ( isset( $options['inv'] ) ) {
				$inverse = $options['inv'];
				if ( !$inverse instanceof Closure ) {
					throw new FlowException( 'Expected inverse callback to be Closure instance' );
				}

				return $inverse();
			}
		}

		return '';
	}

	/**
	 * @param array $options
	 * @return string tooltip
	 */
	static public function tooltip( $options ) {
		$fn = $options['fn'];
		$params = $options['hash'];

		return (
			self::processTemplate( 'flow_tooltip', array(
				'positionClass' => $params['positionClass'] ? 'flow-ui-tooltip-' . $params['positionClass'] : null,
				'contextClass' => $params['contextClass'] ? 'flow-ui-' . $params['contextClass'] : null,
				'extraClass' => $params['extraClass'] ?: '',
				'blockClass' => $params['isBlock'] ? 'flow-ui-tooltip-block' : null,
				'content' => $fn(),
			) )
		);
	}

	/**
	 * Returns the provided content as a plaintext string. Commonly for
	 * injecting into an i18n message.
	 *
	 * @param string $contentFormat html|wikitext|plaintext
	 * @param string $content
	 * @return string plaintext
	 */
	static public function plaintextSnippet( $contentFormat, $content ) {
		if ( $contentFormat === 'html' ) {
			return Utils::htmlToPlaintext( $content, 200 );
		} else {
			global $wgLang;
			return $wgLang->truncate( trim( $content ), 200 );
		}
	}
}
