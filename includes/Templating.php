<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Block\TopicBlock;
use Flow\Block\PostBlock;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use OutputPage;
// These dont really belong here
use Html;
use Linker;
use MWTimestamp;
use RequestContext;
use Title;
use User;
use Flow\View\PostActionMenu;

class Templating {
	protected $namespaces;
	protected $globals;
	protected $output;

	public function __construct( UrlGenerator $urlGenerator, OutputPage $output, array $namespaces = array(), array $globals = array() ) {
		$this->urlGenerator = $urlGenerator;
		$this->output = $output;
		foreach ( $namespaces as $ns => $path ) {
			$this->addNamespace( $ns, $path );
		}
		$this->globals = $globals;
	}

	public function getOutput() {
		return $this->output;
	}

	public function addNamespace( $ns, $path ) {
		$this->namespaces[$ns] = rtrim( $path, '/' );
	}

	public function addGlobalVariable( $name, $value ) {
		$this->globals[$name] = $value;
	}

	public function render( $file, array $vars = array(), $return = false ) {
		$file = $this->applyNamespacing( $file );

		ob_start();
		$this->_render( $file, $vars + $this->globals );
		$content = ob_get_contents();
		ob_end_clean();

		if ( $return ) {
			return $content;
		} else {
			$this->output->addHTML( $content );
		}
	}

	protected function applyNamespacing( $file ) {
		if ( false === strpos( $file, ':' ) ) {
			return $file;
		}
		list( $ns, $file ) = explode( ':', $file, 2 );
		if ( !isset( $this->namespaces[$ns] ) ) {
			throw new MWException( 'Unknown template namespace' );
		}

		return $this->namespaces[$ns] . '/' . ltrim( $file, '/' );
	}

	protected function _render( $__file__, $__vars__ ) {
		extract( $__vars__ );

		include $__file__;
	}

	// Helper methods for the view
	//
	// Everything below here *DOES* *NOT*  belong in this class.  Its also pointless for us to invent a properly
	// abstracted templating implementation so these can be elsewhere.  Figure out if we can transition to an
	// industry standard templating solution and stop the NIH.

	public function generateUrl( $workflow, $action = 'view', array $query = array() ) {
		return $this->urlGenerator->generateUrl( $workflow, $action, $query );
	}

	public function getPagingLink( $block, $direction, $offset, $limit ) {
		$output = '';

		// Use the message/class flow-paging-fwd or flow-paging-rev
		//  depending on direction
		$output .= \Html::element(
			'a',
			array(
				'href' => $this->generateUrl(
					$block->getWorkflowId(),
					'view',
					array(
						$block->getName().'[offset-id]' => $offset,
						$block->getName().'[offset-dir]' => $direction,
						$block->getName().'[limit]' => $limit,
					)
				),
			),
			wfMessage( 'flow-paging-'.$direction )->parse()
		);

		$output = \Html::rawElement(
			'div',
			array(
				'class' => 'flow-paging flow-paging-'.$direction,
				'data-offset' => $offset,
				'data-direction' => $direction,
				'data-limit' => $limit,
			),
			$output
		);

		return $output;
	}

	public function userToolLinks( $userId, $userText ) {
		if ( $userText instanceof MWMessage ) {
			// username was moderated away, we dont know who this is
			return '';
		}

		return Linker::userLink( $userId, $userText ) . Linker::userToolLinks( $userId, $userText );
	}
}
