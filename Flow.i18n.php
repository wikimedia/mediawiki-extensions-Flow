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

	'flow-post-moderated-toggle-show' => '[Show]',
	'flow-post-moderated-toggle-hide' => '[Hide]',
	'flow-hide-content' => '{{GENDER:$1|Hidden}} by $1',
	'flow-hide-usertext' => '$1',
	'flow-delete-content' => '{{GENDER:$1|Deleted}} by $1',
	'flow-delete-usertext' => '$1',
	'flow-suppress-content' => '{{GENDER:$1|Suppressed}} by $1',
	'flow-suppress-usertext' => "<em>Username suppressed</em>",
	'flow-post-actions' => 'Actions',
	'flow-topic-actions' => 'Actions',
	'flow-cancel' => 'Cancel',
	'flow-preview' => 'Preview',

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
	'flow-talk-link' => 'Talk to {{GENDER:$1|$1}}',

	'flow-edit-post-submit' => 'Submit changes',

	'flow-post-edited' => 'Post {{GENDER:$1|edited}} by $1 $2',
	'flow-post-action-view' => 'Permalink',
	'flow-post-action-post-history' => 'Post history',
	'flow-post-action-suppress-post' => 'Suppress',
	'flow-post-action-delete-post' => 'Delete',
	'flow-post-action-hide-post' => 'Hide',
	'flow-post-action-edit-post' => 'Edit post',
	'flow-post-action-edit' => 'Edit',
	'flow-post-action-restore-post' => 'Restore post',

	'flow-topic-action-view' => 'Permalink',
	'flow-topic-action-watchlist' => 'Watchlist',
	'flow-topic-action-edit-title' => 'Edit title',
	'flow-topic-action-history' => 'Topic history',
	'flow-topic-action-hide-topic' => 'Hide topic',
	'flow-topic-action-delete-topic' => 'Delete topic',
	'flow-topic-action-suppress-topic' => 'Suppress topic',
	'flow-topic-action-restore-topic' => 'Restore topic',

	'flow-error-http' => 'An error occurred while contacting the server.', // Needs real copy
	'flow-error-other' => 'An unexpected error occurred.',
	'flow-error-external' => 'An error occurred.<br /><small>The error message received was: $1</small>',
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
	'flow-error-invalid-moderation-state' => 'An invalid value was provided for moderationState',
	'flow-error-invalid-moderation-reason' => 'Please provide a reason for the moderation',
	'flow-error-not-allowed' => 'Insufficient permissions to execute this action',
	'flow-error-title-too-long' => 'Topic titles are restricted to $1 {{PLURAL:$1|byte|bytes}}.',
	'flow-error-no-existing-workflow' => 'This workflow does not yet exist.',
	'flow-error-not-a-post' => 'Topic title can not be saved as a post.',
	'flow-error-missing-header-content' => 'Header has no content. Content is required to save a header.',
	'flow-error-missing-prev-revision-identifier' => 'Previous revision identifier is missing.',
	'flow-error-prev-revision-mismatch' => 'The edit could not be saved: a more recent change has already been submitted.',
	'flow-error-prev-revision-does-not-exist' => 'Could not find the previous revision.',

	'flow-edit-header-submit' => 'Save header',

	'flow-edit-title-submit' => 'Change title',

	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|edited}} a [$3 comment].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|added}} a [$3 comment].',
	'flow-rev-message-reply-bundle' => "<strong>$1 {{PLURAL:$1|comment|comments}}</strong> {{PLURAL:$1|was|were}} added.",
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|created}} the topic [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|changed}} the topic title from $5 to [$3 $4].',

	'flow-rev-message-create-header' => "$1 {{GENDER:$2|created}} the board header.",
	'flow-rev-message-edit-header' => "$1 {{GENDER:$2|edited}} the board header.",

	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|hid}} a [$4 comment] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|deleted}} a [$4 comment] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|suppressed}} a [$4 comment] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|restored}} a [$4 comment] (<em>$5</em>).',

	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|hid}} the [$4 topic] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|deleted}} the [$4 topic] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|suppressed}} the [$4 topic] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|restored}} the [$4 topic] (<em>$5</em>).',

	'flow-board-history' => '"$1" history',
	'flow-topic-history' => '"$1" topic history',
	'flow-post-history' => '"Comment by {{GENDER:$2|$2}}" post history',
	'flow-history-last4' => 'Last 4 hours',
	'flow-history-day' => 'Today',
	'flow-history-week' => 'Last week',
	'flow-history-pages-topic' => 'Appears on [$1 "$2" board]',
	'flow-history-pages-post' => 'Appears on [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 started this topic|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} and $2 {{PLURAL:$2|other|others}}|0=No participation yet|2={{GENDER:$3|$3}} and {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} and {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Comment ($1)|Comments ($1)|0={{GENDER:$2|Be the first}} to comment!}}',
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Be the first to comment!|$1 {{PLURAL:$1|comment|comments}} by $2 {{PLURAL:$2|user|users}}}}',

	'flow-comment-restored' => 'Restored comment',
	'flow-comment-deleted' => 'Deleted comment',
	'flow-comment-hidden' => 'Hidden comment',
	'flow-comment-moderated' => 'Moderated comment',

	'flow-paging-rev' => 'More recent topics',
	'flow-paging-fwd' => 'Older topics',
	'flow-last-modified' => 'Last modified about $1',

	// Notification message
	'flow-notification-reply' => '$1 {{GENDER:$1|replied}} to your [$5 post] in $2 on "$4".',
	'flow-notification-reply-bundle' => '$1 and $5 {{PLURAL:$6|other|others}} {{GENDER:$1|replied}} to your [$4 post] in $2 on "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|edited}} a [$5 post] in $2 on [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 and $5 {{PLURAL:$6|other|others}} {{GENDER:$1|edited}} a [$4 post] in $2 on "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|created}} a [$5 new topic] on [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|changed}} the title of [$2 $3] to "$4" on [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|mentioned}} you in {{GENDER:$1|his|her|their}} [$2 post] in "$3" on "$4".',

	// Notification primary links and secondary links
	'flow-notification-link-text-view-post' => 'View post',
	'flow-notification-link-text-view-board' => 'View board',
	'flow-notification-link-text-view-topic' => 'View topic',

	// Notification Email messages
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|replied}} to your post',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|replied}} to your post in $2 on "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 and $4 {{PLURAL:$5|other|others}} {{GENDER:$1|replied}} to your post in $2 on "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|mentioned}} you on $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|mentioned}} you in {{GENDER:$1|his|her|their}} post in "$2" on "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|edited}} your post',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|edited}} your post in $2 on "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 and $4 {{PLURAL:$5|other|others}} {{GENDER:$1|edited}} a post in $2 on "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|renamed}} your topic',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|renamed}} your topic "$2" to "$3" on "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|created}} a new topic on $2',
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
	'flow-moderation-title-restore-post'=> 'Restore post?',
	'flow-moderation-intro-suppress-post' => "Please explain why you're suppressing this post.",
	'flow-moderation-intro-delete-post' => "Please explain why you're deleting this post.",
	'flow-moderation-intro-hide-post' => "Please explain why you're hiding this post.",
	'flow-moderation-intro-restore-post'=> "Please explain why you're restoring this post.",
	'flow-moderation-confirm-suppress-post' => 'Suppress',
	'flow-moderation-confirm-delete-post' => 'Delete',
	'flow-moderation-confirm-hide-post' => 'Hide',
	'flow-moderation-confirm-restore-post' => 'Restore',
	'flow-moderation-confirmation-suppress-post' => 'Consider {{GENDER:$1|giving}} $1 feedback on this post.',
	'flow-moderation-confirmation-delete-post' => 'Consider {{GENDER:$1|giving}} $1 feedback on this post.',
	'flow-moderation-confirmation-hide-post' => 'Consider {{GENDER:$1|giving}} $1 feedback on this post.',
	'flow-moderation-confirmation-restore-post' => 'You have successfully restored this post.',
	'flow-moderation-title-suppress-topic' => 'Suppress topic?',
	'flow-moderation-title-delete-topic' => 'Delete topic?',
	'flow-moderation-title-hide-topic' => 'Hide topic?',
	'flow-moderation-title-restore-topic'=> 'Restore topic?',
	'flow-moderation-intro-suppress-topic' => "Please explain why you're suppressing this topic.",
	'flow-moderation-intro-delete-topic' => "Please explain why you're deleting this topic.",
	'flow-moderation-intro-hide-topic' => "Please explain why you're hiding this topic.",
	'flow-moderation-intro-restore-topic'=> "Please explain why you're restoring this topic.",
	'flow-moderation-confirm-suppress-topic' => 'Suppress',
	'flow-moderation-confirm-delete-topic' => 'Delete',
	'flow-moderation-confirm-hide-topic' => 'Hide',
	'flow-moderation-confirm-restore-topic' => 'Restore',
	'flow-moderation-confirmation-suppress-topic' => 'Consider {{GENDER:$1|giving}} $1 feedback on this topic.',
	'flow-moderation-confirmation-delete-topic' => 'Consider {{GENDER:$1|giving}} $1 feedback on this topic.',
	'flow-moderation-confirmation-hide-topic' => 'Consider {{GENDER:$1|giving}} $1 feedback on this topic.',
	'flow-moderation-confirmation-restore-topic' => 'You have successfully restored this topic.',

	// Permalink related stuff
	'flow-topic-permalink-warning' => 'This topic was started on [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'This topic was started on [$2 {{GENDER:$1|$1}}\'s board]',
	'flow-revision-permalink-warning-post' => 'This is a permanent link to a single version of this post.
This version is from $1.
You can see the [$5 differences from the previous version], or view other versions on the [$4 post history page].',
	'flow-revision-permalink-warning-post-first' => 'This is a permanent link to the first version of this post.
You can view later versions on the [$4 post history page].',

	'flow-compare-revisions-revision-header' => 'Version by {{GENDER:$2|$2}} from $1',
	'flow-compare-revisions-header-post' => 'This page shows the changes between two versions of a post by $3 in the topic "[$5 $2]" on [$4 $1].
You can see other versions of this post at its [$6 history page].',

	// Topic collapse states
	'flow-topic-collapsed-one-line' => 'Small view',
	'flow-topic-collapsed-full' => 'Collapsed view',
	'flow-topic-complete' => 'Full view',
);

/** Message documentation (Message documentation)
 * @author Amire80
 * @author Beta16
 * @author Raymond
 * @author Shirayuki
 * @author Siebrand
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
	'flow-user-moderated' => 'Name to display instead of a moderated user name',
	'flow-edit-header-link' => 'Used as text for the button that either allows editing the header in place or brings the user to a page for editing the header.',
	'flow-header-empty' => 'Used as a placeholder text for headers which have no content.',
	'flow-post-moderated-toggle-show' => 'Text for link used to display a moderated post',
	'flow-post-moderated-toggle-hide' => 'Text for link used to hide a moderated post',
	'flow-hide-content' => 'Message to display instead of content when the content has been hidden.

Parameters:
* $1 - username that hid the post, can be used for GENDER
{{Related|Flow-content}}',
	'flow-hide-usertext' => 'Used as username if the post was hidden.

Parameters:
* $1 - Username of the post creator. Can be used for GENDER',
	'flow-delete-content' => 'Message to display instead of content when the content has been deleted.

Parameters:
* $1 - username that deleted the post, can be used for GENDER
{{Related|Flow-content}}',
	'flow-delete-usertext' => 'Used as username if the post was deleted.

Parameters:
* $1 - Username of the post creator. Can be used for GENDER',
	'flow-suppress-content' => 'Message to display instead of content when the content has been suppressed.

Parameters:
* $1 - username that suppressed the post, can be used for GENDER
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
	'flow-newtopic-title-placeholder' => 'Used as placeholder for the "Subject/Title for topic" textarea.
{{Identical|New topic}}',
	'flow-newtopic-content-placeholder' => 'Used as placeholder for the "Content" textarea.',
	'flow-newtopic-header' => 'Unused at this time.',
	'flow-newtopic-save' => 'Used as label for the Submit button.',
	'flow-newtopic-start-placeholder' => 'Used as placeholder for the "Topic" textarea.',
	'flow-reply-topic-placeholder' => 'Used as placeholder for the "reply to this topic" textarea. Parameters:
* $1 - username of the logged in user, can be used for GENDER
* $2 - topic title',
	'flow-reply-placeholder' => 'Used as placeholder for the Content textarea. Parameters:
* $1 - username',
	'flow-reply-submit' => 'Used as label for the Submit button. Parameters:
* $1 - username, can be used for GENDER
{{Identical|Reply}}',
	'flow-reply-link' => 'Text for the link that appears near the post and offers the user to reply to it. Clicking the link will display the reply editor. Parameters:
* $1 - username, can be used for GENDER
{{Identical|Reply}}',
	'flow-thank-link' => 'Link text of the button that will (when clicked) thank the editor of the comment Parameters:
* $1 - username, can be used for GENDER',
	'flow-talk-link' => 'Link text of the button that links to the talk page of the user whose comment is deleted. Parameters:
* $1 - username of the user whose comment is deleted, can be used for GENDER',
	'flow-edit-post-submit' => 'Used as label for the Submit button.',
	'flow-post-edited' => 'Text displayed to notify the user a post has been modified. Parameters:
* $1 - username that created the most recent revision of the post
* $2 - humanized timestamp, relative to now, of when the edit occurred; rendered by MWTimestamp::getHumanTimestamp',
	'flow-post-action-view' => 'Used as text for the link which is used to view.
{{Identical|Permalink}}',
	'flow-post-action-post-history' => 'Used as text for the link which is used to view post-history of the topic.',
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
{{Identical|Edit post}}',
	'flow-post-action-edit' => 'Unused at this time.

Translate as label for the link or the Submit button.
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
{{Identical|Topic history}}',
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
	'flow-error-prev-revision-mismatch' => 'Error message when the provided previous revision identifier does not match the last stored revision. Parameters:
* $1: The submitted previous revision id
* $2: The actual latest revision id',
	'flow-error-prev-revision-does-not-exist' => 'Error message when the provided previous revision identifier could not be found.',
	'flow-edit-header-submit' => 'Used as label for the Submit button.',
	'flow-edit-title-submit' => 'Used as label for the Submit button.',
	'flow-rev-message-edit-post' => 'Used as a revision comment when a post has been edited.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who edited the post. Can be used for GENDER
* $3 - the URL of the post
{{Related|Flow-rev-message}}',
	'flow-rev-message-reply' => 'Used as a revision comment when a new reply has been posted.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who replied. Can be used for GENDER
* $3 - the URL of the post
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
{{Related|Flow-rev-message}}',
	'flow-rev-message-deleted-post' => 'Used as revision comment when a post has been deleted.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the comment. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the comment. Can be used for GENDER
* $4 - permalink to the comment
* $5 - Reason, from the moderating user, for moderating this post
{{Related|Flow-rev-message}}',
	'flow-rev-message-suppressed-post' => 'Used as revision comment when a post has been suppressed.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the comment. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the comment. Can be used for GENDER
* $4 - permalink to the comment
* $5 - Reason, from the moderating user, for moderating this post
{{Related|Flow-rev-message}}',
	'flow-rev-message-restored-post' => 'Used as revision comment when a post has been restored (un-hidden).

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who restored the comment. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the comment. Can be used for GENDER
* $4 - permalink to the comment
* $5 - Reason, from the moderating user, for moderating this post
{{Related|Flow-rev-message}}',
	'flow-rev-message-hid-topic' => 'Used as revision comment when a topic has been hidden.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
{{Related|Flow-rev-message}}',
	'flow-rev-message-deleted-topic' => 'Used as revision comment when a topic has been deleted.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
{{Related|Flow-rev-message}}',
	'flow-rev-message-suppressed-topic' => 'Used as revision comment when a topic has been suppressed.

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who moderated the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
{{Related|Flow-rev-message}}',
	'flow-rev-message-restored-topic' => 'Used as revision comment when a topic has been restored (un-hidden).

Parameters:
* $1 - user link and tool links for the user.
* $2 - username of the user who restored the topic. Can be used for GENDER
* $3 - (Optional) username of the user who had posted the topic. Can be used for GENDER
* $4 - permalink to the topic
* $5 - Reason, from the moderating user, for moderating this topic
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
	'flow-topic-meta-minimal' => 'Message to display the amount of users and comments in a topic in the collapsed topic views.

Parameters:
* $1 - the number of comments in this topic, can be used for PLURAL
* $2 - the number of users participating in this topic, can be used for PLURAL
See also:
* {{msg-mw|Flow-topic-comments}}',
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
See also:
* {{msg-mw|Flow-notification-reply-email-batch-body}}',
	'flow-notification-reply-email-batch-body' => 'Email notification body when a user receives a reply, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who replied
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
See also:
* {{msg-mw|Flow-notification-reply-email-subject}}',
	'flow-notification-reply-email-batch-bundle-body' => 'Email notification body when a user receives reply from multiple users, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who replied
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
* $4 - the count of other action performers, could be number or {{msg-mw|Echo-notification-count}}. e.g. 7 others or 99+ others
* $5 - a number used for plural support
See also:
* {{msg-mw|Flow-notification-reply-bundle}}',
	'flow-notification-mention-email-subject' => 'Email notification subject when a user is mentioned in a post.  Parameters:
* $1 - Username of the person who mentions other users
* $2 - Flow title text',
	'flow-notification-mention-email-batch-body' => '{{doc-singularthey}}
Email notification body when a user is mentioned in a post, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who mentions other users
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to',
	'flow-notification-edit-email-subject' => 'Subject line of notification email for post being edited. Parameters:
* $1 - name of the user that edited the post',
	'flow-notification-edit-email-batch-body' => 'Email notification for post being edited. Parameters:
* $1 - name of the user that edited the post
* $2 - name of the topic the edited post belongs to
* $3 - title of the page the topic belongs to',
	'flow-notification-edit-email-batch-bundle-body' => 'Email notification body when a user receives post edits from multiple users, this message is used in both single email and email digest.

Parameters:
* $1 - username of the person who replied
* $2 - title of the topic
* $3 - title for the page that the Flow board is attached to
* $4 - the count of other action performers, could be number or {{msg-mw|Echo-notification-count}}. e.g. 7 others or 99+ others
* $5 - a number used for plural support
See also:
* {{msg-mw|Flow-notification-edit-bundle}}',
	'flow-notification-rename-email-subject' => 'Subject line of notification email for topic being renamed. Parameters:
* $1 - name of the user that renamed the topic',
	'flow-notification-rename-email-batch-body' => 'Email notification for topic being renamed. Parameters:
* $1 - name of the user that renamed the topic
* $2 - the original topic title
* $3 - the new topic title
* $4 - title of the page the topic belongs to',
	'flow-notification-newtopic-email-subject' => 'Subject line of notification email for new topic creation. Parameters:
* $1 - name of the user that created a new topic
* $2 - title',
	'flow-notification-newtopic-email-batch-body' => 'Email notification for new topic creation. Parameters:
* $1 - name of the user that created a new topic
* $2 - the title of the new topic
* $3 - title of the page the topic belongs to',
	'echo-category-title-flow-discussion' => 'This is a short title for notification category.  Parameters:
* $1 - number of mentions, for PLURAL support

{{Related|Echo-category-title}}',
	'echo-pref-tooltip-flow-discussion' => 'This is a short description of the flow-discussion notification category.',
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
* $1 - (Unused) the name of the user whose post is being suppressed. GENDER supported.
* $2 - (Unused) the subject of the topic in which a post is being suppressed
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-delete-post' => 'Intro for the moderation confirmation dialog when a post is being deleted. Parameters:
* $1 - (Unused) the name of the user whose post is being deleted. GENDER supported.
* $2 - (Unused) the subject of the topic in which a post is being deleted
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-hide-post' => 'Intro for the moderation confirmation dialog when a post is being hidden. Parameters:
* $1 - (Unused) the name of the user whose post is being hidden. GENDER supported.
* $2 - (Unused) the subject of the topic in which a post is being hidden
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-restore-post' => 'Intro for the restore confirmation dialog. Parameters:
* $1 - (Unused) the name of the user whose post is being suppressed. GENDER supported.
* $2 - (Unused) the subject of the topic in which a post is being suppressed
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
* $1 - the name of the user whose post is being moderated. GENDER supported.
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-delete-post' => 'Message displayed after a successful deletion of a post. Parameters:
* $1 - the name of the user whose post is being moderated. GENDER supported.
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-hide-post' => 'Message displayed after a successful hiding of a post. Parameters:
* $1 - the name of the user whose post is being moderated. GENDER supported.
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-restore-post' => 'Message displayed after a successful restoring of a post. Parameters:
* $1 - the name of the user whose post is being restored. GENDER supported.
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
* $1 - (Unused) the name of the user whose post is being suppressed. GENDER supported.
* $2 - (Unused) the subject of the topic in which a topic is being suppressed
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-delete-topic' => 'Intro for the moderation confirmation dialog when a topic is being deleted. Parameters:
* $1 - (Unused) the name of the user whose post is being deleted. GENDER supported.
* $2 - (Unused) the subject of the topic in which a topic is being deleted
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-hide-topic' => 'Intro for the moderation confirmation dialog when a topic is being hidden. Parameters:
* $1 - (Unused) the name of the user whose post is being hidden. GENDER supported.
* $2 - (Unused) the subject of the topic in which a topic is being hidden
{{Related|Flow-moderation-intro}}',
	'flow-moderation-intro-restore-topic' => 'Intro for the restore confirmation dialog. Parameters:
* $1 - (Unused) the name of the user whose post is being suppressed. GENDER supported.
* $2 - (Unused) the subject of the topic in which a topic is being suppressed
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
* $1 - the name of the user whose post is being moderated. GENDER supported.
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-delete-topic' => 'Message displayed after a successful deletion of a topic. Parameters:
* $1 - the name of the user whose post is being moderated. GENDER supported.
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-hide-topic' => 'Message displayed after a successful hiding of a topic. Parameters:
* $1 - the name of the user whose post is being moderated. GENDER supported.
{{Related|Flow-moderation-confirmation}}',
	'flow-moderation-confirmation-restore-topic' => 'Message displayed after a successful restoring of a topic. Parameters:
* $1 - the name of the user whose post is being restored. GENDER supported.
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
* {{msg-mw|Flow-revision-permalink-warning-post-first}}',
	'flow-revision-permalink-warning-post-first' => 'Header displayed at the top of a page when somebody is viewing a single-revision permalink of a post.

This message will only be shown for the first revision.

Note that the "topic permalink warning" (see {{msg-mw|Flow-topic-permalink-warning}}) will also be displayed.

Parameters:
* $1 - date and timestamp, formatted as most are in Flow. That is, a human-readable timestamp that changes into an RFC2822 timestamp when hovered over.
* $2 - title of the Flow Board that the post appears on. Example: User talk:Andrew
* $3 - title of the topic that this post appears in
* $4 - URL to the history page
See also:
* {{msg-mw|Flow-revision-permalink-warning-post}}',
	'flow-compare-revisions-revision-header' => 'Diff column header for a revision. Parameters:
* $1: Date and timestamp, formatted as most are in Flow. That is, a human-readable timestamp that changes into an RFC-2822 timestamp when hovered over.
* $2: User who made this revision.',
	'flow-compare-revisions-header-post' => 'Header for a page showing a "diff" between two revisions of a Flow post. Parameters:
* $1: The title of the Board on which this post sits. Example: User talk:Andrew.
* $2: The subject of the Topic in which this post sits.
* $3: The username of the author of the post.
* $4: URL to the Board, with the fragment set to the post in question.
* $5: URL to the Topic, with the fragment set to the post in question.
* $5: URL to the history page for this post.',
	'flow-topic-collapsed-one-line' => 'Used as title for the icon which is used to show small view of topics.

"Small view" is also called "Collapsed one line view".',
	'flow-topic-collapsed-full' => 'Used as title for the icon which is used to show collapsed view of topics.',
	'flow-topic-complete' => 'Used as title for the icon which is used to show full view of topics.
{{Identical|Full view}}',
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
	'flow-error-external' => 'حدث خطأ.<br /><small>رسالة الخطأ المتلقاة هي: $1</small>',
	'flow-moderation-title-restore-post' => 'استعد الصفحة',
	'flow-moderation-confirmation-restore-post' => 'لقد استعدت هذه الصفحة بنجاح.',
	'flow-topic-permalink-warning' => 'بدأ هذا الموضوع في [$2  $1]',
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'flow-desc' => 'Sistema de xestión del fluxu de trabayu',
);

/** Bulgarian (български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'flow-post-moderated-toggle-show' => '[Показване]',
	'flow-post-moderated-toggle-hide' => '[Скриване]',
	'flow-cancel' => 'Отказване',
	'flow-newtopic-header' => 'Добавяне на нова тема',
	'flow-newtopic-save' => 'Добавяне на тема',
	'flow-newtopic-start-placeholder' => 'Започване на нова тема',
	'flow-post-action-edit' => 'Редактиране',
	'flow-topic-action-watchlist' => 'Списък за наблюдение',
	'flow-link-history' => 'история',
);

/** Breton (brezhoneg)
 * @author Y-M D
 */
$messages['br'] = array(
	'flow-post-actions' => 'Oberoù',
	'flow-topic-actions' => 'Oberoù',
	'flow-cancel' => 'Nullañ',
	'flow-preview' => 'Rakwelet',
	'flow-post-action-delete-post' => 'Dilemel',
	'flow-post-action-hide-post' => 'Kuzhat',
	'flow-post-action-edit' => 'Kemmañ',
	'flow-topic-action-edit-title' => 'Kemmañ an titl',
	'flow-moderation-confirm-delete-topic' => 'Diverkañ',
	'flow-moderation-confirm-hide-topic' => 'Kuzhat',
	'flow-moderation-confirm-restore-topic' => 'Assevel',
);

