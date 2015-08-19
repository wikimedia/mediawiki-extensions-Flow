<?php

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * The templates will be created with a default content, but can be customized.
 * If the templates already exists, they will be left untouched.
 *
 * @ingroup Maintenance
 */
class FlowCreateTemplates extends LoggedUpdateMaintenance {
	/**
	 * Returns an array of templates to be created (= pages in NS_TEMPLATE)
	 *
	 * The key in the array is an i18n message so the template titles can be
	 * internationalized and/or edited per wiki.
	 * The value is a callback function that will only receive $title and is
	 * expected to return the page content in wikitext.
	 *
	 * @return array [title i18n key => content callback]
	 */
	protected function getTemplates() {
		return array(
			// Template:FlowMention, used to render mentions in Flow's Visual Editor
			'flow-ve-mention-template-title' => function( Title $title ) {
				// get "User:" namespace prefix in wiki language
				global $wgContLang;
				$namespaces = $wgContLang->getFormattedNamespaces();

				return '@[[' . $namespaces[NS_USER] . ':{{{1|Example}}}|{{{2|{{{1|Example}}}}}}]]';
			},
			// LiquidThread import templates
			'flow-importer-lqt-moved-thread-template' => function( Title $title ) {
				return 'This post by {{{author}}} was moved on {{{date}}}.  You can find it at [[{{{title}}}]].';
			},
			'flow-importer-lqt-converted-template' => function( Title $title ) {
				return '{{hatnote|Previous page history was archived for backup purposes at [[{{{archive}}}]] on {{#time: Y-m-d|{{{date}}} }}.}}<noinclude>[[Category:Flow]]</noinclude>';
			},
			'flow-importer-lqt-converted-archive-template' => function( Title $title ) {
				return
					"{{Ombox|image=[[File:Replacement filing cabinet.svg|50px|Archive|alt=|link=]]|text=This page is " .
					"an archived LiquidThreads page. '''Do not edit the contents of this page'''. Please direct any " .
					"additional comments to the [[{{{from}}}|current talk page]]." .
					"<!-- Template:Archive for converted LQT page -->\n" .
					"}}<noinclude>\n" .
					"==See also==\n" .
					"* [[Template:Archived]]\n\n" .
					"[[Category:Flow]]</noinclude>";
			},
			'flow-importer-lqt-suppressed-user-template' => function( Title $title ) {
				return 'This revision was imported from LiquidThreads with a suppressed user. It has been reassigned to the current user.';
			},
			'flow-importer-lqt-different-author-signature-template' => function( Title $title ) {
				return "''This post was posted by [[User:{{{authorUser}}}|{{{authorUser}}}]], but signed as [[User:{{{signatureUser}}}|{{{signatureUser}}}]].''";
			},
			// Wikitext import templates
			'flow-importer-wt-converted-template' => function( Title $title ) {
				return "Previous discussion was archived at [[{{{archive}}}]] on {{#time: Y-m-d|{{{date}}} }}.<noinclude>\n[[Category:Flow]]</noinclude>";
			},
			'flow-importer-wt-converted-archive-template' => function( Title $title ) {
				return
					"{{Ombox|image=[[File:Replacement filing cabinet.svg|50px|Archive|alt=|link=]]|text=This page is " .
					"an archive. '''Do not edit the contents of this page'''. Please direct any additional comments " .
					"to the [[{{{from|{{TALKSPACE}}:{{BASEPAGENAME}}}}}|current talk page]]." .
					"<!-- Template:Archived -->\n" .
					"}}<includeonly>[[Category:Archive]]</includeonly><noinclude>\n" .
					"This template includes pages in [[:Category:Archive]].\n" .
					"==See also==\n" .
					"* [[Template:Archive for converted LQT page]]\n\n" .
					"[[Category:Flow]]</noinclude>";
			},
		);
	}

	public function __construct() {
		parent::__construct();

		$this->mDescription = "Creates templates required by Flow";
	}

	protected function getUpdateKey() {
		$templates = $this->getTemplates();
		$keys = array_keys( $templates );
		sort( $keys );

		// make the updatekey unique for the i18n keys of the pages to be created
		// so we can easily skip this update if there are no changes
		return __CLASS__ . ':' . md5( implode( ',', $keys ) );
	}

	protected function doDBUpdates() {
		$status = Status::newGood();

		$templates = $this->getTemplates();
		foreach ( $templates as $key => $callback ) {
			$title = Title::newFromText( wfMessage( $key )->inContentLanguage()->plain(), NS_TEMPLATE );
			$content = new WikitextContent( $callback( $title ) );

			$status->merge( $this->create( $title, $content ) );
		}

		return $status->isOK();
	}

	/**
	 * Creates a page with the given content (unless it already exists)
	 *
	 * @param Title $title
	 * @param WikitextContent $content
	 * @return Status
	 * @throws MWException
	 */
	protected function create( Title $title, WikitextContent $content ) {
		$article = new Article( $title );
		$page = $article->getPage();

		if ( $page->getRevision() !== null ) {
			// template already exists, don't overwrite it
			return Status::newGood();
		}

		return $page->doEditContent(
			$content,
			'/* Automatically created by Flow */',
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			FlowHooks::getOccupationController()->getTalkpageManager()
		);
	}
}

$maintClass = 'FlowCreateTemplates';
require_once( RUN_MAINTENANCE_IF_MAIN );
