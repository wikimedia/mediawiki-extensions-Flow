<?php

namespace Flow;

use ContextSource;
use Flow\Block\AbstractBlock;
use Flow\Exception\InvalidActionException;
use Flow\Model\Anchor;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Html;
use Hooks;
use IContextSource;
use Message;
use OutputPage;
use Title;
use WebRequest;

class View extends ContextSource {
	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var TemplateHelper
	 */
	protected $lightncandy;

	/**
	 * @var FlowActions
	 */
	protected $actions;

	function __construct(
		UrlGenerator $urlGenerator,
		TemplateHelper $lightncandy,
		IContextSource $requestContext,
		FlowActions $actions
	) {
		$this->urlGenerator = $urlGenerator;
		$this->lightncandy = $lightncandy;
		$this->setContext( $requestContext );
		$this->actions = $actions;
	}

	public function show( WorkflowLoader $loader, $action ) {
		$blocks = $loader->getBlocks();

		$parameters = $this->extractBlockParameters( $action, $blocks );
		foreach ( $loader->getBlocks() as $block ) {
			$block->init( $this, $action );
		}

		if ( $this->getRequest()->wasPosted() ) {
			$retval = $this->handleSubmit( $loader, $action, $parameters );
			// successful submission
			if ( $retval === true ) {
				$this->redirect( $loader->getWorkflow(), 'view' );
				return;
			// only render the returned subset of blocks
			} elseif ( is_array( $retval ) ) {
				$blocks = $retval;
			}
		}

		$apiResponse = $this->buildApiResponse( $loader, $blocks, $action, $parameters );

		$output = $this->getOutput();
		$output->enableOOUI();
		$this->addModules( $output, $action );
		// Please note that all blocks can set page title, which may cause them
		// to override one another's titles
		foreach ( $blocks as $block ) {
			$block->setPageTitle( $output );
		}


		$this->renderApiResponse( $apiResponse );
	}

	protected function addModules( OutputPage $out, $action ) {
		if ( $this->actions->hasValue( $action, 'modules' ) ) {
			$out->addModules( $this->actions->getValue( $action, 'modules' ) );
		} else {
			$out->addModules( array( 'ext.flow' ) );
		}

		if ( $this->actions->hasValue( $action, 'moduleStyles' ) ) {
			$out->addModuleStyles( $this->actions->getValue( $action, 'moduleStyles' ) );
		} else {
			$out->addModuleStyles( array(
				'mediawiki.ui',
				'mediawiki.ui.anchor',
				'mediawiki.ui.button',
				'mediawiki.ui.input',
				'mediawiki.ui.icon',
				'mediawiki.ui.text',
				'ext.flow.styles.base' ,
				'ext.flow.mediawiki.ui.form',
				'ext.flow.mediawiki.ui.text',
				'oojs-ui.styles.icons',
				'oojs-ui.styles.icons-layout',
				'oojs-ui.styles.icons-interactions',
				'ext.flow.board.styles',
				'ext.flow.board.topic.styles',
				'oojs-ui.styles.icons',
				'oojs-ui.styles.icons-alerts',
				'oojs-ui.styles.icons-content',
				'oojs-ui.styles.icons-layout',
				'oojs-ui.styles.icons-movement',
				'oojs-ui.styles.icons-indicators',
				'oojs-ui.styles.icons-editing-core',
				'oojs-ui.styles.icons-moderation',
				// Needed for pending texture while switching editors
				'oojs-ui.styles.textures'
			) );
		}

		// Add Parsoid modules if necessary
		Conversion\Utils::onFlowAddModules( $out );
		// Allow other extensions to add modules
		Hooks::run( 'FlowAddModules', array( $out ) );
	}