/** Chechen (нохчийн)
 * @author Умар
 */
$messages['ce'] = array(
	'flow-post-actions' => 'дийраш',
	'flow-topic-actions' => 'Дийраш',
);

/** Czech (čeština)
 * @author Michaelbrabec
 * @author Mormegil
 */
$messages['cs'] = array(
	'flow-cancel' => 'Storno',
	'flow-newtopic-title-placeholder' => 'Nové téma',
	'flow-topic-action-edit-title' => 'Upravit název',
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
	'flow-post-moderated-toggle-show' => '[Anzeigen]',
	'flow-post-moderated-toggle-hide' => '[Ausblenden]',
	'flow-hide-content' => '{{GENDER:$1|Versteckt}} von $1',
	'flow-delete-content' => '{{GENDER:$1|Gelöscht}} von $1',
	'flow-suppress-content' => '{{GENDER:$1|Unterdrückt}} von $1',
	'flow-suppress-usertext' => '<em>Benutzername unterdrückt</em>',
	'flow-post-actions' => 'Aktionen',
	'flow-topic-actions' => 'Aktionen',
	'flow-cancel' => 'Abbrechen',
	'flow-preview' => 'Vorschau',
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
	'flow-talk-link' => 'Mit {{GENDER:$1|$1}} diskutieren',
	'flow-edit-post-submit' => 'Änderungen übertragen',
	'flow-post-edited' => 'Beitrag {{GENDER:$1|bearbeitet}} von $1 $2',
	'flow-post-action-view' => 'Permanentlink',
	'flow-post-action-post-history' => 'Beitragsversionsgeschichte',
	'flow-post-action-suppress-post' => 'Unterdrücken',
	'flow-post-action-delete-post' => 'Löschen',
	'flow-post-action-hide-post' => 'Verstecken',
	'flow-post-action-edit-post' => 'Beitrag bearbeiten',
	'flow-post-action-edit' => 'Bearbeiten',
	'flow-post-action-restore-post' => 'Beitrag wiederherstellen',
	'flow-topic-action-view' => 'Permanentlink',
	'flow-topic-action-watchlist' => 'Beobachtungsliste',
	'flow-topic-action-edit-title' => 'Titel bearbeiten',
	'flow-topic-action-history' => 'Themenversionsgeschichte',
	'flow-topic-action-hide-topic' => 'Thema verstecken',
	'flow-topic-action-delete-topic' => 'Thema löschen',
	'flow-topic-action-suppress-topic' => 'Thema unterdrücken',
	'flow-topic-action-restore-topic' => 'Thema wiederherstellen',
	'flow-error-http' => 'Beim Kontaktieren des Servers ist ein Fehler aufgetreten.',
	'flow-error-other' => 'Ein unerwarteter Fehler ist aufgetreten.',
	'flow-error-external' => 'Es ist ein Fehler aufgetreten.<br /><small>Die empfangene Fehlermeldung lautete: $1</small>',
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
	'flow-error-prev-revision-mismatch' => 'Die Bearbeitung konnte nicht gespeichert werden, da bereits eine aktuellere Änderung gespeichert wurde.',
	'flow-error-prev-revision-does-not-exist' => 'Die vorherige Version konnte nicht gefunden werden.',
	'flow-edit-header-submit' => 'Überschrift speichern',
	'flow-edit-title-submit' => 'Titel ändern',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|bearbeitete}} einen [$3 Kommentar].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|fügte}} einen [$3 Kommentar] hinzu.',
	'flow-rev-message-reply-bundle' => '{{PLURAL:$1|<strong>Ein Kommentar</strong> wurde|<strong>$1 Kommentare</strong> wurden}} hinzugefügt.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|erstellte}} das Thema [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|änderte}} den Thementitel von $5 zu [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|erstellte}} die Boardüberschrift.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|bearbeitete}} die Boardüberschrift.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|versteckte}} einen [$4 Kommentar] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|löschte}} einen [$4 Kommentar] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|unterdrückte}} einen [$4 Kommentar] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|stellte}} einen [$4 Kommentar] wieder her (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|versteckte}} das [$4 Thema] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|löschte}} das [$4 Thema] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|unterdrückte}} das [$4 Thema] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|stellte}} das [$4 Thema] wieder her (<em>$5</em>).',
	'flow-board-history' => 'Versionsgeschichte von „$1“',
	'flow-topic-history' => 'Themenversionsgeschichte von „$1“',
	'flow-post-history' => 'Beitragsversionsgeschichte – Kommentar von {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Letzte 4 Stunden',
	'flow-history-day' => 'Heute',
	'flow-history-week' => 'Letzte Woche',
	'flow-history-pages-topic' => 'Erscheint auf dem [$1 Board „$2“]',
	'flow-history-pages-post' => 'Erscheint auf [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 startete dieses Thema|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} und {{PLURAL:$2|ein anderer|andere}}|0=Noch keine Teilnehmer|2={{GENDER:$3|$3}} und {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} und {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Kommentar ($1)|Kommentare ($1)|0={{GENDER:$2|Sei der erste!}}}}',
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Sei der erste!|{{PLURAL:$1|Ein Kommentar|$1 Kommentare}} von {{PLURAL:$2|einem Benutzer|$2 Benutzern}}}}',
	'flow-comment-restored' => 'Kommentar wiederhergestellt',
	'flow-comment-deleted' => 'Kommentar gelöscht',
	'flow-comment-hidden' => 'Versteckter Kommentar',
	'flow-comment-moderated' => 'Kommentar moderiert',
	'flow-paging-rev' => 'Mehr aktuelle Themen',
	'flow-paging-fwd' => 'Ältere Themen',
	'flow-last-modified' => 'Zuletzt geändert $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|antwortete}} auf deinen [$5 Beitrag] in „$2“ auf [[$3|$4]].',
	'flow-notification-reply-bundle' => '$1 und {{PLURAL:$6|ein anderer|$5 andere}} {{GENDER:$1|antworteten}} auf deinen [$4 Beitrag] in $2 auf „$3“.',
	'flow-notification-edit' => '$1 {{GENDER:$1|bearbeitete}} einen [$5 Beitrag] in „$2“ auf [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 und {{PLURAL:$6|ein anderer|$5 andere}} {{GENDER:$1|bearbeiteten}} einen [$4 Beitrag] in $2 auf „$3“.',
	'flow-notification-newtopic' => '$1  {{GENDER:$1|erstellte}} ein [$5 neues Thema] auf [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|änderte}} den Titel von [$2 $3] nach „$4“ auf [[$5|$6]]',
	'flow-notification-mention' => '$1 hat dich in {{GENDER:$1|seinem|ihrem|seinem}} [$2 Beitrag] in „$3“ auf Seite „$4“ erwähnt.',
	'flow-notification-link-text-view-post' => 'Beitrag ansehen',
	'flow-notification-link-text-view-board' => 'Board ansehen',
	'flow-notification-link-text-view-topic' => 'Thema ansehen',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|antwortete}} auf deinen Beitrag',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|antwortete}} auf deinen Beitrag in $2 auf „$3“',
	'flow-notification-reply-email-batch-bundle-body' => '$1 und {{PLURAL:$5|ein anderer|$4 andere}} {{GENDER:$1|antworteten}} auf deinen Beitrag in $2 auf „$3“',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|erwähnte}} dich auf $2',
	'flow-notification-mention-email-batch-body' => '$1 hat dich in {{GENDER:$1|seinem|ihrem|seinem}} Beitrag in „$2“ auf der Seite „$3“ erwähnt',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|bearbeitete}} deinen Beitrag',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|bearbeitete}} deinen Beitrag in $2 auf der Seite „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 und {{PLURAL:$5|ein anderer|$4 andere}} {{GENDER:$1|bearbeiteten}} einen Beitrag in $2 auf der Seite „$3“',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|benannte}} dein Thema um',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|benannte}} dein Thema „$2“ in „$3“ auf der Seite „$4“ um',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|erstellte}} ein neues Thema auf $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|erstellte}} ein neues Thema mit dem Titel „$2“ auf $3',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Benachrichtige mich, wenn mich betreffende Aktionen in Flow stattfinden.',
	'flow-link-post' => 'Beitrag',
	'flow-link-topic' => 'Thema',
	'flow-link-history' => 'Versionsgeschichte',
	'flow-moderation-reason-placeholder' => 'Hier Begründung eingeben',
	'flow-moderation-title-suppress-post' => 'Beitrag unterdrücken?',
	'flow-moderation-title-delete-post' => 'Beitrag löschen?',
	'flow-moderation-title-hide-post' => 'Beitrag verstecken?',
	'flow-moderation-title-restore-post' => 'Beitrag wiederherstellen?',
	'flow-moderation-intro-suppress-post' => 'Bitte erkläre, warum du diesen Beitrag unterdrückst.',
	'flow-moderation-intro-delete-post' => 'Bitte erkläre, warum du diesen Beitrag löschst.',
	'flow-moderation-intro-hide-post' => 'Bitte erkläre, warum du diesen Beitrag versteckst.',
	'flow-moderation-intro-restore-post' => 'Bitte erkläre, warum du diesen Beitrag wiederherstellst.',
	'flow-moderation-confirm-suppress-post' => 'Unterdrücken',
	'flow-moderation-confirm-delete-post' => 'Löschen',
	'flow-moderation-confirm-hide-post' => 'Verstecken',
	'flow-moderation-confirm-restore-post' => 'Wiederherstellen',
	'flow-moderation-confirmation-suppress-post' => 'Ziehe in Erwägung, $1 eine Rückmeldung für diesen Beitrag zu {{GENDER:$1|geben}}.',
	'flow-moderation-confirmation-delete-post' => 'Ziehe in Erwägung, $1 eine Rückmeldung für diesen Beitrag zu {{GENDER:$1|geben}}.',
	'flow-moderation-confirmation-hide-post' => 'Ziehe in Erwägung, $1 eine Rückmeldung für diesen Beitrag zu {{GENDER:$1|geben}}.',
	'flow-moderation-confirmation-restore-post' => 'Du hast erfolgreich diesen Beitrag wiederhergestellt.',
	'flow-moderation-title-suppress-topic' => 'Thema unterdrücken?',
	'flow-moderation-title-delete-topic' => 'Thema löschen?',
	'flow-moderation-title-hide-topic' => 'Thema verstecken?',
	'flow-moderation-title-restore-topic' => 'Thema wiederherstellen?',
	'flow-moderation-intro-suppress-topic' => 'Bitte erkläre, warum du dieses Thema unterdrückst.',
	'flow-moderation-intro-delete-topic' => 'Bitte erkläre, warum du dieses Thema löschst.',
	'flow-moderation-intro-hide-topic' => 'Bitte erkläre, warum du dieses Thema versteckst.',
	'flow-moderation-intro-restore-topic' => 'Bitte erkläre, warum du dieses Thema wiederherstellst.',
	'flow-moderation-confirm-suppress-topic' => 'Unterdrücken',
	'flow-moderation-confirm-delete-topic' => 'Löschen',
	'flow-moderation-confirm-hide-topic' => 'Verstecken',
	'flow-moderation-confirm-restore-topic' => 'Wiederherstellen',
	'flow-moderation-confirmation-suppress-topic' => 'Ziehe in Erwägung, $1 eine Rückmeldung für dieses Thema zu {{GENDER:$1|geben}}.',
	'flow-moderation-confirmation-delete-topic' => 'Ziehe in Erwägung, $1 eine Rückmeldung für dieses Thema zu {{GENDER:$1|geben}}.',
	'flow-moderation-confirmation-hide-topic' => 'Ziehe in Erwägung, $1 eine Rückmeldung für dieses Thema zu {{GENDER:$1|geben}}.',
	'flow-moderation-confirmation-restore-topic' => 'Du hast dieses Thema erfolgreich wiederhergestellt.',
	'flow-topic-permalink-warning' => 'Dieses Thema wurde gestartet auf  [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Dieses Thema wurde gestartet auf dem [$2 Board von {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Dies ist ein Permanentlink zu einer einzelnen Version dieses Beitrags.
Diese Version ist vom $1.
Du kannst die [$5 Unterschiede von der vorherigen Version] oder andere Versionen in der [$4 Versionsgeschichte] ansehen.',
	'flow-revision-permalink-warning-post-first' => 'Dies ist ein Permanentlink zur ersten Version dieses Beitrags.
Du kannst spätere Versionen in der [$4 Versionsgeschichte] ansehen.',
	'flow-compare-revisions-revision-header' => 'Version von {{GENDER:$2|$2}} vom $1',
	'flow-compare-revisions-header-post' => 'Diese Seite zeigt die Änderungen zwischen zwei Versionen eines Beitrags von $3 im Thema „[$5 $2]“ auf [$4 $1] an.
Du kannst andere Versionen dieses Beitrags in der [$6 Versionsgeschichte] ansehen.',
	'flow-topic-collapsed-one-line' => 'Kleine Ansicht',
	'flow-topic-collapsed-full' => 'Zusammengeklappte Ansicht',
	'flow-topic-complete' => 'Volle Ansicht',
);

/** Greek (Ελληνικά)
 * @author Astralnet
 * @author Evropi
 * @author Geraki
 */
$messages['el'] = array(
	'flow-topic-actions' => 'Ενέργειες',
	'flow-preview' => 'Προεπισκόπηση',
	'flow-history-last4' => 'Τελευταίες 4 ώρες',
	'flow-history-day' => 'Σήμερα',
);

/** Spanish (español)
 * @author Benfutbol10
 * @author Fitoschido
 * @author Ihojose
 * @author Ovruni
 */
$messages['es'] = array(
	'flow-desc' => 'Sistema de gestión de flujo de trabajo',
	'log-name-flow' => 'Registro de actividad de flujo',
	'flow-user-moderated' => 'Usuario moderado',
	'flow-post-moderated-toggle-show' => '[Mostrar]',
	'flow-post-moderated-toggle-hide' => '[Ocultar]',
	'flow-suppress-usertext' => "''Nombre de usuario suprimido''", # Fuzzy
	'flow-post-actions' => 'Acciones',
	'flow-topic-actions' => 'Acciones',
	'flow-cancel' => 'Cancelar',
	'flow-preview' => 'Previsualizar',
	'flow-newtopic-title-placeholder' => 'Tema nuevo',
	'flow-newtopic-content-placeholder' => 'Si quieres, añade detalles',
	'flow-newtopic-header' => 'Añadir un nuevo tema',
	'flow-newtopic-save' => 'Añadir tema',
	'flow-newtopic-start-placeholder' => 'Iniciar un tema nuevo',
	'flow-reply-topic-placeholder' => 'Comentario en «$2»', # Fuzzy
	'flow-reply-placeholder' => 'Responder a $1',
	'flow-reply-submit' => 'Responder', # Fuzzy
	'flow-reply-link' => 'Responder', # Fuzzy
	'flow-thank-link' => 'Agradecer', # Fuzzy
	'flow-edit-post-submit' => 'Enviar cambios',
	'flow-post-edited' => 'Mensaje {{GENDER:$1|editado}} por $1 $2',
	'flow-post-action-view' => 'Enlace permanente',
	'flow-post-action-post-history' => 'Publicar historia',
	'flow-post-action-suppress-post' => 'Censurar mensaje',
	'flow-post-action-delete-post' => 'Eliminar',
	'flow-post-action-hide-post' => 'Ocultar',
	'flow-post-action-edit-post' => 'Editar mensaje',
	'flow-post-action-edit' => 'Editar',
	'flow-post-action-restore-post' => 'Restaurar mensaje',
	'flow-topic-action-view' => 'Enlace permanente',
	'flow-topic-action-watchlist' => 'Lista de seguimiento',
	'flow-topic-action-edit-title' => 'Editar título',
	'flow-topic-action-history' => 'Historial del tema',
	'flow-topic-action-hide-topic' => 'Ocultar el tema',
	'flow-topic-action-delete-topic' => 'Eliminar el tema',
	'flow-topic-action-suppress-topic' => 'Suprimir el tema',
	'flow-topic-action-restore-topic' => 'Restaurar el tema',
	'flow-error-http' => 'Ha ocurrido un error mientras se contactaba al servidor.',
	'flow-error-other' => 'Ha ocurrido un error inesperado.',
	'flow-error-external' => 'Se ha producido un error.<br /><small>El mensaje de error recibido es: $1</small>',
	'flow-error-edit-restricted' => 'No tienes permitido editar esta entrada.',
	'flow-error-external-multi' => 'Se han encontrado errores.<br />$1',
	'flow-error-missing-content' => 'La entrada no tiene contenido. Para guardarla necesitas añadir contenido.', # Fuzzy
	'flow-error-missing-title' => 'El tema no tiene título. Para guardarlo necesitas añadirle un título.', # Fuzzy
	'flow-error-delete-failure' => 'Falló la eliminación de este elemento.',
	'flow-error-hide-failure' => 'Falló el ocultamiento de este elemento.',
	'flow-error-restore-failure' => 'Falló la restauración de este elemento.',
	'flow-edit-title-submit' => 'Cambiar el título',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|editó}} un [$3 comentario].',
	'flow-rev-message-deleted-post' => '[[User:$1|$1]] borró un [$3 comentario].', # Fuzzy
	'flow-comment-restored' => 'Comentario restaurado',
	'flow-comment-deleted' => 'Comentario eliminado',
	'flow-comment-hidden' => 'Comentario oculto',
	'flow-comment-moderated' => 'Comentario moderado',
	'flow-paging-rev' => 'Más temas recientes',
	'flow-paging-fwd' => 'Temas anteriores',
	'flow-last-modified' => 'Última modificación hace $1',
	'flow-notification-link-text-view-post' => 'Ver la entrada',
	'flow-notification-link-text-view-topic' => 'Ver el tema',
	'flow-moderation-reason-placeholder' => 'Ingresa tu razón aquí',
	'flow-moderation-title-suppress-post' => '¿Quieres suprimir la entrada?',
	'flow-moderation-title-delete-post' => '¿Quieres eliminar la entrada?',
	'flow-moderation-title-hide-post' => '¿Quieres ocultar la entrada?',
	'flow-moderation-title-restore-post' => '¿Quieres restaurar la entrada?',
	'flow-moderation-confirm-suppress-post' => 'Suprimir',
	'flow-moderation-confirm-delete-post' => 'Eliminar',
	'flow-moderation-confirm-hide-post' => 'Ocultar',
	'flow-moderation-confirm-restore-post' => 'Restaurar',
	'flow-moderation-confirmation-restore-post' => 'Has restaurado el tema correctamente.',
	'flow-moderation-title-suppress-topic' => '¿Quieres suprimir el tema?',
	'flow-moderation-title-delete-topic' => '¿Quieres eliminar el tema?',
	'flow-moderation-title-hide-topic' => '¿Quieres ocultar el tema?',
	'flow-moderation-title-restore-topic' => '¿Quieres restaurar el tema?',
	'flow-moderation-confirm-suppress-topic' => 'Suprimir',
	'flow-moderation-confirm-delete-topic' => 'Eliminar',
	'flow-moderation-confirm-hide-topic' => 'Ocultar',
	'flow-moderation-confirm-restore-topic' => 'Restaurar',
);

/** Persian (فارسی)
 * @author Amire80
 * @author Ebraminio
 */
$messages['fa'] = array(
	'flow-desc' => 'سامانهٔ مدیریت گردش کار',
	'flow-topic-comments' => '{{PLURAL:$1|نظر ($1)|نظرها ($1)|0=اولین شخصی باشید که نظر می‌گذارد!}}', # Fuzzy
);

/** Finnish (suomi)
 * @author Nike
 * @author Stryn
 */
$messages['fi'] = array(
	'flow-post-actions' => 'toiminnot', # Fuzzy
	'flow-topic-actions' => 'toiminnot', # Fuzzy
	'flow-cancel' => 'Peru',
	'flow-newtopic-title-placeholder' => 'Viestin aihe', # Fuzzy
	'flow-newtopic-content-placeholder' => 'Viestin teksti. Ole mukava!', # Fuzzy
	'flow-newtopic-header' => 'Lisää uusi aihe',
	'flow-newtopic-save' => 'Lisää aihe',
	'flow-newtopic-start-placeholder' => 'Aloita uusi aihe napsauttamalla tästä. Muistathan kohteliaat käytöstavat!', # Fuzzy
	'flow-reply-placeholder' => 'Paina tästä vastataksesi käyttäjälle $1. Ole mukava!', # Fuzzy
	'flow-reply-submit' => 'Lähetä vastaus', # Fuzzy
	'flow-edit-post-submit' => 'Lähetä muutokset',
	'flow-post-action-view' => 'Ikilinkki',
	'flow-post-action-edit' => 'Muokkaa',
	'flow-post-action-restore-post' => 'Palauta viesti',
	'flow-topic-action-edit-title' => 'Muokkaa otsikkoa',
	'flow-topic-action-history' => 'Aiheen historia',
	'flow-error-not-allowed' => 'Käyttöoikeutesi eivät riitä tämän toiminnon suorittamiseen',
	'flow-edit-title-submit' => 'Muuta otsikkoa',
	'flow-moderation-reason-placeholder' => 'Kirjoita syy tähän',
	'flow-moderation-title-suppress-post' => 'Viestin sensurointi',
	'flow-moderation-title-delete-post' => 'Viestin poisto',
	'flow-moderation-title-hide-post' => 'Viestin piilotus',
	'flow-moderation-title-restore-post' => 'Viestin palauttaminen',
	'flow-moderation-confirmation-restore-post' => 'Viesti on palautettu.',
);

