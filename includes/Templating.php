<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Block\TopicBlock;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Rendering\UIElement;
use OutputPage;
// These dont really belong here
use DOMDocument;
use Html;
use Linker;
use MWTimestamp;
use RequestContext;
use Title;
use User;

class Templating {
	public $urlGenerator;
	protected $output;
	protected $namespaces;
	protected $globals;

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

		$content = $this->postprocess( $content );

		if ( $return ) {
			return $content;
		} else {
			$this->output->addHTML( $content );
		}
	}

	/**
	 * Postprocessing for a template call.
	 *
	 * Currently, just allows HTML to call a Flow UI Element.
	 * @param  string $content HTML content
	 * @return string          Postprocessed HTML
	 */
	protected function postprocess( $content ) {
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );

		// Sort of dirty hack to improve performance
		if ( strpos( $content, '<flow-element' ) !== false ) {
			$originalUseInternalErrors = libxml_use_internal_errors( true );

			$dom = new DOMDocument();
			$dom->loadHTML( $content );

			$embeddedElements = $dom->getElementsByTagName( 'flow-element' );

			foreach( $embeddedElements as $element ) {
				$params = array();

				foreach( $element->attributes as $attr ) {
					$params[$attr->nodeName] = $attr->nodeValue;
				}

				$replacementHTML = $this->renderElement( $params['elementname'], $params, true );
				$replacementFragment = $dom->createDocumentFragment();
				$replacementFragment->appendXML( $replacementHTML );

				$element->parentNode->replaceChild( $replacementFragment, $element );
			}

			$content = $dom->saveHTML();

			libxml_use_internal_errors( $originalUseInternalErrors );
		}

		return $content;
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
	
	public function renderElement( $element, $parameters, $return = false ) {
		$parameters += array(
			'templating' => $this,
			'urlGenerator' => $this->urlGenerator,
		);

		// @todo Pass to constructor instead once initial approach is validated
		$elementFactory = Container::get( 'factory.uielement' );
		$html = $elementFactory->getElement( $element, $parameters )->render();

		if ( $return ) {
			return $html;
		} else {
			$this->output->addHTML( $html );
		}
	}

	public function getUrlGenerator() {
		return $this->urlGenerator;
	}

	public function generateUrl( $workflow, $action = 'view', array $query = array() ) {
		return $this->getUrlGenerator()->generateUrl( $workflow, $action, $query );
	}

	public function renderPost( PostRevision $post, Block $block, $return = true ) {
		return $this->renderElement( 'post',
			array(
				'post' => $post,
				'block' => $block,
				'urlGenerator' => $this->urlGenerator,
				'user' => Container::get( 'user' ),
			),
			$return
		);
	}

	public function renderTopic( PostRevision $root, TopicBlock $block, $return = true ) {
		$container = Container::getContainer();

		return $this->render( "flow:topic.html.php", array(
			'block' => $block,
			'topic' => $block->getWorkflow(),
			'root' => $root,
			'permissions' => new PostActionPermissions( $container['flow_actions'], $container['user'] ),
		), $return );
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
		static $cache = array();
		if ( isset( $cache[$userId][$userText] ) ) {
			return $cache[$userId][$userText];
		}

		if ( $userText instanceof MWMessage ) {
			// username was moderated away, we dont know who this is
			$res = '';
		} else {
			$res = Linker::userLink( $userId, $userText ) . Linker::userToolLinks( $userId, $userText );
		}
		return $cache[$userId][$userText] = $res;
	}

	/**
	 * Returns a message that displays information on the participants of a topic.
	 *
	 * @param PostRevision $post
	 * @param int[optional] $registered The identifier that was returned when
	 * registering the callback via PostRevision::registerRecursive()
	 * @return string
	 */
	public function printParticipants( PostRevision $post, $registered = null ) {
		$participants = $post->getRecursiveResult( $registered );
		$participantCount = count( $participants );

		$originalPoster = array_shift( $participants );
		$mostRecentPoster = array_pop( $participants );
		$secondMostRecentPoster = array_pop( $participants );

		return wfMessage(
			'flow-topic-participants',
			$participantCount,
			max( 0, $participantCount - 3 ),
			$originalPoster,
			$mostRecentPoster,
			$secondMostRecentPoster
		)->parse();
	}

	/**
	 * Formats a revision's usertext for displaying. Usually, the revision's
	 * usertext can just be displayed. In the event of moderation, however, that
	 * info should not be exposed.
	 *
	 * If a specific i18n message is available for a certain moderation level,
	 * that message will be returned (well, unless the user actually has the
	 * required permissions to view the full username). Otherwise, in normal
	 * cases, the full username will be returned.
	 *
	 * @param AbstractRevision $revision Revision to display usertext for
	 * @param User[optional] $permissionsUser User to display usertext to
	 * @return string
	 */
	public function getUserText( AbstractRevision $revision, User $permissionsUser = null ) {
		$state = $revision->getModerationState();
		$username = $revision->getUserText();

		// Messages: flow-hide-usertext, flow-delete-usertext, flow-censor-usertext
		$message = wfMessage( "flow-$state-usertext", $username );

		if ( !$revision->isAllowed( $permissionsUser ) && $message->exists() ) {
			return $message->text();
		} else {
			return $username;
		}
	}

	/**
	 * Formats a post's creator name for displaying. Usually, the post's creator
	 * name can just be displayed. In the event of moderation, however, that
	 * info should not be exposed.
	 *
	 * If a specific i18n message is available for a certain moderation level,
	 * that message will be returned (well, unless the user actually has the
	 * required permissions to view the full username). Otherwise, in normal
	 * cases, the full creator name will be returned.
	 *
	 * @param PostRevision $revision Revision to display creator name for
	 * @param User[optional] $permissionsUser User to display creator name to
	 * @return string
	 */
	public function getCreatorText( PostRevision $revision, User $permissionsUser = null ) {
		$state = $revision->getModerationState();
		$username = $revision->getCreatorNameRaw();

		// Messages: flow-hide-usertext, flow-delete-usertext, flow-censor-usertext
		$message = wfMessage( "flow-$state-usertext", $username );

		if ( !$revision->isAllowed( $permissionsUser ) && $message->exists() ) {
			return $message->text();
		} else {
			return $username;
		}
	}

	/**
	 * Formats a revision's content for displaying. Usually, the revisions's
	 * content can just be displayed. In the event of moderation, however, that
	 * info should not be exposed.
	 *
	 * If a specific i18n message is available for a certain moderation level,
	 * that message will be returned (well, unless the user actually has the
	 * required permissions to view the full content). Otherwise, in normal
	 * cases, the full content will be returned.
	 *
	 * @param AbstractRevision $revision Revision to display content for
	 * @param string[optional] $format Format to output content in (html|wikitext)
	 * @param User[optional] $permissionsUser User to display content to
	 * @return string
	 */
	public function getContent( AbstractRevision $revision, $format = 'html', User $permissionsUser = null ) {
		$state = $revision->getModerationState();
		$user = $revision->getModeratedByUserText();
		$moderatedAt = new MWTimestamp( $revision->getModerationTimestamp() );

		// Messages: flow-hide-content, flow-delete-content, flow-censor-content
		$message = wfMessage( "flow-$state-content", $user, $moderatedAt->getHumanTimestamp() );

		if ( !$revision->isAllowed( $permissionsUser ) && $message->exists() ) {
			return $message->text();
		} else {
			return $revision->getContent( $format );
		}
	}
}