	protected function handleSubmit( WorkflowLoader $loader, $action, array $parameters ) {
		$this->getOutput()->enableClientCache( false );

		$blocksToCommit = $loader->handleSubmit( $this, $action, $parameters );
		if ( !$blocksToCommit ) {
			return false;
		}

		if ( !$this->getUser()->matchEditToken( $this->getRequest()->getVal( 'wpEditToken' ) ) ) {
			// this uses the above $blocksToCommit reference to only render the failed blocks
			foreach ( $blocksToCommit as $block ) {
				$block->addError( 'edit-token', $this->msg( 'sessionfailure' ) );
			}
			return $blocksToCommit;
		}

		$loader->commit( $blocksToCommit );
		return true;
	}

	protected function buildApiResponse( WorkflowLoader $loader, array $blocks, $action, array $parameters ) {
		$workflow = $loader->getWorkflow();
		$title = $workflow->getArticleTitle();
		$user = $this->getUser();
		$categories = array_keys( $title->getParentCategories() );
		$categoryObject = array();
		$linkedCategories = array();

		// Transform the raw category names into links
		foreach ( $categories as $value ) {
			$categoryTitle = Title::newFromText( $value );
			$categoryObject[ $value ] = array(
				'name' => $value,
				'exists' => $categoryTitle->exists()
			);
			$linkedCategories[] = \Linker::link( $categoryTitle, htmlspecialchars( $categoryTitle->getText() ) );
		}

		// @todo This and API should use same code
		$apiResponse = array(
			'title' => $title->getPrefixedText(),
			'categories' => $categoryObject,
			// We need to store the link to the Special:Categories page from the
			// back end php script, because there is no way in JS front end to
			// get the localized link of a special page
			'specialCategoryLink' => \SpecialPage::getTitleFor( 'Categories' )->getLocalURL(),
			'workflow' => $workflow->isNew() ? '' : $workflow->getId()->getAlphadecimal(),
			'blocks' => array(),
			'isWatched' => $user->isWatched( $title ),
			'watchable' => !$user->isAnon(),
			'links' => array(
				'watch-board' => array(
					'url' => $title->getLocalUrl( 'action=watch' ),
				),
				'unwatch-board' => array(
					'url' => $title->getLocalUrl( 'action=unwatch' ),
				),
			)
		);

		$editToken = $user->getEditToken();
		$wasPosted = $this->getRequest()->wasPosted();
		$topicListBlock = null;
		foreach ( $blocks as $block ) {
			if ( $wasPosted ? $block->canSubmit( $action ) : $block->canRender( $action ) ) {
				$apiResponse['blocks'][$block->getName()] = $block->renderApi( $parameters[$block->getName()] )
								+ array(
									'title' => $apiResponse['title'],
									'block-action-template' => $block->getTemplate( $action ),
									'editToken' => $editToken,
								);
				if ( $block->getName() == 'topiclist' ) {
					$topicListBlock = $block;
				}
			}
		}

		// Add category items to the header if they exist
		if ( count( $linkedCategories ) > 0 && isset( $apiResponse['blocks']['header'] ) ) {
			$apiResponse['blocks']['header']['categories'] = array(
				'link' => \Linker::link(
						\SpecialPage::getTitleFor( 'Categories' ),
						wfMessage( 'pagecategories' )->params( count( $linkedCategories ) )->text()
					) . wfMessage( 'colon-separator' )->text(),
				'items' => $linkedCategories
			);
		}

		if ( isset( $topicListBlock ) && isset( $parameters['topiclist'] ) ) {
			$apiResponse['toc'] = $topicListBlock->renderTocApi(
				$apiResponse['blocks']['topiclist'],
				$parameters['topiclist']
			);
		}

		if ( count( $apiResponse['blocks'] ) === 0 ) {
			throw new InvalidActionException( "No blocks accepted action: $action", 'invalid-action' );
		}

		array_walk_recursive( $apiResponse, function( &$value ) {
			if ( $value instanceof Anchor ) {
				$anchor = $value;
				$value = $value->toArray();

				// TODO: We're looking into another approach for this
				// using a parser function, so the URL doesn't have to be
				// fully qualified.
				// See https://bugzilla.wikimedia.org/show_bug.cgi?id=66746
				$value['url'] = $anchor->getFullURL();

			} elseif ( $value instanceof Message ) {
				$value = $value->text();
			} elseif ( $value instanceof UUID ) {
				$value = $value->getAlphadecimal();
			}
		} );

		return $apiResponse;
	}