/** French (français)
 * @author Ayack
 * @author Gomoko
 * @author Jean-Frédéric
 * @author Linedwell
 * @author Ltrlg
 * @author Sherbrooke
 * @author VIGNERON
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
	'flow-post-moderated-toggle-show' => '[Afficher]',
	'flow-post-moderated-toggle-hide' => '[Masquer]',
	'flow-hide-content' => '{{GENDER:$1|Masqué}} par $1',
	'flow-delete-content' => '{{GENDER:$1|Supprimé}} par $1',
	'flow-suppress-content' => '{{GENDER:$1|Supprimé}} par $1',
	'flow-suppress-usertext' => '<em>Nom d’utilisateur supprimé</em>',
	'flow-post-actions' => 'Actions',
	'flow-topic-actions' => 'Actions',
	'flow-cancel' => 'Annuler',
	'flow-preview' => 'Prévisualiser',
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
	'flow-talk-link' => 'Parler à {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Soumettre les modifications',
	'flow-post-edited' => 'Note {{GENDER:$1|modifiée}} par $1 $2',
	'flow-post-action-view' => 'Lien permanent',
	'flow-post-action-post-history' => 'Historique des publications',
	'flow-post-action-suppress-post' => 'Supprimer',
	'flow-post-action-delete-post' => 'Supprimer',
	'flow-post-action-hide-post' => 'Masquer',
	'flow-post-action-edit-post' => 'Modifier la publication',
	'flow-post-action-edit' => 'Modifier',
	'flow-post-action-restore-post' => 'Restaurer le message',
	'flow-topic-action-view' => 'Lien permanent',
	'flow-topic-action-watchlist' => 'Liste de surveillance',
	'flow-topic-action-edit-title' => 'Modifier le titre',
	'flow-topic-action-history' => 'Historique des sujets',
	'flow-topic-action-hide-topic' => 'Masquer le sujet',
	'flow-topic-action-delete-topic' => 'Supprimer le sujet',
	'flow-topic-action-suppress-topic' => 'Supprimer le sujet',
	'flow-topic-action-restore-topic' => 'Rétablir le sujet',
	'flow-error-http' => 'Une erreur s’est produite en communiquant avec le serveur.',
	'flow-error-other' => 'Une erreur inattendue s’est produite.',
	'flow-error-external' => 'Une erreur s’est produite.<br /><small>Le message d’erreur reçu était : $1</small>',
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
	'flow-error-prev-revision-mismatch' => 'La modification n’a pas pu être enregistrée : une modification plus récente a déjà été envoyée.',
	'flow-error-prev-revision-does-not-exist' => 'Impossible de trouver la révision précédente.',
	'flow-edit-header-submit' => 'Enregistrer l’entête',
	'flow-edit-title-submit' => 'Changer le titre',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|a modifié}} un [$3 commentaire].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|a ajouté}} un [$3 commentaire].',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|commentaire|commentaires}}</strong> {{PLURAL:$1|a été ajouté|ont été ajoutés}}.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|a créé}} le sujet [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|a changé}} le titre du sujet de [$3 $4], précédemment $5.',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|a créé}} l’entête du tableau.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|a modifié}} l’entête du tableau.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|a masqué}} un [$4 commentaire] (<em>$5</em>)..',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|a supprimé}} un [$4 commentaire] (<em>$5</em>)..',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|a effacé}} un [$4 commentaire] (<em>$5</em>)..',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|a rétabli}} un [$4 commentaire] (<em>$5</em>)..',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|a masqué}} le [$4 sujet] (<em>$5</em>)..',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|a supprimé}} le [$4 sujet] (<em>$5</em>)..',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|a supprimé}} le [$4 sujet] (<em>$5</em>)..',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|a rétabli}} le [$4 sujet] (<em>$5</em>)..',
	'flow-board-history' => 'Historique de « $1 »',
	'flow-topic-history' => 'Historique du sujet « $1 »',
	'flow-post-history' => 'Commentaire par {{GENDER:$2|$2}} Historique de la note',
	'flow-history-last4' => 'Dernières 4 heures',
	'flow-history-day' => 'Aujourd’hui',
	'flow-history-week' => 'Semaine dernière',
	'flow-history-pages-topic' => 'Apparaît sur [$1 le tableau « $2 »]',
	'flow-history-pages-post' => 'Apparaît sur [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 a démarré ce sujet|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} et {{PLURAL:$2|autre|autres}}|0=Encore aucune participation|2={{GENDER:$3|$3}} et {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} et {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Soyez le premier à laisser un commentaire !|Commenter ($1)}}', # Fuzzy
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Soyez le premier à commenter !|$1 {{PLURAL:$1|commentaire|commentaires}} par $2 {{PLURAL:$2|utilisateur|utilisateurs}}}}',
	'flow-comment-restored' => 'Commentaire rétabli',
	'flow-comment-deleted' => 'Commentaire supprimé',
	'flow-comment-hidden' => 'Commentaire masqué',
	'flow-comment-moderated' => 'Commentaire soumis à modération',
	'flow-paging-rev' => 'Sujets les plus récents',
	'flow-paging-fwd' => 'Sujets plus anciens',
	'flow-last-modified' => 'Dernière modification $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|a répondu}} à votre [$5 note] sur $2 en [[$3|$4]].',
	'flow-notification-reply-bundle' => '$1 et $5 {{PLURAL:$6|autre|autres}} {{GENDER:$1|ont répondu}} à votre [$4 note] concernant $2 sur « $3 ».',
	'flow-notification-edit' => '$1 {{GENDER:$1|a modifié}} une [$5 note] sur $2 en [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 et $5 {{PLURAL:$6|autre|autres}} {{GENDER:$1|ont modifié}} une [$4 note] sur $2 en « $3 ».',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|a créé}} un [$5 nouveau sujet] en [[$2|$3]] : $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|a modifié}} le titre de [$2 $3] en « $4 » sur [[$5|$6]].',
	'flow-notification-mention' => '$1 vous {{GENDER:$1|a mentionné|a mentionné|ont mentionné}} dans {{GENDER:$1|sa|sa|leur}} [$2 note] sur « $3 » en « $4 »',
	'flow-notification-link-text-view-post' => 'Afficher la note',
	'flow-notification-link-text-view-board' => 'Afficher le tableau',
	'flow-notification-link-text-view-topic' => 'Afficher le sujet',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|a répondu}} à votre note',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|a répondu}} a votre note concernant $2 sur « $3 »',
	'flow-notification-reply-email-batch-bundle-body' => '$1 et $4 {{PLURAL:$5|autre|autres}} {{GENDER:$1|ont répondu}} à votre note concernant $2 sur « $3 »',
	'flow-notification-mention-email-subject' => '$1 vous {{GENDER:$1|a mentionné}} en $2',
	'flow-notification-mention-email-batch-body' => '$1 vous {{GENDER:$1|a mentionné}} dans {{GENDER:$1|sa}} note sur « $2 » en « $3 »',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|a modifié}} votre note',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|a modifié}} votre note sur $2 en « $3 »',
	'flow-notification-edit-email-batch-bundle-body' => '$1 et $4 {{PLURAL:$5|autre|autres}} {{GENDER:$1|ont modifié}} une note sur $2 en « $3 »',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|a renommé}} votre sujet',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|a renommé}} votre sujet « $2 » en « $3 » sur « $4 »',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|a créé}} un nouveau sujet sur $2',
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
	'flow-moderation-intro-suppress-post' => 'Veuillez expliquer pourquoi vous censurez cette note.',
	'flow-moderation-intro-delete-post' => 'Veuillez expliquer pourquoi vous supprimez cette note.',
	'flow-moderation-intro-hide-post' => 'Veuillez expliquer pourquoi vous cachez cette note.',
	'flow-moderation-intro-restore-post' => 'Veuillez expliquer pourquoi vous restaurez cette note.',
	'flow-moderation-confirm-suppress-post' => 'Supprimer',
	'flow-moderation-confirm-delete-post' => 'Supprimer',
	'flow-moderation-confirm-hide-post' => 'Masquer',
	'flow-moderation-confirm-restore-post' => 'Rétablir',
	'flow-moderation-confirmation-suppress-post' => 'Penser à {{GENDER:$1|donner}} à $1 un avis sur cette note.',
	'flow-moderation-confirmation-delete-post' => 'Penser à {{GENDER:$1|donner}} à $1 un avis sur cette note.',
	'flow-moderation-confirmation-hide-post' => 'Penser à {{GENDER:$1|donner}} à $1 un avis sur cette note.',
	'flow-moderation-confirmation-restore-post' => 'Vous avez bien restauré cette note.',
	'flow-moderation-title-suppress-topic' => 'Supprimer le sujet ?',
	'flow-moderation-title-delete-topic' => 'Supprimer le sujet ?',
	'flow-moderation-title-hide-topic' => 'Masquer le sujet ?',
	'flow-moderation-title-restore-topic' => 'Rétablir le sujet ?',
	'flow-moderation-intro-suppress-topic' => 'Veuillez expliquer pourquoi vous supprimez ce sujet.',
	'flow-moderation-intro-delete-topic' => 'Veuillez expliquer pourquoi vous supprimez ce sujet.',
	'flow-moderation-intro-hide-topic' => 'Veuillez expliquer pourquoi vous masquez ce sujet.',
	'flow-moderation-intro-restore-topic' => 'Veuillez expliquer pourquoi vous rétablissez ce sujet.',
	'flow-moderation-confirm-suppress-topic' => 'Supprimer',
	'flow-moderation-confirm-delete-topic' => 'Supprimer',
	'flow-moderation-confirm-hide-topic' => 'Masquer',
	'flow-moderation-confirm-restore-topic' => 'Rétablir',
	'flow-moderation-confirmation-suppress-topic' => 'Penser à {{GENDER:$1|donner}} à $1 un avis sur ce sujet.',
	'flow-moderation-confirmation-delete-topic' => 'Penser à {{GENDER:$1|donner}} à $1 un avis sur ce sujet.',
	'flow-moderation-confirmation-hide-topic' => 'Penser à {{GENDER:$1|donner}} à $1 un avis sur ce sujet.',
	'flow-moderation-confirmation-restore-topic' => 'Vous avez bien rétabli ce sujet.',
	'flow-topic-permalink-warning' => 'Ce sujet a été démarré sur [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Ce sujet a été démarré sur le tableau de [$2 {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Voici un lien permanent vers une version unique de cette note.
Cette version date de $1.
Vous pouvez voir les [$5 différences depuis la version précédente], ou afficher d’autres versions sur la [$4 page d’historique de la note].',
	'flow-revision-permalink-warning-post-first' => 'Voici un lien permanent vers la première version de cette note.
Vous pouvez afficher des versions ultérieures depuis la [$4 page d’historique de la note].',
	'flow-compare-revisions-revision-header' => 'Version par {{GENDER:$2|$2}} du $1',
	'flow-compare-revisions-header-post' => 'Cette page affiche les modifications entre deux versions d’une note par $3 dans le sujet « [$5 $2] » sur [$4 $1].
Vous pouvez voir d’autres versions de cette note dans sa [$6 page d’historique].',
	'flow-topic-collapsed-one-line' => 'Vue petite',
	'flow-topic-collapsed-full' => 'Vue réduite',
	'flow-topic-complete' => 'Vue complète',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'flow-desc' => 'Sistema de xestión do fluxo de traballo',
	'flow-edit-header-link' => 'Editar a cabeceira',
	'flow-header-empty' => 'Actualmente, esta páxina de conversa non ten cabeceira.',
	'flow-post-moderated-toggle-show' => '[Mostrar]',
	'flow-post-moderated-toggle-hide' => '[Agochar]',
	'flow-post-actions' => 'Accións',
	'flow-topic-actions' => 'Accións',
	'flow-cancel' => 'Cancelar',
	'flow-newtopic-title-placeholder' => 'Asunto da mensaxe', # Fuzzy
	'flow-newtopic-content-placeholder' => 'Texto da mensaxe. Sexa amable!', # Fuzzy
	'flow-newtopic-header' => 'Engadir un novo fío',
	'flow-newtopic-save' => 'Nova sección',
	'flow-newtopic-start-placeholder' => 'Iniciar un novo fío',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Comentario}} en "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|Responder}} a $1',
	'flow-reply-submit' => '{{GENDER:$1|Responder}}',
	'flow-reply-link' => '{{GENDER:$1|Responder}}',
	'flow-thank-link' => '{{GENDER:$1|Agradecer}}',
	'flow-talk-link' => 'Falarlle a {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Enviar os cambios',
	'flow-post-edited' => 'Mensaxe {{GENDER:$1|editada}} por $1 $2',
	'flow-post-action-view' => 'Ligazón permanente',
	'flow-post-action-post-history' => 'Historial da mensaxe',
	'flow-post-action-suppress-post' => 'Censurar a mensaxe',
	'flow-post-action-delete-post' => 'Borrar a mensaxe', # Fuzzy
	'flow-post-action-hide-post' => 'Agochar a mensaxe', # Fuzzy
	'flow-post-action-edit-post' => 'Editar a mensaxe',
	'flow-post-action-edit' => 'Editar',
	'flow-post-action-restore-post' => 'Restaurar a mensaxe',
	'flow-topic-action-view' => 'Ligazón permanente',
	'flow-topic-action-watchlist' => 'Lista de vixilancia',
	'flow-topic-action-edit-title' => 'Editar o título',
	'flow-topic-action-history' => 'Historial do fío',
	'flow-error-http' => 'Produciuse un erro ao contactar co servidor. Non se gardou a súa mensaxe.', # Fuzzy
	'flow-error-other' => 'Produciuse un erro inesperado. Non se gardou a súa mensaxe.', # Fuzzy
	'flow-error-external' => 'Produciuse un erro ao gardar a súa mensaxe. Non se gardou a súa mensaxe.<br /><small>A mensaxe de erro recibida foi: $1</small>', # Fuzzy
	'flow-error-edit-restricted' => 'Non lle está permitido editar esta mensaxe.',
	'flow-error-external-multi' => 'Producíronse erros ao gardar a súa mensaxe. Non se gardou a súa mensaxe.<br />$1', # Fuzzy
	'flow-error-missing-content' => 'A mensaxe non ten contido. O contido é obrigatorio para gardar unha nova mensaxe.', # Fuzzy
	'flow-error-missing-title' => 'O fío non ten título. O título é obrigatorio para gardar un novo fío.', # Fuzzy
	'flow-error-parsoid-failure' => 'Non é posible analizar o contido debido a un fallo do Parsoid.',
	'flow-error-missing-replyto' => 'Non se achegou ningún parámetro de resposta. Este parámetro é obrigatorio para a acción "responder".',
	'flow-error-invalid-replyto' => 'O parámetro de resposta non é válido. Non se puido atopar a mensaxe especificada.',
	'flow-error-delete-failure' => 'Houbo un erro ao borrar este elemento.',
	'flow-error-hide-failure' => 'Houbo un erro ao agochar este elemento.',
	'flow-error-missing-postId' => 'Non se achegou ningún parámetro de identificación. Este parámetro é obrigatorio para a manipular unha mensaxe.',
	'flow-error-invalid-postId' => 'O parámetro de identificación non é válido. Non se puido atopar a mensaxe especificada.', # Fuzzy
	'flow-error-restore-failure' => 'Houbo un erro ao restaurar este elemento.',
	'flow-edit-header-submit' => 'Gardar a cabeceira',
	'flow-edit-title-submit' => 'Cambiar o título',
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
	'flow-topic-history' => 'Historial do fío', # Fuzzy
	'flow-comment-restored' => 'Comentario restaurado',
	'flow-comment-deleted' => 'Comentario borrado',
	'flow-comment-hidden' => 'Comentario agochado',
	'flow-comment-moderated' => 'Comentario moderado',
	'flow-paging-rev' => 'Fíos máis recentes',
	'flow-paging-fwd' => 'Fíos máis vellos',
	'flow-last-modified' => 'Última modificación $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|respondeu}} á súa [$5 mensaxe] "$2" en "$4".',
	'flow-notification-reply-bundle' => '$1 e {{PLURAL:$6|outra persoa|outras $5 persoas}} {{GENDER:$1|responderon}} á súa [$4 mensaxe] "$2" en "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|editou}} a [$5 mensaxe] "$2" en "[[$3|$4]]".',
	'flow-notification-edit-bundle' => '$1 e {{PLURAL:$6|outra persoa|outras $5 persoas}} {{GENDER:$1|responderon}} á [$4 mensaxe] "$2" en "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|creou}} un [$5 novo fío] en "[[$2|$3]]": "$4".',
	'flow-notification-rename' => '$1 {{GENDER:$1|cambiou}} o título de "[$2 $3]" a "$4" en "[[$5|$6]]"',
	'flow-notification-mention' => '$1 {{GENDER:$1|fíxolle unha mención}} na súa [$2 mensaxe] "$3" en "$4"', # Fuzzy
	'flow-notification-link-text-view-post' => 'Ver a mensaxe',
	'flow-notification-link-text-view-board' => 'Ver o taboleiro',
	'flow-notification-link-text-view-topic' => 'Ver o fío',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|respondeu}} á súa mensaxe',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|respondeu}} á súa mensaxe "$2" en "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e {{PLURAL:$5|outra persoa|outras $4 persoas}} {{GENDER:$1|responderon}} á súa mensaxe "$2" en "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|fíxolle unha mención}} en "$2"',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|fíxolle unha mención}} na súa mensaxe "$2" en "$3"', # Fuzzy
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|editou}} a súa mensaxe',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|editou}} a súa mensaxe "$2" en "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 e {{PLURAL:$5|outra persoa|outras $4 persoas}} {{GENDER:$1|editaron}} a mensaxe "$2" en "$3".',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|renomeou}} o seu fío',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|renomeou}} o seu fío "$2" a "$3" en "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|creou}} un novo fío en "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|creou}} un novo fío co título "$2" en "$3"',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Conversa|Conversas}}', # Fuzzy
	'echo-pref-tooltip-flow-discussion' => 'Notificádeme cando sucedan accións relacionadas comigo no taboleiro de conversas.', # Fuzzy
	'flow-link-post' => 'mensaxe',
	'flow-link-topic' => 'fío',
	'flow-link-history' => 'historial',
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
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 רשומה] בדף [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|שחזר|שחזרה}} [$4 רשומה] בדף [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|העלים|העלימה}} [$4 נושא] בדף [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 נושא] בדף [[$3]]',
	'flow-user-moderated' => 'משתמש מפוקח',
	'flow-edit-header-link' => 'עריכת התיאור',
	'flow-header-empty' => 'לדף השיחה הזה אין כרגע תיאור.',
	'flow-post-moderated-toggle-show' => '[להציג]',
	'flow-post-moderated-toggle-hide' => '[להסתיר]',
	'flow-hide-content' => 'הוסתר על־ידי $1',
	'flow-delete-content' => 'הוסתר על־ידי $1',
	'flow-suppress-content' => 'הועלם על־ידי $1',
	'flow-suppress-usertext' => '<strong>השם הועלם</strong>',
	'flow-post-actions' => 'פעולות',
	'flow-topic-actions' => 'פעולות',
	'flow-cancel' => 'ביטול',
	'flow-preview' => 'תצוגה מקדימה',
	'flow-newtopic-title-placeholder' => 'כותרת חדשה',
	'flow-newtopic-content-placeholder' => 'אפשר להוסיף כאן פרטים',
	'flow-newtopic-header' => 'הוספת נושא חדש',
	'flow-newtopic-save' => 'נוספת נושא',
	'flow-newtopic-start-placeholder' => 'התחלת נושא חדש',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|הגב|הגיבי|להגיב}} על "$2"',
	'flow-reply-placeholder' => '{{GENDER:$1|השב|השיבי|להשיב}} ל{{GRAMMAR:תחילית|$1}}',
	'flow-reply-submit' => '{{GENDER:$1|להשיב}}',
	'flow-reply-link' => '{{GENDER:$1|השב|השיבי|להשיב}}',
	'flow-thank-link' => '{{GENDER:$1|תודה}}',
	'flow-talk-link' => 'לדבר עם $1',
	'flow-edit-post-submit' => 'שליחת שינויים',
	'flow-post-edited' => '$1 {{GENDER:$1|ערך|ערכה}} את הרשומה $2',
	'flow-post-action-view' => 'קישור קבוע',
	'flow-post-action-post-history' => 'היסטוריית הרשומה',
	'flow-post-action-suppress-post' => 'להעלים',
	'flow-post-action-delete-post' => 'למחוק',
	'flow-post-action-hide-post' => 'להסתיר',
	'flow-post-action-edit-post' => 'לערוך את הרשומה',
	'flow-post-action-edit' => 'עריכה',
	'flow-post-action-restore-post' => 'לשחזר את הרשומה',
	'flow-topic-action-view' => 'קישור קבוע',
	'flow-topic-action-watchlist' => 'רשימת מעקב',
	'flow-topic-action-edit-title' => 'עריכת כותרת',
	'flow-topic-action-history' => 'היסטוריית הנושא',
	'flow-topic-action-hide-topic' => 'להסתיר נושא',
	'flow-topic-action-delete-topic' => 'למחוק נושא',
	'flow-topic-action-suppress-topic' => 'להעלים נושא',
	'flow-topic-action-restore-topic' => 'לשחזר נושא',
	'flow-error-http' => 'אירעה שגיאה בעת התחברות לשרת',
	'flow-error-other' => 'אירעה שגיאה בלתי־צפויה.',
	'flow-error-external' => 'אירעה שגיאה בעת ניסיון לשמור את הרשומה שלך.<br /><small>התקבלה הודעת השגיאה הבאה: $1</small>',
	'flow-error-edit-restricted' => 'אין לך הרשאה לערוך את הרשומה הזאת.',
	'flow-error-external-multi' => 'אירעו שגיאות.<br />
$1',
	'flow-error-missing-content' => 'ברשומה אין תוכן. דרוש תוכן כדי לשמור רשומה',
	'flow-error-missing-title' => 'לנושא אין כותרת. דרושה כותרת כדי לשמור נושא.',
	'flow-error-parsoid-failure' => 'לא ניתן לפענח את התוכן עקב כשל בפרסואיד.',
	'flow-error-missing-replyto' => 'לא נשלח פרמטר "replyTo". הפרמטר הזה דרוש לפעולת "reply".',
	'flow-error-invalid-replyto' => 'פרמטר "replyTo" שנשלח היה בלתי־תקין. לא נמצאה הרשומה שצוינה.',
	'flow-error-delete-failure' => 'מחירת הפריט הזה נכשלה.',
	'flow-error-hide-failure' => 'הסתרת הפריט הזה נכשלה.',
	'flow-error-missing-postId' => 'לא ניתן פרמטר "postId". הפרמטר הזה דרוש כדי לשנות רשומה.',
	'flow-error-invalid-postId' => 'פרמטר "postId" שנשלח היה בלתי־תקין. הרשומה שצוינה ($1) לא נמצאה.',
	'flow-error-restore-failure' => 'שחזור הפריט הזה נכשל.',
	'flow-error-invalid-moderation-state' => 'ערך בלתי־תקין ניתן לפרמטר moderationState',
	'flow-error-invalid-moderation-reason' => 'נא לתת סיבה להחלת הפיקוח',
	'flow-error-not-allowed' => 'אין הרשאות מספיקות לביצוע הפעולה הזאת',
	'flow-error-title-too-long' => 'כותרות של נושאים מוגבלות {{PLURAL:$1|לבית אחד|ל־$1 בתים}}',
	'flow-error-no-existing-workflow' => 'הזרימה הזאת עוד לא קיימת.',
	'flow-error-not-a-post' => 'לא ניתן לשמור כותרת נושא בתור רשומה.',
	'flow-error-missing-header-content' => 'בתיאור אין תוכן. התוכן נחוץ לשם שמירת תיאור.',
	'flow-error-missing-prev-revision-identifier' => 'חסר מזהה גרסה קודמת.',
	'flow-error-prev-revision-mismatch' => 'לא ניתן לשמור את העריכה: כבר נשלחה גרסה חדשה יותר.',
	'flow-error-prev-revision-does-not-exist' => 'לא נמצאה גרסה קודמת.',
	'flow-edit-header-submit' => 'שמירת התיאור',
	'flow-edit-title-submit' => 'שינוי כותרת',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|ערך|ערכה}} [$3 הערה].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|הוסיף|הוסיפה}} [$3 הערה].',
	'flow-rev-message-reply-bundle' => '{{PLURAL:$1|נוספה <strong>הערה אחת</strong>|נוספו <strong>$1 הערות</strong>}}',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|יצר|יצרה}} את הנושא [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|שינה|שינתה}} את כותרת הנושא מ{{GRAMMAR:תחילית|$5}} אל [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|יצר|יצרה}} את תיאור הלוח.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|ערך|ערכה}} את כותרת הלוח.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|הסתיר|הסתירה}} [$4 הערה] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|מחק|מחקה}} [$4 הערה] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|העלים|העלימה}} [$4 הערה] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|שחזר|שחזרה}} [$4 הערה] (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|הסתיר|הסתירה}} את [$4 הנושא] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|מחק|מחקה}} את [$4 הנושא] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|העלים|העלימה}} את [$4 הנושא] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|שחזר|שחזרה}} את [$4 הנושא] (<em>$5</em>).',
	'flow-board-history' => 'ההיסטוריה של "$1"',
	'flow-topic-history' => 'היסטוריית הנושא "$1"',
	'flow-post-history' => 'ההיסטוריה של "הערה מאת $2"',
	'flow-history-last4' => '4 השעות האחרונות',
	'flow-history-day' => 'היום',
	'flow-history-week' => 'בשבוע שעבר',
	'flow-history-pages-topic' => 'מופיע ב[$1 לוח "$2"]',
	'flow-history-pages-post' => 'מופיע ב[$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 {{GENDER:$3|התחיל|התחילה}} את הנושא הזה|$3, $4, $5 ועוד {{PLURAL:$2|אדם אחד|$2 אנשים}}|0=אין עדיין השתתפות|2=$3 ו{{GRAMMAR:תחילית|$4}}|3=$3, $4 ו{{GRAMMAR:תחילית|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|תגובה ($1)|תגובות ($1)|0={{GENDER:$2|כתוב|כתבי}} את התגובה הראשונה!}}',
	'flow-comment-restored' => 'הערה משוחזרת',
	'flow-comment-deleted' => 'הערה מחוקה',
	'flow-comment-hidden' => 'הערה מוסתרת',
	'flow-comment-moderated' => 'הערה מפוקחת',
	'flow-paging-rev' => 'נושאים חדשים יותר',
	'flow-paging-fwd' => 'נושאים ישנים יותר',
	'flow-last-modified' => 'שוּנה לאחרונה $1 בערך',
	'flow-notification-reply' => '$1 {{GENDER:$1|השיב|השיבה}} ל[$5 רשומה] שלך בנושא $2 בדף "$4".',
	'flow-notification-reply-bundle' => '$1 {{PLURAL:$6|ועוד אדם אחד|ו־$5 אנשים אחרים}} השיבו ל[$4 רשומה] שלך בנושא $2 בדף "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|ערך|ערכה}} [$5 רשומה] בנושא $2 בדף [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 {{PLURAL:$6|ועוד אדם אחד|ועוד $5 אנשים}} ערכו ל[$4 רשומה] שלך בנושא $2 בדף "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|יצר|יצרה}} [$5 נושא חדש] בדף [[$2|$3]]&rlm;: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|שינה|שינתה}} את הכותרת של [$2 $3] אל "$4" בדף [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|הזכיר|הזכירה}} אותך ב[$2 רשומה] {{GENDER:$1|שלו|שלה}} בנושא "$3" בדף "$4".',
	'flow-notification-link-text-view-post' => 'הצגת הרשומה',
	'flow-notification-link-text-view-board' => 'הצגת הלוח',
	'flow-notification-link-text-view-topic' => 'הצגת הנושא',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|השיב|השיבה}} לרשומה שלך',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|השיב|השיבה}} לרשומה שלך בנושא $2 בדף "$3".',
	'flow-notification-reply-email-batch-bundle-body' => '$1 {{PLURAL:$5|ועוד אדם אחד|ועוד $4 אנשים}} השיבו לרשומה שלך בנושא $2 בדף "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|הזכיר|הזכירה}} אותך ב{{GRAMMAR:תחילית|$2}}',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|הזכיר|הזכירה}} אותך ברשומה {{GENDER:$1|שלו|שלה}} בנושא "$2" בדף "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|ערך|ערכה}} את הרשומה שלך',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|ערך|ערכה}} את הרשומה שלך בנושא $2 בדף "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 {{PLURAL:$5|ועוד אדם אחד|ועוד $4 אנשים}} ערכו רשומה בנושא $2 בדף "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|שינה|שינתה}} את השם של נושא שלך',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|שינה|שינתה}} את השם של הנושא שלך "$2" אל "$3" בדף "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|יצר|יצרה}} נושא חדש בדף $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|יצר|יצרה}} נושא חדש עם הכותרת "$2" ב{{GRAMMAR:תחלילית|$3}}',
	'echo-category-title-flow-discussion' => 'זרימה',
	'echo-pref-tooltip-flow-discussion' => 'להודיע לי כשיש פעולות שקשורות אליי בזרימה.',
	'flow-link-post' => 'רשומה',
	'flow-link-topic' => 'נושא',
	'flow-link-history' => 'היסטוריה',
	'flow-moderation-reason-placeholder' => 'נא להזין כאן את הסיבה שלך',
	'flow-moderation-title-suppress-post' => 'להעלים את הרשומה?',
	'flow-moderation-title-delete-post' => 'למחוק את הרשומה?',
	'flow-moderation-title-hide-post' => 'להסתיר את הרשומה?',
	'flow-moderation-title-restore-post' => 'לשחזר את הרשומה?',
	'flow-moderation-intro-suppress-post' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה מעלים|את מעלימה}} את הרשומה הזאת.',
	'flow-moderation-intro-delete-post' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה מוחק|את מוחקת}} את הרשומה הזאת.', # Fuzzy
	'flow-moderation-intro-hide-post' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה מסתיר|את מסתירה}} את הרשומה הזאת.', # Fuzzy
	'flow-moderation-intro-restore-post' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה משחזר|את משחזרת}} את הרשומה הזאת.', # Fuzzy
	'flow-moderation-confirm-suppress-post' => 'להעלים',
	'flow-moderation-confirm-delete-post' => 'למחוק',
	'flow-moderation-confirm-hide-post' => 'להסתיר',
	'flow-moderation-confirm-restore-post' => 'לשחזר',
	'flow-moderation-confirmation-suppress-post' => 'נא לשקול לתת ל{{GRAMMAR:תחילית|$1}} משוב על הרשומה הזאת.',
	'flow-moderation-confirmation-delete-post' => 'נא לשקול לתת ל{{GRAMMAR:תחילית|$1}} משוב על הרשומה הזאת.',
	'flow-moderation-confirmation-hide-post' => 'נא לשקול לתת ל{{GRAMMAR:תחילית|$1}} משוב על הרשומה הזאת.',
	'flow-moderation-confirmation-restore-post' => 'שחזרת בהצלחה את הרשומה הזאת.',
	'flow-moderation-title-suppress-topic' => 'להעלים את הנושא?',
	'flow-moderation-title-delete-topic' => 'למחוק את הנושא?',
	'flow-moderation-title-hide-topic' => 'להסתיר את הנושא?',
	'flow-moderation-title-restore-topic' => 'לשחזר את הנושא?',
	'flow-moderation-intro-suppress-topic' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה מעלים|את מעלימה}} את הנושא הזה.', # Fuzzy
	'flow-moderation-intro-delete-topic' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה מוחק|את מוחקת}} את הנושא הזה.', # Fuzzy
	'flow-moderation-intro-hide-topic' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה מסתיר|את מסתירה}} את הנושא הזה.', # Fuzzy
	'flow-moderation-intro-restore-topic' => '{{GENDER:$1|הסבר|הסבירי}} בבקשה למה {{GENDER:$1|אתה משחזר|את משחזרת}} את הנושא הזה.', # Fuzzy
	'flow-moderation-confirm-suppress-topic' => 'להעלים',
	'flow-moderation-confirm-delete-topic' => 'למחוק',
	'flow-moderation-confirm-hide-topic' => 'להסתיר',
	'flow-moderation-confirm-restore-topic' => 'לשחזר',
	'flow-moderation-confirmation-suppress-topic' => 'נא לשקול לתת ל{{GRAMMAR:תחילית|$1}} משוב על הרשומה הזאת.',
	'flow-moderation-confirmation-delete-topic' => 'נא לשקול לתת ל{{GRAMMAR:תחילית|$1}} משוב על הנושא הזה.',
	'flow-moderation-confirmation-hide-topic' => 'נא לשקול לתת ל{{GRAMMAR:תחילית|$1}} משוב על הנושא הזה.',
	'flow-moderation-confirmation-restore-topic' => 'שחזרת בהצלחה את הרשומה הזאת.',
	'flow-topic-permalink-warning' => 'הנושא הזה התחיל בדף [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'הנושא הזה התחיל ב[$2 לוח של $1]',
	'flow-revision-permalink-warning-post' => 'זהו קישור קבוע לגרסה פרטנית של הרשומה הזאת.
זוהי גרסה מ־$1.
באפשרותך לראות את [$5 השינויים מהגרסה הקודמת] או להציג גרסאות אחרות ב[$4 דף ההיסטוריה של הרשומה].',
	'flow-revision-permalink-warning-post-first' => 'זהו קישור קבוע לגרסה הראשונה של הרשומה.
אפשר להציג גרסאות מאוחרות יותר ב[$4 דף ההיסטוריה של הרשומה].',
	'flow-compare-revisions-revision-header' => 'גרסה מאת $2 מ{{GRAMMAR:תחילית|$1}}',
	'flow-compare-revisions-header-post' => 'הדף הזה מציג את השינויים בין שתי גרסאות של רשומה מאת $3 בנושא "[$5 $2]" בלוח [$4 $1].

באפשרותך לראות גרסאות אחרות של הרשומה הזאת ב[$6 דף ההיסטוריה].',
	'flow-topic-collapsed-one-line' => 'תצוגה מוקטנת',
	'flow-topic-collapsed-full' => 'תצוגה מקופלת',
	'flow-topic-complete' => 'תצוגה מלאה',
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
	'flow-hide-content' => '{{GENDER:$1|Celate}} per $1',
	'flow-delete-content' => '{{GENDER:$1|Delite}} per $1',
	'flow-suppress-content' => '{{GENDER:$1|Supprimite}} per $1',
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
	'flow-talk-link' => 'Parlar a {{GENDER:$1|$1}}',
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
	'flow-error-external' => 'Un error ha occurrite.<br /><small>Le message de error recipite es: $1</small>',
	'flow-error-edit-restricted' => 'Tu non es autorisate a modificar iste entrata.',
	'flow-error-external-multi' => 'Errores ha occurrite.<br />$1',
	'flow-error-missing-content' => 'Le message non ha contento. Contento es necessari pro salveguardar un message.',
	'flow-error-missing-title' => 'Le topico non ha titulo. Le titulo es necessari pro salveguardar un nove topico.', # Fuzzy
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
	'flow-rev-message-edit-post' => '[[User:$1|$1]] {{GENDER:$1|modificava}} un [$2 commento]', # Fuzzy
	'flow-rev-message-reply' => '[[User:$1|$1]] {{GENDER:$1|addeva}} un [$2 commento].', # Fuzzy
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|commento|commentos}}</strong> ha essite addite.',
	'flow-rev-message-new-post' => '[[User:$1|$1]] {{GENDER:$1|creava}} le topico [$2 $3].', # Fuzzy
	'flow-rev-message-edit-title' => '[[User:$1|$1]] {{GENDER:$1|cambiava}} le titulo del topico de $4 in [$2 $3].', # Fuzzy
	'flow-rev-message-create-header' => '[[User:$1|$1]] {{GENDER:$1|creava}} le titulo del tabuliero.', # Fuzzy
	'flow-rev-message-edit-header' => '[[User:$1|$1]] {{GENDER:$1|modificava}} le titulo del tabuliero.', # Fuzzy
	'flow-rev-message-hid-post' => '[[User:$1|$1]] {{GENDER:$1|celava}} un [$3 commento].', # Fuzzy
	'flow-rev-message-deleted-post' => '[[User:$1|$1]] {{GENDER:$1|deleva}} un [$3 commento].', # Fuzzy
	'flow-rev-message-suppressed-post' => '[[User:$1|$1]] {{GENDER:$1|supprimeva}} un [$3 commento].', # Fuzzy
	'flow-rev-message-restored-post' => '[[User:$1|$1]] {{GENDER:$1|restaurava}} un [$3 commento].', # Fuzzy
	'flow-rev-message-hid-topic' => '[[User:$1|$1]] {{GENDER:$1|celava}} le [$3 topico].', # Fuzzy
	'flow-rev-message-deleted-topic' => '[[User:$1|$1]] {{GENDER:$1|deleva}} le [$3 topico].', # Fuzzy
	'flow-rev-message-suppressed-topic' => '[[User:$1|$1]] {{GENDER:$1|supprimeva}} le [$3 topico].', # Fuzzy
	'flow-rev-message-restored-topic' => '[[User:$1|$1]] {{GENDER:$1|restaurava}} le [$3 topico].', # Fuzzy
	'flow-topic-history' => 'Historia de topicos', # Fuzzy
	'flow-comment-restored' => 'Commento restaurate',
	'flow-comment-deleted' => 'Commento delite',
	'flow-comment-hidden' => 'Commento celate',
	'flow-comment-moderated' => 'Commento moderate',
	'flow-paging-rev' => 'Topicos plus recente',
	'flow-paging-fwd' => 'Topicos plus vetule',
	'flow-last-modified' => 'Ultime modification circa $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|respondeva}} a tu [$5 message] in $2 super [[$3|$4]].',
	'flow-notification-reply-bundle' => '$1 e $5 {{PLURAL:$6|altere|alteres}} {{GENDER:$1|respondeva}} a tu [$4 message] in $2 sur "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|modificava}} tu [$5 message] in $2 super [[$3|$4]].', # Fuzzy
	'flow-notification-newtopic' => '$1 {{GENDER:$1|creava}} un [$5 nove topico] super [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|cambiava}} le titulo de [$2 $3] a "$4" super [[$5|$6]].',
	'flow-notification-link-text-view-post' => 'Vider message',
	'flow-notification-link-text-view-board' => 'Vider tabuliero',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|respondeva}} a tu message',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|respondeva}} a tu message in $2 sur "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e $4 {{PLURAL:$5|altere|alteres}} {{GENDER:$1|respondeva}} a tu message in $2 sur "$3"',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Discussion|Discussiones}}', # Fuzzy
	'echo-pref-tooltip-flow-discussion' => 'Notificar me quando actiones concernente me occurre in le tabuliero de discussion.', # Fuzzy
);

/** Italian (italiano)
 * @author Amire80
 * @author Beta16
 * @author Gianfranco
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
	'flow-post-moderated-toggle-show' => '[Mostra]',
	'flow-post-moderated-toggle-hide' => '[Nascondi]',
	'flow-hide-content' => '{{GENDER:$1|Nascosto}} da $1',
	'flow-delete-content' => '{{GENDER:$1|Cancellato}} da $1',
	'flow-suppress-content' => '{{GENDER:$1|Soppresso}} da $1',
	'flow-suppress-usertext' => '<em>Nome utente soppresso</em>',
	'flow-post-actions' => 'Azioni',
	'flow-topic-actions' => 'Azioni',
	'flow-cancel' => 'Annulla',
	'flow-preview' => 'Anteprima',
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
	'flow-talk-link' => 'Scrivi a {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Invia modifiche',
	'flow-post-edited' => 'Messaggio {{GENDER:$1|modificato}} da $1 $2',
	'flow-post-action-view' => 'Link permanente',
	'flow-post-action-post-history' => 'Cronologia del messaggio',
	'flow-post-action-suppress-post' => 'Sopprimi',
	'flow-post-action-delete-post' => 'Cancella',
	'flow-post-action-hide-post' => 'Nascondi',
	'flow-post-action-edit-post' => 'Modifica messaggio',
	'flow-post-action-edit' => 'Modifica',
	'flow-post-action-restore-post' => 'Ripristina messaggio',
	'flow-topic-action-view' => 'Link permanente',
	'flow-topic-action-watchlist' => 'Osservati speciali',
	'flow-topic-action-edit-title' => 'Modifica titolo',
	'flow-topic-action-history' => 'Cronologia della discussione',
	'flow-topic-action-hide-topic' => 'Nascondi discussione',
	'flow-topic-action-delete-topic' => 'Cancella discussione',
	'flow-topic-action-suppress-topic' => 'Sopprimi discussione',
	'flow-topic-action-restore-topic' => 'Ripristina discussione',
	'flow-error-http' => 'Si è verificato un errore durante la comunicazione con il server.',
	'flow-error-other' => 'Si è verificato un errore imprevisto.',
	'flow-error-external' => 'Si è verificato un errore.<br /><small>Il messaggio di errore ricevuto è: $1</small>',
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
	'flow-error-prev-revision-mismatch' => 'La modifica non può essere salvata: una modifica più recente è già stata inviata.',
	'flow-error-prev-revision-does-not-exist' => 'Impossibile trovare la versione precedente.',
	'flow-edit-header-submit' => 'Salva intestazione',
	'flow-edit-title-submit' => 'Cambia titolo',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|ha modificato}} un [$3 commento].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|ha aggiunto}} un [$3 commento].',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|commento|commenti}}</strong> {{PLURAL:$1|è stato aggiunto|sono stati aggiunti}}.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|ha creato}} la discussione [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|ha modificato}} il titolo della discussione in [$3 $4] da $5.',
	'flow-rev-message-create-header' => "$1 {{GENDER:$2|ha creato}} l'intestazione della bacheca.",
	'flow-rev-message-edit-header' => "$1 {{GENDER:$2|ha modificato}} l'intestazione della scheda.",
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|ha nascosto}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|ha cancellato}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|ha soppresso}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|ha ripristinato}} un [$4 commento] (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|ha nascosto}} la [$4 discussione] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|ha cancellato}} la [$4 discussione] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|ha soppresso}} la [$4 discussione] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|ha ripristinato}} la [$4 discussione] (<em>$5</em>).',
	'flow-board-history' => 'Cronologia di "$1"',
	'flow-topic-history' => 'Cronologia della discussione "$1"',
	'flow-post-history' => 'Cronologia del commento di {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Ultime 4 ore',
	'flow-history-day' => 'Oggi',
	'flow-history-week' => 'Ultima settimana',
	'flow-history-pages-topic' => 'Apparso sulla [$1 bacheca "$2"]',
	'flow-history-pages-post' => 'Apparso su [$1  $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 ha iniziato questa discussione|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} e {{PLURAL:$2|un altro|altri}}|0=Nessuno ha partecipato ancora|2={{GENDER:$3|$3}} e {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} e {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Commento ($1)|Commenti ($1)|0=Sii il primo a commentare!}}', # Fuzzy
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Sii il primo a commentare!|$1 {{PLURAL:$1|commento|commenti}} di $2 {{PLURAL:$2|utente|utenti}}}}',
	'flow-comment-restored' => 'Commento ripristinato',
	'flow-comment-deleted' => 'Commento cancellato',
	'flow-comment-hidden' => 'Commento nascosto',
	'flow-comment-moderated' => 'Commento moderato',
	'flow-paging-rev' => 'Discussioni più recenti',
	'flow-paging-fwd' => 'Vecchie discussioni',
	'flow-last-modified' => 'Ultima modifica $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|ha risposto}} al tuo [$5 messaggio] in $2 su "$4".',
	'flow-notification-reply-bundle' => '$1 e {{PLURAL:$6|un altro|altri $5}} utenti {{GENDER:$1|hanno risposto}} al tuo [$4 messaggio] in $2 su "$3".',
	'flow-notification-edit' => '$1 ha {{GENDER:$1|modificato}} un [$5 messaggio] in $2 su [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 e {{PLURAL:$6|un altro|altri $5}} utenti {{GENDER:$1|hanno modificato}} un [$4 messaggio] in $2 su "$3".',
	'flow-notification-newtopic' => '$1 ha {{GENDER:$1|creato}} una [$5 nuova discussione] su [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 ha {{GENDER:$1|cambiato}} il titolo di [$2 $3] in "$4" su [[$5|$6]]',
	'flow-notification-mention' => '$1 ti {{GENDER:$1|ha menzionato}} nel suo [$2 messaggio] in "$3" su "$4".',
	'flow-notification-link-text-view-post' => 'Vedi messaggio',
	'flow-notification-link-text-view-board' => 'Vedi bacheca',
	'flow-notification-link-text-view-topic' => 'Vedi discussione',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ha risposto}} al tuo messaggio',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|ha risposto}} al tuo messaggio in $2 su "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e {{PLURAL:$5|un altro|altri $4}} {{GENDER:$1|hanno risposto}} al tuo messaggio in $2 su "$3"',
	'flow-notification-mention-email-subject' => '$1 ti {{GENDER:$1|ha menzionato}} su $2',
	'flow-notification-mention-email-batch-body' => '$1 ti {{GENDER:$1|ha menzionato}} nel suo messaggio in "$2" su "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|ha modificato}} il tuo messaggio',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|ha modificato}} il tuo messaggio in $2 su "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 e {{PLURAL:$5|un altro|altri $4}} utenti {{GENDER:$1|hanno modificato}} un messaggio in $2 su "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|ha rinominato}} la tua discussione',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|ha rinominato}} la discussione "$2" in "$3" su "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|ha creato}} una nuova discussione su $2',
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
	'flow-moderation-intro-suppress-post' => 'Spiega perché stai sopprimendo questo messaggio.',
	'flow-moderation-intro-delete-post' => 'Spiega perché stai cancellando questo messaggio.',
	'flow-moderation-intro-hide-post' => 'Spiega perché stai nascondendo questo messaggio.',
	'flow-moderation-intro-restore-post' => 'Spiega perché stai ripristinando questo messaggio.',
	'flow-moderation-confirm-suppress-post' => 'Sopprimi',
	'flow-moderation-confirm-delete-post' => 'Cancella',
	'flow-moderation-confirm-hide-post' => 'Nascondi',
	'flow-moderation-confirm-restore-post' => 'Ripristina',
	'flow-moderation-confirmation-suppress-post' => '{{GENDER:$1|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-delete-post' => '{{GENDER:$1|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-hide-post' => '{{GENDER:$1|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-restore-post' => 'Hai ripristinato con successo questo messaggio.',
	'flow-moderation-title-suppress-topic' => 'Sopprimere la discussione?',
	'flow-moderation-title-delete-topic' => 'Cancellare la discussione?',
	'flow-moderation-title-hide-topic' => 'Nascondere la discussione?',
	'flow-moderation-title-restore-topic' => 'Ripristinare la discussione?',
	'flow-moderation-intro-suppress-topic' => 'Spiega perché stai sopprimendo questa discussione.',
	'flow-moderation-intro-delete-topic' => 'Spiega perché stai cancellando questa discussione.',
	'flow-moderation-intro-hide-topic' => 'Spiega perché stai nascondendo questa discussione.',
	'flow-moderation-intro-restore-topic' => 'Spiega perché stai ripristinando questa discussione.',
	'flow-moderation-confirm-suppress-topic' => 'Sopprimi',
	'flow-moderation-confirm-delete-topic' => 'Cancella',
	'flow-moderation-confirm-hide-topic' => 'Nascondi',
	'flow-moderation-confirm-restore-topic' => 'Ripristina',
	'flow-moderation-confirmation-suppress-topic' => '{{GENDER:$1|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-delete-topic' => '{{GENDER:$1|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-hide-topic' => '{{GENDER:$1|Scrivi}} a $1 riguardo a questo messaggio.',
	'flow-moderation-confirmation-restore-topic' => 'Hai ripristinato con successo questa discussione.',
	'flow-topic-permalink-warning' => 'La discussione è iniziata su [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'La discussione è iniziata sulla [$2 bacheca di {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Questo è un collegamento permanente ad una singola versione di questo messaggio.
Questa versione è del $1.
Puoi vedere le [$5 differenze dalla versione precedente] o le altre versioni nella [$4 cronologia della pagina].',
	'flow-revision-permalink-warning-post-first' => 'Questo è un collegamento permanente alla prima versione di questo messaggio.
Puoi vedere le versioni successive nella [$4 cronologia della pagina].',
	'flow-compare-revisions-revision-header' => 'Versione di {{GENDER:$2|$2}} del $1',
	'flow-compare-revisions-header-post' => 'Questa pagina mostra le modifiche tra due versioni del messaggio di $3, nella discussione "[$5 $2]" su [$4 $1].
Puoi vedere le altre versioni nella [$6 cronologia della pagina].',
	'flow-topic-collapsed-one-line' => 'Vista piccola',
	'flow-topic-collapsed-full' => 'Vista compatta',
	'flow-topic-complete' => 'Vista completa',
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
	'logentry-suppress-flow-restore-post' => '$1 が [[$3]] の[$4 投稿]を{{GENDER:$2|削除}}',
	'logentry-delete-flow-delete-topic' => '$1 が [[$3]] の[$4 話題]を{{GENDER:$2|削除}}',
	'logentry-delete-flow-restore-topic' => '$1 が [[$3]] の[$4 話題]を{{GENDER:$2|復元}}',
	'logentry-suppress-flow-restore-topic' => '$1 が [[$3]] の[$4 話題]を{{GENDER:$2|削除}}',
	'flow-edit-header-link' => 'ヘッダーを編集',
	'flow-header-empty' => '現在、このトークページにはヘッダーがありません。',
	'flow-post-moderated-toggle-show' => '[表示]',
	'flow-post-moderated-toggle-hide' => '[非表示]',
	'flow-hide-content' => '$1 が{{GENDER:$1|非表示にしました}}',
	'flow-delete-content' => '$1 が{{GENDER:$1|削除しました}}',
	'flow-post-actions' => '操作',
	'flow-topic-actions' => '操作',
	'flow-cancel' => 'キャンセル',
	'flow-preview' => 'プレビュー',
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
	'flow-talk-link' => '{{GENDER:$1|$1}} のトーク',
	'flow-edit-post-submit' => '変更を保存',
	'flow-post-edited' => '$1 が $2 に{{GENDER:$1|編集した}}投稿',
	'flow-post-action-view' => '固定リンク',
	'flow-post-action-post-history' => '投稿履歴',
	'flow-post-action-delete-post' => '削除',
	'flow-post-action-hide-post' => '非表示にする',
	'flow-post-action-edit-post' => '投稿を編集',
	'flow-post-action-edit' => '編集',
	'flow-post-action-restore-post' => '投稿を復元',
	'flow-topic-action-view' => '固定リンク',
	'flow-topic-action-watchlist' => 'ウォッチリスト',
	'flow-topic-action-edit-title' => 'タイトルを編集',
	'flow-topic-action-history' => '話題の履歴',
	'flow-topic-action-hide-topic' => '話題を非表示にする',
	'flow-topic-action-delete-topic' => '話題を削除',
	'flow-topic-action-restore-topic' => '話題を復元',
	'flow-error-http' => 'サーバーとの通信中にエラーが発生しました。',
	'flow-error-other' => '予期しないエラーが発生しました。',
	'flow-error-external' => 'エラーが発生しました。<br /><small>受信したエラーメッセージ: $1</small>',
	'flow-error-edit-restricted' => 'あなたはこの投稿を編集を許可されていません。',
	'flow-error-external-multi' => '複数のエラーが発生しました。<br /> $1',
	'flow-error-missing-content' => '投稿の本文がありません。投稿を保存するには本文が必要です。',
	'flow-error-missing-title' => '話題のタイトルがありません。話題を保存するにはタイトルが必要です。',
	'flow-error-parsoid-failure' => 'Parsoid でエラーが発生したため、本文を構文解析できませんでした。',
	'flow-error-missing-replyto' => '「返信先」のパラメーターを指定していません。「返信」するには、このパラメーターが必要です。',
	'flow-error-invalid-replyto' => '「返信先」のパラメーターが無効です。指定した投稿が見つかりませんでした。',
	'flow-error-delete-failure' => 'この項目を削除できませんでした。',
	'flow-error-hide-failure' => 'この項目を非表示にできませんでした。',
	'flow-error-missing-postId' => '「投稿 ID」のパラメーターを指定していません。投稿を操作するには、このパラメーターが必要です。',
	'flow-error-invalid-postId' => '「投稿 ID」のパラメーターが無効です。指定した投稿 ($1) が見つかりませんでした。',
	'flow-error-restore-failure' => 'この項目を復元できませんでした。',
	'flow-error-invalid-moderation-state' => 'moderationState に指定した値は無効です。',
	'flow-error-not-allowed' => 'この操作を実行するのに十分な権限がありません',
	'flow-error-title-too-long' => '話題のタイトルは $1 {{PLURAL:$1|バイト}}までに制限されています。',
	'flow-error-no-existing-workflow' => 'このワークフローはまだ存在しません。',
	'flow-error-not-a-post' => '話題のタイトルは投稿としては保存できません。',
	'flow-error-missing-header-content' => 'ヘッダーの本文がありません。ヘッダーを保存するには本文が必要です。',
	'flow-error-missing-prev-revision-identifier' => '以前の版の ID がありません。',
	'flow-error-prev-revision-does-not-exist' => '過去の版が見つかりませんでした。',
	'flow-edit-header-submit' => 'ヘッダーを保存',
	'flow-edit-title-submit' => 'タイトルを変更',
	'flow-rev-message-edit-post' => '$1 が[$3 コメント]を{{GENDER:$2|編集}}',
	'flow-rev-message-reply' => '$1 が[$3 コメント]を{{GENDER:$2|追加}}',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|件のコメント}}</strong>が追加{{PLURAL:$1|されました}}。',
	'flow-rev-message-new-post' => '$1 が話題 [$3 $4] を{{GENDER:$2|作成}}',
	'flow-rev-message-edit-title' => '$1 が話題のタイトルを $5 から [$3 $4] に{{GENDER:$2|変更}}',
	'flow-rev-message-create-header' => '$1 が掲示板のヘッダーを{{GENDER:$2|作成}}',
	'flow-rev-message-edit-header' => '$1 が掲示板のヘッダーを{{GENDER:$2|編集}}',
	'flow-rev-message-hid-post' => '$1 が[$4 コメント]を{{GENDER:$2|非表示化}} (<em>$5</em>)',
	'flow-rev-message-deleted-post' => '$1 が[$4 コメント]を{{GENDER:$2|削除}} (<em>$5</em>)',
	'flow-rev-message-restored-post' => '$1 が[$4 コメント]を{{GENDER:$2|復元}} (<em>$5</em>)',
	'flow-rev-message-hid-topic' => '$1 が[$4 話題]を{{GENDER:$2|非表示化}} (<em>$5</em>)',
	'flow-rev-message-deleted-topic' => '$1 が[$4 話題]を{{GENDER:$2|削除}} (<em>$5</em>)',
	'flow-rev-message-restored-topic' => '$1 が[$4 話題]を{{GENDER:$2|復元}} (<em>$5</em>)',
	'flow-board-history' => '「$1」の履歴',
	'flow-topic-history' => '話題「$1」の履歴',
	'flow-post-history' => '「{{GENDER:$2|$2}} によるコメント」投稿履歴',
	'flow-history-last4' => '過去 4 時間',
	'flow-history-day' => '今日',
	'flow-history-week' => '過去 1 週間',
	'flow-topic-participants' => '{{PLURAL:$1|$3 がこの話題を開始|{{GENDER:$3|$3}}、{{GENDER:$4|$4}}、{{GENDER:$5|$5}} と他 $2 {{PLURAL:$2|人}}|0=まだ誰も参加していません|2={{GENDER:$3|$3}} と {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}、{{GENDER:$4|$4}}、{{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|コメント ($1)|0=最初のコメントを書きましょう!}}',
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=最初のコメントを書きましょう!|$2 {{PLURAL:$2|人の利用者}}による $1 {{PLURAL:$1|件のコメント}}}}',
	'flow-comment-restored' => 'コメントを復元',
	'flow-comment-deleted' => 'コメントを削除',
	'flow-comment-hidden' => 'コメントを非表示',
	'flow-paging-rev' => '最近の話題',
	'flow-paging-fwd' => '古い話題',
	'flow-last-modified' => '最終更新 $1',
	'flow-notification-reply' => '$1 が「$4」の $2 でのあなたの[$5 投稿]に{{GENDER:$1|返信しました}}。',
	'flow-notification-reply-bundle' => '$1 と他 $5 {{PLURAL:$6|人}}が「$3」の $2 でのあなたの[$4 投稿]に{{GENDER:$1|返信しました}}。',
	'flow-notification-edit' => '$1 が [[$3|$4]] の $2 での[$5 投稿]を{{GENDER:$1|編集しました}}。',
	'flow-notification-edit-bundle' => '$1 と他 $5 {{PLURAL:$6|人}}が「$3」の $2 での[$4 投稿]を{{GENDER:$1|編集しました}}。',
	'flow-notification-newtopic' => '$1 が [[$2|$3]] で[$5 新しい話題]を{{GENDER:$1|作成しました}}: $4',
	'flow-notification-rename' => '$1 が [[$5|$6]] で [$2 $3] のページ名を「$4」に{{GENDER:$1|変更しました}}。',
	'flow-notification-mention' => '$1 が「$4」の「$3」での{{GENDER:$1|自身の}}[$2 投稿]であなたに{{GENDER:$1|言及しました}}。',
	'flow-notification-link-text-view-post' => '投稿を閲覧',
	'flow-notification-link-text-view-board' => '掲示板を閲覧',
	'flow-notification-link-text-view-topic' => '話題を閲覧',
	'flow-notification-reply-email-subject' => '$1 があなたの投稿に{{GENDER:$1|返信しました}}',
	'flow-notification-reply-email-batch-body' => '$1 が「$3」の $2 でのあなたの投稿に{{GENDER:$1|返信しました}}',
	'flow-notification-reply-email-batch-bundle-body' => '$1 と他 $4 {{PLURAL:$5|人}}が「$3」の $2 でのあなたの投稿に{{PLURAL:$1|返信しました}}',
	'flow-notification-mention-email-subject' => '$1 が $2 であなたに{{GENDER:$1|言及しました}}',
	'flow-notification-mention-email-batch-body' => '$1 が「$3」の「$2」での{{GENDER:$1|自身の}}投稿であなたに{{GENDER:$1|言及しました}}',
	'flow-notification-edit-email-subject' => '$1 があなたの投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-edit-email-batch-body' => '$1 が「$3」の $2 でのあなたの投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-edit-email-batch-bundle-body' => '$1 と他 $4 {{PLURAL:$5|人}}が「$3」の $2 での投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-rename-email-subject' => '$1 があなたの話題の{{GENDER:$1|名前を変更しました}}',
	'flow-notification-rename-email-batch-body' => '$1 が「$4」のあなたの話題「$2」の名前を「$3」に{{GENDER:$1|変更しました}}',
	'flow-notification-newtopic-email-subject' => '$1 が $2 に新しい話題を{{GENDER:$1|作成しました}}',
	'flow-notification-newtopic-email-batch-body' => '$1 が $3 で新しい話題「$2」を{{GENDER:$1|作成しました}}',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Flow で私に関連する操作がなされたときに通知する。',
	'flow-link-post' => '投稿',
	'flow-link-topic' => '話題',
	'flow-link-history' => '履歴',
	'flow-moderation-reason-placeholder' => '理由をここに入力',
	'flow-moderation-title-delete-post' => '投稿を削除しますか?',
	'flow-moderation-title-hide-post' => '投稿を非表示にしますか?',
	'flow-moderation-title-restore-post' => '投稿を復元しますか?',
	'flow-moderation-intro-delete-post' => 'この投稿を削除する理由を説明してください。',
	'flow-moderation-intro-hide-post' => 'この投稿を非表示にする理由を説明してください。',
	'flow-moderation-intro-restore-post' => 'この投稿を復元する理由を説明してください。',
	'flow-moderation-confirm-delete-post' => '削除',
	'flow-moderation-confirm-hide-post' => '非表示にする',
	'flow-moderation-confirm-restore-post' => '復元',
	'flow-moderation-confirmation-restore-post' => 'この投稿を復元しました。',
	'flow-moderation-title-delete-topic' => '話題を削除しますか?',
	'flow-moderation-title-hide-topic' => '話題を非表示にしますか?',
	'flow-moderation-title-restore-topic' => '話題を復元しますか?',
	'flow-moderation-intro-delete-topic' => 'この話題を削除する理由を説明してください。',
	'flow-moderation-intro-hide-topic' => 'この話題を非表示にする理由を説明してください。',
	'flow-moderation-intro-restore-topic' => 'この話題を復元する理由を説明してください。',
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
	'flow-compare-revisions-header-post' => 'このページでは、[$4 $1] の話題「[$5 $2]」での $3 の投稿の 2 つの版の差分を表示しています。
この投稿の[$6 履歴ページ]でその他の版を閲覧できます。',
	'flow-topic-collapsed-one-line' => '縮小表示',
	'flow-topic-collapsed-full' => '折りたたみ表示',
	'flow-topic-complete' => '全体表示',
);

/** Korean (한국어)
 * @author Daisy2002
 * @author Hym411
 * @author 아라
 */
