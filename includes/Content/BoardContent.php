<?php

namespace Flow\Content;

use DerivativeContext;
use Flow\Container;
use Flow\Model\UUID;
use Flow\View;
use MWException;
use OutputPage;
use ParserOutput;
use RequestContext;
use Title;

class BoardContent extends \AbstractContent {
	/** @var UUID */
	protected $workflowId;

	public function __construct( $contentModel = 'flow-board', $workflow = null ) {
		parent::__construct( 'flow-board' );

		// Allowed ways of loading a Workflow
		if ( ! (
			$workflow === null ||
			$workflow instanceof UUID ||
			$workflow instanceof Workflow
		) ) {
			throw new MWException( "Invalid argument for 'workflow' parameter." );
		}

		$this->workflow = $workflow;
	}

	/**
	 * @since 1.21
	 *
	 * @return string A string representing the content in a way useful for
	 *   building a full text search index. If no useful representation exists,
	 *   this method returns an empty string.
	 *
	 * @todo Test that this actually works
	 * @todo Make sure this also works with LuceneSearch / WikiSearch
	 */
	public function getTextForSearchIndex() {
		return '';
	}

	/**
	 * @since 1.21
	 *
	 * @return string|false The wikitext to include when another page includes this
	 * content, or false if the content is not includable in a wikitext page.
	 *
	 * @todo Allow native handling, bypassing wikitext representation, like
	 *  for includable special pages.
	 * @todo Allow transclusion into other content models than Wikitext!
	 * @todo Used in WikiPage and MessageCache to get message text. Not so
	 *  nice. What should we use instead?!
	 */
	public function getWikitextForTransclusion() {
		return '<span class="error">' . wfMessage( 'flow-embedding-unsupported' )->plain() . '</span>';
	}

	/**
	 * Returns a textual representation of the content suitable for use in edit
	 * summaries and log messages.
	 *
	 * @since 1.21
	 *
	 * @param int $maxLength Maximum length of the summary text.
	 *
	 * @return string The summary text.
	 */
	public function getTextForSummary( $maxLength = 250 ) {
		return '[Flow board ' . $this->workflowId->getAlphaDecimal() . ']';
	}

	/**
	 * Returns native representation of the data. Interpretation depends on
	 * the data model used, as given by getDataModel().
	 *
	 * @since 1.21
	 *
	 * @return mixed The native representation of the content. Could be a
	 *    string, a nested array structure, an object, a binary blob...
	 *    anything, really.
	 *
	 * @note Caller must be aware of content model!
	 */
	public function getNativeData() {
		return $this->workflow->getWorkflowId();
	}

	/**
	 * Returns the content's nominal size in bogo-bytes.
	 *
	 * @return int
	 */
	public function getSize() {
		return 1;
	}

	/**
	 * Return a copy of this Content object. The following must be true for the
	 * object returned:
	 *
	 * if $copy = $original->copy()
	 *
	 * - get_class($original) === get_class($copy)
	 * - $original->getModel() === $copy->getModel()
	 * - $original->equals( $copy )
	 *
	 * If and only if the Content object is immutable, the copy() method can and
	 * should return $this. That is, $copy === $original may be true, but only
	 * for immutable content objects.
	 *
	 * @since 1.21
	 *
	 * @return Content A copy of this object
	 */
	public function copy() {
		return $this;
	}

	/**
	 * Returns true if this content is countable as a "real" wiki page, provided
	 * that it's also in a countable location (e.g. a current revision in the
	 * main namespace).
	 *
	 * @since 1.21
	 *
	 * @param bool $hasLinks If it is known whether this content contains
	 *    links, provide this information here, to avoid redundant parsing to
	 *    find out.
	 *
	 * @return bool
	 */
	public function isCountable( $hasLinks = null ) {
		return true;
	}

	/**
	 * Parse the Content object and generate a ParserOutput from the result.
	 * $result->getText() can be used to obtain the generated HTML. If no HTML
	 * is needed, $generateHtml can be set to false; in that case,
	 * $result->getText() may return null.
	 *
	 * @note To control which options are used in the cache key for the
	 *       generated parser output, implementations of this method
	 *       may call ParserOutput::recordOption() on the output object.
	 *
	 * @param Title $title The page title to use as a context for rendering.
	 * @param int $revId Optional revision ID being rendered.
	 * @param ParserOptions $options Any parser options.
	 * @param bool $generateHtml Whether to generate HTML (default: true). If false,
	 *        the result of calling getText() on the ParserOutput object returned by
	 *        this method is undefined.
	 *
	 * @since 1.21
	 *
	 * @return ParserOutput
	 */
	public function getParserOutput( Title $title, $revId = null,
			ParserOptions $options = null, $generateHtml = true )
	{
		$parserOutput = new ParserOutput();
		$parserOutput->updateCacheExpiry( 0 );

		// Set up a derivative context (which inherits the current request)
		// to hold the output modules + text
		$childContext = new DerivativeContext( RequestContext::getMain() );
		$childContext->setOutput( new OutputPage( $childContext ) );

		// Create a View set up to output to our derivative context
		$view = new View(
			Container::get('templating'), // Should this also use the output page? I think it's okay
			Container::get('url_generator'),
			Container::get('lightncandy'),
			$childContext->getOutput()
		);

		// Load workflow and run View.
		$loader = Container::get('factory.loader.workflow')
			->createWorkflowLoader( $title, $this->getWorkflowId() );
		$view->show( $loader, 'view' );

		// Extract data from derivative context
		$parserOutput->setText( $childContext->getOutput()->getHTML() );
		$parserOutput->addModules( $childContext->getOutput()->getModules() );
		$parserOutput->addModuleStyles( $childContext->getOutput()->getModuleStyles() );
		$parserOutput->addModuleScripts( $childContext->getOutput()->getModuleScripts() );

		// Apply References
		// @todo
		// $linksTableUpdater = Container::get( 'reference.updater.links-tables' );
		// $

		return $parserOutput;
	}

	public function getWorkflowId() {
		if ( $this->workflow instanceof UUID ) {
			return $this->workflow;
		} elseif ( $this->workflow instanceof Workflow ) {
			return $this->workflow->getWorkflowId();
		} elseif ( $this->workflow === null ) {
			return null;
		} else {
			throw new MWException( "Unknown Workflow specifier" );
		}
	}
}