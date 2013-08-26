<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Block\TopicBlock;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use OutputPage;
// These dont really belong here
use RequestContext;
use MWTimestamp;

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
	public function generateUrl( $workflow, $action = 'view', array $query = array() ) {
		return $this->urlGenerator->generateUrl( $workflow, $action, $query );
	}

	public function renderPost( PostRevision $post, Block $block, $return = true ) {
		return $this->render(
			'flow:post.html.php',
			array(
				'block' => $block,
				'post' => $post,
			),
			$return
		);
	}

	public function renderTopic( PostRevision $root, TopicBlock $block, $return = true ) {
		return $this->render( "flow:topic.html.php", array(
			'block' => $block,
			'topic' => $block->getWorkflow(),
			'root' => $root,
		), $return );
	}

	public function timeAgo( $timestamp ) {
		if ( $timestamp instanceof UUID ) {
			$timestamp = $timestamp->getTimestamp();
		}
		return self::getApproxHumanTimestamp( new MWTimestamp( $timestamp ), new MWTimestamp );
	}

	static public function getApproxHumanTimestamp( MWTimestamp $ts, MWTimestamp $relativeTo ) {
		$diff = $ts->diff( $relativeTo );
		$lang = RequestContext::getMain()->getLanguage();
		if ( $diff->y ) {
			return wfMessage( 'flow-years-ago' )->inLanguage( $lang )->numParams( $diff->y )->text();
		} elseif ( $diff->m ) {
			return wfMessage( 'flow-months-ago' )->inLanguage( $lang )->numParams( $diff->m )->text();
		} elseif ( $diff->d ) {
			return wfMessage( 'flow-days-ago' )->inLanguage( $lang )->numParams( $diff->d )->text();
		} elseif ( $diff-h ) {
			return wfMessage( 'hours-ago' )->inLanguage( $lang )->numParams( $diff->h )->text();
		} elseif ( $diff->i ) {
			return wfMessage( 'minutes-ago' )->inLanguage( $lang )->numParams( $diff->i )->text();
		} elseif ( $diff->s >= 30 ) {
			return wfMessage( 'seconds-ago' )->inLanguage( $lang )->numParams( $diff->s )->text();
		} else {
			return wfMessage( 'just-now' )->text();
		}
	}

}