$messages['ko'] = array(
	'flow-desc' => '워크플로우 관리 시스템',
	'flow-edit-header-link' => '머리말 고치기',
	'flow-header-empty' => '이 토론 문서에는 머릿말이 없습니다.',
	'flow-post-moderated-toggle-show' => '[보기]',
	'flow-post-moderated-toggle-hide' => '[숨김]',
	'flow-cancel' => '취소',
	'flow-newtopic-title-placeholder' => '새 주제',
	'flow-newtopic-save' => '새 항목',
	'flow-newtopic-start-placeholder' => '새 주제',
	'flow-reply-topic-placeholder' => '$1의 "$2"에 대한 의견',
	'flow-reply-submit' => '{{GENDER:$1|답변}}',
	'flow-talk-link' => '$1에게 말하기',
	'flow-post-action-edit' => '편집',
	'flow-topic-action-view' => '고유링크',
	'flow-topic-action-watchlist' => '주시문서 목록',
	'flow-error-http' => '서버 접속 중에 에러가 발생했습니다. 편집이 저장이 되지 않았습니다.', # Fuzzy
	'flow-error-other' => '예기치 않은 에러가 발생했습니다. 편집이 저장이 되지 않았습니다.', # Fuzzy
	'flow-error-external' => '포스트를 저장하는 중에 에러가 발생했습니다.편집이 저장이 되지 않았습니다.<br /><small>에러 메시지: $1</small>', # Fuzzy
	'flow-error-edit-restricted' => '이 문서의 편집을 허용하지 않습니다.',
	'flow-error-external-multi' => '에러가 발생해 편집 저장에 실패하였습니다.<br />$1', # Fuzzy
	'flow-rev-message-edit-title' => '주제 제목이 편집되었습니다', # Fuzzy
	'flow-rev-message-edit-header' => '수정된 머리말', # Fuzzy
	'flow-rev-message-hid-post' => '내용 숨겨짐', # Fuzzy
	'flow-rev-message-deleted-post' => '삭제된 게시글', # Fuzzy
	'flow-rev-message-restored-post' => '게시글 숨김 해제', # Fuzzy
	'flow-topic-comments' => '{{PLURAL:$1|0=첫 댓글을 달아 보세요!|댓글 ($1개)}}', # Fuzzy
	'flow-notification-link-text-view-post' => '게시물 보기',
	'flow-notification-reply-email-subject' => '$1이 당신의 글에 덧글을 달았습니다.',
	'flow-notification-rename-email-subject' => '$1 이 당신의 주제를 바꾸었습니다.',
	'flow-link-topic' => '주제',
	'flow-link-history' => '역사',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 * @author Soued031
 */
$messages['lb'] = array(
	'flow-desc' => 'Workflow-Management-System',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|huet}} eng [$4 Bemierkung] op [[$3]] geläscht', # Fuzzy
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|huet}} e(n) [$4 Thema] op [[$3]] geläscht',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|huet}} e(n) [$4 Thema] op [[$3]] restauréiert',
	'flow-edit-header-link' => 'Iwwerschrëft änneren',
	'flow-header-empty' => 'Dës Diskussiounssäit huet elo keng Iwwerschrëft',
	'flow-post-moderated-toggle-show' => '[Weisen]',
	'flow-post-moderated-toggle-hide' => '[Verstoppen]',
	'flow-hide-content' => '{{GENDER:$1|Verstoppt}} vum $1',
	'flow-delete-content' => '{{GENDER:$1|Geläscht}} vum $1',
	'flow-post-actions' => 'Aktiounen',
	'flow-topic-actions' => 'Aktiounen',
	'flow-cancel' => 'Ofbriechen',
	'flow-preview' => 'Kucken ouni ze späicheren',
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
	'flow-talk-link' => 'Mam {{GENDER:$1|$1}} schwëtzen',
	'flow-edit-post-submit' => 'Ännerunge späicheren',
	'flow-post-action-view' => 'Permanentlink',
	'flow-post-action-delete-post' => 'Läschen',
	'flow-post-action-hide-post' => 'Verstoppen',
	'flow-post-action-edit' => 'Änneren',
	'flow-topic-action-watchlist' => 'Iwwerwaachungslëscht',
	'flow-topic-action-edit-title' => 'Titel änneren',
	'flow-topic-action-hide-topic' => 'Thema verstoppen',
	'flow-topic-action-delete-topic' => 'Thema läschen',
	'flow-topic-action-restore-topic' => 'Thema restauréieren',
	'flow-error-other' => 'En onerwaarte Feeler ass geschitt.',
	'flow-error-external' => 'Et ass e Feeler geschitt.<br /><small>De Feelermessage war:$1</ small>',
	'flow-error-external-multi' => 'Et si Feeler geschitt.<br />$1',
	'flow-error-missing-title' => "D'Thema huet keen Titel. Den Titel ass obligatoresch fir een Thema ze späicheren.",
	'flow-error-delete-failure' => "D'Läsche vun dësem Element huet net funktionéiert.",
	'flow-error-hide-failure' => 'Verstoppe vun dësem Element huet net funktionéiert.',
	'flow-error-restore-failure' => "D'Restauréiere vun dësem Element huet net funktionéiert.",
	'flow-error-not-allowed' => 'Net genuch Rechter fir dës Aktioun ze maachen',
	'flow-error-missing-header-content' => "D'Iwwerschrëft huet keen Inhalt. Den Inhalt ass obligatoresch fir eng Iwwerschrëft ze späicheren.",
	'flow-error-prev-revision-mismatch' => 'Dës Ännerung konnt net gespäichert ginn: méi eng rezent Ännerung gouf scho gespäichert.',
	'flow-error-prev-revision-does-not-exist' => 'Déi vireg Versioun konnt net fonnt ginn.',
	'flow-edit-header-submit' => 'Iwwerschrëft späicheren',
	'flow-edit-title-submit' => 'Titel änneren',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|huet}} eng [$3 Bemierkung] geännert.',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|huet}} eng [$3 Bemierkung] derbäigesat.',
	'flow-rev-message-reply-bundle' => '<strong>{{PLURAL:$1|Eng Bemierkung gouf|$1 Bemierkunge goufen}} derbäigesat</strong>.',
	'flow-rev-message-new-post' => "$1 {{GENDER:$2|huet}} d'Thema [$3 $4] ugeluecht.",
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|huet}} eng [$4 Bemierkung] (<em>$5</em>) verstoppt.',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|huet}} eng [$4 Bemierkung] (<em>$5</em>) geläscht.',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|huet}} eng [$4 Bemierkung] (<em>$5</em>) restauréiert.',
	'flow-rev-message-deleted-topic' => "$1 {{GENDER:$2|huet}} d'[Thema $4] (<em>$5</em>) geläscht.",
	'flow-rev-message-restored-topic' => "$1 {{GENDER:$2|huet}} d'[Thema $4] (<em>$5</em>) restauréiert.",
	'flow-board-history' => 'Versioune vun "$1"',
	'flow-topic-history' => 'Versioune vum Thema "$1"',
	'flow-history-last4' => 'Lescht 4 Stonnen',
	'flow-history-day' => 'Haut',
	'flow-history-week' => 'Lescht Woch',
	'flow-topic-comments' => '{{PLURAL:$1|Bemierkung ($1)|Bemierkunge(n) ($1)|0=Sidd deen éischten deen enge Bemierkung mécht!}}', # Fuzzy
	'flow-comment-restored' => 'Restauréiert Bemierkung',
	'flow-comment-deleted' => 'Geläscht Bemierkung',
	'flow-comment-hidden' => 'Verstoppte Bemierkung',
	'flow-comment-moderated' => 'Moderéiert Bemierkung',
	'flow-paging-rev' => 'Méi rezent Themen',
	'flow-paging-fwd' => 'Méi al Themen',
	'flow-last-modified' => "Fir d'lescht geännert ongeféier $1",
	'flow-notification-newtopic' => '$1  {{GENDER:$1|huet}} een [$5 neit Thema] op [[$2|$3]]: $4 ugeluecht.',
	'flow-notification-rename' => '$1 {{GENDER:$1|huet}} den Titel vu(n) [$2 $3] op "$4" op [[$5|$6]] geännert.',
	'flow-notification-link-text-view-board' => 'Tableau weisen',
	'flow-notification-link-text-view-topic' => 'Thema weisen',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|huet}} een neit Thema iwwer $2 ugeluecht',
	'echo-category-title-flow-discussion' => 'Flow',
	'echo-pref-tooltip-flow-discussion' => 'Mech informéiere wann Aktiounen déi mech betreffen a geschéien.',
	'flow-link-topic' => 'Thema',
	'flow-link-history' => 'Versiounen',
	'flow-moderation-reason-placeholder' => 'Gitt Äre Grond hei an',
	'flow-moderation-confirm-delete-post' => 'Läschen',
	'flow-moderation-confirm-hide-post' => 'Verstoppen',
	'flow-moderation-confirm-restore-post' => 'Restauréieren',
	'flow-moderation-title-delete-topic' => 'Thema läschen?',
	'flow-moderation-title-hide-topic' => 'Thema verstoppen?',
	'flow-moderation-title-restore-topic' => 'Thema restauréieren?',
	'flow-moderation-intro-delete-topic' => 'Erklärt w.e.g. firwat datt Dir dëst Thema läscht.',
	'flow-moderation-intro-hide-topic' => 'Erklärt w.e.g. firwat datt Dir dëst Thema verstoppt.',
	'flow-moderation-intro-restore-topic' => 'Erklärt w.e.g. firwat datt Dir dëst Thema restauréiert.',
	'flow-moderation-confirm-delete-topic' => 'Läschen',
	'flow-moderation-confirm-hide-topic' => 'Verstoppen',
	'flow-moderation-confirm-restore-topic' => 'Restauréieren',
	'flow-moderation-confirmation-restore-topic' => 'Dir hutt dëst Thema restauréiert.',
	'flow-topic-permalink-warning' => 'Dëse Sujet gouf op [$2 $1] ugefaang',
	'flow-compare-revisions-revision-header' => 'Versioun vum {{GENDER:$2|$2}} vum $1',
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
	'flow-post-moderated-toggle-show' => '[Прикажи]',
	'flow-post-moderated-toggle-hide' => '[Скриј]',
	'flow-hide-content' => '{{GENDER:$1|Скриена}} од $1',
	'flow-delete-content' => '{{GENDER:$1|Избришана}} од $1',
	'flow-suppress-content' => '{{GENDER:$1|Притаена}} од $1',
	'flow-suppress-usertext' => '<em>Корисничкото име е притаено</em>',
	'flow-post-actions' => 'Дејства',
	'flow-topic-actions' => 'Дејства',
	'flow-cancel' => 'Откажи',
	'flow-preview' => 'Преглед',
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
	'flow-talk-link' => 'Разговарај со {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Спроведи измени',
	'flow-post-edited' => '$1 {{GENDER:$1|измени}} објава во $2',
	'flow-post-action-view' => 'Постојана врска',
	'flow-post-action-post-history' => 'Историја на пораки',
	'flow-post-action-suppress-post' => 'Притај',
	'flow-post-action-delete-post' => 'Избриши',
	'flow-post-action-hide-post' => 'Скриј',
	'flow-post-action-edit-post' => 'Уреди ја пораката',
	'flow-post-action-edit' => 'Измени',
	'flow-post-action-restore-post' => 'Поврати ја пораката',
	'flow-topic-action-view' => 'Постојана врска',
	'flow-topic-action-watchlist' => 'Набљудувања',
	'flow-topic-action-edit-title' => 'Уреди наслов',
	'flow-topic-action-history' => 'Историја на темата',
	'flow-topic-action-hide-topic' => 'Скриј тема',
	'flow-topic-action-delete-topic' => 'Избриши тема',
	'flow-topic-action-suppress-topic' => 'Притај тема',
	'flow-topic-action-restore-topic' => 'Поврати тема',
	'flow-error-http' => 'Се јави грешка при поврзувањето со опслужувачот.',
	'flow-error-other' => 'Се појави неочекувана грешка.',
	'flow-error-external' => 'Се појави грешка.<br /><small>Објаснувањето гласи: $1</small>',
	'flow-error-edit-restricted' => 'Не ви е дозволено да ја менувате објавата.',
	'flow-error-external-multi' => 'Наидов на грешки.<br />$1',
	'flow-error-missing-content' => 'Пораката нема содржина. За да се зачува, мора да има содржина.',
	'flow-error-missing-title' => 'Темата нема наслов. Се бара наслов за да може да се зачува темата.',
	'flow-error-parsoid-failure' => 'Не можам да ја парсирам содржината поради проблем со Parsoid.',
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
	'flow-error-prev-revision-mismatch' => 'Не можев да го зачувам уредувањето: веќе е поднесена понова ревизија.',
	'flow-error-prev-revision-does-not-exist' => 'Не можев да ја надам претходната ревизија.',
	'flow-edit-header-submit' => 'Зачувај заглавие',
	'flow-edit-title-submit' => 'Измени наслов',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|измени}} [$3 коментар].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|доидаде}} [$3 коментар].',
	'flow-rev-message-reply-bundle' => '{{PLURAL:$1|Додаден|Додадени}} <strong>{{PLURAL:$1|еден коментар|$1 коментари}}</strong>.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|ја создаде}} темата [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|го смени}} насловот на темата од $5 во [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|го создаде}} заглавието на таблата.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|го измени}} заглавието на таблата.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|скри}} [$4 коментар] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|избриша}} [$4 коментар] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|притаи}} [$4 коментар] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|поврати}} [$4 коментар] (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|ја скри}} [$4 темата] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|ја избриша}} [$4 темата] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|ја притаи}} [$4 темата] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|ја поврати}} [$4 темата] (<em>$5</em>).',
	'flow-board-history' => 'Историја на „$1“',
	'flow-topic-history' => 'Историја на темата „$1“',
	'flow-post-history' => 'Историја на објавите — Коментар од {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Последниве 4 часа',
	'flow-history-day' => 'Денес',
	'flow-history-week' => 'Минатата седмица',
	'flow-history-pages-topic' => 'Фигурира на [$1 таблата „$2“]',
	'flow-history-pages-post' => 'Фигурира на [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|Темава ја започна $3|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} и {{PLURAL:$2|уште еден|$2 други}}|0=Досега никој не учествувал|2={{GENDER:$3|$3}} и {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} и {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Коментар ($1)|Коментари ($1)|0=Бидете први со коментар!}}',
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Бидете први со коментар!|$1 {{PLURAL:$1|коментар|коментари}} од {{PLURAL:$2|еден корисник|$2 корисници}}}}',
	'flow-comment-restored' => 'Повратен коментар',
	'flow-comment-deleted' => 'Избришан коментар',
	'flow-comment-hidden' => 'Скриен коментар',
	'flow-comment-moderated' => 'Модериран коментар',
	'flow-paging-rev' => 'Најнови теми',
	'flow-paging-fwd' => 'Постари теми',
	'flow-last-modified' => 'Последна измена: $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|ви одговори}} на вашата [$5 порака] во $2 на [[$3|$4]].',
	'flow-notification-reply-bundle' => '$1 и $5 уште {{PLURAL:$6|еден друг|$5 други}} {{GENDER:$1|ви одговорија}} на вашата [$4 објава] во $2 на „$3“.',
	'flow-notification-edit' => '$1 {{GENDER:$1|ви ја измени}} измени [$5 порака] во $2 на [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 и $5 {{PLURAL:$6|уште еден друг|уште $5 други}} {{GENDER:$1|изменија}} [$4 објава] во $2 на „$3“.',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|создаде}} [$5 нова тема] во [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 го {{GENDER:$1|смени}} насловот на [$2 $3] во „$4“ на [[$5|$6]]',
	'flow-notification-mention' => '$1 ве спомна во {{GENDER:$1|неговата|нејзината|неговата}} [$2 објава] во „$3“ на „$4“',
	'flow-notification-link-text-view-post' => 'Погл. објавата',
	'flow-notification-link-text-view-board' => 'Погл. таблата',
	'flow-notification-link-text-view-topic' => 'Погл. темата',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ви одговори}} на објавата',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|ви одговори}} на вашата објава во $2 на „$3“',
	'flow-notification-reply-email-batch-bundle-body' => '$1 и уште {{PLURAL:$5|еден друг|$4 други}} {{GENDER:$1|ви одговорија}} на вашата објава во $2 на „$3“',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|ве спомна}} на $2',
	'flow-notification-mention-email-batch-body' => '$1 ве спомна во {{GENDER:$1|неговата|нејзината|неговата}} објава во „$2“ на „$3“',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|ја измени}} вашата објава',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|ја измени}} вашата објава во $2 на „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 и {{PLURAL:$5|уште еден друг|уште $4 други}} {{GENDER:$1|ја изменија}} вашата објава во $2 на „$3“',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|ја преименуваше}} вашата тема',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|ја преименуваше}} вашата тема „$2“ во „$3“ на „$4“',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|создаде}} нова тема на $2',
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
	'flow-moderation-intro-suppress-post' => 'Објаснете зошто ја притајувате објавава.',
	'flow-moderation-intro-delete-post' => 'Објаснете зошто ја бришење објавава.',
	'flow-moderation-intro-hide-post' => 'Објаснете зошто ја скривате објавава.',
	'flow-moderation-intro-restore-post' => 'Објаснете зошто ја повраќате објавава.',
	'flow-moderation-confirm-suppress-post' => 'Притај',
	'flow-moderation-confirm-delete-post' => 'Избриши',
	'flow-moderation-confirm-hide-post' => 'Скриј',
	'flow-moderation-confirm-restore-post' => 'Поврати',
	'flow-moderation-confirmation-suppress-post' => 'Ви препорачуваме на $1 да {{GENDER:$1|му|ѝ}} дадете образложение и/или совет за објавата.',
	'flow-moderation-confirmation-delete-post' => 'Ви препорачуваме на $1 да {{GENDER:$1|му|ѝ}} дадете образложение и/или совет за објавата.',
	'flow-moderation-confirmation-hide-post' => 'Ви препорачуваме на $1 да {{GENDER:$1|му|ѝ}} дадете образложение и/или совет за објавата.',
	'flow-moderation-confirmation-restore-post' => 'Успешно ја повративте објавата.',
	'flow-moderation-title-suppress-topic' => 'Да ја притаам темата?',
	'flow-moderation-title-delete-topic' => 'Да ја избришам темата?',
	'flow-moderation-title-hide-topic' => 'Да ја скријам темата?',
	'flow-moderation-title-restore-topic' => 'Да ја повратам темата?',
	'flow-moderation-intro-suppress-topic' => 'Објаснете зошто ја притајувате темава.',
	'flow-moderation-intro-delete-topic' => 'Објаснете зошто ја бришете темава.',
	'flow-moderation-intro-hide-topic' => 'Објаснете зошто ја скривате темава.',
	'flow-moderation-intro-restore-topic' => 'Објаснете зошто ја повраќате темава.',
	'flow-moderation-confirm-suppress-topic' => 'Притај',
	'flow-moderation-confirm-delete-topic' => 'Избриши',
	'flow-moderation-confirm-hide-topic' => 'Скриј',
	'flow-moderation-confirm-restore-topic' => 'Поврати',
	'flow-moderation-confirmation-suppress-topic' => 'Ви препорачуваме на $1 да {{GENDER:$1|му|ѝ}} дадете образложение и/или совет за темата.',
	'flow-moderation-confirmation-delete-topic' => 'Ви препорачуваме на $1 да {{GENDER:$1|му|ѝ}} дадете образложение и/или совет за темата.',
	'flow-moderation-confirmation-hide-topic' => 'Ви препорачуваме на $1 да {{GENDER:$1|му|ѝ}} дадете образложение и/или совет за темата.',
	'flow-moderation-confirmation-restore-topic' => 'Успешно ја повративте темата.',
	'flow-topic-permalink-warning' => 'Темата е започната на [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Темата е започната на [$2 таблата на {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Ова е постојана врска со една верзија на објавава.
