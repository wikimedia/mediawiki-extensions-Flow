<?php

namespace Flow;

use ApiMain;
use FauxRequest;
use Flow\Model\AbstractRevision;
use Flow\Model\Anchor;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Search\Connection;
use Flow\Search\SearchEngine;
use FormSpecialPage;
use HTMLForm;
use Message;
use Status;
use Title;

class SpecialFlowSearch extends FormSpecialPage {
	/**
	 * @var string
	 */
	protected $term = '';

	/**
	 * @var Title|null
	 */
	protected $page;

	/**
	 * @var int[]
	 */
	protected $namespaces;

	/**
	 * @var string
	 */
	protected $moderationState = array();

	/**
	 * @var string
	 */
	protected $sort = 'relevance';

	/**
	 * @var string|false
	 */
	protected $type = false;

	function __construct() {
		// @todo: check if search is possible (needs ES) and show error if not

		parent::__construct( 'FlowSearch' );
	}

	protected function getFormFields() {
		return array(
			'qterm' => array(
				'id' => 'mw-flow-special-search-term',
				'name' => 'term',
				'type' => 'text',
				'label-message' => 'flow-special-search-term',
				'default' => $this->term,
			),
			'qtitle' => array(
				'id' => 'mw-flow-special-search-page',
				'name' => 'page', // I'd prefer "title" here, but that auto-fill current page title (Special:FlowSearch)
				'type' => 'text',
				'label-message' => 'flow-special-search-page',
				'default' => $this->page ? $this->page->getPrefixedDBkey() : '',
			),
			'qnamespaces' => array(
				'id' => 'mw-flow-special-search-namespaces',
				'name' => 'namespaces',
				'type' => 'multiselect',
				'label-message' => 'flow-special-search-namespaces',
				'options' => $this->getNamespaces(),
				'default' => $this->namespaces,
			),
			'qmoderationState' => array(
				'id' => 'mw-flow-special-search-moderationstates',
				'name' => 'moderationStates',
				'type' => 'multiselect',
				'label-message' => 'flow-special-search-moderationstates',
				'options' => $this->getModerationStates(),
				'default' => $this->moderationState,
			),
			'qsort' => array(
				'id' => 'mw-flow-special-search-sort',
				'name' => 'sort',
				'type' => 'select',
				'label-message' => 'flow-special-search-sort',
				'options' => $this->getSorts(),
				'default' => $this->sort,
			),
			'qtype' => array(
				'id' => 'mw-flow-special-search-type',
				'name' => 'type',
				'type' => 'select',
				'label-message' => 'flow-special-search-type',
				'options' => $this->getIndexTypes(),
				'default' => $this->type,
			),
		);
	}

	/**
	 * Description shown at the top of the page
	 * @return string
	 */
	protected function preText() {
		return '<p>' . $this->msg( 'flow-special-search-desc' )->escaped() . '</p>';
	}

	/**
	 * @param HTMLForm $form
	 * @throws \MWException
	 */
	protected function alterForm( HTMLForm $form ) {
		// Style the form.
		$form->setDisplayFormat( 'vform' );
		$form->setWrapperLegend( false );

		$form->setMethod( 'get' ); // This also submits the form every time the page loads.
	}

	/**
	 * Set redirect and return true if $data['uuid'] or $this->par exists and is
	 * a valid UUID; otherwise return false or a Status object encapsulating any
	 * error, which causes the form to be shown.
	 * @param array $data
	 * @return bool|Status
	 */
	public function onSubmit( array $data ) {
		if ( trim( $data['qterm'] ) === '' ) {
			// when no term is entered and referer is different than current
			// page, let's treat it as not-submitted and display no error
			// message for the missing term
			$referer = $this->getRequest()->getHeader( 'REFERER' );
			if ( $this->getFullTitle()->getFullURL() !== $referer ) {
				return false;
			}

			$this->getOutput()->setStatusCode( 404 );
			return Status::newFatal( 'flow-special-search-invalid-term' );
		}

		// relay search to API
		$request = new FauxRequest( $this->prepareApiData( $data ) + array(
			'action' => 'flow',
			'submodule' => 'search',
			// @todo: implement paging
//			'qoffset' => 0,
//			'qlimit' => 50,
		) );

		$api = new ApiMain( $request );
		$api->execute();

		$apiResponse = $api->getResult()->getData();
		$render = $this->renderResults( $apiResponse );
		$this->getOutput()->addHTML( $render );
		return Status::newGood( true );
	}

