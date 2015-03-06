<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Exception\WrongNumberArgumentsException;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Closure;
use HTML;
use LightnCandy;
use MWTimestamp;
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
	 * @param string $templateName
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
			return $renderer( $args, $scopes );
		};
	}

	/**
	 * @param string $code Handlebars code
	 * @param string $templateDir Directory templates are stored in
	 * @return string PHP code
	 */
	static public function compile( $code, $templateDir ) {
		return LightnCandy::compile(
			$code,
			array(
				'flags' => LightnCandy::FLAG_ERROR_EXCEPTION
					| LightnCandy::FLAG_EXTHELPER
					| LightnCandy::FLAG_SPVARS
					// Commented LightnCandy::FLAG_HANDLEBARS because it includes
					// FLAG_MUSTACHEPAIN, which currently causes issues. Below
					// line can be uncommented & the one below (spelling out all
					// options excluding FLAG_MUSTACHEPAIN) can be removed once
					// https://github.com/zordius/lightncandy/pull/126 or similar
					// lands.
//					| LightnCandy::FLAG_HANDLEBARS // FLAG_THIS + FLAG_WITH + FLAG_PARENT + FLAG_JSQUOTE + FLAG_ADVARNAME + FLAG_SPACECTL + FLAG_NAMEDARG + FLAG_SPVARS + FLAG_SLASH + FLAG_ELSE + FLAG_MUSTACHESP + FLAG_MUSTACHEPAIN
					| LightnCandy::FLAG_THIS | LightnCandy::FLAG_WITH | LightnCandy::FLAG_PARENT | LightnCandy::FLAG_JSQUOTE | LightnCandy::FLAG_ADVARNAME | LightnCandy::FLAG_SPACECTL | LightnCandy::FLAG_NAMEDARG | LightnCandy::FLAG_SPVARS | LightnCandy::FLAG_SLASH | LightnCandy::FLAG_ELSE | LightnCandy::FLAG_MUSTACHESP
					| LightnCandy::FLAG_RUNTIMEPARTIAL,
				'basedir' => array( $templateDir ),
				'fileext' => array( '.handlebars' ),
				'helpers' => array(
					'l10n' => 'Flow\TemplateHelper::l10n',
					'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
					'timestamp' => 'Flow\TemplateHelper::timestampHelper',
					'html' => 'Flow\TemplateHelper::htmlHelper',
					'block' => 'Flow\TemplateHelper::block',
					'author' => 'Flow\TemplateHelper::author',
					'post' => 'Flow\TemplateHelper::post',
					'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
					'historyDescription' => 'Flow\TemplateHelper::historyDescription',
					'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
					'l10nParse' => 'Flow\TemplateHelper::l10nParse',
					'diffRevision' => 'Flow\TemplateHelper::diffRevision',
					'moderationAction' => 'Flow\TemplateHelper::moderationAction',
					'concat' => 'Flow\TemplateHelper::concat',
					'user' => 'Flow\TemplateHelper::user',
					'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
					'escapeContent' => 'Flow\TemplateHelper::escapeContent',
				),
				'hbhelpers' => array(
					'eachPost' => 'Flow\TemplateHelper::eachPost',
					'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
					'ifCond' => 'Flow\TemplateHelper::ifCond',
					'tooltip' => 'Flow\TemplateHelper::tooltip',
					'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
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
		/** @var TemplateHelper $lightncandy */
		$lightncandy = Container::get( 'lightncandy' );
		$template = $lightncandy->getTemplate( $templateName );
		// @todo ugly hack...remove someday.  Requires switching to newest version
		// of lightncandy which supports recursive partial templates.
		if ( !array_key_exists( 'rootBlock', $args ) ) {
			$args['rootBlock'] = $args;
		}
		return call_user_func( $template, $args, $scopes );
	}

	// Helpers

	/**
	 * Generates a timestamp using the UUID, then calls the timestamp helper with it.
	 * @param array $args Expects string $uuid, string $str, bool $timeAgoOnly = false
	 * @param array $named No named arguments expected
	 * @return null|string
	 * @throws WrongNumberArgumentsException
	 */
	static public function uuidTimestamp( array $args, array $named ) {
		if ( count( $args ) !== 1 ) {
			throw new WrongNumberArgumentsException( $args, 'one' );
		}
		$uuid = $args[0];

		$obj = UUID::create( $uuid );
		if ( !$obj ) {
			return null;
		}

		// timestamp helper expects ms timestamp
		$timestamp = $obj->getTimestampObj()->getTimestamp() * 1000;
		return self::timestamp( $timestamp );
	}

	/**
	 * @param array $args Expects string $timestamp, string $str, bool $timeAgoOnly = false
	 * @param array $named No named arguments expected
	 * @return string
	 * @throws WrongNumberArgumentsException
	 */
	static public function timestampHelper( array $args, array $named ) {
		if ( count( $args ) < 1 || count( $args ) > 2 ) {
			throw new WrongNumberArgumentsException( $args, 'one', 'two' );
		}
		return self::timestamp(
			$args[0],
			isset( $args[1] ) ? $args[1] : false
		);
	}

	/**
	 * @param integer $timestamp milliseconds since the unix epoch
	 * @return string|false
	 */
	static protected function timestamp( $timestamp ) {
		global $wgLang, $wgUser;

		if ( !$timestamp ) {
			return false;
		}

		// source timestamps are in ms
		$timestamp /= 1000;
		$ts = new MWTimestamp( $timestamp );

		return self::html( self::processTemplate(
			'timestamp',
			array(
				'time_iso' => $timestamp,
				'time_ago' => $ts->getHumanTimestamp(),
				'time_readable' => $wgLang->userTimeAndDate( $timestamp, $wgUser ),
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
	 * @return string[] array(html, 'raw')
	 */
	static protected function html( $string ) {
		return array( $string, 'raw' );
	}

	/**
	 * @param array $args Expects one string argument to be output unescaped.
	 * @param array $named unused
	 * @return string[] array(html, 'raw')
	 */
	static public function htmlHelper( array $args, array $named ) {
		return self::html( isset( $args[0] ) ? $args[0] : 'undefined' );
	}

	/**
	 * @param array $args Expects one array $block
	 * @param array $named No named arguments expected
	 * @return string[]
	 * @throws WrongNumberArgumentsException
	 */
	static public function block( array $args, array $named ) {
		if ( !isset( $args[0] ) ) {
			throw new WrongNumberArgumentsException( $args, 'one' );
		}
		$block = $args[0];
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
		$inverse = isset( $options['inverse'] ) ? $options['inverse'] : null;
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
	 * Required to prevent recursion loop rendering nested posts
	 *
	 * @param array $args Expects array $rootBlock, array $revision
	 * @param array $named No named arguments expected
	 * @return string[]
	 * @throws WrongNumberArgumentsException
	 */
	static public function post( array $args, array $named ) {
		if ( count( $args ) !== 2 ) {
			throw new WrongNumberArgumentsException( $args, 'two' );
		}
		list( $rootBlock, $revision ) = $args;
		return self::html( self::processTemplate( 'flow_post', array(
			'revision' => $revision,
			'rootBlock' => $rootBlock,
		) ) );
	}

	/**
	 * @param array $args Expects array $revision, string $key = 'timeAndDate'
	 * @param array $named No named arguments expected
	 * @return string[]
	 * @throws WrongNumberArgumentsException
	 */
	static public function historyTimestamp( array $args, array $named ) {
		if ( !$args ) {
			throw new WrongNumberArgumentsException( $args, 'one', 'two' );
		}
		$revision = $args[0];
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
	 * @param array $args Expects array $revision
	 * @param array $named No named arguments expected
	 * @return string[]
	 * @throws WrongNumberArgumentsException
	 */
	static public function historyDescription( array $args, array $named ) {
		if ( count( $args ) !== 1 ) {
			throw new WrongNumberArgumentsException( $args, 'one' );
		}
		$revision = $args[0];
		$i18nKey = $revision['properties']['_key'];
		unset( $revision['properties']['_key'] );

		// a variety of the i18n history messages contain wikitext and require ->parse()
		return self::html( wfMessage( $i18nKey, $revision['properties'] )->parse() );
	}

	/**
	 * @param array $args Expects string $old, string $new
	 * @param array $named No named arguments expected
	 * @return string[]
	 * @throws WrongNumberArgumentsException
	 */
	static public function showCharacterDifference( array $args, array $named ) {
		if ( count( $args ) !== 2 ) {
			throw new WrongNumberArgumentsException( $args, 'two' );
		}
		list( $old, $new ) = $args;
		return self::html( \ChangesList::showCharacterDifference( $old, $new ) );
	}

	/**
	 * Creates a special script tag to be processed client-side. This contains extra template HTML, which allows
	 * the front-end to "progressively enhance" the page with more content which isn't needed in a non-JS state.
	 *
	 * @see FlowHandlebars.prototype.progressiveEnhancement in flow-handlebars.js for more details.
	 * @param array $options
	 * @return string[]
	 */
	static public function progressiveEnhancement( array $options ) {
		$fn = $options['fn'];
		$input = $options['hash'];
		$insertionType = empty( $input['type'] ) ? 'insert' : htmlspecialchars( $input['type'] );
		$target = empty( $input['target'] ) ? '' : 'data-target="' . htmlspecialchars( $input['target'] ) . '"';
		$sectionId = empty( $input['id'] ) ? '' : 'id="' . htmlspecialchars( $input['id'] ) . '"';

		return self::html(
			'<script name="handlebars-template-progressive-enhancement"' .
				' type="text/x-handlebars-template-progressive-enhancement"' .
				' data-type="' . $insertionType . '"' .
				' ' . $target .
				' ' . $sectionId .
			'>' .
				// Replace the nested script tag with a placeholder tag for recursive progressiveEnhancement
				str_replace( '</script>', '</flowprogressivescript>', $fn() ) .
			'</script>'
		);
	}

	/**
	 * @param array $args one or more arguments, i18n key and parameters
	 * @param array $named unused
	 * @return string Plaintext
	 */
	static public function l10n( array $args, array $named ) {
		$message = null;
		$str = array_shift( $args );

		return wfMessage( $str )->params( $args )->text();
	}
	/**
	 * @param array $args one or more arguments, i18n key and parameters
	 * @param array $named unused
	 * @return string[] HTML
	 */
	static public function l10nParse( array $args, array $named ) {
		$str = array_shift( $args );
		return self::html( wfMessage( $str, $args )->parse() );
	}

	/**
	 * @param array $args Expects seven arguments as follows:
	 *	   array $named No named arguments expected
	 *	   string $diffContent Plain text output of DifferenceEngine::getDiffBody
	 *	   string $oldTimestamp Time when the `old` content was created
	 *	   string $newTimestamp Time when the `new` content was created
	 *	   string $oldAuthor Creator of the `old` content
	 *	   string $newAuthor Creator of the `new` content
	 *	   string $oldLink Url pointing to `old` content
	 *	   string $newLink Url pointing to `new` content
	 *	   string $prevLink Url pointing to diff between `old` and its previous revision
	 *	   string $nextLink Url pointing to diff between `new` and its next revision
	 * @param array $named No named arguments expected
	 * @return string[] HTML wrapped in array to prevent lightncandy from escaping
	 * @throws WrongNumberArgumentsException
	 */
	static public function diffRevision( array $args, array $named ) {
		if ( count( $args ) !== 9 ) {
			throw new WrongNumberArgumentsException( $args, 'nine' );
		}
		list ( $diffContent, $oldTimestamp, $newTimestamp, $oldAuthor, $newAuthor, $oldLink, $newLink, $prevLink, $nextLink ) = $args;
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

		$renderer = Container::get( 'lightncandy' )->getTemplate( 'flow_revision_diff_header' );

		return self::html( $differenceEngine->addHeader(
			$diffContent,
			$renderer( array(
				'timestamp' => $oldTimestamp,
				'author' => $oldAuthor,
				'link' => $oldLink,
				'previous' => $prevLink,
			) ),
			$renderer( array(
				'timestamp' => $newTimestamp,
				'author' => $newAuthor,
				'link' => $newLink,
				'next' => $nextLink,
			) ),
			$multi,
			$notice
		) );
	}

	/**
	 * @param array $args Expects array $actions, string $moderationState
	 * @param array $named No named arguments expected
	 * @return string
	 * @throws WrongNumberArgumentsException
	 */
	static public function moderationAction( array $args, array $named ) {
		if ( count( $args ) !== 2 ) {
			throw new WrongNumberArgumentsException( $args, 'two' );
		}
		list( $actions, $moderationState ) = $args;
		return isset( $actions[$moderationState] ) ? $actions[$moderationState]['url'] : '';
	}

	/**
	 * @param array $args Expects one or more strings to join
	 * @param array $named No named arguments expected
	 * @return string all unnamed arguments joined together
	 */
	static public function concat( array $args, array $named ) {
		return implode( '', $args );
	}

	/**
	 * Return information about given user
	 *
	 * @param array $args Expects string $feature e.g. name, id
	 * @param array $named No named arguments expected
	 * @return string value of property
	 */
	static public function user( array $args, array $named ) {
		$feature = isset( $args[0] ) ? $args[0] : 'name';
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
	static protected function addReturnTo( $url ) {
		$ctx = RequestContext::getMain();
		$returnTo = $ctx->getTitle();
		if ( !$returnTo ) {
			return $url;
		}
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
	 * @param array $args Expects string $title
	 * @param array $named No named arguments expected
	 * @return string modified url
	 * @throws WrongNumberArgumentsException
	 */
	static public function linkWithReturnTo( array $args, array $named ) {
		if ( count( $args ) !== 1 ) {
			throw new WrongNumberArgumentsException( $args, 'one' );
		}
		$title = Title::newFromText( $args[0] );
		if ( !$title ) {
			return '';
		}
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
	 * @param array $args Expects string $contentType, string $content
	 * @param array $named No named arguments expected
	 * @return string
	 * @throws WrongNumberArgumentsException
	 */
	static public function escapeContent( array $args, array $named ) {
		if ( count( $args ) !== 2 ) {
			throw new WrongNumberArgumentsException( $args, 'two' );
		}
		list( $contentType, $content ) = $args;
		return $contentType === 'html' ? self::html( $content ) : $content;
	}

	/**
	 * Only perform action when conditions match
	 * @param string $value
	 * @param string $operator e.g. 'or'
	 * @param string $value2 to compare with
	 * @param array $options lightncandy hbhelper options
	 * @return mixed result of callback
	 * @throws FlowException Fails when callbacks are not Closure instances
	 * @param array @options
	 */
	static public function ifCond( $value, $operator, $value2, $options ) {
		$doCallback = false;

		// Perform operator
		// FIXME: Rename to || to be consistent with other operators
		if ( $operator === 'or' ) {
			if ( $value || $value2 ) {
				$doCallback = true;
			}
		} elseif ( $operator === '===' ) {
			if ( $value === $value2 ) {
				$doCallback = true;
			}
		} elseif ( $operator === '!==' ) {
			if ( $value !== $value2 ) {
				$doCallback = true;
			}
		} else {
			return '';
		}

		if ( $doCallback ) {
			$fn = $options['fn'];
			if ( !$fn instanceof Closure ) {
				throw new FlowException( 'Expected callback to be Closure instance' );
			}
			return $fn();
		} elseif ( isset( $options['inverse'] ) ) {
			$inverse = $options['inverse'];
			if ( !$inverse instanceof Closure ) {
				throw new FlowException( 'Expected inverse callback to be Closure instance' );
			}
			return $inverse();
		} else {
			return '';
		}
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
				'contextClass' => $params['contextClass'] ? 'mw-ui-' . $params['contextClass'] : null,
				'extraClass' => $params['extraClass'] ?: '',
				'blockClass' => $params['isBlock'] ? 'flow-ui-tooltip-block' : null,
				'content' => $fn(),
			) )
		);
	}
}