Оваа верзиај е од $1.
Можете да ги погледате [$5 разликите од претходната верзија], или пак другите верзии во [$4 историјата на објавата].',
	'flow-revision-permalink-warning-post-first' => 'Ова е постојана врска до една верзија на објавава.
Можете да ги погледате подоцнежните верзии во [$4 историјата на објавата].',
	'flow-compare-revisions-revision-header' => 'Верзија на {{GENDER:$2|$2}} од $1',
	'flow-compare-revisions-header-post' => 'На страницава се прикажани разликите помеѓу две верзии на објава на $3 во темата „[$5 $2]“ на [$4 $1].
Можете да ги погледате другите верзии на објавата во [$6 нејзината историја].',
	'flow-topic-collapsed-one-line' => 'Мал приказ',
	'flow-topic-collapsed-full' => 'Расклопен приказ',
	'flow-topic-complete' => 'Целосен приказ',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Suresh.balasubra
 */
$messages['ml'] = array(
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
	'flow-moderation-intro-suppress-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം ഒതുക്കേണ്ടതെന്ന് ദയവായി വിശദീകരിക്കുക.',
	'flow-moderation-intro-delete-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം മായ്ക്കുന്നതെന്ന് വിശദീകരിക്കുക.',
	'flow-moderation-intro-hide-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം മറയ്ക്കുന്നതെന്ന് വിശദീകരിക്കുക.',
	'flow-moderation-intro-restore-topic' => 'എന്തുകൊണ്ടാണ് ഈ വിഷയം പുനഃസ്ഥാപിക്കുന്നതെന്ന് ദയവായി വിശദീകരിക്കുക.',
	'flow-moderation-confirm-suppress-topic' => 'ഒതുക്കുക',
	'flow-moderation-confirm-delete-topic' => 'മായ്ക്കുക',
	'flow-moderation-confirm-hide-topic' => 'മറയ്ക്കുക',
	'flow-moderation-confirm-restore-topic' => 'പുനഃസ്ഥാപിക്കുക',
	'flow-moderation-confirmation-restore-topic' => 'താങ്കൾ ഈ വിഷയം വിജയകരമായി പുനഃസ്ഥാപിച്ചിരിക്കുന്നു.',
);