	/**
	 * @param array $apiResponse
	 * @return string
	 */
	protected function renderResults( $apiResponse ) {
		// @todo: this is still very much WIP
		// @todo: currently just using raw RevisionFormatter output
		// @todo: haven't even considered header search results

		$lightncandy = Container::get( 'lightncandy' );

		array_walk_recursive( $apiResponse, function( &$value ) {
			if ( $value instanceof Anchor ) {
				$value = $value->toArray();
			} elseif ( $value instanceof Message ) {
				$value = $value->text();
			}
		} );

		// Render with lightncandy. The exact template to render
		// will likely need to vary, but not yet.
		wfProfileIn( __CLASS__ . '-render' );
		$template = $lightncandy->getTemplate( 'flow_search' );
		$render = $template( $apiResponse );
		wfProfileOut( __CLASS__ . '-render' );

		return $render;
	}

	/**
	 * Convert data to be suitable for API consumption.
	 *
	 * @param array $data
	 * @return array
	 */
	protected function prepareApiData( array $data ) {
		/**
		 * API expects array values to be in the form of 'val1|val2' for a
		 * array( 'val1', 'val2' )
		 *
		 * @param mixed $value
		 * @return string
		 */
		$convertArrays = function ( $value ) {
			if ( is_array( $value ) ) {
				$value = $value ? implode( '|', $value ) : null;
			}

			return $value;
		};

		return array_map( $convertArrays, $data );
	}

	/**
	 * Return an array of namespaces in [namespace id => namespace text] format.
	 *
	 * @return array
	 * @throws \MWException
	 */
	protected function getNamespaces() {
		global $wgFlowOccupyPages, $wgFlowOccupyNamespaces;

		// get namespaces from $wgFlowOccupyNamespaces and individual $wgFlowOccupyPages
		$namespaces = array_combine( $wgFlowOccupyNamespaces, $wgFlowOccupyNamespaces );
		foreach ( $wgFlowOccupyPages as $page ) {
			$title = \Title::newFromText( $page );
			$namespaces[$title->getNamespace()] = $title->getNamespace();
		}
		ksort( $namespaces );

		// get descriptive namespace texts
		foreach ( $namespaces as $id => &$text ) {
			$text = $this->getLanguage()->getNsText( $id );
		}

		return array_flip($namespaces);
	}

	/**
	 * Return an array of possible moderation states.
	 *
	 * @return array
	 */
	protected function getModerationStates() {
		$states = array(
			AbstractRevision::MODERATED_NONE,
			AbstractRevision::MODERATED_HIDDEN,
			AbstractRevision::MODERATED_DELETED,
			AbstractRevision::MODERATED_SUPPRESSED,
		);

		/** @var RevisionActionPermissions $permissions */
		$permissions = Container::get( 'permissions' );

		// filter states user has no sufficient permissions for
		foreach ( $states as $i => $state ) {
			if ( $state === AbstractRevision::MODERATED_NONE ) {
				// unmoderated is always allowed
				continue;
			}

			// create fake revision in given permission to test if current
			// user is allowed to view them
			$revision = PostRevision::newFromId( UUID::create(), $this->getUser(), '' );
			$revision = $revision->moderate( $this->getUser(), $state, $state . '-topic', '' );
			if ( $revision && !$permissions->isAllowed( $revision, 'view' ) ) {
				unset( $states[$i] );
			}
		}

		// @todo: keys should be i18nized $states values
		return array_combine( $states, $states );
	}

	/**
	 * @return array
	 */
	protected function getSorts() {
		$searchEngine = new SearchEngine();
		$sorts = $searchEngine->getValidSorts();

		// @todo: keys should be i18nized $sorts values
		return array_combine( $sorts, $sorts );
	}

	/**
	 * @return array
	 */
	protected function getIndexTypes() {
		$types = Connection::getAllTypes();
		array_unshift( $types, false ); // false = all index types

		// @todo: keys should be i18nized $types values
		return array_combine( $types, $types );
	}
}
