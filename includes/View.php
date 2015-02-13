<?php

namespace Flow;

use Flow\Block\AbstractBlock;
use Flow\Exception\InvalidActionException;
use Flow\Model\Anchor;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use ContextSource;
use Html;
use IContextSource;
use Message;
use WebRequest;


class View extends ContextSource {
	/**
	 * @var Templating $templating
	 */
	protected $templating;

	/**
	 * @var UrlGenerator $urlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var TemplateHelper $lightncandy
	 */
	protected $lightncandy;

	function __construct(
		Templating $templating,
		UrlGenerator $urlGenerator,
		TemplateHelper $lightncandy,
		IContextSource $requestContext
	) {
		$this->templating = $templating;
		$this->urlGenerator = $urlGenerator;
		$this->lightncandy = $lightncandy;
		$this->setContext( $requestContext );
	}

	public function show( WorkflowLoader $loader, $action ) {
		wfProfileIn( __CLASS__ . '-init' );

		$out = $this->getOutput();
		$styles = array(
			'mediawiki.ui',
			'mediawiki.ui.anchor',
			'mediawiki.ui.button',
			'mediawiki.ui.input',
			'mediawiki.ui.text',
			'ext.flow.styles',
			'ext.flow.mediawiki.ui.tooltips',
			'ext.flow.mediawiki.ui.form',
			'ext.flow.mediawiki.ui.modal',
			'ext.flow.mediawiki.ui.text',
			'ext.flow.icons.styles',
			'ext.flow.board.styles',
			'ext.flow.board.topic.styles'
		);
		$out->addModuleStyles( $styles );
		$out->addModules( array( 'ext.flow' ) );

		// Allow other extensions to add modules
		wfRunHooks( 'FlowAddModules', array( $out ) );

		$workflow = $loader->getWorkflow();

		$title = $workflow->getArticleTitle();

		$request = $this->getRequest();
		$user = $this->getUser();

		$blocks = $loader->getBlocks();
		wfProfileOut( __CLASS__ . '-init' );

		$parameters = $this->extractBlockParameters( $action, $request, $blocks );

		$wasPosted = $request->wasPosted();
		if ( $wasPosted ) {
			wfProfileIn( __CLASS__ . '-submit' );
			$blocksToCommit = $loader->handleSubmit( $this, $action, $parameters );
			if ( $blocksToCommit ) {
				if ( !$user->matchEditToken( $request->getVal( 'wpEditToken' ) ) ) {
					// only render the failed blocks
					$blocks = $blocksToCommit;
					foreach ( $blocks as $block ) {
						$block->addError( 'edit-token', $this->msg( 'sessionfailure' ) );
					}
				} else {
					$loader->commit( $workflow, $blocksToCommit );
					$this->redirect( $workflow, 'view' );
					wfProfileOut( __CLASS__ . '-submit' );
					return;
				}
			}
			wfProfileOut( __CLASS__ . '-submit' );
		} else {
			foreach ( $blocks as $block ) {
				$block->init( $this, $action );
			}
		}

		wfProfileIn( __CLASS__ . '-serialize' );
		// @todo This and API should use same code
		$apiResponse = array(
			'title' => $title->getPrefixedText(),
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
			),
		);

		$editToken = $user->getEditToken();
		foreach ( $blocks as $block ) {
			if ( $wasPosted ? $block->canSubmit( $action ) : $block->canRender( $action ) ) {
				$apiResponse['blocks'][] = $block->renderApi( $parameters[$block->getName()] )
								+ array(
									'title' => $apiResponse['title'],
									'block-action-template' => $block->getTemplate( $action ),
									'editToken' => $editToken,
								);
			}
		}

		// Please note that all blocks can set page title, which may cause them
		// to override one another's titles
		foreach ( $blocks as $block ) {
			$block->setPageTitle( $this->templating, $this->getOutput() );
		}

		if ( count( $apiResponse['blocks'] ) === 0 ) {
			throw new InvalidActionException( "No blocks accepted action: $action" );
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
		wfProfileOut( __CLASS__ . '-serialize' );

		// Update newtalk and watchlist notification status on view action of any workflow
		// since the normal page view that resets notification status is not accessiable
		// anymore due to Flow occupation
		if ( $action === 'view' ) {
			$user->clearNotification( $title );
		}

		/**
		header( 'Content-Type: application/json; content=utf-8' );
		$data = json_encode( $apiResponse );
		//return;
		die( $data );
		**/

		// Render with lightncandy. The exact template to render
		// will likely need to vary, but not yet.
		wfProfileIn( __CLASS__ . '-render' );

		// Render the flow-component wrapper
		if ( !empty( $apiResponse['blocks'] ) ) {
			$renderedBlocks = array();

			foreach ( $apiResponse['blocks'] as $block ) {
				// @todo find a better way to do this; potentially make all blocks their own components
				switch ( $block['type'] ) {
					case 'board-history':
						$flowComponent = 'boardHistory';
						break;
					default:
						$flowComponent = 'board';
				}

				// Don't re-render a block type twice in one page
				if ( isset( $renderedBlocks[$flowComponent] ) ) {
					continue;
				}
				$renderedBlocks[$flowComponent] = true;

				// Get the block loop template
				$template = $this->lightncandy->getTemplate( 'flow_block_loop' );
				// Output the component, with the rendered blocks inside it
				$out->addHTML( Html::rawElement(
					'div',
					array(
						'class'               => 'flow-component',
						'data-flow-component' => $flowComponent,
						'data-flow-id'        => $apiResponse['workflow'],
					),
					$template( $apiResponse )
				) );
			}
		}

		wfProfileOut( __CLASS__ . '-render' );
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
	public function extractBlockParameters( $action, WebRequest $request, array $blocks ) {
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
