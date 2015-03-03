<?php

namespace Flow;

use ContextSource;
use Flow\Block\AbstractBlock;
use Flow\Exception\InvalidActionException;
use Flow\Model\Anchor;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Html;
use IContextSource;
use Message;
use OutputPage;
use Title;
use WebRequest;


class View extends ContextSource {
	/**
	 * @var UrlGenerator $urlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var TemplateHelper $lightncandy
	 */
	protected $lightncandy;

	function __construct(
		UrlGenerator $urlGenerator,
		TemplateHelper $lightncandy,
		IContextSource $requestContext
	) {
		$this->urlGenerator = $urlGenerator;
		$this->lightncandy = $lightncandy;
		$this->setContext( $requestContext );
	}

	public function show( WorkflowLoader $loader, $action ) {
		wfProfileIn( __CLASS__ . '-init' );

		$blocks = $loader->getBlocks();
		wfProfileOut( __CLASS__ . '-init' );

		$parameters = $this->extractBlockParameters( $action, $blocks );
		foreach ( $loader->getBlocks() as $block ) {
			$block->init( $this, $action );
		}

		if ( $this->getRequest()->wasPosted() ) {
			$retval = $this->handleSubmit( $loader, $action, $parameters );
			// successfull submission
			if ( $retval === true ) {
				return;
			// only render the returned subset of blocks
			} elseif ( is_array( $retval ) ) {
				$blocks = $retval;
			}
		}

		$apiResponse = $this->buildApiResponse( $loader, $blocks, $action, $parameters );

		/**
		header( 'Content-Type: application/json; content=utf-8' );
		$data = json_encode( $apiResponse );
		//return;
		die( $data );
		**/

		$output = $this->getOutput();
		$this->addModules( $output, $apiResponse );
		// Please note that all blocks can set page title, which may cause them
		// to override one another's titles
		foreach ( $blocks as $block ) {
			$block->setPageTitle( $output );
		}


		$this->renderApiResponse( $apiResponse );
	}

	protected function addModules( OutputPage $out, array $apiResponse ) {
		$modules = $moduleStyles = array();
		foreach ( $apiResponse['blocks'] as $block ) {
			if ( isset( $block['modules'] ) ) {
				$modules = array_merge( $modules, $block['modules'] );
			}
			if ( isset( $block['moduleStyles'] ) ) {
				$moduleStyles = array_merge( $moduleStyles, $block['moduleStyles'] );
			}
		}

		if ( $moduleStyles ) {
			$out->addModuleStyles( array_unique( $moduleStyles ) );
		}
		if ( $modules ) {
			$out->addModules( array_unique( $modules ) );
		}

		// Allow other extensions to add modules
		wfRunHooks( 'FlowAddModules', array( $out ) );
	}

	protected function handleSubmit( WorkflowLoader $loader, $action, array $parameters ) {
		$this->getOutput()->enableClientCache( false );

		$blocksToCommit = $loader->handleSubmit( $this, $action, $parameters );
		if ( !$blocksToCommit ) {
			return false;
		}

		if ( !$this->getUser()->matchEditToken( $this->getRequest()->getVal( 'wpEditToken' ) ) ) {
			// this uses the above $blocks reference to only render the failed blocks
			foreach ( $blocks as $block ) {
				$block->addError( 'edit-token', $this->msg( 'sessionfailure' ) );
			}
			return $blocksToCommit;
		}

		$loader->commit( $blocksToCommit );
		$this->redirect( $loader->getWorkflow(), 'view' );
		return true;
	}

	protected function buildApiResponse( WorkflowLoader $loader, array $blocks, $action, array $parameters ) {
		$workflow = $loader->getWorkflow();
		$title = $workflow->getArticleTitle();
		$user = $this->getUser();

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
		$wasPosted = $this->getRequest()->wasPosted();
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

		return $apiResponse;
	}

	protected function renderApiResponse( array $apiResponse ) {
		// Render the flow-component wrapper
		if ( empty( $apiResponse['blocks'] ) ) {
			return array();
		}

		$out = $this->getOutput();
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