/** Marathi (मराठी)
 * @author V.narsikar
 */
$messages['mr'] = array(
	'flow-newtopic-title-placeholder' => 'संदेशाचा विषय', # Fuzzy
	'flow-error-external' => 'आपले उत्तर जतन करण्यात त्रूटी घडली.आपले उत्तर जतन झाले नाही.<br /><small>मिळालेला त्रूटी संदेश असा होता: $1</small>', # Fuzzy
	'flow-error-external-multi' => 'आपले उत्तर जतन करण्यात त्रूटी आढळल्या.आपले उत्तर जतन झाले नाही.<br />$1', # Fuzzy
	'flow-error-missing-title' => 'विषयास मथळा नाही.एखाद्या विषयास जतन करावयाचे तर मथळा हवा.',
	'flow-error-prev-revision-mismatch' => 'आपले संपादन जतन करता आले नाही:त्यापेक्षा नविनतम बदल हा सादर करण्यात आला आहे.',
	'flow-error-prev-revision-does-not-exist' => 'मागील आवृत्ती शोधता आली नाही.',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Jadilah pengulas pertama!|$1 {{PLURAL:$1|komen}} oleh $2 {{PLURAL:$2|orang pengguna}}}}',
	'flow-topic-collapsed-one-line' => 'Paparan kecil',
	'flow-topic-collapsed-full' => 'Paparan terlipat',
	'flow-topic-complete' => 'Paparan penuh',
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
	'flow-hide-content' => '{{GENDER:$1|Skjult}} av $1',
	'flow-delete-content' => '{{GENDER:$1|Slettet}} av $1',
	'flow-suppress-content' => '{{GENDER:$1|Sensurert}} av $1',
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
	'flow-talk-link' => 'Diskuter med {{GENDER:$1|$1}}',
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
	'flow-error-external' => 'Det oppsto en feil.<br /><small>Feilmeldingen var: $1</small>',
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
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|redigerte}} innlegget ditt',
	'echo-category-title-flow-discussion' => 'Flow',
	'flow-link-post' => 'innlegg',
	'flow-link-topic' => 'diskusjon',
	'flow-link-history' => 'historikk',
	'flow-moderation-reason-placeholder' => 'Skriv inn årsaken her',
	'flow-moderation-title-suppress-post' => 'Sensurer melding',
	'flow-moderation-title-delete-post' => 'Slett melding',
	'flow-moderation-title-hide-post' => 'Skjul melding',
	'flow-moderation-title-restore-post' => 'Gjenopprett melding.',
	'flow-moderation-intro-suppress-post' => 'Bekreft at du ønsker å sensurere melding av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.',
	'flow-moderation-intro-delete-post' => 'Bekreft at du ønsker å slette meldingen av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.',
	'flow-moderation-intro-hide-post' => 'Bekreft at du ønsker å skjule meldingen av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.',
	'flow-moderation-intro-restore-post' => 'Bekreft at du ønsker å gjenopprette meldingen av {{GENDER:$1|$1}} i diskusjonen «$2», og oppgi en årsak for handlingen.',
	'flow-moderation-confirm-suppress-post' => 'Sensurer',
	'flow-moderation-confirm-delete-post' => 'Slett',
	'flow-moderation-confirm-hide-post' => 'Skjul',
	'flow-moderation-confirm-restore-post' => 'Gjenopprett',
	'flow-moderation-confirmation-restore-post' => 'Du har gjenopprettet dette innlegget.',
	'flow-moderation-title-suppress-topic' => 'Sensurer diskusjon?',
	'flow-moderation-title-delete-topic' => 'Slett diskusjon?',
	'flow-moderation-title-hide-topic' => 'Skjul diskusjon?',
	'flow-moderation-title-restore-topic' => 'Gjenopprett diskusjon?',
	'flow-moderation-intro-suppress-topic' => 'Forklar hvorfor du sensurerer denne diskusjonen.',
	'flow-moderation-intro-delete-topic' => 'Forklar hvorfor du sletter denne diskusjonen.',
	'flow-moderation-intro-hide-topic' => 'Forklar hvorfor du skjuler denne diskusjonen.',
	'flow-moderation-intro-restore-topic' => 'Forklar hvorfor du gjenoppretter denne diskusjonen.',
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
 * @author Breghtje
 * @author Effeietsanders
 * @author Krinkle
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
	'flow-post-moderated-toggle-show' => '[Weergeven]',
	'flow-post-moderated-toggle-hide' => '[Verbergen]',
	'flow-hide-content' => '{{GENDER:$1|Verborgen}} door $1',
	'flow-delete-content' => '{{GENDER:$1|Verwijderd}} door $1',
	'flow-suppress-content' => '{{GENDER:$1|Onderdrukt}} door $1',
	'flow-suppress-usertext' => '<em>Gebruikersnaam onderdrukt</em>',
	'flow-post-actions' => 'Handelingen',
	'flow-topic-actions' => 'Handelingen',
	'flow-cancel' => 'Annuleren',
	'flow-preview' => 'Voorvertoning',
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
	'flow-talk-link' => 'In contact komen met {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Wijzigingen opslaan',
	'flow-post-edited' => 'Bericht $2 {{GENDER:$1|bewerkt}} door $1',
	'flow-post-action-view' => 'Permanente koppeling',
	'flow-post-action-post-history' => 'Berichtgeschiedenis',
	'flow-post-action-suppress-post' => 'Onderdrukken',
	'flow-post-action-delete-post' => 'Verwijderen',
	'flow-post-action-hide-post' => 'Verbergen',
	'flow-post-action-edit-post' => 'Bericht bewerken',
	'flow-post-action-edit' => 'Bewerken',
	'flow-post-action-restore-post' => 'Bericht terugplaatsen',
	'flow-topic-action-view' => 'Permanente koppeling',
	'flow-topic-action-watchlist' => 'Volglijst',
	'flow-topic-action-edit-title' => 'Titel wijzigen',
	'flow-topic-action-history' => 'Onderwerpgeschiedenis',
	'flow-topic-action-hide-topic' => 'Onderwerp verbergen',
	'flow-topic-action-delete-topic' => 'Onderwerp verwijderen',
	'flow-topic-action-suppress-topic' => 'Onderwerp onderdrukken',
	'flow-topic-action-restore-topic' => 'Onderwerp terugplaatsen',
	'flow-error-http' => 'Er is een fout opgetreden in het contact met de server.',
	'flow-error-other' => 'Er is een onverwachte fout opgetreden.',
	'flow-error-external' => 'Er is een fout opgetreden.<br /><small>De foutmelding is: $1</small>',
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
	'flow-error-prev-revision-mismatch' => 'De bewerking kon niet worden opgeslagen.  Er is al een meer recente wijziging opgeslagen.',
	'flow-error-prev-revision-does-not-exist' => 'De vorige versie kon niet gevonden worden.',
	'flow-edit-header-submit' => 'Koptekst opslaan',
	'flow-edit-title-submit' => 'Onderwerp wijzigen',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|heeft}} een [$3 reactie] bewerkt.',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|heeft}} een [$3 reactie] toegevoegd.',
	'flow-rev-message-reply-bundle' => 'Er {{PLURAL:$1|is|zijn}} <strong>$1 {{PLURAL:$1|reactie|reacties}}</strong> toegevoegd.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|heeft}} het onderwerp [$3 $4] aangemaakt.',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|heeft}} het onderwerp gewijzigd van $5 naar [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|heeft}} prikbordaanhef aangemaakt.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|heeft}} de prikbordaanhef bewerkt.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] verborgen (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] verwijderd (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] onderdrukt (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|heeft}} een [$4 reactie] teruggeplaatst (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] verborgen (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] verwijderd (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] onderdrukt (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|heeft}} een [$4 onderwerp] teruggeplaatst (<em>$5</em>).',
	'flow-board-history' => 'Geschiedenis van "$1"',
	'flow-topic-history' => 'Onderwerpgeschiedenis van "$1"',
	'flow-post-history' => 'Berichtgeschiedenis van "Reactie van {{GENDER:$2|$2}}"',
	'flow-history-last4' => 'Laatste 4 uur',
	'flow-history-day' => 'Vandaag',
	'flow-history-week' => 'Afgelopen week',
	'flow-history-pages-topic' => 'Komt voor op het [$1 prikbord "$2"]',
	'flow-history-pages-post' => 'Komt voor op [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 is dit onderwerp begonnen|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} en {{PLURAL:$2|een andere gebruiker|andere gebruikers}}|0=Nog geen deelnemers|2={{GENDER:$3|$3}} en {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} en {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Reageer als eerste!|Reactie|Reacties ($1)}}',
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Reageer als eerste!|$1 {{PLURAL:$1|reactie|reacties}} van $2 {{PLURAL:$2|gebruiker|gebruikers}}}}',
	'flow-comment-restored' => 'Teruggeplaatste reactie',
	'flow-comment-deleted' => 'Verwijderde reactie',
	'flow-comment-hidden' => 'Verborgen reactie',
	'flow-comment-moderated' => 'Gemodereerde reactie',
	'flow-paging-rev' => 'Meer recente onderwerpen',
	'flow-paging-fwd' => 'Oudere onderwerpen',
	'flow-last-modified' => 'Ongeveer $1 voor het laatst bewerkt',
	'flow-notification-reply' => '$1 {{GENDER:$1|heeft}} geantwoord op uw [$5 bericht] in $2 op "$4".',
	'flow-notification-reply-bundle' => '$1 en {{PLURAL:$6|iemand anders|$5 anderen}} {{GENDER:$1|hebben}} gereageerd op uw [$4 bericht] in $2 op "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|heeft}} een [$5 bericht] geplaatst in $2 op [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 en $5 {{PLURAL:$6|andere gebruiker|anderen}} {{GENDER:$1|hebben}} een [$4 bericht] geplaatst in $2 op "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|heeft}} een [$5 nieuw onderwerp] aangemaakt op [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|heeft}} het onderwerp [$2 $3] hernoemd naar "$4" op [[$5|$6]].',
	'flow-notification-mention' => '$1 heeft u genoemd in {{GENDER:$1|zijn|haar|zijn/haar}} [$2 bericht] in "$3" op "$4"',
	'flow-notification-link-text-view-post' => 'Bericht bekijken',
	'flow-notification-link-text-view-board' => 'Prikbord bekijken',
	'flow-notification-link-text-view-topic' => 'Onderwerp bekijken',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|heeft}} gereageerd op uw bericht',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|heeft}} gereageerd op uw bericht in $2 op "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 en {{PLURAL:$5|iemand anders|$4 anderen}} {{GENDER:$1|hebben}} gereageerd op uw bericht in $2 op "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|heeft}} u genoemd op $2',
	'flow-notification-mention-email-batch-body' => '$1 heeft u genoemd in {{GENDER:$1|zijn|haar|zijn/haar}} bericht in "$2" op "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|heeft}} uw bericht bewerkt',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|heeft}} uw bericht bewerkt in $2 op "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 en $4 {{PLURAL:$5|andere gebruiker|anderen}} {{GENDER:$1|hebben}} een bericht bewerkt in $2 op "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|heeft}} uw onderwerp een andere naam gegeven',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|heeft}} uw onderwerp "$2" hernoemd naar "$3" op "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|heeft}} een nieuw onderwerp aangemaakt op $2',
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
	'flow-moderation-intro-suppress-post' => 'Geef een reden op waarom u dit bericht onderdrukt.',
	'flow-moderation-intro-delete-post' => 'Geef een reden op waarom u dit bericht verwijdert.',
	'flow-moderation-intro-hide-post' => 'Geef een reden op waarom u dit bericht verbergt.',
	'flow-moderation-intro-restore-post' => 'Geef een reden op waarom u dit bericht terugplaatst.',
	'flow-moderation-confirm-suppress-post' => 'Onderdrukken',
	'flow-moderation-confirm-delete-post' => 'Verwijderen',
	'flow-moderation-confirm-hide-post' => 'Verbergen',
	'flow-moderation-confirm-restore-post' => 'Terugplaatsen',
	'flow-moderation-confirmation-suppress-post' => 'Overweeg $1 terugkoppeling te geven over dit bericht.',
	'flow-moderation-confirmation-delete-post' => 'Overweeg {{GENDER:$1|$1}} terugkoppeling te geven over dit bericht.',
	'flow-moderation-confirmation-hide-post' => 'Overweeg {{GENDER:$1|$1}} terugkoppeling te geven over dit bericht.',
	'flow-moderation-confirmation-restore-post' => 'Dit bericht is teruggeplaatst.',
	'flow-moderation-title-suppress-topic' => 'Onderwerp onderdrukken?',
	'flow-moderation-title-delete-topic' => 'Onderwerp verwijderen?',
	'flow-moderation-title-hide-topic' => 'Onderwerp verbergen?',
	'flow-moderation-title-restore-topic' => 'Onderwerp terugplaatsen?',
	'flow-moderation-intro-suppress-topic' => 'Leg uit waarom u dit onderwerp onderdrukt.',
	'flow-moderation-intro-delete-topic' => 'Leg uit waarom u dit onderwerp verwijdert.',
	'flow-moderation-intro-hide-topic' => 'Leg uit waarom u dit onderwerp verbergt.',
	'flow-moderation-intro-restore-topic' => 'Leg uit waarom u dit onderwerp terugplaatst.',
	'flow-moderation-confirm-suppress-topic' => 'Onderdrukken',
	'flow-moderation-confirm-delete-topic' => 'Verwijderen',
	'flow-moderation-confirm-hide-topic' => 'Verbergen',
	'flow-moderation-confirm-restore-topic' => 'Terugplaatsen',
	'flow-moderation-confirmation-suppress-topic' => 'Overweeg {{GENDER:$1|$1}} terugkoppeling te geven over dit onderwerp.',
	'flow-moderation-confirmation-delete-topic' => 'Overweeg {{GENDER:$1|$1}} terugkoppeling te geven over dit onderwerp.',
	'flow-moderation-confirmation-hide-topic' => 'Overweeg {{GENDER:$1|$1}} terugkoppeling te geven over dit onderwerp.',
	'flow-moderation-confirmation-restore-topic' => 'Dit onderwerp is teruggeplaatst.',
	'flow-topic-permalink-warning' => 'Dit onderwerp is gestart op [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Dit onderwerp is gestart op het [$2 prikbord van {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Dit is een permanente koppeling naar een enkele versie van dit bericht.
Deze versie is van $1.
U kunt de [$5 verschillen ten opzichte van de vorige versie] bekijken, of andere versies bekijken op de [$4 geschiedenispagina van het bericht].',
	'flow-revision-permalink-warning-post-first' => 'Dit is een permanente kopeeling naar de eerste versie van dit bericht.
U kunt nieuwere versies bekijken op de [$4 geschiedenispagina van dit bericht].',
	'flow-compare-revisions-revision-header' => 'Version van $1 door {{GENDER:$2|$2}}',
	'flow-compare-revisions-header-post' => 'Op deze pagina worden de verschillen tussen twee versies weergegeven van een bericht van $3 in het onderwerp "[$5 $2]" op [$4 $1].
U kunt de andere versie van dit bericht bekijken op de [$6 geschiedenispagina].',
	'flow-topic-collapsed-one-line' => 'Kleine weergave',
	'flow-topic-collapsed-full' => 'Ingeklapte weergave',
	'flow-topic-complete' => 'Volledige weergave',
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
	'flow-error-external' => "Una error s'es producha.<br /><small>Lo messatge d'error recebut èra :$1</small>",
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
 * @author Rzuwig
 * @author Woytecr
 */
$messages['pl'] = array(
	'flow-post-moderated-toggle-show' => '[Pokaż]',
	'flow-post-moderated-toggle-hide' => '[Ukryj]',
	'flow-cancel' => 'Anuluj',
	'flow-preview' => 'Podgląd',
	'flow-newtopic-title-placeholder' => 'Temat wiadomości', # Fuzzy
	'flow-newtopic-header' => 'Dodaj nowy temat',
	'flow-newtopic-save' => 'Dodaj temat',
	'flow-newtopic-start-placeholder' => 'Rozpocznij nowy temat',
	'flow-edit-post-submit' => 'Zapisz zmiany',
	'flow-paging-fwd' => 'Starsze tematy',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|odpowiedział|odpowiedziała}} na twój post',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|zmienił|zmieniła}} nazwę twojego tematu',
	'flow-link-topic' => 'temat',
	'flow-link-history' => 'historia',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'flow-post-edited' => 'ليکنه د $1 لخوا په $2 {{GENDER:$1|سمه شوه}}',
	'flow-notification-edit-email-subject' => '$1 ستاسې ليکنه {{GENDER:$1|سمه کړه}}',
	'flow-notification-rename-email-subject' => '$1 ستاسې سرليک {{GENDER:$1|نوم بدل کړ}}',
);

/** Portuguese (português)
 * @author Helder.wiki
 */
$messages['pt'] = array(
	'flow-desc' => 'Sistema de Gerenciamento do Fluxo de Trabalho',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Helder.wiki
 * @author Tuliouel
 */
$messages['pt-br'] = array(
	'flow-desc' => 'Sistema de Gerenciamento do Fluxo de Trabalho',
	'flow-link-post' => 'publicar',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'flow-desc' => 'Sisteme de Gestione de le Flusse de fatìe',
	'flow-post-actions' => 'aziune', # Fuzzy
	'flow-topic-actions' => 'aziune', # Fuzzy
	'flow-cancel' => 'Annulle',
	'flow-newtopic-title-placeholder' => "Oggette d'u messàgge", # Fuzzy
	'flow-newtopic-content-placeholder' => 'Messàgge de teste. Si belle!', # Fuzzy
	'flow-newtopic-header' => "Aggiunge 'n'argomende nuève",
	'flow-newtopic-save' => "Aggiunge 'n'argomende",
	'flow-newtopic-start-placeholder' => "Cazze aqquà pe accumenzà 'nu 'ngazzamende nuève. Sì belle!", # Fuzzy
	'flow-reply-placeholder' => 'Cazze pe responnere a $1. Sì belle!', # Fuzzy
	'flow-reply-submit' => "Manne 'na resposte", # Fuzzy
	'flow-post-action-delete-post' => "Scangìlle 'u messàgge", # Fuzzy
	'flow-post-action-restore-post' => "Repristine 'u messàgge",
	'flow-topic-action-edit-title' => "Cange 'u titole",
	'flow-error-http' => "Ha assute 'n'errore condattanne 'u server. 'U messàgge tune non g'ha state reggistrate.", # Fuzzy
	'flow-error-other' => "Ha assute 'n'errore. 'U messàgge tune non g'ha state reggistrate.", # Fuzzy
	'flow-edit-title-submit' => "Cange 'u titole",
);

/** Russian (русский)
 * @author Midnight Gambler
 * @author Okras
 */
$messages['ru'] = array(
	'flow-desc' => 'Система управления потоками работ',
	'log-name-flow' => 'Журнал активности потоков',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|удалил|удалила}} [$4 сообщение] на странице [[$3]]',
	'flow-edit-header-link' => 'Изменить заголовок',
	'flow-header-empty' => 'У этой страницы обсуждения в настоящее время нет заголовка.',
	'flow-post-moderated-toggle-show' => '[Показать]',
	'flow-post-moderated-toggle-hide' => '[Скрыть]',
	'flow-post-actions' => 'Действия',
	'flow-topic-actions' => 'Действия',
	'flow-cancel' => 'Отменить',
	'flow-newtopic-title-placeholder' => 'Новая тема',
	'flow-newtopic-content-placeholder' => 'Добавьте, если хотите, какие-нибудь подробности',
	'flow-newtopic-header' => 'Добавить новую тему',
	'flow-newtopic-save' => 'Добавить тему',
	'flow-newtopic-start-placeholder' => 'Начать новое обсуждение',
	'flow-reply-placeholder' => 'Ответить {{GENDER:$1|участнику|участнице}} $1',
	'flow-reply-submit' => '{{GENDER:$1|Ответить}}',
	'flow-reply-link' => '{{GENDER:$1|Ответить}}',
	'flow-thank-link' => '{{GENDER:$1|Поблагодарить}}',
	'flow-edit-post-submit' => 'Подтвердить изменения',
	'flow-post-edited' => 'Сообщение отредактировано {{GENDER:$1|участником|участницей}} $1 $2',
	'flow-post-action-view' => 'Постоянная ссылка',
	'flow-post-action-delete-post' => 'Удалить',
	'flow-post-action-hide-post' => 'Скрыть',
	'flow-post-action-edit-post' => 'Редактировать сообщение',
	'flow-post-action-edit' => 'Править',
	'flow-post-action-restore-post' => 'Восстановить сообщение',
	'flow-topic-action-watchlist' => 'Список наблюдения',
	'flow-topic-action-edit-title' => 'Редактировать заголовок',
	'flow-topic-action-history' => 'История темы',
	'flow-topic-action-hide-topic' => 'Скрыть тему',
	'flow-topic-action-delete-topic' => 'Удалить тему',
	'flow-topic-action-restore-topic' => 'Восстановить тему',
	'flow-error-http' => 'Произошла ошибка при обращении к серверу.',
	'flow-error-other' => 'Произошла непредвиденная ошибка.',
	'flow-error-external' => 'Произошла ошибка.<br /><small>Было получено следующее сообщение об ошибке: $1</small>',
	'flow-error-edit-restricted' => 'Вам не разрешено редактировать это сообщение.',
	'flow-error-missing-content' => 'Сообщение не имеет содержимого. Для сохранения сообщения требуется содержимое.',
	'flow-error-missing-title' => 'Тема не имеет заголовка. Заголовок необходим для сохранения темы.',
	'flow-error-parsoid-failure' => 'Не удаётся выполнить разбор содержимого из-за сбоя Parsoid.',
	'flow-error-delete-failure' => 'Не удалось удалить этот элемент.',
	'flow-error-hide-failure' => 'Не удалось скрыть этот элемент.',
	'flow-error-restore-failure' => 'Не удалось восстановить этот элемент.',
	'flow-error-not-allowed' => 'Недостаточно прав для выполнения этого действия',
	'flow-edit-header-submit' => 'Сохранить заголовок',
	'flow-edit-title-submit' => 'Изменить название',
	'flow-rev-message-reply' => '$1 добавил{{GENDER:$2||а}} [$3 комментарий].',
	'flow-rev-message-new-post' => '$1 создал{{GENDER:$2||а}} тему [$3 $4].',
	'flow-rev-message-deleted-post' => '$1 удалил{{GENDER:$2||а}} [$4 комментарий] (<em>$5</em>).',
	'flow-topic-history' => 'История темы «$1»',
	'flow-history-last4' => 'За последние 4 часа',
	'flow-history-day' => 'Сегодня',
	'flow-history-week' => 'На прошлой неделе',
	'flow-comment-restored' => 'Восстановленный комментарий',
	'flow-comment-deleted' => 'Удалённый комментарий',
	'flow-comment-hidden' => 'Скрытый комментарий',
	'flow-notification-link-text-view-post' => 'Посмотреть сообщение',
	'flow-notification-link-text-view-topic' => 'Посмотреть тему',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ответил|ответила}} на ваше сообщение',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|ответил|ответила}} на ваше сообщение в теме $2 в «$3»',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|упомянул|упомянула}} вас в $2',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|отредактировал|отредактировала}} ваше сообщение',
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
	'flow-moderation-intro-delete-topic' => 'Поясните причину удаления данной темы.',
	'flow-moderation-intro-hide-topic' => 'Поясните, почему вы хотите скрыть данную тему.',
	'flow-moderation-intro-restore-topic' => 'Поясните причину восстановления данной темы.',
	'flow-topic-permalink-warning' => 'Эта тема была начата на [$2 $1]',
);

