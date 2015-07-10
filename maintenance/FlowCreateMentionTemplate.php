<?php

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
    ? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
    : dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Creates Template:FlowMention, which is used to render mentions in Flow's Visual Editor.
 * The template will be created with a default format, but can be customized.
 * If the template already exists, it will be left untouched.
 *
 * @ingroup Maintenance
 */
class FlowCreateMentionTemplate extends LoggedUpdateMaintenance {
    public function __construct() {
        parent::__construct();

        $this->mDescription = "Creates Template:FlowMention, which is used te render mentions in Flow's Visual Editor";
    }

    protected function getUpdateKey() {
        return __CLASS__;
    }

    protected function doDBUpdates() {
        // get "User:" namespace prefix in wiki language
        global $wgContLang;
        $namespaces = $wgContLang->getFormattedNamespaces();

        $title = Title::newFromText( wfMessage( 'flow-ve-mention-template-title' )->inContentLanguage()->plain(), NS_TEMPLATE );
        $article = new Article( $title );
        $page = $article->getPage();

        if ( $page->getRevision() !== null ) {
            // template already exists, don't overwrite it
            return true;
        }

        $status = $page->doEditContent(
            new WikitextContent( '@[[' . $namespaces[NS_USER] . ':{{{1|Example}}}|{{{2|{{{1|Example}}}}}}]]' ),
            '/* Automatically created by Flow */',
            EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
            false,
            FlowHooks::getOccupationController()->getTalkpageManager()
        );

        return $status->isOK();
    }
}

$maintClass = 'FlowCreateMentionTemplate';
require_once( RUN_MAINTENANCE_IF_MAIN );
