<?php

// Internationalisation file for Flow extension.

$messages = array();

/**
 * English
 * @author Erik Bernhardson
 * @author Matthias Mullie
 * @author Benny Situ
 * @author Andrew Garrett
 * @author Yuki Shira
 * @author Amir E. Aharoni
 */
$messages['en'] = array(
	'flow-desc' => 'Workflow management system',

	'flow-talk-taken-over' => 'This talk page has been taken over by a [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow board].',

	'log-name-flow' => 'Flow activity log',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|deleted}} a [$4 post] on [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|restored}} a [$4 post] on [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|suppressed}} a [$4 post] on [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|deleted}} a [$4 post] on [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|deleted}} a [$4 topic] on [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|restored}} a [$4 topic] on [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|suppressed}} a [$4 topic] on [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|deleted}} a [$4 topic] on [[$3]]',

	'flow-user-moderated' => 'Moderated user',

	'flow-edit-header-link' => 'Edit header',
	'flow-header-empty' => 'This talk page currently has no header.',
	'flow-post-moderated-toggle-hide-show' => 'Show comment {{GENDER:$1|hidden}} by $2',
	'flow-post-moderated-toggle-delete-show' => 'Show comment {{GENDER:$1|deleted}} by $2',
	'flow-post-moderated-toggle-suppress-show' => 'Show comment {{GENDER:$1|suppressed}} by $2',
	'flow-post-moderated-toggle-hide-hide' => 'Hide comment {{GENDER:$1|hidden}} by $2',
	'flow-post-moderated-toggle-delete-hide' => 'Hide comment {{GENDER:$1|deleted}} by $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Hide comment {{GENDER:$1|suppressed}} by $2',
	'flow-hide-post-content' => 'This comment was {{GENDER:$1|hidden}} by $2',
	'flow-hide-title-content' => 'This topic was {{GENDER:$1|hidden}} by $2',
	'flow-hide-header-content' => '{{GENDER:$1|Hidden}} by $2',
	'flow-hide-usertext' => '$1',
	'flow-delete-post-content' => 'This comment was {{GENDER:$1|deleted}} by $2',
	'flow-delete-title-content' => 'This topic was {{GENDER:$1|deleted}} by $2',
	'flow-delete-header-content' => '{{GENDER:$1|Deleted}} by $2',
	'flow-delete-usertext' => '$1',
	'flow-suppress-post-content' => 'This comment was {{GENDER:$1|suppressed}} by $2',
	'flow-suppress-title-content' => 'This topic was {{GENDER:$1|suppressed}} by $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Suppressed}} by $2',
	'flow-suppress-usertext' => "<em>Username suppressed</em>",
	'flow-post-actions' => 'Actions',
	'flow-topic-actions' => 'Actions',
	'flow-cancel' => 'Cancel',
	'flow-preview' => 'Preview',
	'flow-show-change' => 'Show changes',
	'flow-last-modified-by' => 'Last {{GENDER:$1|modified}} by $1',

	'flow-system-usertext' => '{{SITENAME}}',
	'flow-stub-post-content' => "''Due to a technical error, this post could not be retrieved.''",

	'flow-newtopic-title-placeholder' => 'New topic',
	'flow-newtopic-content-placeholder' => "Add some details if you'd like",
	'flow-newtopic-header' => 'Add a new topic',
	'flow-newtopic-save' => 'Add topic',
	'flow-newtopic-start-placeholder' => 'Start a new topic',

	'flow-reply-topic-placeholder' => '{{GENDER:$1|Comment}} on "$2"',

	'flow-reply-placeholder' => '{{GENDER:$1|Reply}} to $1',
	'flow-reply-submit' => '{{GENDER:$1|Reply}}',
	'flow-reply-link' => '{{GENDER:$1|Reply}}',
	'flow-thank-link' => '{{GENDER:$1|Thank}}',
	'flow-post-interaction-separator' => '&#32;•&#32;', # only translate this message to other languages if you have to change it

	'flow-post-edited' => 'Post {{GENDER:$1|edited}} by $1 $2',
	'flow-post-action-view' => 'Permalink',
	'flow-post-action-post-history' => 'History',
	'flow-post-action-suppress-post' => 'Suppress',
	'flow-post-action-delete-post' => 'Delete',
	'flow-post-action-hide-post' => 'Hide',
	'flow-post-action-edit-post' => 'Edit',
	'flow-post-action-restore-post' => 'Restore post',

	'flow-topic-action-view' => 'Permalink',
	'flow-topic-action-watchlist' => 'Watchlist',
	'flow-topic-action-edit-title' => 'Edit title',
	'flow-topic-action-history' => 'History',
	'flow-topic-action-hide-topic' => 'Hide topic',
	'flow-topic-action-delete-topic' => 'Delete topic',
	'flow-topic-action-suppress-topic' => 'Suppress topic',
	'flow-topic-action-restore-topic' => 'Restore topic',

	'flow-error-http' => 'An error occurred while contacting the server.', // Needs real copy
	'flow-error-other' => 'An unexpected error occurred.',
	'flow-error-external' => 'An error occurred.<br />The error message received was: $1',
	'flow-error-edit-restricted' => 'You are not allowed to edit this post.',
	'flow-error-external-multi' => 'Errors were encountered.<br />$1',

	'flow-error-missing-content' => 'Post has no content. Content is required to save a post.',
	'flow-error-missing-title' => 'Topic has no title. Title is required to save a topic.',
	'flow-error-parsoid-failure' => 'Unable to parse content due to a Parsoid failure.',
	'flow-error-missing-replyto' => 'No "replyTo" parameter was supplied. This parameter is required for the "reply" action.',
	'flow-error-invalid-replyto' => '"replyTo" parameter was invalid. The specified post could not be found.',
	'flow-error-delete-failure' => 'Deletion of this item failed.',
	'flow-error-hide-failure' => 'Hiding this item failed.',
	'flow-error-missing-postId' => 'No "postId" parameter was supplied. This parameter is required to manipulate a post.',
	'flow-error-invalid-postId' => '"postId" parameter was invalid. The specified post ($1) could not be found.',
	'flow-error-restore-failure' => 'Restoration of this item failed.',
	'flow-error-invalid-moderation-state' => 'An invalid value was provided for moderationState.',
	'flow-error-invalid-moderation-reason' => 'Please provide a reason for the moderation.',
	'flow-error-not-allowed' => 'Insufficient permissions to execute this action.',
	'flow-error-title-too-long' => 'Topic titles are restricted to $1 {{PLURAL:$1|byte|bytes}}.',
	'flow-error-no-existing-workflow' => 'This workflow does not yet exist.',
	'flow-error-not-a-post' => 'Topic title cannot be saved as a post.',
	'flow-error-missing-header-content' => 'Header has no content. Content is required to save a header.',
	'flow-error-missing-prev-revision-identifier' => 'Previous revision identifier is missing.',
	'flow-error-prev-revision-mismatch' => 'Another user just edited this post a few seconds ago. Are you sure you want to overwrite the recent change?',
	'flow-error-prev-revision-does-not-exist' => 'Could not find the previous revision.',
	'flow-error-default' => 'An error has occurred.',
	'flow-error-invalid-input' => 'Invalid value was provided for loading flow content.',
	'flow-error-invalid-title' => 'Invalid page title was provided.',
	'flow-error-invalid-action' => '{{int:nosuchactiontext}}',
	'flow-error-fail-load-history' => 'Failed to load history content.',
	'flow-error-missing-revision' => 'Could not find a revision to load flow content.',
	'flow-error-fail-commit' => 'Failed to save the flow content.',
	'flow-error-insufficient-permission' => 'Insufficient permission to access the content.',
	'flow-error-revision-comparison' => 'Diff operation can only be done for two revisions belonging to the same post.',
	'flow-error-missing-topic-title' => 'Could not find the topic title for current workflow.',
	'flow-error-fail-load-data' => 'Failed to load the requested data.',
	'flow-error-invalid-workflow' => 'Could not find the requested workflow.',
	'flow-error-process-data' => 'An error has occurred while processing the data in your request.',
	'flow-error-process-wikitext' => 'An error has occurred while processing HTML/wikitext conversion.',
	'flow-error-no-index' => 'Failed to find an index to perform data search.',

	'flow-edit-header-submit' => 'Save header',
	'flow-edit-header-submit-overwrite' => 'Overwrite header',
	'flow-edit-title-submit' => 'Change title',
	'flow-edit-title-submit-overwrite' => 'Overwrite title',
	'flow-edit-post-submit' => 'Submit changes',
	'flow-edit-post-submit-overwrite' => 'Overwrite changes',

	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|edited}} a [$3 comment] on $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|commented}}] on $4 (<em>$5</em>).',
	'flow-rev-message-reply-bundle' => "<strong>$1 {{PLURAL:$1|comment|comments}}</strong> {{PLURAL:$1|was|were}} added.",
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|created}} the topic [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|changed}} the topic title from $5 to [$3 $4].',

	'flow-rev-message-create-header' => "$1 {{GENDER:$2|created}} the header.",
	'flow-rev-message-edit-header' => "$1 {{GENDER:$2|edited}} the header.",

	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|hid}} a [$4 comment] on $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|deleted}} a [$4 comment] on $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|suppressed}} a [$4 comment] on $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|restored}} a [$4 comment] on $6 (<em>$5</em>).',

	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|hid}} the [$4 topic] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|deleted}} the [$4 topic] $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|suppressed}} the [$4 topic] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|restored}} the [$4 topic] $6 (<em>$5</em>).',

	'flow-board-history' => '"$1" history',
	'flow-topic-history' => '"$1" topic history',
	'flow-post-history' => '"Comment by {{GENDER:$2|$2}}" post history',
	'flow-history-last4' => 'Last 4 hours',
	'flow-history-day' => 'Today',
	'flow-history-week' => 'Last week',
	'flow-history-pages-topic' => 'Appears on [$1 "$2" board]',
	'flow-history-pages-post' => 'Appears on [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 started this topic|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} and $2 {{PLURAL:$2|other|others}}|0=No participation yet|2={{GENDER:$3|$3}} and {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} and {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 comment|$1 comments|0={{GENDER:$2|Be the first}} to comment!}}',

	'flow-comment-restored' => 'Restored comment',
	'flow-comment-deleted' => 'Deleted comment',
	'flow-comment-hidden' => 'Hidden comment',
	'flow-comment-moderated' => 'Moderated comment',

	'flow-paging-rev' => 'More recent topics',
	'flow-paging-fwd' => 'Older topics',
	'flow-last-modified' => 'Last modified about $1',

	// Notification message
	'flow-notification-reply' => '$1 {{GENDER:$1|replied}} to your <span class="plainlinks">[$5 post]</span> in "$2" on "$4".',
	'flow-notification-reply-bundle' => '$1 and $5 {{PLURAL:$6|other|others}} {{GENDER:$1|replied}} to your <span class="plainlinks">[$4 post]</span> in "$2" on "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|edited}} a <span class="plainlinks">[$5 post]</span> in "$2" on [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 and $5 {{PLURAL:$6|other|others}} {{GENDER:$1|edited}} a <span class="plainlinks">[$4 post]</span> in "$2" on "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|created}} a <span class="plainlinks">[$5 new topic]</span> on [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|changed}} the title of <span class="plainlinks">[$2 $3]</span> to "$4" on [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|mentioned}} you in {{GENDER:$1|his|her|their}} <span class="plainlinks">[$2 post]</span> in "$3" on "$4".',

	// Notification primary links and secondary links
	'flow-notification-link-text-view-post' => 'View post',
	'flow-notification-link-text-view-board' => 'View board',
	'flow-notification-link-text-view-topic' => 'View topic',

	// Notification Email messages
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|replied}} to your post',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|replied}} to your post in "$2" on "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 and $4 {{PLURAL:$5|other|others}} {{GENDER:$1|replied}} to your post in "$2" on "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|mentioned}} you on "$2"',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|mentioned}} you in {{GENDER:$1|his|her|their}} post in "$2" on "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|edited}} a post',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|edited}} a post in "$2" on "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 and $4 {{PLURAL:$5|other|others}} {{GENDER:$1|edited}} a post in "$2" on "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|renamed}} your topic',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|renamed}} your topic "$2" to "$3" on "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|created}} a new topic on "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|created}} a new topic with the title "$2" on $3',

	// Notification preference
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Notify me when actions related to me occur in Flow.',

	// Link text used throughout flow for action menus and the like
	'flow-link-post' => 'post',
	'flow-link-topic' => 'topic',
	'flow-link-history' => 'history',

	// Moderation dialog
	'flow-moderation-reason-placeholder' => 'Enter your reason here',
	'flow-moderation-title-suppress-post' => 'Suppress post?',
	'flow-moderation-title-delete-post' => 'Delete post?',
	'flow-moderation-title-hide-post' => 'Hide post?',
	'flow-moderation-title-restore-post' => 'Restore post?',
	'flow-moderation-intro-suppress-post' => "Please {{GENDER:$3|explain}} why you're suppressing this post.",
	'flow-moderation-intro-delete-post' => "Please {{GENDER:$3|explain}} why you're deleting this post.",
	'flow-moderation-intro-hide-post' => "Please {{GENDER:$3|explain}} why you're hiding this post.",
	'flow-moderation-intro-restore-post' => "Please {{GENDER:$3|explain}} why you're restoring this post.",
	'flow-moderation-confirm-suppress-post' => 'Suppress',
	'flow-moderation-confirm-delete-post' => 'Delete',
	'flow-moderation-confirm-hide-post' => 'Hide',
	'flow-moderation-confirm-restore-post' => 'Restore',
	'flow-moderation-confirmation-suppress-post' => 'The post was successfully suppressed.
{{GENDER:$2|Consider}} giving $1 feedback on this post.',
	'flow-moderation-confirmation-delete-post' => 'The post was successfully deleted.
{{GENDER:$2|Consider}} giving $1 feedback on this post.',
	'flow-moderation-confirmation-hide-post' => 'The post was successfully hidden.
{{GENDER:$2|Consider}} giving $1 feedback on this post.',
	'flow-moderation-confirmation-restore-post' => 'You have successfully restored the above post.',
	'flow-moderation-title-suppress-topic' => 'Suppress topic?',
	'flow-moderation-title-delete-topic' => 'Delete topic?',
	'flow-moderation-title-hide-topic' => 'Hide topic?',
	'flow-moderation-title-restore-topic' => 'Restore topic?',
	'flow-moderation-intro-suppress-topic' => "Please {{GENDER:$3|explain}} why you're suppressing this topic.",
	'flow-moderation-intro-delete-topic' => "Please {{GENDER:$3|explain}} why you're deleting this topic.",
	'flow-moderation-intro-hide-topic' => "Please {{GENDER:$3|explain}} why you're hiding this topic.",
	'flow-moderation-intro-restore-topic' => "Please {{GENDER:$3|explain}} why you're restoring this topic.",
	'flow-moderation-confirm-suppress-topic' => 'Suppress',
	'flow-moderation-confirm-delete-topic' => 'Delete',
	'flow-moderation-confirm-hide-topic' => 'Hide',
	'flow-moderation-confirm-restore-topic' => 'Restore',
	'flow-moderation-confirmation-suppress-topic' => 'The topic was successfully suppressed.
{{GENDER:$2|Consider}} giving $1 feedback on this topic.',
	'flow-moderation-confirmation-delete-topic' => 'The topic was successfully deleted.
{{GENDER:$2|Consider}} giving $1 feedback on this topic.',
	'flow-moderation-confirmation-hide-topic' => 'The topic was successfully hidden.
{{GENDER:$2|Consider}} giving $1 feedback on this topic.',
	'flow-moderation-confirmation-restore-topic' => 'You have successfully restored this topic.',

	// Permalink related stuff
	'flow-topic-permalink-warning' => 'This topic was started on [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'This topic was started on [$2 {{GENDER:$1|$1}}\'s board]',
	'flow-revision-permalink-warning-post' => 'This is a permanent link to a single version of this post.
This version is from $1.
You can see the [$5 differences from the previous version], or view other versions on the [$4 post history page].',
	'flow-revision-permalink-warning-post-first' => 'This is a permanent link to the first version of this post.
You can view later versions on the [$4 post history page].',
	'flow-revision-permalink-warning-header' => 'This is a permanent link to a single version of the header.
This version is from $1.  You can see the [$3 differences from the previous version], or view other versions on the [$2 board history page].',
	'flow-revision-permalink-warning-header-first' => 'This is a permanent link to the first version of the header.
You can view later versions on the [$2 board history page].',
	'flow-compare-revisions-revision-header' => 'Version by {{GENDER:$2|$2}} from $1',
	'flow-compare-revisions-header-post' => 'This page shows the {{GENDER:$3|changes}} between two versions of a post by $3 in the topic "[$5 $2]" on [$4 $1].
You can see other versions of this post at its [$6 history page].',
	'flow-compare-revisions-header-header' => 'This page shows the {{GENDER:$2|changes}} between two versions of the header on [$3 $1].
You can see other versions of the header at its [$4 history page].',

	// Topic collapse states
	'flow-topic-collapsed-one-line' => 'Small view',
	'flow-topic-collapsed-full' => 'Collapsed view',
	'flow-topic-complete' => 'Full view',

	// Terms of use
	'flow-terms-of-use-new-topic' => 'By clicking "{{int:flow-newtopic-save}}", you agree to the terms of use for this wiki.',
	'flow-terms-of-use-reply' => 'By clicking "{{int:flow-reply-submit}}", you agree to the terms of use for this wiki.',
	'flow-terms-of-use-edit' => 'By saving your changes, you agree to the terms of use for this wiki.',
);

/** Message documentation (Message documentation)
 * @author Amire80
 * @author Beta16
 * @author Lokal Profil
 * @author Raymond
 * @author Shirayuki
 * @author Siebrand
 * @author Withoutaname
 */
$messages['qqq'] = array(
	'flow-desc' => '{{desc|name=Flow|url=http://www.mediawiki.org/wiki/Extension:Flow}}',
	'flow-talk-taken-over' => 'Content to replace existing page content by for pages that are turned into Flow boards.',
	'log-name-flow' => '{{doc-logpage}}
Name of the Flow log filter on the [[Special:Log]] page.',
	'logentry-delete-flow-delete-post' => 'Text for a deletion log entry when a post was deleted. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the post was moderated
* $4 - permalink URL to the moderated post
{{Related|Flow-logentry}}',
	'logentry-delete-flow-restore-post' => 'Text for a deletion log entry when a deleted post was restored. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the post was moderated
* $4 - permalink URL to the moderated post
{{Related|Flow-logentry}}',
	'logentry-suppress-flow-suppress-post' => 'Text for a deletion log entry when a post was suppressed. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the post was moderated
* $4 - permalink URL to the moderated post
{{Related|Flow-logentry}}',
	'logentry-suppress-flow-restore-post' => 'Text for a deletion log entry when a suppressed post was restored. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the post was moderated
* $4 - permalink URL to the moderated post
{{Related|Flow-logentry}}',
	'logentry-delete-flow-delete-topic' => 'Text for a deletion log entry when a topic was deleted. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the topic was moderated
* $4 - permalink URL to the moderated topic
{{Related|Flow-logentry}}',
	'logentry-delete-flow-restore-topic' => 'Text for a deletion log entry when a deleted topic was restored. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the topic was moderated
* $4 - permalink URL to the moderated topic
{{Related|Flow-logentry}}',
	'logentry-suppress-flow-suppress-topic' => 'Text for a deletion log entry when a topic was suppressed. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the topic was moderated
* $4 - permalink URL to the moderated topic
{{Related|Flow-logentry}}',
	'logentry-suppress-flow-restore-topic' => 'Text for a deletion log entry when a suppressed topic was restored. Parameters:
* $1 - the user: link to the user page
* $2 - the username. Can be used for GENDER.
* $3 - the page where the topic was moderated
* $4 - permalink URL to the moderated topic
{{Related|Flow-logentry}}',
	'flow-user-moderated' => 'Name to display when the current user is not allowed to see the users name due to moderation',
	'flow-edit-header-link' => 'Used as text for the button that either allows editing the header in place or brings the user to a page for editing the header.',
	'flow-header-empty' => 'Used as a placeholder text for headers which have no content.',
	'flow-post-moderated-toggle-hide-show' => 'Message to display instead of content when a hidden post has been hidden.

Parameters:
* $1 - username that hid the title, can be used for GENDER
* $2 - user link and tool links for the user
{{Related|Flow-post-moderated-toggle}}',
	'flow-post-moderated-toggle-delete-show' => 'Message to display instead of content when a deleted post has been hidden.

Parameters:
* $1 - username that hid the title, can be used for GENDER
* $2 - user link and tool links for the user
{{Related|Flow-post-moderated-toggle}}',
	'flow-post-moderated-toggle-suppress-show' => 'Message to display instead of content when a suppressed post has been hidden.

Parameters:
* $1 - username that hid the title, can be used for GENDER
* $2 - user link and tool links for the user
{{Related|Flow-post-moderated-toggle}}',
	'flow-post-moderated-toggle-hide-hide' => 'Message to display instead of content when a hidden post has been hidden.

Parameters:
* $1 - username that hid the title, can be used for GENDER
* $2 - user link and tool links for the user
{{Related|Flow-post-moderated-toggle}}',
	'flow-post-moderated-toggle-delete-hide' => 'Message to display instead of content when a deleted post has been hidden.

Parameters:
* $1 - username that hid the title, can be used for GENDER
* $2 - user link and tool links for the user
{{Related|Flow-post-moderated-toggle}}',
	'flow-post-moderated-toggle-suppress-hide' => 'Message to display instead of content when a suppressed post has been hidden.

Parameters:
* $1 - username that hid the title, can be used for GENDER
* $2 - user link and tool links for the user
{{Related|Flow-post-moderated-toggle}}',
	'flow-hide-post-content' => 'Message to display instead of content when the post has been hidden.

Parameters:
* $1 - username that hid the post, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-hide-title-content' => 'Message to display instead of content when the title has been hidden.

Parameters:
* $1 - username that hid the title, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-hide-header-content' => 'Message to display instead of content when the header has been hidden.

Parameters:
* $1 - username that hid the header, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-hide-usertext' => 'Used as username if the post was hidden.

Parameters:
* $1 - Username of the post creator. Can be used for GENDER',
	'flow-delete-post-content' => 'Message to display instead of content when the post has been deleted.

Parameters:
* $1 - username that deleted the post, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-delete-title-content' => 'Message to display instead of content when the title has been deleted.

Parameters:
* $1 - username that deleted the title, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-delete-header-content' => 'Message to display instead of content when the header has been deleted.

Parameters:
* $1 - username that deleted the header, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-delete-usertext' => 'Used as username if the post was deleted.

Parameters:
* $1 - Username of the post creator. Can be used for GENDER',
	'flow-suppress-post-content' => 'Message to display instead of content when the post has been suppressed.

Parameters:
* $1 - username that suppressed the post, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-suppress-title-content' => 'Message to display instead of content when the title has been suppressed.

Parameters:
* $1 - username that suppressed the title, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-suppress-header-content' => 'Message to display instead of content when the header has been suppressed.

Parameters:
* $1 - username that suppressed the header, can be used for GENDER
* $2 - user link and tool links for the user.
{{Related|Flow-content}}',
	'flow-suppress-usertext' => 'Used as username if the post was suppressed.

Parameters:
* $1 - Username of the post creator. Can be used for GENDER',
	'flow-post-actions' => 'Used as link text.
{{Identical|Action}}',
	'flow-topic-actions' => 'Used as link text.
{{Identical|Action}}',
	'flow-cancel' => 'Used as action link text.
{{Identical|Cancel}}',
	'flow-preview' => 'Used as action link text.
{{Identical|Preview}}',
	'flow-show-change' => 'Used as action link text.

Changes refers to diff between revisions.
{{Identical|Show change}}',
	'flow-last-modified-by' => 'Used as text to show who made the last content modification. Parameters:
* $1 - username of the user who last made the content modification, can be used for GENDER support',
	'flow-system-usertext' => "Stub username to be displayed when a post's information could not be loaded due to technical issues.",
	'flow-stub-post-content' => 'Stub post content to be displayed when the real post could not be loaded due to technical issues.',
	'flow-newtopic-title-placeholder' => 'Used as placeholder for the "Subject/Title for topic" textarea.
{{Identical|New topic}}',
	'flow-newtopic-content-placeholder' => 'Used as placeholder for the "Content" textarea.',
	'flow-newtopic-header' => 'Unused at this time.',
	'flow-newtopic-save' => 'Used as label for the Submit button.

Also used in:
* {{msg-mw|Flow-terms-of-use-new-topic}}
* {{msg-mw|Wikimedia-flow-terms-of-use-new-topic}}',
	'flow-newtopic-start-placeholder' => 'Used as placeholder for the "Topic" textarea.',
	'flow-reply-topic-placeholder' => 'Used as placeholder for the "reply to this topic" textarea. Parameters:
* $1 - username of the logged in user, can be used for GENDER
* $2 - topic title',
	'flow-reply-placeholder' => 'Used as placeholder for the Content textarea. Parameters:
* $1 - username',
	'flow-reply-submit' => 'Used as label for the Submit button. Parameters:
* $1 - username, can be used for GENDER
Also used in:
* {{msg-mw|Flow-terms-of-use-reply}}
* {{msg-mw|Wikimedia-flow-terms-of-use-reply}}
{{Identical|Reply}}',
	'flow-reply-link' => 'Text for the link that appears near the post and offers the user to reply to it. Clicking the link will display the reply editor. Parameters:
* $1 - username, can be used for GENDER
{{Identical|Reply}}',
	'flow-thank-link' => 'Link text of the button that will (when clicked) thank the editor of the comment Parameters:
* $1 - username, can be used for GENDER',
	'flow-post-interaction-separator' => '{{optional}}',
	'flow-post-edited' => 'Text displayed to notify the user a post has been modified. Parameters:
* $1 - username that created the most recent revision of the post
* $2 - humanized timestamp, relative to now, of when the edit occurred; rendered by MWTimestamp::getHumanTimestamp',
	'flow-post-action-view' => 'Used as text for the link which is used to view.
{{Identical|Permalink}}',
	'flow-post-action-post-history' => 'Used as text for the link which is used to view post-history of the topic.
{{Identical|History}}',
	'flow-post-action-suppress-post' => 'Used as a label for  the submit button in the suppression form.
{{Related|Flow-action}}
{{Identical|Suppress}}',
	'flow-post-action-delete-post' => 'Used as a label for the submit button in the deletion form.
{{Related|Flow-action}}
{{Identical|Delete}}',
	'flow-post-action-hide-post' => 'Used as label for the Submit button.
{{Related|Flow-action}}
{{Identical|Hide}}',
	'flow-post-action-edit-post' => 'Used as text for the link which is used to edit the post.
{{Related|Flow-action}}
{{Identical|Edit}}',
	'flow-post-action-restore-post' => 'Used as label for the Submit button.
{{Related|Flow-action}}
{{Identical|Restore post}}',
	'flow-topic-action-view' => "Title text for topic's permalink icon.
{{Identical|Permalink}}",
	'flow-topic-action-watchlist' => "Title text for topic's watchlist icon.
{{Identical|Watchlist}}",
	'flow-topic-action-edit-title' => 'Used as title for the link which is used to edit the title.',
	'flow-topic-action-history' => 'Used as text for the link which is used to view topic-history.
{{Identical|History}}',
	'flow-topic-action-hide-topic' => 'Used as a link in a dropdown menu to hide a topic.
{{Related|Flow-action}}',
	'flow-topic-action-delete-topic' => 'Used as a link in a dropdown menu to delete a topic.
{{Related|Flow-action}}',
	'flow-topic-action-suppress-topic' => 'Used as a link in a dropdown menu to suppress a topic.
{{Related|Flow-action}}',
	'flow-topic-action-restore-topic' => 'Used as a link in a dropdown menu to clear existing moderation.
{{Related|Flow-action}}
{{Identical|Restore topic}}',
	'flow-error-http' => 'Used as error message on HTTP error.',
	'flow-error-other' => 'Used as generic error message.',
	'flow-error-external' => 'Uses as error message. Parameters:
* $1 - error message
See also:
* {{msg-mw|Flow-error-external-multi}}',
	'flow-error-edit-restricted' => 'Used as error message when a user attempts to edit a post they do not have the permissions for.',
	'flow-error-external-multi' => 'Used as error message. Parameters:
* $1 - list of error messages
See also:
* {{msg-mw|Flow-error-external}}',
	'flow-error-missing-content' => 'Used as error message.
{{Related|Flow-error-missing}}',
	'flow-error-missing-title' => 'Used as error message.
{{Related|Flow-error-missing}}',
	'flow-error-parsoid-failure' => 'Used as error message.

Parsoid is a bidirectional wikitext parser and runtime. Converts back and forth between wikitext and HTML/XML DOM with RDFa. See [[mw:Parsoid]].',
	'flow-error-missing-replyto' => 'Used as error message.

The variable name "replyTo" is invisible to users, so "replyTo" can be translated.',
	'flow-error-invalid-replyto' => 'Used as error message.

The variable name "replyTo" is invisible to users, so "replyTo" can be translated.',
	'flow-error-delete-failure' => 'Used as error message.

"this item" refers either "this topic" or "this post".',
	'flow-error-hide-failure' => 'Used as error message.

"this item" refers either "this topic" or "this post".',
	'flow-error-missing-postId' => 'Used as error message when deleting/restoring a post.

"manipulate" refers either "delete" or "restore".',
	'flow-error-invalid-postId' => 'Used as error message when deleting/restoring a post.

The variable name "postId" is invisible to users, so "postId" can be translated.

Parameters:
* $1 - contains the postId that was specified',
	'flow-error-restore-failure' => 'Used as error message when restoring a post.

"this item" seems to refer "this post".',
	'flow-error-invalid-moderation-state' => 'Used as error message.

Usually indicates a code bug, so technical terminology is okay.

Valid values for moderationState are: (none), hidden, deleted, suppressed',
	'flow-error-invalid-moderation-reason' => 'Used as error message when no reason is given for the moderation of a post.',
	'flow-error-not-allowed' => 'Error message when the user has insufficient permissions to execute this action',
	'flow-error-title-too-long' => 'Used as error message when a user submits a topic title that is too long to save.

Parameters:
* $1 - The number of bytes allowed',
	'flow-error-no-existing-workflow' => 'Error message when an edit to a non-existing topic is performed.',
	'flow-error-not-a-post' => "Error message when a topic title is attempted to be saved as post (most likely a code issue - shouldn't happen).",
	'flow-error-missing-header-content' => 'Error message when the header is submitted without content.
{{Related|Flow-error-missing}}',
	'flow-error-missing-prev-revision-identifier' => 'Error message when the identifier for the previous header revision is missing.',
	'flow-error-prev-revision-mismatch' => 'Error message when the provided previous revision identifier does not match the last stored revision.',
	'flow-error-prev-revision-does-not-exist' => 'Error message when the provided previous revision identifier could not be found.',
	'flow-error-default' => 'General error message for flow.',
	'flow-error-invalid-input' => 'Error message when invalid input is provided.',
	'flow-error-invalid-title' => 'Error message when invalid title is provided.',
	'flow-error-invalid-action' => 'Error message when invalid action is provided.',
	'flow-error-fail-load-history' => 'Error message when load history content is failed to load.',
	'flow-error-missing-revision' => 'Error message when a revision is missing.',
	'flow-error-fail-commit' => 'Error message when a commit action fails.',
	'flow-error-insufficient-permission' => 'Error message when user does not have sufficient permission to perform an action.',
	'flow-error-revision-comparison' => 'Error message when revision comparison fails.',
	'flow-error-missing-topic-title' => 'Error message when a topic title is missing.',
	'flow-error-fail-load-data' => 'General error message when failing to load data.',
	'flow-error-invalid-workflow' => 'Error message when invalid workflow is provided.',
	'flow-error-process-data' => 'General error message when failing to process data.',
	'flow-error-process-wikitext' => 'Error message when failing to process html/wikitext conversion.',
	'flow-error-no-index' => 'Error message when failing to find an index to perform data search.',
	'flow-edit-header-submit' => 'Used as label for the Submit button.',
	'flow-edit-header-submit-overwrite' => 'Used as label for the Submit button, when submitting will overwrite a more recent change.',
	'flow-edit-title-submit' => 'Used as label for the Submit button.',
	'flow-edit-title-submit-overwrite' => 'Used as label for the Submit button, when submitting will overwrite a more recent change.',
	'flow-edit-post-submit' => 'Used as label for the Submit button.',
	'flow-edit-post-submit-overwrite' => 'Used as label for the Submit button, when submitting will overwrite a more recent change.',
	'flow-rev-message-edit-post' => 'Used as a revision comment when a post has been edited.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who edited the post. Can be used for GENDER
* $3 - the URL of the post
* $4 -  the name of the topic that the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-reply' => 'Used as a revision comment when a new reply has been posted.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who replied. Can be used for GENDER
* $3 - the URL of the post
* $4 - the name of the topic that was commented on
* $5 - truncated summary of the reply content
{{Related|Flow-rev-message}}',
	'flow-rev-message-reply-bundle' => "When multiple replies have been posted, they're bundled. This is the message to describe that multiple replies were posted.

Parameters:
* $1 - the amount of replies posted
{{Related|Flow-rev-message}}",
	'flow-rev-message-new-post' => 'Used as revision comment when the topic has been created.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username. Can be used for GENDER
* $3 - the URL of the topic
* $4 - the topic title
{{Related|Flow-rev-message}}',
	'flow-rev-message-edit-title' => 'Used as revision comment when a post has been edited.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who edited the title. Can be used for GENDER
* $3 - the URL of the topic
* $4 - the topic title
* $5 - the previous topic title
{{Related|Flow-rev-message}}',
	'flow-rev-message-create-header' => 'Used as revision comment when the header has been created.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who created the header. Can be used for GENDER
{{Related|Flow-rev-message}}',
	'flow-rev-message-edit-header' => 'Used as revision comment when the header has been edited.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who edited the header. Can be used for GENDER
{{Related|Flow-rev-message}}',
	'flow-rev-message-hid-post' => 'Used as revision comment when a post has been hidden.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the comment. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the comment. Can be used for GENDER
* $4 - permalink to the comment
* $5 - Reason, from the moderating user, for moderating this post
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-deleted-post' => 'Used as revision comment when a post has been deleted.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the comment. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the comment. Can be used for GENDER
* $4 - permalink to the comment
* $5 - Reason, from the moderating user, for moderating this post
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-suppressed-post' => 'Used as revision comment when a post has been suppressed.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the comment. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the comment. Can be used for GENDER
* $4 - permalink to the comment
* $5 - Reason, from the moderating user, for moderating this post
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-restored-post' => 'Used as revision comment when a post has been restored (un-hidden).

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who restored the comment. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the comment. Can be used for GENDER
* $4 - permalink to the comment
* $5 - Reason, from the moderating user, for moderating this post
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-hid-topic' => 'Used as revision comment when a topic has been hidden.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-deleted-topic' => 'Used as revision comment when a topic has been deleted.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-suppressed-topic' => 'Used as revision comment when a topic has been suppressed.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-rev-message-restored-topic' => 'Used as revision comment when a topic has been restored (un-hidden).

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who restored the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
* $6 - Name of the topic the post belongs to
{{Related|Flow-rev-message}}',
	'flow-board-history' => 'Used as <code><nowiki><h1></nowiki></code> heading and HTML title in the "Board history" page.

Parameters:
* $1 - the title to which the flow board belongs
{{Identical|History}}',
	'flow-topic-history' => 'Used as <code><nowiki><h1></nowiki></code> heading and HTML title in the "Topic history" page.

Parameters:
* $1 - the topic title',
	'flow-post-history' => 'Used as <code><nowiki><h1></nowiki></code> heading and HTML title in the "Post history" page.

Parameters:
* $1 - the topic title
* $2 - the username of the creator of the post. Can be used for GENDER',
	'flow-history-last4' => 'Used as <code><nowiki><h2></nowiki></code> heading in the "Topic history" page to display all history of the last 4 hours',
	'flow-history-day' => 'Used as <code><nowiki><h2></nowiki></code> heading in the "Topic history" page to display all history of today.
{{Identical|Today}}',
	'flow-history-week' => 'Used as <code><nowiki><h2></nowiki></code> heading in the "Topic history" page to display all history of last week.

This "Last week" is equal to "Last 7 days".
{{Identical|Last week}}',
	'flow-history-pages-topic' => 'Used to describe what board the topic is added to. Parameters:
* $1 - the link to the page
* $2 - the page title',
	'flow-history-pages-post' => 'Used to describe what topic the post is added to. Parameters:
* $1 - the link to the topic
* $2 - the topic title',
	'flow-topic-participants' => 'Message to display the amount of participants in this topic (and potentially a couple of names).

Parameters:
* $1 - the total amount of participants in the conversation, can be used for PLURAL
* $2 - the total amount of participants minus 3, can be used to generate a message like: X, Y, Z and $2 others ($3, $4 and $5 will be usernames)
* $3 - username of the topic starter, can be used for GENDER
* $4 - username of the most recent participant (if there is a second participant, otherwise not available), can be used for GENDER
* $5 - username of the second most recent participant (if there is a third participant, otherwise not available), can be used for GENDER',
	'flow-topic-comments' => 'Message to display the amount of comments in this topic. Shown as a link after the topic title and the line with the topic authors. Clicking the link lets the current user write a new comment.

Parameters:
* $1 - the number of comments on this topic, can be used for PLURAL
* $2 - the name of the current user, can be used for GENDER
See also:
* {{msg-mw|Flow-topic-meta-minimal}}',
	'flow-comment-restored' => 'Used as revision comment when the post has been restored.

See also:
* {{msg-mw|Flow-comment-deleted}}',
	'flow-comment-deleted' => 'Used as revision comment when the post has been deleted.

See also:
* {{msg-mw|Flow-comment-restored}}',
	'flow-comment-hidden' => 'Used as revision comment when the post has been hidden.',
	'flow-comment-moderated' => 'Used as a revision comment when the post has been oversighted.',
	'flow-paging-rev' => 'Label for paging link that shows more recently modified topics.

See also:
* {{msg-mw|Flow-paging-fwd}}',
	'flow-paging-fwd' => 'Label for paging link that shows less recently modified topics.

See also:
* {{msg-mw|Flow-paging-rev}}',
	'flow-last-modified' => 'Followed by the timestamp.

Parameters:
* $1 - most significant unit of time since modification rendered by MWTimestamp::getHumanTimestamp',
	'flow-notification-reply' => 'Notification text for when a user receives a reply. Parameters:
* $1 - Username of the person who replied
* $2 - Title of the topic
* $3 - Title for the Flow board, this parameter is not used for the message at this moment
* $4 - Title for the page that the Flow board is attached to
* $5 - Permanent URL for the post
{{Related|Flow-notification}}',
	'flow-notification-reply-bundle' => 'Notification text for when a user receives replies from multiple users on the same topic.

Parameters:
* $1 - username of the person who replied
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
* $4 - permantent URL for the post
* $5 - the count of other action performers, could be number or {{msg-mw|Echo-notification-count}}. e.g. 7 others or 99+ others
* $6 - a number used for plural support
See also:
* {{msg-mw|Flow-notification-reply-email-batch-bundle-body}}
{{Related|Flow-notification}}',
	'flow-notification-edit' => "Notification text for when a user's post is edited. Parameters:
* $1 - Username of the person who edited the post
* $2 - Title of the topic
* $3 - Title for the Flow board
* $4 - Title for the page that the Flow board is attached to
* $5 - Permanent URL for the post
{{Related|Flow-notification}}",
	'flow-notification-edit-bundle' => 'Notification text for when a user receives post edits from multiple users on the same topic.

Parameters:
* $1 - username of the person who edited post
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
* $4 - permantent URL for the topic
* $5 - the count of other action performers, could be number or {{msg-mw|Echo-notification-count}}. e.g. 7 others or 99+ others
* $6 - a number used for plural support
See also:
* {{msg-mw|Flow-notification-edit-email-batch-bundle-body}}
{{Related|Flow-notification}}',
	'flow-notification-newtopic' => 'Notification text for when a new topic is created. Parameters:
* $1 - Username of the person who created the topic
* $2 - Title for the Flow board
* $3 - Title for the page that the Flow board is attached to
* $4 - Title of the topic
* $5 - Permanent URL for the topic
{{Related|Flow-notification}}',
	'flow-notification-rename' => 'Notification text for when the subject of a topic is changed. Parameters:
* $1 - username of the person who edited the title, can be used for GENDER
* $2 - permalink to the topic
* $3 - old topic subject
* $4 - new topic subject
* $5 - title for the Flow board
* $6 - title for the page that the Flow board is attached to
{{Related|Flow-notification}}',
	'flow-notification-mention' => '{{doc-singularthey}}
Notification text for when a user is mentioned in another conversation. Parameters:
* $1 - username of the person who made the post, can be used for GENDER
* $2 - permalink to the post
* $3 - title of the topic
* $4 - title for the page that the Flow board is attached to
{{Related|Flow-notification}}',
	'flow-notification-link-text-view-post' => 'Label for button that links to a flow post.',
	'flow-notification-link-text-view-board' => 'Label for button that links to a flow discussion board.',
	'flow-notification-link-text-view-topic' => 'Link text in for the view topic button in a notification',
	'flow-notification-reply-email-subject' => 'Email notification subject when a user receives a reply. Parameters:
* $1 - username of the person who replied
{{Related|Flow-notification-email}}',
	'flow-notification-reply-email-batch-body' => 'Email notification body when a user receives a reply, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who replied
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
{{Related|Flow-notification-email}}',
	'flow-notification-reply-email-batch-bundle-body' => 'Email notification body when a user receives reply from multiple users, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who replied
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
* $4 - the count of other action performers, could be number or {{msg-mw|Echo-notification-count}}. e.g. 7 others or 99+ others
* $5 - a number used for plural support
{{Related|Flow-notification-email}}',
	'flow-notification-mention-email-subject' => 'Email notification subject when a user is mentioned in a post.  Parameters:
* $1 - username of the person who mentions other users
* $2 - flow title text
{{Related|Flow-notification-email}}',
	'flow-notification-mention-email-batch-body' => '{{doc-singularthey}}
Email notification body when a user is mentioned in a post, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who mentions other users
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
{{Related|Flow-notification-email}}',
	'flow-notification-edit-email-subject' => 'Subject line of notification email for post being edited. Parameters:
* $1 - name of the user that edited the post
{{Related|Flow-notification-email}}',
	'flow-notification-edit-email-batch-body' => 'Email notification for post being edited. Parameters:
* $1 - name of the user that edited the post
* $2 - name of the topic the edited post belongs to
* $3 - title of the page the topic belongs to
{{Related|Flow-notification-email}}',
	'flow-notification-edit-email-batch-bundle-body' => 'Email notification body when a user receives post edits from multiple users, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who replied
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
* $4 - the count of other action performers, could be number or {{msg-mw|Echo-notification-count}}. e.g. 7 others or 99+ others
* $5 - a number used for plural support
{{Related|Flow-notification-email}}',
	'flow-notification-rename-email-subject' => 'Subject line of notification email for topic being renamed. Parameters:
* $1 - name of the user that renamed the topic
{{Related|Flow-notification-email}}',
	'flow-notification-rename-email-batch-body' => 'Email notification for topic being renamed. Parameters:
* $1 - name of the user that renamed the topic
* $2 - the original topic title
* $3 - the new topic title
* $4 - title of the page the topic belongs to
{{Related|Flow-notification-email}}',
	'flow-notification-newtopic-email-subject' => 'Subject line of notification email for new topic creation. Parameters:
* $1 - name of the user that created a new topic
* $2 - title
{{Related|Flow-notification-email}}',
	'flow-notification-newtopic-email-batch-body' => 'Email notification for new topic creation. Parameters:
* $1 - name of the user that created a new topic
* $2 - the title of the new topic
* $3 - title of the page the topic belongs to
{{Related|Flow-notification-email}}',
	'echo-category-title-flow-discussion' => 'This is a short title for notification category. Parameters:
* $1 - number of mentions, for PLURAL support
{{Related|Echo-category-title}}',
	'echo-pref-tooltip-flow-discussion' => 'This is a short description of the flow-discussion notification category.
{{Related|Echo-pref-tooltip}}',
	'flow-link-post' => 'Text used when linking to a post from recentchanges.
{{Identical|Post}}',
	'flow-link-topic' => 'Text used when linking to a topic from recentchanges.
{{Identical|Topic}}',
	'flow-link-history' => 'Text used when linking to history of a post/topic from recentchanges.
{{Identical|History}}',
	'flow-moderation-reason-placeholder' => 'Placeholder text for the textbox that holds the reason field on moderation confirmation dialogs.',
	'flow-moderation-title-suppress-post' => 'Title for the moderation confirmation dialog when a post is being suppressed.
{{Related|Flow-moderation-title}}',
	'flow-moderation-title-delete-post' => 'Title for the moderation confirmation dialog when a post is being deleted.
{{Related|Flow-moderation-title}}
{{Identical|Delete post}}',
	'flow-moderation-title-hide-post' => 'Title for the moderation confirmation dialog when a post is being hidden.
{{Related|Flow-moderation-title}}
{{Identical|Hide post}}',
	'flow-moderation-title-restore-post' => 'Title for the moderation confirmation dialog when a post is being restored.
{{Related|Flow-moderation-title}}
{{Identical|Restore post}}',
	'flow-moderation-intro-suppress-post' => 'Intro for the moderation confirmation dialog when a post is being suppressed. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-delete-post' => 'Intro for the moderation confirmation dialog when a post is being deleted. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-hide-post' => 'Intro for the moderation confirmation dialog when a post is being hidden. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-restore-post' => 'Intro for the restore confirmation dialog. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-confirm-suppress-post' => 'Label for a button that will confirm suppression of a post.
{{Related|Flow-moderation-confirm}}
{{Identical|Suppress}}',
	'flow-moderation-confirm-delete-post' => 'Label for a button that will confirm deletion of a post.
{{Related|Flow-moderation-confirm}}
{{Identical|Delete}}',
	'flow-moderation-confirm-hide-post' => 'Label for a button that will confirm hiding of a post.
{{Related|Flow-moderation-confirm}}
{{Identical|Hide}}',
	'flow-moderation-confirm-restore-post' => 'Label for a button that will confirm restoring of a post.
{{Related|Flow-moderation-confirm}}
{{Identical|Restore}}',
	'flow-moderation-confirmation-suppress-post' => 'Message displayed after a successful suppression of a post. Parameters:
* $1 - the user whose post is being moderated
* $2 - the username, for GENDER support
* $3 - (Unused) the current user, for GENDER support
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-delete-post' => 'Message displayed after a successful deletion of a post. Parameters:
* $1 - the user whose post is being moderated
* $2 - the username, for GENDER support
* $3 - (Unused) the current user, for GENDER support
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-hide-post' => 'Message displayed after a successful hiding of a post. Parameters:
* $1 - the user whose post is being moderated
* $2 - the username, for GENDER support
* $3 - (Unused) the current user, for GENDER support
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-restore-post' => 'Message displayed after a successful restoring of a post. Parameters:
* $1 - (Unused) the user whose post is being moderated
* $2 - (Unused) the username, for GENDER support
* $3 - (Unused) the current user, for GENDER support
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-title-suppress-topic' => 'Title for the moderation confirmation dialog when a topic is being suppressed.
{{Related|Flow-moderation-title}}',
	'flow-moderation-title-delete-topic' => 'Title for the moderation confirmation dialog when a topic is being deleted.
{{Related|Flow-moderation-title}}
{{Identical|Delete topic}}',
	'flow-moderation-title-hide-topic' => 'Title for the moderation confirmation dialog when a topic is being hidden.
{{Related|Flow-moderation-title}}
{{Identical|Hide topic}}',
	'flow-moderation-title-restore-topic' => 'Title for the moderation confirmation dialog when a topic is being restored.
{{Related|Flow-moderation-title}}
{{Identical|Restore topic}}',
	'flow-moderation-intro-suppress-topic' => 'Intro for the moderation confirmation dialog when a topic is being suppressed. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-delete-topic' => 'Intro for the moderation confirmation dialog when a topic is being deleted. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-hide-topic' => 'Intro for the moderation confirmation dialog when a topic is being hidden. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-restore-topic' => 'Intro for the restore confirmation dialog. Parameters:
* $1 - (Unused) The user whose post is being moderated.
* $2 - (Unused) The subject.
* $3 - the user who is moderating the post. GENDER supported.
{{Related|Flow-moderation-intro}}',
	'flow-moderation-confirm-suppress-topic' => 'Label for a button that will confirm suppression of a topic.
{{Related|Flow-moderation-confirm}}
{{Identical|Suppress}}',
	'flow-moderation-confirm-delete-topic' => 'Label for a button that will confirm deletion of a topic.
{{Related|Flow-moderation-confirm}}
{{Identical|Delete}}',
	'flow-moderation-confirm-hide-topic' => 'Label for a button that will confirm hiding of a topic.
{{Related|Flow-moderation-confirm}}
{{Identical|Hide}}',
	'flow-moderation-confirm-restore-topic' => 'Label for a button that will confirm restoring of a topic.
{{Related|Flow-moderation-confirm}}
{{Identical|Restore}}',
	'flow-moderation-confirmation-suppress-topic' => 'Message displayed after a successful suppression of a topic. Parameters:
* $1 - the user whose post is being moderated
* $2 - the username, for GENDER support
* $3 - (Unused) the current user, for GENDER support
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-delete-topic' => 'Message displayed after a successful deletion of a topic. Parameters:
* $1 - the user whose post is being moderated
* $2 - the username, for GENDER support
* $3 - (Unused) the current user, for GENDER support
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-hide-topic' => 'Message displayed after a successful hiding of a topic. Parameters:
* $1 - the user whose post is being moderated
* $2 - the username, for GENDER support
* $3 - (Unused) the current user, can be used for GENDER
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-restore-topic' => 'Message displayed after a successful restoring of a topic.
* $1 - (Unused) the user whose post is being moderated
* $2 - (Unused) the username, for GENDER support
* $3 - (Unused) the current user, for GENDER support
{{Related|Flow-moderation-confirmation}}',
	'flow-topic-permalink-warning' => 'Displayed at the top of a page when a person has clicked on a permanent link to a topic.

Parameters:
* $1 - display text for a link to the board that the topic comes from
* $2 - URL for a link to the board that the topic comes from
See also:
* {{msg-mw|Flow-topic-permalink-warning-user-board}}',
	'flow-topic-permalink-warning-user-board' => "Displayed at the top of a page when a person has clicked on a permanent link to a topic from a user's board.

Parameters:
* $1 - the user's name. Supports GENDER.
* $2 - URL for a link to the board that the topic comes from
See also:
* {{msg-mw|Flow-topic-permalink-warning}}",
	'flow-revision-permalink-warning-post' => 'Header displayed at the top of a page when somebody is viewing a single-revision permalink of a post.

This message will not appear for the first revision, which has its own message ({{msg-mw|Flow-revision-permalink-warning-post-first}}).

Note that the "topic permalink warning" (see {{msg-mw|Flow-topic-permalink-warning}}) will also be displayed.

Parameters:
* $1 - date and timestamp, formatted as most are in Flow. That is, a human-readable timestamp that changes into an RFC2822 timestamp when hovered over.
* $2 - title of the Flow Board that the post appears on. Example: User talk:Andrew
* $3 - title of the topic that this post appears in
* $4 - URL to the history page
* $5 - URL to the diff from the previous revision to this one
See also:
* {{msg-mw|Flow-revision-permalink-warning-post-first}}
* {{msg-mw|Flow-revision-permalink-warning-header}}',
	'flow-revision-permalink-warning-post-first' => 'Header displayed at the top of a page when somebody is viewing a single-revision permalink of a post.

This message will only be shown for the first revision.

Note that the "topic permalink warning" (see {{msg-mw|Flow-topic-permalink-warning}}) will also be displayed.

Parameters:
* $1 - date and timestamp, formatted as most are in Flow. That is, a human-readable timestamp that changes into an RFC2822 timestamp when hovered over.
* $2 - title of the Flow Board that the post appears on. Example: User talk:Andrew
* $3 - title of the topic that this post appears in
* $4 - URL to the history page
See also:
* {{msg-mw|Flow-revision-permalink-warning-post}}
* {{msg-mw|Flow-revision-permalink-warning-header-first}}',
	'flow-revision-permalink-warning-header' => 'Header displayed at the top of a page when somebody is viewing a single-revision permalink of board header.

This message will not appear for the first revision, which has its own message ({{msg-mw|Flow-revision-permalink-warning-header-first}}).

Parameters:
* $1 - date and timestamp, formatted as most are in Flow. That is, a human-readable timestamp that changes into an RFC2822 timestamp when hovered over.
* $2 - URL to the history page
* $3 - URL to the diff from the previous revision to this one
See also:
* {{msg-mw|Flow-revision-permalink-warning-header-first}}
* {{msg-mw|Flow-revision-permalink-warning-post}}',
	'flow-revision-permalink-warning-header-first' => 'Header displayed at the top of a page when somebody is viewing a single-revision permalink of board header.

This message will only be shown for the first revision.

Parameters:
* $1 - (Unused) date and timestamp, formatted as most are in Flow. That is, a human-readable timestamp that changes into an RFC2822 timestamp when hovered over.
* $2 - URL to the history page
See also:
* {{msg-mw|Flow-revision-permalink-warning-header}}
* {{msg-mw|Flow-revision-permalink-warning-post-first}}',
	'flow-compare-revisions-revision-header' => 'Diff column header for a revision. Parameters:
* $1 - date and timestamp, formatted as most are in Flow. That is, a human-readable timestamp that changes into an RFC-2822 timestamp when hovered over.
* $2 - user who made this revision',
	'flow-compare-revisions-header-post' => 'Header for a page showing a "diff" between two revisions of a Flow post. Parameters:
* $1 - the title of the Board on which this post sits. Example: User talk:Andrew
* $2 - the subject of the Topic in which this post sits
* $3 - the username of the author of the post
* $4 - URL to the Board, with the fragment set to the post in question
* $5 - URL to the Topic, with the fragment set to the post in question
* $6 - URL to the history page for this post
See also:
* {{msg-mw|Flow-compare-revisions-header-header}}',
	'flow-compare-revisions-header-header' => 'Header for a page showing a "diff" between two revisions of a Flow board header. Parameters:
* $1 - the title of the Board on which this header sits. Example: User talk:Andrew
* $2 - the username of the author of the header
* $3 - URL to the Board, with the fragment set to the post in question
* $4 - URL to the history page for this post
See also:
* {{msg-mw|Flow-compare-revisions-header-post}}',
	'flow-topic-collapsed-one-line' => 'Used as title for the icon which is used to show small view of topics.

"Small view" is also called "Collapsed one line view".',
	'flow-topic-collapsed-full' => 'Used as title for the icon which is used to show collapsed view of topics.',
	'flow-topic-complete' => 'Used as title for the icon which is used to show full view of topics.
{{Identical|Full view}}',
	'flow-terms-of-use-new-topic' => 'Terms of use for adding a new topic.

This should be consistent with {{msg-mw|Flow-newtopic-save}}.
{{Related|Flow-terms-of-use}}',
	'flow-terms-of-use-reply' => 'Terms of use for posting a reply.

This should be consistent with {{msg-mw|Flow-reply-submit}}.
{{Related|Flow-terms-of-use}}',
	'flow-terms-of-use-edit' => 'Terms of use for editing a header/topic/post.
{{Related|Flow-terms-of-use}}',
);

/** Achinese (Acèh)
 * @author Rachmat.Wahidi
 */
$messages['ace'] = array(
	'flow-hide-content' => '{{GENDER:$1|Geupeusom}} lé $1',
	'flow-delete-content' => '{{GENDER:$1|Geusampôh}} lé $1',
	'flow-topic-action-hide-topic' => 'Peusom topik',
	'flow-topic-action-delete-topic' => 'Sampôh topik',
	'flow-topic-action-restore-topic' => 'Peuriwang topik',
	'flow-rev-message-hid-topic' => '[[Ureuëng Nguy:$1|$1]] {{GENDER:$1|geupeusom}} [topic $3].', # Fuzzy
	'flow-rev-message-deleted-topic' => '[[Ureuëng Nguy:$1|$1]] {{GENDER:$1|sampôh}} [kumènta $3].', # Fuzzy
	'flow-rev-message-restored-topic' => '[[Ureuëng Nguy:$1|$1]] {{GENDER:$1|peuriwang}} [topik $3].', # Fuzzy
	'flow-moderation-title-delete-topic' => 'Sampôh topik?',
	'flow-moderation-title-hide-topic' => 'Peusom topik?',
	'flow-moderation-title-restore-topic' => 'Peuriwang topik?',
	'flow-moderation-intro-delete-topic' => 'Tulông peutrang pakön droeneuh neuneuk sampôh topik nyoe.',
	'flow-moderation-intro-hide-topic' => 'Neutulông peutrang pakön peusom topik nyoe.',
	'flow-moderation-confirm-delete-topic' => 'Sampôh',
	'flow-moderation-confirm-hide-topic' => 'Peusom',
	'flow-moderation-confirm-restore-topic' => 'Peuriwang',
	'flow-moderation-confirmation-restore-topic' => 'Droeneuh ka lheuh neupeuriwang topik nyoe.',
);

/** Arabic (العربية)
 * @author Claw eg
 * @author مشعل الحربي
 */
$messages['ar'] = array(
	'flow-post-actions' => 'الإجراءات',
	'flow-topic-actions' => 'الإجراءات',
	'flow-error-http' => 'حدث خطأ أثناء الاتصال بالخادم.',
	'flow-error-external' => 'حدث خطأ.<br />رسالة الخطأ المتلقاة هي: $1',
	'flow-moderation-title-restore-post' => 'استعد الصفحة',
	'flow-moderation-confirmation-restore-post' => 'لقد استعدت هذه الصفحة بنجاح.', # Fuzzy
	'flow-topic-permalink-warning' => 'بدأ هذا الموضوع في [$2  $1]',
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'flow-desc' => 'Sistema de xestión del fluxu de trabayu',
	'flow-talk-taken-over' => "Esta páxina d'alderique has sustituyóse por un [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal tableru Flow].",
	'log-name-flow' => "Rexistru d'actividá de Flow",
	'logentry-delete-flow-delete-post' => "$1 {{GENDER:$2|desanició}} un [$4 mensaxe]'n [[$3]]",
	'logentry-delete-flow-restore-post' => "$1 {{GENDER:$2|restauró}} un [$4 mensaxe]'n [[$3]]",
	'logentry-suppress-flow-suppress-post' => "$1 {{GENDER:$2|suprimió}} un [$4 mensaxe]'n [[$3]]",
	'logentry-suppress-flow-restore-post' => "$1 {{GENDER:$2|desanició}} un [$4 mensaxe]'n [[$3]]",
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|desanició}} un [$4 asuntu] en [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|restauró}} un [$4 asuntu] en [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|suprimió}} un [$4 asuntu] en [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|desanició}} un [$4 asuntu] en [[$3]]',
	'flow-user-moderated' => 'Usuariu moderáu',
	'flow-edit-header-link' => 'Editar la testera',
	'flow-header-empty' => "Anguaño esta páxina d'alderique nun tien testera.",
	'flow-post-moderated-toggle-hide-show' => 'Amosar el comentariu {{GENDER:$1|tapecíu}} por $2',
	'flow-post-moderated-toggle-delete-show' => 'Amosar el comentariu {{GENDER:$1|desaniciáu}} por $2',
	'flow-post-moderated-toggle-suppress-show' => 'Amosar el comentariu {{GENDER:$1|suprimíu}} por $2',
	'flow-post-moderated-toggle-hide-hide' => 'Tapecer el comentariu {{GENDER:$1|tapecíu}} por $2',
	'flow-post-moderated-toggle-delete-hide' => 'Tapecer el comentariu {{GENDER:$1|desaniciáu}} por $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Tapecer el comentariu {{GENDER:$1|suprimíu}} por $2',
	'flow-hide-post-content' => 'Esti comentariu {{GENDER:$1|tapecióse}} por $2',
	'flow-hide-title-content' => 'Esti asuntu {{GENDER:$1|tapecióse}} por $2',
	'flow-hide-header-content' => '{{GENDER:$1|Tapecíu}} por $2',
	'flow-delete-post-content' => 'Esti comentariu {{GENDER:$1|desanicióse}} por $2',
	'flow-delete-title-content' => 'Esti asuntu {{GENDER:$1|desanicióse}} por $2',
	'flow-delete-header-content' => '{{GENDER:$1|Desaniciáu}} por $2',
	'flow-suppress-post-content' => 'Esti comentariu {{GENDER:$1|suprimióse}} por $2',
	'flow-suppress-title-content' => 'Esti asuntu {{GENDER:$1|suprimióse}} por $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Suprimíu}} por $2',
	'flow-suppress-usertext' => "<em>Nome d'usuariu suprimíu</em>",
	'flow-post-actions' => 'Aiciones',
	'flow-topic-actions' => 'Aiciones',
	'flow-cancel' => 'Encaboxar',
	'flow-preview' => 'Vista previa',
	'flow-show-change' => 'Amosar cambeos',
	'flow-last-modified-by' => 'Últimu {{GENDER:$1|cambiu}} por $1',
	'flow-stub-post-content' => "''Por un fallu téunicu, esti mensaxe nun pudo recuperase.''",
	'flow-newtopic-title-placeholder' => 'Nuevu asuntu',
	'flow-newtopic-content-placeholder' => 'Amieste algún detalle, si quier',
	'flow-newtopic-header' => 'Amestar un nuevu asuntu',
	'flow-newtopic-save' => 'Amestar un asuntu',
	'flow-newtopic-start-placeholder' => 'Principiar un nuevu asuntu',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Comentar}} sobro «$2»',
	'flow-reply-placeholder' => '{{GENDER:$1|Responder}} a $1',
	'flow-reply-submit' => '{{GENDER:$1|Responder}}',
	'flow-reply-link' => '{{GENDER:$1|Responder}}',
	'flow-thank-link' => '{{GENDER:$1|Agradecer}}',
	'flow-post-edited' => 'Mensaxe {{GENDER:$1|editáu}} por $1 $2',
	'flow-post-action-view' => 'Enllaz permanente',
	'flow-post-action-post-history' => 'Historial',
	'flow-post-action-suppress-post' => 'Suprimir',
	'flow-post-action-delete-post' => 'Desaniciar',
	'flow-post-action-hide-post' => 'Tapecer',
	'flow-post-action-edit-post' => 'Editar',
	'flow-post-action-restore-post' => 'Restaurar el mensaxe',
	'flow-topic-action-view' => 'Enllaz permanente',
	'flow-topic-action-watchlist' => 'Llista de vixilancia',
	'flow-topic-action-edit-title' => 'Editar el títulu',
	'flow-topic-action-history' => 'Historial',
	'flow-topic-action-hide-topic' => 'Tapecer esti asuntu',
	'flow-topic-action-delete-topic' => 'Desaniciar esti asuntu',
	'flow-topic-action-suppress-topic' => 'Suprimir esti asuntu',
	'flow-topic-action-restore-topic' => 'Restaurar esti asuntu',
	'flow-error-http' => 'Hebo un error al comunicase col sirvidor.',
	'flow-error-other' => 'Hebo un fallu inesperáu.',
	'flow-error-external' => "Hebo un error.<br />El mensaxe d'error recibíu ye: $1",
	'flow-error-edit-restricted' => 'Nun tien permisu pa editar esti mensaxe.',
	'flow-error-external-multi' => 'Alcontráronse errores.<br />$1',
	'flow-error-missing-content' => 'El mensaxe nun tien conteníu. El conteníu ye obligatoriu pa guardar un mensaxe.',
	'flow-error-missing-title' => "L'asuntu nun tien títulu. El títulu ye obligatoriu pa guardar un asuntu.",
	'flow-error-parsoid-failure' => 'Nun ye posible analizar el conteníu por un fallu de Parsoid.',
	'flow-error-missing-replyto' => 'Nun se dio nengún parámetru «responder a». Esti parámetru ye obligatoriu pa l\'aición "responder".',
	'flow-error-invalid-replyto' => "El parámetru «responder a» yera inválidu. Nun pudo alcontrase'l mensaxe especificáu.",
	'flow-error-delete-failure' => "Falló'l desaniciu d'esti elementu.",
	'flow-error-hide-failure' => "Falló'l tapecer esti elementu.",
	'flow-error-missing-postId' => "Nun se dio nengún parámetru «postId». Esti parámetru ye obligatoriu p'actuar sobro un mensaxe.",
	'flow-error-invalid-postId' => "El parámetru «postId» yera inválidu. Nun pudo alcontrase'l mensaxe especificáu ($1).",
	'flow-error-restore-failure' => "Falló la restauración d'esti elementu.",
	'flow-error-invalid-moderation-state' => 'Diose un valor inválidu pa moderationState.',
	'flow-error-invalid-moderation-reason' => 'Por favor, ufra un motivu pa la moderación.',
	'flow-error-not-allowed' => 'Nun tien permisu bastante pa executar esta aición.',
	'flow-error-title-too-long' => 'Los títulos del asuntu tan llendaos a $1 {{PLURAL:$1|byte|bytes}}.',
	'flow-error-no-existing-workflow' => 'Esti fluxu de trabayu inda nun esiste.',
	'flow-error-not-a-post' => 'El títulu del asuntu nun pue guardase como mensaxe.',
	'flow-error-missing-header-content' => 'La testera nun tien conteníu. El conteníu ye obligatoriu pa guardar una testera.',
	'flow-error-missing-prev-revision-identifier' => "Falta l'identificador de revisión anterior.",
	'flow-error-prev-revision-mismatch' => "Otru usuariu acaba d'editar esti mensaxe hai dellos segundos. ¿Ta seguru de que quier sobreescribir esi cambiu?",
	'flow-error-prev-revision-does-not-exist' => 'Nun pudo alcontrase la revisión anterior.',
	'flow-error-default' => 'Hebo un error.',
	'flow-error-invalid-input' => 'Dióse un valor inválidu pa cargar el conteníu de fluxu.',
	'flow-error-invalid-title' => 'Dióse un títulu de páxina inválidu.',
	'flow-error-fail-load-history' => 'Falló la carga del conteníu del historial.',
	'flow-error-missing-revision' => 'Nun pudo alcontrase una revisión pa cargar conteníu de fluxu.',
	'flow-error-fail-commit' => "Nun pudo guardase'l conteníu del fluxu.",
	'flow-error-insufficient-permission' => 'Nun tien permisu bastante pa tener accesu al conteníu.',
	'flow-error-revision-comparison' => 'La operación diff sólo pue facese ente dos revisiones del mesmu mensaxe.',
	'flow-error-missing-topic-title' => "Nun pue alcontrase'l títulu del asuntu del fluxu de trabayu actual.",
	'flow-error-fail-load-data' => 'Nun pudieron cargase los datos solicitaos.',
	'flow-error-invalid-workflow' => "Nun pudo alcontrase'l fluxu de trabayu solicitáu.",
	'flow-error-process-data' => 'Hebo un error al procesar los datos de la solicitú.',
	'flow-error-process-wikitext' => 'Hebo un error al procesar la conversión HTML/testu wiki.',
	'flow-error-no-index' => "Nun s'alcontró un índiz pa facer la gueta de datos.",
	'flow-edit-header-submit' => 'Guardar testera',
	'flow-edit-header-submit-overwrite' => 'Sobreescribir testera',
	'flow-edit-title-submit' => 'Camudar el títulu',
	'flow-edit-title-submit-overwrite' => 'Sobreescribir el títulu',
	'flow-edit-post-submit' => 'Unviar los cambios',
	'flow-edit-post-submit-overwrite' => 'Sobreescribir los cambios',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|editó}} un [$3 comentariu] sobro $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|comentó}}] sobro $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '{{PLURAL:$1|Amestóse|Amestáronse}} <strong>$1 {{PLURAL:$1|comentariu|comentarios}}</strong>.',
	'flow-rev-message-new-post' => "$1 {{GENDER:$2|creó}} l'asuntu [$3 $4].",
	'flow-rev-message-edit-title' => "$1 {{GENDER:$2|camudó}}'l títulu del asuntu de $5 a [$3 $4].",
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|creó}} la testera.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|editó}} la testera.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|tapeció}} un [$4 comentariu] sobro $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|desanició}} un [$4 comentariu] sobro $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|suprimió}} un [$4 comentariu] sobro $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|restauró}} un [$4 comentariu] sobro $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => "$1 {{GENDER:$2|tapeció}} l'[$4 asuntu] $6 (<em>$5</em>).",
	'flow-rev-message-deleted-topic' => "$1 {{GENDER:$2|desanició}} l'[$4 asuntu] $6 (<em>$5</em>).",
	'flow-rev-message-suppressed-topic' => "$1 {{GENDER:$2|suprimió}} l'[$4 asuntu] $6 (<em>$5</em>).",
	'flow-rev-message-restored-topic' => "$1 {{GENDER:$2|restauró}} l'[$4 asuntu] $6 (<em>$5</em>).",
	'flow-board-history' => 'Historial de «$1»',
	'flow-topic-history' => 'Historial del asuntu «$1»',
	'flow-post-history' => 'Historial del mensaxe "Comentariu de {{GENDER:$2|$2}}"',
	'flow-history-last4' => 'Últimes 4 hores',
	'flow-history-day' => 'Güei',
	'flow-history-week' => 'Cabera selmana',
	'flow-history-pages-topic' => 'Apaez nel [$1 tableru «$2»]',
	'flow-history-pages-post' => 'Apaez en [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 abrió esti filu|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} y {{PLURAL:$2|otru|otros $2}}|0=Inda naide participó|2={{GENDER:$3|$3}} y {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} y {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 comentariu|$1 comentarios|0=¡Comenta tú {{GENDER:$2|el primeru|la primera}}!}}',
	'flow-comment-restored' => 'Comentariu restauráu',
	'flow-comment-deleted' => 'Comentariu desaniciáu',
	'flow-comment-hidden' => 'Comentariu tapecíu',
	'flow-comment-moderated' => 'Comentariu moderáu',
	'flow-paging-rev' => 'Más asuntos de recién',
	'flow-paging-fwd' => 'Asuntos más antiguos',
);

/** Bikol Central (Bikol Central)
 * @author Geopoet
 */
$messages['bcl'] = array(
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|pinagliwat}} an sarong [$3 komentaryo] kan $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|pinagkomentaryohan}}] on $4.', # Fuzzy
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|pinagmukna}} an kapamayuhan.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|pinagliwat}} an kapamayuhan.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|itinago}} an sarong [$4 komentaryo] kan $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|pinagpura}} an sarong [$4 komentaryo] kan $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|pinag-untok}} an sarong [$4 komentaryo] kan $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|pinagbalikwat}} an sarong [$4 komentaryo] kan $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|itinago}} an [$4 na tema] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|pinagpura}} an [$4 na tema] $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|pinag-untok}} an [$4 na tema] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|pinagbalikwat}} an [$4 na tema] $6 (<em>$5</em>).',
);

/** Bulgarian (български)
 * @author DCLXVI
 * @author Mitzev
 */
$messages['bg'] = array(
	'flow-cancel' => 'Отказване',
	'flow-newtopic-header' => 'Добавяне на нова тема',
	'flow-newtopic-save' => 'Добавяне на тема',
	'flow-newtopic-start-placeholder' => 'Започване на нова тема',
	'flow-topic-action-watchlist' => 'Списък за наблюдение',
	'flow-rev-message-reply' => '$1[$3 {{Пол:$2|коментар}}] за $4 (<em>$5</em>).',
	'flow-link-history' => 'история',
	'flow-revision-permalink-warning-header' => 'Това е постоянна връзка към една версия на заглавката.
Тази версия е от  $1 .  Можете да видите [ $3  разлики от предишната версия], или да видите други версии на [ $2  история страницата].',
	'flow-revision-permalink-warning-header-first' => 'Това е постоянна връзка към първата версия на заглавката.
Можете да видите по-нови версии на [ $2  история страницата].',
	'flow-compare-revisions-header-header' => 'Тази страница показва  {{GENDER:$2| промени}} между две версии на заглавието на [ $3  $1 ].
Можете да видите други версии на горния му [ $4  история страница].',
);

/** Bengali (বাংলা)
 * @author Tauhid16
 */
$messages['bn'] = array(
	'flow-post-moderated-toggle-delete-show' => '$2-এর {{GENDER:$1|অপসারিত}} মন্তব্যসমূহ দেখাও।',
	'flow-post-moderated-toggle-suppress-hide' => '$2-এর {{GENDER:$1|গোপনকৃত}} মন্তব্যসমূহ লুকাও।',
	'flow-post-action-post-history' => 'ইতিহাস',
	'flow-post-action-edit-post' => 'সম্পাদনা',
	'flow-moderation-confirmation-suppress-post' => 'আপনার পোস্টটি সফলভাবে গোপন করা হয়েছে। 
পোস্টটির উপর প্রতিক্রিয়া {{GENDER:$1|প্রকাশের}} $1 মাধ্যমে বিবেচনা করুন।', # Fuzzy
	'flow-moderation-confirmation-delete-post' => 'আপনার পোস্টটি সফলভাবে মুছে ফেলা হয়েছে। 
পোস্টটির উপর প্রতিক্রিয়া {{GENDER:$1|প্রকাশের}} $1 মাধ্যমে বিবেচনা করুন।', # Fuzzy
	'flow-moderation-confirmation-hide-post' => 'আপনার পোস্টটি সফলভাবে লুকানো হয়েছে। 
পোস্টটির উপর প্রতিক্রিয়া {{GENDER:$1|প্রকাশের}} $1 মাধ্যমে বিবেচনা করুন।', # Fuzzy
	'flow-moderation-confirmation-restore-post' => 'আপনি সফলভাবে উপরের পোস্টটি পুনরুদ্ধার করেছেন।',
	'flow-moderation-confirmation-suppress-topic' => 'আপনার পোস্টটি সফলভাবে গোপন করা হয়েছে। 
পোস্টটির উপর প্রতিক্রিয়া {{GENDER:$1|প্রকাশের}} $1 মাধ্যমে বিবেচনা করুন।', # Fuzzy
	'flow-moderation-confirmation-delete-topic' => 'আপনার পোস্টটি সফলভাবে মুছে ফেলা হয়েছে। 
পোস্টটির উপর প্রতিক্রিয়া {{GENDER:$1|প্রকাশের}} $1 মাধ্যমে বিবেচনা করুন।', # Fuzzy
	'flow-moderation-confirmation-hide-topic' => 'আপনার পোস্টটি সফলভাবে লুকানো হয়েছে। 
পোস্টটির উপর প্রতিক্রিয়া {{GENDER:$1|প্রকাশের}} $1 মাধ্যমে বিবেচনা করুন।', # Fuzzy
	'flow-moderation-confirmation-restore-topic' => 'আপনি সফলভাবে এই বিষয়টি পুনরুদ্ধার করছেন।',
);

/** Breton (brezhoneg)
 * @author Fohanno
 * @author Y-M D
 */
$messages['br'] = array(
	'flow-user-moderated' => 'Implijer habaskaet',
	'flow-post-moderated-toggle-show' => '[Diskouez]',
	'flow-post-moderated-toggle-hide' => '[Kuzhat]',
	'flow-hide-header-content' => '{{GENDER:$1|Kuzhet}} gant $2',
	'flow-delete-post-content' => 'An evezhiadenn a oa bet {{GENDER:$1|dilamet}} gant $2',
	'flow-delete-header-content' => '{{GENDER:$1|Dilamet}} gant $2',
	'flow-suppress-post-content' => 'An evezhiadenn-mañ a oa bet {{GENDER:$1|dilamet}} gant $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Dilamet}} gant $2',
	'flow-suppress-usertext' => '<em>Anv implijer lamet</em>',
	'flow-post-actions' => 'Oberoù',
	'flow-topic-actions' => 'Oberoù',
	'flow-cancel' => 'Nullañ',
	'flow-preview' => 'Rakwelet',
	'flow-show-change' => "Diskouez ar c'hemmoù",
	'flow-last-modified-by' => '{{GENDER:$1|kemmet}} da ziwezhañ gant $1',
	'flow-newtopic-content-placeholder' => 'Ouzhpennañ munudoù ma karit',
	'flow-reply-placeholder' => '{{GENDER:$1|Respont da}} to $1',
	'flow-reply-submit' => '{{GENDER:$1|Respont}}',
	'flow-reply-link' => '{{GENDER:$1|Respont}}',
	'flow-thank-link' => '{{GENDER:$1|Trugarez}}',
	'flow-edit-post-submit' => "Kas ar c'hemmoù",
	'flow-post-edited' => 'Kemennadenn {{GENDER:$1|aozet}} gant $1 $2',
	'flow-post-action-view' => 'Permalink',
	'flow-post-action-post-history' => 'Istor ar gemennadenn',
	'flow-post-action-suppress-post' => 'Lemel',
	'flow-post-action-delete-post' => 'Dilemel',
	'flow-post-action-hide-post' => 'Kuzhat',
	'flow-post-action-edit-post' => 'Aozañ ar gemennadenn',
	'flow-post-action-edit' => 'Kemmañ',
	'flow-post-action-restore-post' => 'Assevel ar gemennadenn',
	'flow-topic-action-view' => 'Permalink',
	'flow-topic-action-watchlist' => 'Roll evezhiañ',
	'flow-topic-action-edit-title' => 'Kemmañ an titl',
	'flow-error-other' => "Ur fazi dic'hortoz zo bet.",
	'flow-error-external' => 'Ur fazi zo bet.<br />Ar gemennadenn fazi resevet a oa : $1',
	'flow-error-edit-restricted' => "N'oc'h ket aotreet da aozañ ar gemennadenn-mañ.",
	'flow-error-external-multi' => 'Fazioù zo bet.<br />$1',
	'flow-error-delete-failure' => "C'hwitet eo bet diverkadenn an elfenn-mañ",
	'flow-error-hide-failure' => "N'eus ket bet gallet kuzhat an elfenn-mañ.",
	'flow-error-default' => "C'hoarvezet ez eus ur fazi.",
	'flow-edit-title-submit' => 'Cheñch an titl',
	'flow-board-history' => 'Istor "$1"',
	'flow-history-last4' => '4 eur diwezhañ',
	'flow-history-day' => 'Hiziv',
	'flow-history-week' => 'Er sizhun baseet',
	'flow-comment-restored' => 'Evezhiadenn assavet',
	'flow-comment-deleted' => 'Evezhiadenn dilamet',
	'flow-comment-hidden' => 'Evezhiadenn kuzhet',
	'flow-comment-moderated' => 'Evezhiadenn habaskaet',
	'flow-last-modified' => 'Kemm diwezhañ war-dro $1',
	'flow-notification-link-text-view-post' => 'Gwelet ar gemennadenn',
	'flow-notification-link-text-view-board' => 'Gwelet an daolenn',
	'flow-notification-reply-email-subject' => "$1 {{GENDER:$1|en deus|he deus}} respontet d'ho kemennadenn",
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|en deus|he deus}} respontet d\'ho kemennadenn e-barzh $2 war "$3"',
	'flow-notification-mention-email-subject' => "$1 {{GENDER:$1|en deus|he deus}} meneget ac'hanoc'h war $2",
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|en deus|he deus}} aozet ur gemennadenn',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|en deus|he deus}} aozet ur gemennadenn e-barzh $2 war "$3"',
	'flow-link-post' => 'kemennadenn',
	'flow-link-history' => 'istor',
	'flow-moderation-title-suppress-post' => 'Lemel ar gemennadenn ?',
	'flow-moderation-title-delete-post' => 'Dilemel ar gemennadenn ?',
	'flow-moderation-title-hide-post' => 'Kuzhat ar gemennadenn ?',
	'flow-moderation-title-restore-post' => 'Assevel ar gemennadenn ?',
	'flow-moderation-intro-suppress-post' => '{{GENDER:$3|Displegit}}, mar plij, perak e tilamit ar gemennadenn-mañ.',
	'flow-moderation-intro-delete-post' => '{{GENDER:$3|Displegit}}, mar plij perak e tilamit ar gemennadenn-mañ.',
	'flow-moderation-intro-hide-post' => '{{GENDER:$3|Displegit}}, lar plij, perak e kuzhit ar gemennadenn-mañ.',
	'flow-moderation-intro-restore-post' => '{{GENDER:$3|Displegit}}, mar plij, perak e assavit ar gemennadenn-mañ.',
	'flow-moderation-confirm-suppress-post' => 'Lemel',
	'flow-moderation-confirm-delete-post' => 'Dilemel',
	'flow-moderation-confirm-hide-post' => 'Kuzhat',
	'flow-moderation-confirm-restore-post' => 'Assevel',
	'flow-moderation-confirmation-restore-post' => 'Assavet ho peus ar gemennadenn a-us.',
	'flow-moderation-confirm-suppress-topic' => 'Lemel',
	'flow-moderation-confirm-delete-topic' => 'Diverkañ',
	'flow-moderation-confirm-hide-topic' => 'Kuzhat',
	'flow-moderation-confirm-restore-topic' => 'Assevel',
	'flow-topic-collapsed-one-line' => 'Gwel bihan',
	'flow-topic-complete' => 'Gwel klok',
);

/** Bosnian (bosanski)
 * @author DzWiki
 */
$messages['bs'] = array(
	'flow-edit-header-link' => 'Uredi zaglavlje',
	'flow-post-actions' => 'Akcije',
	'flow-topic-actions' => 'Akcije',
	'flow-cancel' => 'Otkaži',
	'flow-show-change' => 'Prikaži izmjene',
	'flow-newtopic-title-placeholder' => 'Nova tema',
	'flow-newtopic-save' => 'Dodaj temu',
	'flow-post-action-post-history' => 'Historija',
	'flow-post-action-delete-post' => 'Obriši',
	'flow-post-action-hide-post' => 'Sakrij',
	'flow-post-action-edit-post' => 'Uredi',
	'flow-topic-action-edit-title' => 'Uredi naslov',
	'flow-topic-action-history' => 'Historija',
	'flow-edit-post-submit' => 'Pošalji promjene',
	'flow-history-day' => 'Danas',
);

/** Chechen (нохчийн)
 * @author Умар
 */
$messages['ce'] = array(
	'flow-post-actions' => 'дийраш',
	'flow-topic-actions' => 'Дийраш',
	'flow-show-change' => 'Гайта хийцам',
	'flow-last-modified-by' => 'ТӀехьара бина {{GENDER:$1|хийцам}} цу $1',
	'flow-post-action-post-history' => 'Истори',
	'flow-post-action-edit-post' => 'Тае',
	'flow-topic-action-history' => 'Теман истори', # Fuzzy
	'flow-edit-header-submit-overwrite' => 'Юха дӀаязбе корта',
	'flow-edit-title-submit-overwrite' => 'Юха дӀаязъе цӀе',
	'flow-edit-post-submit-overwrite' => 'Юха дӀаязде хийцамаш',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|тадина}} [$3  къамел] темехь $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|къамел диттина}}] темухь $4.', # Fuzzy
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2||дӀадяьккхина}} [$4 къамел] темехь $6(<em>$5</em>).',
	'flow-notification-reply-email-batch-body' => '$1 хан хааан {{GENDER:$1||жоп делла}} темехь «$2» «$3» чохь',
	'flow-notification-mention-email-subject' => '$1 хьо {{GENDER:$1||хьахийна}} «$2» чохь',
	'flow-moderation-confirmation-restore-topic' => 'Ахьа кхиамца хӀара тема карлаяьккхина.',
);

/** Czech (čeština)
 * @author Michaelbrabec
 * @author Mormegil
 * @author Paxt
 */
$messages['cs'] = array(
	'flow-post-moderated-toggle-hide-show' => 'Ukázat komentář {{GENDER:$1|skrytý}} od $2',
	'flow-post-moderated-toggle-delete-show' => 'Ukázat komentář {{GENDER:$1|odstraněný}} od $2',
	'flow-post-moderated-toggle-suppress-show' => 'Ukázat komentář {{GENDER:$1|odstraněný}} od $2',
	'flow-post-moderated-toggle-hide-hide' => 'Skrýt komentář {{GENDER:$1|skrytý}} od $2',
	'flow-post-moderated-toggle-delete-hide' => 'Skrýt komentář {{GENDER:$1|odstraněný}} od $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Skrýt komentář {{GENDER:$1|potlačený}} od $2',
	'flow-cancel' => 'Storno',
	'flow-newtopic-title-placeholder' => 'Nové téma',
	'flow-post-action-post-history' => 'Historie',
	'flow-post-action-edit-post' => 'Editovat',
	'flow-topic-action-edit-title' => 'Upravit název',
	'flow-topic-action-history' => 'Historie',
);

/** German (Deutsch)
 * @author Kghbln
 * @author Metalhead64
 */
$messages['de'] = array(
	'flow-desc' => 'Ermöglicht ein Verwaltungssystem zu Benutzerdiskussionen',
	'flow-talk-taken-over' => 'Diese Diskussionsseite wurde von einem [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow-Board] übernommen.',
	'log-name-flow' => 'Flow-Aktivitätslogbuch',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|löschte}} einen [$4 Beitrag] auf [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|stellte}} einen [$4 Beitrag] auf [[$3]] wieder her',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|unterdrückte}} einen [$4 Beitrag] auf [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|löschte}} einen [$4 Beitrag] auf [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|löschte}} ein [$4 Thema] auf [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|stellte}} ein [$4 Thema] auf [[$3]] wieder her',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|unterdrückte}} ein [$4 Thema] auf [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|löschte}} ein [$4 Thema] auf [[$3]]',
	'flow-user-moderated' => 'Moderierter Benutzer',
	'flow-edit-header-link' => 'Überschrift bearbeiten',
	'flow-header-empty' => 'Diese Diskussionsseite hat derzeit keine Überschrift.',
	'flow-post-moderated-toggle-hide-show' => 'Kommentar anzeigen, der von $2 {{GENDER:$1|versteckt}} wurde.',
	'flow-post-moderated-toggle-delete-show' => 'Kommentar anzeigen, der von $2 {{GENDER:$1|gelöscht}} wurde.',
	'flow-post-moderated-toggle-suppress-show' => 'Kommentar anzeigen, der von $2 {{GENDER:$1|unterdrückt}} wurde.',
	'flow-post-moderated-toggle-hide-hide' => 'Kommentar ausblenden, der von $2 {{GENDER:$1|versteckt}} wurde.',
	'flow-post-moderated-toggle-delete-hide' => 'Kommentar ausblenden, der von $2 {{GENDER:$1|gelöscht}} wurde.',
	'flow-post-moderated-toggle-suppress-hide' => 'Kommentar ausblenden, der von $2 {{GENDER:$1|unterdrückt}} wurde.',
	'flow-hide-post-content' => 'Dieser Kommentar wurde {{GENDER:$1|versteckt}} von $2',
	'flow-hide-title-content' => 'Dieses Thema wurde {{GENDER:$1|versteckt}} von $2',
	'flow-hide-header-content' => '{{GENDER:$1|Versteckt}} von $2',
	'flow-delete-post-content' => 'Dieser Kommentar wurde {{GENDER:$1|gelöscht}} von $2',
	'flow-delete-title-content' => 'Dieses Thema wurde {{GENDER:$1|gelöscht}} von $2',
	'flow-delete-header-content' => '{{GENDER:$1|Gelöscht}} von $2',
	'flow-suppress-post-content' => 'Dieser Kommentar wurde {{GENDER:$1|unterdrückt}} von $2',
	'flow-suppress-title-content' => 'Dieses Thema wurde {{GENDER:$1|unterdrückt}} von $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Unterdrückt}} von $2',
	'flow-suppress-usertext' => '<em>Benutzername unterdrückt</em>',
	'flow-post-actions' => 'Aktionen',
	'flow-topic-actions' => 'Aktionen',
	'flow-cancel' => 'Abbrechen',
	'flow-preview' => 'Vorschau',
	'flow-show-change' => 'Änderungen anzeigen',
	'flow-last-modified-by' => 'Zuletzt {{GENDER:$1|geändert}} von $1',
	'flow-stub-post-content' => "''Aufgrund eines technischen Fehlers konnte dieser Beitrag nicht abgerufen werden.''",
	'flow-newtopic-title-placeholder' => 'Neues Thema',
	'flow-newtopic-content-placeholder' => 'Gib hier Einzelheiten ein, wenn du möchtest.',
	'flow-newtopic-header' => 'Ein neues Thema hinzufügen',
	'flow-newtopic-save' => 'Thema hinzufügen',
	'flow-newtopic-start-placeholder' => 'Ein neues Thema starten',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Kommentieren}} auf „$2“',
	'flow-reply-placeholder' => '{{GENDER:$1|Antworten}} an $1',
	'flow-reply-submit' => '{{GENDER:$1|Antworten}}',
	'flow-reply-link' => '{{GENDER:$1|Antworten}}',
	'flow-thank-link' => '{{GENDER:$1|Danken}}',
	'flow-post-edited' => 'Beitrag {{GENDER:$1|bearbeitet}} von $1 $2',
	'flow-post-action-view' => 'Permanentlink',
	'flow-post-action-post-history' => 'Verlauf',
	'flow-post-action-suppress-post' => 'Unterdrücken',
	'flow-post-action-delete-post' => 'Löschen',
	'flow-post-action-hide-post' => 'Verstecken',
	'flow-post-action-edit-post' => 'Bearbeiten',
	'flow-post-action-restore-post' => 'Beitrag wiederherstellen',
	'flow-topic-action-view' => 'Permanentlink',
	'flow-topic-action-watchlist' => 'Beobachtungsliste',
	'flow-topic-action-edit-title' => 'Titel bearbeiten',
	'flow-topic-action-history' => 'Verlauf',
	'flow-topic-action-hide-topic' => 'Thema verstecken',
	'flow-topic-action-delete-topic' => 'Thema löschen',
	'flow-topic-action-suppress-topic' => 'Thema unterdrücken',
	'flow-topic-action-restore-topic' => 'Thema wiederherstellen',
	'flow-error-http' => 'Beim Kontaktieren des Servers ist ein Fehler aufgetreten.',
	'flow-error-other' => 'Ein unerwarteter Fehler ist aufgetreten.',
	'flow-error-external' => 'Es ist ein Fehler aufgetreten.<br />Die empfangene Fehlermeldung lautete: $1',
	'flow-error-edit-restricted' => 'Du bist nicht berechtigt, diesen Beitrag zu bearbeiten.',
	'flow-error-external-multi' => 'Es sind Fehler aufgetreten.<br />$1',
	'flow-error-missing-content' => 'Der Beitrag hat keinen Inhalt. Dieser ist erforderlich, um einen Beitrag zu speichern.',
	'flow-error-missing-title' => 'Das Thema hat keinen Titel. Dieser ist erforderlich, um ein Thema zu speichern.',
	'flow-error-parsoid-failure' => 'Aufgrund eines Parsoid-Fehlers konnte der Inhalt nicht geparst werden.',
	'flow-error-missing-replyto' => 'Es wurde kein Parameter „Antworten an“ angegeben. Dieser Parameter ist für die „Antworten“-Aktion erforderlich.',
	'flow-error-invalid-replyto' => 'Der Parameter „Antworten an“ war ungültig. Der angegebene Beitrag konnte nicht gefunden werden.',
	'flow-error-delete-failure' => 'Das Löschen dieses Objektes ist fehlgeschlagen.',
	'flow-error-hide-failure' => 'Das Verstecken dieses Objektes ist fehlgeschlagen.',
	'flow-error-missing-postId' => 'Es wurde kein Parameter „postId“ angegeben. Dieser Parameter ist zum Löschen/Wiederherstellen eines Beitrags erforderlich.',
	'flow-error-invalid-postId' => 'Der Parameter „postId“ war ungültig. Der angegebene Beitrag ($1) konnte nicht gefunden werden.',
	'flow-error-restore-failure' => 'Das Wiederherstellen dieses Objektes ist fehlgeschlagen.',
	'flow-error-invalid-moderation-state' => 'Für moderationState wurde ein ungültiger Wert angegeben',
	'flow-error-invalid-moderation-reason' => 'Bitte gib einen Grund für die Moderation an',
	'flow-error-not-allowed' => 'Keine ausreichenden Berechtigungen zum Ausführen dieser Aktion',
	'flow-error-title-too-long' => 'Thementitel sind beschränkt auf {{PLURAL:$1|ein Byte|$1 Bytes}}.',
	'flow-error-no-existing-workflow' => 'Dieses Workflow ist noch nicht vorhanden.',
	'flow-error-not-a-post' => 'Der Thementitel kann nicht als Beitrag gespeichert werden.',
	'flow-error-missing-header-content' => 'Die Überschrift hat keinen Inhalt. Um eine Überschrift zu speichern, ist ein Inhalt erforderlich.',
	'flow-error-missing-prev-revision-identifier' => 'Eine Kennung der vorherigen Version fehlt.',
	'flow-error-prev-revision-mismatch' => 'Ein anderer Benutzer hat diesen Beitrag soeben vor einigen Sekunden bearbeitet. Bist du sicher, dass du die letzte Änderung überschreiben möchtest?',
	'flow-error-prev-revision-does-not-exist' => 'Die vorherige Version konnte nicht gefunden werden.',
	'flow-error-default' => 'Es ist ein Fehler aufgetreten.',
	'flow-error-invalid-input' => 'Für das Laden des Flow-Inhalts wurde ein ungültiger Wert angegeben.',
	'flow-error-invalid-title' => 'Es wurde ein ungültiger Seitentitel angegeben.',
	'flow-error-fail-load-history' => 'Der Inhalt des Verlaufs konnte nicht geladen werden.',
	'flow-error-missing-revision' => 'Zum Laden des Flow-Inhalts konnte keine Version gefunden werden.',
	'flow-error-fail-commit' => 'Der Flow-Inhalt konnte nicht gespeichert werden.',
	'flow-error-insufficient-permission' => 'Keine ausreichenden Berechtigungen, um auf den Inhalt zugreifen zu können.',
	'flow-error-revision-comparison' => 'Der Unterschiedsvorgang kann nur für zwei Versionen des gleichen Beitrags ausgeführt werden.',
	'flow-error-missing-topic-title' => 'Der Thementitel für das aktuelle Workflow konnte nicht gefunden werden.',
	'flow-error-fail-load-data' => 'Die angeforderten Daten konnten nicht geladen werden.',
	'flow-error-invalid-workflow' => 'Das angeforderte Workflow konnte nicht gefunden werden.',
	'flow-error-process-data' => 'Beim Verarbeiten der Daten in deiner Anfrage ist ein Fehler aufgetreten.',
	'flow-error-process-wikitext' => 'Beim Verarbeiten der HTML-/Wikitext-Umwandlung ist ein Fehler aufgetreten.',
	'flow-error-no-index' => 'Es konnte kein Index zum Ausführen der Datensuche gefunden werden.',
	'flow-edit-header-submit' => 'Überschrift speichern',
	'flow-edit-header-submit-overwrite' => 'Überschrift überschreiben',
	'flow-edit-title-submit' => 'Titel ändern',
	'flow-edit-title-submit-overwrite' => 'Titel überschreiben',
	'flow-edit-post-submit' => 'Änderungen übertragen',
	'flow-edit-post-submit-overwrite' => 'Änderungen überschreiben',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|bearbeitete}} einen [$3 Kommentar] auf $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|kommentierte}}] auf $4 (<em>$5</em>).',
	'flow-rev-message-reply-bundle' => '{{PLURAL:$1|<strong>Ein Kommentar</strong> wurde|<strong>$1 Kommentare</strong> wurden}} hinzugefügt.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|erstellte}} das Thema [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|änderte}} den Thementitel von $5 zu [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|erstellte}} die Überschrift.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|bearbeitete}} die Überschrift.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|versteckte}} einen [$4 Kommentar] auf $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|löschte}} einen [$4 Kommentar] auf $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|unterdrückte}} einen [$4 Kommentar] auf $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|stellte}} einen [$4 Kommentar] auf $6 wieder her (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|versteckte}} das [$4 Thema] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|löschte}} das [$4 Thema] $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|unterdrückte}} das [$4 Thema] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|stellte}} das [$4 Thema] $6 wieder her (<em>$5</em>).',
	'flow-board-history' => 'Verlauf von „$1“',
	'flow-topic-history' => 'Themenverlauf von „$1“',
	'flow-post-history' => 'Beitragsverlauf – Kommentar von {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Letzte 4 Stunden',
	'flow-history-day' => 'Heute',
	'flow-history-week' => 'Letzte Woche',
	'flow-history-pages-topic' => 'Erscheint auf dem [$1 Board „$2“]',
	'flow-history-pages-post' => 'Erscheint auf [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 startete dieses Thema|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} und {{PLURAL:$2|ein anderer|andere}}|0=Noch keine Teilnehmer|2={{GENDER:$3|$3}} und {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} und {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Ein Kommentar|$1 Kommentare|0=Sei {{GENDER:$2|der|die|der}} erste!}}',
	'flow-comment-restored' => 'Kommentar wiederhergestellt',
	'flow-comment-deleted' => 'Kommentar gelöscht',
	'flow-comment-hidden' => 'Versteckter Kommentar',
	'flow-comment-moderated' => 'Kommentar moderiert',
	'flow-paging-rev' => 'Mehr aktuelle Themen',
	'flow-paging-fwd' => 'Ältere Themen',
	'flow-last-modified' => 'Zuletzt geändert $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|antwortete}} auf deinen <span class="plainlinks">[$5 Beitrag]</span> in „$2“ auf „$4“.',
	'flow-notification-reply-bundle' => '$1 und {{PLURAL:$6|ein anderer|$5 andere}} {{GENDER:$1|antworteten}} auf deinen <span class="plainlinks">[$4 Beitrag]</span> in „$2“ auf „$3“.',
	'flow-notification-edit' => '$1 {{GENDER:$1|bearbeitete}} einen <span class="plainlinks">[$5 Beitrag]</span> in „$2“ auf [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 und {{PLURAL:$6|ein anderer|$5 andere}} {{GENDER:$1|bearbeiteten}} einen <span class="plainlinks">[$4 Beitrag]</span> in „$2“ auf „$3“.',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|erstellte}} ein <span class="plainlinks">[$5 neues Thema]</span> auf [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|änderte}} den Titel von <span class="plainlinks">[$2 $3]</span> nach „$4“ auf [[$5|$6]].',
	'flow-notification-mention' => '$1 hat dich in {{GENDER:$1|seinem|ihrem|dem}} <span class="plainlinks">[$2 Beitrag]</span> in „$3“ auf Seite „$4“ erwähnt.',
	'flow-notification-link-text-view-post' => 'Beitrag ansehen',
	'flow-notification-link-text-view-board' => 'Board ansehen',
	'flow-notification-link-text-view-topic' => 'Thema ansehen',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|antwortete}} auf deinen Beitrag',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|antwortete}} auf deinen Beitrag in „$2“ auf „$3“',
	'flow-notification-reply-email-batch-bundle-body' => '$1 und {{PLURAL:$5|ein anderer|$4 andere}} {{GENDER:$1|antworteten}} auf deinen Beitrag in „$2“ auf „$3“',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|erwähnte}} dich auf „$2“',
	'flow-notification-mention-email-batch-body' => '$1 hat dich in {{GENDER:$1|seinem|ihrem|dem}} Beitrag in „$2“ auf der Seite „$3“ erwähnt',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|bearbeitete}} einen Beitrag',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|bearbeitete}} einen Beitrag in „$2“ auf der Seite „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 und {{PLURAL:$5|ein anderer|$4 andere}} {{GENDER:$1|bearbeiteten}} einen Beitrag in „$2“ auf der Seite „$3“',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|benannte}} dein Thema um',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|benannte}} dein Thema „$2“ in „$3“ auf der Seite „$4“ um',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|erstellte}} ein neues Thema auf „$2“',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|erstellte}} ein neues Thema mit dem Titel „$2“ auf $3',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Benachrichtige mich, wenn mich betreffende Aktionen in Flow stattfinden.',
	'flow-link-post' => 'Beitrag',
	'flow-link-topic' => 'Thema',
	'flow-link-history' => 'Verlauf',
	'flow-moderation-reason-placeholder' => 'Hier Begründung eingeben',
	'flow-moderation-title-suppress-post' => 'Beitrag unterdrücken?',
	'flow-moderation-title-delete-post' => 'Beitrag löschen?',
	'flow-moderation-title-hide-post' => 'Beitrag verstecken?',
	'flow-moderation-title-restore-post' => 'Beitrag wiederherstellen?',
	'flow-moderation-intro-suppress-post' => 'Bitte {{GENDER:$3|erkläre}}, warum du diesen Beitrag unterdrückst.',
	'flow-moderation-intro-delete-post' => 'Bitte {{GENDER:$3|erkläre}}, warum du diesen Beitrag löschst.',
	'flow-moderation-intro-hide-post' => 'Bitte {{GENDER:$3|erkläre}}, warum du diesen Beitrag versteckst.',
	'flow-moderation-intro-restore-post' => 'Bitte {{GENDER:$3|erkläre}}, warum du diesen Beitrag wiederherstellst.',
	'flow-moderation-confirm-suppress-post' => 'Unterdrücken',
	'flow-moderation-confirm-delete-post' => 'Löschen',
	'flow-moderation-confirm-hide-post' => 'Verstecken',
	'flow-moderation-confirm-restore-post' => 'Wiederherstellen',
	'flow-moderation-confirmation-suppress-post' => 'Der Beitrag wurde erfolgreich unterdrückt.
{{GENDER:$2|Ziehe}} in Erwägung, $1 eine Rückmeldung für diesen Beitrag zu geben.',
	'flow-moderation-confirmation-delete-post' => 'Der Beitrag wurde erfolgreich gelöscht.
{{GENDER:$2|Ziehe}} in Erwägung, $1 eine Rückmeldung für diesen Beitrag zu geben.',
	'flow-moderation-confirmation-hide-post' => 'Der Beitrag wurde erfolgreich versteckt.
{{GENDER:$2|Ziehe}} in Erwägung, $1 eine Rückmeldung für diesen Beitrag zu geben.',
	'flow-moderation-confirmation-restore-post' => 'Du hast erfolgreich den obigen Beitrag wiederhergestellt.',
	'flow-moderation-title-suppress-topic' => 'Thema unterdrücken?',
	'flow-moderation-title-delete-topic' => 'Thema löschen?',
	'flow-moderation-title-hide-topic' => 'Thema verstecken?',
	'flow-moderation-title-restore-topic' => 'Thema wiederherstellen?',
	'flow-moderation-intro-suppress-topic' => 'Bitte {{GENDER:$3|erkläre}}, warum du dieses Thema unterdrückst.',
	'flow-moderation-intro-delete-topic' => 'Bitte {{GENDER:$3|erkläre}}, warum du dieses Thema löschst.',
	'flow-moderation-intro-hide-topic' => 'Bitte {{GENDER:$3|erkläre}}, warum du dieses Thema versteckst.',
	'flow-moderation-intro-restore-topic' => 'Bitte {{GENDER:$3|erkläre}}, warum du dieses Thema wiederherstellst.',
	'flow-moderation-confirm-suppress-topic' => 'Unterdrücken',
	'flow-moderation-confirm-delete-topic' => 'Löschen',
	'flow-moderation-confirm-hide-topic' => 'Verstecken',
	'flow-moderation-confirm-restore-topic' => 'Wiederherstellen',
	'flow-moderation-confirmation-suppress-topic' => 'Das Thema wurde erfolgreich unterdrückt.
{{GENDER:$2|Ziehe}} in Erwägung, $1 eine Rückmeldung für dieses Thema zu geben.',
	'flow-moderation-confirmation-delete-topic' => 'Das Thema wurde erfolgreich gelöscht.
{{GENDER:$2|Ziehe}} in Erwägung, $1 eine Rückmeldung für dieses Thema zu geben.',
	'flow-moderation-confirmation-hide-topic' => 'Das Thema wurde erfolgreich versteckt.
{{GENDER:$2|Ziehe}} in Erwägung, $1 eine Rückmeldung für dieses Thema zu geben.',
	'flow-moderation-confirmation-restore-topic' => 'Du hast dieses Thema erfolgreich wiederhergestellt.',
	'flow-topic-permalink-warning' => 'Dieses Thema wurde gestartet auf  [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Dieses Thema wurde gestartet auf dem [$2 Board von {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Dies ist ein Permanentlink zu einer einzelnen Version dieses Beitrags.
Diese Version ist vom $1.
Du kannst die [$5 Unterschiede von der vorherigen Version] oder andere Versionen im [$4 Verlauf] ansehen.',
	'flow-revision-permalink-warning-post-first' => 'Dies ist ein Permanentlink zur ersten Version dieses Beitrags.
Du kannst spätere Versionen im [$4 Verlauf] ansehen.',
	'flow-revision-permalink-warning-header' => 'Dies ist ein Permanentlink zu einer einzelnen Version der Überschrift.
Diese Version ist von $1. Du kannst die [$3 Unterschiede von der vorherigen Version] oder andere Versionen im [$2 Verlauf des Boards] ansehen.',
	'flow-revision-permalink-warning-header-first' => 'Dies ist ein Permanentlink zur ersten Version der Überschrift.
Du kannst neuere Versionen im [$2 Verlauf des Boards] ansehen.',
	'flow-compare-revisions-revision-header' => 'Version von {{GENDER:$2|$2}} vom $1',
	'flow-compare-revisions-header-post' => 'Diese Seite zeigt die {{GENDER:$3|Änderungen}} zwischen zwei Versionen eines Beitrags von $3 im Thema „[$5 $2]“ auf [$4 $1] an.
Du kannst andere Versionen dieses Beitrags im [$6 Verlauf] ansehen.',
	'flow-compare-revisions-header-header' => 'Diese Seite zeigt die {{GENDER:$2|Änderungen}} zwischen zwei Versionen der Überschrift von [$3 $1] an.
Du kannst andere Versionen der Überschrift in ihrem [$4 Verlauf] einsehen.',
	'flow-topic-collapsed-one-line' => 'Kleine Ansicht',
	'flow-topic-collapsed-full' => 'Zusammengeklappte Ansicht',
	'flow-topic-complete' => 'Volle Ansicht',
	'flow-terms-of-use-new-topic' => 'Mit dem Klicken auf „{{int:flow-newtopic-save}}“ stimmst du unseren Nutzungsbedingungen für dieses Wiki zu.',
	'flow-terms-of-use-reply' => 'Mit dem Klicken auf „{{int:flow-reply-submit}}“ stimmst du unseren Nutzungsbedingungen für dieses Wiki zu.',
	'flow-terms-of-use-edit' => 'Mit dem Speichern deiner Änderungen stimmst du unseren Nutzungsbedingungen für dieses Wiki zu.',
);

/** Greek (Ελληνικά)
 * @author Astralnet
 * @author Evropi
 * @author Geraki
 * @author Nikosguard
 */
$messages['el'] = array(
	'flow-post-moderated-toggle-delete-show' => 'Εμφάνιση σχολίου {{GENDER:$1|διαγραφή}} $2',
	'flow-topic-actions' => 'Ενέργειες',
	'flow-preview' => 'Προεπισκόπηση',
	'flow-post-action-post-history' => 'Ιστορικό',
	'flow-post-action-edit-post' => 'Επεξεργασία',
	'flow-history-last4' => 'Τελευταίες 4 ώρες',
	'flow-history-day' => 'Σήμερα',
);

/** British English (British English)
 * @author Shirayuki
 */
$messages['en-gb'] = array(
	'flow-terms-of-use-new-topic' => 'By clicking add topic, you agree to our [//wikimediafoundation.org/wiki/Terms_of_use Terms of Use] and agree to irrevocably release your text under the [//creativecommons.org/licenses/by-sa/3.0 CC BY-SA 3.0 Licence] and [//en.wikipedia.org/wiki/Wikipedia:Text_of_the_GNU_Free_Documentation_License GFDL]',
	'flow-terms-of-use-reply' => 'By clicking reply, you agree to our [//wikimediafoundation.org/wiki/Terms_of_use Terms of Use] and agree to irrevocably release your text under the [//creativecommons.org/licenses/by-sa/3.0 CC BY-SA 3.0 Licence] and [//en.wikipedia.org/wiki/Wikipedia:Text_of_the_GNU_Free_Documentation_License GFDL]',
	'flow-terms-of-use-edit' => 'By saving changes, you agree to our [//wikimediafoundation.org/wiki/Terms_of_use Terms of Use] and agree to irrevocably release your text under the [//creativecommons.org/licenses/by-sa/3.0 CC BY-SA 3.0 Licence] and [//en.wikipedia.org/wiki/Wikipedia:Text_of_the_GNU_Free_Documentation_License GFDL]',
);

/** Spanish (español)
 * @author Benfutbol10
 * @author Carlitosag
 * @author Carlosz22
 * @author Ciencia Al Poder
 * @author Csbotero
 * @author Epicfaace
 * @author Fitoschido
 * @author Ihojose
 * @author Ovruni
 * @author Sethladan
 */
$messages['es'] = array(
	'flow-desc' => 'Sistema de gestión de flujo de trabajo',
	'log-name-flow' => 'Registro de actividad de flujo',
	'flow-user-moderated' => 'Usuario moderado',
	'flow-edit-header-link' => 'Editar cabecera',
	'flow-header-empty' => 'Esta página de discusión no tiene cabecera actualmente.',
	'flow-post-moderated-toggle-hide-show' => 'Mostrar comentarios {{GENDER:$1|hidden}} por $2',
	'flow-post-moderated-toggle-delete-show' => 'Mostrar comentario {{GENDER:$1|deleted}} por $2',
	'flow-post-moderated-toggle-suppress-show' => 'Mostrar comentario {{GENDER:$1|suppresed}} por $2',
	'flow-post-moderated-toggle-hide-hide' => 'Ocultar comentario {{GENDER:$1|hidden}} por $2',
	'flow-post-moderated-toggle-delete-hide' => 'Ocultar comentario eliminado por $2', # Fuzzy
	'flow-suppress-post-content' => '$2 ha suprimido este comentario', # Fuzzy
	'flow-suppress-usertext' => '<em>Nombre de usuario suprimido</em>',
	'flow-post-actions' => 'Acciones',
	'flow-topic-actions' => 'Acciones',
	'flow-cancel' => 'Cancelar',
	'flow-preview' => 'Previsualizar',
	'flow-show-change' => 'Mostrar cambios',
	'flow-newtopic-title-placeholder' => 'Tema nuevo',
	'flow-newtopic-content-placeholder' => 'Si quieres, añade detalles',
	'flow-newtopic-header' => 'Añadir un nuevo tema',
	'flow-newtopic-save' => 'Añadir tema',
	'flow-newtopic-start-placeholder' => 'Iniciar un tema nuevo',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Comentario}} en «$2»',
	'flow-reply-placeholder' => 'Responder a $1',
	'flow-reply-submit' => '{{GENDER:$1|Responder}}',
	'flow-reply-link' => '{{GENDER:$1|Responder}}',
	'flow-thank-link' => '{{GENDER:$1|Agradecer}}',
	'flow-post-edited' => 'Mensaje {{GENDER:$1|editado}} por $1 $2',
	'flow-post-action-view' => 'Enlace permanente',
	'flow-post-action-post-history' => 'Historial',
	'flow-post-action-suppress-post' => 'Censurar mensaje',
	'flow-post-action-delete-post' => 'Eliminar',
	'flow-post-action-hide-post' => 'Ocultar',
	'flow-post-action-edit-post' => 'Editar',
	'flow-post-action-restore-post' => 'Restaurar mensaje',
	'flow-topic-action-view' => 'Enlace permanente',
	'flow-topic-action-watchlist' => 'Lista de seguimiento',
	'flow-topic-action-edit-title' => 'Editar título',
	'flow-topic-action-history' => 'Historial',
	'flow-topic-action-hide-topic' => 'Ocultar el tema',
	'flow-topic-action-delete-topic' => 'Eliminar el tema',
	'flow-topic-action-suppress-topic' => 'Suprimir el tema',
	'flow-topic-action-restore-topic' => 'Restaurar el tema',
	'flow-error-http' => 'Ha ocurrido un error mientras se contactaba al servidor.',
	'flow-error-other' => 'Ha ocurrido un error inesperado.',
	'flow-error-external' => 'Se ha producido un error.<br />El mensaje de error recibido es: $1',
	'flow-error-edit-restricted' => 'No tienes permitido editar esta entrada.',
	'flow-error-external-multi' => 'Se han encontrado errores.<br />$1',
	'flow-error-missing-content' => 'La entrada no tiene contenido. Para guardarla necesitas añadir contenido.',
	'flow-error-missing-title' => 'El tema no tiene título. Para guardarlo necesitas añadirle un título.',
	'flow-error-delete-failure' => 'Falló la eliminación de este elemento.',
	'flow-error-hide-failure' => 'Falló el ocultamiento de este elemento.',
	'flow-error-restore-failure' => 'Falló la restauración de este elemento.',
	'flow-error-default' => 'Se ha producido un error.',
	'flow-error-invalid-title' => 'Se proporcionó un título de página no válido.',
	'flow-edit-header-submit' => 'Guardar cabecera',
	'flow-edit-header-submit-overwrite' => 'Sobrescribir encabezado',
	'flow-edit-title-submit' => 'Cambiar el título',
	'flow-edit-title-submit-overwrite' => 'Sobrescribir título',
	'flow-edit-post-submit' => 'Enviar cambios',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|editó}} un [$3 comentario].', # Fuzzy
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|borró}} un [$4 comentario] (<em>$5</em>).', # Fuzzy
	'flow-board-history' => 'Historial de «$1»',
	'flow-topic-history' => 'Historial del tema «$1»',
	'flow-history-last4' => 'Últimas 4 horas',
	'flow-history-day' => 'Hoy',
	'flow-history-week' => 'Semana pasada',
	'flow-history-pages-post' => 'Aparece en [$1 $2]',
	'flow-comment-restored' => 'Comentario restaurado',
	'flow-comment-deleted' => 'Comentario eliminado',
	'flow-comment-hidden' => 'Comentario oculto',
	'flow-comment-moderated' => 'Comentario moderado',
	'flow-paging-rev' => 'Más temas recientes',
	'flow-paging-fwd' => 'Temas anteriores',
	'flow-last-modified' => 'Última modificación hace $1',
	'flow-notification-reply' => '$1 respondió a tu [$5 publicación] de $2 en «$4».', # Fuzzy
	'flow-notification-link-text-view-post' => 'Ver la entrada',
	'flow-notification-link-text-view-topic' => 'Ver el tema',
	'flow-link-topic' => 'tema',
	'flow-link-history' => 'historial',
	'flow-moderation-reason-placeholder' => 'Ingresa tu razón aquí',
	'flow-moderation-title-suppress-post' => '¿Quieres suprimir la entrada?',
	'flow-moderation-title-delete-post' => '¿Quieres eliminar la entrada?',
	'flow-moderation-title-hide-post' => '¿Quieres ocultar la entrada?',
	'flow-moderation-title-restore-post' => '¿Quieres restaurar la entrada?',
	'flow-moderation-intro-suppress-post' => 'Por favor, {{GENDER:$3|explica}} por qué vas a suprimir esta publicación.',
	'flow-moderation-intro-delete-post' => 'Por favor, {{GENDER:$3|explica}} por qué vas a eliminar esta publicación.',
	'flow-moderation-intro-hide-post' => 'Por favor, {{GENDER:$3|explica}} por qué vas a ocultar esta publicación.',
	'flow-moderation-intro-restore-post' => 'Por favor, {{GENDER:$3|explica}} por qué vas a restaurar esta publicación.',
	'flow-moderation-confirm-suppress-post' => 'Suprimir',
	'flow-moderation-confirm-delete-post' => 'Eliminar',
	'flow-moderation-confirm-hide-post' => 'Ocultar',
	'flow-moderation-confirm-restore-post' => 'Restaurar',
	'flow-moderation-confirmation-suppress-post' => 'La entrada fue suprimida con éxito.
{{GENDER:$2|Considera}} entregar un comentario $1 sobre esta entrada.',
	'flow-moderation-confirmation-delete-post' => 'La entrada fue eliminada con éxito.
{{GENDER:$2|Considera}} entregar un comentario $1 sobre esta entrada.',
	'flow-moderation-confirmation-hide-post' => 'La entrada fue ocultada con éxito.
{{GENDER:$2|Considera}} entregar un comentario $1 sobre esta entrada.',
	'flow-moderation-confirmation-restore-post' => 'Has restaurado la publicación anterior con éxito.',
	'flow-moderation-title-suppress-topic' => '¿Quieres suprimir el tema?',
	'flow-moderation-title-delete-topic' => '¿Quieres eliminar el tema?',
	'flow-moderation-title-hide-topic' => '¿Quieres ocultar el tema?',
	'flow-moderation-title-restore-topic' => '¿Quieres restaurar el tema?',
	'flow-moderation-intro-suppress-topic' => 'Por favor, {{GENDER:$3|explica}} por qué vas a suprimir este tema.',
	'flow-moderation-intro-delete-topic' => 'Por favor, {{GENDER:$3|explica}} por qué vas a eliminar este tema.',
	'flow-moderation-intro-hide-topic' => 'Por favor, {{GENDER:$3|explica}} por qué vas a ocultar este tema.',
	'flow-moderation-intro-restore-topic' => 'Explica por qué quieres restaurar el tema.', # Fuzzy
	'flow-moderation-confirm-suppress-topic' => 'Suprimir',
	'flow-moderation-confirm-delete-topic' => 'Eliminar',
	'flow-moderation-confirm-hide-topic' => 'Ocultar',
	'flow-moderation-confirm-restore-topic' => 'Restaurar',
	'flow-moderation-confirmation-suppress-topic' => 'El tópico fue eliminado con éxito.
{{GENDER:$2|Considera}} entregar un comentario $1 sobre esta entrada.',
	'flow-moderation-confirmation-delete-topic' => 'El tópico fue eliminado con éxito.
{{GENDER:$2|Considera}} entregar un comentario $1 sobre esta entrada.',
	'flow-moderation-confirmation-hide-topic' => 'El tópico fue ocultado con éxito.
{{GENDER:$2|Considera}} entregar un comentario $1 sobre esta entrada.',
	'flow-moderation-confirmation-restore-topic' => 'Has restaurado este tema correctamente.',
);

/** Persian (فارسی)
 * @author Amire80
 * @author Armin1392
 * @author Ebraminio
 * @author Omidh
 * @author Reza1615
 */
$messages['fa'] = array(
	'flow-desc' => 'سامانهٔ مدیریت گردش کار',
	'flow-talk-taken-over' => 'این صفحهٔ گفتگو توسط یک [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow board] تصاحب شده‌است.',
	'log-name-flow' => 'جریان داشتن فعالیت سیاهه',
	'logentry-delete-flow-delete-post' => '$1 یک [$4 پست] را در [[$3]] {{GENDER:$2|حذف کرد}}',
	'logentry-delete-flow-restore-post' => '$1 یک [$4 ارسال] را در [[$3]] {{GENDER:$2|بازیابی کرد}}',
	'logentry-suppress-flow-suppress-post' => '$1 یک [$4 پست] را در [[$3]] {{GENDER:$2|سرکوب شده}}',
	'logentry-suppress-flow-restore-post' => '$1 یک [$4 پست] را در [[$3]] {{GENDER:$2|حذف کرد}}',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|حذف شده}} یک [$4 tموضوع] در [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|بازگردانده شده}} یک [$4 موضوع] در [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|سرکوب شده}} یک [$4 topic] در [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|حذف شده}} یک [$4 topic] در [[$3]]',
	'flow-user-moderated' => 'کاربر کنترل شده',
	'flow-edit-header-link' => 'ویرایش سرفصل',
	'flow-header-empty' => 'این صفحهٔ گفتگو در حال حاضر هیچ سرفصلی ندارد.',
	'flow-post-moderated-toggle-hide-show' => 'نمایش نظر {{GENDER:$1|پنهان شده}} توسط $2',
	'flow-post-moderated-toggle-delete-show' => 'نمایش نظر {{GENDER:$1|حذف شده}} توسط $2',
	'flow-post-moderated-toggle-suppress-show' => 'نمایش نظر {{GENDER:$1|سرکوب شده}} توسط $2',
	'flow-post-moderated-toggle-hide-hide' => 'پنهان کردن نظر {{GENDER:$1|پنهان شده}} توسط $2',
	'flow-post-moderated-toggle-delete-hide' => 'پنهان کردن نظر {{GENDER:$1|حذف شده}} توسط $2',
	'flow-post-moderated-toggle-suppress-hide' => 'پنهان کردن نظر {{GENDER:$1|سرکوب شده}} توسط $2',
	'flow-hide-post-content' => 'این نظر توسط $2 ، {{GENDER:$1|hidden}} بود',
	'flow-hide-title-content' => 'این موضوع توسط $2، {{GENDER:$1|hidden}} بود',
	'flow-hide-header-content' => '{{GENDER:$1|Hidden}}  توسط $2',
	'flow-delete-post-content' => 'این نظر توسط $2، {{GENDER:$1|deleted}} بود',
	'flow-delete-title-content' => 'این موضوع توسط $2، {{GENDER:$1|deleted}} بود',
	'flow-delete-header-content' => '{{GENDER:$1|Deleted}} توسط $2',
	'flow-suppress-post-content' => 'این نظر توسط $2، {{GENDER:$1|suppressed}} بود',
	'flow-suppress-title-content' => 'این موضوع توسط $2، {{GENDER:$1|suppressed}} بود',
	'flow-suppress-header-content' => '{{GENDER:$1|Suppressed}} توسط $2',
	'flow-suppress-usertext' => '<em>نام کاربری سرکوب شده</em>',
	'flow-post-actions' => 'اقدامات',
	'flow-topic-actions' => 'اقدامات',
	'flow-cancel' => 'لغو',
	'flow-preview' => 'پیش‌نمایش',
	'flow-show-change' => 'نمایش تغییرات',
	'flow-last-modified-by' => 'آخرین {{GENDER:$1|modified}} توسط $1',
	'flow-stub-post-content' => "''به دلیل یک خطای فنی، این پست نتوانست بازیابی شود.''",
	'flow-newtopic-title-placeholder' => 'موضوع جدید',
	'flow-newtopic-content-placeholder' => 'اگر دوست دارید بعضی از جزئیات را وارد کنید',
	'flow-newtopic-header' => 'اضافه کردن یک موضوع جدید',
	'flow-newtopic-save' => 'اضافه‌کردن موضوع',
	'flow-newtopic-start-placeholder' => 'شروع یک موضوع جدید',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|نظر}} در "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|پاسخ}} به $1',
	'flow-reply-submit' => '{{GENDER:$1|پاسخ}}',
	'flow-reply-link' => '{{GENDER:$1|پاسخ}}',
	'flow-thank-link' => '{{GENDER:$1|تشکر}}',
	'flow-post-edited' => 'پست {{GENDER:$1|ویرایش شد}} توسط $1 $2',
	'flow-post-action-view' => 'پیوند پایدار',
	'flow-post-action-post-history' => 'تاریخچه',
	'flow-post-action-suppress-post' => 'سرکوب',
	'flow-post-action-delete-post' => 'حذف',
	'flow-post-action-hide-post' => 'نهفتن',
	'flow-post-action-edit-post' => 'ویرایش',
	'flow-post-action-restore-post' => 'بازگرداندن ارسال',
	'flow-topic-action-view' => 'پیوند پایدار',
	'flow-topic-action-watchlist' => 'فهرست پی‌گیری‌ها',
	'flow-topic-action-edit-title' => 'ویرایش عنوان',
	'flow-topic-action-history' => 'تاریخچه',
	'flow-topic-action-hide-topic' => 'پنهان کردن موضوع',
	'flow-topic-action-delete-topic' => 'حذف موضوع',
	'flow-topic-action-suppress-topic' => 'سرکوب موضوع',
	'flow-topic-action-restore-topic' => 'بازگرداندن موضوع',
	'flow-error-http' => 'یک خطا هنگام تماس با سرور رخ داد.',
	'flow-error-other' => 'یک خطای غیرمنتظره رخ داد.',
	'flow-error-external' => 'خطایی رخ داده. <br /> پیغام خطای دریافت شده: $1 بود',
	'flow-error-edit-restricted' => 'شما مجاز به ویرایش این پست نیستید.',
	'flow-error-external-multi' => 'خطاهایی رخ داده‌اند. <br />$1',
	'flow-error-missing-content' => 'پست هیچ محتوایی ندارد. محتوا نیازمند به ذخیرهٔ یک پست است.',
	'flow-error-missing-title' => 'موضوع هیچ عنوانی ندارد. عنوان نیازمند به ذخیرهٔ یک موضوع است.',
	'flow-error-parsoid-failure' => 'به علت یک پارسوئید ناموفق، قادر به تجزیهٔ محتوا نیست.',
	'flow-error-missing-replyto' => 'هیچ "پاسخی به" پارامتر عرضه نشد. این پارامتر نیازمند عمل "پاسخ" است.',
	'flow-error-invalid-replyto' => '«پاسخ» پارامتر نامعتبر بود. پست تعیین‌شده نتوانست پیدا شود.',
	'flow-error-delete-failure' => 'حذف کردن این مورد ناموفق بود.',
	'flow-error-hide-failure' => 'پنهان کردن این مورد ناموفق بود.',
	'flow-error-missing-postId' => 'هیچ "شناسهٔ پستی" پارامتری عرضه نشد. این پارامتر نیازمند به کنترل یک پست است.',
	'flow-error-invalid-postId' => '"شناسهٔ پستی" پارامتر نامعتبر بود. پست تعیین شدهٔ ($1) نتوانست پیدا شود.',
	'flow-error-restore-failure' => 'بازگردانی این مورد ناموفق بود.',
	'flow-error-invalid-moderation-state' => 'یک ارزش نامعتبر برای وضعیت کنترل، ارائه شد.',
	'flow-error-invalid-moderation-reason' => 'لطفاً یک دلیل برای کنترل ارائه دهید.',
	'flow-error-not-allowed' => 'مجوزهای ناکافی برای اجرای این عمل.',
	'flow-error-title-too-long' => 'عناوین موضوع، محدود به $1 {{PLURAL:$1|byte|bytes}} هستند.',
	'flow-error-no-existing-workflow' => 'این جریان کار هنوز وجود ندارد.',
	'flow-error-not-a-post' => 'عنوان موضوع نمی‌تواند به عنوان یک پست ذخیره شود.',
	'flow-error-missing-header-content' => 'سرفصل هیچ محتوایی ندارد. محتوا نیازمند به ذخیزهٔ یک سرفصل است.',
	'flow-error-missing-prev-revision-identifier' => 'معرف بررسی قبلی از گم شده‌است.',
	'flow-error-prev-revision-mismatch' => 'چند ثانیه پیش کاربر دیگری این پست را ویرایش کرده‌است. آیا مطمئن هستید که می‌خواهید تغییر اخیر را بازنویسی کنید؟',
	'flow-error-prev-revision-does-not-exist' => 'بررسی قبلی نتوانست پیدا شود.',
	'flow-error-default' => 'یک خطا رخ داده است.',
	'flow-error-invalid-input' => 'ارزش نامعتبر برای بارگذاری جریان محتوا، ارائه شده.',
	'flow-error-invalid-title' => 'عنوان صفحهٔ نامعتبر ارائه شده.',
	'flow-error-fail-load-history' => 'عدم موفقیت بارگذاری محتوای سابقه.',
	'flow-error-missing-revision' => 'بررسی برای بارگذاری محتوای جریان، نتوانست پیدا شود.',
	'flow-error-fail-commit' => 'عدم موفقیت ذخیرهٔ محتوای جریان.',
	'flow-error-insufficient-permission' => 'مجوز ناکافی برای دسترسی به محتوا.',
	'flow-error-revision-comparison' => 'عملکرد متفاوت برای دو بررسی متعلق به پست مشابه، می‌تواند به تنهایی انجام شده باشد.',
	'flow-error-missing-topic-title' => 'عنوان موضوع برای جریان کار کنونی، نتوانست پیدا شود.',
	'flow-error-fail-load-data' => 'عدم موفقیت در بارگذاری اطلاعات درخواست شده.',
	'flow-error-invalid-workflow' => 'جریان کار درخواست شده نتوانست پیدا شود.',
	'flow-error-process-data' => 'خطایی هنگام پردازش اطلاعات در درخواست شما رخ داده‌است.',
	'flow-error-process-wikitext' => 'خطایی هنگام پردازش تبدیل اچ‌تی‌‌ام‌ال/متن‌ویکی رخ داده‌است.',
	'flow-error-no-index' => 'عدم موفقیت در پیدا کردن یک شاخص برای انجام جستجوی اطلاعات.',
	'flow-edit-header-submit' => 'ذخیرهٔ سرفصل',
	'flow-edit-header-submit-overwrite' => 'بازنویسی سرصفحه',
	'flow-edit-title-submit' => 'تغییر عنوان',
	'flow-edit-title-submit-overwrite' => 'بازنویسی عنوان',
	'flow-edit-post-submit' => 'ثبت تغییرات',
	'flow-edit-post-submit-overwrite' => 'بازنویسی تغییرات',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|ویرایش شد}} یک [$3 نظر] در $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|نطر داده}}] در $4 (<em>$5</em>).',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|نظر|نظرها}}</strong> {{PLURAL:$1|بود|بودند}} اضافه شد.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|ایجاد شد}} موضوع [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|تغییر یافت}} عنوان موضوع از $5 به [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|ایجاد شده}} سرفصل صفحه.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|ویرایش شده}} سرفصل صفحه.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|پنهان}} یک [$4 نظر] در $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|حذف شده}} یک [$4 نظر] در $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|سرکوب شده}} یک [$4 نظر] در $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|بازگردانده شده}} یک [$4 نظر] در $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|پنهان شد}} [$4 موضوع] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|حذف شده}} [$4 موضوع] $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|سرکوب شده}} [$4 موضوع] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|بازگردانده شده}} [$4 موضوع] $6 (<em>$5</em>).',
	'flow-board-history' => 'تاریخ "$1"',
	'flow-topic-history' => ' تاریخچهٔ موضوع "$1"',
	'flow-post-history' => '"نظر توسط {{GENDER:$2|$2}}" تاریخچهٔ پست',
	'flow-history-last4' => '4 ساعت گذشته',
	'flow-history-day' => 'امروز',
	'flow-history-week' => 'هفتهٔ گذشته',
	'flow-history-pages-topic' => 'بر روی [$1 "$2" صفحه] به نظر رسیدن',
	'flow-history-pages-post' => 'بر روی [$1 $2] به نظر رسیدن',
	'flow-topic-participants' => '{{PLURAL:$1|$3 این موضوع شروع شده|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} و $2 {{PLURAL:$2|دیگر|دیگران}}|0=هنوز هیچ مشارکتی نیست|2={{GENDER:$3|$3}} و {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} و {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|نظر $1 |نظرها  $1 |0={{GENDER:$2|اولین}} شخصی باشید که نظر می‌گذارد!}}',
	'flow-comment-restored' => 'بازگرداندن نظر',
	'flow-comment-deleted' => 'نظر حذف شده',
	'flow-comment-hidden' => 'پنهان کردن نظر',
	'flow-comment-moderated' => 'کنترل نظر',
	'flow-paging-rev' => 'موضوعات اخیر بیشتر',
	'flow-paging-fwd' => 'موضوعات قدیمی‌تر',
	'flow-last-modified' => 'آخرین تغییریافته دربارهٔ $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|پاسخ داده شد}} به شما <span class="plainlinks">[$5 post]</span> در "$2" در "$4".',
	'flow-notification-reply-bundle' => '$1 و $5 {{PLURAL:$6|دیگر|دیگران}} {{GENDER:$1|ویرایش شده}} یک <span class="plainlinks">[$4 post]</span> در "$2" در "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|ویرایش شده}} یک <span class="plainlinks">[$5 post]</span> در "$2" در [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 و $5 {{PLURAL:$6|دیگر|دیگران}} {{GENDER:$1|ویرایش شده}} یک <span class="plainlinks">[$4 post]</span> در "$2" در "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|ایجاد شده}} یک <span class="plainlinks">[$5 موضوع جدید]</span> در [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|تغیر یافته}} به عنوان <span class="plainlinks">[$2 $3]</span> به "$4" در [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|ذکر شده}} شما در {{GENDER:$1|او|او|آنها}} <span class="plainlinks">[$2 post]</span> در "$3" در "$4".',
	'flow-notification-link-text-view-post' => 'نمایش ارسال',
	'flow-notification-link-text-view-board' => 'مشاهدهٔ صفحه',
	'flow-notification-link-text-view-topic' => 'مشاهدهٔ موضوع',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|پاسخ داده}} به پست شما',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|پاسخ داده شده}} به پست شما در $2 در "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 و $4 {{PLURAL:$5|دیگر|دیگران}} {{GENDER:$1|پاسخ داده}} به پست شما  در "$2" در "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|ذکر شد}} شما در $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|ذکر شد}} شما در {{GENDER:$1|او|او|آنها}} پست در "$2" بر "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|ویرایش شد}} یک پست',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|ویرایش شده}} یک پست در $2 در "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 و $4 {{PLURAL:$5|دیگر|دیگران}} {{GENDER:$1|ویرایش شده}} یک پست در $2 در "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|تغییر نام}}موضوع شما',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|تغییر نام داد}} موضوع شما "$2" بر "$3" بر "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|ایجاد شده}} یک موضوع جدید در $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|ایجاد شد}} یک موضوع جدید با عنوان "$2" بر $3',
	'echo-category-title-flow-discussion' => 'جریان',
	'echo-pref-tooltip-flow-discussion' => 'هنگامی که عملیات مربوط به من رخ می‌دهد، من را در جریان قرار بده.',
	'flow-link-post' => 'ارسال',
	'flow-link-topic' => 'موضوع',
	'flow-link-history' => 'تاریخچه',
	'flow-moderation-reason-placeholder' => 'دلیل خود را اینجا وارد کنید',
	'flow-moderation-title-suppress-post' => 'سرکوب ارسال؟',
	'flow-moderation-title-delete-post' => 'حذف ارسال؟',
	'flow-moderation-title-hide-post' => 'پنهان‌کردن پست؟',
	'flow-moderation-title-restore-post' => 'بازگرداندن ارسال؟',
	'flow-moderation-intro-suppress-post' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا شما این پست را سرکوب می‌کنید.',
	'flow-moderation-intro-delete-post' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا این پست را حذف می‌کنید.',
	'flow-moderation-intro-hide-post' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا شما این پست را پنهان می‌کنید.',
	'flow-moderation-intro-restore-post' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا شما این پست را بازمی‌گردانید.',
	'flow-moderation-confirm-suppress-post' => 'سرکوب',
	'flow-moderation-confirm-delete-post' => 'حذف',
	'flow-moderation-confirm-hide-post' => 'نهفتن',
	'flow-moderation-confirm-restore-post' => 'بازیابی',
	'flow-moderation-confirmation-suppress-post' => 'پست با موفقیت سرکوب شده‌بود.
{{GENDER:$2|در نظر بگیرید}} واکنش دادن $1 را در این پست.',
	'flow-moderation-confirmation-delete-post' => 'پست با موفقیت حذف شده‌بود.
{{GENDER:$2|در نظر بگیرید}} واکنش دادن $1 را در این پست.',
	'flow-moderation-confirmation-hide-post' => 'پست با موفقیت پنهان شده‌بود.
{{GENDER:$2|در نظر بگیرید}} واکنش دادن $1 را در این پست.',
	'flow-moderation-confirmation-restore-post' => 'شما پست بالا را با موفقیت بازگردانده‌اید.',
	'flow-moderation-title-suppress-topic' => 'موضوع سرکوب؟',
	'flow-moderation-title-delete-topic' => 'موضوع حذف؟',
	'flow-moderation-title-hide-topic' => 'موضوع پنهان؟',
	'flow-moderation-title-restore-topic' => 'موضوع بازگردانی؟',
	'flow-moderation-intro-suppress-topic' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا شما این موضوع را سرکوب می‌کنید.',
	'flow-moderation-intro-delete-topic' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا شما این موضوع را سرکوب می‌کنید.',
	'flow-moderation-intro-hide-topic' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا شما این موضوع را پنهان می‌کنید.',
	'flow-moderation-intro-restore-topic' => 'لطفاً {{GENDER:$3|توضیح دهید}} که چرا شما این موضوع را بازگردانی می‌کنید.',
	'flow-moderation-confirm-suppress-topic' => 'سرکوب',
	'flow-moderation-confirm-delete-topic' => 'حذف',
	'flow-moderation-confirm-hide-topic' => 'نهفتن',
	'flow-moderation-confirm-restore-topic' => 'بازیابی',
	'flow-moderation-confirmation-suppress-topic' => 'موضوع با موفقیت سرکوب شده‌بود.
{{GENDER:$2|در نظر بگیرید}} واکنش دادن $1 را در این موضوع.',
	'flow-moderation-confirmation-delete-topic' => 'موضوع با موفقیت حذف شده‌بود.
{{GENDER:$2|در نظر بگیرید}} واکنش دادن $1 را در این موضوع.',
	'flow-moderation-confirmation-hide-topic' => 'موضوع با موفقیت پنهان شده‌بود.
{{GENDER:$2|در نظر بگیرید}} واکنش دادن $1 را در این موضوع.',
	'flow-moderation-confirmation-restore-topic' => 'شما این موضوع را با موفقیت بازگردانده‌اید.',
	'flow-topic-permalink-warning' => 'این موضوع در [$2 $1] شروع شده‌ بود',
	'flow-topic-permalink-warning-user-board' => "این موضوع در [$2 {{GENDER:$1|$1}}'sصفحهٔ] شروع شده بود",
	'flow-revision-permalink-warning-post' => 'این یک لینک دائم برای یک تک نسخه از این پست است.
این نسخه از $1 است.
شما می توانید [$5 تفاوت‌ها از نسخهٔ قبلی] را مشاهده کنید، یا نسخه‌های دیگری را در [$4 صفحهٔ تاریخچهٔ پست] مشاهده کنید.',
	'flow-revision-permalink-warning-post-first' => 'این یک لینک دائم برای اولین نسخهٔ این پست است.
شما می‌توانید نسخه‌های بعدی را در [$4 صفحهٔ تاریخچهٔ پست] مشاهده کنید.',
	'flow-revision-permalink-warning-header' => 'این یک لینک دائمی برای یک نسخهٔ تک سرفصل است.
این نسخه از $1 است. شما می‌توانید [$3 تفاوت‌ها را از نسخهٔ قبلی] مشاهده کنید, یا نسخه های دیگر را در [$2 تابلو  صفحهٔ تاریخچه] مشاهده کنید.',
	'flow-revision-permalink-warning-header-first' => 'این یک لینک دائمی برای یک نسخهٔ تک سرفصل است.
شما می‌توانید نسخه‌های بعدی را در [$2 تابلو  صفحهٔ تاریخچه] مشاهده کنید.',
	'flow-compare-revisions-revision-header' => 'نسخه توسط {{GENDER:$2|$2}} از $1',
	'flow-compare-revisions-header-post' => 'این صفحه {{GENDER:$3|تغییرات}} را بین دو نسخه از یک پست توسط $3 در موضوع "[$5 $2]" بر  [$4 $1] نمایش می‌دهد.
شما می‌توانید نسخه‌های دیگری از این پست را در [$6 صفحهٔ تاریخچه] مشاهده کنید.',
	'flow-compare-revisions-header-header' => 'این صفحه {{GENDER:$2|تغییرات}} بین دو نسخهٔ سرفصل را در [$3 $1] نشان می‌دهد.
شما می‌توانید نسخه‌های دیگر سرفصل را در این [$4 صفحهٔ تاریخچه] مشاهده کنید.',
	'flow-topic-collapsed-one-line' => 'مشاهدهٔ کوچک',
	'flow-topic-collapsed-full' => 'مشاهده با شکست روبه‌رو شده',
	'flow-topic-complete' => 'مشاهدهٔ کامل',
	'flow-terms-of-use-new-topic' => 'با کلیک کردن "{{int:flow-newtopic-save}}"، شما با شرایط استفاده برای این ویکی موافقت می‌کنید.',
	'flow-terms-of-use-reply' => 'با کلیک کردن "{{int:flow-reply-submit}}"، شما با شرایط استفاده برای این ویکی موافقت می‌کنید.',
	'flow-terms-of-use-edit' => 'با ذخیرهٔ تغییرات شما،  شما با شرایط استفاده برای این ویکی موافقت می‌کنید.',
);

/** Finnish (suomi)
 * @author Elseweyr
 * @author Nike
 * @author Stryn
 */
$messages['fi'] = array(
	'flow-talk-taken-over' => 'Tällä keskustelusivulla on otettu käyttöön [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow].',
	'log-name-flow' => 'Flow-tapahtumaloki',
	'flow-edit-header-link' => 'Muokkaa otsikkoa',
	'flow-header-empty' => 'Tällä keskustelusivulla ei ole tällä hetkellä otsikkoa.',
	'flow-hide-post-content' => '$2 on {{GENDER:$1|piilottanut}} tämän kommentin.',
	'flow-hide-title-content' => '$2 on {{GENDER:$1|piilottanut}} tämän aiheen.',
	'flow-hide-header-content' => '{{GENDER:$1|Piilottanut}} $2',
	'flow-delete-header-content' => '{{GENDER:$1|Poistanut}} $2',
	'flow-suppress-header-content' => 'käyttäjän $2 {{GENDER:$1|häivyttämä}}',
	'flow-suppress-usertext' => '<em>Käyttäjänimi häivytetty</em>',
	'flow-post-actions' => 'Toiminnot',
	'flow-topic-actions' => 'Toiminnot',
	'flow-cancel' => 'Peru',
	'flow-preview' => 'Esikatselu',
	'flow-show-change' => 'Näytä muutokset',
	'flow-newtopic-title-placeholder' => 'Uusi aihe',
	'flow-newtopic-content-placeholder' => 'Lisää jotain yksityiskohtia, jos haluat',
	'flow-newtopic-header' => 'Lisää uusi aihe',
	'flow-newtopic-save' => 'Lisää aihe',
	'flow-newtopic-start-placeholder' => 'Aloita uusi aihe',
	'flow-reply-placeholder' => '{{GENDER:$1|Vastaa}} käyttäjälle $1',
	'flow-reply-submit' => '{{GENDER:$1|Vastaa}}',
	'flow-reply-link' => '{{GENDER:$1|Vastaa}}',
	'flow-thank-link' => '{{GENDER:$1|Kiitä}}',
	'flow-post-action-view' => 'Ikilinkki',
	'flow-post-action-post-history' => 'Historia',
	'flow-post-action-suppress-post' => 'Häivytä',
	'flow-post-action-delete-post' => 'Poista',
	'flow-post-action-hide-post' => 'Piilota',
	'flow-post-action-edit-post' => 'Muokkaa',
	'flow-post-action-restore-post' => 'Palauta viesti',
	'flow-topic-action-view' => 'Ikilinkki',
	'flow-topic-action-watchlist' => 'Tarkkailulista',
	'flow-topic-action-edit-title' => 'Muokkaa otsikkoa',
	'flow-topic-action-history' => 'Historia',
	'flow-topic-action-hide-topic' => 'Piilota aihe',
	'flow-topic-action-delete-topic' => 'Poista aihe',
	'flow-topic-action-suppress-topic' => 'Häivytä aihe',
	'flow-topic-action-restore-topic' => 'Palauta aihe',
	'flow-error-http' => 'Virhe muodostettaessa yhteyttä palvelimeen.',
	'flow-error-other' => 'Tuntematon virhe tapahtui.',
	'flow-error-external' => 'On tapahtunut virhe.<br />Vastaanotettu virheilmoitus: $1',
	'flow-error-edit-restricted' => 'Sinulla ei ole lupaa muokata tätä viestiä.',
	'flow-error-not-allowed' => 'Käyttöoikeutesi eivät riitä tämän toiminnon suorittamiseen',
	'flow-edit-title-submit' => 'Muuta otsikkoa',
	'flow-edit-post-submit' => 'Lähetä muutokset',
	'flow-history-last4' => 'Viimeiset 4 tuntia',
	'flow-history-day' => 'Tänään',
	'flow-history-week' => 'Viimeinen viikko',
	'flow-comment-restored' => 'Palautettu kommentti',
	'flow-comment-deleted' => 'Poistettu kommentti',
	'flow-comment-hidden' => 'Piilotettu kommentti',
	'flow-comment-moderated' => 'Moderoitu kommentti',
	'flow-paging-fwd' => 'Vanhemmat aiheet',
	'flow-notification-link-text-view-post' => 'Näytä viesti',
	'flow-notification-link-text-view-topic' => 'Näytä aihe',
	'flow-link-post' => 'viesti',
	'flow-link-topic' => 'aihe',
	'flow-link-history' => 'historia',
	'flow-moderation-reason-placeholder' => 'Kirjoita syy tähän',
	'flow-moderation-title-suppress-post' => 'Viestin sensurointi',
	'flow-moderation-title-delete-post' => 'Viestin poisto',
	'flow-moderation-title-hide-post' => 'Viestin piilotus',
	'flow-moderation-title-restore-post' => 'Viestin palauttaminen',
	'flow-moderation-confirm-suppress-post' => 'Häivytä',
	'flow-moderation-confirm-delete-post' => 'Poista',
	'flow-moderation-confirm-hide-post' => 'Piilota',
	'flow-moderation-confirm-restore-post' => 'Palauta',
	'flow-moderation-confirmation-restore-post' => 'Olet onnistuneesti palauttanut yllä olevan viestin.',
	'flow-moderation-title-suppress-topic' => 'Häivytä aihe?',
	'flow-moderation-title-delete-topic' => 'Poista aihe?',
	'flow-moderation-title-hide-topic' => 'Piilota aihe?',
	'flow-moderation-title-restore-topic' => 'Palauta aihe?',
	'flow-moderation-confirm-suppress-topic' => 'Häivytä',
	'flow-moderation-confirm-delete-topic' => 'Poista',
	'flow-moderation-confirm-hide-topic' => 'Piilota',
	'flow-moderation-confirm-restore-topic' => 'Palauta',
	'flow-topic-collapsed-one-line' => 'Pieni näkymä',
	'flow-topic-collapsed-full' => 'Suurennettu näkymä',
	'flow-topic-complete' => 'Koko näkymä',
);

/** French (français)
 * @author Ayack
 * @author Ebe123
 * @author Gomoko
 * @author Jean-Frédéric
 * @author Linedwell
 * @author Ltrlg
 * @author Sherbrooke
 * @author VIGNERON
 * @author Verdy p
 */
$messages['fr'] = array(
	'flow-desc' => 'Système de gestion du flux de travail',
	'flow-talk-taken-over' => "Cette page de discussion a été remplacée par un [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal ''Flow board''].",
	'log-name-flow' => 'Journal de flux d’activité',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|a supprimé}} une [$4 note] sur [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|a rétabli}} une [$4 note] sur [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|a supprimé}} une [$4 note] sur [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|a supprimé}} une [$4 note] sur [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|a supprimé}} un [$4 sujet] sur [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|a rétabli}} un [$4 sujet] sur [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|a supprimé}} un [$4 sujet] sur [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|a supprimé}} un [$4 sujet] sur [[$3]]',
	'flow-user-moderated' => 'Utilisateur modéré',
	'flow-edit-header-link' => 'Modifier l’entête',
	'flow-header-empty' => 'Cette page de discussion n’a pas d’entête pour l’instant.',
	'flow-post-moderated-toggle-hide-show' => 'Afficher le commentaire {{GENDER:$1|masqué}} par $2',
	'flow-post-moderated-toggle-delete-show' => 'Afficher le commentaire {{GENDER:$1|supprimé}} par $2',
	'flow-post-moderated-toggle-suppress-show' => 'Afficher le commentaire {{GENDER:$1|supprimé}} par $2',
	'flow-post-moderated-toggle-hide-hide' => 'Masquer le commentaire {{GENDER:$1|masqué}} par $2',
	'flow-post-moderated-toggle-delete-hide' => 'Masquer le commentaire {{GENDER:$1|supprimé}} par $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Masquer le commentaire {{GENDER:$1|supprimé}} par $2',
	'flow-hide-post-content' => 'Ce commentaire a été {{GENDER:$1|masqué}} par $2',
	'flow-hide-title-content' => 'Le sujet a été {{GENDER:$1|masqué}} par $2',
	'flow-hide-header-content' => '{{GENDER:$1|Masqué}} par $2',
	'flow-delete-post-content' => 'Ce commentaire a été {{GENDER:$1|supprimé}} par $2',
	'flow-delete-title-content' => 'Le sujet a été {{GENDER:$1|supprimé}} par $2',
	'flow-delete-header-content' => '{{GENDER:$1|Supprimé}} par $2',
	'flow-suppress-post-content' => 'Ce commentaire a été {{GENDER:$1|supprimé}} par $2',
	'flow-suppress-title-content' => 'Le sujet a été {{GENDER:$1|supprimé}} par $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Supprimé}} par $2',
	'flow-suppress-usertext' => '<em>Nom d’utilisateur supprimé</em>',
	'flow-post-actions' => 'Actions',
	'flow-topic-actions' => 'Actions',
	'flow-cancel' => 'Annuler',
	'flow-preview' => 'Prévisualiser',
	'flow-show-change' => 'Voir les modifications',
	'flow-last-modified-by' => '{{GENDER:$1|Modifié}} en dernier par $1',
	'flow-stub-post-content' => '« En raison d’une erreur technique, ce message n’a pas pu être récupéré. »',
	'flow-newtopic-title-placeholder' => 'Nouveau sujet',
	'flow-newtopic-content-placeholder' => 'Ajouter des détails si vous le voulez',
	'flow-newtopic-header' => 'Ajouter un nouveau sujet',
	'flow-newtopic-save' => 'Ajouter sujet',
	'flow-newtopic-start-placeholder' => 'Commencer un nouveau sujet',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Commenter}} « $2 »',
	'flow-reply-placeholder' => '{{GENDER:$1|Répondre}} à $1',
	'flow-reply-submit' => '{{GENDER:$1|Répondre}}',
	'flow-reply-link' => '{{GENDER:$1|Répondre}}',
	'flow-thank-link' => '{{GENDER:$1|Remercier}}',
	'flow-post-interaction-separator' => '&nbsp;•&#32;',
	'flow-post-edited' => 'Note {{GENDER:$1|modifiée}} par $1 $2',
	'flow-post-action-view' => 'Lien permanent',
	'flow-post-action-post-history' => 'Historique',
	'flow-post-action-suppress-post' => 'Supprimer',
	'flow-post-action-delete-post' => 'Supprimer',
	'flow-post-action-hide-post' => 'Masquer',
	'flow-post-action-edit-post' => 'Modifier',
	'flow-post-action-restore-post' => 'Restaurer le message',
	'flow-topic-action-view' => 'Lien permanent',
	'flow-topic-action-watchlist' => 'Liste de surveillance',
	'flow-topic-action-edit-title' => 'Modifier le titre',
	'flow-topic-action-history' => 'Historique',
	'flow-topic-action-hide-topic' => 'Masquer le sujet',
	'flow-topic-action-delete-topic' => 'Supprimer le sujet',
	'flow-topic-action-suppress-topic' => 'Supprimer le sujet',
	'flow-topic-action-restore-topic' => 'Rétablir le sujet',
	'flow-error-http' => 'Une erreur s’est produite en communiquant avec le serveur.',
	'flow-error-other' => 'Une erreur inattendue s’est produite.',
	'flow-error-external' => 'Une erreur s’est produite.<br />Le message d’erreur reçu était : $1',
	'flow-error-edit-restricted' => 'Vous n’êtes pas autorisé à modifier cette note',
	'flow-error-external-multi' => 'Des erreurs se sont produites.<br />$1',
	'flow-error-missing-content' => 'Le message n’a aucun contenu. Un contenu est obligatoire pour enregistrer un message.',
	'flow-error-missing-title' => 'Le sujet n’a pas de titre. Un titre est obligatoire pour enregistrer un sujet.',
	'flow-error-parsoid-failure' => "Impossible d'analyser le contenu en raison d'une panne de Parsoid.",
	'flow-error-missing-replyto' => 'Aucun paramètre « replyTo » n’a été fourni. Ce paramètre est requis pour l’action « répondre ».',
	'flow-error-invalid-replyto' => 'Le paramètre « replyTo » n’était pas valide. Le message spécifié n’a pas pu être trouvé.',
	'flow-error-delete-failure' => 'Échec de la suppression de cette entrée.',
	'flow-error-hide-failure' => 'Le masquage de cet élément a échoué.',
	'flow-error-missing-postId' => 'Aucun paramètre « postId » n’a été fourni. Ce paramètre est obligatoire pour manipuler un message.',
	'flow-error-invalid-postId' => 'Le paramètre « postId » n’était pas valide. Le message spécifié ($1) n’a pas pu être trouvé.',
	'flow-error-restore-failure' => 'Échec de la restauration de cette entrée.',
	'flow-error-invalid-moderation-state' => 'Une valeur non valide a été fournie pour moderationState',
	'flow-error-invalid-moderation-reason' => 'Veuillez indiquer un motif de la modération',
	'flow-error-not-allowed' => 'Droits insuffisants pour exécuter cette action',
	'flow-error-title-too-long' => 'Les titres des sujets sont limités à $1 {{PLURAL:$1|octet|octets}}.',
	'flow-error-no-existing-workflow' => 'Ce flux de travail n’existe pas encore.',
	'flow-error-not-a-post' => 'Le titre du sujet ne peut pas être enregistré comme un message.',
	'flow-error-missing-header-content' => 'L’entête n’a pas de contenu. Un contenu est obligatoire pour enregistrer un entête.',
	'flow-error-missing-prev-revision-identifier' => 'L’identifiant de révision précédente est absent.',
	'flow-error-prev-revision-mismatch' => 'Un autre utilisateur vient de modifier cette note il y a quelques secondes. Êtes-vous sûr de vouloir écraser cette modification récente ?',
	'flow-error-prev-revision-does-not-exist' => 'Impossible de trouver la révision précédente.',
	'flow-error-default' => 'Une erreur s’est produite.',
	'flow-error-invalid-input' => 'Une valeur non valide a été fournie lors du chargement du contenu du flux.',
	'flow-error-invalid-title' => 'Un titre de page non valide a été fourni.',
	'flow-error-fail-load-history' => 'Échec au chargement du contenu de l’historique.',
	'flow-error-missing-revision' => 'Impossible de trouver une révision pour charger le contenu du flux.',
	'flow-error-fail-commit' => 'Échec à l’enregistrement du contenu du flux.',
	'flow-error-insufficient-permission' => 'Permission insuffisante pour accéder au contenu.',
	'flow-error-revision-comparison' => 'Une opération diff ne peut être faite que pour deux révisions appartenant à la même publication.',
	'flow-error-missing-topic-title' => 'Impossible de trouver le titre du sujet pour le flux de travail actuel.',
	'flow-error-fail-load-data' => 'Échec au chargement des données demandées.',
	'flow-error-invalid-workflow' => 'Impossible de trouver le flux de travail demandé.',
	'flow-error-process-data' => 'Une erreur s’est produite lors du traitement des données dans votre demande.',
	'flow-error-process-wikitext' => 'Une erreur s’est produite lors du traitement de la conversion HTML/wikitexte.',
	'flow-error-no-index' => 'Impossible de trouver un index pour effectuer la recherche de données.',
	'flow-edit-header-submit' => 'Enregistrer l’entête',
	'flow-edit-header-submit-overwrite' => 'Écraser l’entête',
	'flow-edit-title-submit' => 'Changer le titre',
	'flow-edit-title-submit-overwrite' => 'Écraser le titre',
	'flow-edit-post-submit' => 'Soumettre les modifications',
	'flow-edit-post-submit-overwrite' => 'Écraser les modifications',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|a modifié}} un [$3 commentaire] sur $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|a ajouté}} un commentaire] sur $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|commentaire|commentaires}}</strong> {{PLURAL:$1|a été ajouté|ont été ajoutés}}.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|a créé}} le sujet [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|a changé}} le titre du sujet de [$3 $4], précédemment $5.',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|a créé}} l’entête.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|a modifié}} l’entête.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|a masqué}} un [$4 commentaire] sur $6 (<em>$5</em>)..',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|a supprimé}} un [$4 commentaire] sur $6 (<em>$5</em>)..',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|a effacé}} un [$4 commentaire] sur $6 (<em>$5</em>)..',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|a rétabli}} un [$4 commentaire] sur $6 (<em>$5</em>)..',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|a masqué}} le [$4 sujet] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|a supprimé}} le [$4 sujet] $6(<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|a supprimé}} le [$4 sujet] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|a rétabli}} le [$4 sujet] $6 (<em>$5</em>).',
	'flow-board-history' => 'Historique de « $1 »',
	'flow-topic-history' => 'Historique du sujet « $1 »',
	'flow-post-history' => 'Commentaire par {{GENDER:$2|$2}} Historique de la note',
	'flow-history-last4' => 'Dernières 4 heures',
	'flow-history-day' => 'Aujourd’hui',
	'flow-history-week' => 'Semaine dernière',
	'flow-history-pages-topic' => 'Apparaît sur [$1 le tableau « $2 »]',
	'flow-history-pages-post' => 'Apparaît sur [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 a démarré ce sujet|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} et {{PLURAL:$2|autre|autres}}|0=Encore aucune participation|2={{GENDER:$3|$3}} et {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} et {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 commentaire|$1 commentaires|0={{GENDER:$2|Soyez le premier|Soyez la première}} à laisser un commentaire !}}',
	'flow-comment-restored' => 'Commentaire rétabli',
	'flow-comment-deleted' => 'Commentaire supprimé',
	'flow-comment-hidden' => 'Commentaire masqué',
	'flow-comment-moderated' => 'Commentaire soumis à modération',
	'flow-paging-rev' => 'Sujets les plus récents',
	'flow-paging-fwd' => 'Sujets plus anciens',
	'flow-last-modified' => 'Dernière modification $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|a répondu}} à votre <span class="plainlinks">[$5 note]</span> sur « $2 » en « $4 ».',
	'flow-notification-reply-bundle' => '$1 et $5 {{PLURAL:$6|autre|autres}} {{GENDER:$1|ont répondu}} à votre <span class="plainlinks">[$4 note]</span> concernant « $2 » sur « $3 ».',
	'flow-notification-edit' => '$1 {{GENDER:$1|a modifié}} une <span class="plainlinks">[$5 note]</span> sur « $2 » en [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 et $5 {{PLURAL:$6|autre|autres}} {{GENDER:$1|ont modifié}} une <span class="plainlinks">[$4 note]</span> sur « $2 » en « $3 ».',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|a créé}} un <span class="plainlinks">[$5 nouveau sujet]</span> en [[$2|$3]] : $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|a modifié}} le titre de <span class="plainlinks">[$2 $3]</span> en « $4 » sur [[$5|$6]].',
	'flow-notification-mention' => '$1 vous {{GENDER:$1|a mentionné|a mentionné|ont mentionné}} dans {{GENDER:$1|sa|sa|leur}} <span class="plainlinks">[$2 note]</span> sur « $3 » en « $4 »',
	'flow-notification-link-text-view-post' => 'Afficher la note',
	'flow-notification-link-text-view-board' => 'Afficher le tableau',
	'flow-notification-link-text-view-topic' => 'Afficher le sujet',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|a répondu}} à votre note',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|a répondu}} a votre note concernant « $2 » sur « $3 »',
	'flow-notification-reply-email-batch-bundle-body' => '$1 et $4 {{PLURAL:$5|autre|autres}} {{GENDER:$1|ont répondu}} à votre note concernant « $2 » sur « $3 »',
	'flow-notification-mention-email-subject' => '$1 vous {{GENDER:$1|a mentionné}} en « $2 »',
	'flow-notification-mention-email-batch-body' => '$1 vous {{GENDER:$1|a mentionné}} dans {{GENDER:$1|sa}} note sur « $2 » en « $3 »',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|a modifié}} une note',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|a modifié}} une note sur « $2 » en « $3 »',
	'flow-notification-edit-email-batch-bundle-body' => '$1 et $4 {{PLURAL:$5|autre|autres}} {{GENDER:$1|ont modifié}} une note sur « $2 » en « $3 »',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|a renommé}} votre sujet',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|a renommé}} votre sujet « $2 » en « $3 » sur « $4 »',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|a créé}} un nouveau sujet sur « $2 »',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|a créé}} un nouveau sujet avec le titre « $2 » en $3',
	'echo-category-title-flow-discussion' => 'Flux',
	'echo-pref-tooltip-flow-discussion' => 'M’informer quand des actions me concernant ont lieu dans le flux.',
	'flow-link-post' => 'note',
	'flow-link-topic' => 'sujet',
	'flow-link-history' => 'historique',
	'flow-moderation-reason-placeholder' => 'Saisissez votre motif ici',
	'flow-moderation-title-suppress-post' => 'Censurer la note ?',
	'flow-moderation-title-delete-post' => 'Supprimer la note ?',
	'flow-moderation-title-hide-post' => 'Masquer la note ?',
	'flow-moderation-title-restore-post' => 'Restaurer la note ?',
	'flow-moderation-intro-suppress-post' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous censurez cette note.',
	'flow-moderation-intro-delete-post' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous supprimez cette note.',
	'flow-moderation-intro-hide-post' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous cachez cette note.',
	'flow-moderation-intro-restore-post' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous restaurez cette note.',
	'flow-moderation-confirm-suppress-post' => 'Supprimer',
	'flow-moderation-confirm-delete-post' => 'Supprimer',
	'flow-moderation-confirm-hide-post' => 'Masquer',
	'flow-moderation-confirm-restore-post' => 'Rétablir',
	'flow-moderation-confirmation-suppress-post' => 'Cette note à été supprimée avec succès.
{{GENDER:$2|Pensez}} à donner à $1 un avis sur cette note.',
	'flow-moderation-confirmation-delete-post' => 'Cette note a bien été supprimée.
{{GENDER:$2|Pensez}} à donner à $1 un avis sur cette note.',
	'flow-moderation-confirmation-hide-post' => 'Cette note a bien été masquée.
{{GENDER:$2|Pensez}} à donner à $1 un avis sur cette note.',
	'flow-moderation-confirmation-restore-post' => 'Vous avez bien restauré la note ci-dessus.',
	'flow-moderation-title-suppress-topic' => 'Supprimer le sujet ?',
	'flow-moderation-title-delete-topic' => 'Supprimer le sujet ?',
	'flow-moderation-title-hide-topic' => 'Masquer le sujet ?',
	'flow-moderation-title-restore-topic' => 'Rétablir le sujet ?',
	'flow-moderation-intro-suppress-topic' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous supprimez ce sujet.',
	'flow-moderation-intro-delete-topic' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous supprimez ce sujet.',
	'flow-moderation-intro-hide-topic' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous masquez ce sujet.',
	'flow-moderation-intro-restore-topic' => 'Veuillez {{GENDER:$3|expliquer}} pourquoi vous rétablissez ce sujet.',
	'flow-moderation-confirm-suppress-topic' => 'Supprimer',
	'flow-moderation-confirm-delete-topic' => 'Supprimer',
	'flow-moderation-confirm-hide-topic' => 'Masquer',
	'flow-moderation-confirm-restore-topic' => 'Rétablir',
	'flow-moderation-confirmation-suppress-topic' => 'Le sujet a bien été supprimé.
{{GENDER:$2|Pensez}} à donner à $1 un avis sur ce sujet.',
	'flow-moderation-confirmation-delete-topic' => 'Le sujet a bien été supprimé.
{{GENDER:$2|Pensez}} à donner à $1 un avis sur ce sujet.',
	'flow-moderation-confirmation-hide-topic' => 'Le sujet a bien été masqué.
{{GENDER:$2|Pensez}} à donner à $1 un avis sur ce sujet.',
	'flow-moderation-confirmation-restore-topic' => 'Vous avez bien rétabli ce sujet.',
	'flow-topic-permalink-warning' => 'Ce sujet a été démarré sur [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Ce sujet a été démarré sur le tableau de [$2 {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Voici un lien permanent vers une version unique de cette note.
Cette version date de $1.
Vous pouvez voir les [$5 différences depuis la version précédente], ou afficher d’autres versions sur la [$4 page d’historique de la note].',
	'flow-revision-permalink-warning-post-first' => 'Voici un lien permanent vers la première version de cette note.
Vous pouvez afficher des versions ultérieures depuis la [$4 page d’historique de la note].',
	'flow-revision-permalink-warning-header' => 'Voici un lien permanent vers une version unique de l’entête.
Cette version date de $1. Vous pouvez voir les [$3 différences avec la version précédente], ou afficher les autres versions sur la [$2 page du tableau historique].',
	'flow-revision-permalink-warning-header-first' => 'Voici un lien permanent vers la première version de l’entête.
Vous pouvez afficher les versions ultérieures sur la [$2 page du tableau historique].',
	'flow-compare-revisions-revision-header' => 'Version par {{GENDER:$2|$2}} du $1',
	'flow-compare-revisions-header-post' => 'Cette page affiche les {{GENDER:$3|modifications}} entre deux versions d’une note par $3 dans le sujet « [$5 $2] » sur [$4 $1].
Vous pouvez voir d’autres versions de cette note dans sa [$6 page d’historique].',
	'flow-compare-revisions-header-header' => 'Cette page affiche les {{GENDER:$2|modifications}} entre deux versions de l’entête sur [$3 $1].
Vous pouvez voir les autres versions de l’entête sur sa [$4 page d’historique].',
	'flow-topic-collapsed-one-line' => 'Vue petite',
	'flow-topic-collapsed-full' => 'Vue réduite',
	'flow-topic-complete' => 'Vue complète',
	'flow-terms-of-use-new-topic' => 'En cliquant sur « {{int:flow-newtopic-save}} », vous acceptez les conditions d’utilisation de ce wiki.',
	'flow-terms-of-use-reply' => 'En cliquant sur « {{int:flow-reply-submit}} », vous acceptez les conditions d’utilisation de ce wiki.',
	'flow-terms-of-use-edit' => 'En enregistrant vos modifications, vous acceptez les conditions d’utilisation de ce wiki.',
);

/** Western Frisian (Frysk)
 * @author Kening Aldgilles
 */
$messages['fy'] = array(
	'flow-cancel' => 'Ofbrekke',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'flow-desc' => 'Sistema de xestión do fluxo de traballo',
	'flow-edit-header-link' => 'Editar a cabeceira',
	'flow-header-empty' => 'Actualmente, esta páxina de conversa non ten cabeceira.',
	'flow-post-actions' => 'Accións',
	'flow-topic-actions' => 'Accións',
	'flow-cancel' => 'Cancelar',
	'flow-newtopic-title-placeholder' => 'Novo fío',
	'flow-newtopic-content-placeholder' => 'Engada algún detalle, se quere',
	'flow-newtopic-header' => 'Engadir un novo fío',
	'flow-newtopic-save' => 'Nova sección',
	'flow-newtopic-start-placeholder' => 'Iniciar un novo fío',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Comentario}} en "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|Responder}} a $1',
	'flow-reply-submit' => '{{GENDER:$1|Responder}}',
	'flow-reply-link' => '{{GENDER:$1|Responder}}',
	'flow-thank-link' => '{{GENDER:$1|Agradecer}}',
	'flow-post-edited' => 'Mensaxe {{GENDER:$1|editada}} por $1 $2',
	'flow-post-action-view' => 'Ligazón permanente',
	'flow-post-action-post-history' => 'Historial',
	'flow-post-action-suppress-post' => 'Suprimir',
	'flow-post-action-delete-post' => 'Borrar',
	'flow-post-action-hide-post' => 'Agochar',
	'flow-post-action-edit-post' => 'Editar',
	'flow-post-action-restore-post' => 'Restaurar a mensaxe',
	'flow-topic-action-view' => 'Ligazón permanente',
	'flow-topic-action-watchlist' => 'Lista de vixilancia',
	'flow-topic-action-edit-title' => 'Editar o título',
	'flow-topic-action-history' => 'Historial',
	'flow-error-http' => 'Produciuse un erro ao contactar co servidor.',
	'flow-error-other' => 'Produciuse un erro inesperado.',
	'flow-error-external' => 'Produciuse un erro.<br />A mensaxe de erro recibida foi: $1',
	'flow-error-edit-restricted' => 'Non lle está permitido editar esta mensaxe.',
	'flow-error-external-multi' => 'Producíronse varios erros.<br />$1',
	'flow-error-missing-content' => 'A mensaxe non ten contido. O contido é obrigatorio para gardar unha mensaxe.',
	'flow-error-missing-title' => 'O fío non ten título. O título é obrigatorio para gardar un fío.',
	'flow-error-parsoid-failure' => 'Non é posible analizar o contido debido a un fallo do Parsoid.',
	'flow-error-missing-replyto' => 'Non se achegou ningún parámetro de resposta. Este parámetro é obrigatorio para a acción "responder".',
	'flow-error-invalid-replyto' => 'O parámetro de resposta non é válido. Non se puido atopar a mensaxe especificada.',
	'flow-error-delete-failure' => 'Houbo un erro ao borrar este elemento.',
	'flow-error-hide-failure' => 'Houbo un erro ao agochar este elemento.',
	'flow-error-missing-postId' => 'Non se achegou ningún parámetro de identificación. Este parámetro é obrigatorio para a manipular unha mensaxe.',
	'flow-error-invalid-postId' => 'O parámetro de identificación non é válido. Non se puido atopar a mensaxe especificada ($1).',
	'flow-error-restore-failure' => 'Houbo un erro ao restaurar este elemento.',
	'flow-edit-header-submit' => 'Gardar a cabeceira',
	'flow-edit-title-submit' => 'Cambiar o título',
	'flow-edit-post-submit' => 'Enviar os cambios',
	'flow-rev-message-edit-post' => 'Editouse o contido da mensaxe', # Fuzzy
	'flow-rev-message-reply' => 'Publicouse unha nova resposta', # Fuzzy
	'flow-rev-message-new-post' => 'Creouse un fío', # Fuzzy
	'flow-rev-message-edit-title' => 'Editouse o título do fío', # Fuzzy
	'flow-rev-message-create-header' => 'Creouse a cabeceira', # Fuzzy
	'flow-rev-message-edit-header' => 'Editouse a cabeceira', # Fuzzy
	'flow-rev-message-hid-post' => 'Agochouse a mensaxe', # Fuzzy
	'flow-rev-message-deleted-post' => 'Borrouse a mensaxe', # Fuzzy
	'flow-rev-message-suppressed-post' => 'Censurouse a mensaxe', # Fuzzy
	'flow-rev-message-restored-post' => 'Descubriuse a mensaxe', # Fuzzy
	'flow-topic-history' => 'Historial do fío "$1"',
	'flow-comment-restored' => 'Comentario restaurado',
	'flow-comment-deleted' => 'Comentario borrado',
	'flow-comment-hidden' => 'Comentario agochado',
	'flow-comment-moderated' => 'Comentario moderado',
	'flow-paging-rev' => 'Fíos máis recentes',
	'flow-paging-fwd' => 'Fíos máis vellos',
	'flow-last-modified' => 'Última modificación $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|respondeu}} á súa [$5 mensaxe] de "$2" en "$4".', # Fuzzy
	'flow-notification-reply-bundle' => '$1 e {{PLURAL:$6|outra persoa|outras $5 persoas}} {{GENDER:$1|responderon}} á súa [$4 mensaxe] de "$2" en "$3".', # Fuzzy
	'flow-notification-edit' => '$1 {{GENDER:$1|editou}} a [$5 mensaxe] de "$2" en "[[$3|$4]]".', # Fuzzy
	'flow-notification-edit-bundle' => '$1 e {{PLURAL:$6|outra persoa|outras $5 persoas}} {{GENDER:$1|responderon}} á [$4 mensaxe] de "$2" en "$3".', # Fuzzy
	'flow-notification-newtopic' => '$1 {{GENDER:$1|creou}} un [$5 novo fío] en "[[$2|$3]]": "$4".', # Fuzzy
	'flow-notification-rename' => '$1 {{GENDER:$1|cambiou}} o título de "[$2 $3]" a "$4" en "[[$5|$6]]".', # Fuzzy
	'flow-notification-mention' => '$1 {{GENDER:$1|fíxolle unha mención}} na {{GENDER:$1|súa}} [$2 mensaxe] de "$3" en "$4".', # Fuzzy
	'flow-notification-link-text-view-post' => 'Ver a mensaxe',
	'flow-notification-link-text-view-board' => 'Ver o taboleiro',
	'flow-notification-link-text-view-topic' => 'Ver o fío',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|respondeu}} á súa mensaxe',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|respondeu}} á súa mensaxe de "$2" en "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e {{PLURAL:$5|outra persoa|outras $4 persoas}} {{GENDER:$1|responderon}} á súa mensaxe de "$2" en "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|fíxolle unha mención}} en "$2"',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|fíxolle unha mención}} na {{GENDER:$1|súa}} mensaxe de "$2" en "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|editou}} unha mensaxe',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|editou}} unha mensaxe de "$2" en "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 e {{PLURAL:$5|outra persoa|outras $4 persoas}} {{GENDER:$1|editaron}} unha mensaxe de "$2" en "$3".',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|renomeou}} o seu fío',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|renomeou}} o seu fío "$2" a "$3" en "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|creou}} un novo fío en "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|creou}} un novo fío co título "$2" en "$3"',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Conversa|Conversas}}', # Fuzzy
	'echo-pref-tooltip-flow-discussion' => 'Notificádeme cando sucedan accións relacionadas comigo no taboleiro de conversas.', # Fuzzy
	'flow-link-post' => 'mensaxe',
	'flow-link-topic' => 'fío',
	'flow-link-history' => 'historial',
	'flow-moderation-confirm-suppress-post' => 'Suprimir',
	'flow-moderation-confirm-delete-post' => 'Borrar',
	'flow-moderation-confirm-hide-post' => 'Agochar',
	'flow-moderation-confirm-restore-post' => 'Restaurar',
	'flow-moderation-confirm-suppress-topic' => 'Suprimir',
	'flow-moderation-confirm-delete-topic' => 'Borrar',
	'flow-moderation-confirm-hide-topic' => 'Agochar',
	'flow-moderation-confirm-restore-topic' => 'Restaurar',
	'flow-terms-of-use-new-topic' => 'Ao premer no botón "{{int:flow-newtopic-save}}", acepta os termos de uso deste wiki.',
	'flow-terms-of-use-reply' => 'Ao premer no botón "{{int:flow-reply-submit}}", acepta os termos de uso deste wiki.',
	'flow-terms-of-use-edit' => 'Ao gardar os seus cambios, acepta os termos de uso deste wiki.',
);

/** Gujarati (ગુજરાતી)
 * @author KartikMistry
 */
$messages['gu'] = array(
	'flow-preview' => 'પૂર્વદર્શન',
	'flow-notification-link-text-view-topic' => 'વિષય જુઓ',
);

/** Hebrew (עברית)
 * @author Amire80
 * @author Guycn2
 * @author Lokal Profil
 * @author Orsa
 */
$messages['he'] = array(
	'flow-desc' => 'מערכת לניהול זרימת עבודה',
	'flow-talk-taken-over' => 'דף השיחה הוחלף על־ידי [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal לוח זרימה].',
	'log-name-flow' => 'יומן פעילות זרימה',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 רשומה] בדף [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|שחזר|שחזרה}} [$4 רשומה] בדף [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|העלים|העלימה}} [$4 רשומה] בדף [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 רשומה] בדף [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 נושא] בדף [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|שחזר|שחזרה}} [$4 נושא] בדף [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|העלים|העלימה}} [$4 נושא] בדף [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 נושא] בדף [[$3]]',
	'flow-user-moderated' => 'משתמש מפוקח',
	'flow-edit-header-link' => 'עריכת התיאור',
	'flow-header-empty' => 'לדף השיחה הזה אין כרגע תיאור.',
	'flow-post-moderated-toggle-hide-show' => 'הצגת ההערה ש{{GRAMMAR:תחילית|$2}} {{GENDER:$1|הסתיר|הסתירה}}',
	'flow-post-moderated-toggle-delete-show' => 'הצגת ההערה ש{{GRAMMAR:תחילית|$2}} {{GENDER:$1|מחק|מחקה}}',
	'flow-post-moderated-toggle-suppress-show' => 'הצגת ההערה ש{{GRAMMAR:תחילית|$2}} {{GENDER:$1|העלים|העלימה}}',
	'flow-post-moderated-toggle-hide-hide' => 'הסתרת ההערה ש{{GRAMMAR:תחילית|$2}} {{GENDER:$1|הסתיר|הסתירה}}',
	'flow-post-moderated-toggle-delete-hide' => 'הסתרת ההערה ש{{GRAMMAR:תחילית|$2}} {{GENDER:$1|מחק|מחקה}}',
	'flow-post-moderated-toggle-suppress-hide' => 'הסתרת ההערה ש{{GRAMMAR:תחילית|$2}} {{GENDER:$1|העלים|העלימה}}',
	'flow-hide-post-content' => '$2 {{GENDER:$1|הסתיר|הסתירה}} את התגובה הזאת',
	'flow-hide-title-content' => '$2 {{GENDER:$1|הסתיר|הסתירה}} את הנושא הזה',
	'flow-hide-header-content' => '$2 {{GENDER:$1|הסתיר|הסתירה}} את זה',
	'flow-delete-post-content' => '$2 {{GENDER:$1|מחק|מחקה}} את התגובה הזאת',
	'flow-delete-title-content' => '$2 {{GENDER:$1|מחק|מחקה}} את הנושא הזה',
	'flow-delete-header-content' => '$2 {{GENDER:$1|מחק|מחקה}} את זה',
	'flow-suppress-post-content' => '$2 {{GENDER:$1|העלים|העלימה}} את התגובה הזאת',
	'flow-suppress-title-content' => '$2 {{GENDER:$1|העלים|העלימה}} את הנושא הזה',
	'flow-suppress-header-content' => '$2 {{GENDER:$1|העלים|העלימה}} את זה',
	'flow-suppress-usertext' => '<strong>השם הועלם</strong>',
	'flow-post-actions' => 'פעולות',
	'flow-topic-actions' => 'פעולות',
	'flow-cancel' => 'ביטול',
	'flow-preview' => 'תצוגה מקדימה',
	'flow-show-change' => 'הצגת שינויים',
	'flow-last-modified-by' => 'שוּנה לאחרונה על־ידי $1',
	'flow-stub-post-content' => "'''בשל בעיה טכנית, לא ניתן לאחזר את הרשומה הזאת.'''",
	'flow-newtopic-title-placeholder' => 'כותרת חדשה',
	'flow-newtopic-content-placeholder' => 'אפשר להוסיף כאן פרטים אם בא לך',
	'flow-newtopic-header' => 'הוספת נושא חדש',
	'flow-newtopic-save' => 'הוספת נושא',
	'flow-newtopic-start-placeholder' => 'התחלת נושא חדש',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|הגב|הגיבי|להגיב}} על "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|השב|השיבי|להשיב}} ל{{GRAMMAR:תחילית|$1}}',
	'flow-reply-submit' => '{{GENDER:$1|להשיב}}',
	'flow-reply-link' => '{{GENDER:$1|השב|השיבי|להשיב}}',
	'flow-thank-link' => '{{GENDER:$1|תודה}}',
	'flow-post-edited' => '$1 {{GENDER:$1|ערך|ערכה}} את הרשומה $2',
	'flow-post-action-view' => 'קישור קבוע',
	'flow-post-action-post-history' => 'היסטוריה',
	'flow-post-action-suppress-post' => 'להעלים',
	'flow-post-action-delete-post' => 'למחוק',
	'flow-post-action-hide-post' => 'להסתיר',
	'flow-post-action-edit-post' => 'עריכה',
	'flow-post-action-restore-post' => 'לשחזר את הרשומה',
	'flow-topic-action-view' => 'קישור קבוע',
	'flow-topic-action-watchlist' => 'רשימת מעקב',
	'flow-topic-action-edit-title' => 'עריכת כותרת',
	'flow-topic-action-history' => 'היסטוריה',
	'flow-topic-action-hide-topic' => 'להסתיר נושא',
	'flow-topic-action-delete-topic' => 'למחוק נושא',
	'flow-topic-action-suppress-topic' => 'להעלים נושא',
	'flow-topic-action-restore-topic' => 'לשחזר נושא',
	'flow-error-http' => 'אירעה שגיאה בעת יצירת קשר עם השרת.',
	'flow-error-other' => 'אירעה שגיאה בלתי־צפויה.',
	'flow-error-external' => 'אירעה שגיאה.<br />התקבלה הודעת השגיאה הבאה: $1',
	'flow-error-edit-restricted' => 'אין לך הרשאה לערוך את הרשומה הזאת.',
	'flow-error-external-multi' => 'אירעו שגיאות.<br />
$1',
	'flow-error-missing-content' => 'ברשומה אין תוכן. דרוש תוכן כדי לשמור רשומה',
	'flow-error-missing-title' => 'לנושא אין כותרת. דרושה כותרת כדי לשמור נושא.',
	'flow-error-parsoid-failure' => 'לא ניתן לפענח את התוכן עקב כשל בפרסואיד.',
	'flow-error-missing-replyto' => 'לא נשלח פרמטר "replyTo". הפרמטר הזה דרוש לפעולת "reply".',
	'flow-error-invalid-replyto' => 'פרמטר "replyTo" שנשלח היה בלתי־תקין. לא נמצאה הרשומה שצוינה.',
	'flow-error-delete-failure' => 'מחיקת הפריט הזה נכשלה.',
	'flow-error-hide-failure' => 'הסתרת הפריט הזה נכשלה.',
	'flow-error-missing-postId' => 'לא ניתן פרמטר "postId". הפרמטר הזה דרוש כדי לשנות רשומה.',
	'flow-error-invalid-postId' => 'פרמטר "postId" שנשלח היה בלתי־תקין. הרשומה שצוינה ($1) לא נמצאה.',
	'flow-error-restore-failure' => 'שחזור הפריט הזה נכשל.',
	'flow-error-invalid-moderation-state' => 'ערך בלתי־תקין ניתן לפרמטר moderationState',
	'flow-error-invalid-moderation-reason' => 'נא לתת סיבה להחלת הפיקוח',
	'flow-error-not-allowed' => 'אין הרשאות מספיקות לביצוע הפעולה הזאת.',
	'flow-error-title-too-long' => 'כותרות של נושאים מוגבלות {{PLURAL:$1|לבית אחד|ל־$1 בתים}}',
	'flow-error-no-existing-workflow' => 'הזרימה הזאת עוד לא קיימת.',
	'flow-error-not-a-post' => 'לא ניתן לשמור כותרת נושא בתור רשומה.',
	'flow-error-missing-header-content' => 'בתיאור אין תוכן. התוכן נחוץ לשם שמירת תיאור.',
	'flow-error-missing-prev-revision-identifier' => 'חסר מזהה גרסה קודמת.',
	'flow-error-prev-revision-mismatch' => 'משתמש אחר ערך את הרשומה הזרת לפני שניות אחדות. האם ברצונך לדרוס את את השינוי האחרון?',
	'flow-error-prev-revision-does-not-exist' => 'לא נמצאה גרסה קודמת.',
	'flow-error-default' => 'אירעה שגיאה.',
	'flow-error-invalid-input' => 'ערך בלתי־תקין ניתן ניתן לטעינת תוכן זרימה.',
	'flow-error-invalid-title' => 'ניתנה כותרת דף בלתי־תקינה.',
	'flow-error-fail-load-history' => 'טעינת תוכן ההיסטוריה נכשלה.',
	'flow-error-missing-revision' => 'לא נמצאה גרסה שממנה ייטען תוכן הזרימה.',
	'flow-error-fail-commit' => 'שמירת תוכן הזרימה נכשלה.',
	'flow-error-insufficient-permission' => 'אין הרשאות מספיקות בכדי לגשת לתוכן.',
	'flow-error-revision-comparison' => 'פעולת השוואה יכולה להיעשות רק בין שתי גרסאות של אותה רשומה.',
	'flow-error-missing-topic-title' => 'לא נמצאה כותרת נושא עבור הזרימה הנוכחית.',
	'flow-error-fail-load-data' => 'טעינת הנתונים המובקשים נכשלה.',
	'flow-error-invalid-workflow' => 'הזרימה המובקשת לא נמצאה.',
	'flow-error-process-data' => 'אירעה שגיאה בעת עיבוד הנתונים בבקשה שלך.',
	'flow-error-process-wikitext' => 'אירעה שגיאה בעת עיבוד המרה בין HTML לקוד ויקי.',
	'flow-error-no-index' => 'מציאת מפתח לביצוע חיפוש נתונים נכשלה.',
	'flow-edit-header-submit' => 'שמירת התיאור',
	'flow-edit-header-submit-overwrite' => 'דריסת התיאור',
	'flow-edit-title-submit' => 'שינוי כותרת',
	'flow-edit-title-submit-overwrite' => 'דריסת הכותרת',
	'flow-edit-post-submit' => 'שליחת שינויים',
	'flow-edit-post-submit-overwrite' => 'דריסת השינויים',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|ערך|ערכה}} [$3 תגובה] לנושא $4.',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|הוסיף|הוסיפה}} [$3 תגובה] לנושא $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '{{PLURAL:$1|נוספה <strong>תגובה אחת</strong>|נוספו <strong>$1 תגובות</strong>}}',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|יצר|יצרה}} את הנושא [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|שינה|שינתה}} את כותרת הנושא מ{{GRAMMAR:תחילית|$5}} אל [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|יצר|יצרה}} את התיאור.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|ערך|ערכה}} את התיאור.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|הסתיר|הסתירה}} [$4 תגובה] בנושא $6‏ (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 תגובה] בנושא $6‏ (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|העלים|העלימה}} [$4 תגובה] בנושא $6‏ (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|שחזר|שחזרה}} [$4 תגובה] בנושא $6‏ (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|הסתיר|הסתירה}} את [$4 הנושא] $6‏ (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|מחק|מחקה}} את [$4 הנושא] $6‏ (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|העלים|העלימה}} את [$4 הנושא] $6‏ (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|שחזר|שחזרה}} את [$4 הנושא] $6‏ (<em>$5</em>).',
	'flow-board-history' => 'ההיסטוריה של "$1"',
	'flow-topic-history' => 'היסטוריית הנושא "$1"',
	'flow-post-history' => 'ההיסטוריה של "תגובה מאת $2"',
	'flow-history-last4' => '4 השעות האחרונות',
	'flow-history-day' => 'היום',
	'flow-history-week' => 'בשבוע שעבר',
	'flow-history-pages-topic' => 'מופיע ב[$1 לוח "$2"]',
	'flow-history-pages-post' => 'מופיע ב[$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 {{GENDER:$3|התחיל|התחילה}} את הנושא הזה|$3, $4, $5 ועוד {{PLURAL:$2|אדם אחד|$2 אנשים}}|0=אין עדיין השתתפות|2=$3 ו{{GRAMMAR:תחילית|$4}}|3=$3, $4 ו{{GRAMMAR:תחילית|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|תגובה אחת|$1 תגובות|0={{GENDER:$2|כתוב|כתבי}} את התגובה הראשונה!}}',
	'flow-comment-restored' => 'תגובה משוחזרת',
	'flow-comment-deleted' => 'תגובה מחוקה',
	'flow-comment-hidden' => 'תגובה מוסתרת',
	'flow-comment-moderated' => 'תגובה מפוקחת',
	'flow-paging-rev' => 'נושאים חדשים יותר',
	'flow-paging-fwd' => 'נושאים ישנים יותר',
	'flow-last-modified' => 'שוּנה לאחרונה $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|השיב|השיבה}} ל<span class="plainlinks">[$5 רשומה]</span> שלך בנושא "$2" בדף "$4".',
	'flow-notification-reply-bundle' => '$1 {{PLURAL:$6|ועוד אדם אחד|ו־$5 אנשים}} השיבו ל<span class="plainlinks">[$4 רשומה]</span> שלך בנושא "$2" בדף "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|ערך|ערכה}} <span class="plainlinks">[$5 רשומה]</span> בנושא "$2" בדף [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 {{PLURAL:$6|ועוד אדם אחד|ועוד $5 אנשים}} ערכו <span class="plainlinks">[$4 רשומה]</span> שלך בנושא "$2" בדף "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|יצר|יצרה}} <span class="plainlinks">[$5 נושא חדש]</span> בדף [[$2|$3]]&rlm;: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|שינה|שינתה}} את הכותרת של <span class="plainlinks">[$2 $3]</span> אל "$4" בדף [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|הזכיר|הזכירה}} אותך ב<span class="plainlinks">[$2 רשומה]</span> {{GENDER:$1|שלו|שלה}} בנושא "$3" בדף "$4".',
	'flow-notification-link-text-view-post' => 'הצגת הרשומה',
	'flow-notification-link-text-view-board' => 'הצגת הלוח',
	'flow-notification-link-text-view-topic' => 'הצגת הנושא',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|השיב|השיבה}} לרשומה שלך',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|השיב|השיבה}} לרשומה שלך בנושא "$2" בדף "$3".',
	'flow-notification-reply-email-batch-bundle-body' => '$1 {{PLURAL:$5|ועוד אדם אחד|ועוד $4 אנשים}} השיבו לרשומה שלך בנושא "$2" בדף "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|הזכיר|הזכירה}} אותך ברשומה "$2"',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|הזכיר|הזכירה}} אותך ברשומה {{GENDER:$1|שלו|שלה}} בנושא "$2" בדף "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|ערך|ערכה}} רשומה',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|ערך|ערכה}} רשומה בנושא "$2" בדף "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 {{PLURAL:$5|ועוד אדם אחד|ועוד $4 אנשים}} ערכו רשומה בנושא "$2" בדף "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|שינה|שינתה}} את השם של נושא שלך',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|שינה|שינתה}} את השם של הנושא שלך "$2" אל "$3" בדף "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|יצר|יצרה}} נושא חדש בדף "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|יצר|יצרה}} נושא חדש עם הכותרת "$2" ב{{GRAMMAR:תחלילית|$3}}',
	'echo-category-title-flow-discussion' => 'זרימה',
	'echo-pref-tooltip-flow-discussion' => 'להודיע לי כשיש פעולות שקשורות אליי ב"זרימה".',
	'flow-link-post' => 'רשומה',
	'flow-link-topic' => 'נושא',
	'flow-link-history' => 'היסטוריה',
	'flow-moderation-reason-placeholder' => 'נא להזין כאן את הסיבה שלך',
	'flow-moderation-title-suppress-post' => 'להעלים את הרשומה?',
	'flow-moderation-title-delete-post' => 'למחוק את הרשומה?',
	'flow-moderation-title-hide-post' => 'להסתיר את הרשומה?',
	'flow-moderation-title-restore-post' => 'לשחזר את הרשומה?',
	'flow-moderation-intro-suppress-post' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה מעלים|את מעלימה}} את הרשומה הזאת.',
	'flow-moderation-intro-delete-post' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה מוחק|את מוחקת}} את הרשומה הזאת.',
	'flow-moderation-intro-hide-post' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה מסתיר|את מסתירה}} את הרשומה הזאת.',
	'flow-moderation-intro-restore-post' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה משחזר|את משחזרת}} את הרשומה הזאת.',
	'flow-moderation-confirm-suppress-post' => 'להעלים',
	'flow-moderation-confirm-delete-post' => 'למחוק',
	'flow-moderation-confirm-hide-post' => 'להסתיר',
	'flow-moderation-confirm-restore-post' => 'לשחזר',
	'flow-moderation-confirmation-suppress-post' => 'הרשומה הזאת הועלמה בהצלחה.
אנא {{GENDER:$2|שקול|שקלי}} לתת ל{{GRAMMAR:תחילית|$1}} משוב על הרשומה הזאת.',
	'flow-moderation-confirmation-delete-post' => 'הרשומה נמחקה בהצלחה.
אנא {{GENDER:$2|שקול|שקלי}} לתת ל{{GRAMMAR:תחילית|$1}} משוב על הרשומה הזאת.',
	'flow-moderation-confirmation-hide-post' => 'הרשמה הועלמה בהצלחה.
אנא {{GENDER:$2|שקול|שקלי}} לתת ל{{GRAMMAR:תחילית|$1}} משוב על הרשומה הזאת.',
	'flow-moderation-confirmation-restore-post' => 'שחזרת בהצלחה את הרשומה הזאת.',
	'flow-moderation-title-suppress-topic' => 'להעלים את הנושא?',
	'flow-moderation-title-delete-topic' => 'למחוק את הנושא?',
	'flow-moderation-title-hide-topic' => 'להסתיר את הנושא?',
	'flow-moderation-title-restore-topic' => 'לשחזר את הנושא?',
	'flow-moderation-intro-suppress-topic' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה מעלים|את מעלימה}} את הנושא הזה.',
	'flow-moderation-intro-delete-topic' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה מוחק|את מוחקת}} את הרשומה הזאת.',
	'flow-moderation-intro-hide-topic' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה מסתיר|את מסתירה}} את הנושא הזה.',
	'flow-moderation-intro-restore-topic' => '{{GENDER:$3|הסבר|הסבירי}} בבקשה למה {{GENDER:$3|אתה משחזר|את משחזרת}} את הנושא הזה.',
	'flow-moderation-confirm-suppress-topic' => 'להעלים',
	'flow-moderation-confirm-delete-topic' => 'למחוק',
	'flow-moderation-confirm-hide-topic' => 'להסתיר',
	'flow-moderation-confirm-restore-topic' => 'לשחזר',
	'flow-moderation-confirmation-suppress-topic' => 'הנושא הזה הועלם בהצלחה.
אנא {{GENDER:$2|שקול|שקלי}} לתת ל{{GRAMMAR:תחילית|$1}} משוב על הנושא הזה.',
	'flow-moderation-confirmation-delete-topic' => 'הנושא הזה נמחק בהצלחה.
אנא {{GENDER:$2|שקול|שקלי}} לתת ל{{GRAMMAR:תחילית|$1}} משוב על הנושא הזה.',
	'flow-moderation-confirmation-hide-topic' => 'הנושא הזה הוסתר בהצלחה.
אנא {{GENDER:$2|שקול|שקלי}} לתת ל{{GRAMMAR:תחילית|$1}} משוב על הנושא הזה.',
	'flow-moderation-confirmation-restore-topic' => 'שחזרת בהצלחה את הרשומה הזאת.',
	'flow-topic-permalink-warning' => 'הנושא הזה התחיל בדף [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'הנושא הזה התחיל ב[$2 לוח של $1]',
	'flow-revision-permalink-warning-post' => 'זהו קישור קבוע לגרסה פרטנית של הרשומה הזאת.
זוהי גרסה מ־$1.
באפשרותך לראות את [$5 השינויים מהגרסה הקודמת] או להציג גרסאות אחרות ב[$4 דף ההיסטוריה של הרשומה].',
	'flow-revision-permalink-warning-post-first' => 'זהו קישור קבוע לגרסה הראשונה של הרשומה.
אפשר להציג גרסאות מאוחרות יותר ב[$4 דף ההיסטוריה של הרשומה].',
	'flow-compare-revisions-revision-header' => 'גרסה מאת $2 מ{{GRAMMAR:תחילית|$1}}',
	'flow-compare-revisions-header-post' => 'הדף הזה מציג את ההבדלים בין שתי גרסאות של רשומה מאת $3 בנושא "[$5 $2]" בלוח [$4 $1].

באפשרותך לראות גרסאות אחרות של הרשומה הזאת ב[$6 דף ההיסטוריה] שלו.',
	'flow-topic-collapsed-one-line' => 'תצוגה מוקטנת',
	'flow-topic-collapsed-full' => 'תצוגה מקופלת',
	'flow-topic-complete' => 'תצוגה מלאה',
	'flow-terms-of-use-new-topic' => 'לחיצה על "{{int:flow-newtopic-save}}" מהווה את הסכמתך לתנאי השימוש של הוויקי הזה.',
	'flow-terms-of-use-reply' => 'לחיצה על "{{int:flow-reply-submit}}" מהווה את הסכמתך לתנאי השימוש של הוויקי הזה.',
	'flow-terms-of-use-edit' => 'שמירת השינויים מהווה את הסכמתך לתנאי השימוש של הוויקי הזה.',
);

/** Hindi (हिन्दी)
 * @author Vivek Rai
 */
$messages['hi'] = array(
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|हटाया हुआ}} एक [ $4  टिप्पणी] पर  $6  (<em> $5 </em>)।',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|हटाया हुआ}} [ $4  विषय]  $6  (<em> $5 </em>)।',
);

/** Croatian (hrvatski)
 * @author MaGa
 */
$messages['hr'] = array(
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|Vas je spomenuo|Vas je spomenula}} na projektu $2',
);

/** Armenian (Հայերեն)
 * @author M hamlet
 * @author Vadgt
 */
$messages['hy'] = array(
	'flow-preview' => 'Նախադիտել',
	'flow-reply-placeholder' => 'Սեղմեք {{GENDER:$1|պատասխանել}} $1-ում: Կլինի լա՜վ', # Fuzzy
	'flow-notification-edit' => '$1՝ {{GENDER:$1|խմբագրեց}} ձեր [$5 գրառում(ներ)ը] $2-ում [[$3|$4]]ի վրա:', # Fuzzy
	'flow-notification-rename' => '$1՝ {{GENDER:$1|փոխեց}} վերնագրիրը [$2 $3]-ի "$4"-ում [[$5|$6]]-ի վրա:',
);

/** Interlingua (interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'flow-desc' => 'Systema de gestion de fluxo de travalio',
	'log-name-flow' => 'Registro de activitate de fluxo',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|deleva}} un [$4 message] in [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|restaurava}} un [$4 message] in [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|deleva}} un [$4 message] in [[$3]]',
	'flow-user-moderated' => 'Usator moderate',
	'flow-edit-header-link' => 'Modificar titulo',
	'flow-header-empty' => 'Iste pagina de discussion actualmente non ha titulo.',
	'flow-post-moderated-toggle-show' => '[Monstrar]',
	'flow-post-moderated-toggle-hide' => '[Celar]',
	'flow-suppress-usertext' => '<em>Nomine de usator supprimite</em>',
	'flow-post-actions' => 'Actiones',
	'flow-topic-actions' => 'Actiones',
	'flow-cancel' => 'Cancellar',
	'flow-preview' => 'Previsualisar',
	'flow-newtopic-title-placeholder' => 'Nove topico',
	'flow-newtopic-content-placeholder' => 'Adde detalios si tu vole',
	'flow-newtopic-header' => 'Adder un nove topico',
	'flow-newtopic-save' => 'Adder topico',
	'flow-newtopic-start-placeholder' => 'Initiar un nove discussion',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Commentar}} "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|Responder}} a $1',
	'flow-reply-submit' => '{{GENDER:$1|Responder}}',
	'flow-reply-link' => '{{GENDER:$1|Responder}}',
	'flow-thank-link' => '{{GENDER:$1|Regratiar}}',
	'flow-edit-post-submit' => 'Submitter modificationes',
	'flow-post-edited' => 'Entrata {{GENDER:$1|modificate}} per $1 $2',
	'flow-post-action-view' => 'Permaligamine',
	'flow-post-action-post-history' => 'Historia de messages',
	'flow-post-action-suppress-post' => 'Supprimer',
	'flow-post-action-delete-post' => 'Deler',
	'flow-post-action-hide-post' => 'Celar',
	'flow-post-action-edit-post' => 'Modificar entrata',
	'flow-post-action-edit' => 'Modificar',
	'flow-post-action-restore-post' => 'Restaurar entrata',
	'flow-topic-action-view' => 'Permaligamine',
	'flow-topic-action-watchlist' => 'Observatorio',
	'flow-topic-action-edit-title' => 'Modificar titulo',
	'flow-topic-action-history' => 'Historia del topico',
	'flow-topic-action-hide-topic' => 'Celar topico',
	'flow-topic-action-delete-topic' => 'Deler topico',
	'flow-topic-action-suppress-topic' => 'Supprimer topico',
	'flow-topic-action-restore-topic' => 'Restaurar topico',
	'flow-error-http' => 'Un error occurreva durante le communication con le servitor.',
	'flow-error-other' => 'Un error inexpectate ha occurrite.',
	'flow-error-external' => 'Un error ha occurrite.<br />Le message de error recipite es: $1',
	'flow-error-edit-restricted' => 'Tu non es autorisate a modificar iste entrata.',
	'flow-error-external-multi' => 'Errores ha occurrite.<br />$1',
	'flow-error-missing-content' => 'Le message non ha contento. Contento es necessari pro salveguardar un message.',
	'flow-error-missing-title' => 'Le topico non ha titulo. Le titulo es necessari pro salveguardar un topico.',
	'flow-error-parsoid-failure' => 'Impossibile interpretar le contento a causa de un fallimento de Parsoid.',
	'flow-error-missing-replyto' => 'Nulle parametro "replyTo" ha essite fornite. Iste parametro es necessari pro le action "responder".',
	'flow-error-invalid-replyto' => 'Le parametro "replyTo" es invalide. Le entrata specificate non pote esser trovate.',
	'flow-error-delete-failure' => 'Le deletion de iste elemento ha fallite.',
	'flow-error-hide-failure' => 'Le celamento de iste elemento ha fallite.',
	'flow-error-missing-postId' => 'Nulle parametro "postId" ha essite specificate. Iste parametro es necessari pro manipular un entrata.',
	'flow-error-invalid-postId' => 'Le parametro "postId" es invalide. Le entrata specificate ($1) non poteva esser trovate.',
	'flow-error-restore-failure' => 'Le restauration de iste elemento ha fallite.',
	'flow-error-invalid-moderation-state' => 'Un valor invalide ha essite fornite pro moderationState',
	'flow-error-invalid-moderation-reason' => 'Per favor da un motivo pro le moderation',
	'flow-error-not-allowed' => 'Permissiones insufficiente pro exequer iste action',
	'flow-edit-header-submit' => 'Salveguardar titulo',
	'flow-edit-title-submit' => 'Cambiar titulo',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|modificava}} un [$3 commento].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|addeva}} un [$3 commento].',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|commento|commentos}}</strong> ha essite addite.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|creava}} le topico [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|cambiava}} le titulo del topico de $5 in [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|creava}} le titulo del tabuliero.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|modificava}} le titulo del tabuliero.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|celava}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|deleva}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|supprimeva}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|restaurava}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|celava}} le [$4 topico] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|deleva}} le [$4 topico] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|supprimeva}} le [$4 topico] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|restaurava}} le [$4 topico] (<em>$5</em>).',
	'flow-topic-history' => 'Historia del topico "$1"',
	'flow-comment-restored' => 'Commento restaurate',
	'flow-comment-deleted' => 'Commento delite',
	'flow-comment-hidden' => 'Commento celate',
	'flow-comment-moderated' => 'Commento moderate',
	'flow-paging-rev' => 'Topicos plus recente',
	'flow-paging-fwd' => 'Topicos plus vetule',
	'flow-last-modified' => 'Ultime modification circa $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|respondeva}} a tu [$5 message] in $2 super [[$3|$4]].',
	'flow-notification-reply-bundle' => '$1 e $5 {{PLURAL:$6|altere|alteres}} {{GENDER:$1|respondeva}} a tu [$4 message] in $2 sur "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|modificava}} un [$5 message] in $2 sur [[$3|$4]].',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|creava}} un [$5 nove topico] super [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|cambiava}} le titulo de [$2 $3] a "$4" super [[$5|$6]].',
	'flow-notification-link-text-view-post' => 'Vider message',
	'flow-notification-link-text-view-board' => 'Vider tabuliero',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|respondeva}} a tu message',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|respondeva}} a tu message in $2 sur "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e $4 {{PLURAL:$5|altere|alteres}} {{GENDER:$1|respondeva}} a tu message in $2 sur "$3"',
	'echo-category-title-flow-discussion' => 'Fluxo',
	'echo-pref-tooltip-flow-discussion' => 'Notificar me quando actiones concernente me occurre in Fluxo.',
);

/** Italian (italiano)
 * @author Amire80
 * @author Beta16
 * @author Gianfranco
 * @author Maria victoria
 * @author Rosh
 */
$messages['it'] = array(
	'flow-desc' => 'Sistema di gestione del flusso di lavoro',
	'flow-talk-taken-over' => 'Questa pagina di discussione è stata sostituita da una [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal bacheca dei flussi].',
	'log-name-flow' => 'Attività sui flussi',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|ha cancellato}} un [$4 messaggio] su [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|ha ripristinato}} un [$4 messaggio] su [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|ha soppresso}} un [$4 messaggio] su [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|ha cancellato}} un [$4 messaggio] su [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|ha cancellato}} una [$4 discussione] su [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|ha ripristinato}} una [$4 discussione] su [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|ha soppresso}} una [$4 discussione] su [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|ha cancellato}} una [$4 discussione] su [[$3]]',
	'flow-user-moderated' => 'Utente moderato',
	'flow-edit-header-link' => 'Modifica intestazione',
	'flow-header-empty' => 'Questa pagina di discussione attualmente non ha alcuna intestazione.',
	'flow-post-moderated-toggle-hide-show' => 'Mostra commenti {{GENDER:$1|nascosti}} da $2',
	'flow-post-moderated-toggle-delete-show' => 'Mostra commenti {{GENDER:$1|cancellati}} da $2',
	'flow-post-moderated-toggle-suppress-show' => 'Mostra commenti {{GENDER:$1|soppressi}} da $2',
	'flow-post-moderated-toggle-hide-hide' => 'Nascondi commenti {{GENDER:$1|nascosti}} da $2',
	'flow-post-moderated-toggle-delete-hide' => 'Nascondi commento {{GENDER:$1|cancellato}} da $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Nascondi commenti {{GENDER:$1|soppressi}} da $2',
	'flow-hide-post-content' => 'Questo commento è stato {{GENDER:$1|nascosto}} da $2',
	'flow-hide-title-content' => 'Questa discussione è stata {{GENDER:$1|nascosta}} da $2',
	'flow-hide-header-content' => '{{GENDER:$1|Nascosto}} da $2',
	'flow-delete-post-content' => 'Questo commento è stato {{GENDER:$1|cancellato}} da $2',
	'flow-delete-title-content' => 'Questa discussione è stata {{GENDER:$1|cancellata}} da $2',
	'flow-delete-header-content' => '{{GENDER:$1|Cancellato}} da $2',
	'flow-suppress-post-content' => 'Questo commento è stato {{GENDER:$1|soppresso}} da $2',
	'flow-suppress-title-content' => 'Questa discussione è stata {{GENDER:$1|soppressa}} da $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Soppresso}} da $2',
	'flow-suppress-usertext' => '<em>Nome utente soppresso</em>',
	'flow-post-actions' => 'Azioni',
	'flow-topic-actions' => 'Azioni',
	'flow-cancel' => 'Annulla',
	'flow-preview' => 'Anteprima',
	'flow-show-change' => 'Mostra modifiche',
	'flow-last-modified-by' => 'Ultima {{GENDER:$1|modifica}} di $1',
	'flow-stub-post-content' => "''A causa di un errore tecnico, questo messaggio non può essere recuperato.''",
	'flow-newtopic-title-placeholder' => 'Nuova discussione',
	'flow-newtopic-content-placeholder' => 'Aggiungi qualche dettaglio, se vuoi',
	'flow-newtopic-header' => 'Aggiungi una nuova discussione',
	'flow-newtopic-save' => 'Aggiungi discussione',
	'flow-newtopic-start-placeholder' => 'Inizia una nuova discussione',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Commento}} su "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|Rispondi}} a $1',
	'flow-reply-submit' => '{{GENDER:$1|Rispondi}}',
	'flow-reply-link' => '{{GENDER:$1|Rispondi}}',
	'flow-thank-link' => '{{GENDER:$1|Ringrazia}}',
	'flow-post-edited' => 'Messaggio {{GENDER:$1|modificato}} da $1 $2',
	'flow-post-action-view' => 'Link permanente',
	'flow-post-action-post-history' => 'Cronologia',
	'flow-post-action-suppress-post' => 'Sopprimi',
	'flow-post-action-delete-post' => 'Cancella',
	'flow-post-action-hide-post' => 'Nascondi',
	'flow-post-action-edit-post' => 'Modifica',
	'flow-post-action-restore-post' => 'Ripristina messaggio',
	'flow-topic-action-view' => 'Link permanente',
	'flow-topic-action-watchlist' => 'Osservati speciali',
	'flow-topic-action-edit-title' => 'Modifica titolo',
	'flow-topic-action-history' => 'Cronologia',
	'flow-topic-action-hide-topic' => 'Nascondi discussione',
	'flow-topic-action-delete-topic' => 'Cancella discussione',
	'flow-topic-action-suppress-topic' => 'Sopprimi discussione',
	'flow-topic-action-restore-topic' => 'Ripristina discussione',
	'flow-error-http' => 'Si è verificato un errore durante la comunicazione con il server.',
	'flow-error-other' => 'Si è verificato un errore imprevisto.',
	'flow-error-external' => 'Si è verificato un errore.<br />Il messaggio di errore ricevuto è: $1',
	'flow-error-edit-restricted' => 'Non è consentito modificare questo messaggio.',
	'flow-error-external-multi' => 'Si sono verificati errori.<br />$1',
	'flow-error-missing-content' => 'Il tuo messaggio non ha contenuto. Un minimo di contenuto è necessario per poter salvare un messaggio.',
	'flow-error-missing-title' => 'La discussione non ha titolo. Serve un titolo per salvare una discussione.',
	'flow-error-parsoid-failure' => 'Impossibile analizzare il contenuto a causa di un errore di Parsoid.',
	'flow-error-missing-replyto' => 'Non è stato indicato un parametro "rispondi_a". Questo parametro è richiesto per la funzione "rispondi".',
	'flow-error-invalid-replyto' => 'Il parametro "rispondi_a" non era valido. Il messaggio indicato non è stato trovato.',
	'flow-error-delete-failure' => 'La cancellazione di questo elemento non è riuscita.',
	'flow-error-hide-failure' => 'Il tentativo di nascondere questo elemento non è riuscito.',
	'flow-error-missing-postId' => 'Non è stato fornito alcun parametro "ID_messaggio". Questo parametro è necessario per poter elaborare un messaggio.',
	'flow-error-invalid-postId' => 'Il parametro "ID_messaggio" non era valido. Il messaggio indicato ($1) non è stato trovato.',
	'flow-error-restore-failure' => 'Il ripristino di questo elemento non è riuscito.',
	'flow-error-invalid-moderation-state' => 'È stato fornito un valore non valido per moderationState',
	'flow-error-invalid-moderation-reason' => 'Fornisci una motivazione per la moderazione',
	'flow-error-not-allowed' => 'Autorizzazioni insufficienti per eseguire questa azione',
	'flow-error-title-too-long' => 'I titoli delle discussioni sono limitati a $1 {{PLURAL:$1|byte}}.',
	'flow-error-no-existing-workflow' => 'Questo flusso di lavoro non esiste ancora.',
	'flow-error-not-a-post' => 'Il titolo di una discussione non può essere salvato come un messaggio.',
	'flow-error-missing-header-content' => "L'intestazione non ha contenuto. Un minimo di contenuto è necessario per poter salvare un'intestazione.",
	'flow-error-missing-prev-revision-identifier' => "L'Identificatore della versione precedente è mancante.",
	'flow-error-prev-revision-mismatch' => 'Un altro utente ha modificato questo messaggio pochi secondi fa. Sei sicuro di voler sovrascrivere la recente modifica?',
	'flow-error-prev-revision-does-not-exist' => 'Impossibile trovare la versione precedente.',
	'flow-error-default' => 'Si è verificato un errore.',
	'flow-error-invalid-input' => 'È stato fornito un valore non valido per il caricamento dei contenuti del flusso.',
	'flow-error-invalid-title' => 'È stato fornito un titolo di pagina non valido.',
	'flow-error-fail-load-history' => 'Impossibile caricare la cronologia.',
	'flow-error-missing-revision' => 'Non è possibile trovare una versione per il caricamento dei contenuti del flusso.',
	'flow-error-fail-commit' => 'Impossibile salvare il contenuto del flusso.',
	'flow-error-insufficient-permission' => 'Autorizzazioni insufficienti per accedere al contenuto.',
	'flow-error-revision-comparison' => 'Le differenze possono essere visualizzate solo per due versioni dello stesso messaggio.',
	'flow-error-missing-topic-title' => 'Impossibile trovare il titolo della discussione per il flusso di lavoro attuale.',
	'flow-error-fail-load-data' => 'Impossibile caricare i dati richiesti.',
	'flow-error-invalid-workflow' => 'Impossibile trovare il flusso di lavoro richiesto.',
	'flow-error-process-data' => "Si è verificato un errore durante l'elaborazione dei dati nella tua richiesta.",
	'flow-error-process-wikitext' => 'Si è verificato un errore durante il processo di conversione HTML/wikitesto.',
	'flow-error-no-index' => 'Impossibile trovare un indice per eseguire la ricerca di dati.',
	'flow-edit-header-submit' => 'Salva intestazione',
	'flow-edit-header-submit-overwrite' => 'Sovrascrivi intestazione',
	'flow-edit-title-submit' => 'Cambia titolo',
	'flow-edit-title-submit-overwrite' => 'Sovrascrivi titolo',
	'flow-edit-post-submit' => 'Invia modifiche',
	'flow-edit-post-submit-overwrite' => 'Sovrascrivi modifiche',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|modificato}} un [$3 commento] su $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|ha commentato}}] su $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|commento|commenti}}</strong> {{PLURAL:$1|è stato aggiunto|sono stati aggiunti}}.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|ha creato}} la discussione [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|ha modificato}} il titolo della discussione in [$3 $4] da $5.',
	'flow-rev-message-create-header' => "$1 {{GENDER:$2|creato}} l'intestazione.",
	'flow-rev-message-edit-header' => "$1 {{GENDER:$2|modificato}} l'intestazione.",
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|nascosto}} un [$4 commento] su $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|eliminato}} un [ $4  commento] su  $6  (<em> $5 </em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|soppresso}} un [ $4  commento] su  $6  (<em> $5 </em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|ripristinato}} un [$4 commento] su $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|ha nascosto}} la [$4 discussione] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|eliminato}} il [ $4  argomento]  $6  (<em> $5 </em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|soppresso}} il [ $4  argomento]  $6  (<em> $5 </em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|ha ripristinato}} la [$4 discussione] $6 (<em>$5</em>).',
	'flow-board-history' => 'Cronologia di "$1"',
	'flow-topic-history' => 'Cronologia della discussione "$1"',
	'flow-post-history' => 'Cronologia del commento di {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Ultime 4 ore',
	'flow-history-day' => 'Oggi',
	'flow-history-week' => 'Ultima settimana',
	'flow-history-pages-topic' => 'Apparso sulla [$1 bacheca "$2"]',
	'flow-history-pages-post' => 'Apparso su [$1  $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 ha iniziato questa discussione|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} e {{PLURAL:$2|un altro|altri}}|0=Nessuno ha partecipato ancora|2={{GENDER:$3|$3}} e {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} e {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 commento|$1 commenti|0=Sii {{GENDER:$2|il primo|la prima}} a commentare!}}',
	'flow-comment-restored' => 'Commento ripristinato',
	'flow-comment-deleted' => 'Commento cancellato',
	'flow-comment-hidden' => 'Commento nascosto',
	'flow-comment-moderated' => 'Commento moderato',
	'flow-paging-rev' => 'Discussioni più recenti',
	'flow-paging-fwd' => 'Vecchie discussioni',
	'flow-last-modified' => 'Ultima modifica $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|ha risposto}} al tuo <span class="plainlinks">[$5 messaggio]</span> in "$2" su "$4".',
	'flow-notification-reply-bundle' => '$1 e {{PLURAL:$6|un altro|altri $5}} utenti {{GENDER:$1|hanno risposto}} al tuo <span class="plainlinks">[$4 messaggio]</span> in "$2" su "$3".',
	'flow-notification-edit' => '$1 ha {{GENDER:$1|modificato}} un <span class="plainlinks">[$5 messaggio]</span> in "$2" su [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 e {{PLURAL:$6|un altro|altri $5}} utenti {{GENDER:$1|hanno modificato}} un <span class="plainlinks">[$4 messaggio]</span> in "$2" su "$3".',
	'flow-notification-newtopic' => '$1 ha {{GENDER:$1|creato}} una <span class="plainlinks">[$5 nuova discussione]</span> su [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 ha {{GENDER:$1|cambiato}} il titolo di <span class="plainlinks">[$2 $3]</span> in "$4" su [[$5|$6]]',
	'flow-notification-mention' => '$1 ti {{GENDER:$1|ha menzionato}} nel suo <span class="plainlinks">[$2 messaggio]</span> in "$3" su "$4".',
	'flow-notification-link-text-view-post' => 'Vedi messaggio',
	'flow-notification-link-text-view-board' => 'Vedi bacheca',
	'flow-notification-link-text-view-topic' => 'Vedi discussione',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ha risposto}} al tuo messaggio',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|ha risposto}} al tuo messaggio in "$2" su "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e {{PLURAL:$5|un altro|altri $4}} {{GENDER:$1|hanno risposto}} al tuo messaggio in "$2" su "$3"',
	'flow-notification-mention-email-subject' => '$1 ti {{GENDER:$1|ha menzionato}} su "$2"',
	'flow-notification-mention-email-batch-body' => '$1 ti {{GENDER:$1|ha menzionato}} nel suo messaggio in "$2" su "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|ha modificato}} un messaggio',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|ha modificato}} un messaggio in "$2" su "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 e {{PLURAL:$5|un altro|altri $4}} utenti {{GENDER:$1|hanno modificato}} un messaggio in "$2" su "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|ha rinominato}} la tua discussione',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|ha rinominato}} la discussione "$2" in "$3" su "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|ha creato}} una nuova discussione su "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|ha creato}} una nuova discussione "$2" su $3',
	'echo-category-title-flow-discussion' => 'Flusso',
	'echo-pref-tooltip-flow-discussion' => 'Avvisami quando vengono eseguite azioni connesse a me nel flusso delle discussioni.',
	'flow-link-post' => 'messaggio',
	'flow-link-topic' => 'discussione',
	'flow-link-history' => 'cronologia',
	'flow-moderation-reason-placeholder' => 'Inserisci qui la motivazione',
	'flow-moderation-title-suppress-post' => 'Sopprimere il messaggio?',
	'flow-moderation-title-delete-post' => 'Cancellare il messaggio?',
	'flow-moderation-title-hide-post' => 'Nascondere il messaggio?',
	'flow-moderation-title-restore-post' => 'Ripristinare il messaggio?',
	'flow-moderation-intro-suppress-post' => '{{GENDER:$3|Spiega}} perché stai sopprimendo questo messaggio.',
	'flow-moderation-intro-delete-post' => '{{GENDER:$3|Spiega}} perché stai cancellando questo messaggio.',
	'flow-moderation-intro-hide-post' => '{{GENDER:$3|Spiega}} perché stai nascondendo questo messaggio.',
	'flow-moderation-intro-restore-post' => '{{GENDER:$3|Spiega}} perché stai ripristinando questo messaggio.',
	'flow-moderation-confirm-suppress-post' => 'Sopprimi',
	'flow-moderation-confirm-delete-post' => 'Cancella',
	'flow-moderation-confirm-hide-post' => 'Nascondi',
	'flow-moderation-confirm-restore-post' => 'Ripristina',
	'flow-moderation-confirmation-suppress-post' => 'Il messaggio è stato soppresso con successo.
{{GENDER:$2|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-delete-post' => 'Il messaggio è stato cancellato con successo.
{{GENDER:$2|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-hide-post' => 'Il messaggio è stato nascosto con successo.
{{GENDER:$2|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-restore-post' => 'Hai ripristinato con successo il messaggio precedente.',
	'flow-moderation-title-suppress-topic' => 'Sopprimere la discussione?',
	'flow-moderation-title-delete-topic' => 'Cancellare la discussione?',
	'flow-moderation-title-hide-topic' => 'Nascondere la discussione?',
	'flow-moderation-title-restore-topic' => 'Ripristinare la discussione?',
	'flow-moderation-intro-suppress-topic' => '{{GENDER:$3|Spiega}} perché stai sopprimendo questa discussione.',
	'flow-moderation-intro-delete-topic' => '{{GENDER:$3|Spiega}} perché stai cancellando questa discussione.',
	'flow-moderation-intro-hide-topic' => '{{GENDER:$3|Spiega}} perché stai nascondendo questa discussione.',
	'flow-moderation-intro-restore-topic' => '{{GENDER:$3|Spiega}} perché stai ripristinando questa discussione.',
	'flow-moderation-confirm-suppress-topic' => 'Sopprimi',
	'flow-moderation-confirm-delete-topic' => 'Cancella',
	'flow-moderation-confirm-hide-topic' => 'Nascondi',
	'flow-moderation-confirm-restore-topic' => 'Ripristina',
	'flow-moderation-confirmation-suppress-topic' => 'La discussione è stata soppressa con successo.
{{GENDER:$2|Scrivi}} a $1 riguardo a questa discussione.',
	'flow-moderation-confirmation-delete-topic' => 'La discussione è stata cancellata con successo.
{{GENDER:$2|Scrivi}} a $1 riguardo a questa discussione.',
	'flow-moderation-confirmation-hide-topic' => 'La discussione è stata nascosta con successo.
{{GENDER:$2|Scrivi}} a $1 riguardo a questa discussione.',
	'flow-moderation-confirmation-restore-topic' => 'Hai ripristinato con successo questa discussione.',
	'flow-topic-permalink-warning' => 'La discussione è iniziata su [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'La discussione è iniziata sulla [$2 bacheca di {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Questo è un collegamento permanente ad una singola versione di questo messaggio.
Questa versione è del $1.
Puoi vedere le [$5 differenze dalla versione precedente] o le altre versioni nella [$4 cronologia della pagina].',
	'flow-revision-permalink-warning-post-first' => 'Questo è un collegamento permanente alla prima versione di questo messaggio.
Puoi vedere le versioni successive nella [$4 cronologia della pagina].',
	'flow-revision-permalink-warning-header' => "Questo è un collegamento permanente ad una singola versione dell'intestazione.
Questa versione è del $1.
Puoi vedere le [$3 differenze dalla versione precedente] o le altre versioni nella [$2 cronologia della pagina].",
	'flow-revision-permalink-warning-header-first' => "Questo è un collegamento permanente alla prima versione dell'intestazione.
Puoi vedere le ultime versioni nella [$2 cronologia della pagina].",
	'flow-compare-revisions-revision-header' => 'Versione di {{GENDER:$2|$2}} del $1',
	'flow-compare-revisions-header-post' => 'Questa pagina mostra le {{GENDER:$3|modifiche}} tra due versioni del messaggio di $3, nella discussione "[$5 $2]" su [$4 $1].
Puoi vedere le altre versioni nella [$6 cronologia della pagina].',
	'flow-compare-revisions-header-header' => "Questa pagina mostra le {{GENDER:$2|modifiche}} tra due versioni dell'intestazione su [$3 $1].
Puoi vedere le altre versioni nella [$4 cronologia della pagina].",
	'flow-topic-collapsed-one-line' => 'Vista piccola',
	'flow-topic-collapsed-full' => 'Vista compatta',
	'flow-topic-complete' => 'Vista completa',
	'flow-terms-of-use-new-topic' => 'Cliccando su "{{int:flow-newtopic-save}}", accetti le condizioni d\'uso per questo wiki.',
	'flow-terms-of-use-reply' => 'Cliccando su "{{int:flow-reply-submit}}", accetti le condizioni d\'uso per questo wiki.',
	'flow-terms-of-use-edit' => "Salvando le modifiche, accetti le condizioni d'uso per questo wiki.",
);

/** Japanese (日本語)
 * @author Fryed-peach
 * @author Kanon und wikipedia
 * @author Shirayuki
 */
$messages['ja'] = array(
	'flow-desc' => 'ワークフロー管理システム',
	'flow-talk-taken-over' => 'このトークページは、[https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow 掲示板]に引き継がれました。',
	'log-name-flow' => 'Flow活動記録',
	'logentry-delete-flow-delete-post' => '$1 が [[$3]] の[$4 投稿]を{{GENDER:$2|削除}}',
	'logentry-delete-flow-restore-post' => '$1 が [[$3]] の[$4 投稿]を{{GENDER:$2|復元}}',
	'logentry-suppress-flow-suppress-post' => '$1 が [[$3]] の[$4 投稿]を{{GENDER:$2|秘匿}}',
	'logentry-suppress-flow-restore-post' => '$1 が [[$3]] の[$4 投稿]を{{GENDER:$2|削除}}',
	'logentry-delete-flow-delete-topic' => '$1 が [[$3]] の[$4 話題]を{{GENDER:$2|削除}}',
	'logentry-delete-flow-restore-topic' => '$1 が [[$3]] の[$4 話題]を{{GENDER:$2|復元}}',
	'logentry-suppress-flow-suppress-topic' => '$1 が [[$3]] の[$4 話題]を{{GENDER:$2|秘匿}}',
	'logentry-suppress-flow-restore-topic' => '$1 が [[$3]] の[$4 話題]を{{GENDER:$2|削除}}',
	'flow-edit-header-link' => 'ヘッダーを編集',
	'flow-header-empty' => '現在、このトークページにはヘッダーがありません。',
	'flow-post-moderated-toggle-hide-show' => '$2 が{{GENDER:$1|非表示にした}}コメントを表示',
	'flow-post-moderated-toggle-delete-show' => '$2 が{{GENDER:$1|削除した}}コメントを表示',
	'flow-post-moderated-toggle-hide-hide' => '$2 が{{GENDER:$1|非表示にした}}コメントを非表示',
	'flow-post-moderated-toggle-delete-hide' => '$2 が{{GENDER:$1|削除した}}コメントを非表示',
	'flow-hide-post-content' => 'このコメントは $2 によって{{GENDER:$1|非表示にされました}}',
	'flow-hide-title-content' => 'この話題は $2 によって{{GENDER:$1|非表示にされました}}',
	'flow-hide-header-content' => '$2 が{{GENDER:$1|非表示にしました}}',
	'flow-delete-post-content' => 'このコメントは $2 によって{{GENDER:$1|削除されました}}',
	'flow-delete-title-content' => 'この話題は $2 によって{{GENDER:$1|削除されました}}',
	'flow-delete-header-content' => '$2 が{{GENDER:$1|削除しました}}',
	'flow-suppress-post-content' => 'このコメントは $2 によって{{GENDER:$1|秘匿されました}}',
	'flow-suppress-title-content' => 'この話題は $2 によって{{GENDER:$1|秘匿されました}}',
	'flow-suppress-header-content' => '$2 が{{GENDER:$1|秘匿しました}}',
	'flow-suppress-usertext' => '<em>利用者名は秘匿されています</em>',
	'flow-post-actions' => '操作',
	'flow-topic-actions' => '操作',
	'flow-cancel' => 'キャンセル',
	'flow-preview' => 'プレビュー',
	'flow-show-change' => '差分を表示',
	'flow-last-modified-by' => '最終{{GENDER:$1|更新}}者: $1',
	'flow-stub-post-content' => "''技術的な問題が発生したため、この投稿を取得できませんでした。''",
	'flow-newtopic-title-placeholder' => '新しい話題',
	'flow-newtopic-content-placeholder' => '詳細情報を入力 (省略可能)',
	'flow-newtopic-header' => '新しい話題の追加',
	'flow-newtopic-save' => '話題を追加',
	'flow-newtopic-start-placeholder' => '新しい話題の作成',
	'flow-reply-topic-placeholder' => '「$2」に{{GENDER:$1|コメントする}}',
	'flow-reply-placeholder' => '$1 への{{GENDER:$1|返信}}',
	'flow-reply-submit' => '{{GENDER:$1|返信}}',
	'flow-reply-link' => '{{GENDER:$1|返信}}',
	'flow-thank-link' => '{{GENDER:$1|感謝}}',
	'flow-post-edited' => '$1 が $2 に{{GENDER:$1|編集した}}投稿',
	'flow-post-action-view' => '固定リンク',
	'flow-post-action-post-history' => '履歴',
	'flow-post-action-suppress-post' => '秘匿',
	'flow-post-action-delete-post' => '削除',
	'flow-post-action-hide-post' => '非表示にする',
	'flow-post-action-edit-post' => '編集',
	'flow-post-action-restore-post' => '投稿を復元',
	'flow-topic-action-view' => '固定リンク',
	'flow-topic-action-watchlist' => 'ウォッチリスト',
	'flow-topic-action-edit-title' => '題名を編集',
	'flow-topic-action-history' => '履歴',
	'flow-topic-action-hide-topic' => '話題を非表示にする',
	'flow-topic-action-delete-topic' => '話題を削除',
	'flow-topic-action-suppress-topic' => '話題を秘匿',
	'flow-topic-action-restore-topic' => '話題を復元',
	'flow-error-http' => 'サーバーとの通信中にエラーが発生しました。',
	'flow-error-other' => '予期しないエラーが発生しました。',
	'flow-error-external' => 'エラーが発生しました。<br />受信したエラーメッセージ: $1',
	'flow-error-edit-restricted' => 'あなたはこの投稿を編集を許可されていません。',
	'flow-error-external-multi' => '複数のエラーが発生しました。<br /> $1',
	'flow-error-missing-content' => '投稿の本文がありません。投稿を保存するには本文が必要です。',
	'flow-error-missing-title' => '話題の題名がありません。話題を保存するには題名が必要です。',
	'flow-error-parsoid-failure' => 'Parsoid でエラーが発生したため、本文を構文解析できませんでした。',
	'flow-error-missing-replyto' => '「返信先」のパラメーターを指定していません。「返信」するには、このパラメーターが必要です。',
	'flow-error-invalid-replyto' => '「返信先」のパラメーターが無効です。指定した投稿が見つかりませんでした。',
	'flow-error-delete-failure' => 'この項目を削除できませんでした。',
	'flow-error-hide-failure' => 'この項目を非表示にできませんでした。',
	'flow-error-missing-postId' => '「投稿 ID」のパラメーターを指定していません。投稿を操作するには、このパラメーターが必要です。',
	'flow-error-invalid-postId' => '「投稿 ID」のパラメーターが無効です。指定した投稿 ($1) が見つかりませんでした。',
	'flow-error-restore-failure' => 'この項目を復元できませんでした。',
	'flow-error-invalid-moderation-state' => 'moderationState に指定した値は無効です。',
	'flow-error-not-allowed' => 'この操作を実行するのに十分な権限がありません。',
	'flow-error-title-too-long' => '話題の題名は $1 {{PLURAL:$1|バイト}}までに制限されています。',
	'flow-error-no-existing-workflow' => 'このワークフローはまだ存在しません。',
	'flow-error-not-a-post' => '話題の題名は投稿としては保存できません。',
	'flow-error-missing-header-content' => 'ヘッダーの本文がありません。ヘッダーを保存するには本文が必要です。',
	'flow-error-missing-prev-revision-identifier' => '以前の版の ID がありません。',
	'flow-error-prev-revision-mismatch' => '編集内容を保存できませんでした。より新しい変更が既に投稿されました。', # Fuzzy
	'flow-error-prev-revision-does-not-exist' => '過去の版が見つかりませんでした。',
	'flow-error-default' => 'エラーが発生しました。',
	'flow-error-invalid-input' => 'Flow の本文の読み込みについて無効な値を指定しました。',
	'flow-error-invalid-title' => '無効なページ名を指定しました。',
	'flow-error-fail-load-history' => '履歴の内容を読み込めませんでした。',
	'flow-error-missing-revision' => 'Flow の本文を読み込むための版が見つかりませんでした。',
	'flow-error-fail-commit' => 'Flow の本文を保存できませんでした。',
	'flow-error-insufficient-permission' => 'その内容にアクセスするのに十分な権限がありません。',
	'flow-error-revision-comparison' => '差分の操作は、2 つの版が同一の投稿に属する場合のみ実行できます。',
	'flow-error-missing-topic-title' => '現在のワークフローについて話題の題名が見つかりませんでした。',
	'flow-error-fail-load-data' => '要求したデータを読み込めませんでした。',
	'flow-error-invalid-workflow' => '要求したワークフローが見つかりませんでした。',
	'flow-error-process-data' => '要求されたデータを処理する際にエラーが発生しました。',
	'flow-error-process-wikitext' => 'HTML/ウィキテキスト変換を処理する際にエラーが発生しました。',
	'flow-error-no-index' => 'データ検索を実行するためのインデックスが見つかりませんでした。',
	'flow-edit-header-submit' => 'ヘッダーを保存',
	'flow-edit-header-submit-overwrite' => 'ヘッダーを上書き',
	'flow-edit-title-submit' => '題名を変更',
	'flow-edit-title-submit-overwrite' => '題名を上書き',
	'flow-edit-post-submit' => '変更を保存',
	'flow-edit-post-submit-overwrite' => '変更を上書き',
	'flow-rev-message-edit-post' => '$1 が $4 の[$3 コメント]を{{GENDER:$2|編集}}',
	'flow-rev-message-reply' => '$1 が $4 に[$3 {{GENDER:$2|コメントを追加}}]', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|件のコメント}}</strong>が追加{{PLURAL:$1|されました}}。',
	'flow-rev-message-new-post' => '$1 が話題 [$3 $4] を{{GENDER:$2|作成}}',
	'flow-rev-message-edit-title' => '$1 が話題の題名を $5 から [$3 $4] に{{GENDER:$2|変更}}',
	'flow-rev-message-create-header' => '$1 がヘッダーを{{GENDER:$2|作成}}',
	'flow-rev-message-edit-header' => '$1 がヘッダーを{{GENDER:$2|編集}}',
	'flow-rev-message-hid-post' => '$1 が $6 の[$4 コメント]を{{GENDER:$2|非表示化}} (<em>$5</em>)',
	'flow-rev-message-deleted-post' => '$1 が $6 の[$4 コメント]を{{GENDER:$2|削除}} (<em>$5</em>)',
	'flow-rev-message-suppressed-post' => '$1 が $6 の[$4 コメント]を{{GENDER:$2|秘匿}} (<em>$5</em>)',
	'flow-rev-message-restored-post' => '$1 が $6 の[$4 コメント]を{{GENDER:$2|復元}} (<em>$5</em>)',
	'flow-rev-message-hid-topic' => '$1 が[$4 話題] $6 を{{GENDER:$2|非表示化}} (<em>$5</em>)',
	'flow-rev-message-deleted-topic' => '$1 が[$4 話題] $6 を{{GENDER:$2|削除}} (<em>$5</em>)',
	'flow-rev-message-suppressed-topic' => '$1 が[$4 話題] $6 を{{GENDER:$2|秘匿}} (<em>$5</em>)',
	'flow-rev-message-restored-topic' => '$1 が[$4 話題] $6 を{{GENDER:$2|復元}} (<em>$5</em>)',
	'flow-board-history' => '「$1」の履歴',
	'flow-topic-history' => '話題「$1」の履歴',
	'flow-post-history' => '「{{GENDER:$2|$2}} によるコメント」投稿履歴',
	'flow-history-last4' => '過去 4 時間',
	'flow-history-day' => '今日',
	'flow-history-week' => '過去 1 週間',
	'flow-history-pages-topic' => '[$1 掲示板「$2」]に出現',
	'flow-history-pages-post' => '[$1 $2]に出現',
	'flow-topic-participants' => '{{PLURAL:$1|$3 がこの話題を開始|{{GENDER:$3|$3}}、{{GENDER:$4|$4}}、{{GENDER:$5|$5}} と他 $2 {{PLURAL:$2|人}}|0=まだ誰も参加していません|2={{GENDER:$3|$3}} と {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}、{{GENDER:$4|$4}}、{{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 件のコメント|0=最初のコメントを{{GENDER:$2|書きましょう}}!}}',
	'flow-comment-restored' => 'コメントを復元',
	'flow-comment-deleted' => 'コメントを削除',
	'flow-comment-hidden' => 'コメントを非表示',
	'flow-paging-rev' => '最近の話題',
	'flow-paging-fwd' => '古い話題',
	'flow-last-modified' => '最終更新 $1',
	'flow-notification-reply' => '$1 が「$4」の「$2」でのあなたの<span class="plainlinks">[$5 投稿]</span>に{{GENDER:$1|返信しました}}。',
	'flow-notification-reply-bundle' => '$1 と他 $5 {{PLURAL:$6|人}}が「$3」の「$2」でのあなたの<span class="plainlinks">[$4 投稿]</span>に{{GENDER:$1|返信しました}}。',
	'flow-notification-edit' => '$1 が [[$3|$4]] の「$2」での<span class="plainlinks">[$5 投稿]</span>を{{GENDER:$1|編集しました}}。',
	'flow-notification-edit-bundle' => '$1 と他 $5 {{PLURAL:$6|人}}が「$3」の「$2」での<span class="plainlinks">[$4 投稿]</span>を{{GENDER:$1|編集しました}}。',
	'flow-notification-newtopic' => '$1 が [[$2|$3]] で<span class="plainlinks">[$5 新しい話題]</span>を{{GENDER:$1|作成しました}}: $4',
	'flow-notification-rename' => '$1 が [[$5|$6]] で <span class="plainlinks">[$2 $3]</span> のページ名を「$4」に{{GENDER:$1|変更しました}}。',
	'flow-notification-mention' => '$1 が「$4」の「$3」での{{GENDER:$1|自身の}}<span class="plainlinks">[$2 投稿]</span>であなたに{{GENDER:$1|言及しました}}。',
	'flow-notification-link-text-view-post' => '投稿を閲覧',
	'flow-notification-link-text-view-board' => '掲示板を閲覧',
	'flow-notification-link-text-view-topic' => '話題を閲覧',
	'flow-notification-reply-email-subject' => '$1 があなたの投稿に{{GENDER:$1|返信しました}}',
	'flow-notification-reply-email-batch-body' => '$1 が「$3」の「$2」でのあなたの投稿に{{GENDER:$1|返信しました}}',
	'flow-notification-reply-email-batch-bundle-body' => '$1 と他 $4 {{PLURAL:$5|人}}が「$3」の「$2」でのあなたの投稿に{{PLURAL:$1|返信しました}}',
	'flow-notification-mention-email-subject' => '$1 が「$2」であなたに{{GENDER:$1|言及しました}}',
	'flow-notification-mention-email-batch-body' => '$1 が「$3」の「$2」での{{GENDER:$1|自身の}}投稿であなたに{{GENDER:$1|言及しました}}',
	'flow-notification-edit-email-subject' => '$1 が投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-edit-email-batch-body' => '$1 が「$3」の「$2」で投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-edit-email-batch-bundle-body' => '$1 と他 $4 {{PLURAL:$5|人}}が「$3」の「$2」での投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-rename-email-subject' => '$1 があなたの話題の{{GENDER:$1|題名を変更しました}}',
	'flow-notification-rename-email-batch-body' => '$1 が「$4」のあなたの話題「$2」の題名を「$3」に{{GENDER:$1|変更しました}}',
	'flow-notification-newtopic-email-subject' => '$1 が「$2」に新しい話題を{{GENDER:$1|作成しました}}',
	'flow-notification-newtopic-email-batch-body' => '$1 が $3 で新しい話題「$2」を{{GENDER:$1|作成しました}}',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Flow で私に関連する操作がなされたときに通知する。',
	'flow-link-post' => '投稿',
	'flow-link-topic' => '話題',
	'flow-link-history' => '履歴',
	'flow-moderation-reason-placeholder' => '理由をここに入力',
	'flow-moderation-title-suppress-post' => '投稿を秘匿しますか?',
	'flow-moderation-title-delete-post' => '投稿を削除しますか?',
	'flow-moderation-title-hide-post' => '投稿を非表示にしますか?',
	'flow-moderation-title-restore-post' => '投稿を復元しますか?',
	'flow-moderation-intro-suppress-post' => 'この投稿を秘匿する理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-intro-delete-post' => 'この投稿を削除する理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-intro-hide-post' => 'この投稿を非表示にする理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-intro-restore-post' => 'この投稿を復元する理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-confirm-suppress-post' => '秘匿',
	'flow-moderation-confirm-delete-post' => '削除',
	'flow-moderation-confirm-hide-post' => '非表示にする',
	'flow-moderation-confirm-restore-post' => '復元',
	'flow-moderation-confirmation-restore-post' => 'この投稿を復元しました。',
	'flow-moderation-title-suppress-topic' => '話題を秘匿しますか?',
	'flow-moderation-title-delete-topic' => '話題を削除しますか?',
	'flow-moderation-title-hide-topic' => '話題を非表示にしますか?',
	'flow-moderation-title-restore-topic' => '話題を復元しますか?',
	'flow-moderation-intro-suppress-topic' => 'この話題を秘匿する理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-intro-delete-topic' => 'この話題を削除する理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-intro-hide-topic' => 'この話題を非表示にする理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-intro-restore-topic' => 'この話題を復元する理由を{{GENDER:$3|説明}}してください。',
	'flow-moderation-confirm-suppress-topic' => '秘匿',
	'flow-moderation-confirm-delete-topic' => '削除',
	'flow-moderation-confirm-hide-topic' => '非表示にする',
	'flow-moderation-confirm-restore-topic' => '復元',
	'flow-moderation-confirmation-restore-topic' => 'この話題を復元しました。',
	'flow-topic-permalink-warning' => 'この話題は [$2 $1] で開始されました',
	'flow-topic-permalink-warning-user-board' => 'この話題は [$2 {{GENDER:$1|$1}} の掲示板]で開始されました',
	'flow-revision-permalink-warning-post' => 'これはこの投稿の特定の版への固定リンクです。
この版は $1 時点のものです。
[$5 以前の版との差分]や、[$4 投稿の履歴ページ]でその他の版を閲覧することもできます。',
	'flow-revision-permalink-warning-post-first' => 'これはこの投稿の初版への固定リンクです。
[$4 投稿の履歴ページ]で以降の版を閲覧できます。',
	'flow-revision-permalink-warning-header' => 'これはヘッダーの特定の版への固定リンクです。
この版は $1 時点のものです。[$3 以前の版との差分]や、[$2 掲示板の履歴ページ]でその他の版を閲覧することもできます。',
	'flow-revision-permalink-warning-header-first' => 'これはヘッダーの初版への固定リンクです。
[$2 掲示板の履歴ページ]で以降の版を閲覧できます。',
	'flow-compare-revisions-revision-header' => '$1における {{GENDER:$2|$2}} による版',
	'flow-compare-revisions-header-post' => 'このページでは、[$4 $1] の話題「[$5 $2]」での $3 の投稿の 2 つの版の{{GENDER:$3|差分}}を表示しています。
この投稿の[$6 履歴ページ]でその他の版を閲覧できます。',
	'flow-compare-revisions-header-header' => 'このページでは、[$3 $1] のヘッダーの 2 つの版の{{GENDER:$2|差分}}を表示しています。
このヘッダーの[$4 履歴ページ]でその他の版を閲覧できます。',
	'flow-topic-collapsed-one-line' => '縮小表示',
	'flow-topic-collapsed-full' => '折りたたみ表示',
	'flow-topic-complete' => '全体表示',
	'flow-terms-of-use-new-topic' => '「{{int:flow-newtopic-save}}」をクリックすると、このウィキの利用規約に同意したと見なされます。',
	'flow-terms-of-use-reply' => '「{{int:flow-reply-submit}}」をクリックすると、このウィキの利用規約に同意したと見なされます。',
	'flow-terms-of-use-edit' => '変更内容を保存すると、このウィキの利用規約に同意したと見なされます。',
);

/** Lojban (Lojban)
 * @author Gleki
 */
$messages['jbo'] = array(
	'log-name-flow' => 'flecu fasnu citri',
	'flow-post-moderated-toggle-show' => '(to zganygau toi)',
	'flow-post-moderated-toggle-hide' => '(to cancygau toi)',
	'flow-post-actions' => 'loi se zukte',
	'flow-topic-actions' => 'loi se zukte',
	'flow-newtopic-save' => "jmina la'e se casnu",
	'flow-post-action-delete-post' => 'daspo',
	'flow-post-action-hide-post' => 'cancygau',
	'flow-post-action-edit-post' => 'stika lo se mrilu',
	'flow-post-action-edit' => 'stika',
	'echo-category-title-flow-discussion' => 'lo flecu',
	'flow-link-topic' => 'lo se casnu',
	'flow-link-history' => 'lo citri',
);

/** Korean (한국어)
 * @author Clockoon
 * @author Daisy2002
 * @author Hym411
 * @author Jskang
 * @author Priviet
 * @author Yjs5497
 * @author 아라
 */
$messages['ko'] = array(
	'flow-desc' => '워크플로우 관리 시스템',
	'flow-talk-taken-over' => '이 토론 문서는 [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal 플로우 판]에 의해 인계되었습니다.',
	'log-name-flow' => '플로우 활동 기록',
	'logentry-delete-flow-delete-post' => '$1 사용자가 [[$3]] 문서의 [$4 게시물]을 {{GENDER:$2|삭제했습니다}}',
	'logentry-delete-flow-restore-post' => '$1 사용자가 [[$3]] 문서의 [$4 게시물]을  {{GENDER:$2|되살렸습니다}}',
	'logentry-suppress-flow-suppress-post' => '$1 사용자가 [[$3]] 문서의 [$4 게시물]을 {{GENDER:$2|숨겼습니다}}',
	'logentry-suppress-flow-restore-post' => '$1 사용자가 [[$3]] 문서의 [$4 게시물]을 {{GENDER:$2|삭제했습니다}}',
	'logentry-delete-flow-delete-topic' => '$1 사용자가 [[$3]] 문서의 [$4 주제]를 {{GENDER:$2|삭제했습니다}}',
	'logentry-delete-flow-restore-topic' => '$1 사용자가 [[$3]] 문서의 [$4 주제]를 {{GENDER:$2|복원했습니다}}',
	'logentry-suppress-flow-suppress-topic' => '$1 사용자가 [[$3]] 문서의 [$4 주제]를 {{GENDER:$2|숨겼습니다}}',
	'logentry-suppress-flow-restore-topic' => '$1 사용자가 [[$3]] 문서의 [$4 주제]를 {{GENDER:$2|삭제했습니다}}',
	'flow-user-moderated' => '중재된 사용자',
	'flow-edit-header-link' => '머리말 고치기',
	'flow-header-empty' => '이 토론 문서에는 머릿말이 없습니다.',
	'flow-post-moderated-toggle-hide-show' => '$2 사용자가 {{GENDER:$1|표시 안 함으로 설정한}} 댓글 보이기',
	'flow-post-moderated-toggle-delete-show' => '$2 사용자가 {{GENDER:$1|삭제한}} 댓글 보이기',
	'flow-post-moderated-toggle-suppress-show' => '$2 사용자가 {{GENDER:$1|숨긴}} 댓글 보이기',
	'flow-post-moderated-toggle-hide-hide' => '$2 사용자가 {{GENDER:$1|표시 안 함으로 설정한}} 댓글 숨기기',
	'flow-post-moderated-toggle-delete-hide' => '$2 사용자가 {{GENDER:$1|삭제한}} 댓글 숨기기',
	'flow-post-moderated-toggle-suppress-hide' => '$2 사용자가 {{GENDER:$1|숨긴}} 댓글 숨기기',
	'flow-hide-post-content' => '이 덧글은 $2 사용자가 {{GENDER:$1|표시 안 함으로 설정했습니다}}',
	'flow-hide-title-content' => '이 주제는 $2 사용자가 {{GENDER:$1|표시 안 함으로 설정했습니다}}',
	'flow-hide-header-content' => '$2 사용자가 {{GENDER:$1|표시 안 함으로 설정함}}',
	'flow-delete-post-content' => '이 덧글은 $2 사용자가 {{GENDER:$1|삭제했습니다}}',
	'flow-delete-title-content' => '$2이(가) 이 문서를 {{GENDER:$1|제거했습니다}}',
	'flow-delete-header-content' => '$2 사용자가 {{GENDER:$1|삭제함}}',
	'flow-suppress-post-content' => '이 덧글은 $2 사용자가 {{GENDER:$1|표시 안 함으로 설정했습니다}}',
	'flow-suppress-title-content' => '이 주제는 $2 사용자가 {{GENDER:$1|표시 안 함으로 설정했습니다}}',
	'flow-suppress-header-content' => '$2 사용자가 {{GENDER:$1|표시 안 함으로 설정함}}',
	'flow-suppress-usertext' => '<em>사용자 이름 표시 안함으로 설정됨</em>',
	'flow-post-actions' => '동작',
	'flow-topic-actions' => '동작',
	'flow-cancel' => '취소',
	'flow-preview' => '미리 보기',
	'flow-show-change' => '차이 보기',
	'flow-last-modified-by' => '$1 사용자가 마지막으로 {{GENDER:$1|수정함}}',
	'flow-stub-post-content' => '"기술적인 오류로 인하여 이 게시물을 가져올 수 없었습니다."',
	'flow-newtopic-title-placeholder' => '새 주제',
	'flow-newtopic-content-placeholder' => '세부 사항을 추가(생략 가능)',
	'flow-newtopic-header' => '새 항목 추가',
	'flow-newtopic-save' => '새 항목',
	'flow-newtopic-start-placeholder' => '새 주제',
	'flow-reply-topic-placeholder' => '$1의 "$2"에 대한 의견',
	'flow-reply-placeholder' => '$1 사용자에게 {{GENDER:$1|답변}}',
	'flow-reply-submit' => '{{GENDER:$1|답변}}',
	'flow-reply-link' => '{{GENDER:$1|답변}}',
	'flow-thank-link' => '{{GENDER:$1|감사합니다}}',
	'flow-post-edited' => '$1 사용자가 $2에 게시물을 {{GENDER:$1|편집했습니다}}',
	'flow-post-action-view' => '고유링크',
	'flow-post-action-post-history' => '역사',
	'flow-post-action-suppress-post' => '숨기기',
	'flow-post-action-delete-post' => '삭제',
	'flow-post-action-hide-post' => '숨기기',
	'flow-post-action-edit-post' => '편집',
	'flow-post-action-restore-post' => '문서 복구',
	'flow-topic-action-view' => '고유링크',
	'flow-topic-action-watchlist' => '주시문서 목록',
	'flow-topic-action-edit-title' => '제목 편집',
	'flow-topic-action-history' => '역사',
	'flow-topic-action-hide-topic' => '항목 숨기기',
	'flow-topic-action-delete-topic' => '항목 삭제',
	'flow-topic-action-suppress-topic' => '주제 숨겨놓기',
	'flow-topic-action-restore-topic' => '항목 복원',
	'flow-error-http' => '서버 접속 중에 에러가 발생했습니다.',
	'flow-error-other' => '예기치 않은 오류가 발생했습니다.',
	'flow-error-external' => '포스트를 저장하는 중에 에러가 발생했습니다.편집이 저장이 되지 않았습니다.<br />에러 메시지: $1',
	'flow-error-edit-restricted' => '이 문서의 편집을 허용하지 않습니다.',
	'flow-error-external-multi' => '에러가 발생하였습니다.<br />$1',
	'flow-error-missing-content' => '내용이 없습니다. 저장하려면 내용이 있어야 합니다.',
	'flow-error-missing-title' => '항목에 제목이 없습니다. 항목을 저장하려면 제목이 필요합니다.',
	'flow-error-parsoid-failure' => 'Parsoid 오류로 인해 내용을 구문 분석할 수 없습니다.',
	'flow-error-missing-replyto' => '"ReplyTo" 매개변수는 지원되지 않습니다. 이 매개변수는 "답변" 명령에 대해 필요합니다.',
	'flow-error-invalid-replyto' => '"replyTo" 매개변수가 유효하지 않습니다. 지정한 게시물을 찾을 수 없습니다.',
	'flow-error-delete-failure' => '이 항목을 삭제하는 데 실패했습니다.',
	'flow-error-hide-failure' => '이 항목을 표시하지 않음으로 설정하지 못하였습니다.',
	'flow-error-missing-postId' => '"postId" 매개변수를 지원하지 않습니다. 이 매개변수는 게시물을 조작하여야 합니다.',
	'flow-error-invalid-postId' => '"replyTo" 매개변수가 유효하지 않습니다. 지정한 게시물($1)을 찾을 수 없습니다.',
	'flow-error-restore-failure' => '이 항목을 복원하는 데 실패했습니다.',
	'flow-error-invalid-moderation-state' => '유효하지 않은 값이 조정상태에 입력되었습니다.',
	'flow-error-invalid-moderation-reason' => '조정의 이유를 알려주세요.',
	'flow-error-not-allowed' => '이 명령을 실행할 권한이 부족합니다.',
	'flow-error-title-too-long' => '주제 제목은 $1 {{PLURAL:$1|바이트}}로 제한됩니다.',
	'flow-error-no-existing-workflow' => '이 워크플로우는 아직 존재하지 않습니다.',
	'flow-error-not-a-post' => '주제 제목은 기여로 저장할 수 없습니다.',
	'flow-error-missing-header-content' => '머릿글에 내용이 없습니다. 내용은 머릿글을 저장하기 위해서 필요합니다.',
	'flow-error-missing-prev-revision-identifier' => '이전 판 식별자가 없습니다.',
	'flow-error-prev-revision-mismatch' => '다른 사용자가 이 게시물을 조금 전에 편집했습니다 최근 바뀜을 덮어쓰시겠습니까?',
	'flow-error-prev-revision-does-not-exist' => '이전 판을 찾을 수 없습니다.',
	'flow-error-default' => '오류가 발생했습니다.',
	'flow-error-invalid-input' => '유효하지 않은 값은 플로우 콘텐츠를 불러오기 위해 입력됩니다.',
	'flow-error-invalid-title' => '유효하지 않은 문서 제목을 입력했습니다.',
	'flow-error-fail-load-history' => '역사 내용을 불러오는 데 실패했습니다.',
	'flow-error-missing-revision' => '플로우 내용을 불러오기 위한 판을 찾는 데 실패하였습니다.',
	'flow-error-fail-commit' => '플로우 내용을 저장하는 데 실패하였습니다.',
	'flow-error-insufficient-permission' => '내용에 접근하기 위한 권한이 부족합니다.',
	'flow-error-revision-comparison' => '차이 보기 명령은 같은 게시물의 두 개 판에 대해서만 이루어집니다.',
	'flow-error-missing-topic-title' => '현재 워크플로우에 대한 주제 제목을 찾을 수 없습니다.',
	'flow-error-fail-load-data' => '요청한 데이터를 불러오는 데 실패했습니다.',
	'flow-error-invalid-workflow' => '요청한 워크플로우를 찾을 수 없습니다.',
	'flow-error-process-data' => '당신의 요청 데이터를 처리하는 도중 오류가 발생했습니다.',
	'flow-error-process-wikitext' => 'HTML/위키텍스트 대화를 처리하는 도중 오류가 발생했습니다.',
	'flow-error-no-index' => '데이터 검색을 수행하기 위한 인덱스를 찾는 데 실패했습니다.',
	'flow-edit-header-submit' => '머릿글을 저장',
	'flow-edit-header-submit-overwrite' => '머릿글을 덮어쓰기',
	'flow-edit-title-submit' => '제목 바꾸기',
	'flow-edit-title-submit-overwrite' => '제목 덮어쓰기',
	'flow-edit-post-submit' => '변경된 내용을 제출합니다',
	'flow-edit-post-submit-overwrite' => '바뀜 덮어쓰기',
	'flow-rev-message-edit-post' => '$1 사용자가 $4의 [$3 덧글]을 {{GENDER:$2|편집하였습니다}} .',
	'flow-rev-message-reply' => '$1 사용자가 $4에 [$3 {{GENDER:$2|댓글을 남겼습니다}}].', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|개의 덧글}}</strong>이 추가{{PLURAL:$1|되었습니다}}.',
	'flow-rev-message-new-post' => '$1 사용자가 [$3 $4] 주제를  {{GENDER:$2|만들었습니다}}.',
	'flow-rev-message-edit-title' => ' $1 사용자가 $5에서 [$3 $4]으로(로) 주제의 제목을 {{GENDER:$2|바꾸었습니다}}.',
	'flow-rev-message-create-header' => '$1 사용자가 머릿글을 {{GENDER:$2|만들었습니다}}.',
	'flow-rev-message-edit-header' => '$1 사용자가 머릿글을 {{GENDER:$2|편집하였습니다}}.',
	'flow-rev-message-hid-post' => '$1 사용자가 $6의 [$4 덧글]을 {{GENDER:$2|표시 안 함으로 설정하였습니다}}(<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 사용자가 [$4 덧글]을 $6에서 {{GENDER:$2|삭제하였습니다}}(<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 사용자가 $6의 [$4 덧글]을 {{GENDER:$2|숨겼습니다}}(<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 사용자가 $6의 [$4 덧글]을 {{GENDER:$2|복원하였습니다}}(<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 사용자가 $6의 [$4 이 주제]를 {{GENDER:$2|표시 안 함으로 설정하였습니다}}(<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 사용자가 $6의 [$4 주제]를 {{GENDER:$2|삭제하였습니다}}(<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 사용자가 $6의 [$4 주제]를 {{GENDER:$2|숨겼습니다}}(<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 사용자가가 $6의 [$4 주제]를 {{GENDER:$2|복원하였습니다}}(<em>$5</em>).',
	'flow-board-history' => '"$1" 역사',
	'flow-topic-history' => '"$1" 주제 역사',
	'flow-post-history' => '"{{GENDER:$2|$2}}가 쓴 덧글"의 역사',
	'flow-history-last4' => '지난 4시간',
	'flow-history-day' => '오늘',
	'flow-history-week' => '지난 주',
	'flow-history-pages-topic' => '[$1 "$2" 게시판]에 나타납니다',
	'flow-history-pages-post' => '[$1 $2]에 나타납니다',
	'flow-topic-participants' => '{{PLURAL:$1|$3 사용자가 이 주제를 시작했습니다.|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}}, 그 외 $2 {{PLURAL:$2|사용자}}|0=아직 참가하지 않음et|2={{GENDER:$3|$3}}와 {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|댓글 ($1개)|댓글 ($1개)|0=첫 댓글을 {{GENDER:$2|달아 보세요}}!}}',
	'flow-comment-restored' => '복원된 덧글',
	'flow-comment-deleted' => '삭제된 덧글',
	'flow-comment-hidden' => '표시 안 함으로 설정된 댓글',
	'flow-comment-moderated' => '검토 의견',
	'flow-paging-rev' => '최근 주제 더보기',
	'flow-paging-fwd' => '지난 주제',
	'flow-last-modified' => '$1에 대한 마지막 수정',
	'flow-notification-reply' => '$1 사용자가 <span class="plainlinks">"$4"의 $2 주제에 대한 [$5 게시물]</span>에 {{GENDER:$1|답변을 남겼습니다}}.',
	'flow-notification-reply-bundle' => '$1 사용자와 $5 {{PLURAL:$6|그 외 사용자}}가 당신이 "$3"에 남긴 <span class="plainlinks">[$4 게시물] $2</span>에 {{GENDER:$1|답변을 남겼습니다}}.',
	'flow-notification-edit' => '$1 [[$3|$4]]에 남긴 <span class="plainlinks">[$5 게시물]</span> $2을 {{GENDER:$1|편집했습니다}}.',
	'flow-notification-edit-bundle' => '$1 사용자와 $5 {{PLURAL:$6|그 외 사용자}}가 당신이 "$3"에 남긴 <span class="plainlinks">[$4 게시물]</span> $2을 {{GENDER:$1|편집했습니다}}.',
	'flow-notification-newtopic' => '$1 사용자가 [[$2|$3]]의 <span class="plainlinks">[$5 새로운 주제]</span>를 {{GENDER:$1|만들었습니다.}}:$4',
	'flow-notification-rename' => '$1 사용자가 [[$5|$6]]의 <span class="plainlinks">[$2 $3]</span>의 제목을 "$4"으로 {{GENDER:$1|바꿨습니다}}.',
	'flow-notification-mention' => '$1 사용자가 "$4"의 "$3" {{GENDER:$1|}} <span class="plainlinks">[$2 게시물]</span>에서 {{GENDER:$1|언급했습니다}}.',
	'flow-notification-link-text-view-post' => '게시물 보기',
	'flow-notification-link-text-view-board' => '게시판 보기',
	'flow-notification-link-text-view-topic' => '주제 보기',
	'flow-notification-reply-email-subject' => '$1이 당신의 글에 덧글을 달았습니다.',
	'flow-notification-reply-email-batch-body' => '$1 사용자가 $3에 있는 "$2" 주제의 당신의 게시물에 {{GENDER:$1|답변을 남겼습니다}}.',
	'flow-notification-reply-email-batch-bundle-body' => '$1 사용자와 $4 {{PLURAL:$5|그 외 사용자}}가 당신이 "$3"에 남긴 "$2"에 {{GENDER:$1|답변을 남겼습니다}}.',
	'flow-notification-mention-email-subject' => '$1 사용자가 "$2"에 당신을 {{GENDER:$1|언급했습니다}}.',
	'flow-notification-mention-email-batch-body' => '$1 사용자가 "$3"에 있는 "$2"의 {{GENDER:$1|}} 게시물을 {{GENDER:$1|언급했습니다}}',
	'flow-notification-edit-email-subject' => '$1 사용자가 게시물을 {{GENDER:$1|편집했습니다}}',
	'flow-notification-edit-email-batch-body' => '$1 사용자가 "$3"에 있는 "$2"의 게시물을 {{GENDER:$1|편집했습니다}}',
	'flow-notification-edit-email-batch-bundle-body' => '$1 사용자와 $4 {{PLURAL:$5|그 외 사용자}}가 당신이 "$3"에 있는 "$2" [$4 게시물]을 {{GENDER:$1|편집했습니다}}.',
	'flow-notification-rename-email-subject' => '$1 이 당신의 주제를 바꾸었습니다.',
	'flow-notification-rename-email-batch-body' => '$1 사용자가 "$4"에 있는 당신의 "$2" 주제를  "$3"으로 {{GENDER:$1|이름을 바꿨습니다}}',
	'flow-notification-newtopic-email-subject' => '$1 사용자가 "$2"의 새 주제를 {{GENDER:$1|만들었습니다}}',
	'flow-notification-newtopic-email-batch-body' => '$1 사용자가 $3의 "$2" 문서와 새 주제를 {{GENDER:$1|만들었습니다}}',
	'echo-category-title-flow-discussion' => '플로우',
	'echo-pref-tooltip-flow-discussion' => '플로우에 나와 관련된 명령을 알림',
	'flow-link-post' => '게시물',
	'flow-link-topic' => '주제',
	'flow-link-history' => '역사',
	'flow-moderation-reason-placeholder' => '여기에 이유를 입력하세요',
	'flow-moderation-title-suppress-post' => '게시물을 숨기시겠습니까?',
	'flow-moderation-title-delete-post' => '게시물을 삭제하시겠습니까?',
	'flow-moderation-title-hide-post' => '게시물을 표시 안 함으로 설정하시겠습니까?',
	'flow-moderation-title-restore-post' => '게시물을 복원하시겠습니까?',
	'flow-moderation-intro-suppress-post' => '게시물을 숨기시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-intro-delete-post' => '게시물을 삭제하시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-intro-hide-post' => '게시물을 표시 안 함으로 설정하시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-intro-restore-post' => '게시물을 복원하시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-confirm-suppress-post' => '숨기기',
	'flow-moderation-confirm-delete-post' => '삭제',
	'flow-moderation-confirm-hide-post' => '표시 안함',
	'flow-moderation-confirm-restore-post' => '복원',
	'flow-moderation-confirmation-suppress-post' => '게시물 숨김에 성공하였습니다. 이 게시물에 대한 피드백을 $1 사용자에게 주는 것을 {{GENDER:$2|고려해주세요}}.',
	'flow-moderation-confirmation-delete-post' => '게시물 삭제에 성공하였습니다. 이 게시물에 대한 피드백을 $1 사용자에게  주는 것을 {{GENDER:$2|고려해주세요}}.',
	'flow-moderation-confirmation-hide-post' => '게시물을 표시 안 함으로 설정하는 데 성공하였습니다. 이 게시물에 대한 피드백을 $1 사용자에게 주는 것을 {{GENDER:$2|고려해주세요}}.',
	'flow-moderation-confirmation-restore-post' => '위의 게시물을 복원하는 데 성공했습니다.',
	'flow-moderation-title-suppress-topic' => '주제를 숨기시겠습니까?',
	'flow-moderation-title-delete-topic' => '주제를 삭제하시겠습니까?',
	'flow-moderation-title-hide-topic' => '주제를 표시 안함으로 설정하시겠습니까?',
	'flow-moderation-title-restore-topic' => '주제를 복원하시겠습니까?',
	'flow-moderation-intro-suppress-topic' => '이 주제를 숨기시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-intro-delete-topic' => '이 주제를 삭제하시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-intro-hide-topic' => '이 주제를 표시 안 함으로 설정하시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-intro-restore-topic' => '이 주제를 복원하시는 이유를 {{GENDER:$3|설명해주세요}}.',
	'flow-moderation-confirm-suppress-topic' => '숨기기',
	'flow-moderation-confirm-delete-topic' => '삭제',
	'flow-moderation-confirm-hide-topic' => '숨기기',
	'flow-moderation-confirm-restore-topic' => '복원',
	'flow-moderation-confirmation-suppress-topic' => '주제 숨김에 성공했습니다. 이 게시물에 대한 피드백을 $1 사용자에게 주는 것을 {{GENDER:$2|고려해주세요}}.',
	'flow-moderation-confirmation-delete-topic' => '주제 삭제에 성공하였습니다. 이 게시물에 대한 피드백을 $1 사용자에게 주는 것을 {{GENDER:$2|고려해주세요}}.',
	'flow-moderation-confirmation-hide-topic' => '주제를 표시 안 함으로 설정하는 데 성공하였습니다. 이 게시물에 대한 피드백을 $1 사용자에게 주는 것을 {{GENDER:$2|고려해주세요}}.',
	'flow-moderation-confirmation-restore-topic' => '위의 주제를 복원하는 데 성공했습니다.',
	'flow-topic-permalink-warning' => '이 주제는 [$2 $1]에 시작됐습니다',
	'flow-topic-permalink-warning-user-board' => '이 주제는 [$2 {{GENDER:$1|$1}}의 게시판]에서 시작됐습니다',
	'flow-revision-permalink-warning-post' => '이 게시물의 하나의 판에 대한 영구 링크 입니다.
이 판은 $1에서 가져왔습니다.
[$5 이전 판]나 [$4 게시물 역사 문서]의 다른 판과의 차이를 볼 수 있습니다.',
	'flow-revision-permalink-warning-post-first' => '이 게시물의 첫 번째 판으로 연결된 영구 링크입니다.
[$4 게시물 역사 문서]에서 이후의 판을 볼 수 있습니다.',
	'flow-compare-revisions-revision-header' => '$1에 {{GENDER:$2|$2}} 사용자가 작성한 판',
	'flow-compare-revisions-header-post' => '이 문서는 $3 사용자가 [$4 $1] 게시판의 "[$5 $2]" 주제에 올린 두 판 사이의 {{GENDER:$3|차이}}를 보여줍니다.

[$6 역사 문서]에서 이 게시물의 다른 판을 볼수 있습니다.',
	'flow-topic-collapsed-one-line' => '작게 보기',
	'flow-topic-collapsed-full' => '접힌 보기',
	'flow-topic-complete' => '전체 보기',
	'flow-terms-of-use-new-topic' => '"{{int:flow-newtopic-save}}"을 클릭하면 이 위키의 이용 약관에 동의한 것이 됩니다.',
	'flow-terms-of-use-reply' => '"{{int:flow-newtopic-save}}"을 클릭하면 이 위키의 이용 약관에 동의한 것이 됩니다.',
	'flow-terms-of-use-edit' => '바뀐 내용을 저장하면 이 위키의 이용 약관에 동의한 것이 됩니다.',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 * @author Soued031
 */
$messages['lb'] = array(
	'flow-desc' => 'Workflow-Management-System',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|huet}} eng [$4 Matddelung] op [[$3]] geläscht',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|huet}} e(n) [$4 Thema] op [[$3]] geläscht',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|huet}} e(n) [$4 Thema] op [[$3]] restauréiert',
	'flow-edit-header-link' => 'Iwwerschrëft änneren',
	'flow-header-empty' => 'Dës Diskussiounssäit huet elo keng Iwwerschrëft',
	'flow-post-moderated-toggle-hide-show' => 'Bemierkung weisen déi {{GENDER:$1|vum|vun der}} $2 verstoppt gouf',
	'flow-post-moderated-toggle-delete-show' => 'Bemierkung weisen {{GENDER:$1|geläscht}} vum $2',
	'flow-post-moderated-toggle-hide-hide' => 'Bemierkung verstoppen {{GENDER:$1|verstoppt}} vum $2',
	'flow-post-moderated-toggle-delete-hide' => 'Bemierkung verstoppen déi vum $2 {{GENDER:$1|geläscht}} gouf',
	'flow-hide-post-content' => 'Dës Bemierkung gouf vum $2 {{GENDER:$1|verstoppt}}',
	'flow-hide-title-content' => 'Dëst Thema gouf vum $2 {{GENDER:$1|verstoppt}}',
	'flow-hide-header-content' => '{{GENDER:$1|Verstoppt}} vum $2',
	'flow-delete-post-content' => 'Dës Bemierkung gouf vum $2 {{GENDER:$1|geläscht}}',
	'flow-delete-title-content' => 'Dëst Thema gouf vum $2 {{GENDER:$1|Geläscht}}',
	'flow-delete-header-content' => '{{GENDER:$1|Geläscht}} vum $2',
	'flow-post-actions' => 'Aktiounen',
	'flow-topic-actions' => 'Aktiounen',
	'flow-cancel' => 'Ofbriechen',
	'flow-preview' => 'Kucken ouni ze späicheren',
	'flow-show-change' => 'Ännerunge weisen',
	'flow-last-modified-by' => "Fir d'lescht {{GENDER:$1|geännert}} vum $1",
	'flow-stub-post-content' => '"Duerch en technesche Feeler konnt dës Matdeelung net ofgeruff ginn."',
	'flow-newtopic-title-placeholder' => 'Neit Thema',
	'flow-newtopic-content-placeholder' => 'Setzt e puer Detailer derbäi, wann Dir wëllt',
	'flow-newtopic-header' => 'En neit Thema derbäisetzen',
	'flow-newtopic-save' => 'Thema derbäisetzen',
	'flow-newtopic-start-placeholder' => 'En neit Thema ufänken',
	'flow-reply-topic-placeholder' => '"$2" {{GENDER:$1|kommentéieren}}',
	'flow-reply-placeholder' => 'Dem $1 {{GENDER:$1|äntwerten}}',
	'flow-reply-submit' => '{{GENDER:$1|Äntwerten}}',
	'flow-reply-link' => '{{GENDER:$1|Äntwerten}}',
	'flow-thank-link' => '{{GENDER:$1|Merci soen}}',
	'flow-post-action-view' => 'Permanentlink',
	'flow-post-action-post-history' => 'Versiounen',
	'flow-post-action-delete-post' => 'Läschen',
	'flow-post-action-hide-post' => 'Verstoppen',
	'flow-post-action-edit-post' => 'Änneren',
	'flow-topic-action-watchlist' => 'Iwwerwaachungslëscht',
	'flow-topic-action-edit-title' => 'Titel änneren',
	'flow-topic-action-history' => 'Versiounen',
	'flow-topic-action-hide-topic' => 'Thema verstoppen',
	'flow-topic-action-delete-topic' => 'Thema läschen',
	'flow-topic-action-restore-topic' => 'Thema restauréieren',
	'flow-error-other' => 'En onerwaarte Feeler ass geschitt.',
	'flow-error-external' => 'Et ass e Feeler geschitt.<br />De Feelermessage war:$1</ small>',
	'flow-error-edit-restricted' => 'Dir däerft dës Matdeelung net änneren.',
	'flow-error-external-multi' => 'Et si Feeler geschitt.<br />$1',
	'flow-error-missing-title' => "D'Thema huet keen Titel. Den Titel ass obligatoresch fir een Thema ze späicheren.",
	'flow-error-delete-failure' => "D'Läsche vun dësem Element huet net funktionéiert.",
	'flow-error-hide-failure' => 'Verstoppe vun dësem Element huet net funktionéiert.',
	'flow-error-restore-failure' => "D'Restauréiere vun dësem Element huet net funktionéiert.",
	'flow-error-not-allowed' => 'Net genuch Rechter fir dës Aktioun ze maachen',
	'flow-error-not-a-post' => 'Den Titel vum Thema kann net als Matdeelung gespäichert ginn.',
	'flow-error-missing-header-content' => "D'Iwwerschrëft huet keen Inhalt. Den Inhalt ass obligatoresch fir eng Iwwerschrëft ze späicheren.",
	'flow-error-prev-revision-mismatch' => 'En anere Benotzer huet dës Matdeelung virun e puer Sekonne geännert. Sidd Dir sécher datt Dir déi rezent Ännerung iwwerschreiwe wëllt?',
	'flow-error-prev-revision-does-not-exist' => 'Déi vireg Versioun konnt net fonnt ginn.',
	'flow-error-default' => 'Et ass e Feeler geschitt.',
	'flow-error-invalid-title' => 'En net valabelen Säitentitel gouf uginn.',
	'flow-error-insufficient-permission' => 'Net genuch Rechter fir op den Inhalt zouzegräifen.',
	'flow-edit-header-submit' => 'Iwwerschrëft späicheren',
	'flow-edit-header-submit-overwrite' => 'Iwwerschrëft iwwerschreiwen',
	'flow-edit-title-submit' => 'Titel änneren',
	'flow-edit-title-submit-overwrite' => 'Titel iwwerschreiwen',
	'flow-edit-post-submit' => 'Ännerunge späicheren',
	'flow-edit-post-submit-overwrite' => 'Ännerungen iwwerschreiwen',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|huet}} eng [$3 Bemierkung] iwwer $4 geännert.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|huet}} eng Bemierkung] iwwer $4 derbäigesat.', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>{{PLURAL:$1|Eng Bemierkung gouf|$1 Bemierkunge goufen}} derbäigesat</strong>.',
	'flow-rev-message-new-post' => "$1 {{GENDER:$2|huet}} d'Thema [$3 $4] ugeluecht.",
	'flow-rev-message-create-header' => "$1 {{GENDER:$2|huet}} d'Iwwerschrëft ugeluecht.",
	'flow-rev-message-edit-header' => "$1 {{GENDER:$2|huet}} d'Iwwerschrëft geännert.",
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|huet}} eng [$4 Bemierkung] iwwer $6 (<em>$5</em>) verstoppt.',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|huet}} eng [$4 Bemierkung] iwwer $6 (<em>$5</em>) geläscht.',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|huet}} eng [$4 Bemierkung] iwwer $6 (<em>$5</em>) restauréiert.',
	'flow-rev-message-deleted-topic' => "$1 {{GENDER:$2|huet}} d'[Thema $4] $6 (<em>$5</em>) geläscht.",
	'flow-rev-message-restored-topic' => "$1 {{GENDER:$2|huet}} d'[Thema $4] $6 (<em>$5</em>) restauréiert.",
	'flow-board-history' => 'Versioune vun "$1"',
	'flow-topic-history' => 'Versioune vum Thema "$1"',
	'flow-history-last4' => 'Lescht 4 Stonnen',
	'flow-history-day' => 'Haut',
	'flow-history-week' => 'Lescht Woch',
	'flow-topic-comments' => '{{PLURAL:$1|Eng Bemierkung|$1 Bemierkungen|0=Sidd {{GENDER:$2|deen éischten deen|déi éischt déi}} eng Bemierkung mécht!}}',
	'flow-comment-restored' => 'Restauréiert Bemierkung',
	'flow-comment-deleted' => 'Geläscht Bemierkung',
	'flow-comment-hidden' => 'Verstoppte Bemierkung',
	'flow-comment-moderated' => 'Moderéiert Bemierkung',
	'flow-paging-rev' => 'Méi rezent Themen',
	'flow-paging-fwd' => 'Méi al Themen',
	'flow-last-modified' => "Fir d'lescht geännert ongeféier $1",
	'flow-notification-newtopic' => '$1 {{GENDER:$1|huet}} een <span class="plainlinks">[$5 neit Thema]</span> op [[$2|$3]]: $4 ugeluecht.',
	'flow-notification-rename' => '$1 {{GENDER:$1|huet}} den Titel vu(n) span class="plainlinks">[$2 $3]</span> op "$4" op [[$5|$6]] geännert.',
	'flow-notification-link-text-view-board' => 'Tableau weisen',
	'flow-notification-link-text-view-topic' => 'Thema weisen',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|huet}} op Är Matdeelung iwwer "$2" op "$3" geäntwert',
	'flow-notification-mention-email-subject' => '$1 huet Iech op "$2" {{GENDER:$1|ernimmt}}',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|huet}} eng Matdeelung geännert',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|huet}} eng Matdeelung vu(n) "$2" iwwer "$3" geännert',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|huet}} een neit Thema iwwer "$2" ugeluecht',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Mech informéiere wann Aktiounen déi mech betreffen a geschéien.',
	'flow-link-topic' => 'Thema',
	'flow-link-history' => 'Versiounen',
	'flow-moderation-reason-placeholder' => 'Gitt Äre Grond hei an',
	'flow-moderation-intro-delete-post' => '{{GENDER:$3|Erklärt}} w.e.g. firwat datt Dir dës Matdeelung läscht.',
	'flow-moderation-intro-hide-post' => '{{GENDER:$3|Erklärt}} w.e.g. firwat datt Dir dës Matdeelung verstoppt.',
	'flow-moderation-intro-restore-post' => '{{GENDER:$3|Erklärt}} w.e.g. firwat datt Dir dës Matdeelung restauréiert.',
	'flow-moderation-confirm-delete-post' => 'Läschen',
	'flow-moderation-confirm-hide-post' => 'Verstoppen',
	'flow-moderation-confirm-restore-post' => 'Restauréieren',
	'flow-moderation-confirmation-restore-post' => 'Dir hutt dës Matdeelung restauréiert.',
	'flow-moderation-title-delete-topic' => 'Thema läschen?',
	'flow-moderation-title-hide-topic' => 'Thema verstoppen?',
	'flow-moderation-title-restore-topic' => 'Thema restauréieren?',
	'flow-moderation-intro-suppress-topic' => '{{GENDER:$3|Erkläert}} w.e.g. fir wat datt Dir dëst Thema läscht.',
	'flow-moderation-intro-delete-topic' => '{{GENDER:$3|Erklärt}} w.e.g. firwat datt Dir dëst Thema läscht.',
	'flow-moderation-intro-hide-topic' => '{{GENDER:$3|Erklärt}} w.e.g. firwat datt Dir dëst Thema verstoppt.',
	'flow-moderation-intro-restore-topic' => '{{GENDER:$3|Erklärt}} w.e.g. firwat datt Dir dëst Thema restauréiert.',
	'flow-moderation-confirm-delete-topic' => 'Läschen',
	'flow-moderation-confirm-hide-topic' => 'Verstoppen',
	'flow-moderation-confirm-restore-topic' => 'Restauréieren',
	'flow-moderation-confirmation-restore-topic' => 'Dir hutt dëst Thema restauréiert.',
	'flow-topic-permalink-warning' => 'Dëse Sujet gouf op [$2 $1] ugefaang',
	'flow-compare-revisions-revision-header' => 'Versioun vum {{GENDER:$2|$2}} vum $1',
	'flow-terms-of-use-new-topic' => 'Wann Dir op "{{int:flow-newtopic-save}}" klickt, da sidd Dir mat de Benotzungsbedingunge vun dëser Wiki d\'accord.',
	'flow-terms-of-use-reply' => 'Wann Dir op "{{int:flow-reply-submit}}" klickt, da sidd Dir mat de Benotzungsbedingunge vun dëser Wiki d\'accord.',
	'flow-terms-of-use-edit' => "Duerch Späichere vun Ären Ännerunge sidd Dir mat de Konditioune fir d'Benotze vun dëser Wiki d'Accord.",
);

/** لوری (لوری)
 * @author Mogoeilor
 */
$messages['lrc'] = array(
	'flow-show-change' => 'آلشتيانه نشون بيئه',
);

/** Lithuanian (lietuvių)
 * @author Robotukas11
 */
$messages['lt'] = array(
	'flow-error-external' => 'Įvyko klaida.<br>Gautas klaidos pranešimas: $1', # Fuzzy
);

/** Latvian (latviešu)
 * @author Papuass
 */
$messages['lv'] = array(
	'flow-edit-header-link' => 'Labot galveni',
	'flow-post-moderated-toggle-show' => '[Parādīt]',
	'flow-post-moderated-toggle-hide' => '[Paslēpt]',
	'flow-newtopic-start-placeholder' => 'Sākt jaunu tēmu',
	'flow-reply-submit' => '{{GENDER:$1|Atbildēt}}',
	'flow-reply-link' => '{{GENDER:$1|Atbildēt}}',
	'flow-thank-link' => '{{GENDER:$1|Pateikties}}',
	'flow-talk-link' => 'Diskutēt ar {{GENDER:$1|$1}}',
	'flow-topic-action-view' => 'Pastāvīgā saite',
	'flow-edit-header-submit' => 'Saglabāt galveni', # Fuzzy
	'flow-rev-message-edit-post' => 'Labot ieraksta saturu', # Fuzzy
	'flow-rev-message-create-header' => 'Izveidoja galveni', # Fuzzy
	'flow-rev-message-edit-header' => 'Izmainīja galveni', # Fuzzy
	'flow-rev-message-deleted-post' => 'Dzēsts ieraksts', # Fuzzy
	'flow-rev-message-suppressed-post' => 'Cenzēts ieraksts', # Fuzzy
	'flow-link-topic' => 'tēma',
	'flow-link-history' => 'vēsture',
);

/** Malagasy (Malagasy)
 * @author Jagwar
 */
$messages['mg'] = array(
	'flow-edit-post-submit-overwrite' => 'Hanitsaka ny fiovana',
);

/** Macedonian (македонски)
 * @author Amire80
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'flow-desc' => 'Систем за раководење со работниот тек',
	'flow-talk-taken-over' => 'Оваа страница за разговор е преземена [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Таблата за тек].',
	'log-name-flow' => 'Дневник на активности во текот',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|избриша}} [$4 објава] на [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|поврати}} [$4 објава] на [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|притаи}} [$4 објава] на [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|избриша}} [$4 објава] на [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|избриша}} [$4 тема] на [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|поврати}} [$4 тема] на [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|притаи}} [$4 тема] на [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|избриша}} [$4 тема] на [[$3]]',
	'flow-user-moderated' => 'Модериран корисник',
	'flow-edit-header-link' => 'Измени наслов',
	'flow-header-empty' => 'Страницава засега нема заглавие.',
	'flow-post-moderated-toggle-hide-show' => 'Прикажи го коментарот што го {{GENDER:$1|скри}} $2',
	'flow-post-moderated-toggle-delete-show' => 'Прикажи го коментарот што го {{GENDER:$1|избриша}} $2',
	'flow-post-moderated-toggle-suppress-show' => 'Прикажи го коментарот што го {{GENDER:$1|притаи}} $2',
	'flow-post-moderated-toggle-hide-hide' => 'Скриј го коментарот што го {{GENDER:$1|скри}} $2',
	'flow-post-moderated-toggle-delete-hide' => 'Скриј го коментарот што го {{GENDER:$1|избриша}} $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Скриј го коментарот што го {{GENDER:$1|притаи}} $2',
	'flow-hide-post-content' => 'Коментаров е {{GENDER:$1|скриен}} од $2',
	'flow-hide-title-content' => 'Темава е {{GENDER:$1|скриена}} од $2',
	'flow-hide-header-content' => '{{GENDER:$1|Скриено}} од $2',
	'flow-delete-post-content' => 'Коментаров е {{GENDER:$1|избришан}} од $2',
	'flow-delete-title-content' => 'Темава е {{GENDER:$1|избришана}} од $2',
	'flow-delete-header-content' => '{{GENDER:$1|Избришано}} од $2',
	'flow-suppress-post-content' => 'Коментаров е {{GENDER:$1|притаен}} од $2',
	'flow-suppress-title-content' => 'Темава е {{GENDER:$1|притаена}} од $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Притаено}} од $2',
	'flow-suppress-usertext' => '<em>Корисничкото име е притаено</em>',
	'flow-post-actions' => 'Дејства',
	'flow-topic-actions' => 'Дејства',
	'flow-cancel' => 'Откажи',
	'flow-preview' => 'Преглед',
	'flow-show-change' => 'Прикажи промени',
	'flow-last-modified-by' => 'Последно {{GENDER:$1|изменето}} од $1',
	'flow-stub-post-content' => "''Објавата не може да се добие поради техничка грешка.''",
	'flow-newtopic-title-placeholder' => 'Нова тема',
	'flow-newtopic-content-placeholder' => 'Додајте подробности, ако сакате',
	'flow-newtopic-header' => 'Додај нова тема',
	'flow-newtopic-save' => 'Додај тема',
	'flow-newtopic-start-placeholder' => 'Почнете нова тема',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Коментирај}} на „$2“',
	'flow-reply-placeholder' => '{{GENDER:$1|Одговорете му|Одговорете ѝ одговорите|Одговорете}} на $1',
	'flow-reply-submit' => '{{GENDER:$1|Одговори}}',
	'flow-reply-link' => '{{GENDER:$1|Одговори}}',
	'flow-thank-link' => '{{GENDER:$1|Заблагодари се}}',
	'flow-post-edited' => '$1 {{GENDER:$1|измени}} објава во $2',
	'flow-post-action-view' => 'Постојана врска',
	'flow-post-action-post-history' => 'Историја',
	'flow-post-action-suppress-post' => 'Притај',
	'flow-post-action-delete-post' => 'Избриши',
	'flow-post-action-hide-post' => 'Скриј',
	'flow-post-action-edit-post' => 'Уреди ја пораката',
	'flow-post-action-restore-post' => 'Поврати ја пораката',
	'flow-topic-action-view' => 'Постојана врска',
	'flow-topic-action-watchlist' => 'Набљудувања',
	'flow-topic-action-edit-title' => 'Уреди наслов',
	'flow-topic-action-history' => 'Историја',
	'flow-topic-action-hide-topic' => 'Скриј тема',
	'flow-topic-action-delete-topic' => 'Избриши тема',
	'flow-topic-action-suppress-topic' => 'Притај тема',
	'flow-topic-action-restore-topic' => 'Поврати тема',
	'flow-error-http' => 'Се јави грешка при поврзувањето со опслужувачот.',
	'flow-error-other' => 'Се појави неочекувана грешка.',
	'flow-error-external' => 'Се појави грешка.<br />Објаснувањето гласи: $1',
	'flow-error-edit-restricted' => 'Не ви е дозволено да ја менувате објавата.',
	'flow-error-external-multi' => 'Наидов на грешки.<br />$1',
	'flow-error-missing-content' => 'Пораката нема содржина. За да се зачува, мора да има содржина.',
	'flow-error-missing-title' => 'Темата нема наслов. Се бара наслов за да може да се зачува темата.',
	'flow-error-parsoid-failure' => 'Не можам да ја расчленам содржината поради проблем со Parsoid.',
	'flow-error-missing-replyto' => 'Нема зададено параметар „replyTo“. Овој параметар е потребен за да може да се даде одговор.',
	'flow-error-invalid-replyto' => 'Параметарот на „replyTo“ е неважечки. Не можев да ја најдам укажаната порака.',
	'flow-error-delete-failure' => 'Бришењето на ставката не успеа.',
	'flow-error-hide-failure' => 'Не успеав да ја скријам ставката.',
	'flow-error-missing-postId' => 'Нема зададено параметар „postId“. Овој параметар е потребен за работа со пораката.',
	'flow-error-invalid-postId' => 'Параметарот на „postId“ е неважечки. Не можев да ја најдам укажаната порака ($1).',
	'flow-error-restore-failure' => 'Повраќањето на ставката не успеа.',
	'flow-error-invalid-moderation-state' => 'Укажана е неважечка вредност за состојбата на модерација',
	'flow-error-invalid-moderation-reason' => 'Наведете причина за модерирањето',
	'flow-error-not-allowed' => 'Немате дозвола за да го извршите ова дејство',
	'flow-error-title-too-long' => 'Насловот на темата може да има највеќе {{PLURAL:$1|еден бајт|$1 бајти}}.',
	'flow-error-no-existing-workflow' => 'Овој работен тек сè уште не постои.',
	'flow-error-not-a-post' => 'Насловот на темата не може да се зачува како објава.',
	'flow-error-missing-header-content' => 'Заглавието нема содржина. Ви треба содржина за да можете да го зачувате.',
	'flow-error-missing-prev-revision-identifier' => 'Недостасува назнака на претходната ревизија.',
	'flow-error-prev-revision-mismatch' => 'Објавава ја измени друг корисник пред неколку секунди. Дали сте сигурни дека сакате да презапишете врз оваа последна промена?',
	'flow-error-prev-revision-does-not-exist' => 'Не можев да ја надам претходната ревизија.',
	'flow-error-default' => 'Се појави грешка.',
	'flow-error-invalid-input' => 'Укажана е неважечка вредност за вчитување на содржините на текот.',
	'flow-error-invalid-title' => 'Укажан е неважечки наслов на страницата.',
	'flow-error-fail-load-history' => 'Не успеав да ја вчитам содржината на историјата.',
	'flow-error-missing-revision' => 'Не можев да ја пронајдам ревизијата од која би ја вчитал содржината на текот.',
	'flow-error-fail-commit' => 'Не успеав да ја зачувам содржината на текот.',
	'flow-error-insufficient-permission' => 'Немате доволно дозволи за пристап до содржината.',
	'flow-error-revision-comparison' => 'Операцијата за разлика може да се врши само кога две ревизии припаѓаат на иста објава.',
	'flow-error-missing-topic-title' => 'Не можев да го најдам насловот на темата во тековниот работен тек.',
	'flow-error-fail-load-data' => 'Не успеав да ги вчитам побараните податоци.',
	'flow-error-invalid-workflow' => 'Не успеав да го најдам бараниот работен тек.',
	'flow-error-process-data' => 'Се појави грешка при обработката на податоците во вашето барање.',
	'flow-error-process-wikitext' => 'Се појави грешка при обработката на претворањето на HTML/викитекстот.',
	'flow-error-no-index' => 'Не успеав да најдам индекс за пребарување на податоците.',
	'flow-edit-header-submit' => 'Зачувај заглавие',
	'flow-edit-header-submit-overwrite' => 'Презапиши врз заглавието',
	'flow-edit-title-submit' => 'Измени наслов',
	'flow-edit-title-submit-overwrite' => 'Презапиши врз насловот',
	'flow-edit-post-submit' => 'Спроведи измени',
	'flow-edit-post-submit-overwrite' => 'Презапиши врз промените',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|измени}} [$3 коментар] на $4.',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|доидаде}} [$3 коментар] на $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '{{PLURAL:$1|Додаден|Додадени}} <strong>{{PLURAL:$1|еден коментар|$1 коментари}}</strong>.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|ја создаде}} темата [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|го смени}} насловот на темата од $5 во [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|го создаде}} заглавието.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|го измени}} заглавието.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|скри}} [$4 коментар] на на $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|избриша}} [$4 коментар] на $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|притаи}} [$4 коментар] на $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|поврати}} [$4 коментар] на $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|ја скри}} [$4 темата] на $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|ја избриша}} [$4 темата] $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|ја притаи}} [$4 темата] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|ја поврати}} [$4 темата] $6 (<em>$5</em>).',
	'flow-board-history' => 'Историја на „$1“',
	'flow-topic-history' => 'Историја на темата „$1“',
	'flow-post-history' => 'Историја на објавите — Коментар од {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Последниве 4 часа',
	'flow-history-day' => 'Денес',
	'flow-history-week' => 'Минатата седмица',
	'flow-history-pages-topic' => 'Фигурира на [$1 таблата „$2“]',
	'flow-history-pages-post' => 'Фигурира на [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|Темава ја започна $3|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} и {{PLURAL:$2|уште еден|$2 други}}|0=Досега никој не учествувал|2={{GENDER:$3|$3}} и {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} и {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 коментар|$1 коментари|0={{GENDER:$2|Бидете први}} со коментар!}}',
	'flow-comment-restored' => 'Повратен коментар',
	'flow-comment-deleted' => 'Избришан коментар',
	'flow-comment-hidden' => 'Скриен коментар',
	'flow-comment-moderated' => 'Модериран коментар',
	'flow-paging-rev' => 'Најнови теми',
	'flow-paging-fwd' => 'Постари теми',
	'flow-last-modified' => 'Последна измена: $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|ви одговори}} на вашата <span class="plainlinks">[$5 објава]</span> во „$2“ на „$4“.',
	'flow-notification-reply-bundle' => '$1 и $5 уште {{PLURAL:$6|еден друг|$5 други}} {{GENDER:$1|ви одговорија}} на вашата <span class="plainlinks">[$4 објава]</span>  во „$2“ на „$3“.',
	'flow-notification-edit' => '$1 {{GENDER:$1|ви ја измени}} измени <span class="plainlinks">[$5 објава]</span> во „$2“ на [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 и $5 {{PLURAL:$6|уште еден друг|уште $5 други}} {{GENDER:$1|изменија}} <span class="plainlinks">[$4 post]</span> во „$2“ на „$3“.',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|создаде}} <span class="plainlinks">[$5 нова тема]</span> во [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 го {{GENDER:$1|смени}} насловот на <span class="plainlinks">[$2 $3]</span> во „$4“ на [[$5|$6]]',
	'flow-notification-mention' => '$1 ве спомна во {{GENDER:$1|неговата|нејзината|неговата}} <span class="plainlinks">[$2 објава]</span>  во „$3“ на „$4“',
	'flow-notification-link-text-view-post' => 'Погл. објавата',
	'flow-notification-link-text-view-board' => 'Погл. таблата',
	'flow-notification-link-text-view-topic' => 'Погл. темата',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ви одговори}} на објавата',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|ви одговори}} на вашата објава во „$2“ на „$3“',
	'flow-notification-reply-email-batch-bundle-body' => '$1 и уште {{PLURAL:$5|еден друг|$4 други}} {{GENDER:$1|ви одговорија}} на вашата објава во „$2“ на „$3“',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|ве спомна}} на „$2“',
	'flow-notification-mention-email-batch-body' => '$1 ве спомна во {{GENDER:$1|неговата|нејзината|неговата}} објава во „$2“ на „$3“',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|измени}} објава',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|измени}} објава во „$2“ на „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 и {{PLURAL:$5|уште еден друг|уште $4 други}} {{GENDER:$1|ја изменија}} вашата објава во „$2“ на „$3“',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|ја преименуваше}} вашата тема',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|ја преименуваше}} вашата тема „$2“ во „$3“ на „$4“',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|создаде}} нова тема на „$2“',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|создаде}} нова тема со наслов „$2“ на $3',
	'echo-category-title-flow-discussion' => 'Тек',
	'echo-pref-tooltip-flow-discussion' => 'Извести ме кога во Тек ќе се случат дејства поврзани со мене.',
	'flow-link-post' => 'објава',
	'flow-link-topic' => 'тема',
	'flow-link-history' => 'историја',
	'flow-moderation-reason-placeholder' => 'Тука внесете причина',
	'flow-moderation-title-suppress-post' => 'Да ја притаам објавата?',
	'flow-moderation-title-delete-post' => 'Да ја избришам објавата?',
	'flow-moderation-title-hide-post' => 'Да ја скријам објавата?',
	'flow-moderation-title-restore-post' => 'Да ја повратам објавата?',
	'flow-moderation-intro-suppress-post' => '{{GENDER:$3|Објаснете}} зошто ја притајувате објавава.',
	'flow-moderation-intro-delete-post' => '{{GENDER:$3|Објаснете}} зошто ја бришење објавава.',
	'flow-moderation-intro-hide-post' => '{{GENDER:$3|Објаснете}} зошто ја скривате објавава.',
	'flow-moderation-intro-restore-post' => '{{GENDER:$3|Објаснете}} зошто ја повраќате објавава.',
	'flow-moderation-confirm-suppress-post' => 'Притај',
	'flow-moderation-confirm-delete-post' => 'Избриши',
	'flow-moderation-confirm-hide-post' => 'Скриј',
	'flow-moderation-confirm-restore-post' => 'Поврати',
	'flow-moderation-confirmation-suppress-post' => 'Објавата е успешно притаена. {{GENDER:$2|Ви препорачуваме}} на корисникот $1 да му дадете образложение и/или совет за објавата.',
	'flow-moderation-confirmation-delete-post' => 'Објавата е успешно избришана. {{GENDER:$2|Ви препорачуваме}} на корисникот $1 да му дадете образложение и/или совет за објавата.',
	'flow-moderation-confirmation-hide-post' => 'Објавата е успешно скриена. {{GENDER:$2|Ви препорачуваме}} на корисникот $1 да му дадете образложение и/или совет за објавата.',
	'flow-moderation-confirmation-restore-post' => 'Успешно ја повративте објавата.',
	'flow-moderation-title-suppress-topic' => 'Да ја притаам темата?',
	'flow-moderation-title-delete-topic' => 'Да ја избришам темата?',
	'flow-moderation-title-hide-topic' => 'Да ја скријам темата?',
	'flow-moderation-title-restore-topic' => 'Да ја повратам темата?',
	'flow-moderation-intro-suppress-topic' => '{{GENDER:$3|Објаснете}} зошто ја притајувате темава.',
	'flow-moderation-intro-delete-topic' => '{{GENDER:$3|Објаснете}} зошто ја бришете темава.',
	'flow-moderation-intro-hide-topic' => '{{GENDER:$3|Објаснете}} зошто ја скривате темава.',
	'flow-moderation-intro-restore-topic' => '{{GENDER:$3|Објаснете}} зошто ја повраќате темава.',
	'flow-moderation-confirm-suppress-topic' => 'Притај',
	'flow-moderation-confirm-delete-topic' => 'Избриши',
	'flow-moderation-confirm-hide-topic' => 'Скриј',
	'flow-moderation-confirm-restore-topic' => 'Поврати',
	'flow-moderation-confirmation-suppress-topic' => 'Темата е успешно притаена. {{GENDER:$2|Ви препорачуваме}} на корисникот $1 да му дадете образложение и/или совет за темата.',
	'flow-moderation-confirmation-delete-topic' => 'Темата е успешно избришана. {{GENDER:$2|Ви препорачуваме}} на корисникот $1 да му дадете образложение и/или совет за темата.',
	'flow-moderation-confirmation-hide-topic' => 'Темата е успешно скриена. {{GENDER:$2|Ви препорачуваме}} на корисникот $1 да му дадете образложение и/или совет за темата.',
	'flow-moderation-confirmation-restore-topic' => 'Успешно ја повративте темата.',
	'flow-topic-permalink-warning' => 'Темата е започната на [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Темата е започната на [$2 таблата на {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Ова е постојана врска со една верзија на објавава.
Оваа верзија е од $1.
Можете да ги погледате [$5 разликите од претходната верзија], или пак другите верзии во [$4 историјата на објавата].',
	'flow-revision-permalink-warning-post-first' => 'Ова е постојана врска до една верзија на објавава.
Можете да ги погледате подоцнежните верзии во [$4 историјата на објавата].',
	'flow-revision-permalink-warning-header' => 'Ова е постојана врска до една верзија на заглавието.
Оваа верзиаја е од $1. Можете да ги погледате [$3 разликите од претходната верзија], или пак другите верзии во [$2 историјата].',
	'flow-revision-permalink-warning-header-first' => 'Ова е постојана врска до првата верзија на заглавието.
Можете да ги погледате подоцнежните верзии во [$2 историјата].',
	'flow-compare-revisions-revision-header' => 'Верзија на {{GENDER:$2|$2}} од $1',
	'flow-compare-revisions-header-post' => 'На страницава се прикажани {{GENDER:$3|разликите}} помеѓу две верзии на објава на $3 во темата „[$5 $2]“ на [$4 $1].
Можете да ги погледате другите верзии на објавата во [$6 нејзината историја].',
	'flow-compare-revisions-header-header' => 'На страницава се прикажани {{GENDER:$2|промените}} помеѓу две верзии на заглавието на [$3 $1].
Другите верзии на заглавието можете да ги видите во [$4 неговата историја].',
	'flow-topic-collapsed-one-line' => 'Мал приказ',
	'flow-topic-collapsed-full' => 'Расклопен приказ',
	'flow-topic-complete' => 'Целосен приказ',
	'flow-terms-of-use-new-topic' => 'Стискајќи на „{{int:flow-newtopic-save}}“, се согласувате со условите на употреба на ова вики.',
	'flow-terms-of-use-reply' => 'Стискајќи на „{{int:flow-reply-submit}}“, се согласувате со условите на употреба на ова вики.',
	'flow-terms-of-use-edit' => 'Зачувувајќи ги промените, се согласувате со условите на употреба на ова вики.',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Suresh.balasubra
 */
$messages['ml'] = array(
	'flow-desc' => 'പ്രവൃത്തി കൈകാര്യ സൗകര്യം',
	'flow-newtopic-title-placeholder' => 'പുതിയ വിഷയം',
	'flow-post-action-suppress-post' => 'ഒതുക്കുക',
	'flow-post-action-delete-post' => 'മായ്ക്കുക',
	'flow-post-action-hide-post' => 'മറയ്ക്കുക',
	'flow-topic-action-hide-topic' => 'വിഷയം മറയ്ക്കുക',
	'flow-topic-action-delete-topic' => 'വിഷയം മായ്ക്കുക',
	'flow-topic-action-suppress-topic' => 'വിഷയം ഒതുക്കുക',
	'flow-topic-action-restore-topic' => 'വിഷയം പുനഃസ്ഥാപിക്കുക',
	'flow-error-other' => 'അപ്രതീക്ഷിതമായ പിഴവ് ഉണ്ടായി.',
	'flow-moderation-title-suppress-topic' => 'വിഷയം ഒതുക്കണോ?',
	'flow-moderation-title-delete-topic' => 'വിഷയം മായ്ക്കണോ?',
	'flow-moderation-title-hide-topic' => 'വിഷയം മറയ്ക്കണോ?',
	'flow-moderation-title-restore-topic' => 'വിഷയം പുനഃസ്ഥാപിക്കണോ?',
	'flow-moderation-intro-suppress-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം ഒതുക്കേണ്ടതെന്ന് ദയവായി വിശദീകരിക്കുക.', # Fuzzy
	'flow-moderation-intro-delete-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം മായ്ക്കുന്നതെന്ന് വിശദീകരിക്കുക.', # Fuzzy
	'flow-moderation-intro-hide-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം മറയ്ക്കുന്നതെന്ന് വിശദീകരിക്കുക.', # Fuzzy
	'flow-moderation-intro-restore-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം പുനഃസ്ഥാപിക്കുന്നതെന്ന് ദയവായി വിശദീകരിക്കുക.', # Fuzzy
	'flow-moderation-confirm-suppress-topic' => 'ഒതുക്കുക',
	'flow-moderation-confirm-delete-topic' => 'മായ്ക്കുക',
	'flow-moderation-confirm-hide-topic' => 'മറയ്ക്കുക',
	'flow-moderation-confirm-restore-topic' => 'പുനഃസ്ഥാപിക്കുക',
	'flow-moderation-confirmation-restore-topic' => 'താങ്കൾ ഈ വിഷയം വിജയകരമായി പുനഃസ്ഥാപിച്ചിരിക്കുന്നു.', # Fuzzy
);

/** Marathi (मराठी)
 * @author V.narsikar
 */
$messages['mr'] = array(
	'flow-newtopic-title-placeholder' => 'संदेशाचा विषय', # Fuzzy
	'flow-post-action-post-history' => 'इतिहास',
	'flow-post-action-edit-post' => 'संपादन',
	'flow-topic-action-history' => 'इतिहास',
	'flow-error-external' => 'आपले उत्तर जतन करण्यात त्रूटी घडली.<br />मिळालेला त्रूटी संदेश असा होता: $1',
	'flow-error-external-multi' => 'आपले उत्तर जतन करण्यात त्रूटी आढळल्या.आपले उत्तर जतन झाले नाही.<br />$1', # Fuzzy
	'flow-error-missing-title' => 'विषयास मथळा नाही.एखाद्या विषयास जतन करावयाचे तर मथळा हवा.',
	'flow-error-prev-revision-mismatch' => 'काही सेकंदांपूर्वी दुसऱ्या सदस्याने हे टपालन संपादन केले आहे.अलीकडील बदलांवर आपणास उपरीलेखन(ओव्हरराईट) करावयाचे हे नक्की काय?',
	'flow-error-prev-revision-does-not-exist' => 'मागील आवृत्ती शोधता आली नाही.',
	'flow-edit-header-submit-overwrite' => 'शीर्षाचे उपरीलेखन करा',
	'flow-edit-title-submit-overwrite' => 'शीर्षकाचे उपरीलेखन करा',
	'flow-edit-post-submit-overwrite' => 'बदलांवर उपरीलेखन करा',
	'flow-notification-mention-email-subject' => '$1 ने "$2"वर आपला {{GENDER:$1|उल्लेख केला}}',
	'flow-notification-newtopic-email-subject' => '$1 ने "$2" वर एक नविन विषय {{GENDER:$1|तयार केला}}',
	'flow-terms-of-use-new-topic' => '"{{int:flow-newtopic-save}}" टिचकण्याने,आपण या विकिच्या वापरण्याच्या अटी मान्य करीत आहात.',
	'flow-terms-of-use-reply' => '"{{int:flow-reply-submit}}" टिचकण्याने, आपण या विकिच्या \'वापरण्याच्या अटी\' मान्य करीत आहात.',
	'flow-terms-of-use-edit' => "आपले बदल 'जतन करण्याने',आपण या विकिच्या 'वापरण्याच्या अटी' मान्य करीत आहात.",
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'flow-post-moderated-toggle-hide-show' => 'Paparkan komen yang {{GENDER:$1|disembunyikan}} oleh $2',
	'flow-post-moderated-toggle-delete-show' => 'Paparkan komen yang {{GENDER:$1|dihapuskan}} oleh $2',
	'flow-post-moderated-toggle-suppress-show' => 'Paparkan komen yang {{GENDER:$1|disekat}} oleh $2',
	'flow-post-moderated-toggle-hide-hide' => 'Sembunyikan komen yang {{GENDER:$1|disembunyikan}} oleh $2',
	'flow-post-moderated-toggle-delete-hide' => 'Sembunyikan komen yang {{GENDER:$1|dihapuskan}} oleh $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Sembunyikan komen yang {{GENDER:$1|disekat}} oleh $2',
	'flow-stub-post-content' => "''Disebabkan ralat teknikal, kiriman ini tidak dapat diperoleh.''",
	'flow-post-action-post-history' => 'Sejarah',
	'flow-post-action-edit-post' => 'Sunting',
	'flow-topic-action-history' => 'Sejarah',
	'flow-notification-reply' => '$1 telah {{GENDER:$1|membalas}} <span class="plainlinks">[$5 kiriman]</span> anda di "$2" pada "$4".',
	'flow-notification-reply-bundle' => '$1 dan $5 {{PLURAL:$6|orang lain}} telah {{GENDER:$1|membalas}} <span class="plainlinks">[$4 kiriman]</span> anda di "$2" pada "$3".',
	'flow-notification-edit' => '$1 telah {{GENDER:$1|menyunting}} suatu <span class="plainlinks">[$5 kiriman]</span> di "$2" pada [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 dan $5 {{PLURAL:$6|orang lain}} telah {{GENDER:$1|menyunting}} sepucuk <span class="plainlinks">[$4 kiriman]</span> di "$2" pada "$3".',
	'flow-notification-newtopic' => '$1 telah {{GENDER:$1|membuka}} <span class="plainlinks">[$5 topik baru]</span> pada [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 telah {{GENDER:$1|menukar}} tajuk <span class="plainlinks">[$2 $3]</span> kepada "$4" pada [[$5|$6]].',
	'flow-notification-mention' => '$1 telah {{GENDER:$1|menyebut}} nama anda di <span class="plainlinks">[$2 kirimannya]</span> di "$3" pada "$4".',
	'flow-notification-reply-email-batch-body' => '$1 telah {{GENDER:$1|membalas}} kiriman anda di "$2" pada "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 dan $4 {{PLURAL:$5|orang lain}} telah {{GENDER:$1|membalas}} kiriman anda di "$2" pada "$3"',
	'flow-notification-mention-email-subject' => '$1 telah {{GENDER:$1|menyebut}} nama anda di "$2"',
	'flow-notification-edit-email-batch-body' => '$1 telah {{GENDER:$1|menyunting}} suatu kiriman di "$2" pada "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 dan $4 {{PLURAL:$5|orang lain}} telah {{GENDER:$1|menyunting}} suatu kiriman di "$2" pada "$3"',
	'flow-notification-newtopic-email-subject' => '$1 telah {{GENDER:$1|membuka}} topik baru di "$2"',
	'flow-moderation-confirmation-restore-post' => 'Anda telah berjaya memulihkan kiriman di atas.',
	'flow-moderation-confirmation-delete-topic' => 'Topik ini berjaya dihapuskan.
Apa kata anda {{GENDER:$2|memaklum balas}} $1 mengenai topik ini?',
	'flow-moderation-confirmation-restore-topic' => 'Anda telah berjaya memulihkan topik ini.',
	'flow-topic-collapsed-one-line' => 'Paparan kecil',
	'flow-topic-collapsed-full' => 'Paparan terlipat',
	'flow-topic-complete' => 'Paparan penuh',
);

/** Neapolitan (Napulitano)
 * @author Chelin
 */
$messages['nap'] = array(
	'flow-show-change' => "Vere 'e cagnamiente",
	'flow-post-action-post-history' => 'Cronologgia',
	'flow-post-action-edit-post' => 'Càgna',
);

/** Norwegian Bokmål (norsk bokmål)
 * @author Danmichaelo
 */
$messages['nb'] = array(
	'log-name-flow' => 'Flow-aktivitetslogg',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|slettet}} et [$4 innlegg] på [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|gjenopprettet}} et [$4 innlegg] på [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|sensurerte}} et [$4 innlegg] på [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|slettet}} et [$4 innlegg] på [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|slettet}} et [$4 innlegg] på [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|gjenopprettet}} et [$4 innlegg] på [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|sensurerte}} et [$4 innlegg] på [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|slettet}} et [$4 innlegg] på [[$3]]',
	'flow-user-moderated' => 'Moderert bruker',
	'flow-edit-header-link' => 'Rediger overskrift',
	'flow-header-empty' => 'Denne diskusjonssiden har ingen overskrift.',
	'flow-post-moderated-toggle-show' => '[Vis]',
	'flow-post-moderated-toggle-hide' => '[Skjul]',
	'flow-suppress-usertext' => '<em>Brukernavn sensurert</em>',
	'flow-post-actions' => 'Handlinger',
	'flow-topic-actions' => 'Handlinger',
	'flow-cancel' => 'Avbryt',
	'flow-preview' => 'Forhåndsvis',
	'flow-newtopic-title-placeholder' => 'Nytt emne',
	'flow-newtopic-content-placeholder' => 'Skriv noen ord om du vil',
	'flow-newtopic-header' => 'Legg til et nytt emne',
	'flow-newtopic-save' => 'Legg til diskusjon',
	'flow-newtopic-start-placeholder' => 'Start en ny diskusjon',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Kommentér}} «$2»',
	'flow-reply-placeholder' => '{{GENDER:$1|Svar}} til $1',
	'flow-reply-submit' => '{{GENDER:$1|Svar}}',
	'flow-reply-link' => '{{GENDER:$1|Svar}}',
	'flow-thank-link' => '{{GENDER:$1|Takk}}',
	'flow-edit-post-submit' => 'Send inn endringer',
	'flow-post-edited' => 'Melding {{GENDER:$1|redigert}} av $1 $2',
	'flow-post-action-view' => 'Permanent lenke',
	'flow-post-action-post-history' => 'Meldingshistorikk',
	'flow-post-action-suppress-post' => 'Sensurer',
	'flow-post-action-delete-post' => 'Slett',
	'flow-post-action-hide-post' => 'Skjul',
	'flow-post-action-edit-post' => 'Rediger melding',
	'flow-post-action-edit' => 'Rediger',
	'flow-post-action-restore-post' => 'Gjenopprett melding',
	'flow-topic-action-view' => 'Permalenke',
	'flow-topic-action-watchlist' => 'Overvåkningsliste',
	'flow-topic-action-edit-title' => 'Rediger tittel',
	'flow-topic-action-history' => 'Emnehistorikk',
	'flow-topic-action-hide-topic' => 'Skjul diskusjon',
	'flow-topic-action-delete-topic' => 'Slett diskusjon',
	'flow-topic-action-suppress-topic' => 'Sensurer diskusjon',
	'flow-topic-action-restore-topic' => 'Gjenopprett diskusjon',
	'flow-error-http' => 'Det oppsto en feil ved kontakt med serveren.',
	'flow-error-other' => 'Det oppsto en ukjent feil.',
	'flow-error-external' => 'Det oppsto en feil.<br />Feilmeldingen var: $1',
	'flow-error-edit-restricted' => 'Du har ikke tilgang til å redigere denne meldingen.',
	'flow-error-external-multi' => 'Feil oppsto under lagring av meldingen.<br />$1',
	'flow-error-missing-content' => 'Meldingen har ikke noe innhold. Innhold kreves for å lagre en melding.',
	'flow-error-missing-title' => 'Meldingen har ingen tittel. En tittel kreves for å lagre en diskusjon.',
	'flow-error-parsoid-failure' => 'Innholdet kunne ikke parseres pga. et Parsord-problem.',
	'flow-error-missing-replyto' => 'Ingen "replyTo"-parameter ble sendt inn. Parameteren er påkrevd for "reply"-handlingen.',
	'flow-error-invalid-replyto' => 'Parameteren "replyTo" var ugyldig. Det angitte innlegget ble ikke funnet.',
	'flow-error-delete-failure' => 'Sletting av dette innlegget feilet.',
	'flow-error-hide-failure' => 'Skjuling av dette innlegget feilet.',
	'flow-error-missing-postId' => 'Ingen "postId"-parameter ble sendt inn. Parameteren er påkrevd for å redigere et innlegg.',
	'flow-error-invalid-postId' => 'Parameteren «postId» var ugyldig. Det angitte innlegget ($1) ble ikke funnet.',
	'flow-error-restore-failure' => 'Gjenoppretting av dette innlegget feilet.',
	'flow-error-invalid-moderation-state' => 'En ugyldig verdi ble gitt for moderationState',
	'flow-error-invalid-moderation-reason' => 'Vennligst oppgi en grunn for modereringen',
	'flow-error-not-allowed' => 'Manglende rettigheter til å utføre denne handlingen',
	'flow-edit-header-submit' => 'Lagre overskrift',
	'flow-edit-title-submit' => 'Endre tittel',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|redigerte}} en [$3 kommentar].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|la inn}} en [$3 kommentar].',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|kommentar|kommentarer}}</strong> {{PLURAL:$1|ble}} lagt til.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|opprettet}} emnet [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|redigerte}} emnetittelen fra $5 til [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|opprettet}} overskrift.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|redigerte}} overskrift.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|skjulte}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|slettet}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|sensurerte}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|gjenopprettet}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|skjulte}} [$4 diskusjonen] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|slettet}} [$4 diskusjonen] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|sensurerte}} [$4 diskusjonen] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|gjenopprettet}} [$4 diskusjonen] (<em>$5</em>).',
	'flow-board-history' => 'Historikk for «$1»',
	'flow-topic-history' => '«$1» Samtalehistorikk',
	'flow-history-last4' => 'Siste 4 timer',
	'flow-history-day' => 'I dag',
	'flow-history-week' => 'Forrige uke',
	'flow-topic-participants' => '{{PLURAL:$1|$3 startet denne diskusjonen|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} og {{PLURAL:$2|annen|andre}}|0=Ingen deltakelse enda|2={{GENDER:$3|$3}} og {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} og {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Kommentér ($1)|Kommentarer ($1)|0=Bli den første til å kommentere!}}', # Fuzzy
	'flow-comment-restored' => 'Gjenopprettet kommentar',
	'flow-comment-deleted' => 'Slettet kommentar',
	'flow-comment-hidden' => 'Skjult kommentar',
	'flow-comment-moderated' => 'Modererte melding',
	'flow-paging-rev' => 'Mer aktuelle samtaler',
	'flow-paging-fwd' => 'Eldre samtaler',
	'flow-last-modified' => 'Sist endret for rundt $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|svarte}} på [$5 meldingen] din under $2 på «$4».',
	'flow-notification-reply-bundle' => '$1 og $5 {{PLURAL:$6|annen|andre}} {{GENDER:$1|svarte}} på [$4 innlegget] ditt under $2 på «$3».',
	'flow-notification-edit' => '$1 {{GENDER:$1|redigerte}} en [$5 melding] i «$2» på [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 og $5 {{PLURAL:$6|annen|andre}} {{GENDER:$1|redigerte}} et [$4 innlegg] under $2 på «$3».',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|startet}} en [$5 ny samtale] på [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|endret}} overskriften for [$2 $3] til «$4» på [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|nevnte}} deg i [$2 innlegget] {{GENDER:$1|hans|hennes|sitt}} under «$3» på «$4»',
	'flow-notification-link-text-view-post' => 'Vis innlegg',
	'flow-notification-link-text-view-topic' => 'Vis samtale',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|svarte}} på meldingen din',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|svarte}} på innlegget ditt under $2 på «$3»',
	'flow-notification-reply-email-batch-bundle-body' => '$1 og $4 {{PLURAL:$5|annen|andre}} {{GENDER:$1|svarte}} på innlegget ditt i $2 på «$3»',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|nevnte}} deg på $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|nevnte}} deg i innlegget {{GENDER:$1|hans|hennes|sitt}} i «$2» på «$3»',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|redigerte}} innlegget ditt', # Fuzzy
	'echo-category-title-flow-discussion' => 'Flow',
	'flow-link-post' => 'innlegg',
	'flow-link-topic' => 'diskusjon',
	'flow-link-history' => 'historikk',
	'flow-moderation-reason-placeholder' => 'Skriv inn årsaken her',
	'flow-moderation-title-suppress-post' => 'Sensurer melding',
	'flow-moderation-title-delete-post' => 'Slett melding',
	'flow-moderation-title-hide-post' => 'Skjul melding',
	'flow-moderation-title-restore-post' => 'Gjenopprett melding.',
	'flow-moderation-intro-suppress-post' => 'Bekreft at du ønsker å sensurere melding av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.', # Fuzzy
	'flow-moderation-intro-delete-post' => 'Bekreft at du ønsker å slette meldingen av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.', # Fuzzy
	'flow-moderation-intro-hide-post' => 'Bekreft at du ønsker å skjule meldingen av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.', # Fuzzy
	'flow-moderation-intro-restore-post' => 'Bekreft at du ønsker å gjenopprette meldingen av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.', # Fuzzy
	'flow-moderation-confirm-suppress-post' => 'Sensurer',
	'flow-moderation-confirm-delete-post' => 'Slett',
	'flow-moderation-confirm-hide-post' => 'Skjul',
	'flow-moderation-confirm-restore-post' => 'Gjenopprett',
	'flow-moderation-confirmation-restore-post' => 'Du har gjenopprettet dette innlegget.', # Fuzzy
	'flow-moderation-title-suppress-topic' => 'Sensurer diskusjon?',
	'flow-moderation-title-delete-topic' => 'Slett diskusjon?',
	'flow-moderation-title-hide-topic' => 'Skjul diskusjon?',
	'flow-moderation-title-restore-topic' => 'Gjenopprett diskusjon?',
	'flow-moderation-intro-suppress-topic' => 'Forklar hvorfor du sensurerer denne diskusjonen.', # Fuzzy
	'flow-moderation-intro-delete-topic' => 'Forklar hvorfor du sletter denne diskusjonen.', # Fuzzy
	'flow-moderation-intro-hide-topic' => 'Forklar hvorfor du skjuler denne diskusjonen.', # Fuzzy
	'flow-moderation-intro-restore-topic' => 'Forklar hvorfor du gjenoppretter denne diskusjonen.', # Fuzzy
	'flow-moderation-confirm-suppress-topic' => 'Sensurer',
	'flow-moderation-confirm-delete-topic' => 'Slett',
	'flow-moderation-confirm-hide-topic' => 'Skjul',
	'flow-moderation-confirm-restore-topic' => 'Gjenopprett',
	'flow-topic-permalink-warning' => 'Denne diskusjonen startet på [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Denne diskusjonen startet på [$2 {{GENDER:$1|$1}}s diskusjonsside]',
);

/** Nepali (नेपाली)
 * @author सरोज कुमार ढकाल
 */
$messages['ne'] = array(
	'flow-newtopic-title-placeholder' => 'नयाँ विषय',
	'flow-post-action-suppress-post' => 'दबाउने',
	'flow-post-action-delete-post' => 'हटाउने',
	'flow-post-action-hide-post' => 'लुकाउनुहोस्',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|टिप्पणी|टिप्पणीहरू}}</strong> {{PLURAL:$1|थपिएको|थपिएका}} थिए ।',
	'flow-moderation-confirm-suppress-post' => 'दबाउने',
	'flow-moderation-confirm-delete-post' => 'मेट्ने',
	'flow-moderation-confirm-hide-post' => 'लुकाउनुहोस्',
	'flow-moderation-confirm-restore-post' => 'पूर्वावस्थामा ल्याउनुहोस्',
);

/** Dutch (Nederlands)
 * @author Arent
 * @author AvatarTeam
 * @author Breghtje
 * @author Effeietsanders
 * @author Krinkle
 * @author Niknetniko
 * @author SPQRobin
 * @author Siebrand
 * @author Sjoerddebruin
 * @author Southparkfan
 * @author TBloemink
 */
$messages['nl'] = array(
	'flow-desc' => 'Workflowmanagementsysteem',
	'flow-talk-taken-over' => 'Deze overlegpagina is overgenomen door een [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal prikbord van Flow].',
	'log-name-flow' => 'Logboek Flow',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|heeft}} een [$4 bericht] verwijderd van [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|heeft}} een [$4 bericht] teruggeplaatst op [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|heeft}} een [$4 bericht] onderdrukt op [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|heeft}} een [$4 bericht] verwijderd van [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] verwijderd op [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] teruggeplaatst op [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] onderdrukt op [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] verwijderd op [[$3]]',
	'flow-user-moderated' => 'Gemodereerde gebruiker',
	'flow-edit-header-link' => 'Koptekst bewerken',
	'flow-header-empty' => 'Deze overlegpagina heeft momenteel geen koptekst.',
	'flow-post-moderated-toggle-hide-show' => 'Toon commentaar {{GENDER:$1|verborgen}} door $2',
	'flow-post-moderated-toggle-delete-show' => 'Toon commentaar {{GENDER:$1|verwijderd}} door $2',
	'flow-post-moderated-toggle-suppress-show' => 'Toon commentaar {{GENDER:$1|onderdrukt}} door $2',
	'flow-post-moderated-toggle-hide-hide' => 'Verberg commentaar {{GENDER:$1|verborgen}} door $2',
	'flow-post-moderated-toggle-delete-hide' => 'Verberg commentaar {{GENDER:$1|verwijderd}} door $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Verberg commentaar {{GENDER:$1|onderdrukt}} door $2',
	'flow-hide-post-content' => 'Deze opmerking is {{GENDER:$1|verborgen}} door $2',
	'flow-hide-title-content' => 'Dit onderwerp is {{GENDER:$1|verborgen}} door $2',
	'flow-hide-header-content' => '{{GENDER:$1|Verborgen}} door $2',
	'flow-delete-post-content' => 'Deze opmerking is {{GENDER:$1|verwijderd}} door $2',
	'flow-delete-title-content' => 'Dit onderwerp is {{GENDER:$1|verwijderd}} door $2',
	'flow-delete-header-content' => '{{GENDER:$1|Verwijderd}} door $2',
	'flow-suppress-post-content' => 'Deze opmerking is {{GENDER:$1|onderdrukt}} door $2',
	'flow-suppress-title-content' => 'Dit onderwerp is {{GENDER:$1|onderdrukt}} door $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Onderdrukt}} door $2',
	'flow-suppress-usertext' => '<em>Gebruikersnaam onderdrukt</em>',
	'flow-post-actions' => 'Handelingen',
	'flow-topic-actions' => 'Handelingen',
	'flow-cancel' => 'Annuleren',
	'flow-preview' => 'Voorvertoning',
	'flow-show-change' => 'Laat wijzigingen zien',
	'flow-last-modified-by' => 'Laatst {{GENDER:$1|bewerkt}} door $1',
	'flow-stub-post-content' => "''Als gevolg van een technische fout kon dit bericht niet worden opgehaald.''",
	'flow-newtopic-title-placeholder' => 'Nieuw onderwerp',
	'flow-newtopic-content-placeholder' => 'Voeg nog wat details toe als u dat wilt',
	'flow-newtopic-header' => 'Nieuw onderwerp toevoegen',
	'flow-newtopic-save' => 'Onderwerp toevoegen',
	'flow-newtopic-start-placeholder' => 'Nieuw onderwerp',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Reageren}} op "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|Reageren}} op $1',
	'flow-reply-submit' => '{{GENDER:$1|Reageren}}',
	'flow-reply-link' => '{{GENDER:$1|Reageren}}',
	'flow-thank-link' => '{{GENDER:$1|Bedanken}}',
	'flow-post-edited' => 'Bericht $2 {{GENDER:$1|bewerkt}} door $1',
	'flow-post-action-view' => 'Permanente koppeling',
	'flow-post-action-post-history' => 'Geschiedenis',
	'flow-post-action-suppress-post' => 'Onderdrukken',
	'flow-post-action-delete-post' => 'Verwijderen',
	'flow-post-action-hide-post' => 'Verbergen',
	'flow-post-action-edit-post' => 'Bewerken',
	'flow-post-action-restore-post' => 'Bericht terugplaatsen',
	'flow-topic-action-view' => 'Permanente koppeling',
	'flow-topic-action-watchlist' => 'Volglijst',
	'flow-topic-action-edit-title' => 'Titel wijzigen',
	'flow-topic-action-history' => 'Geschiedenis',
	'flow-topic-action-hide-topic' => 'Onderwerp verbergen',
	'flow-topic-action-delete-topic' => 'Onderwerp verwijderen',
	'flow-topic-action-suppress-topic' => 'Onderwerp onderdrukken',
	'flow-topic-action-restore-topic' => 'Onderwerp terugplaatsen',
	'flow-error-http' => 'Er is een fout opgetreden in het contact met de server.',
	'flow-error-other' => 'Er is een onverwachte fout opgetreden.',
	'flow-error-external' => 'Er is een fout opgetreden.<br />De foutmelding is: $1',
	'flow-error-edit-restricted' => 'U mag dit bericht niet bewerken.',
	'flow-error-external-multi' => 'Er zijn fouten opgetreden.<br />$1',
	'flow-error-missing-content' => 'Het bericht heeft geen inhoud. Inhoud is vereist voor het opslaan van een bericht.',
	'flow-error-missing-title' => 'Onderwerp heeft geen titel. Een titel is vereist voor het opslaan van een onderwerp.',
	'flow-error-parsoid-failure' => 'Verwerken is niet mogelijk vanwege een fout in Parsoid.',
	'flow-error-missing-replyto' => 'Er is geen parameter "replyTo" opgegeven. Deze parameter is verplicht voor de handeling "reply".',
	'flow-error-invalid-replyto' => 'De parameter "replyTo" is ongeldig. Het opgegeven bericht kon niet worden gevonden.',
	'flow-error-delete-failure' => 'Het verwijderen van dit item is mislukt.',
	'flow-error-hide-failure' => 'Het verbergen van dit item is mislukt.',
	'flow-error-missing-postId' => 'Er is geen parameter "postId" opgegeven. Deze parameter is verplicht bij het wijzigingen van een bericht.',
	'flow-error-invalid-postId' => 'De parameter "postId" is ongeldig. Het opgegeven bericht ($1) kan niet worden gevonden.',
	'flow-error-restore-failure' => 'Het terugplaatsen van dit item is mislukt.',
	'flow-error-invalid-moderation-state' => 'Er is een ongeldige waarde opgegeven voor "moderationState".',
	'flow-error-invalid-moderation-reason' => 'Geef een reden op voor de moderatie.',
	'flow-error-not-allowed' => 'Onvoldoende rechten voor het uitvoeren van deze handeling.',
	'flow-error-title-too-long' => 'Onderwerpen kunnen niet langer zijn dan  $1 {{PLURAL:$1|teken|tekens}}.',
	'flow-error-no-existing-workflow' => 'Deze workflow bestaat nog niet.',
	'flow-error-not-a-post' => 'Dit onderwerp kan niet als bericht worden opgeslagen.',
	'flow-error-missing-header-content' => 'De koptekst heeft geen inhoud. Zonder inhoud voor de koptekst, kunt u niet opslaan.',
	'flow-error-missing-prev-revision-identifier' => 'Het ID van de vorige versie ontbreekt.',
	'flow-error-prev-revision-mismatch' => 'Een andere gebruiker bewerkte deze bijdrage al een paar seconden geleden. Weet u zeker dat u deze recente verandering wilt overschrijven?',
	'flow-error-prev-revision-does-not-exist' => 'De vorige versie kon niet gevonden worden.',
	'flow-error-default' => 'Er is een fout opgetreden.',
	'flow-error-invalid-input' => 'Er is een ongeldige waarde opgegeven voor het laden van inhoud van Flow.',
	'flow-error-invalid-title' => 'Er is een ongeldige paginanaam opgegeven.',
	'flow-error-fail-load-history' => 'Het laden de geschiedenis is mislukt.',
	'flow-error-missing-revision' => 'Er is geen versie gevonden om inhoud van Flow van te downloaden.',
	'flow-error-fail-commit' => 'Het opslaan van de inhoud van Flow is mislukt.',
	'flow-error-insufficient-permission' => 'Onvoldoende rechten om de inhoud te kunnen bekijken.',
	'flow-error-revision-comparison' => 'Diff bewerking kan alleen worden uitgevoerd voor twee revisies van dezelfde post.',
	'flow-error-missing-topic-title' => 'Kan de onderwerptitel voor de huidige werkstroom niet vinden.',
	'flow-error-fail-load-data' => 'Fout bij het laden van de gevraagde gegevens.',
	'flow-error-invalid-workflow' => 'Kan de gevraagde werkstroom niet vinden.',
	'flow-error-process-data' => 'Er is een fout opgetreden tijdens het verwerken van de gegevens in uw aanvraag.',
	'flow-error-process-wikitext' => 'Er is een fout opgetreden tijdens het verwerken van HTML/wikitext conversie.',
	'flow-error-no-index' => 'Geen index voor het uitvoeren van het zoeken van gegevens.',
	'flow-edit-header-submit' => 'Koptekst opslaan',
	'flow-edit-header-submit-overwrite' => 'Overschrijven van koptekst',
	'flow-edit-title-submit' => 'Onderwerp wijzigen',
	'flow-edit-title-submit-overwrite' => 'Titel overschrijven',
	'flow-edit-post-submit' => 'Wijzigingen opslaan',
	'flow-edit-post-submit-overwrite' => 'Wijzigingen overschrijven',
	'flow-rev-message-edit-post' => '[$1|$2] heeft een [$3 reactie] op $4 bewerkt.',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|heeft}} een [$3 reactie] toegevoegd.', # Fuzzy
	'flow-rev-message-reply-bundle' => 'Er {{PLURAL:$1|is|zijn}} <strong>$1 {{PLURAL:$1|reactie|reacties}}</strong> toegevoegd.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|heeft}} het onderwerp [$3 $4] aangemaakt.',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|heeft}} het onderwerp gewijzigd van $5 naar [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|heeft}} de kop aangemaakt.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|heeft}} de kop bewerkt.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] verborgen (<em>$5</em>).', # Fuzzy
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] verwijderd (<em>$5</em>).', # Fuzzy
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] onderdrukt (<em>$5</em>).', # Fuzzy
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] teruggeplaatst (<em>$5</em>).', # Fuzzy
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] verborgen (<em>$5</em>).', # Fuzzy
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] verwijderd (<em>$5</em>).', # Fuzzy
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] onderdrukt (<em>$5</em>).', # Fuzzy
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] teruggeplaatst (<em>$5</em>).', # Fuzzy
	'flow-board-history' => 'Geschiedenis van "$1"',
	'flow-topic-history' => 'Onderwerpgeschiedenis van "$1"',
	'flow-post-history' => 'Berichtgeschiedenis van "Reactie van {{GENDER:$2|$2}}"',
	'flow-history-last4' => 'Laatste 4 uur',
	'flow-history-day' => 'Vandaag',
	'flow-history-week' => 'Afgelopen week',
	'flow-history-pages-topic' => 'Komt voor op het [$1 prikbord "$2"]',
	'flow-history-pages-post' => 'Komt voor op [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 is dit onderwerp begonnen|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} en {{PLURAL:$2|een andere gebruiker|andere gebruikers}}|0=Nog geen deelnemers|2={{GENDER:$3|$3}} en {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} en {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0={{GENDER:$2|Reageer}} als eerste!|Eén reactie|$1 reacties}}',
	'flow-comment-restored' => 'Teruggeplaatste reactie',
	'flow-comment-deleted' => 'Verwijderde reactie',
	'flow-comment-hidden' => 'Verborgen reactie',
	'flow-comment-moderated' => 'Gemodereerde reactie',
	'flow-paging-rev' => 'Meer recente onderwerpen',
	'flow-paging-fwd' => 'Oudere onderwerpen',
	'flow-last-modified' => 'Ongeveer $1 voor het laatst bewerkt',
	'flow-notification-reply' => '$1 {{GENDER:$1|heeft}} geantwoord op uw <span class="plainlinks">[$5 bericht]</span> in $2 op "$4".',
	'flow-notification-reply-bundle' => '$1 en {{PLURAL:$6|iemand anders|$5 anderen}} {{GENDER:$1|hebben}} gereageerd op uw <span class="plainlinks">[$4 bericht]</span> in $2 op "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|heeft}} een <span class="plainlinks">[$5 bericht]</span> geplaatst in $2 op [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 en $5 {{PLURAL:$6|andere gebruiker|anderen}} {{GENDER:$1|hebben}} een <span class="plainlinks">[$4 bericht]</span> geplaatst in "$2" op "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|heeft}} een <span class="plainlinks">[$5 nieuw onderwerp]</span> aangemaakt op [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|heeft}} het onderwerp <span class="plainlinks">[$2 $3]</span> hernoemd naar "$4" op [[$5|$6]].',
	'flow-notification-mention' => '$1 heeft u genoemd in {{GENDER:$1|zijn|haar|zijn/haar}} <span class="plainlinks">[$2 bericht]</span> in "$3" op "$4"',
	'flow-notification-link-text-view-post' => 'Bericht bekijken',
	'flow-notification-link-text-view-board' => 'Prikbord bekijken',
	'flow-notification-link-text-view-topic' => 'Onderwerp bekijken',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|heeft}} gereageerd op uw bericht',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|heeft}} gereageerd op uw bericht in "$2" op "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 en {{PLURAL:$5|iemand anders|$4 anderen}} {{GENDER:$1|hebben}} gereageerd op uw bericht in "$2" op "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|heeft}} u genoemd op "$2"',
	'flow-notification-mention-email-batch-body' => '$1 heeft u genoemd in {{GENDER:$1|zijn|haar|zijn/haar}} bericht in "$2" op "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|heeft}} een bericht bewerkt',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|heeft}} een bericht bewerkt in "$2" op "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 en $4 {{PLURAL:$5|andere gebruiker|anderen}} {{GENDER:$1|hebben}} een bericht bewerkt in "$2" op "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|heeft}} uw onderwerp een andere naam gegeven',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|heeft}} uw onderwerp "$2" hernoemd naar "$3" op "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|heeft}} een nieuw onderwerp aangemaakt op "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|heeft}} op $3 een nieuw onderwerp aangemaakt met de naam "$2"',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'U een melding sturen als er handelingen over u in Flow plaatsvinden.',
	'flow-link-post' => 'bericht',
	'flow-link-topic' => 'onderwerp',
	'flow-link-history' => 'geschiedenis',
	'flow-moderation-reason-placeholder' => 'Geef hier uw reden op',
	'flow-moderation-title-suppress-post' => 'Bericht onderdrukken?',
	'flow-moderation-title-delete-post' => 'Bericht verwijderen?',
	'flow-moderation-title-hide-post' => 'Bericht verbergen?',
	'flow-moderation-title-restore-post' => 'Bericht terugplaatsen?',
	'flow-moderation-intro-suppress-post' => '{{GENDER:$3|Geef}} een reden op waarom u dit bericht onderdrukt.',
	'flow-moderation-intro-delete-post' => '{{GENDER:$3|Geef}} een reden op waarom u dit bericht verwijdert.',
	'flow-moderation-intro-hide-post' => '{{GENDER:$3|Geef}} een reden op waarom u dit bericht verbergt.',
	'flow-moderation-intro-restore-post' => '{{GENDER:$3|Geef}} een reden op waarom u dit bericht terugplaatst.',
	'flow-moderation-confirm-suppress-post' => 'Onderdrukken',
	'flow-moderation-confirm-delete-post' => 'Verwijderen',
	'flow-moderation-confirm-hide-post' => 'Verbergen',
	'flow-moderation-confirm-restore-post' => 'Terugplaatsen',
	'flow-moderation-confirmation-suppress-post' => 'Het bericht is succesvol onderdrukt.
{{GENDER:$2|Overweeg}} $1 terugkoppeling te geven over dit bericht.',
	'flow-moderation-confirmation-delete-post' => 'Het bericht is succesvol verwijderd.
{{GENDER:$2|Overweeg}} {{GENDER:$1|$1}} terugkoppeling te geven over dit bericht.',
	'flow-moderation-confirmation-hide-post' => 'Het bericht is succesvol verborgen.
{{GENDER:$2|Overweeg}} {{GENDER:$1|$1}} terugkoppeling te geven over dit bericht.',
	'flow-moderation-confirmation-restore-post' => 'U hebt bovenstaand bericht teruggeplaatst.',
	'flow-moderation-title-suppress-topic' => 'Onderwerp onderdrukken?',
	'flow-moderation-title-delete-topic' => 'Onderwerp verwijderen?',
	'flow-moderation-title-hide-topic' => 'Onderwerp verbergen?',
	'flow-moderation-title-restore-topic' => 'Onderwerp terugplaatsen?',
	'flow-moderation-intro-suppress-topic' => '{{GENDER:$3|Leg}} uit waarom u dit onderwerp onderdrukt.',
	'flow-moderation-intro-delete-topic' => '{{GENDER:$3|Leg}} uit waarom u dit onderwerp verwijdert.',
	'flow-moderation-intro-hide-topic' => '{{GENDER:$3|Leg}} uit waarom u dit onderwerp verbergt.',
	'flow-moderation-intro-restore-topic' => '{{GENDER:$3|Leg}} uit waarom u dit onderwerp terugplaatst.',
	'flow-moderation-confirm-suppress-topic' => 'Onderdrukken',
	'flow-moderation-confirm-delete-topic' => 'Verwijderen',
	'flow-moderation-confirm-hide-topic' => 'Verbergen',
	'flow-moderation-confirm-restore-topic' => 'Terugplaatsen',
	'flow-moderation-confirmation-suppress-topic' => 'Het onderwerp is succesvol onderdrukt.
{{GENDER:$2|Overweeg}} {{GENDER:$1|$1}} terugkoppeling te geven over dit onderwerp.',
	'flow-moderation-confirmation-delete-topic' => 'Het onderwerp is succesvol verwijderd.
{{GENDER:$2|Overweeg}} {{GENDER:$1|$1}} terugkoppeling te geven over dit onderwerp.',
	'flow-moderation-confirmation-hide-topic' => 'Het onderwerp is succesvol verborgen.
{{GENDER:$2|Overweeg}} {{GENDER:$1|$1}} terugkoppeling te geven over dit onderwerp.',
	'flow-moderation-confirmation-restore-topic' => 'U hebt dit bericht teruggeplaatst.',
	'flow-topic-permalink-warning' => 'Dit onderwerp is gestart op [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Dit onderwerp is gestart op het [$2 prikbord van {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Dit is een permanente koppeling naar een enkele versie van dit bericht.
Deze versie is van $1.
U kunt de [$5 verschillen ten opzichte van de vorige versie] bekijken, of andere versies bekijken op de [$4 geschiedenispagina van het bericht].',
	'flow-revision-permalink-warning-post-first' => 'Dit is een permanente kopeeling naar de eerste versie van dit bericht.
U kunt nieuwere versies bekijken op de [$4 geschiedenispagina van dit bericht].',
	'flow-compare-revisions-revision-header' => 'Version van $1 door {{GENDER:$2|$2}}',
	'flow-compare-revisions-header-post' => 'Op deze pagina worden de verschillen tussen twee versies weergegeven van een bericht van {{GENDER:$3|$3}} in het onderwerp "[$5 $2]" op [$4 $1].
U kunt de andere versie van dit bericht bekijken op de [$6 geschiedenispagina].',
	'flow-topic-collapsed-one-line' => 'Kleine weergave',
	'flow-topic-collapsed-full' => 'Ingeklapte weergave',
	'flow-topic-complete' => 'Volledige weergave',
	'flow-terms-of-use-new-topic' => 'Door op "{{int:flow-newtopic-save}}" te klikken gaat u akkoord met de gebruiksvoorwaarden van deze wiki.',
	'flow-terms-of-use-reply' => 'Door te klikken op "{{int:flow-reply-submit}}", gaat u akkoord met de gebruiksvoorwaarden van deze wiki.',
	'flow-terms-of-use-edit' => 'Door deze wijzigingen op te slaan, gaat u akkoord met de gebruiksvoorwaarden van deze wiki.',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'flow-desc' => 'Sistèma de gestion del flux de trabalh',
	'flow-post-actions' => 'Accions',
	'flow-topic-actions' => 'Accions',
	'flow-cancel' => 'Anullar',
	'flow-newtopic-title-placeholder' => 'Subjècte novèl',
	'flow-newtopic-content-placeholder' => 'Apondre de detalhs se o volètz',
	'flow-newtopic-header' => 'Apondre un subjècte novèl',
	'flow-newtopic-save' => 'Apondre un subjècte',
	'flow-newtopic-start-placeholder' => 'Començar un subjècte novèl',
	'flow-reply-placeholder' => '{{GENDER:$1|Respondre}} a $1',
	'flow-reply-submit' => '{{GENDER:$1|Respondre}}',
	'flow-edit-post-submit' => 'Sometre las modificacions',
	'flow-post-action-view' => 'Ligam permanent',
	'flow-post-action-post-history' => 'Istoric de las publicacions',
	'flow-post-action-suppress-post' => 'Suprimir',
	'flow-post-action-delete-post' => 'Suprimir',
	'flow-post-action-hide-post' => 'Amagar',
	'flow-post-action-edit-post' => 'Modificar la publicacion',
	'flow-post-action-edit' => 'Modificar',
	'flow-post-action-restore-post' => 'Restablir lo messatge',
	'flow-topic-action-edit-title' => 'Modificar lo títol',
	'flow-topic-action-history' => 'Istoric dels subjèctes',
	'flow-error-http' => "Una error s'es producha en comunicant amb lo servidor.",
	'flow-error-other' => "Una error imprevista s'es producha.",
	'flow-error-external' => "Una error s'es producha.<br />Lo messatge d'error recebut èra :$1",
	'flow-error-external-multi' => "D'errors se son produchas.<br /> $1",
	'flow-error-missing-content' => 'Lo messatge a pas cap de contengut. Es requesit per enregistrar un messatge novèl.', # Fuzzy
	'flow-error-missing-title' => 'Lo subjècte a pas cap de títol. Es requesit per enregistrar un subjècte novèl.', # Fuzzy
	'flow-error-parsoid-failure' => "Impossible d'analisar lo contengut a causa d'una pana de Parsoid.",
	'flow-error-missing-replyto' => "Cap de paramètre « replyTo » es pas estat provesit. Aqueste paramètre es requesit per l'accion « respondre ».",
	'flow-error-invalid-replyto' => 'Lo paramètre « replyTo » èra pas valid. Lo messatge especificat es pas estat trobat.',
	'flow-error-delete-failure' => "Fracàs de la supression d'aquesta entrada.",
	'flow-error-hide-failure' => "L'amagatge d'aqueste element a fracassat.",
	'flow-error-missing-postId' => 'Cap de paramètre « postId » es pas estat provesit. Aqueste paramètre es requesit per manipular un messatge.',
	'flow-error-invalid-postId' => 'Lo paramètre « postId » èra pas valid. Lo messatge especificat ($1) es pas estat trobat.',
	'flow-error-restore-failure' => "Fracàs del restabliment d'aquesta entrada.",
	'flow-edit-title-submit' => 'Cambiar lo títol',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|a apondut}} un [$3 comentari].',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|a creat}} lo subjècte [$3 $4].',
	'flow-topic-history' => 'Istoric del subjècte « $1 »',
	'flow-comment-restored' => 'Comentari restablit',
	'flow-comment-deleted' => 'Comentari suprimit',
	'flow-comment-hidden' => 'Comentari amagat',
	'flow-paging-rev' => 'Subjèctes los mai recents',
	'flow-paging-fwd' => 'Subjèctes mai ancians',
	'flow-last-modified' => 'Darrièr cambiament $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|a respondut}} a vòstra [$5 nòta] sus $2 en "$4".',
	'flow-notification-edit' => '$1 {{GENDER:$1|a modificat}} una [$5 nòta] sus $2 en [[$3|$4]].',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|a creat}} un [$5 subjècte novèl] en [[$2|$3]] : $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|a modificat}} lo títol de [$2 $3] en « $4 » sus [[$5|$6]].',
);

/** Polish (polski)
 * @author Chrumps
 * @author Jacenty359
 * @author Pio387
 * @author Rzuwig
 * @author Vuh
 * @author Woytecr
 */
$messages['pl'] = array(
	'flow-cancel' => 'Anuluj',
	'flow-preview' => 'Podgląd',
	'flow-stub-post-content' => "''Ze względu na błąd techniczny, ten post nie mógł zostać przywrócony.''",
	'flow-newtopic-title-placeholder' => 'Nowy temat',
	'flow-newtopic-header' => 'Dodaj nowy temat',
	'flow-newtopic-save' => 'Dodaj temat',
	'flow-newtopic-start-placeholder' => 'Rozpocznij nowy temat',
	'flow-post-action-post-history' => 'Historia',
	'flow-post-action-delete-post' => 'Usuń',
	'flow-post-action-hide-post' => 'Ukryj',
	'flow-post-action-edit-post' => 'Edytuj',
	'flow-topic-action-edit-title' => 'Edytuj tytuł',
	'flow-topic-action-history' => 'Historia',
	'flow-error-prev-revision-mismatch' => 'Inny użytkownik kilka sekund temu edytował ten post. Na pewno chcesz nadpisać jego zmiany?',
	'flow-error-default' => 'Wystąpił błąd.',
	'flow-edit-header-submit' => 'Zapisz nagłówek',
	'flow-edit-header-submit-overwrite' => 'Nadpisz nagłówek',
	'flow-edit-title-submit' => 'Zmień tytuł',
	'flow-edit-title-submit-overwrite' => 'Nadpisz tytuł',
	'flow-edit-post-submit' => 'Zapisz zmiany',
	'flow-edit-post-submit-overwrite' => 'Nadpisz zmiany',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|edytowano}} [$3 komentarz] $4',
	'flow-rev-message-reply' => '$1 [$4 {{GENDER:$2|skomentowano}}] $4 (<em>$5</em>).', # Fuzzy
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|stworzono}} nagłówek',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|edytowano}} nagłówek.',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|usunięto}} [$4 komentarz] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|usunięto}} [$4 temat] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|przywrócono}} [$4 temat] $6 (<em>$5</em>).',
	'flow-history-last4' => 'Ostatnie 4 godziny',
	'flow-history-day' => 'Dzisiaj',
	'flow-history-week' => 'Ostatni tydzień',
	'flow-paging-fwd' => 'Starsze tematy',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|odpowiedział|odpowiedziała}} na twój post',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|zmienił|zmieniła}} nazwę twojego tematu',
	'flow-link-topic' => 'temat',
	'flow-link-history' => 'historia',
	'flow-revision-permalink-warning-header' => 'To jest link do pojedynczej wersji nagłówka.
Ta wersja jest z $1. Możesz zobaczyć [$3 różnice od poprzedniej wersji] lub zobaczyć inne wersje na [$2 stronie historii].',
	'flow-revision-permalink-warning-header-first' => 'To jest link do pierwszej wersji nagłówka.
Możesz zobaczyć późniejsze wersje na [$2 stronie historii].',
	'flow-compare-revisions-header-header' => 'Ta strona pokazuje {{GENDER:$2|zmiany}} pomiędzy dwiema wersjami nagłówka w [$3 $1]. Możesz również zobaczyć inne wersje na [$4 stronie historii].',
	'flow-terms-of-use-new-topic' => 'Klikając na "{{int:flow-newtopic-save}}", zgadzasz się na zasady użytkowania tej wiki.',
	'flow-terms-of-use-reply' => 'Klikając na "{{int:flow-reply-submit}}", zgadzasz się na warunki użytkowania tej wiki.',
	'flow-terms-of-use-edit' => 'Zapisując zmiany, zgadzasz się na warunki użytkowania tej wiki.',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'flow-hide-title-content' => '{{GENDER:$1|پټ شو}} د $2 لخوا', # Fuzzy
	'flow-delete-title-content' => '{{GENDER:$1|ړنگ شو}} د $2 لخوا', # Fuzzy
	'flow-delete-header-content' => '{{GENDER:$1|ړنگ شو}} د $2 لخوا',
	'flow-post-edited' => 'ليکنه د $1 لخوا په $2 {{GENDER:$1|سمه شوه}}',
	'flow-notification-edit-email-subject' => '$1 ستاسې ليکنه {{GENDER:$1|سمه کړه}}', # Fuzzy
	'flow-notification-rename-email-subject' => '$1 ستاسې سرليک {{GENDER:$1|نوم بدل کړ}}',
);

/** Portuguese (português)
 * @author Helder.wiki
 * @author Imperadeiro98
 */
$messages['pt'] = array(
	'flow-desc' => 'Sistema de Gerenciamento do Fluxo de Trabalho',
	'flow-notification-edit-bundle' => '$1 e $5 {{PLURAL:$6|outro|outros}} {{GENDER:$1|editaram}} uma <span class="plainlinks">[$4 mensagem]</span> em "$2", em "$3".',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e $4 {{PLURAL:$5|outro|outros}} {{GENDER:$1|responderam}} à sua mensagem em "$2", em "$3"',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Helder.wiki
 * @author Tuliouel
 */
$messages['pt-br'] = array(
	'flow-desc' => 'Sistema de Gerenciamento do Fluxo de Trabalho',
	'flow-link-post' => 'publicar',
);

/** Quechua (Runa Simi)
 * @author AlimanRuna
 */
$messages['qu'] = array(
	'flow-last-modified' => 'Qhipaq hukchasqa $1 ñaqha',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'flow-desc' => 'Sisteme de Gestione de le Flusse de fatìe',
	'flow-post-moderated-toggle-delete-show' => "Fà 'ndrucà 'u commende {{GENDER:$1|scangellate}} da $2",
	'flow-post-moderated-toggle-hide-hide' => "Scunne 'u commende {{GENDER:$1|scunnute}} da $2",
	'flow-post-moderated-toggle-suppress-hide' => "Scunne 'u commende {{GENDER:$1|scangellate}} da $2",
	'flow-post-actions' => 'Aziune',
	'flow-topic-actions' => 'Aziune',
	'flow-cancel' => 'Annulle',
	'flow-newtopic-title-placeholder' => 'Argomende nuève',
	'flow-newtopic-content-placeholder' => 'Messàgge de teste. Si belle!', # Fuzzy
	'flow-newtopic-header' => "Aggiunge 'n'argomende nuève",
	'flow-newtopic-save' => "Aggiunge 'n'argomende",
	'flow-newtopic-start-placeholder' => "Cazze aqquà pe accumenzà 'nu 'ngazzamende nuève. Sì belle!", # Fuzzy
	'flow-reply-placeholder' => 'Cazze pe responnere a $1. Sì belle!', # Fuzzy
	'flow-reply-submit' => "Manne 'na resposte", # Fuzzy
	'flow-post-action-post-history' => 'Cunde',
	'flow-post-action-delete-post' => "Scangìlle 'u messàgge", # Fuzzy
	'flow-post-action-edit-post' => 'Cange',
	'flow-post-action-restore-post' => "Repristine 'u messàgge",
	'flow-topic-action-edit-title' => "Cange 'u titole",
	'flow-topic-action-history' => 'Cunde',
	'flow-error-http' => "Ha assute 'n'errore condattanne 'u server. 'U messàgge tune non g'ha state reggistrate.", # Fuzzy
	'flow-error-other' => "Ha assute 'n'errore. 'U messàgge tune non g'ha state reggistrate.", # Fuzzy
	'flow-edit-title-submit' => "Cange 'u titole",
);

/** Russian (русский)
 * @author Alexandr Efremov
 * @author Ignatus
 * @author Kaganer
 * @author Midnight Gambler
 * @author Okras
 * @author Tucvbif
 */
$messages['ru'] = array(
	'flow-desc' => 'Система управления потоками работ',
	'log-name-flow' => 'Журнал активности потоков',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|удалил|удалила}} [$4 сообщение] на странице [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 восстановил{{GENDER:$2||а}} [$4 сообщение] на странице [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 удалил{{GENDER:$2||а}} [$4 сообщение] на странице [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 удалил{{GENDER:$2||а}} [$4 тему] на странице [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 восстановил{{GENDER:$2||а}} [$4 тему] на странице [[$3]]',
	'flow-edit-header-link' => 'Изменить заголовок',
	'flow-header-empty' => 'У этой страницы обсуждения в настоящее время нет заголовка.',
	'flow-post-moderated-toggle-hide-show' => 'Показать комментарий, скрытый {{GENDER:$1|участником|участницей}} $2',
	'flow-post-moderated-toggle-delete-show' => 'Показать комментарий, удалённый {{GENDER:$1|участником|участницей}} $2',
	'flow-post-moderated-toggle-hide-hide' => 'Скрыть комментарий, скрытый {{GENDER:$1|участником|участницей}} $2',
	'flow-post-moderated-toggle-delete-hide' => 'Скрыть комментарий, удалённый {{GENDER:$1|участником|участницей}} $2',
	'flow-hide-post-content' => 'Этот комментарий был скрыт {{GENDER:$1|участником|участницей}} $2',
	'flow-hide-title-content' => 'Эта тема была скрыта {{GENDER:$1|участником|участницей}} $2',
	'flow-hide-header-content' => 'Скрыто {{GENDER:$1|участником|участницей}} $2',
	'flow-delete-post-content' => 'Этот комментарий был удалён {{GENDER:$1|участником|участницей}} $2',
	'flow-delete-header-content' => 'Удалено {{GENDER:$1|участником|участницей}} $2',
	'flow-post-actions' => 'Действия',
	'flow-topic-actions' => 'Действия',
	'flow-cancel' => 'Отменить',
	'flow-preview' => 'Предпросмотр',
	'flow-show-change' => 'Показать изменения',
	'flow-newtopic-title-placeholder' => 'Новая тема',
	'flow-newtopic-content-placeholder' => 'Добавьте, если хотите, какие-нибудь подробности',
	'flow-newtopic-header' => 'Добавить новую тему',
	'flow-newtopic-save' => 'Добавить тему',
	'flow-newtopic-start-placeholder' => 'Начать новое обсуждение',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Комментарий}} в теме «$2»',
	'flow-reply-placeholder' => 'Ответить {{GENDER:$1|участнику|участнице}} $1',
	'flow-reply-submit' => '{{GENDER:$1|Ответить}}',
	'flow-reply-link' => '{{GENDER:$1|Ответить}}',
	'flow-thank-link' => '{{GENDER:$1|Поблагодарить}}',
	'flow-post-edited' => 'Сообщение отредактировано {{GENDER:$1|участником|участницей}} $1 $2',
	'flow-post-action-view' => 'Постоянная ссылка',
	'flow-post-action-post-history' => 'История',
	'flow-post-action-delete-post' => 'Удалить',
	'flow-post-action-hide-post' => 'Скрыть',
	'flow-post-action-edit-post' => 'Редактировать',
	'flow-post-action-restore-post' => 'Восстановить сообщение',
	'flow-topic-action-view' => 'Постоянная ссылка',
	'flow-topic-action-watchlist' => 'Список наблюдения',
	'flow-topic-action-edit-title' => 'Редактировать заголовок',
	'flow-topic-action-history' => 'История',
	'flow-topic-action-hide-topic' => 'Скрыть тему',
	'flow-topic-action-delete-topic' => 'Удалить тему',
	'flow-topic-action-restore-topic' => 'Восстановить тему',
	'flow-error-http' => 'Произошла ошибка при обращении к серверу.',
	'flow-error-other' => 'Произошла непредвиденная ошибка.',
	'flow-error-external' => 'Произошла ошибка.<br />Было получено следующее сообщение об ошибке: $1',
	'flow-error-edit-restricted' => 'Вам не разрешено редактировать это сообщение.',
	'flow-error-missing-content' => 'Сообщение не имеет содержимого. Для сохранения сообщения требуется содержимое.',
	'flow-error-missing-title' => 'Тема не имеет заголовка. Заголовок необходим для сохранения темы.',
	'flow-error-parsoid-failure' => 'Не удаётся выполнить разбор содержимого из-за сбоя Parsoid.',
	'flow-error-delete-failure' => 'Не удалось удалить этот элемент.',
	'flow-error-hide-failure' => 'Не удалось скрыть этот элемент.',
	'flow-error-restore-failure' => 'Не удалось восстановить этот элемент.',
	'flow-error-not-allowed' => 'Недостаточно прав для выполнения этого действия',
	'flow-error-default' => 'Произошла ошибка.',
	'flow-error-fail-load-data' => 'Не удалось загрузить запрошенные данные.',
	'flow-edit-header-submit' => 'Сохранить заголовок',
	'flow-edit-header-submit-overwrite' => 'Перезаписать заголовок',
	'flow-edit-title-submit' => 'Изменить название',
	'flow-edit-title-submit-overwrite' => 'Перезаписать название',
	'flow-edit-post-submit' => 'Подтвердить изменения',
	'flow-edit-post-submit-overwrite' => 'Перезаписать изменения',
	'flow-rev-message-edit-post' => '$1 отредактировал{{GENDER:$2||а}} [$3  комментарий] в теме $4.',
	'flow-rev-message-reply' => '$1 [$3 прокомментировал{{GENDER:$2||а}} ] тему $4 (<em>$5</em>).',
	'flow-rev-message-new-post' => '$1 создал{{GENDER:$2||а}} тему [$3 $4].',
	'flow-rev-message-deleted-post' => '$1 удалил{{GENDER:$2||а}} [$4 комментарий] в теме $6(<em>$5</em>).',
	'flow-topic-history' => 'История темы «$1»',
	'flow-history-last4' => 'За последние 4 часа',
	'flow-history-day' => 'Сегодня',
	'flow-history-week' => 'На прошлой неделе',
	'flow-comment-restored' => 'Восстановленный комментарий',
	'flow-comment-deleted' => 'Удалённый комментарий',
	'flow-comment-hidden' => 'Скрытый комментарий',
	'flow-paging-rev' => 'Более новые темы',
	'flow-paging-fwd' => 'Более старые темы',
	'flow-notification-link-text-view-post' => 'Посмотреть сообщение',
	'flow-notification-link-text-view-board' => 'Просмотреть форум',
	'flow-notification-link-text-view-topic' => 'Посмотреть тему',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ответил|ответила}} на ваше сообщение',
	'flow-notification-reply-email-batch-body' => '$1 ответил{{GENDER:$1||а}} на ваше сообщение в теме «$2» в «$3»',
	'flow-notification-mention-email-subject' => '$1 упомянул{{GENDER:$1||а}} вас в «$2»',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|отредактировал|отредактировала}} сообщение',
	'flow-notification-edit-email-batch-body' => '$1 отредактировал{{GENDER:$2||а}} сообщение в теме «$2» на странице «$3»',
	'flow-link-post' => 'сообщение',
	'flow-link-topic' => 'тема',
	'flow-link-history' => 'история',
	'flow-moderation-reason-placeholder' => 'Введите причину здесь',
	'flow-moderation-title-delete-post' => 'Удалить сообщение?',
	'flow-moderation-title-hide-post' => 'Скрыть сообщение?',
	'flow-moderation-title-restore-post' => 'Восстановить сообщение?',
	'flow-moderation-confirm-delete-post' => 'Удалить',
	'flow-moderation-confirm-hide-post' => 'Скрыть',
	'flow-moderation-confirm-restore-post' => 'Восстановить',
	'flow-moderation-title-delete-topic' => 'Удалить тему?',
	'flow-moderation-title-hide-topic' => 'Скрыть тему?',
	'flow-moderation-title-restore-topic' => 'Восстановить тему?',
	'flow-moderation-intro-delete-topic' => '{{GENDER:$3|Поясните}} причину удаления данной темы.',
	'flow-moderation-intro-hide-topic' => '{{GENDER:$3|Поясните}}, почему вы хотите скрыть данную тему.',
	'flow-moderation-intro-restore-topic' => '{{GENDER:$3|Поясните}} причину восстановления данной темы.',
	'flow-moderation-confirm-delete-topic' => 'Удалить',
	'flow-moderation-confirm-hide-topic' => 'Скрыть',
	'flow-moderation-confirm-restore-topic' => 'Восстановить',
	'flow-moderation-confirmation-restore-topic' => 'Вы успешно обновили эту тему.',
	'flow-topic-permalink-warning' => 'Эта тема была начата на [$2 $1]',
	'flow-compare-revisions-header-post' => 'На этой странице показаны {{GENDER:$3|изменения}} между двумя версиями сообщения от участника $3 в теме «[$5 $2]» раздела [$4 $1].
Вы можете посмотреть другие версии на этого сообщения на его [$6 странице история].',
	'flow-topic-collapsed-one-line' => 'Компактный вид',
	'flow-topic-collapsed-full' => 'Свёрнутый вид',
	'flow-topic-complete' => 'Полный вид',
);

/** Sicilian (sicilianu)
 * @author Gmelfi
 */
$messages['scn'] = array(
	'flow-thank-link' => '{{GENDER:$1|Arringràzzia}}',
);

/** Scots (Scots)
 * @author John Reid
 */
$messages['sco'] = array(
	'flow-post-moderated-toggle-hide-show' => 'Show comment {{GENDER:$1|hidden}} bi $2',
	'flow-post-moderated-toggle-delete-show' => 'Shaw comment {{GENDER:$1|delytit}} bi $2',
	'flow-post-moderated-toggle-suppress-show' => 'Show comment {{GENDER:$1|suppressed}} bi $2',
	'flow-post-moderated-toggle-hide-hide' => 'Skauk comment {{GENDER:$1|hidden}} bi $2',
	'flow-post-moderated-toggle-delete-hide' => 'Skauk comment {{GENDER:$1|delytit}} bi $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Skauk comment {{GENDER:$1|suppressed}} bi $2',
	'flow-post-action-post-history' => 'Histerie',
	'flow-post-action-edit-post' => 'Edit',
	'flow-topic-action-history' => 'Histerie',
	'flow-error-prev-revision-mismatch' => "Anither uiser jyst edited this post ae few seiconts back. Ar ye sair ye wan tae o'erwrite the recent chynge?",
	'flow-edit-header-submit-overwrite' => "O'erwrite header",
	'flow-edit-title-submit-overwrite' => "O'erwrite title",
	'flow-edit-post-submit-overwrite' => "O'erwrite chynges",
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|eidited}} ae [$3 comment] oan $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|commented}}] oan $4.', # Fuzzy
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|makit}} the heider.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|eidited}} the heider.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|hid}} ae [$4 comment] oan $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|delytit}} ae [$4 comment] oan $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => "$1 {{GENDER:$2|suppress't}} ae [$4 comment] oan $6 (<em>$5</em>).",
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|restored}} ae [$4 comment] oan $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|hid}} the [$4 topic] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|delytit}} the [$4 topic] $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => "$1 {{GENDER:$2|suppress't}} the [$4 topic] $6 (<em>$5</em>).",
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|restored}} the [$4 topic] $6 (<em>$5</em>).',
	'flow-notification-reply' => '$1 {{GENDER:$1|replied}} til yer <span class="plainlinks">[$5 post]</span> in "$2" on "$4".',
	'flow-notification-reply-bundle' => '$1 an $5 {{PLURAL:$6|other|others}} {{GENDER:$1|replied}} til yer <span class="plainlinks">[$4 post]</span> in "$2" on "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|edited}} ae <span class="plainlinks">[$5 post]</span> in "$2" on [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 an $5 {{PLURAL:$6|other|others}} {{GENDER:$1|edited}} ae <span class="plainlinks">[$4 post]</span> in "$2" on "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|created}} ae <span class="plainlinks">[$5 new topic]</span> on [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|changed}} the title o <span class="plainlinks">[$2 $3]</span> til "$4" on [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|mentioned}} ye in {{GENDER:$1|his|her|their}} <span class="plainlinks">[$2 post]</span> in "$3" on "$4".',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|replied}} til yer post in "$2" on "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 an $4 {{PLURAL:$5|other|others}} {{GENDER:$1|replied}} til yer post in "$2" on "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|mentioned}} ye on "$2"',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|edited}} ae post in "$2" on "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 an $4 {{PLURAL:$5|other|others}} {{GENDER:$1|edited}} ae post in "$2" on "$3"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|created}} ae new topic on "$2"',
	'flow-revision-permalink-warning-header' => 'This is ae permanent link til ae single version o the heider.
This version is fae $1.  Ye can see the [$3 differences fae the preevioos version], or view ither versions oan the [$2 buird histerie page].',
	'flow-revision-permalink-warning-header-first' => 'This is ae permanent link til the irstwhile version o the heider.
Ye can view later versions oan the [$2 buird histerie page].',
	'flow-compare-revisions-header-header' => 'This page shaws the {{GENDER:$2|chynges}} atween twa versions o the heider oan [$3 $1].
Ye can see ither versions o the heider at its [$4 histerie page].',
	'flow-terms-of-use-new-topic' => 'Bi clapin on "{{int:flow-newtopic-save}}", ye\'r agreein til the terms o uiss fer this wiki.',
	'flow-terms-of-use-reply' => 'Bi clapin oan "{{int:flow-reply-submit}}", ye\'r agreein til the terms o uiss fer this wiki.',
	'flow-terms-of-use-edit' => "Bi savin yer chynges, ye'r agreein til the terms o uiss fer this wiki.",
);

/** Sinhala (සිංහල)
 * @author Sahan.ssw
 */
$messages['si'] = array(
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|අදහස් දැක්වුවා}}] $4 පිළිබඳව.', # Fuzzy
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|ඉවත් කර ඇත.}}  [$4 මාතෘකාව] $6 (<em>$5</em>).',
	'flow-terms-of-use-new-topic' => '"{{int:flow-newtopic-save}}"ක්ලික් කිරීමෙන්, මෙම විකිය භාවිතාකිරීමට ඇති නීතිවලට ඔබ එකඟ වේ.',
	'flow-terms-of-use-reply' => '"{{int:flow-reply-submit}}"click කිරීමෙන්, මෙම විකිය භාවිතාකිරීමට ඇති නීතිවලට ඔබ එකඟ වේ.',
	'flow-terms-of-use-edit' => 'ඔබගේ වෙනස්කම් සුරැකීමෙන්,ඔබ විකිය භාවිතා කිරීමට ඇති කොන්දේසි වලට එකඟ වේ.',
);

/** Slovenian (slovenščina)
 * @author Dbc334
 * @author Eleassar
 */
$messages['sl'] = array(
	'flow-reply-placeholder' => 'Odgovorite {{GENDER:$1|uporabniku|uporabnici}} $1',
	'flow-error-missing-replyto' => 'Podan ni bil noben parameter »odgovori na«. Ta parameter je za dejanje »odgovorite« obvezen.',
	'flow-error-invalid-replyto' => 'Parameter »odgovori« je bil neveljaven. Navedene objave ni bilo mogoče najti.',
	'flow-error-missing-postId' => 'Podan ni bil noben parameter »postId«. Ta parameter je za upravljanje z objavo obvezen.',
	'flow-error-invalid-postId' => 'Parameter »postId« ni veljaven. Navedene objave ($1) ni bilo mogoče najti.',
	'flow-notification-reply' => '$1 {{GENDER:$1|je odgovoril|je odgovorila}} na vašo [$5 objavo] v razdelku $2 na strani »$4«.', # Fuzzy
	'flow-notification-reply-bundle' => '$1 in $5 {{PLURAL:$6|drug|druga|drugi|drugih}} {{GENDER:$1|je odgovoril|je odgovorila|so odgovorili}} na vašo [$4 objavo] v razdelku $2 na strani »$3«.', # Fuzzy
	'flow-notification-edit' => '$1 {{GENDER:$1|je urejal|je urejala}} [$5 objavo] v razdelku $2 na [[$3|$4]].', # Fuzzy
	'flow-notification-newtopic' => '$1 {{GENDER:$1|je ustvaril|je ustvarila}} [$5 novo temo] na [[$2|$3]]: $4.', # Fuzzy
	'flow-notification-rename' => '$1 {{GENDER:$1|je spremenil|je spremenila}} naslov [$2 $3] v »$4« na [[$5|$6]].', # Fuzzy
	'flow-notification-link-text-view-post' => 'Ogled objave',
	'flow-notification-link-text-view-board' => 'Ogled deske',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|je odgovoril|je odgovorila}} na vašo objavo',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|je odgovoril|je odgovorila}} na vašo objavo v razdelku $2 na strani »$3«', # Fuzzy
	'flow-notification-reply-email-batch-bundle-body' => '$1 in $4 {{PLURAL:$5|drugi|druga|drugi|drugih}} {{PLURAL:$5|sta {{GENDER:$1|odgovorila}}|so odgovorili}} na vašo objavo v razdelku $2 na strani »$3«', # Fuzzy
	'echo-category-title-flow-discussion' => 'Tok',
	'echo-pref-tooltip-flow-discussion' => 'Obvesti me, ko se v Toku pojavijo dejanja v zvezi z mano.',
	'flow-link-post' => 'objava',
	'flow-link-topic' => 'tema',
	'flow-link-history' => 'zgodovina',
	'flow-moderation-reason-placeholder' => 'Tukaj vnesite svoj razlog',
	'flow-moderation-title-suppress-post' => 'Cenzoriraj objavo',
	'flow-moderation-title-delete-post' => 'Izbriši objavo',
	'flow-moderation-title-hide-post' => 'Skrij objavo',
	'flow-moderation-title-restore-post' => 'Obnovi objavo',
);

/** Serbian (Cyrillic script) (српски (ћирилица)‎)
 * @author Milicevic01
 * @author Rancher
 */
$messages['sr-ec'] = array(
	'flow-edit-header-link' => 'Уреди заглавље',
	'flow-post-actions' => 'Радње',
	'flow-topic-actions' => 'Радње',
	'flow-cancel' => 'Откажи',
	'flow-preview' => 'Претпреглед',
	'flow-newtopic-title-placeholder' => 'Нова тема',
	'flow-newtopic-save' => 'Додај тему',
	'flow-post-action-post-history' => 'Историја',
	'flow-post-action-delete-post' => 'Обриши',
	'flow-post-action-hide-post' => 'Сакриј',
	'flow-post-action-edit-post' => 'Уреди',
	'flow-topic-action-edit-title' => 'Уреди наслов',
	'flow-topic-action-history' => 'Историја',
	'flow-edit-post-submit' => 'Сачувај',
	'flow-history-day' => 'Данас',
	'flow-link-topic' => 'тема',
	'flow-terms-of-use-new-topic' => 'Кликом на „{{int:flow-newtopic-save}}“, прихватате услове коришћења на овом викију.',
	'flow-terms-of-use-reply' => 'Кликом на „{{int:flow-reply-submit}}“, прихватате услове коришћења на овом викију.',
	'flow-terms-of-use-edit' => 'Чувањем измена, прихватате услове коришћења на овом викију.',
);

/** Serbian (Latin script) (srpski (latinica)‎)
 * @author Milicevic01
 */
$messages['sr-el'] = array(
	'flow-preview' => 'Pretpregled',
);

/** Swedish (svenska)
 * @author Ainali
 * @author Jopparn
 * @author Lokal Profil
 * @author Tobulos1
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'flow-desc' => 'Arbetsflödeshanteringssystem',
	'flow-talk-taken-over' => 'Denna diskussionssida har tagits över av ett [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow-forum].',
	'log-name-flow' => 'Flödesaktivitetslogg',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|raderade}} ett [$4 inlägg] på [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|återställde}} ett [$4 inlägg] på [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|censurerade}} ett [$4 inlägg] på [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|raderade}} ett [$4 inlägg] på [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|raderade}} ett [$4 ämne] på [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|återställde}} ett [$4 ämne] på [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|censurerade}} ett [$4 ämne] på [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|raderade}} ett [$4 ämne] på [[$3]]',
	'flow-user-moderated' => 'Modererad användare',
	'flow-edit-header-link' => 'Redigera sidhuvud',
	'flow-header-empty' => 'Denna diskussionssida har för närvarande inget sidhuvud.',
	'flow-post-moderated-toggle-hide-show' => 'Visa kommentar {{GENDER:$1|dold}} av $2',
	'flow-post-moderated-toggle-delete-show' => 'Visa kommentar {{GENDER:$1|raderad}} av $2',
	'flow-post-moderated-toggle-suppress-show' => 'Visa kommentar {{GENDER:$1|censurerad}} av $2',
	'flow-post-moderated-toggle-hide-hide' => 'Dölj kommentar {{GENDER:$1|dold}} av $2',
	'flow-post-moderated-toggle-delete-hide' => 'Dölj kommentar {{GENDER:$1|raderad}} av $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Dölj kommentar {{GENDER:$1|censurerad}} av $2',
	'flow-hide-post-content' => 'Denna kommentar {{GENDER:$1|doldes}} av $2',
	'flow-hide-title-content' => 'Detta ämne {{GENDER:$1|doldes}} av $2',
	'flow-hide-header-content' => '{{GENDER:$1|Dold}} av $2',
	'flow-delete-post-content' => 'Denna kommentar {{GENDER:$1|raderades}} av $2',
	'flow-delete-title-content' => 'Detta ämne {{GENDER:$1|raderades}} av $2',
	'flow-delete-header-content' => '{{GENDER:$1|Raderades}} av $2',
	'flow-suppress-post-content' => 'Denna kommentar {{GENDER:$1|censurerades}} av $2',
	'flow-suppress-title-content' => 'Detta ämne {{GENDER:$1|censurerades}} av $2',
	'flow-suppress-header-content' => '{{GENDER:$1|Censurerades}} av $2',
	'flow-suppress-usertext' => '<em>Användarnamn censurerat</em>',
	'flow-post-actions' => 'Åtgärder',
	'flow-topic-actions' => 'Åtgärder',
	'flow-cancel' => 'Avbryt',
	'flow-preview' => 'Förhandsgranska',
	'flow-show-change' => 'Visa ändringar',
	'flow-last-modified-by' => 'Senast {{GENDER:$1|ändrad}} av $1',
	'flow-stub-post-content' => '"På grund av ett tekniskt fel, kunde detta inlägg inte hämtas."',
	'flow-newtopic-title-placeholder' => 'Nytt ämne',
	'flow-newtopic-content-placeholder' => 'Lägg till några detaljer om du vill',
	'flow-newtopic-header' => 'Lägg till ett nytt ämne',
	'flow-newtopic-save' => 'Lägg till ämne',
	'flow-newtopic-start-placeholder' => 'Starta ett nytt ämne',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Kommentera}} på "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|Svara}} på $1',
	'flow-reply-submit' => '{{GENDER:$1|Svara}}',
	'flow-reply-link' => '{{GENDER:$1|Svara}}',
	'flow-thank-link' => '{{GENDER:$1|Tacka}}',
	'flow-post-edited' => 'Inlägg {{GENDER:$1|redigerat}} av $1 $2',
	'flow-post-action-view' => 'Permanent länk',
	'flow-post-action-post-history' => 'Historik',
	'flow-post-action-suppress-post' => 'Censurera',
	'flow-post-action-delete-post' => 'Radera',
	'flow-post-action-hide-post' => 'Dölj',
	'flow-post-action-edit-post' => 'Redigera',
	'flow-post-action-restore-post' => 'Återställ inlägg',
	'flow-topic-action-view' => 'Permanent länk',
	'flow-topic-action-watchlist' => 'Bevakningslista',
	'flow-topic-action-edit-title' => 'Redigera rubrik',
	'flow-topic-action-history' => 'Historik',
	'flow-topic-action-hide-topic' => 'Dölj ämne',
	'flow-topic-action-delete-topic' => 'Radera ämne',
	'flow-topic-action-suppress-topic' => 'Censurera ämne',
	'flow-topic-action-restore-topic' => 'Återställ ämne',
	'flow-error-http' => 'Ett fel uppstod när servern kontaktades.',
	'flow-error-other' => 'Ett oväntat fel uppstod.',
	'flow-error-external' => 'Ett fel uppstod.<br />Felmeddelandet var: $1',
	'flow-error-edit-restricted' => 'Du har inte behörighet att redigera detta inlägg.',
	'flow-error-external-multi' => 'Fel uppstod.<br />$1',
	'flow-error-missing-content' => 'Inlägget har inget innehåll. Innehåll krävs för att spara ett inlägg.',
	'flow-error-missing-title' => 'Ämnet har ingen rubrik. En rubrik krävs för att spara ett ämne.',
	'flow-error-parsoid-failure' => 'Det gick inte att parsa innehållet på grund av ett Parsoid-fel.',
	'flow-error-missing-replyto' => 'Ingen "replyTo"-parameter tillhandahölls. Denna parameter krävs för åtgärden "svara".',
	'flow-error-invalid-replyto' => '"replyTo"-parametern var ogiltig. Det angivna inlägget kunde inte hittas.',
	'flow-error-delete-failure' => 'Radering av detta objekt misslyckades.',
	'flow-error-hide-failure' => 'Döljandet av detta objekt misslyckades.',
	'flow-error-missing-postId' => 'Ingen "postId"-parameter tillhandahölls. Denna parameter krävs för att påverka ett inlägg.',
	'flow-error-invalid-postId' => 'Parametern "postId" var ogiltig. Det angivna inlägget ($1) kunde inte hittas.',
	'flow-error-restore-failure' => 'Det gick inte att återställa objektet.',
	'flow-error-invalid-moderation-state' => 'Ett ogiltigt värde angavs för moderationState',
	'flow-error-invalid-moderation-reason' => 'Vänligen ange en orsak för moderationen',
	'flow-error-not-allowed' => 'Otillräcklig behörighet att utföra denna åtgärd',
	'flow-error-title-too-long' => 'Ämnesrubriker är begränsade till $1 {{PLURAL:$1|byte|bytes}}.',
	'flow-error-no-existing-workflow' => 'Detta arbetsflöde finns inte ännu.',
	'flow-error-not-a-post' => 'En ämnesrubrik kan inte sparas som ett inlägg.',
	'flow-error-missing-header-content' => 'Sidhuvudet har inget innehåll. Innehåll krävs för att spara ett sidhuvud.',
	'flow-error-missing-prev-revision-identifier' => 'Tidigare omarbetningsidentifieraren saknas.',
	'flow-error-prev-revision-mismatch' => 'En annan användare redigerade just inlägget för några sekunder sedan. Är du säker på att du vill skriva över de senaste ändringarna?',
	'flow-error-prev-revision-does-not-exist' => 'Kunde inte hitta den tidigare omarbetningen.',
	'flow-error-default' => 'Ett fel har uppstått.',
	'flow-error-invalid-input' => 'Ett ogiltigt värde angavs för att läsa in flödesinnehållet.',
	'flow-error-invalid-title' => 'Ogiltig sidrubrik angavs.',
	'flow-error-fail-load-history' => 'Innehållet i historiken kunde inte läsas in.',
	'flow-error-missing-revision' => 'Det gick inte att hitta en revision för att ladda flödesinnehållet.',
	'flow-error-fail-commit' => 'Flödesinnehållet kunde inte sparas.',
	'flow-error-insufficient-permission' => 'Otillräcklig behörighet för att komma åt innehållet.',
	'flow-error-revision-comparison' => 'Diff-funktionen kan endast användas för två revideringar som hör till samma post.',
	'flow-error-missing-topic-title' => 'Kunde inte hitta ämnesrubriken för det aktuella arbetsflödet.',
	'flow-error-fail-load-data' => 'Det gick inte att läsa in de begärda uppgifterna.',
	'flow-error-invalid-workflow' => 'Kunde inte hitta det önskade arbetsflödet.',
	'flow-error-process-data' => 'Ett fel uppstod under bearbetning av uppgifterna i din begäran.',
	'flow-error-process-wikitext' => 'Ett fel uppstod under bearbetning av HTML/wikitext konvertering.',
	'flow-error-no-index' => 'Det gick inte att hitta ett index för att utföra datasökning.',
	'flow-edit-header-submit' => 'Spara sidhuvud',
	'flow-edit-header-submit-overwrite' => 'Skriv över sidhuvudet',
	'flow-edit-title-submit' => 'Ändra rubrik',
	'flow-edit-title-submit-overwrite' => 'Skriva över rubriken',
	'flow-edit-post-submit' => 'Skicka ändringar',
	'flow-edit-post-submit-overwrite' => 'Skriver över ändringar',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|redigerade}} en [$3 kommentar] på $4.',
	'flow-rev-message-reply' => '$1 [$3 {{GENDER:$2|kommenterade}}] på $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>{{PLURAL:$1|En kommentar|$1 kommentarer}}</strong> har {{PLURAL:$1|lagts till}}.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|skapade}} ämnet [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|ändrade}} ämnesrubriken till [$3 $4] från $5.',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|skapade}} sidhuvudet.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|redigerade}} sidhuvudet.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|dolde}} en [$4 kommentar] på $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|raderade}} en [$4 kommentar] på $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|censurerade}} en [$4 kommentar] på $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|återställde}} en [$4 kommentar] på $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|dolde}} [$4 ämnet] på $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|tog bort}} [$4 ämnet] på $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|censurerade}} [$4 ämnet] på $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|återställde}} [$4 ämnet] på $6 (<em>$5</em>).',
	'flow-board-history' => '"$1" historik',
	'flow-topic-history' => 'Ämneshistorik för "$1"',
	'flow-post-history' => '"Kommenterad av {{GENDER:$2|$2}}" inläggshistorik',
	'flow-history-last4' => 'Senaste 4 timmarna',
	'flow-history-day' => 'I dag',
	'flow-history-week' => 'Senaste veckan',
	'flow-history-pages-topic' => 'Visas på [$1 "$2" forum]',
	'flow-history-pages-post' => 'Visas på [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 påbörjade detta ämne|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} och $2 {{PLURAL:$2|annan|andra}}|0=Inget deltagande ännu|2={{GENDER:$3|$3}} och {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} och {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 kommentar|$1 kommentarer |0=Bli den {{GENDER:$2|förste|första}} att kommentera!}}',
	'flow-comment-restored' => 'Återställd kommentar',
	'flow-comment-deleted' => 'Raderad kommentar',
	'flow-comment-hidden' => 'Dold kommentar',
	'flow-comment-moderated' => 'Modererad kommentar',
	'flow-paging-rev' => 'Nyare ämnen',
	'flow-paging-fwd' => 'Äldre ämnen',
	'flow-last-modified' => 'Senast ändrad $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|svarade}} på ditt <span class="plainlinks">[$5 inlägg]</span> i "$2" på "$4".',
	'flow-notification-reply-bundle' => '$1 och $5 {{PLURAL:$6|annan|andra}} {{GENDER:$1|svarade}} på ditt <span class="plainlinks">[$4 inlägg]</span> i "$2" på "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|redigerade}} ett <span class="plainlinks">[$5 inlägg]</span> i "$2" på [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 och $5 {{PLURAL:$6|annan|andra}} {{GENDER:$1|redigerade}} ett <span class="plainlinks">[$4 inlägg]</span> i "$2" på "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|skapade}} ett <span class="plainlinks">[$5 nytt ämne]</span> på [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|ändrade}} rubriken för <span class="plainlinks">[$2 $3]</span> till "$4" på [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|nämnde}} dig i {{GENDER:$1|hans|hennes|sitt}} <span class="plainlinks">[$2 inlägg]</span> i "$3" på "$4".',
	'flow-notification-link-text-view-post' => 'Visa inlägg',
	'flow-notification-link-text-view-board' => 'Visa forum',
	'flow-notification-link-text-view-topic' => 'Visa ämne',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|svarade}} på ditt inlägg',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|svarade}} på ditt inlägg i "$2" på "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 och $4 {{PLURAL:$5|annan|andra}} {{GENDER:$1|svarade}} på ditt inlägg i "$2" på "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|omnämnde}} dig på "$2"',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|omnämnde}} dig i {{GENDER:$1|hans|hennes|sitt}} inlägg i "$2" på "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|redigerade}} ett inlägg',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|redigerade}} ett inlägg i "$2" på "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 och $4 {{PLURAL:$5|annan|andra}} {{GENDER:$1|redigerade}} ett inlägg i "$2" på "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|byt namn på}} ditt ämne',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|byt namn på}} ditt ämne "$2" till "$3" på "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|skapade}} ett nytt ämne på "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|skapade}} ett ny ämne med rubriken "$2" på $3',
	'echo-category-title-flow-discussion' => 'Flöde',
	'echo-pref-tooltip-flow-discussion' => 'Meddela mig när åtgärder som rör mig förekommer i flödet.',
	'flow-link-post' => 'inlägg',
	'flow-link-topic' => 'ämne',
	'flow-link-history' => 'historik',
	'flow-moderation-reason-placeholder' => 'Ange din orsak här',
	'flow-moderation-title-suppress-post' => 'Censurera inlägget?',
	'flow-moderation-title-delete-post' => 'Radera inlägget?',
	'flow-moderation-title-hide-post' => 'Dölj inlägget?',
	'flow-moderation-title-restore-post' => 'Återställ inlägget?',
	'flow-moderation-intro-suppress-post' => 'Var god {{GENDER:$3|förklara}} varför du censurerar detta inlägg.',
	'flow-moderation-intro-delete-post' => 'Var god {{GENDER:$3|förklara}} varför du raderar detta inlägg.',
	'flow-moderation-intro-hide-post' => 'Var god {{GENDER:$3|förklara}} varför du döljer detta inlägg.',
	'flow-moderation-intro-restore-post' => 'Var god {{GENDER:$3|förklara}} varför du återställer detta inlägg.',
	'flow-moderation-confirm-suppress-post' => 'Censurera',
	'flow-moderation-confirm-delete-post' => 'Radera',
	'flow-moderation-confirm-hide-post' => 'Dölj',
	'flow-moderation-confirm-restore-post' => 'Återställ',
	'flow-moderation-confirmation-suppress-post' => 'Inlägget censurerades framgångsrikt.
{{GENDER:$2|Överväg}} att ge feedback åt $1 gällande detta inlägg.',
	'flow-moderation-confirmation-delete-post' => 'Inlägget raderades framgångsrikt.
{{GENDER:$2|Överväg}} att ge feedback åt $1 gällande detta inlägg.',
	'flow-moderation-confirmation-hide-post' => 'Inlägget doldes framgångsrikt.
{{GENDER:$2|Överväg}} att ge feedback åt $1 gällande detta inlägg.',
	'flow-moderation-confirmation-restore-post' => 'Du har återställt ovanstående inlägg.',
	'flow-moderation-title-suppress-topic' => 'Censurera ämnet?',
	'flow-moderation-title-delete-topic' => 'Radera ämnet?',
	'flow-moderation-title-hide-topic' => 'Dölja ämnet?',
	'flow-moderation-title-restore-topic' => 'Återställa ämnet?',
	'flow-moderation-intro-suppress-topic' => 'Var god {{GENDER:$3|förklara}} varför du censurerar detta ämne.',
	'flow-moderation-intro-delete-topic' => 'Var god {{GENDER:$3|förklara}} varför du raderar detta ämne.',
	'flow-moderation-intro-hide-topic' => 'Var god {{GENDER:$3|förklara}} varför du döljer detta ämne.',
	'flow-moderation-intro-restore-topic' => 'Var god {{GENDER:$3|förklara}} varför du återställer detta ämne.',
	'flow-moderation-confirm-suppress-topic' => 'Censurera',
	'flow-moderation-confirm-delete-topic' => 'Ta bort',
	'flow-moderation-confirm-hide-topic' => 'Dölj',
	'flow-moderation-confirm-restore-topic' => 'Återställ',
	'flow-moderation-confirmation-suppress-topic' => 'Ämnet censurerades framgångsrikt.
{{GENDER:$2|Överväg}} att ge feedback åt $1 gällande detta ämne.',
	'flow-moderation-confirmation-delete-topic' => 'Ämnet raderades framgångsrikt.
{{GENDER:$2|Överväg}} att ge feedback åt $1 gällande detta ämne.',
	'flow-moderation-confirmation-hide-topic' => 'Ämnet doldes framgångsrikt.
{{GENDER:$2|Överväg}} att ge feedback åt $1 gällande detta ämne.',
	'flow-moderation-confirmation-restore-topic' => 'Du har återställt detta ämne.',
	'flow-topic-permalink-warning' => 'Detta ämne påbörjades den [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Detta ämne startades på [$2 {{GENDER:$1|$1}}s forum]',
	'flow-revision-permalink-warning-post' => 'Detta är en permanent länk till en enda version av det här inlägget.
Denna version är från $1.
Du kan se [$5 skillnader från föregående version], eller visa andra versioner på [$4 inläggets historiksida].',
	'flow-revision-permalink-warning-post-first' => 'Detta är en permanent länk till den första versionen av det här inlägget.
Du kan visa senare versioner på [$4 inläggets historiksida].',
	'flow-compare-revisions-revision-header' => 'Version av {{GENDER:$2|$2}} från $1',
	'flow-compare-revisions-header-post' => 'Denna sida visar {{GENDER:$3|förändringar}} mellan två versioner av ett inlägg av $3 i ämnet "[$5 $2]" på [$4 $1].
Du kan se andra versioner av detta inlägg genom dess [$6 historiksida].',
	'flow-topic-collapsed-one-line' => 'Liten vy',
	'flow-topic-collapsed-full' => 'Komprimerad vy',
	'flow-topic-complete' => 'Full vy',
	'flow-terms-of-use-new-topic' => 'Genom att klicka på "{{int:flow-newtopic-save}}" godkänner du användarvillkoren för denna wiki.',
	'flow-terms-of-use-reply' => 'Genom att klicka på "{{int:flow-reply-submit}}" godkänner du användarvillkoren för denna wiki.',
	'flow-terms-of-use-edit' => 'Genom att spara dina ändringar godkänner du användarvillkoren för denna wiki.',
);

/** Telugu (తెలుగు)
 * @author Chaduvari
 * @author Ravichandra
 */
$messages['te'] = array(
	'flow-post-action-post-history' => 'చరిత్ర',
	'flow-post-action-edit-post' => 'సవరించు',
	'flow-topic-action-history' => 'చరిత్ర',
	'flow-edit-header-submit-overwrite' => 'శీర్షికను తిరగరాయి',
	'flow-edit-title-submit-overwrite' => 'పేరును తిరగరాయి',
	'flow-edit-post-submit-overwrite' => 'మార్పులను తిరగరాయి',
	'flow-terms-of-use-new-topic' => '"{{int:flow-newtopic-save}}" నొక్కడం ద్వారా, మీరు ఈ వికీ విధివిధానాలకు కట్టుబడుతున్నారు.',
	'flow-terms-of-use-reply' => '"{{int:flow-reply-submit}}" నొక్కడం ద్వారా, మీరు ఈ వికీ విధివిధానాలను అంగీకరిస్తున్నట్లు.',
	'flow-terms-of-use-edit' => 'మీ మార్పులను భద్రపరచడం ద్వారా, మీరు ఈ వికీ విధివిధానాలను అంగీకరిస్తున్నట్లు.',
);

/** Tagalog (Tagalog)
 * @author Jewel457
 */
$messages['tl'] = array(
	'flow-post-moderated-toggle-hide-show' => 'Ipakita ang pagpuna.', # Fuzzy
	'flow-post-moderated-toggle-delete-show' => 'Ipakita ang mungkahi', # Fuzzy
	'flow-post-moderated-toggle-suppress-show' => 'Ipakita ang mga puna.', # Fuzzy
	'flow-post-moderated-toggle-delete-hide' => 'Itago ang pagpuna.', # Fuzzy
	'flow-post-moderated-toggle-suppress-hide' => 'Itago ang mungkahi', # Fuzzy
	'flow-post-action-post-history' => 'Kasaysayan',
	'flow-post-action-edit-post' => 'Baguhin',
);

/** Turkish (Türkçe)
 * @author Rapsar
 * @author Rhinestorm
 */
$messages['tr'] = array(
	'flow-notification-mention' => '$1, "$4" sayfasındaki "$3" başlığındaki [$2 değişikliğinde] sizden {{GENDER:$1|bahsetti}}', # Fuzzy
	'flow-notification-mention-email-subject' => '$1, $2 sayfasında sizden {{GENDER:$1|bahsetti}}', # Fuzzy
	'flow-notification-mention-email-batch-body' => '$1, "$3" sayfasındaki "$2" başlığında sizden {{GENDER:$1|bahsetti}}', # Fuzzy
	'flow-link-history' => 'geçmiş',
	'flow-anon-warning' => 'Oturum açmadınız.',
);

/** Uyghur (Arabic script) (ئۇيغۇرچە)
 * @author Tel'et
 */
$messages['ug-arab'] = array(
	'flow-post-action-delete-post' => 'ئۆچۈر',
	'flow-post-action-hide-post' => 'يوشۇر',
	'flow-moderation-title-delete-post' => 'بۇ ئۇچۇرنى ئۆچۈرەمسىز؟',
	'flow-moderation-confirm-delete-post' => 'ئۆچۈر',
	'flow-moderation-confirm-restore-post' => 'ئەسلىگە كەلتۈر',
);

/** Ukrainian (українська)
 * @author Andriykopanytsia
 * @author Ата
 */
$messages['uk'] = array(
	'flow-desc' => 'Система управління робочими процесами',
	'flow-talk-taken-over' => 'Ця сторінка обговорення була перейняти від [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal стіни Потоку].',
	'log-name-flow' => 'Журнал активності потоку',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|вилучив|вилучила}} [допис $4] на [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|відновив|відновила}} [допис $4] на [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|прибрав|прибрала}} [допис $4] на [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|вилучив|вилучила}} [допис $4] на [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|вилучив|вилучила}} [тему $4] на [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|відновив|відновила}} [тему $4] на [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|прибрав|прибрала}} [$4 тему] на [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|вилучив|вилучила}} [тему $4] на [[$3]]',
	'flow-user-moderated' => 'Обмежений користувач',
	'flow-edit-header-link' => 'Редагувати заговок',
	'flow-header-empty' => 'Ця сторінка обговорення не має зараз заголовка.',
	'flow-post-moderated-toggle-hide-show' => 'Показати коментар, який {{GENDER:$1|приховав|приховала}} $2',
	'flow-post-moderated-toggle-delete-show' => 'Показати коментар, який {{GENDER:$1|вилучив|вилучила}} $2',
	'flow-post-moderated-toggle-suppress-show' => 'Показати коментар, який {{GENDER:$1|прибрав|прибрала}} $2',
	'flow-post-moderated-toggle-hide-hide' => 'Приховати коментар, який {{GENDER:$1|приховав|приховала}}  $2',
	'flow-post-moderated-toggle-delete-hide' => 'Приховати коментар, який {{GENDER:$1|вилучив|вилучила}}  $2',
	'flow-post-moderated-toggle-suppress-hide' => 'Приховати коментар, який {{GENDER:$1|прибрав|прибрала}}  $2',
	'flow-hide-post-content' => 'Цей коментар {{GENDER:$1|приховав|приховала}} $2',
	'flow-hide-title-content' => '$2 {{GENDER:$1|приховав|приховала}} цю тему',
	'flow-hide-header-content' => '{{GENDER:$1|Приховано}} $2',
	'flow-delete-post-content' => '$2 {{GENDER:$1|вилучив|вилучила}} цей коментар',
	'flow-delete-title-content' => '$2 {{GENDER:$1|вилучив|вилучила}} цю тему',
	'flow-delete-header-content' => '{{GENDER:$1|Вилучено}} $2',
	'flow-suppress-post-content' => '$2 {{GENDER:$1|прибрав|прибрала}} цей коментар',
	'flow-suppress-title-content' => '$2 {{GENDER:$1|прибрав|прибрала}} цю тему',
	'flow-suppress-header-content' => '{{GENDER:$1|Прибрано}} $2',
	'flow-suppress-usertext' => "<em>Ім'я користувача приховано</em>",
	'flow-post-actions' => 'Дії',
	'flow-topic-actions' => 'Дії',
	'flow-cancel' => 'Скасувати',
	'flow-preview' => 'Попередній перегляд',
	'flow-show-change' => 'Показати зміни',
	'flow-last-modified-by' => 'Востаннє {{GENDER:$1|змінив|змінила}} $1',
	'flow-stub-post-content' => "''Через технічну помилку цей допис не міг бути отриманим.''",
	'flow-newtopic-title-placeholder' => 'Нова тема',
	'flow-newtopic-content-placeholder' => 'Додайте деякі деталі, якщо ви хочете',
	'flow-newtopic-header' => 'Додати нову тему',
	'flow-newtopic-save' => 'Додати тему',
	'flow-newtopic-start-placeholder' => 'Почати нову тему',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Коментувати}} на "$2"',
	'flow-reply-placeholder' => 'Відповісти {{GENDER:$1|користувачу|користувачці}} $1.',
	'flow-reply-submit' => '{{GENDER:$1|Відповісти}}',
	'flow-reply-link' => '{{GENDER:$1|Відповісти}}',
	'flow-thank-link' => '{{GENDER:$1|Подякувати}}',
	'flow-post-edited' => 'Допис {{GENDER:$1|відредагував|відредагувала}} $1 $2',
	'flow-post-action-view' => 'Постійне посилання',
	'flow-post-action-post-history' => 'Історія',
	'flow-post-action-suppress-post' => 'Прибрати',
	'flow-post-action-delete-post' => 'Видалити',
	'flow-post-action-hide-post' => 'Приховати',
	'flow-post-action-edit-post' => 'Редагувати',
	'flow-post-action-restore-post' => 'Відновити публікацію',
	'flow-topic-action-view' => 'Постійне посилання',
	'flow-topic-action-watchlist' => 'Список спостереження',
	'flow-topic-action-edit-title' => 'Змінити заголовок',
	'flow-topic-action-history' => 'Історія',
	'flow-topic-action-hide-topic' => 'Приховати тему',
	'flow-topic-action-delete-topic' => 'Видалити тему',
	'flow-topic-action-suppress-topic' => 'Прибрати тему',
	'flow-topic-action-restore-topic' => 'Відновити тему',
	'flow-error-http' => 'Сталася помилка при зверненні до сервера.',
	'flow-error-other' => 'Трапилася неочікувана помилка.',
	'flow-error-external' => 'Сталася помилка.<br />Отримане повідомлення було:$1',
	'flow-error-edit-restricted' => 'Вам не дозволено редагувати цей допис.',
	'flow-error-external-multi' => 'Виявлені помилки.<br /> $1',
	'flow-error-missing-content' => 'Публікація не має ніякого вмісту. Необхідний вміст, щоб зберегти публікацію.',
	'flow-error-missing-title' => 'Тема не має назви. Потрібна назва, щоб зберегти тему.',
	'flow-error-parsoid-failure' => 'Не вдалося проаналізувати вміст через помилку Parsoid.',
	'flow-error-missing-replyto' => 'Параметр „reply-to“ не був наданий. Цей параметр є обов\'язковим для дії "відповідь".',
	'flow-error-invalid-replyto' => 'Параметр „replyTo“ неприпустимий. Не вдалося знайти вказану публікацію.',
	'flow-error-delete-failure' => 'Не вдалося видалити цей елемент.',
	'flow-error-hide-failure' => 'Приховання цього елементу не вдалося.',
	'flow-error-missing-postId' => 'Параметр „postId“ не був наданий. Цей параметр вимагає, щоб маніпулювати публікацією.',
	'flow-error-invalid-postId' => 'Параметр „postId“ неприпустимий. Не вдалося знайти вказану публікацію  ($1).',
	'flow-error-restore-failure' => 'Не вдалося виконати відновлення цього елемента.',
	'flow-error-invalid-moderation-state' => 'Неприпустиме значення було надано для стану модерування',
	'flow-error-invalid-moderation-reason' => 'Будь ласка, вкажіть причину для модерації',
	'flow-error-not-allowed' => 'Недостатні дозволи для виконання цієї дії',
	'flow-error-title-too-long' => 'Назви тем обмежені $1 {{PLURAL:$1|1=байтом|байтами}}.',
	'flow-error-no-existing-workflow' => 'Цей робочий процес ще не існує.',
	'flow-error-not-a-post' => 'Назву теми не можна зберегти як допис.',
	'flow-error-missing-header-content' => 'Заголовок не має ніякого вмісту. Необхідний вміст, щоб зберегти заголовок.',
	'flow-error-missing-prev-revision-identifier' => 'Ідентифікатор попередньої ревізії відсутній.',
	'flow-error-prev-revision-mismatch' => 'Цей допис інший користувач щойно відредагував кілька секунд тому. Ви справді бажаєте переписати нещодавні зміни?',
	'flow-error-prev-revision-does-not-exist' => 'Не вдалося знайти попередню ревізію.',
	'flow-error-default' => 'Сталася помилка.',
	'flow-error-invalid-input' => 'Неприпустиме значення було надано для завантаження потоку даних.',
	'flow-error-invalid-title' => 'Надана невірна сторінка заголовку.',
	'flow-error-fail-load-history' => 'Не вдалося завантажити зміст історії.',
	'flow-error-missing-revision' => 'Не вдалося знайти редакцію для завантаження вмісту потоку.',
	'flow-error-fail-commit' => 'Не вдалося зберегти вміст потоку.',
	'flow-error-insufficient-permission' => 'Недостатньо прав для доступу до вмісту.',
	'flow-error-revision-comparison' => 'Операції порівняння може бути зроблена лише для двох ревізій, що належать й того ж допису.',
	'flow-error-missing-topic-title' => 'Не вдалося знайти назву теми для поточного робочого циклу.',
	'flow-error-fail-load-data' => 'Не вдалося завантажити запитані дані.',
	'flow-error-invalid-workflow' => 'Не вдалося знайти запитаний робочий процес.',
	'flow-error-process-data' => 'Сталася помилка під час обробки даних у вашому запиті.',
	'flow-error-process-wikitext' => 'Сталася помилка при обробці HTML/wiki перетворення.',
	'flow-error-no-index' => 'Не вдалося знайти індекс, щоб виконати пошук у базі даних.',
	'flow-edit-header-submit' => 'Зберегти заголовок',
	'flow-edit-header-submit-overwrite' => 'Переписати заголовок',
	'flow-edit-title-submit' => 'Змінити заголовок',
	'flow-edit-title-submit-overwrite' => 'Переписати назву',
	'flow-edit-post-submit' => 'Подати зміни',
	'flow-edit-post-submit-overwrite' => 'Переписати зміни',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|відредагував|відредагувала}} [коментар $3] у темі $4',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|додав|додала}} [коментар $3] у тему $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|коментар|коментарі|коментарів}} </strong> {{PLURAL:$1|1=був доданий|були додані}}.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|створив|створила}} тему [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|відредагував|відредагувала}} назву теми на [$3 $4] із $5.',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|створив|створила}} заголовок.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|змінив|змінила}} заголовок.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|приховав|приховала}} [коментар $4] у темі $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|видалив|видалила}} [коментар $4] у темі $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|подавив|подавила}} [коментар $4] у темі $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|відновив|відновила}} [коментар $4] у темі $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|приховав|приховала}} [тему $4] у темі $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|вилучив|вилучила}} [тему $4]  $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|прибрав|прибрала}} [тему $4]  $6  (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|відновив|відновила}} [тему $4]  $6 (<em>$5</em>).',
	'flow-board-history' => 'Історія "$1"',
	'flow-topic-history' => 'Історія теми "$1"',
	'flow-post-history' => 'Коментарі від історії дописів {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Останні 4 години',
	'flow-history-day' => 'Сьогодні',
	'flow-history-week' => 'Останній тиждень',
	'flow-history-pages-topic' => 'З\'являється на [стіні $1  "$2"]',
	'flow-history-pages-post' => "З'являється на [$1 $2]",
	'flow-topic-participants' => '{{PLURAL:$1|$3 {{GENDER:$3|розпочав цю тему|розпочала цю тему}}|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} та {{PLURAL:$2|інший|інші|інших}}|0=Ще не має учасників|2={{GENDER:$3|$3}} та {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} та {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 коментар|$1 коментарі|$1 коментарів|0={{GENDER:$2|Залиште перший коментар!}}}}',
	'flow-comment-restored' => 'Відновлений коментар',
	'flow-comment-deleted' => 'Видалений коментар',
	'flow-comment-hidden' => 'Прихований коментар',
	'flow-comment-moderated' => 'Модерований коментар',
	'flow-paging-rev' => 'Новіші теми',
	'flow-paging-fwd' => 'Старіші теми',
	'flow-last-modified' => 'Остання зміна про $1',
	'flow-notification-reply' => '$1  {{GENDER:$1|відповів|відповіла}} на ваше <span class="plainlinks">[повідомлення $5]</span> у "$2" на "$4".',
	'flow-notification-reply-bundle' => '$1 та $5 {{PLURAL:$6|інший|інші|інших}} {{GENDER:$1|відповіли}} на ваш <span class="plainlinks">[допис $4]</span> у $2 на "$3".',
	'flow-notification-edit' => '$1  {{GENDER:$1|відредагував|відредагувала}}  <span class="plainlinks">[повідомлення $5]</span> у $2 на [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 та $5 {{PLURAL:$6|інший|інші|інших}} {{GENDER:$1|відредагував|відредагувала}} <span class="plainlinks">[$4 допис]</span> у $2 на "$3".',
	'flow-notification-newtopic' => '$1  {{GENDER:$1|створив|створила}} <span class="plainlinks">[нову тему $5]</span> на [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1  {{GENDER:$1|змінив|змінила}} назву <span class="plainlinks">[$2 $3]</span> на "$4" у [[$5|$6]]',
	'flow-notification-mention' => '$1 {{GENDER:$1|згадав|згадала|згадали}} вас у {{GENDER:$1|своєму|своєму|своєму}}  <span class="plainlinks">[$2 дописі]</span> у "$3" на "$4"',
	'flow-notification-link-text-view-post' => 'Переглянути допис',
	'flow-notification-link-text-view-board' => 'Переглянути стіну',
	'flow-notification-link-text-view-topic' => 'Перегляд теми',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|відповів|відповіла}} на ваш допис',
	'flow-notification-reply-email-batch-body' => '$1  {{GENDER:$1|відповів|відповіла}} на ваш допис у "$2" на $3.',
	'flow-notification-reply-email-batch-bundle-body' => '$1 та $4 {{PLURAL:$5|інший|інші|інших}} {{GENDER:$1|відповіли}} на ваш допис у "$2" на "$3".',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|згадав|згадала}} вас на "$2"',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|згадав|згадала|згадали}} вас у  {{GENDER:$1|своєму|своєму|своєму}} дописі у "$2" на "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|відредагував|відредагувала}} допис',
	'flow-notification-edit-email-batch-body' => '$1  {{GENDER:$1|відредагував|відредагувала}} допис у "$2" на „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 та $4 {{PLURAL:$5|інший|інші|інших}} {{GENDER:$1|відредагував|відредагувала}} допис у "$2" на "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|перейменував|перейменувала}} вашу тему',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|перейменував|перейменувала}} вашу тему   з „$2“ на „$3“  у „$4“',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|створив|створила}} нову тему на "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|створив|створила}} нову тему під назвою "$2" на $3',
	'echo-category-title-flow-discussion' => 'Потік',
	'echo-pref-tooltip-flow-discussion' => "Повідомляти, коли відбуваються дії, пов'язані зі мною в потоці.",
	'flow-link-post' => 'допис',
	'flow-link-topic' => 'тема',
	'flow-link-history' => 'історія',
	'flow-moderation-reason-placeholder' => 'Введіть вашу причина тут',
	'flow-moderation-title-suppress-post' => 'Прибрати допис?',
	'flow-moderation-title-delete-post' => 'Видалити допис?',
	'flow-moderation-title-hide-post' => 'Приховати допис?',
	'flow-moderation-title-restore-post' => 'Відновити допис?',
	'flow-moderation-intro-suppress-post' => 'Будь ласка, {{GENDER:$3|поясніть}}, чому ви прибрали цей допис.',
	'flow-moderation-intro-delete-post' => 'Будь ласка, {{GENDER:$3|поясніть,}} чому ви хочете видалити цей допис.',
	'flow-moderation-intro-hide-post' => 'Будь ласка, {{GENDER:$3|поясніть,}} чому ви приховуєте цей допис.',
	'flow-moderation-intro-restore-post' => 'Будь ласка, {{GENDER:$3|поясніть,}} чому ви відновлюєте цей допис.',
	'flow-moderation-confirm-suppress-post' => 'Прибрати',
	'flow-moderation-confirm-delete-post' => 'Видалити',
	'flow-moderation-confirm-hide-post' => 'Приховати',
	'flow-moderation-confirm-restore-post' => 'Відновити',
	'flow-moderation-confirmation-suppress-post' => 'Допис успішно усунено.
Розгляньте відгук {{GENDER:$2|наданий}} $1 на цей допис.',
	'flow-moderation-confirmation-delete-post' => 'Цей допис успішно вилучено.
Розгляньте відгук, {{GENDER:$2|наданий}} $1, на цей допис.',
	'flow-moderation-confirmation-hide-post' => 'Цей допис успішно приховано.
Розгляньте відгук, {{GENDER:$2|наданий}} $1, на цей допис.',
	'flow-moderation-confirmation-restore-post' => 'Ви успішно відновили публікацію вище.',
	'flow-moderation-title-suppress-topic' => 'Прибрати тему?',
	'flow-moderation-title-delete-topic' => 'Видалити тему?',
	'flow-moderation-title-hide-topic' => 'Приховати тему?',
	'flow-moderation-title-restore-topic' => 'Відновити тему?',
	'flow-moderation-intro-suppress-topic' => 'Будь ласка, {{GENDER:$3|поясніть,}} чому ви прибрали цю тему.',
	'flow-moderation-intro-delete-topic' => 'Будь ласка, {{GENDER:$3|поясніть,}} чому ви вилучаєте цю тему.',
	'flow-moderation-intro-hide-topic' => 'Будь ласка, {{GENDER:$3|поясніть}}, чому ви приховуєте цю тему.',
	'flow-moderation-intro-restore-topic' => 'Будь ласка, {{GENDER:$3|поясніть,}} чому ви відновлюєте цю тему.',
	'flow-moderation-confirm-suppress-topic' => 'Прибрати',
	'flow-moderation-confirm-delete-topic' => 'Видалити',
	'flow-moderation-confirm-hide-topic' => 'Приховати',
	'flow-moderation-confirm-restore-topic' => 'Відновити',
	'flow-moderation-confirmation-suppress-topic' => 'Ця тема успішно усунена.
Розгляньте відгук {{GENDER:$2|наданий}} $1 на цю тему.',
	'flow-moderation-confirmation-delete-topic' => 'Тему успішно вилучено.
Розгляньте відгук, {{GENDER:$2|наданий}} $1, на цю тему.',
	'flow-moderation-confirmation-hide-topic' => 'Тема успішно прихована.
Розгляньте відгук {{GENDER:$2|наданий}} $1 на цю тему.',
	'flow-moderation-confirmation-restore-topic' => 'Ви успішно відновили цю тему.',
	'flow-topic-permalink-warning' => 'Ця тема розпочата [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Ця тема розпочата на [$2 стіні {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Це постійне посилання на окрему версію цього допису.
Це версія за $1.
Ви можете подивитися [відмінності від попередньої версії $5] або переглянути інші версії на [сторінці історії допису $4].',
	'flow-revision-permalink-warning-post-first' => 'Це постійне посилання на першу версію цього допису.
Ви можете переглядати пізніші версії на [сторінці історії допису $4].',
	'flow-revision-permalink-warning-header' => 'Це постійне посилання на окрему версію заголовку.
Дана версія за $1.
Ви можете подивитися [$3 відмінності від попередньої версії] або переглянути інші версії на [$2 сторінці стіни історії].',
	'flow-revision-permalink-warning-header-first' => 'Це постійне посилання на першу версію цього заголовку.
Ви можете переглядати пізніші версії на [$2 сторінці історії стіни].',
	'flow-compare-revisions-revision-header' => 'Версія від {{GENDER:$2|$2}} за $1',
	'flow-compare-revisions-header-post' => 'Ця сторінка відображає зміни між двома версіями допису від $3 у розділі "[$5 $2]" на [$4 $1].
Ви можете побачити інші версії цього допису на його [сторінці історії $6].',
	'flow-compare-revisions-header-header' => 'На цій сторінці відображаються {{GENDER:$2|зміни}} між двома версіями заголовку на [$3  $1].
Ви можете побачити інші версії заголовку на його [$4  сторінці історії].',
	'flow-topic-collapsed-one-line' => 'Малий вигляд',
	'flow-topic-collapsed-full' => 'Згорнутий вигляд',
	'flow-topic-complete' => 'Повний вигляд',
	'flow-terms-of-use-new-topic' => 'Натиснувши на кнопку "{{int:flow-newtopic-save}}", ви погоджуєтеся з умовами використання даного вікі.',
	'flow-terms-of-use-reply' => 'Натиснувши кнопку "{{int:flow-reply-submit}}", ви погоджуєтеся з умовами використання для цього вікі.',
	'flow-terms-of-use-edit' => 'Зберігаючи зміни, ви погоджуєтесь з умовами використання для цього вікі.',
);

/** Vietnamese (Tiếng Việt)
 * @author Baonguyen21022003
 * @author Minh Nguyen
 * @author Withoutaname
 */
$messages['vi'] = array(
	'flow-desc' => 'Hệ thống quản lý luồng làm việc',
	'flow-talk-taken-over' => 'Trang thảo luận này đã được thay thế bằng một [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal bảng tin nhắn Flow].',
	'log-name-flow' => 'Nhật trình hoạt động Flow',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2}}đã xóa một [$4 bài đăng] tại [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2}}đã phục hồi một [$4 bài đăng] tại [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2}}đã đàn áp một [$4 chủ đề] tại [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2}}đã xóa một [$4 bài đăng] tại [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2}}đã xóa một [$4 chủ đề] tại [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2}}đã phục hồi một [$4 chủ đề] tại [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2}}đã đàn áp một [$4 chủ đề] tại [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2}}đã xóa một [$4 chủ đề] tại [[$3]]',
	'flow-user-moderated' => 'Người dùng bị kiểm duyệt',
	'flow-edit-header-link' => 'Sửa đầu đề',
	'flow-header-empty' => 'Trang thảo luận này hiện không có đầu đề.',
	'flow-post-moderated-toggle-hide-show' => 'Hiển thị bình luận đã bị $2 {{GENDER:$1}}ẩn',
	'flow-post-moderated-toggle-delete-show' => 'Hiển thị bình luận đã bị $2 {{GENDER:$1}}xóa',
	'flow-post-moderated-toggle-suppress-show' => 'Hiển thị bình luận đã bị $2 {{GENDER:$1}}đàn áp',
	'flow-post-moderated-toggle-hide-hide' => 'Ẩn bình luận đã bị $2 {{GENDER:$1}}ẩn',
	'flow-post-moderated-toggle-delete-hide' => 'Ẩn bình luận đã bị $2 {{GENDER:$1}}xóa',
	'flow-post-moderated-toggle-suppress-hide' => 'Ẩn bình luận đã bị $2 {{GENDER:$1}}đàn áp',
	'flow-hide-post-content' => 'Bình luận này đã bị {{GENDER:$1}}ẩn bởi $2',
	'flow-hide-title-content' => 'Chủ đề này đã bị {{GENDER:$1}}ẩn bởi $2',
	'flow-hide-header-content' => '{{GENDER:$1}}Ẩn bởi $2',
	'flow-delete-post-content' => 'Bình luận này đã bị {{GENDER:$1}}xóa bởi $2',
	'flow-delete-title-content' => 'Chủ đề này đã bị {{GENDER:$1}}xóa bởi $2',
	'flow-delete-header-content' => '{{GENDER:$1}}Xóa bởi $2',
	'flow-suppress-post-content' => 'Bình luận này đã bị {{GENDER:$1}}đàn áp bởi $2',
	'flow-suppress-title-content' => 'Chủ đề này đã bị {{GENDER:$1}}đàn áp bởi $2',
	'flow-suppress-header-content' => '{{GENDER:$1}}Đàn áp bởi $2',
	'flow-suppress-usertext' => '<em>Tên người dùng bị đàn áp</em>',
	'flow-post-actions' => 'Tác vụ',
	'flow-topic-actions' => 'Tác vụ',
	'flow-cancel' => 'Hủy bỏ',
	'flow-preview' => 'Xem trước',
	'flow-show-change' => 'Xem thay đổi',
	'flow-last-modified-by' => 'Sửa đổi lần cuối cùng bởi $1',
	'flow-stub-post-content' => "''Không thể lấy bài đăng này do một lỗi kỹ thuật.''",
	'flow-newtopic-title-placeholder' => 'Chủ đề mới',
	'flow-newtopic-content-placeholder' => 'Thêm những chi tiết theo ý bạn',
	'flow-newtopic-header' => 'Thêm chủ đề mới',
	'flow-newtopic-save' => 'Thêm chủ đề',
	'flow-newtopic-start-placeholder' => 'Bắt đầu cuộc thảo luận mới',
	'flow-reply-topic-placeholder' => '{{GENDER:$1}}Bình luận về “$2”',
	'flow-reply-placeholder' => 'Trả lời $1',
	'flow-reply-submit' => '{{GENDER:$1}}Trả lời',
	'flow-reply-link' => '{{GENDER:$1}}Trả lời',
	'flow-thank-link' => '{{GENDER:$1}}Cảm ơn',
	'flow-post-edited' => 'Bài đăng được sửa đổi bởi $1 $2',
	'flow-post-action-view' => 'Liên kết thường trực',
	'flow-post-action-post-history' => 'Lịch sử',
	'flow-post-action-suppress-post' => 'Đàn áp',
	'flow-post-action-delete-post' => 'Xóa',
	'flow-post-action-hide-post' => 'Ẩn',
	'flow-post-action-edit-post' => 'Sửa đổi',
	'flow-post-action-restore-post' => 'Phục hồi bài đăng',
	'flow-topic-action-view' => 'Liên kết thường trực',
	'flow-topic-action-watchlist' => 'Danh sách theo dõi',
	'flow-topic-action-edit-title' => 'Sửa tiêu đề',
	'flow-topic-action-history' => 'Lịch sử',
	'flow-topic-action-hide-topic' => 'Ẩn chủ đề',
	'flow-topic-action-delete-topic' => 'Xóa chủ đề',
	'flow-topic-action-suppress-topic' => 'Đàn áp chủ đề',
	'flow-topic-action-restore-topic' => 'Phục hồi chủ đề',
	'flow-error-http' => 'Đã xuất hiện lỗi khi liên lạc với máy chủ.',
	'flow-error-other' => 'Đã xuất hiện lỗi bất ngờ.',
	'flow-error-external' => 'Đã xuất hiện lỗi.<br />Lỗi nhận được là: $1',
	'flow-error-edit-restricted' => 'Bạn không có quyền sửa đổi bài đăng này.',
	'flow-error-external-multi' => 'Đã xuất hiện lỗi.<br />$1',
	'flow-error-missing-content' => 'Bài đăng không có nội dung. Bài đăng phải có nội dung để lưu.',
	'flow-error-missing-title' => 'Chủ đề không có tiêu đề. Chủ đề phải có tiêu đề để lưu.',
	'flow-error-parsoid-failure' => 'Không thể phân tích nội dung vì Parsoid bị thất bại.',
	'flow-error-missing-replyto' => 'Tham số “replyTo” không được cung cấp. Tham số này cần để thực hiện tác vụ “trả lời”.',
	'flow-error-invalid-replyto' => 'Tham số “replyTo” có giá trị không hợp lệ. Không tìm thấy bài đăng.',
	'flow-error-delete-failure' => 'Thất bại khi xóa mục này.',
	'flow-error-hide-failure' => 'Thất bại khi ẩn mục này.',
	'flow-error-missing-postId' => 'Tham số “postId” không được cung cấp. Tham số này cần để xóa hoặc phục hồi bài đăng.',
	'flow-error-invalid-postId' => 'Tham số “postId” có giá trị không hợp lệ. Không tìm thấy bài đăng được chỉ định ($1).',
	'flow-error-restore-failure' => 'Thất bại khi phục hồi mục này.',
	'flow-error-invalid-moderation-state' => 'Một giá trị không hợp lệ được cung cấp cho moderationState',
	'flow-error-invalid-moderation-reason' => 'Xin vui lòng cung cấp một lý do kiểm duyệt',
	'flow-error-not-allowed' => 'Không có đủ quyền để thực hiện tác vụ này',
	'flow-error-title-too-long' => 'Tên chủ đề không được dài hơn $1 byte.',
	'flow-error-no-existing-workflow' => 'Luồng làm việc này chưa tồn tại.',
	'flow-error-not-a-post' => 'Không thể lưu tên chủ đề thành nội dung của bài đăng.',
	'flow-error-missing-header-content' => 'Đầu đề không có nội dung. Đầu đề phải có nội dung để lưu.',
	'flow-error-missing-prev-revision-identifier' => 'Thiếu định danh phiên bản trước.',
	'flow-error-prev-revision-mismatch' => 'Một người dùng khác vừa sửa đổi bài đăng này cách đây vài giây. Bạn có chắc chắn muốn ghi đè thay đổi đó?',
	'flow-error-prev-revision-does-not-exist' => 'Không tìm thấy phiên bản trước.',
	'flow-error-default' => 'Đã xuất hiện lỗi.',
	'flow-error-invalid-input' => 'Đã cung cấp một giá trị không hợp lệ khi tải nội dung luồng.',
	'flow-error-invalid-title' => 'Đã cung cấp tên trang không hợp lệ.',
	'flow-error-fail-load-history' => 'Thất bại khi tải nội dung lịch sử.',
	'flow-error-missing-revision' => 'Không tìm thấy phiên bản để tải nội dung luồng.',
	'flow-error-fail-commit' => 'Thất bại khi lưu nội dung luồng.',
	'flow-error-insufficient-permission' => 'Không đủ quyền để truy cập vào nội dung.',
	'flow-error-revision-comparison' => 'Chỉ có thể so sánh hai phiên bản của cùng bài đăng.',
	'flow-error-missing-topic-title' => 'Không tìm thấy tên chủ đề cho luồng làm việc hiện tại.',
	'flow-error-fail-load-data' => 'Thất bại khi tải dữ liệu được yêu cầu.',
	'flow-error-invalid-workflow' => 'Không tìm thấy luồng làm việc.',
	'flow-error-process-data' => 'Đã xuất hiện lỗi khi xử lý dữ liệu trong yêu cầu của bạn.',
	'flow-error-process-wikitext' => 'Đã xuất hiện lỗi khi xử lý chuyển đổi HTML/mã wiki.',
	'flow-error-no-index' => 'Không tìm thấy chỉ mục để tìm kiếm dữ liệu.',
	'flow-edit-header-submit' => 'Lưu đầu đề',
	'flow-edit-header-submit-overwrite' => 'Ghi đè đầu đề',
	'flow-edit-title-submit' => 'Thay đổi tiêu đề',
	'flow-edit-title-submit-overwrite' => 'Ghi đè tiêu đề',
	'flow-edit-post-submit' => 'Gửi thay đổi',
	'flow-edit-post-submit-overwrite' => 'Ghi đè thay đổi',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2}}đã sửa đổi một [$3 bình luận] về $4.',
	'flow-rev-message-reply' => '$1 {{GENDER:$2}}đã [$3 bình luận] về $4.', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>$1 bình luận</strong> được thêm vào.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2}}đã tạo chủ đề [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2}}đã đổi tiêu đề của chủ đề từ $5 thành [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2}}đã tạo đầu đề.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2}}đã sửa đổi đầu đề.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2}}đã ẩn một [$4 bình luận] về $6 (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2}}đã xóa một [$4 bình luận] về $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2}}đã đàn áp một [$4 bình luận] về $6 (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2}}đã phục hồi một [$4 bình luận] về $6 (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2}}đã ẩn [$4 chủ đề] $6 (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2}}đã xóa [$4 chủ đề] $6 (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2}}đã đàn áp [$4 chủ đề] $6 (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2}}đã phục hồi [$4 chủ đề] $6 (<em>$5</em>).',
	'flow-board-history' => 'Lịch sử “$1”',
	'flow-topic-history' => 'Lịch sử chủ đề “$1”',
	'flow-post-history' => 'Lịch sử bài đăng “Bình luận của $2”',
	'flow-history-last4' => '4 giờ trước đây',
	'flow-history-day' => 'Hôm nay',
	'flow-history-week' => 'Tuần trước',
	'flow-history-pages-topic' => 'Xuất hiện trên [$1 bảng tin nhắn “$2”]',
	'flow-history-pages-post' => 'Xuất hiện trên [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 đã bắt đầu chủ đề này|$3, $4, $5, và {{PLURAL:$2|một người|những người}} khác|0=Chưa có ai tham gia|2=$3 và $4|3=$3, $4, và $5}}',
	'flow-topic-comments' => '{{PLURAL:$1|$1 bình luận|0={{GENDER:$2}}Hãy là người đầu tiên bình luận!}}',
	'flow-comment-restored' => 'Bình luận đã được phục hồi',
	'flow-comment-deleted' => 'Bình luận đã bị xóa',
	'flow-comment-hidden' => 'Bình luận đã bị ẩn',
	'flow-comment-moderated' => 'Bài đăng kiểm duyệt',
	'flow-paging-rev' => 'Thêm chủ đề gần đây',
	'flow-paging-fwd' => 'Chủ đề cũ hơn',
	'flow-last-modified' => 'Thay đổi lần cuối cùng vào khoảng $1',
	'flow-notification-reply' => '$1 đã trả lời <span class="plainlinks">[$5 bài đăng của bạn]</span> về “$2” tại “$4”.',
	'flow-notification-reply-bundle' => '$1 và $5 {{PLURAL:$6}}người khác đã {{GENDER:$1}}trả lời <span class="plainlinks">[$4 bài đăng]</span> của bạn về “$2” tại “$3”.',
	'flow-notification-edit' => '$1 đã sửa đổi một <span class="plainlinks">[$5 bài đăng]</span> về “$2” tại [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 và $5 {{PLURAL:$6}}người khác đã {{GENDER:$1}}sửa đổi một <span class="plainlinks">[$4 bài đăng]</span> về “$2” tại “$3”.',
	'flow-notification-newtopic' => '$1 đã tạo ra <span class="plainlinks">[$5 chủ đề mới]</span> tại [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 đã thay đổi tiêu đề của <span class="plainlinks">[$2 $3]</span> thành “$4” tại [[$5|$6]].',
	'flow-notification-mention' => '$1 đã nói đến bạn trong <span class="plainlinks">[$2 bài đăng]</span> của họ về “$3” tại “$4”.',
	'flow-notification-link-text-view-post' => 'Xem bài đăng',
	'flow-notification-link-text-view-board' => 'Xem bảng tin',
	'flow-notification-link-text-view-topic' => 'Xem chủ đề',
	'flow-notification-reply-email-subject' => '$1 đã trả lời bài đăng của bạn',
	'flow-notification-reply-email-batch-body' => '$1 đã trả lời bài đăng của bạn về “$2” tại “$3”',
	'flow-notification-reply-email-batch-bundle-body' => '$1 và $4 {{PLURAL:$5}}người khác đã trả lời bài đăng của bạn về “$2” tại “$3”',
	'flow-notification-mention-email-subject' => '$1 đã nói đến bạn tại “$2”',
	'flow-notification-mention-email-batch-body' => '$1 đã nói đến bạn trong bài đăng của họ về “$2” tại “$3”.',
	'flow-notification-edit-email-subject' => '$1 đã sửa đổi một bài đăng',
	'flow-notification-edit-email-batch-body' => '$1 đã sửa đổi một bài đăng về “$2” tại “$3”',
	'flow-notification-edit-email-batch-bundle-body' => '$1 và $4 {{PLURAL:$5}}người khác đã sửa đổi một bài đăng về “$2” tại “$3”',
	'flow-notification-rename-email-subject' => '$1 đã đổi tên chủ đề của bạn',
	'flow-notification-rename-email-batch-body' => '$1 đã đổi tên chủ đề của bạn từ “$2” thành “$3” tại “$4”',
	'flow-notification-newtopic-email-subject' => '$1 đã bắt đầu một chủ đề mới tại “$2”',
	'flow-notification-newtopic-email-batch-body' => '$1 đã bắt đầu một chủ đề mới với tiêu đề “$2” tại $3',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Thông báo cho tôi khi các hành động có liên quan đến tôi xảy ra trên Flow.',
	'flow-link-post' => 'bài đăng',
	'flow-link-topic' => 'chủ đề',
	'flow-link-history' => 'lịch sử',
	'flow-moderation-reason-placeholder' => 'Nhập lý do của bạn vào đây',
	'flow-moderation-title-suppress-post' => 'Đàn áp bài đăng?',
	'flow-moderation-title-delete-post' => 'Xóa bài đăng?',
	'flow-moderation-title-hide-post' => 'Ẩn bài đăng?',
	'flow-moderation-title-restore-post' => 'Phục hồi bài đăng?',
	'flow-moderation-intro-suppress-post' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn đàn áp bài đăng này.',
	'flow-moderation-intro-delete-post' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn xóa bài đăng này.',
	'flow-moderation-intro-hide-post' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn ẩn bài đăng này.',
	'flow-moderation-intro-restore-post' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn phục hồi bài đăng này.',
	'flow-moderation-confirm-suppress-post' => 'Đàn áp',
	'flow-moderation-confirm-delete-post' => 'Xóa',
	'flow-moderation-confirm-hide-post' => 'Ẩn',
	'flow-moderation-confirm-restore-post' => 'Phục hồi',
	'flow-moderation-confirmation-suppress-post' => 'Bài đăng đã được đàn áp thành công. Xin hãy {{GENDER:$2}}nghĩ đến việc gửi phản hồi cho $1 về bài đăng này.',
	'flow-moderation-confirmation-delete-post' => 'Bài đăng đã được xóa thành công. Xin hãy {{GENDER:$2}}nghĩ đến việc gửi phản hồi cho $1 về bài đăng này.',
	'flow-moderation-confirmation-hide-post' => 'Bài đăng đã được ẩn thành công. Xin hãy {{GENDER:$2}}nghĩ đến việc gửi phản hồi cho $1 về bài đăng này.',
	'flow-moderation-confirmation-restore-post' => 'Bạn đã phục hồi bài đăng ở trên thành công.',
	'flow-moderation-title-suppress-topic' => 'Đàn áp chủ đề?',
	'flow-moderation-title-delete-topic' => 'Xóa chủ đề?',
	'flow-moderation-title-hide-topic' => 'Ẩn chủ đề?',
	'flow-moderation-title-restore-topic' => 'Phục hồi chủ đề?',
	'flow-moderation-intro-suppress-topic' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn muốn đàn áp chủ đề này.',
	'flow-moderation-intro-delete-topic' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn muốn xóa chủ đề này.',
	'flow-moderation-intro-hide-topic' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn muốn ẩn chủ đề này.',
	'flow-moderation-intro-restore-topic' => 'Xin vui lòng {{GENDER:$3}}giải thích tại sao bạn muốn phục hồi chủ đề này.',
	'flow-moderation-confirm-suppress-topic' => 'Đàn áp',
	'flow-moderation-confirm-delete-topic' => 'Xóa',
	'flow-moderation-confirm-hide-topic' => 'Ẩn',
	'flow-moderation-confirm-restore-topic' => 'Phục hồi',
	'flow-moderation-confirmation-suppress-topic' => 'Chủ đề đã được đàn áp thành công. Xin hãy {{GENDER:$2}}nghĩ đến việc gửi phản hồi cho $1 về chủ đề này.',
	'flow-moderation-confirmation-delete-topic' => 'Chủ đề đã được xóa thành công. Xin hãy {{GENDER:$2}}nghĩ đến việc gửi phản hồi cho $1 về chủ đề này.',
	'flow-moderation-confirmation-hide-topic' => 'Chủ đề đã được ẩn thành công. Xin hãy {{GENDER:$2}}nghĩ đến việc gửi phản hồi cho $1 về chủ đề này.',
	'flow-moderation-confirmation-restore-topic' => 'Bạn đã phục hồi chủ đề này thành công.',
	'flow-topic-permalink-warning' => 'Chủ đề này được bắt đầu tại [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Chủ đề này được bắt đầu tại [$2 bảng tin nhắn của $1]',
	'flow-revision-permalink-warning-post' => 'Đây là liên kết thường trực đến một phiên bản riêng của bài đăng này.
Phiên bản này được lưu vào $1.
Bạn có thể xem [$5 khác biệt với bản trước], hoặc xem các phiên bản khác tại [$4 trang lịch sử bài đăng].',
	'flow-revision-permalink-warning-post-first' => 'Đây là liên kết thường trực đến phiên bản đầu tiên của bài đăng này.
Bạn có thể xem các phiên bản sau tại [$4 trang lịch sử bài đăng].',
	'flow-compare-revisions-revision-header' => 'Phiên bản của $2 vào $1',
	'flow-compare-revisions-header-post' => 'Trang này có các khác biệt giữa hai phiên bản của một bài đăng của $3 trong chủ đề “[$5 $2]” tại [$4 $1].
Bạn có thể xem các phiên bản khác của bài đăng này tại [$6 trang lịch sử] của nó.',
	'flow-topic-collapsed-one-line' => 'Xem danh sách nhỏ',
	'flow-topic-collapsed-full' => 'Xem thu gọn',
	'flow-topic-complete' => 'Xem đầy đủ',
	'flow-terms-of-use-new-topic' => 'Với việc bấm “{{int:flow-newtopic-save}}”, bạn chấp nhận các điều khoản sử dụng của wiki này.',
	'flow-terms-of-use-reply' => 'Với việc bấm “{{int:flow-reply-submit}}”, bạn chấp nhận các điều khoản sử dụng của wiki này.',
	'flow-terms-of-use-edit' => 'Với việc lưu các thay đổi của bạn, bạn chấp nhận các điều khoản sử dụng của wiki này.',
);

/** Volapük (Volapük)
 * @author Malafaya
 */
$messages['vo'] = array(
	'flow-user-anonymous' => 'Nennemik',
	'flow-moderation-reason' => 'Kod:',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|האט אויסגעמעקט}} א [[$4 פאסט]] אויף [[$3]]',
	'flow-user-moderated' => 'מאדערירטער באניצער',
	'flow-edit-header-link' => 'רעדאקטירט קעפל',
	'flow-header-empty' => 'דער דאזיקער שמועס־בלאט האט נישט קיין קעפל.',
	'flow-post-actions' => 'אַקציעס',
	'flow-topic-actions' => 'אַקציעס',
	'flow-cancel' => 'אַנולירן',
	'flow-newtopic-title-placeholder' => 'נײַע טעמע',
	'flow-newtopic-content-placeholder' => 'צולייגן פרטים אז איר ווילט',
	'flow-newtopic-header' => 'צולייגן א נײַע טעמע',
	'flow-newtopic-save' => 'צושטעלן טעמע',
	'flow-newtopic-start-placeholder' => 'אנהייבן א נײַע טעמע',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|קאמענטירן}} אויף "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|ענטפערן}} צו $1',
	'flow-reply-submit' => '{{GENDER:$1|ענטפערן}}',
	'flow-reply-link' => '{{GENDER:$1|ענטפערן}}',
	'flow-thank-link' => '{{GENDER:$1|דאַנקען}}',
	'flow-post-action-view' => 'פערמאנענטער לינק',
	'flow-topic-action-view' => 'פערמאנענטער לינק',
	'flow-topic-action-watchlist' => 'אויפֿפאַסונג ליסטע',
	'flow-topic-action-edit-title' => 'רעדאקטירן טיטל',
	'flow-topic-action-history' => 'היסטאריע',
	'flow-error-delete-failure' => 'אויסמעקן דעם אביעקט אדורכגעפאלן.',
	'flow-error-hide-failure' => 'באהאלטן דעם אביעקט אדורכגעפאלן.',
	'flow-error-restore-failure' => 'צוריקשטעלן דעם אביעקט אדורכגעפאלן.',
	'flow-edit-header-submit' => 'אויפהיטן קעפל.',
	'flow-edit-title-submit' => 'ענדערן טיטל',
	'flow-edit-post-submit' => 'איינגעבן ענדערונגען',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|האט געשאפן}} די טעמע [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|האט געענדערט}} דעם טעמע טיטל צו [$3 $4] פון $5.',
	'flow-rev-message-create-header' => '$1  {{GENDER:$2|האט באשאפן}} דאס טאוול קעפל.', # Fuzzy
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|האט רעדאקטירט}} דאס טאוול קעפל.', # Fuzzy
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|האט באהאלטן}} א [$4 הערה] (<em>$5</em>).', # Fuzzy
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|האט באהאלטן}} א [$4 הערה] (<em>$5</em>).', # Fuzzy
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|האט צוריקגעשטעלט}} א [$4 הערה] (<em>$5</em>).', # Fuzzy
	'flow-topic-history' => '"$1" טעמע היסטאריע',
	'flow-comment-restored' => 'צוריקגעשטעלט הערה',
	'flow-comment-deleted' => 'אויסגעמעקט הערה',
	'flow-comment-hidden' => 'באהאלטענע הערה',
	'flow-comment-moderated' => 'מאדערירטע הערה',
	'flow-paging-fwd' => 'עלטערע טעמעס',
	'echo-category-title-flow-discussion' => 'פֿלוסן',
	'flow-link-topic' => 'טעמע',
	'flow-link-history' => 'היסטאריע',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Dreamism
 * @author Hzy980512
 * @author Linxue9786
 * @author Liuxinyu970226
 * @author Mys 721tx
 * @author Qiyue2001
 * @author Stieizc
 * @author TianyinLee
 * @author Yfdyh000
 */
$messages['zh-hans'] = array(
	'flow-desc' => '工作流管理系统',
	'flow-talk-taken-over' => '此讨论页已被[https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow board]接管。',
	'log-name-flow' => '流活动日志',
	'logentry-delete-flow-delete-post' => '$1在[[$3]]{{GENDER:$2|删除}}了一个[$4 帖子]',
	'logentry-delete-flow-restore-post' => '$1在[[$3]]{{GENDER:$2|恢复}}了一个[$4 帖子]',
	'logentry-suppress-flow-suppress-post' => '$1在[[$3]]{{GENDER:$2|抑制}}了一个[$4 帖子]',
	'logentry-suppress-flow-restore-post' => '$1在[[$3]]{{GENDER:$2|删除}}了一个[$4 帖子]',
	'logentry-delete-flow-delete-topic' => '$1在[[$3]]{{GENDER:$2|删除}}了一个[$4 主题]',
	'logentry-delete-flow-restore-topic' => '$1在[[$3]]{{GENDER:$2|恢复}}了一个[$4 主题]',
	'logentry-suppress-flow-suppress-topic' => '$1在[[$3]]{{GENDER:$2|抑制}}了一个[$4 主题]',
	'logentry-suppress-flow-restore-topic' => '$1在[[$3]]{{GENDER:$2|删除}}了一个[$4 主题]',
	'flow-user-moderated' => '版主用户',
	'flow-edit-header-link' => '编辑页顶',
	'flow-header-empty' => '此讨论页目前没有头部。',
	'flow-post-moderated-toggle-hide-show' => '显示由$2{{GENDER:$1|隐藏}}的留言',
	'flow-post-moderated-toggle-delete-show' => '显示由$2{{GENDER:$1|删除}}的留言',
	'flow-post-moderated-toggle-suppress-show' => '显示由$2{{GENDER:$1|抑制}}的留言',
	'flow-post-moderated-toggle-hide-hide' => '隐藏由$2{{GENDER:$1|隐藏}}的留言',
	'flow-post-moderated-toggle-delete-hide' => '隐藏由$2{{GENDER:$1|删除}}的留言',
	'flow-post-moderated-toggle-suppress-hide' => '隐藏由$2{{GENDER:$1|抑制}}的留言',
	'flow-hide-post-content' => '此评论已由$2{{GENDER:$1|隐藏}}',
	'flow-hide-title-content' => '此主题已被$2{{GENDER:$1|隐藏}}',
	'flow-hide-header-content' => '由$2{{GENDER:$1|隐藏}}',
	'flow-delete-post-content' => '此评论已由$2{{GENDER:$1|删除}}',
	'flow-delete-title-content' => '此主题已被$2{{GENDER:$1|删除}}',
	'flow-delete-header-content' => '由$2{{GENDER:$1|删除}}',
	'flow-suppress-post-content' => '此评论已被$2{{GENDER:$1|抑制}}',
	'flow-suppress-title-content' => '此主题已被$2{{GENDER:$1|抑制}}',
	'flow-suppress-header-content' => '由$2{{GENDER:$1|抑制}}',
	'flow-suppress-usertext' => '<em>用户名已压制</em>',
	'flow-post-actions' => '操作',
	'flow-topic-actions' => '操作',
	'flow-cancel' => '取消',
	'flow-preview' => '预览',
	'flow-show-change' => '显示差异',
	'flow-last-modified-by' => '最后内容{{GENDER:$1|修订}}由$1完成',
	'flow-stub-post-content' => "''由于一个技术错误，此帖子无法被恢复。''",
	'flow-newtopic-title-placeholder' => '新主题',
	'flow-newtopic-content-placeholder' => '添加细节如果您愿意',
	'flow-newtopic-header' => '添加新主题',
	'flow-newtopic-save' => '添加主题',
	'flow-newtopic-start-placeholder' => '开启一个新话题',
	'flow-reply-topic-placeholder' => '在“$2”发表的{{GENDER:$1|评论}}',
	'flow-reply-placeholder' => '{{GENDER:$1|回复}}$1',
	'flow-reply-submit' => '{{GENDER:$1|帖子回复}}',
	'flow-reply-link' => '{{GENDER:$1|回复}}',
	'flow-thank-link' => '{{GENDER:$1|感谢}}',
	'flow-post-edited' => '评论由$1 $2{{GENDER:$1|编辑}}',
	'flow-post-action-view' => '永久链接',
	'flow-post-action-post-history' => '历史',
	'flow-post-action-suppress-post' => '压制',
	'flow-post-action-delete-post' => '删除',
	'flow-post-action-hide-post' => '隐藏',
	'flow-post-action-edit-post' => '编辑',
	'flow-post-action-restore-post' => '恢复帖子',
	'flow-topic-action-view' => '永久链接',
	'flow-topic-action-watchlist' => '监视列表',
	'flow-topic-action-edit-title' => '编辑标题',
	'flow-topic-action-history' => '历史',
	'flow-topic-action-hide-topic' => '隐藏主题',
	'flow-topic-action-delete-topic' => '删除主题',
	'flow-topic-action-suppress-topic' => '抑制主题',
	'flow-topic-action-restore-topic' => '恢复主题',
	'flow-error-http' => '与服务器联系时出错。',
	'flow-error-other' => '出现意外的错误。',
	'flow-error-external' => '出现一个错误。<br />收到的错误信息：$1',
	'flow-error-edit-restricted' => '您无权编辑此帖子。',
	'flow-error-external-multi' => '遇到错误。<br />$1',
	'flow-error-missing-content' => '帖子无内容。只能保存有内容的帖子。',
	'flow-error-missing-title' => '这个主题没有标题。必须有标题才能保存主题。',
	'flow-error-parsoid-failure' => '由于Parsoid故障无法解析内容。',
	'flow-error-delete-failure' => '删除本项失败。',
	'flow-error-hide-failure' => '隐藏此项失败。',
	'flow-error-restore-failure' => '恢复此项失败。',
	'flow-error-invalid-moderation-state' => 'moderationState 提供了无效的值',
	'flow-error-title-too-long' => '主题标题需小于$1字节。',
	'flow-error-no-existing-workflow' => '此工作流尚不存在。',
	'flow-error-not-a-post' => '主题标题不能保存为一个帖子。',
	'flow-error-missing-header-content' => '标头没有内容。必须有内容才能保存标题。',
	'flow-error-missing-prev-revision-identifier' => '上一修订的标识符缺失。',
	'flow-error-prev-revision-mismatch' => '另一位用户已于几秒钟前编辑了此帖子。您确信继续重写最近更新？',
	'flow-error-prev-revision-does-not-exist' => '无法找到以前的版本。',
	'flow-error-default' => '出现了一个错误',
	'flow-error-invalid-input' => '正在加载的flow内容被提供了无效的值。',
	'flow-error-invalid-title' => '指定了无效的页面标题。',
	'flow-error-fail-load-history' => '未能加载历史内容。',
	'flow-error-fail-commit' => '未能保存流内容。',
	'flow-error-insufficient-permission' => '没有足够的权限访问内容。',
	'flow-error-fail-load-data' => '未能加载所请求的数据。',
	'flow-error-invalid-workflow' => '找不到请求的工作流。',
	'flow-error-process-data' => '处理您的请求中的数据时出错。',
	'flow-error-process-wikitext' => '处理 HTML/维基文本 转换时出错。',
	'flow-error-no-index' => '未能找到索引来执行数据搜索。',
	'flow-edit-header-submit' => '保存页顶',
	'flow-edit-header-submit-overwrite' => '覆写页顶',
	'flow-edit-title-submit' => '更改标题',
	'flow-edit-title-submit-overwrite' => '覆写标题',
	'flow-edit-post-submit' => '提交更改',
	'flow-edit-post-submit-overwrite' => '覆写更改',
	'flow-rev-message-edit-post' => '$1在$4{{GENDER:$2|编辑了}}一个[$3 评论]。',
	'flow-rev-message-reply' => '$1在$4{{GENDER:$2|添加了}}一个[$3 评论]。', # Fuzzy
	'flow-rev-message-reply-bundle' => '添加了<strong>$1 条评论</strong>。',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|创建了}}主题 [$3  $4]。',
	'flow-rev-message-edit-title' => '$1将主题名字从$5{{GENDER:$2|改为}}[$3 $4]。',
	'flow-rev-message-create-header' => '$1{{GENDER:$2|创建了}}页顶。',
	'flow-rev-message-edit-header' => '$1{{GENDER:$2|编辑了}}页顶。',
	'flow-rev-message-hid-post' => '$1在$6{{GENDER:$2|隐藏}}了一个[$4 评论]（<em>$5</em>）。',
	'flow-rev-message-deleted-post' => '$1在$6{{GENDER:$2|删除}}了[$4 评论]（<em>$5</em>）。',
	'flow-rev-message-suppressed-post' => '$1在$6{{GENDER:$2|抑制}}了一个[$4 评论]（<em>$5</em>）。',
	'flow-rev-message-restored-post' => '$1在$6{{GENDER:$2|恢复}}了一个[$4 评论]（<em>$5</em>）。',
	'flow-rev-message-hid-topic' => '$1在$6{{GENDER:$2|隐藏}}了一个[$4 主题]（<em>$5</em>）。',
	'flow-rev-message-deleted-topic' => '$1在$6{{GENDER:$2|删除}}了一个[$4 主题]（<em>$5</em>）。',
	'flow-rev-message-suppressed-topic' => '$1{{GENDER:$2|抑制}}了一个[$4 主题]（<em>$5</em>）。', # Fuzzy
	'flow-rev-message-restored-topic' => '$1{{GENDER:$2|恢复}}了一个[$4 主题]（<em>$5</em>）。', # Fuzzy
	'flow-board-history' => '“$1”的历史',
	'flow-topic-history' => '“$1”主题的历史',
	'flow-post-history' => '“评论由{{GENDER:$2|$2}}做出”帖子历史',
	'flow-history-last4' => '过去4个小时',
	'flow-history-day' => '今天',
	'flow-history-week' => '上周',
	'flow-history-pages-topic' => '现身于[$1 “$2”董事会]',
	'flow-history-pages-post' => '出现在[$1 $2]',
	'flow-topic-comments' => '{{PLURAL:$1|$1个评论|0={{GENDER:$2|第一个}}发表评论！}}',
	'flow-comment-restored' => '恢复的评论',
	'flow-comment-deleted' => '已删除的评论',
	'flow-comment-hidden' => '隐藏的评论',
	'flow-comment-moderated' => '主持评论',
	'flow-paging-rev' => '更多最新主题',
	'flow-paging-fwd' => '更早的话题',
	'flow-last-modified' => '有关$1的最后修改时间',
	'flow-notification-reply-bundle' => '$1和$5个{{PLURAL:$6|其他}}用户答复了您在“$3”内“$2”的<span class="plainlinks">[$4 评论]</span>。',
	'flow-notification-edit' => '$1编辑了一个在[[$3|$4]]内“$2”的<span class="plainlinks">[$5 评论]</span>。',
	'flow-notification-newtopic' => '$1在[[$2|$3]]{{GENDER:$1|创建了}}一个<span class="plainlinks">[$5 新话题]</span>：$4。',
	'flow-notification-rename' => '<span class="plainlinks">[$2 $3]</span>的标题已被$1在[[$5|$6]]{{GENDER:$1|更改}}为“$4”。',
	'flow-notification-mention' => '$1于“$4”在{{GENDER:$1|他|她|他们}}的“$3”的<span class="plainlinks">[$2 帖子]</span>中提到了您。',
	'flow-notification-link-text-view-post' => '浏览帖子',
	'flow-notification-link-text-view-board' => '查看讨论版',
	'flow-notification-link-text-view-topic' => '查看主题',
	'flow-notification-reply-email-subject' => '$1回复了您的帖子',
	'flow-notification-reply-email-batch-body' => '$1回复了您在“$3”的帖子“$2”',
	'flow-notification-mention-email-subject' => '$1在“$2”提及了您',
	'flow-notification-edit-email-subject' => '$1编辑了一个帖子',
	'flow-notification-edit-email-batch-body' => '$1编辑了“$3”上主题“$2”中的一个帖子',
	'flow-notification-rename-email-subject' => '$1重命名了您的主题',
	'flow-notification-rename-email-batch-body' => '$1将您在“$4”的主题“$2”重命名为“$3”',
	'flow-notification-newtopic-email-subject' => '$1在“$2”创建了新主题',
	'echo-category-title-flow-discussion' => '流量',
	'echo-pref-tooltip-flow-discussion' => '在讨论版发生有关我的动作时通知我。',
	'flow-link-post' => '帖子',
	'flow-link-topic' => '主题',
	'flow-link-history' => '历史',
	'flow-moderation-reason-placeholder' => '在此输入您的原因',
	'flow-moderation-title-suppress-post' => '压制帖子？',
	'flow-moderation-title-delete-post' => '删除帖子？',
	'flow-moderation-title-hide-post' => '隐藏帖子？',
	'flow-moderation-title-restore-post' => '恢复帖子？',
	'flow-moderation-confirm-suppress-post' => '压制',
	'flow-moderation-confirm-delete-post' => '删除',
	'flow-moderation-confirm-hide-post' => '隐藏',
	'flow-moderation-confirm-restore-post' => '恢复',
	'flow-moderation-confirmation-suppress-post' => '该帖子已成功抑制。{{GENDER:$2|考虑}}此帖子提供$1条反馈。',
	'flow-moderation-confirmation-restore-post' => '您已成功还原上面的帖子。',
	'flow-moderation-title-suppress-topic' => '抑制主题？',
	'flow-moderation-title-delete-topic' => '删除主题?',
	'flow-moderation-title-hide-topic' => '隐藏主题？',
	'flow-moderation-title-restore-topic' => '还原主题？',
	'flow-moderation-intro-suppress-topic' => '请{{GENDER:$3|解释}}为何您要隐藏此主题。',
	'flow-moderation-intro-delete-topic' => '请{{GENDER:$3|说明}}为何您删除此主题。',
	'flow-moderation-intro-hide-topic' => '请{{GENDER:$3|解释}}为何您要隐藏此主题。',
	'flow-moderation-intro-restore-topic' => '请{{GENDER:$3|解释}}为何您要恢复此主题。',
	'flow-moderation-confirm-suppress-topic' => '抑制',
	'flow-moderation-confirm-delete-topic' => '删除',
	'flow-moderation-confirm-hide-topic' => '隐藏',
	'flow-moderation-confirm-restore-topic' => '恢复',
	'flow-moderation-confirmation-restore-topic' => '您已成功还原本主题。',
	'flow-topic-permalink-warning' => '本主题已在[$2 $1]开启',
	'flow-topic-permalink-warning-user-board' => '本主题已在[$2 $1的通告版]开启',
	'flow-revision-permalink-warning-header' => '这是一个指向某个版本的标题的永久链接。
版本号从$1中提取。您可以在这里（$3）看到与上一个版本的不同，或者在历史记录（$2）中查看其他版本。',
	'flow-compare-revisions-revision-header' => '版本由{{GENDER:$2|$2}}从$1生成',
	'flow-topic-collapsed-one-line' => '小型视图',
	'flow-topic-collapsed-full' => '折叠视图',
	'flow-topic-complete' => '完整视图',
	'flow-terms-of-use-new-topic' => '通过点击“{{int:flow-newtopic-save}}”，您同意此wiki的使用条款。',
	'flow-terms-of-use-reply' => '通过点击“{{int:flow-reply-submit}}”，您同意此wiki的使用条款。',
	'flow-terms-of-use-edit' => '通过保存您的更改，您同意此wiki的使用条款。',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Cwlin0416
 * @author EagerLin
 * @author Liuxinyu970226
 */
$messages['zh-hant'] = array(
	'flow-talk-taken-over' => '此討論頁已由[https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow board]接管。',
	'flow-hide-post-content' => '此評論已由$2{{GENDER:$1|隱藏}}',
	'flow-hide-title-content' => '此主題已由$2{{GENDER:$1|隱藏}}',
	'flow-hide-header-content' => '由$2{{GENDER:$1|隱藏}}',
	'flow-delete-post-content' => '此評論已由$2{{GENDER:$1|刪除}}',
	'flow-delete-title-content' => '此主題已由$2{{GENDER:$1|刪除}}',
	'flow-delete-header-content' => '由$2{{GENDER:$1|刪除}}',
	'flow-suppress-post-content' => '此評論已被$2{{GENDER:$1|抑制}}',
	'flow-suppress-title-content' => '此主題已由$2{{GENDER:$1|抑制}}',
	'flow-suppress-header-content' => '由$2{{GENDER:$1|抑制}}',
	'flow-stub-post-content' => "'''由於技術錯誤，這篇文章無法檢索。'''",
	'flow-error-prev-revision-mismatch' => '另一用戶於幾秒鐘前編輯此帖子。您確信覆寫新近變更？',
	'flow-notification-reply' => '$1 {{GENDER:$1|已回覆}}您的 [$5 留言] 於 $2 的 "$4"。', # Fuzzy
	'flow-notification-reply-bundle' => '$1 與另外 $5 {{PLURAL:$6|個人|個人}}已{{GENDER:$1|回覆}}您的 [$4 留言] 於 $2 的 "$3"。', # Fuzzy
	'flow-notification-link-text-view-post' => '檢視留言',
	'flow-notification-link-text-view-board' => '檢視討論版',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|已回覆}}您的留言',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|已回覆}}您的留言於 $2 的 "$3"', # Fuzzy
	'flow-notification-reply-email-batch-bundle-body' => '$1 與另外 $4 {{PLURAL:$5|個人|個人}} {{GENDER:$1|已回覆}} 您的留言於 $2 的 "$3"', # Fuzzy
	'echo-category-title-flow-discussion' => '流量',
	'echo-pref-tooltip-flow-discussion' => '通知我，當有與我相關的動作發生在討論版時', # Fuzzy
	'flow-moderation-confirmation-suppress-post' => '該職位被成功地解除。
考慮 {{GENDER:$1| 給}} $1 對這篇文章的回饋意見。', # Fuzzy
	'flow-moderation-confirmation-delete-post' => '主題已成功刪除。
考慮 {{GENDER:$1| 給}} $1 對此主題的回饋意見。', # Fuzzy
	'flow-moderation-confirmation-hide-post' => '主題已成功刪除。
考慮 {{GENDER:$1| 給}} $1 對此主題的回饋意見。', # Fuzzy
	'flow-moderation-confirmation-restore-post' => '您已成功還原上方的帖子。',
	'flow-moderation-intro-delete-topic' => '請{{GENDER:$3|說明}}為何您要刪除此主題。',
	'flow-moderation-confirmation-suppress-topic' => '主題已成功刪除。
考慮 {{GENDER:$1| 給}} $1 對此主題的回饋意見。', # Fuzzy
	'flow-moderation-confirmation-delete-topic' => '主題已成功刪除。
考慮 {{GENDER:$1| 給}} $1 對此主題的回饋意見。', # Fuzzy
	'flow-moderation-confirmation-hide-topic' => '主題已成功刪除。
考慮 {{GENDER:$1| 給}} $1 對此主題的回饋意見。', # Fuzzy
	'flow-moderation-confirmation-restore-topic' => '您已成功還原本主題。',
	'flow-terms-of-use-new-topic' => '通過點擊「{{int:flow-newtopic-save}}」，您同意此wiki之使用條款。',
	'flow-terms-of-use-reply' => '通過點擊「{{int:flow-reply-submit}}」，您同意此wiki之使用條款。',
	'flow-terms-of-use-edit' => '通過保存您的更改，您同意此wiki之使用條款。',
);