/** Sicilian (sicilianu)
 * @author Gmelfi
 */
$messages['scn'] = array(
	'flow-thank-link' => '{{GENDER:$1|Arringràzzia}}',
);

/** Slovenian (slovenščina)
 * @author Dbc334
 * @author Eleassar
 */
$messages['sl'] = array(
	'flow-post-moderated-toggle-show' => '[Prikaži]',
	'flow-post-moderated-toggle-hide' => '[Skrij]',
	'flow-reply-placeholder' => 'Odgovorite {{GENDER:$1|uporabniku|uporabnici}} $1',
	'flow-error-missing-replyto' => 'Podan ni bil noben parameter »odgovori na«. Ta parameter je za dejanje »odgovorite« obvezen.',
	'flow-error-invalid-replyto' => 'Parameter »odgovori« je bil neveljaven. Navedene objave ni bilo mogoče najti.',
	'flow-error-missing-postId' => 'Podan ni bil noben parameter »postId«. Ta parameter je za upravljanje z objavo obvezen.',
	'flow-error-invalid-postId' => 'Parameter »postId« ni veljaven. Navedene objave ($1) ni bilo mogoče najti.',
	'flow-notification-reply' => '$1 {{GENDER:$1|je odgovoril|je odgovorila}} na vašo [$5 objavo] v razdelku $2 na strani »$4«.',
	'flow-notification-reply-bundle' => '$1 in $5 {{PLURAL:$6|drug|druga|drugi|drugih}} {{GENDER:$1|je odgovoril|je odgovorila|so odgovorili}} na vašo [$4 objavo] v razdelku $2 na strani »$3«.',
	'flow-notification-edit' => '$1 {{GENDER:$1|je urejal|je urejala}} [$5 objavo] v razdelku $2 na [[$3|$4]].',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|je ustvaril|je ustvarila}} [$5 novo temo] na [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|je spremenil|je spremenila}} naslov [$2 $3] v »$4« na [[$5|$6]].',
	'flow-notification-link-text-view-post' => 'Ogled objave',
	'flow-notification-link-text-view-board' => 'Ogled deske',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|je odgovoril|je odgovorila}} na vašo objavo',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|je odgovoril|je odgovorila}} na vašo objavo v razdelku $2 na strani »$3«',
	'flow-notification-reply-email-batch-bundle-body' => '$1 in $4 {{PLURAL:$5|drugi|druga|drugi|drugih}} {{PLURAL:$5|sta {{GENDER:$1|odgovorila}}|so odgovorili}} na vašo objavo v razdelku $2 na strani »$3«',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Pogovor|Pogovori}}', # Fuzzy
	'echo-pref-tooltip-flow-discussion' => 'Obvesti me, ko se na pogovornih deskah pojavijo dejanja v zvezi z mano.', # Fuzzy
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
 */
$messages['sr-ec'] = array(
	'flow-preview' => 'Претпреглед',
	'flow-link-topic' => 'тема',
);

/** Serbian (Latin script) (srpski (latinica)‎)
 * @author Milicevic01
 */
$messages['sr-el'] = array(
	'flow-moderation-reason' => 'Razlog:',
	'flow-moderation-confirm' => 'Potvrdi akciju',
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
	'flow-talk-taken-over' => 'Denna diskussionssida har tagits över av en [https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow board].',
	'log-name-flow' => 'Flödets aktivitetsloggen',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2|tog bort}} en [$4 post] på [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2|återställde}} en [$4 post] på [[$3]]',
	'logentry-suppress-flow-suppress-post' => '$1 {{GENDER:$2|dämpade}} en [$4 post] på [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2|tog bort}} en [$4 post] på [[$3]]',
	'logentry-delete-flow-delete-topic' => '$1 {{GENDER:$2|tog bort}} ett [$4 ämne] på [[$3]]',
	'logentry-delete-flow-restore-topic' => '$1 {{GENDER:$2|återställde}} ett [$4 ämne] på [[$3]]',
	'logentry-suppress-flow-suppress-topic' => '$1 {{GENDER:$2|dämpade}} ett [$4 ämne] på [[$3]]',
	'logentry-suppress-flow-restore-topic' => '$1 {{GENDER:$2|tog bort}} ett [$4 ämne] på [[$3]]',
	'flow-user-moderated' => 'Modererad användare',
	'flow-edit-header-link' => 'Redigera sidhuvud',
	'flow-header-empty' => 'Denna diskussionssida har för närvarande ingen rubrik.',
	'flow-post-moderated-toggle-show' => '[Visa]',
	'flow-post-moderated-toggle-hide' => '[Dölj]',
	'flow-hide-content' => '{{GENDER:$1|Gömd}} av $1',
	'flow-delete-content' => '{{GENDER:$1|Borttagen}} av $1',
	'flow-suppress-content' => '{{GENDER:$1|Dämpad}} av $1',
	'flow-suppress-usertext' => '<em>Användarnamn dämpat</em>',
	'flow-post-actions' => 'Åtgärder',
	'flow-topic-actions' => 'Åtgärder',
	'flow-cancel' => 'Avbryt',
	'flow-preview' => 'Förhandsgranska',
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
	'flow-talk-link' => 'Diskutera med {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Skicka ändringar',
	'flow-post-edited' => 'Meddela {{GENDER:$1|redigerad}} av $1 $2',
	'flow-post-action-view' => 'Permanent länk',
	'flow-post-action-post-history' => 'Inläggshistorik',
	'flow-post-action-suppress-post' => 'Censurera inlägg',
	'flow-post-action-delete-post' => 'Radera',
	'flow-post-action-hide-post' => 'Dölj',
	'flow-post-action-edit-post' => 'Redigera inlägg',
	'flow-post-action-edit' => 'Redigera',
	'flow-post-action-restore-post' => 'Återställ inlägg',
	'flow-topic-action-view' => 'Permanent länk',
	'flow-topic-action-watchlist' => 'Bevakningslista',
	'flow-topic-action-edit-title' => 'Redigera titel',
	'flow-topic-action-history' => 'Ämneshistorik',
	'flow-topic-action-hide-topic' => 'Dölj ämne',
	'flow-topic-action-delete-topic' => 'Ta bort ämne',
	'flow-topic-action-suppress-topic' => 'Dämpa ämne',
	'flow-topic-action-restore-topic' => 'Återställ ämne',
	'flow-error-http' => 'Ett fel uppstod när servern kontaktades.',
	'flow-error-other' => 'Ett oväntat fel uppstod.',
	'flow-error-external' => 'Ett fel uppstod.<br /><small>Felmeddelandet var: $1</small>',
	'flow-error-edit-restricted' => 'Du har inte rätt att redigera detta inlägg.',
	'flow-error-external-multi' => 'Fel uppstod.<br />$1',
	'flow-error-missing-content' => 'Inlägget har inget innehåll. Innehåll krävs för att spara ett inlägg.',
	'flow-error-missing-title' => 'Ämnet har ingen titel. En titel krävs för att spara ett ämne.',
	'flow-error-parsoid-failure' => 'Det gick inte att parsa innehållet på grund av ett Parsoid-fel.',
	'flow-error-missing-replyto' => 'Ingen "replyTo" parameter tillhandahölls. Den här parametern krävs för åtgärden "svara".',
	'flow-error-invalid-replyto' => '"replyTo" parametern var ogiltig. Det angivna inlägget kunde inte hittas.',
	'flow-error-delete-failure' => 'Borttagning av detta objekt misslyckades.',
	'flow-error-hide-failure' => 'Döljandet av detta objekt misslyckades.',
	'flow-error-missing-postId' => 'Ingen "postId" parameter tillhandahölls. Denna parameter krävs för att påverka ett inlägg.',
	'flow-error-invalid-postId' => 'Parametern "postId" var ogiltig. Det angivna inlägget ($1) kunde inte hittas.',
	'flow-error-restore-failure' => 'Det gick inte att återställa objektet.',
	'flow-error-invalid-moderation-state' => 'Ett ogiltigt värde angavs för moderationState',
	'flow-error-invalid-moderation-reason' => 'Vänligen ange en orsak för moderationen',
	'flow-error-not-allowed' => 'Otillräcklig behörighet att utföra denna åtgärd',
	'flow-error-title-too-long' => 'Ämnesrubriker är begränsade till $1 {{PLURAL:$1|byte|bytes}}.',
	'flow-error-no-existing-workflow' => 'Detta arbetsflöde finns inte ännu.',
	'flow-error-not-a-post' => 'En ämnestitel kan inte sparas som ett inlägg.',
	'flow-error-missing-header-content' => 'Rubriken har inget innehåll. Rubrik krävs för att spara ett inlägg.',
	'flow-error-missing-prev-revision-identifier' => 'Tidigare omarbetningsidentifieraren saknas.',
	'flow-error-prev-revision-mismatch' => 'Redigeringen kunde inte sparas: en senare förändring har redan lämnats in.',
	'flow-error-prev-revision-does-not-exist' => 'Kunde inte hitta den tidigare omarbetningen.',
	'flow-edit-header-submit' => 'Spara rubrik',
	'flow-edit-title-submit' => 'Ändra titel',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|redigerade}} en [$3 kommentar]',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|lade till}} en [$3 kommentar].',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|kommentar|kommentarer}}</strong> var {{PLURAL:$1|tillagd|tillagda}} .',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|skapade}} ämnet [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|ändrade}} ämnestiteln till [$3 $4] från $5.',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|skapade}} rubriken.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|redigerade}} rubriken.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|dolde}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|raderade}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|upphävde}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|återställde}} en [$4 kommentar] (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|dolde}} [$4 ämnet] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|tog bort}} [$4 ämnet] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|dämpade}} [$4 ämnet] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|återställde}} [$4 ämnet] (<em>$5</em>).',
	'flow-board-history' => '"$1" historia',
	'flow-topic-history' => 'Ämneshistorik för "$1"',
	'flow-post-history' => '"Kommenterad av {{GENDER:$2|$2}}" post-historia',
	'flow-history-last4' => 'Senaste 4 timmarna',
	'flow-history-day' => 'I dag',
	'flow-history-week' => 'Senaste veckan',
	'flow-history-pages-topic' => 'Visas på [$1 "$2" board]',
	'flow-history-pages-post' => 'Visas på [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 påbörjade detta ämne|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} och $2 {{PLURAL:$2|annan|andra}}|0=Inget deltagande ännu|2={{GENDER:$3|$3}} och {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} och {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Kommentera ($1)|Kommentarer ($1)|0=Var den första att kommentera!}}', # Fuzzy
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Var först med att kommentera!|$1 {{PLURAL:$1|kommentar|kommentarer}} av $2 {{PLURAL:$2|användare|användare}}}}',
	'flow-comment-restored' => 'Återställd kommentar',
	'flow-comment-deleted' => 'Raderad kommentar',
	'flow-comment-hidden' => 'Dold kommentar',
	'flow-comment-moderated' => 'Modererad kommentar',
	'flow-paging-rev' => 'Nyare ämnen',
	'flow-paging-fwd' => 'Äldre ämnen',
	'flow-last-modified' => 'Ändrades senast om $1',
	'flow-notification-reply' => '$1 {{GENDER:$1|svarade}} på ditt [$5 inlägg] om $2 på [[$3|$4]].',
	'flow-notification-reply-bundle' => '$1 och $5 {{PLURAL:$6|annan|andra}} {{GENDER:$1|svarade}} på ditt [$4 inlägg] i $2 på "$3".',
	'flow-notification-edit' => '$1 {{GENDER:$1|redigerade}} ett [$5 inlägg] om $2 på [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 och $5 {{PLURAL:$6|annan|andra}} {{GENDER:$1|redigerade}} ett [$4  inlägg] i $2 på "$3".',
	'flow-notification-newtopic' => '$1 {{GENDER:$1|skapade}} ett [$5 nytt ämne] på [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 {{GENDER:$1|ändrade}} rubriken för [$2 $3] till "$4" på [[$5|$6]].',
	'flow-notification-mention' => '$1 {{GENDER:$1|nämnde}} dig i {{GENDER:$1|hans|hennes|sitt}} [$2 inlägg] i "$3" på "$4".',
	'flow-notification-link-text-view-post' => 'Visa inlägg',
	'flow-notification-link-text-view-board' => 'Visa forum',
	'flow-notification-link-text-view-topic' => 'Visa ämne',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|svarade}} på ditt inlägg',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|svarade}} på ditt inlägg i $2 på "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 och $4 {{PLURAL:$5|annan|andra}} {{GENDER:$1|svarade}} på ditt inlägg i $2 på "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|omnämnde}} dig på $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|omnämnde}} dig i {{GENDER:$1|hans|hennes|sitt}} inlägg i "$2" på "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|redigerade}} ditt inlägg',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|redigerade}} ditt inlägg i $2 på "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 och $4 {{PLURAL:$5|annan|andra}} {{GENDER:$1|redigerade}} ett inlägg i $2 på "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|byt namn på}} ditt ämne',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|byt namn på}} ditt ämne "$2" till "$3" på "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|skapade}} ett nytt ämne på $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|skapade}} ett ny ämne med titeln "$2" på $3',
	'echo-category-title-flow-discussion' => 'Flöde',
	'echo-pref-tooltip-flow-discussion' => 'Meddela mig när åtgärder som rör mig förekommer i flödet.',
	'flow-link-post' => 'inlägg',
	'flow-link-topic' => 'ämne',
	'flow-link-history' => 'historik',
	'flow-moderation-reason-placeholder' => 'Ange din orsak här',
	'flow-moderation-title-suppress-post' => 'Dämpa inlägget?',
	'flow-moderation-title-delete-post' => 'Ta bort inlägget?',
	'flow-moderation-title-hide-post' => 'Dölja inlägget?',
	'flow-moderation-title-restore-post' => 'Återställa inlägget?',
	'flow-moderation-intro-suppress-post' => 'Förklara varför du dämpar detta inlägg.',
	'flow-moderation-intro-delete-post' => 'Förklara varför du tar bort detta inlägg.',
	'flow-moderation-intro-hide-post' => 'Förklara varför du döljer detta inlägg.',
	'flow-moderation-intro-restore-post' => 'Förklara varför du återställer detta inlägg.',
	'flow-moderation-confirm-suppress-post' => 'Dämpa',
	'flow-moderation-confirm-delete-post' => 'Ta bort',
	'flow-moderation-confirm-hide-post' => 'Dölj',
	'flow-moderation-confirm-restore-post' => 'Återställ',
	'flow-moderation-confirmation-suppress-post' => 'Överväg att {{GENDER:$1|ge}} $1 feedback på detta inlägg.',
	'flow-moderation-confirmation-delete-post' => 'Överväg att {{GENDER:$1|ge}} $1 feedback på detta inlägg.',
	'flow-moderation-confirmation-hide-post' => 'Överväg att {{GENDER:$1|ge}} $1 feedback på detta inlägg.',
	'flow-moderation-confirmation-restore-post' => 'Du har återställt detta inlägg.',
	'flow-moderation-title-suppress-topic' => 'Dämpa ämnet?',
	'flow-moderation-title-delete-topic' => 'Ta bort ämne?',
	'flow-moderation-title-hide-topic' => 'Dölja ämnet?',
	'flow-moderation-title-restore-topic' => 'Återställa ämnet?',
	'flow-moderation-intro-suppress-topic' => 'Förklara varför du dämpar detta ämne.',
	'flow-moderation-intro-delete-topic' => 'Förklara varför du tar bort detta ämne.',
	'flow-moderation-intro-hide-topic' => 'Förklara varför du döljer detta ämne.',
	'flow-moderation-intro-restore-topic' => 'Förklara varför du återställer detta ämne.',
	'flow-moderation-confirm-suppress-topic' => 'Dämpa',
	'flow-moderation-confirm-delete-topic' => 'Ta bort',
	'flow-moderation-confirm-hide-topic' => 'Dölj',
	'flow-moderation-confirm-restore-topic' => 'Återställ',
	'flow-moderation-confirmation-suppress-topic' => 'Överväg att {{GENDER:$1|ge}} $1 feedback på detta ämne.',
	'flow-moderation-confirmation-delete-topic' => 'Överväg att {{GENDER:$1|ge}} $1 feedback på detta ämne.',
	'flow-moderation-confirmation-hide-topic' => 'Överväg att {{GENDER:$1|ge}} $1 feedback på detta ämne.',
	'flow-moderation-confirmation-restore-topic' => 'Du har återställt detta ämne.',
	'flow-topic-permalink-warning' => 'Detta ämne blev startad på [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Detta ämne startades på [$2 {{GENDER:$1|$1}}s board]',
	'flow-revision-permalink-warning-post' => 'Detta är en permanent länk till en enda version av det här inlägget.
Denna version är från $1.
Du kan se [$5 skillnader från föregående version], eller visa andra versioner på [$4 inläggets historiksida].',
	'flow-revision-permalink-warning-post-first' => 'Detta är en permanent länk till den första versionen av det här inlägget.
Du kan visa senare versioner på [$4 inläggets historiksida].',
	'flow-compare-revisions-revision-header' => 'Version av {{GENDER:$2|$2}} från $1',
	'flow-compare-revisions-header-post' => 'Denna sida visar ändringarna mellan två versioner av ett inlägg av $3 i ämnet "[$5 $2]" på [$4 $1].
Du kan se andra versioner av detta inlägg på dess [$6 historiksida].',
	'flow-topic-collapsed-one-line' => 'Liten vy',
	'flow-topic-collapsed-full' => 'Komprimerad vy',
	'flow-topic-complete' => 'Full vy',
);

/** Turkish (Türkçe)
 * @author Rapsar
 */
$messages['tr'] = array(
	'flow-notification-mention' => '$1, "$4" sayfasındaki "$3" başlığındaki [$2 değişikliğinde] sizden {{GENDER:$1|bahsetti}}', # Fuzzy
	'flow-notification-mention-email-subject' => '$1, $2 sayfasında sizden {{GENDER:$1|bahsetti}}',
	'flow-notification-mention-email-batch-body' => '$1, "$3" sayfasındaki "$2" başlığında sizden {{GENDER:$1|bahsetti}}', # Fuzzy
	'flow-link-history' => 'geçmiş',
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
	'flow-post-moderated-toggle-show' => '[Показати]',
	'flow-post-moderated-toggle-hide' => '[Сховати]',
	'flow-hide-content' => ' {{GENDER:$1|приховано}} $1',
	'flow-delete-content' => '{{GENDER:$1|вилучено}} $1',
	'flow-suppress-content' => '{{GENDER:$1|прибрано}} $1',
	'flow-suppress-usertext' => "<em>Ім'я користувача приховано</em>",
	'flow-post-actions' => 'Дії',
	'flow-topic-actions' => 'Дії',
	'flow-cancel' => 'Скасувати',
	'flow-preview' => 'Попередній перегляд',
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
	'flow-talk-link' => 'Поговорити з {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Подати зміни',
	'flow-post-edited' => 'Допис {{GENDER:$1|відредагував|відредагувала}} $1 $2',
	'flow-post-action-view' => 'Постійне посилання',
	'flow-post-action-post-history' => 'Опублікувати історію',
	'flow-post-action-suppress-post' => 'Прибрати',
	'flow-post-action-delete-post' => 'Видалити',
	'flow-post-action-hide-post' => 'Приховати',
	'flow-post-action-edit-post' => 'Редагувати публікацію',
	'flow-post-action-edit' => 'Редагувати',
	'flow-post-action-restore-post' => 'Відновити публікацію',
	'flow-topic-action-view' => 'Постійне посилання',
	'flow-topic-action-watchlist' => 'Список спостереження',
	'flow-topic-action-edit-title' => 'Змінити заголовок',
	'flow-topic-action-history' => 'Історія теми',
	'flow-topic-action-hide-topic' => 'Приховати тему',
	'flow-topic-action-delete-topic' => 'Видалити тему',
	'flow-topic-action-suppress-topic' => 'Прибрати тему',
	'flow-topic-action-restore-topic' => 'Відновити тему',
	'flow-error-http' => 'Сталася помилка при зверненні до сервера.',
	'flow-error-other' => 'Трапилася неочікувана помилка.',
	'flow-error-external' => 'Сталася помилка.<br /><small>Отримане повідомлення було:$1</small>',
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
	'flow-error-title-too-long' => 'Назви тем обмежені $1 {{PLURAL:$1|байтом|байтами}}.',
	'flow-error-no-existing-workflow' => 'Цей робочий процес ще не існує.',
	'flow-error-not-a-post' => 'Назву теми не можна зберегти як допис.',
	'flow-error-missing-header-content' => 'Заголовок не має ніякого вмісту. Необхідний вміст, щоб зберегти заголовок.',
	'flow-error-missing-prev-revision-identifier' => 'Ідентифікатор попередньої ревізії відсутній.',
	'flow-error-prev-revision-mismatch' => 'Не вдалося зберегти редагування: пізніші зміни вже представлені.',
	'flow-error-prev-revision-does-not-exist' => 'Не вдалося знайти попередню ревізію.',
	'flow-edit-header-submit' => 'Зберегти заголовок',
	'flow-edit-title-submit' => 'Змінити заголовок',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2|відредагував|відредагувала}} [коментар $3]',
	'flow-rev-message-reply' => '$1 {{GENDER:$2|додав|додала}} [коментар $3].',
	'flow-rev-message-reply-bundle' => '<strong>$1 {{PLURAL:$1|коментар|коментарі|коментарів}} </strong> {{PLURAL:$1|був доданий|були додані}}.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|створив|створила}} тему [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|відредагував|відредагувала}} назву теми на [$3 $4] із $5.',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2|створив|створила}} заголовок стіни.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|змінив|змінила}} заголовок стіни.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|приховав|приховала}} [коментар $4](<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2|видалив|видалила}} [коментар $4] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2|подавив|подавила}} [коментар $4] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|відновив|відновила}} [коментар $4] (<em>$5</em>).',
	'flow-rev-message-hid-topic' => '$1 {{GENDER:$2|приховав|приховала}} [тему $4] (<em>$5</em>).',
	'flow-rev-message-deleted-topic' => '$1 {{GENDER:$2|вилучив|вилучила}} [тему $4] (<em>$5</em>).',
	'flow-rev-message-suppressed-topic' => '$1 {{GENDER:$2|прибрав|прибрала}} [тему $4] (<em>$5</em>).',
	'flow-rev-message-restored-topic' => '$1 {{GENDER:$2|відновив|відновила}} [тему $4] (<em>$5</em>).',
	'flow-board-history' => 'Історія "$1"',
	'flow-topic-history' => 'Історія теми "$1"',
	'flow-post-history' => 'Коментарі від історії дописів {{GENDER:$2|$2}}',
	'flow-history-last4' => 'Останні 4 години',
	'flow-history-day' => 'Сьогодні',
	'flow-history-week' => 'Останній тиждень',
	'flow-history-pages-topic' => 'З\'являється на [стіні $1  "$2"]',
	'flow-history-pages-post' => "З'являється на [$1 $2]",
	'flow-topic-participants' => '{{PLURAL:$1|$3 {{GENDER:$3|розпочав цю тему|розпочала цю тему}}|{{GENDER:$3|$3}}, {{GENDER:$4|$4}}, {{GENDER:$5|$5}} та {{PLURAL:$2|інший|інші|інших}}|0=Ще не має учасників|2={{GENDER:$3|$3}} та {{GENDER:$4|$4}}|3={{GENDER:$3|$3}}, {{GENDER:$4|$4}} та {{GENDER:$5|$5}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|Коментар ($1)|Коментарі ($1)|Коментарів ($1)|0={{GENDER:$2|Залиште перший коментар!}}}}',
	'flow-topic-meta-minimal' => '{{PLURAL:$1|0=Залиште перший коментар!|$1 {{PLURAL:$1|коментар|коментарі|коментарів}} від $2 {{PLURAL:$2|користувача|користувачів}}}}',
	'flow-comment-restored' => 'Відновлений коментар',
	'flow-comment-deleted' => 'Видалений коментар',
	'flow-comment-hidden' => 'Прихований коментар',
	'flow-comment-moderated' => 'Модерований коментар',
	'flow-paging-rev' => 'Новіші теми',
	'flow-paging-fwd' => 'Старіші теми',
	'flow-last-modified' => 'Остання зміна про $1',
	'flow-notification-reply' => '$1  {{GENDER:$1|відповів|відповіла}} на ваше [повідомлення $5] у $2 на [[$3|$4]].',
	'flow-notification-reply-bundle' => '$1 та $5 {{PLURAL:$6|інший|інші|інших}} {{GENDER:$1|відповіли}} на ваш [допис $4] у $2 на "$3".',
	'flow-notification-edit' => '$1  {{GENDER:$1|відредагував|відредагувала}}  [повідомлення $5] у $2 на [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 та $5 {{PLURAL:$6|інший|інші|інших}} {{GENDER:$1|відредагував|відредагувала}} [$4 допис] у $2 на "$3".',
	'flow-notification-newtopic' => '$1  {{GENDER:$1|створив|створила}} [нову тему $5] на [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1  {{GENDER:$1|змінив|змінила}} назву [$2 $3] на "$4" у [[$5|$6]]',
	'flow-notification-mention' => '$1 {{GENDER:$1|згадав|згадала|згадали}} вас у {{GENDER:$1|своєму|своєму|своєму}} [$2 дописі] у "$3" на "$4"',
	'flow-notification-link-text-view-post' => 'Переглянути допис',
	'flow-notification-link-text-view-board' => 'Переглянути стіну',
	'flow-notification-link-text-view-topic' => 'Перегляд теми',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|відповів|відповіла}} на ваш допис',
	'flow-notification-reply-email-batch-body' => '$1  {{GENDER:$1|відповів|відповіла}} на ваш допис у $2 на $3.',
	'flow-notification-reply-email-batch-bundle-body' => '$1 та $4 {{PLURAL:$5|інший|інші|інших}} {{GENDER:$1|відповіли}} на ваш допис у $2 на "$3".',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|згадав|згадала}} вас на $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|згадав|згадала|згадали}} вас у  {{GENDER:$1|своєму|своєму|своєму}} дописі у "$2" на "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|відредагував|відредагувала}} ваш допис',
	'flow-notification-edit-email-batch-body' => '$1  {{GENDER:$1|відредагував|відредагувала}} ваш допис у $2 на „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 та $4 {{PLURAL:$5|інший|інші|інших}} {{GENDER:$1|відредагував|відредагувала}} допис у $2 на "$3".',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|перейменував|перейменувала}} вашу тему',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|перейменував|перейменувала}} вашу тему   з „$2“ на „$3“  у „$4“',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|створив|створила}} нову тему на $2',
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
	'flow-moderation-intro-suppress-post' => 'Будь ласка, поясніть, чому ви прибрали цей допис.',
	'flow-moderation-intro-delete-post' => 'Будь ласка, поясніть, чому ви хочете видалити цей допис.',
	'flow-moderation-intro-hide-post' => 'Будь ласка, чому ви приховуєте цей допис.',
	'flow-moderation-intro-restore-post' => 'Будь ласка, поясніть, чому ви відновлюєте цей допис.',
	'flow-moderation-confirm-suppress-post' => 'Прибрати',
	'flow-moderation-confirm-delete-post' => 'Видалити',
	'flow-moderation-confirm-hide-post' => 'Приховати',
	'flow-moderation-confirm-restore-post' => 'Відновити',
	'flow-moderation-confirmation-suppress-post' => 'Розгляньте відгук {{GENDER:$1|наданий}} $1 на цей допис.',
	'flow-moderation-confirmation-delete-post' => 'Розгляньте відгук {{GENDER:$1|наданий}} $1 на цей допис.',
	'flow-moderation-confirmation-hide-post' => 'Розгляньте відгук {{GENDER:$1|наданий}} $1 на цей допис.',
	'flow-moderation-confirmation-restore-post' => 'Ви успішно відновили цю публікацію.',
	'flow-moderation-title-suppress-topic' => 'Прибрати тему?',
	'flow-moderation-title-delete-topic' => 'Видалити тему?',
	'flow-moderation-title-hide-topic' => 'Приховати тему?',
	'flow-moderation-title-restore-topic' => 'Відновити тему?',
	'flow-moderation-intro-suppress-topic' => 'Будь ласка, поясніть, чому ви прибрали цю тему.',
	'flow-moderation-intro-delete-topic' => 'Будь ласка, поясніть, чому ви вилучаєте цю тему.',
	'flow-moderation-intro-hide-topic' => 'Будь ласка, чому ви приховуєте цю тему.',
	'flow-moderation-intro-restore-topic' => 'Будь ласка, поясніть, чому ви відновлюєте цю тему.',
	'flow-moderation-confirm-suppress-topic' => 'Прибрати',
	'flow-moderation-confirm-delete-topic' => 'Видалити',
	'flow-moderation-confirm-hide-topic' => 'Приховати',
	'flow-moderation-confirm-restore-topic' => 'Відновити',
	'flow-moderation-confirmation-suppress-topic' => 'Розгляньте відгук {{GENDER:$1|наданий}} $1 на цю тему.',
	'flow-moderation-confirmation-delete-topic' => 'Розгляньте відгук {{GENDER:$1|наданий}} $1 на цю тему.',
	'flow-moderation-confirmation-hide-topic' => 'Розгляньте відгук {{GENDER:$1|наданий}} $1 на цю тему.',
	'flow-moderation-confirmation-restore-topic' => 'Ви успішно відновили цю тему.',
	'flow-topic-permalink-warning' => 'Ця тема розпочата [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Ця тема розпочата на [$2 стіні {{GENDER:$1|$1}}]',
	'flow-revision-permalink-warning-post' => 'Це постійне посилання на окрему версію цього допису.