	protected function renderApiResponse( array $apiResponse ) {
		// Render the flow-component wrapper
		if ( empty( $apiResponse['blocks'] ) ) {
			return array();
		}

		$out = $this->getOutput();

		$jsonBlobResponse = $apiResponse;

		// Temporary fix for T107170
		array_walk_recursive( $jsonBlobResponse, function ( &$value, $key ) {
			if ( stristr( $key, 'Token' ) !== false ) {
				$value = null;
			}
		} );

		// Add JSON blob for OOUI widgets
		$out->addJsConfigVars( 'wgFlowData', $jsonBlobResponse );

		$renderedBlocks = array();
		foreach ( $apiResponse['blocks'] as $block ) {
			// @todo find a better way to do this; potentially make all blocks their own components
			switch ( $block['type'] ) {
				case 'board-history':
					$flowComponent = 'boardHistory';
					$page = 'history';
					break;
				case 'topic':
					if ( $block['submitted']['action'] === 'history' ) {
						$page = 'history';
						$flowComponent = 'boardHistory';
					} else {
						$page = 'topic';
						$flowComponent = 'board';
					}
					break;
				default:
					$flowComponent = 'board';
					$page = 'board';
			}

			// Don't re-render a block type twice in one page
			if ( isset( $renderedBlocks[$flowComponent] ) ) {
				continue;
			}
			$renderedBlocks[$flowComponent] = true;

			// Get the block loop template
			$template = $this->lightncandy->getTemplate( 'flow_block_loop' );

			$classes = array( 'flow-component', "flow-$page-page" );

			// Always add mw-content-{ltr,rtl} class
			$title = Title::newFromText( $apiResponse['title'] );
			$classes[] = 'mw-content-' . $title->getPageViewLanguage()->getDir();

			// Output the component, with the rendered blocks inside it
			$out->addHTML( Html::rawElement(
				'div',
				array(
					'class'               => implode( ' ', $classes ),
					'data-flow-component' => $flowComponent,
					'data-flow-id'        => $apiResponse['workflow'],
				),
				$template( $apiResponse )
			) );
		}
	}

	protected function redirect( Workflow $workflow ) {
		$link = $this->urlGenerator->workflowLink(
			$workflow->getArticleTitle(),
			$workflow->getId()
		);
		$this->getOutput()->redirect( $link->getFullURL() );
	}

	/**
	 * Helper function extracts parameters from a WebRequest.
	 *
	 * @param string $action
	 * @param WebRequest $request
	 * @param AbstractBlock[] $blocks
	 * @return array
	 */
	public function extractBlockParameters( $action, array $blocks ) {
		$request = $this->getRequest();
		$result = array();
		// BC for old parameters enclosed in square brackets
		foreach ( $blocks as $block ) {
			$name = $block->getName();
			$result[$name] = $request->getArray( $name, array() );
		}
		// BC for topic_list renamed to topiclist
		if ( isset( $result['topiclist'] ) && !$result['topiclist'] ) {
			$result['topiclist'] = $request->getArray( 'topic_list', array() );
		}
		$globalData = array( 'action' => $action );
		foreach ( $request->getValues() as $name => $value ) {
			// between urls only allowing [-_.] as unencoded special chars and
			// php mangling all of those into '_', we have to split on '_'
			if ( false !== strpos( $name, '_' ) ) {
				list( $block, $var ) = explode( '_', $name, 2 );
				// flow_xxx is global data for all blocks
				if ( $block === 'flow' ) {
					$globalData[$var] = $value;
				} else {
					$result[$block][$var] = $value;
				}
			}
		}

		foreach ( $blocks as $block ) {
			$result[$block->getName()] += $globalData;
		}

		return $result;
	}
}