Це версія за $1.
Ви можете подивитися [відмінності від попередньої версії $5] або переглянути інші версії на [сторінці історії допису $4].',
	'flow-revision-permalink-warning-post-first' => 'Це постійне посилання на першу версію цього допису.
Ви можете переглядати пізніші версії на [сторінці історії допису $4].',
	'flow-compare-revisions-revision-header' => 'Версія від {{GENDER:$2|$2}} за $1',
	'flow-compare-revisions-header-post' => 'Ця сторінка відображає зміни між двома версіями допису від $3 у розділі "[$5 $2]" на [$4 $1].
Ви можете побачити інші версії цього допису на його [сторінці історії $6].',
	'flow-topic-collapsed-one-line' => 'Малий вигляд',
	'flow-topic-collapsed-full' => 'Згорнутий вигляд',
	'flow-topic-complete' => 'Повний вигляд',
);

/** Vietnamese (Tiếng Việt)
 * @author Baonguyen21022003
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'flow-desc' => 'Hệ thống quản lý luồng công việc',
	'log-name-flow' => 'Nhật trình hoạt động Flow',
	'logentry-delete-flow-delete-post' => '$1 {{GENDER:$2}}đã xóa một [$4 bài đăng] tại [[$3]]',
	'logentry-delete-flow-restore-post' => '$1 {{GENDER:$2}}đã phục hồi một [$4 bài đăng] tại [[$3]]',
	'logentry-suppress-flow-restore-post' => '$1 {{GENDER:$2}}đã xóa một [$4 bài đăng] tại [[$3]]',
	'flow-user-moderated' => 'Người dùng bị kiểm duyệt',
	'flow-edit-header-link' => 'Sửa đầu đề',
	'flow-header-empty' => 'Trang thảo luận này hiện không có đầu đề.',
	'flow-post-moderated-toggle-show' => '[Xem]',
	'flow-post-moderated-toggle-hide' => '[Ẩn]',
	'flow-hide-content' => 'Ẩn bởi $1',
	'flow-delete-content' => 'Xóa bởi $1',
	'flow-suppress-content' => 'Đàn áp bởi $1 vào $2',
	'flow-suppress-usertext' => '<em>Tên người dùng bị đàn áp</em>',
	'flow-post-actions' => 'Tác vụ',
	'flow-topic-actions' => 'Tác vụ',
	'flow-cancel' => 'Hủy bỏ',
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
	'flow-talk-link' => 'Nói chuyện với $1',
	'flow-edit-post-submit' => 'Gửi thay đổi',
	'flow-post-edited' => 'Bài đăng được sửa đổi bởi $1 $2',
	'flow-post-action-view' => 'Liên kết thường trực',
	'flow-post-action-post-history' => 'Lịch sử bài đăng',
	'flow-post-action-suppress-post' => 'Đàn áp',
	'flow-post-action-delete-post' => 'Xóa',
	'flow-post-action-hide-post' => 'Ẩn',
	'flow-post-action-edit-post' => 'Sửa bài đăng',
	'flow-post-action-edit' => 'Sửa đổi',
	'flow-post-action-restore-post' => 'Phục hồi bài đăng',
	'flow-topic-action-view' => 'Liên kết thường trực',
	'flow-topic-action-watchlist' => 'Danh sách theo dõi',
	'flow-topic-action-edit-title' => 'Sửa tiêu đề',
	'flow-topic-action-history' => 'Lịch sử chủ đề',
	'flow-error-http' => 'Đã xuất hiện lỗi khi liên lạc với máy chủ.',
	'flow-error-other' => 'Đã xuất hiện lỗi bất ngờ.',
	'flow-error-external' => 'Đã xuất hiện lỗi.<br /><small>Lỗi nhận được là: $1</small>',
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
	'flow-edit-header-submit' => 'Lưu đầu đề',
	'flow-edit-title-submit' => 'Thay đổi tiêu đề',
	'flow-rev-message-edit-post' => '$1 {{GENDER:$2}}đã sửa đổi một [$3 bình luận].',
	'flow-rev-message-reply' => '$1 {{GENDER:$2}}đã thêm một [$3 bình luận].',
	'flow-rev-message-reply-bundle' => '<strong>$1 bình luận</strong> được thêm vào.',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2}}đã tạo chủ đề [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2}}đã đổi tiêu đề của chủ đề từ $5 thành [$3 $4].',
	'flow-rev-message-create-header' => '$1 {{GENDER:$2}}đã tạo đầu đề bảng tin nhắn.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2}}đã sửa đổi đầu đề bảng tin nhắn.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2}}đã ẩn một [$4 bình luận] (<em>$5</em>).',
	'flow-rev-message-deleted-post' => '$1 {{GENDER:$2}}đã xóa một [$4 bình luận] (<em>$5</em>).',
	'flow-rev-message-suppressed-post' => '$1 {{GENDER:$2}}đã đàn áp một [$4 bình luận] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2}}đã phục hồi một [$4 bình luận] (<em>$5</em>).',
	'flow-board-history' => 'Lịch sử “$1”',
	'flow-topic-history' => 'Lịch sử chủ đề “$1”',
	'flow-post-history' => 'Lịch sử bài đăng “Bình luận của $2”',
	'flow-history-last4' => '4 giờ trước đây',
	'flow-history-day' => 'Hôm nay',
	'flow-history-week' => 'Tuần trước',
	'flow-history-pages-topic' => 'Xuất hiện trên [$1 bảng tin nhắn “$2”]',
	'flow-history-pages-post' => 'Xuất hiện trên [$1 $2]',
	'flow-topic-participants' => '{{PLURAL:$1|$3 đã bắt đầu chủ đề này|$3, $4, $5, và {{PLURAL:$2|một người|những người}} khác|0=Chưa có ai tham gia|2=$3 và $4|3=$3, $4, và $5}}',
	'flow-topic-comments' => '{{PLURAL:$1|Bình luận ($1)|0=Hãy là người đầu tiên bình luận!}}', # Fuzzy
	'flow-comment-restored' => 'Bình luận đã được phục hồi',
	'flow-comment-deleted' => 'Bình luận đã bị xóa',
	'flow-comment-hidden' => 'Bình luận đã bị ẩn',
	'flow-comment-moderated' => 'Bài đăng kiểm duyệt',
	'flow-paging-rev' => 'Thêm chủ đề gần đây',
	'flow-paging-fwd' => 'Chủ đề cũ hơn',
	'flow-last-modified' => 'Thay đổi lần cuối cùng vào khoảng $1',
	'flow-notification-reply' => '$1 đã trả lời [$5 bài đăng của bạn] về $2 tại “$4”.',
	'flow-notification-reply-bundle' => '$1 và $5 {{PLURAL:$6}}người khác đã {{GENDER:$1}}trả lời [$4 bài đăng] của bạn về $2 tại “$3”.',
	'flow-notification-edit' => '$1 đã sửa đổi một [$5 bài đăng] về $2 tại [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 và $5 {{PLURAL:$6}}người khác đã {{GENDER:$1}}sửa đổi một [$4 bài đăng] về $2 tại “$3”.',
	'flow-notification-newtopic' => '$1 đã tạo ra [$5 chủ đề mới] tại [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 đã thay đổi tiêu đề của [$2 $3] thành “$4” tại [[$5|$6]].',
	'flow-notification-mention' => '$1 đã nói đến bạn trong [$2 bài đăng] của họ về “$3” tại “$4”.',
	'flow-notification-link-text-view-post' => 'Xem bài đăng',
	'flow-notification-link-text-view-board' => 'Xem bảng tin',
	'flow-notification-link-text-view-topic' => 'Xem chủ đề',
	'flow-notification-reply-email-subject' => '$1 đã trả lời bài đăng của bạn',
	'flow-notification-reply-email-batch-body' => '$1 đã trả lời bài đăng của bạn về $2 tại “$3”',
	'flow-notification-reply-email-batch-bundle-body' => '$1 và $4 {{PLURAL:$5}}người khác đã trả lời bài đăng của bạn về $2 tại “$3”',
	'flow-notification-mention-email-subject' => '$1 đã nói đến bạn tại $2',
	'flow-notification-mention-email-batch-body' => '$1 đã nói đến bạn trong bài đăng của họ về “$2” tại “$3”.',
	'flow-notification-edit-email-subject' => '$1 đã sửa đổi bài đăng của bạn',
	'flow-notification-edit-email-batch-body' => '$1 đã sửa đổi bài đăng của bạn về $2 tại “$3”',
	'flow-notification-edit-email-batch-bundle-body' => '$1 và $4 {{PLURAL:$5}}người khác đã sửa đổi một bài đăng về $2 tại “$3”',
	'flow-notification-rename-email-subject' => '$1 đã đổi tên chủ đề của bạn',
	'flow-notification-rename-email-batch-body' => '$1 đã đổi tên chủ đề của bạn từ “$2” thành “$3” tại “$4”',
	'flow-notification-newtopic-email-subject' => '$1 đã bắt đầu một chủ đề mới tại $2',
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
	'flow-moderation-intro-suppress-post' => 'Xin vui lòng giải thích tại sao bạn đàn áp bài đăng này.',
	'flow-moderation-intro-delete-post' => 'Xin vui lòng giải thích tại sao bạn xóa bài đăng này.',
	'flow-moderation-intro-hide-post' => 'Xin vui lòng giải thích tại sao bạn ẩn bài đăng này.',
	'flow-moderation-intro-restore-post' => 'Xin vui lòng giải thích tại sao bạn phục hồi bài đăng này.',
	'flow-moderation-confirm-suppress-post' => 'Đàn áp',
	'flow-moderation-confirm-delete-post' => 'Xóa',
	'flow-moderation-confirm-hide-post' => 'Ẩn',
	'flow-moderation-confirm-restore-post' => 'Phục hồi',
	'flow-moderation-confirmation-suppress-post' => 'Xin nghĩ đến việc gửi phản hồi cho $1 về bài đăng này.',
	'flow-moderation-confirmation-delete-post' => 'Xin nghĩ đến việc gửi phản hồi cho $1 về bài đăng này.',
	'flow-moderation-confirmation-hide-post' => 'Xin nghĩ đến việc gửi phản hồi cho $1 về bài đăng này.',
	'flow-moderation-confirmation-restore-post' => 'Bạn đã phục hồi bài đăng này thành công.',
	'flow-topic-permalink-warning' => 'Chủ đề này được bắt đầu tại [$2 $1]',
	'flow-topic-permalink-warning-user-board' => 'Chủ đề này được bắt đầu tại [$2 bảng tin nhắn của $1]',
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
	'flow-post-moderated-toggle-show' => '[ווייזן]',
	'flow-post-moderated-toggle-hide' => '[באהאלטן]',
	'flow-delete-content' => '{{GENDER:$1|אויסגעמעקט}} דורך $1',
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
	'flow-talk-link' => 'רעדן צו {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'איינגעבן ענדערונגען',
	'flow-post-action-view' => 'פערמאנענטער לינק',
	'flow-post-action-edit' => 'רעדאַקטירן',
	'flow-topic-action-view' => 'פערמאנענטער לינק',
	'flow-topic-action-watchlist' => 'אויפֿפאַסונג ליסטע',
	'flow-topic-action-edit-title' => 'רעדאקטירן טיטל',
	'flow-topic-action-history' => 'טעמע היסטאריע',
	'flow-error-delete-failure' => 'אויסמעקן דעם אביעקט אדורכגעפאלן.',
	'flow-error-hide-failure' => 'באהאלטן דעם אביעקט אדורכגעפאלן.',
	'flow-error-restore-failure' => 'צוריקשטעלן דעם אביעקט אדורכגעפאלן.',
	'flow-edit-header-submit' => 'אויפהיטן קעפל.',
	'flow-edit-title-submit' => 'ענדערן טיטל',
	'flow-rev-message-new-post' => '$1 {{GENDER:$2|האט געשאפן}} די טעמע [$3 $4].',
	'flow-rev-message-edit-title' => '$1 {{GENDER:$2|האט געענדערט}} דעם טעמע טיטל צו [$3 $4] פון $5.',
	'flow-rev-message-create-header' => '$1  {{GENDER:$2|האט באשאפן}} דאס טאוול קעפל.',
	'flow-rev-message-edit-header' => '$1 {{GENDER:$2|האט רעדאקטירט}} דאס טאוול קעפל.',
	'flow-rev-message-hid-post' => '$1 {{GENDER:$2|האט באהאלטן}} א [$4 הערה] (<em>$5</em>).',
	'flow-rev-message-restored-post' => '$1 {{GENDER:$2|האט צוריקגעשטעלט}} א [$4 הערה] (<em>$5</em>).',
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
 * @author Liuxinyu970226
 * @author Mys 721tx
 * @author Qiyue2001
 * @author TianyinLee
 * @author Yfdyh000
 */
$messages['zh-hans'] = array(
	'flow-talk-taken-over' => '此讨论页已被[https://www.mediawiki.org/wiki/Special:MyLanguage/Flow_Portal Flow board]接管。',
	'logentry-delete-flow-delete-post' => '$1在[[$3]]{{GENDER:$2|删除}}了一个[$4 帖子]',
	'logentry-delete-flow-restore-post' => '$1在[[$3]]{{GENDER:$2|恢复}}了一个[$4 帖子]',
	'logentry-suppress-flow-restore-post' => '$1在[[$3]]{{GENDER:$2|删除}}了一个[$4 帖子]',
	'flow-edit-header-link' => '编辑页顶',
	'flow-header-empty' => '此讨论页目前没有头部。',
	'flow-post-moderated-toggle-show' => '[显示]',
	'flow-post-moderated-toggle-hide' => '[隐藏]',
	'flow-hide-content' => '已由$1隐藏',
	'flow-delete-content' => '由$1删除',
	'flow-suppress-content' => '已由$1抑制',
	'flow-suppress-usertext' => "''用户名已压制''", # Fuzzy
	'flow-post-actions' => '操作',
	'flow-topic-actions' => '操作',
	'flow-cancel' => '取消',
	'flow-preview' => '预览',
	'flow-newtopic-title-placeholder' => '新主题',
	'flow-newtopic-content-placeholder' => '消息正文。祝好！', # Fuzzy
	'flow-newtopic-header' => '添加新主题',
	'flow-newtopic-save' => '添加主题',
	'flow-newtopic-start-placeholder' => '开启一个新话题',
	'flow-reply-topic-placeholder' => '在“$2”发表的{{GENDER:$1|评论}}',
	'flow-reply-placeholder' => '{{GENDER:$1|回复}}$1',
	'flow-reply-submit' => '{{GENDER:$1|帖子回复}}',
	'flow-reply-link' => '{{GENDER:$1|回复}}',
	'flow-thank-link' => '{{GENDER:$1|感谢}}',
	'flow-talk-link' => '讨论{{GENDER:$1|$1}}',
	'flow-edit-post-submit' => '提交更改',
	'flow-post-edited' => '评论由$1 $2{{GENDER:$1|编辑}}',
	'flow-post-action-view' => '永久链接',
	'flow-post-action-post-history' => '发布历史',
	'flow-post-action-suppress-post' => '压制',
	'flow-post-action-delete-post' => '删除',
	'flow-post-action-hide-post' => '隐藏',
	'flow-post-action-edit-post' => '编辑帖子',
	'flow-post-action-edit' => '编辑',
	'flow-post-action-restore-post' => '恢复帖子',
	'flow-topic-action-view' => '永久链接',
	'flow-topic-action-watchlist' => '监视列表',
	'flow-topic-action-edit-title' => '编辑标题',
	'flow-topic-action-history' => '主题历史',
	'flow-topic-action-hide-topic' => '隐藏主题',
	'flow-topic-action-delete-topic' => '删除主题',
	'flow-error-http' => '与服务器联系时出错。',
	'flow-error-other' => '出现意外的错误。',
	'flow-error-edit-restricted' => '您无权编辑此帖子。',
	'flow-error-external-multi' => '遇到错误。<br />$1',
	'flow-error-missing-content' => '帖子无内容。只能保存有内容的帖子。', # Fuzzy
	'flow-error-delete-failure' => '删除本项失败。',
	'flow-error-hide-failure' => '隐藏此项失败。',
	'flow-error-restore-failure' => '恢复此项失败。',
	'flow-error-invalid-moderation-state' => 'moderationState 提供了无效的值',
	'flow-error-title-too-long' => '主题标题需小于$1字节。',
	'flow-error-no-existing-workflow' => '此工作流尚不存在。',
	'flow-error-missing-prev-revision-identifier' => '上一修订的标识符缺失。',
	'flow-error-prev-revision-mismatch' => '无法保存编辑：最近有更新的更改已被提交。',
	'flow-error-prev-revision-does-not-exist' => '无法找到以前的版本。',
	'flow-edit-header-submit' => '保存页顶',
	'flow-edit-title-submit' => '更改标题',
	'flow-rev-message-edit-post' => '$1{{GENDER:$2|编辑了}}一个[$3 评论]。',
	'flow-rev-message-reply' => '$1{{GENDER:$2|添加了}}一个[$3 评论]。',
	'flow-rev-message-create-header' => '$1已创建的页顶。', # Fuzzy
	'flow-rev-message-edit-header' => '$1已编辑的页顶。', # Fuzzy
	'flow-rev-message-deleted-post' => '$1删除了[$4 评论]', # Fuzzy
	'flow-board-history' => '“$1”的历史',
	'flow-topic-history' => '“$1”主题的历史',
	'flow-history-last4' => '过去4个小时',
	'flow-history-day' => '今天',
	'flow-history-week' => '上周',
	'flow-topic-comments' => '{{PLURAL:$1|$1个评论|$1个评论|0=第一个发表评论！}}',
	'flow-comment-restored' => '恢复的评论',
	'flow-comment-deleted' => '已删除的评论',
	'flow-comment-hidden' => '隐藏的评论',
	'flow-paging-rev' => '更多最新主题',
	'flow-paging-fwd' => '更早的话题',
	'flow-notification-edit' => '$1{{GENDER:$1|删除}}了一个在$2的[[$3|$4]]的[$5 评论]。',
	'flow-notification-newtopic' => '$1在[[$2|$3]]{{GENDER:$1|创建了}}一个[$5 新话题]：$4。',
	'flow-notification-rename' => '[$2 $3]的标题已被$1在[[$5|$6]]{{GENDER:$1|更改}}为“$4”。',
	'flow-notification-link-text-view-post' => '浏览帖子',
	'flow-notification-link-text-view-board' => '查看讨论版',
	'flow-notification-link-text-view-topic' => '查看主题',
	'flow-notification-reply-email-subject' => '$1回复了您的帖子',
	'flow-notification-reply-email-batch-body' => '$1回复了您在$3的帖子$2',
	'flow-notification-edit-email-subject' => '$1编辑了您的帖子',
	'flow-notification-edit-email-batch-body' => '$1在编辑了您在“$3”的主题$2上的帖子',
	'flow-notification-rename-email-subject' => '$1重命名了您的主题',
	'flow-notification-rename-email-batch-body' => '$1将您在“$4”的主题“$2”重命名为“$3”',
	'flow-notification-newtopic-email-subject' => '$1在$2创建了新主题',
	'echo-category-title-flow-discussion' => '$1个讨论', # Fuzzy
	'echo-pref-tooltip-flow-discussion' => '在讨论版发生有关我的动作时通知我。', # Fuzzy
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
	'flow-moderation-title-suppress-topic' => '抑制主题？',
	'flow-moderation-title-delete-topic' => '删除主题?',
	'flow-moderation-title-hide-topic' => '隐藏主题？',
	'flow-moderation-title-restore-topic' => '还原主题？',
	'flow-moderation-confirm-suppress-topic' => '抑制',
	'flow-moderation-confirm-delete-topic' => '删除',
	'flow-moderation-confirm-hide-topic' => '隐藏',
	'flow-moderation-confirm-restore-topic' => '恢复',
	'flow-topic-permalink-warning' => '本主题已在[$2 $1]开启',
	'flow-topic-permalink-warning-user-board' => '本主题已在[$2 $1的通告版]开启',
	'flow-topic-collapsed-one-line' => '小型视图',
	'flow-topic-collapsed-full' => '折叠视图',
	'flow-topic-complete' => '完整视图',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Cwlin0416
 */
$messages['zh-hant'] = array(
	'flow-notification-reply' => '$1 {{GENDER:$1|已回覆}}您的 [$5 留言] 於 $2 的 "$4"。',
	'flow-notification-reply-bundle' => '$1 與另外 $5 {{PLURAL:$6|個人|個人}}已{{GENDER:$1|回覆}}您的 [$4 留言] 於 $2 的 "$3"。',
	'flow-notification-link-text-view-post' => '檢視留言',
	'flow-notification-link-text-view-board' => '檢視討論版',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|已回覆}}您的留言',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|已回覆}}您的留言於 $2 的 "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 與另外 $4 {{PLURAL:$5|個人|個人}} {{GENDER:$1|已回覆}} 您的留言於 $2 的 "$3"',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|討論|討論}}', # Fuzzy
	'echo-pref-tooltip-flow-discussion' => '通知我，當有與我相關的動作發生在討論版時', # Fuzzy
);
