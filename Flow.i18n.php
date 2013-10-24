<?php

// Internationalisation file for Flow extension.

$messages = array();

/**
 * English
 */
$messages['en'] = array(
	'flow-desc' => 'Workflow management system',
	'flow-specialpage' => '$1 &ndash; Flow',

	'flow-user-anonymous' => 'Anonymous',
	'flow-user-moderated' => 'Moderated user',

	'flow-edit-header-link' => 'Edit header',
	'flow-header-empty' => 'This talk page currently has no header.',

	'flow-post-moderated-toggle-show' => '[Show]',
	'flow-post-moderated-toggle-hide' => '[Hide]',
	'flow-post-hidden' => '[post hidden]',
	'flow-post-hidden-by' => '{{GENDER:$1|Hidden}} by $1 $2',
	'flow-post-deleted' => '[post deleted]',
	'flow-post-deleted-by' => '{{GENDER:$1|Deleted}} by $1 $2',
	'flow-post-censored' => '[post censored]',
	'flow-post-censored-by' => '{{GENDER:$1|Censored}} by $1 $2',
	'flow-post-actions' => 'actions',
	'flow-topic-actions' => 'actions',
	'flow-cancel' => 'Cancel',

	'flow-newtopic-title-placeholder' => 'Message subject',
	'flow-newtopic-content-placeholder' => 'Message text. Be nice!',
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
	'flow-post-action-censor-post' => 'Censor post',
	'flow-post-action-delete-post' => 'Delete post',
	'flow-post-action-hide-post' => 'Hide post',
	'flow-post-action-edit-post' => 'Edit post',
	'flow-post-action-edit' => 'Edit',
	'flow-post-action-restore-post' => 'Restore post',

	'flow-topic-action-view' => 'Permalink',
	'flow-topic-action-watchlist' => 'Watchlist',
	'flow-topic-action-edit-title' => 'Edit title',
	'flow-topic-action-history' => 'Topic history',

	'flow-error-http' => 'An error occurred while contacting the server. Your post was not saved.', // Needs real copy
	'flow-error-other' => 'An unexpected error occurred. Your post was not saved.',
	'flow-error-external' => 'An error occurred while saving your post. Your post was not saved.<br /><small>The error message received was: $1</small>',
	'flow-error-edit-restricted' => 'You are not allowed to edit this post.',
	'flow-error-external-multi' => 'Errors were encountered while saving your post. Your post was not saved.<br />$1',

	'flow-error-missing-content' => 'Post has no content. Content is required to save a new post.',
	'flow-error-missing-title' => 'Topic has no title. Title is required to save a new topic.',
	'flow-error-parsoid-failure' => 'Unable to parse content due to a Parsoid failure.',
	'flow-error-missing-replyto' => 'No "replyTo" parameter was supplied. This parameter is required for the "reply" action.',
	'flow-error-invalid-replyto' => '"replyTo" parameter was invalid. The specified post could not be found.',
	'flow-error-delete-failure' => 'Deletion of this item failed.',
	'flow-error-hide-failure' => 'Hiding this item failed.',
	'flow-error-missing-postId' => 'No "postId" parameter was supplied. This parameter is required to manipulate a post.',
	'flow-error-invalid-postId' => '"postId" parameter was invalid. The specified post ($1) could not be found.',
	'flow-error-restore-failure' => 'Restoration of this item failed.',
	'flow-error-invalid-moderation-state' => 'An invalid value was provided for moderationState',
	'flow-error-not-allowed' => 'Insufficient permissions to execute this action',

	'flow-edit-header-submit' => 'Save header',

	'flow-edit-title-submit' => 'Change title',

	'flow-rev-message-edit-post' => 'Edited post content',
	'flow-rev-message-reply' => 'New reply posted',
	'flow-rev-message-new-post' => 'Topic created',
	'flow-rev-message-hid-post' => 'Hid post',
	'flow-rev-message-edit-title' => 'Edited topic title',
	'flow-rev-message-create-header' => 'Created header',
	'flow-rev-message-edit-header' => 'Edited header',
	'flow-rev-message-restored-post' => 'Unhid post',
	'flow-rev-message-hid-post' => 'Hid post',
	'flow-rev-message-deleted-post' => 'Deleted post',
	'flow-rev-message-censored-post' => 'Censored post',

	'flow-topic-history' => 'Topic history',
	'flow-topic-participants' => '{{PLURAL:$1|$3 started this topic|{{GENDER:$3|$3}}, {{GENDER:$4|$4}} and {{PLURAL:$2|other|others}}|0=No participation yet|2={{GENDER:$3|$3}} and {{GENDER:$4|$4}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Be the first to comment!|Comment ($1)}}',

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
	'flow-notification-mention' => '$1 {{GENDER:$1|mentioned}} you in their [$2 post] in "$3" on "$4"',

	// Notification primary links and secondary links
	'flow-notification-link-text-view-post' => 'View post',
	'flow-notification-link-text-view-board' => 'View board',
	'flow-notification-link-text-view-topic' => 'View topic',

	// Notification Email messages
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|replied}} to your post',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|replied}} to your post in $2 on "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 and $4 {{PLURAL:$5|other|others}} {{GENDER:$1|replied}} to your post in $2 on "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|mentioned}} you on $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|mentioned}} you in their post in "$2" on "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|edited}} your post',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|edited}} your post in $2 on "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 and $4 {{PLURAL:$5|other|others}} {{GENDER:$1|edited}} a post in $2 on "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|renamed}} your topic',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|renamed}} your topic "$2" to "$3" on "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|created}} a new topic on $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|created}} a new topic with the title "$2" on $3',

	// Notification preference
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Discussion|Discussions}}',
	'echo-pref-tooltip-flow-discussion' => 'Notify me when actions related to me occur in the discussion board.',

	// Link text used throughout flow for action menus and the like
	'flow-link-post' => 'post',
	'flow-link-topic' => 'topic',
	'flow-link-history' => 'history',

	// Moderation dialog
	'flow-moderation-title-censor' => 'Censor post',
	'flow-moderation-title-delete' => 'Delete post',
	'flow-moderation-title-hide'   => 'Hide post',
	'flow-moderation-title-restore'=> 'Restore post.',
	'flow-moderation-intro-censor' => 'Please confirm that you wish to censor the post by {{GENDER:$1|$1}} in the thread "$2", and provide a reason for your action.',
	'flow-moderation-intro-delete' => 'Please confirm that you wish to delete the post by {{GENDER:$1|$1}} in the thread "$2", and provide a reason for your action.',
	'flow-moderation-intro-hide'   => 'Please confirm that you wish to hide the post by {{GENDER:$1|$1}} in the thread "$2", and provide a reason for your action.',
	'flow-moderation-intro-restore'=> 'Please confirm that you wish to restore the post by {{GENDER:$1|$1}} in the thread "$2", and provide a reason for youra ction.',
	'flow-moderation-reason' => 'Reason:',
	'flow-moderation-confirm' => 'Confirm action',
	'flow-moderation-confirmation' => 'You have successfully moderated this post.
Please consider giving $1 feedback about {{GENDER:$1|his|her|their}} post.',
	'flow-moderation-confirmation-restore' => 'You have successfully restored this post.',
	'flow-moderation-reason-placeholder' => 'Enter your reason here',
);

/** Message documentation (Message documentation)
 * @author Beta16
 * @author Raymond
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'flow-desc' => '{{desc|name=Flow|url=http://www.mediawiki.org/wiki/Extension:Flow}}',
	'flow-specialpage' => 'Used as page title in [[Special:Flow]]. Parameters:
* $1 - page title',
	'flow-user-anonymous' => 'Name to display for anonymous users.
{{Identical|Anonymous}}',
	'flow-user-moderated' => 'Name to display instead of a moderated user name',
	'flow-edit-header-link' => 'Used as text for the link which points to the "Edit header" page.',
	'flow-header-empty' => 'Used as a placeholder text for headers which have no content.',
	'flow-post-moderated-toggle-show' => 'Text for link used to display a moderated post',
	'flow-post-moderated-toggle-hide' => 'Text for link used to hide a moderated post',
	'flow-post-hidden' => 'Used as username/content if the post was hidden.

Translate this as "post which was hidden".',
	'flow-post-hidden-by' => 'Parameters:
* $1 - username that hid the post, can be used for GENDER
* $2 - timestamp, relative to post creation date, of when the post was hidden. Any one of the following:
** timestamp (time and date); localized
** day and month; localized
** {{msg-mw|Sunday-at}}, {{msg-mw|Monday-at}}, {{msg-mw|Tuesday-at}}, ...
** {{msg-mw|Yesterday-at}}, {{msg-mw|Today-at}}
{{Related|Flow-post-by}}',
	'flow-post-deleted' => 'Used as username/content if the post was deleted.

Translate this as "post which was deleted".',
	'flow-post-deleted-by' => 'Parameters:
* $1 - username that deleted the post, can be used for GENDER
* $2 - timestamp, relative to post creation date, of when the post was deleted. Any one of the following:
** timestamp (time and date); localized
** day and month; localized
** {{msg-mw|Sunday-at}}, {{msg-mw|Monday-at}}, {{msg-mw|Tuesday-at}}, ...
** {{msg-mw|Yesterday-at}}, {{msg-mw|Today-at}}
{{Related|Flow-post-by}}',
	'flow-post-censored' => 'Used as username/content if the post was censored.

Translate this as "post which was censored".',
	'flow-post-censored-by' => 'Parameters:
* $1 - username that censored the post, can be used for GENDER
* $2 - timestamp, relative to post creation date, of when the post was censored. Any one of the following:
** timestamp (time and date); localized
** day and month; localized
** {{msg-mw|Sunday-at}}, {{msg-mw|Monday-at}}, {{msg-mw|Tuesday-at}}, ...
** {{msg-mw|Yesterday-at}}, {{msg-mw|Today-at}}
{{Related|Flow-post-by}}',
	'flow-post-actions' => 'Used as link text.
{{Identical|Action}}',
	'flow-topic-actions' => 'Used as link text.
{{Identical|Action}}',
	'flow-cancel' => 'Used as action link text.
{{Identical|Cancel}}',
	'flow-newtopic-title-placeholder' => 'Used as placeholder for the "Subject/Title for topic" textarea.',
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
	'flow-reply-link' => 'Link text of the button that will (when clicked) display the editor to reply. Parameters:
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
	'flow-post-action-censor-post' => 'Used as a label for  the submit button in the censor form.

See also:
* {{msg-mw|Flow-post-action-restore-post}}',
	'flow-post-action-delete-post' => 'Used as a label for the submit button in the deletion form.

See also:
* {{msg-mw|Flow-post-action-restore-post}}',
	'flow-post-action-hide-post' => 'Used as label for the Submit button.',
	'flow-post-action-edit-post' => 'Used as text for the link which is used to edit the post.',
	'flow-post-action-edit' => 'Unused at this time.

Translate as label for the link or the Submit button.
{{Identical|Edit}}',
	'flow-post-action-restore-post' => 'Used as label for the Submit button.

See also:
* {{msg-mw|Flow-post-action-delete-post}}
* {{msg-mw|Flow-post-action-censor-post}}',
	'flow-topic-action-view' => "Title text for topic's permalink icon.
{{Identical|Permalink}}",
	'flow-topic-action-watchlist' => "Title text for topic's watchlist icon.
{{Identical|Watchlist}}",
	'flow-topic-action-edit-title' => 'Used as title for the link which is used to edit the title.',
	'flow-topic-action-history' => 'Used as text for the link which is used to view topic-history.
{{Identical|Topic history}}',
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
	'flow-error-missing-content' => 'Used as error message.',
	'flow-error-missing-title' => 'Used as error message.',
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

$1 will contain the postId that was specified.',
	'flow-error-restore-failure' => 'Used as error message when restoring a post.

"this item" seems to refer "this post".',
	'flow-error-invalid-moderation-state' => 'Used as error message.
Usually indicates a code bug, so technical terminology is okay.',
	'flow-error-not-allowed' => 'Insufficient permissions to execute this action',

	'flow-edit-header-submit' => 'Used as label for the Submit button.',
	'flow-edit-title-submit' => 'Used as label for the Submit button.',
	'flow-rev-message-edit-post' => 'Used as a revision comment when a post has been edited',
	'flow-rev-message-reply' => 'Used as a revision comment when a new reply has been posted.',
	'flow-rev-message-new-post' => 'Used as revision comment when the topic has been created.',
	'flow-rev-message-hid-post' => 'Used as revision comment when a post has been hidden',
	'flow-rev-message-edit-title' => 'Used as revision comment when a post has been edited',
	'flow-rev-message-create-header' => 'Used as revision comment when a header has been created',
	'flow-rev-message-edit-header' => 'Used as revision comment when a header has been edited',
	'flow-rev-message-restored-post' => 'Used as revision comment when a post has been restored(un-hidden)',
	'flow-rev-message-deleted-post' => 'Used as revision comment when a post has been deleted',
	'flow-rev-message-censored-post' => 'Used as revision comment when a post has been censored(oversighted)',
	'flow-topic-history' => 'Used as <code><nowiki><h2></nowiki></code> heading in the "Topic history" page.
{{Identical|Topic history}}',
	'flow-topic-participants' => 'Message to display the amount of participants in this discussion (and potentially a couple of names).

Parameters:
* $1 - the total amount of participants in the conversation, can be used for PLURAL
* $2 - the total amount of participants minus 2, can be used to generate a message like: X, Y and $2 others ($3 and $4 will be usernames)
* $3 - username of the topic starter, can be used for GENDER
* $4 - username of a second participant (if there is a second, otherwise not available), can be used for GENDER',
	'flow-topic-comments' => 'Message to display the amount of comments in this discussion. Parameters:
* $1 - The amount of comments on this topic, can be used for PLURAL',
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
* {{msg-mw|Flow-notification-reply-email-batch-bundle-body}}',
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
* {{msg-mw|Flow-notification-edit-email-batch-bundle-body}}',
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
	'flow-notification-mention' => 'Notification text for when a user is mentioned in another conversation. Parameters:
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
	'flow-notification-mention-email-batch-body' => 'Email notification body when a user is mentioned in a post, this message is used in both single email and email digest.

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
* $1 - name of the user that created a new topic',
	'flow-notification-newtopic-email-batch-body' => 'Email notification for new topic creation. Parameters:
* $1 - name of the user that created a new topic
* $2 - the title of the new topic
* $3 - title of the page the topic belongs to',
	'echo-category-title-flow-discussion' => 'This is a short title for notification category.  Parameters:
* $1 - number of mentions, for PLURAL support

{{Related|Echo-category-title}}
{{Identical|Discussion}}',
	'echo-pref-tooltip-flow-discussion' => 'This is a short description of the flow-discussion notification category.',
	'flow-link-post' => 'Text used when linking to a post from recentchanges.
{{Identical|Post}}',
	'flow-link-topic' => 'Text used when linking to a topic from recentchanges.
{{Identical|Topic}}',
	'flow-link-history' => 'Text used when linking to history of a post/topic from recentchanges.
{{Identical|History}}',

	'flow-moderation-title-censor' => 'Title for the moderation confirmation dialog when a post is being censored.',
	'flow-moderation-title-delete' => 'Title for the moderation confirmation dialog when a post is being deleted.',
	'flow-moderation-title-hide'   => 'Title for the moderation confirmation dialog when a post is being hidden.',
	'flow-moderation-title-restore'   => 'Title for the moderation confirmation dialog when a post is being restored.',
	'flow-moderation-intro-censor' => 'Intro for the moderation confirmation dialog when a post is being censored. Parameters:
* $1 the name of the user whose post is being censored. GENDER supported.
* $2 the subject of the topic in which a post is being censored.',
	'flow-moderation-intro-restore' => 'Intro for the restore confirmation dialog. Parameters:
* $1 the name of the user whose post is being censored. GENDER supported.
* $2 the subject of the topic in which a post is being censored.',
	'flow-moderation-intro-delete' => 'Intro for the moderation confirmation dialog when a post is being deleted. Parameters:
* $1 the name of the user whose post is being deleted. GENDER supported.
* $2 the subject of the topic in which a post is being deleted.',
	'flow-moderation-intro-hide'   => 'Intro for the moderation confirmation dialog when a post is being hidden. Parameters:
* $1 the name of the user whose post is being hidden. GENDER supported.
* $2 the subject of the topic in which a post is being hidden.',
	'flow-moderation-reason' => 'Prompt for a text field in which the user should fill in the reason for their action.',
	'flow-moderation-confirm' => 'Label for a button that will confirm a moderation action.',
	'flow-moderation-confirmation' => 'Message displayed after a successful moderation action. Parameters:
* $1 the name of the user whose post is being moderated. GENDER supported.',
	'flow-moderation-confirmation-restore' => 'Message displayed after a successful reversal of a moderation action. Parameters:
* $1 the name of the user whose post is being restored. GENDER supported.',
	'flow-moderation-reason-placeholder' => 'Placeholder text for the textbox that holds the reason field on moderation confirmation dialogs.',
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
	'flow-user-anonymous' => 'Dizanv',
	'flow-cancel' => 'Nullañ',
	'flow-post-action-edit' => 'Kemmañ',
	'flow-topic-action-edit-title' => 'Kemmañ an titl',
);

/** Czech (česky)
 * @author Michaelbrabec
 */
$messages['cs'] = array(
	'flow-cancel' => 'Storno',
	'flow-newtopic-title-placeholder' => 'Předmět zprávy',
	'flow-topic-action-edit-title' => 'Upravit název',
);

/** German (Deutsch)
 * @author Metalhead64
 */
$messages['de'] = array(
	'flow-desc' => 'Workflow-Management-System',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-user-anonymous' => 'Anonym',
	'flow-user-moderated' => 'Moderierter Benutzer',
	'flow-edit-header-link' => 'Überschrift bearbeiten',
	'flow-header-empty' => 'Diese Diskussionsseite hat derzeit keine Überschrift.',
	'flow-post-moderated-toggle-show' => '[Anzeigen]',
	'flow-post-moderated-toggle-hide' => '[Ausblenden]',
	'flow-post-hidden' => '[Beitrag versteckt]',
	'flow-post-hidden-by' => '{{GENDER:$1|Versteckt}} von $1 $2',
	'flow-post-deleted' => '[Beitrag gelöscht]',
	'flow-post-deleted-by' => '{{GENDER:$1|Gelöscht}} von $1 $2',
	'flow-post-censored' => '[Beitrag zensiert]',
	'flow-post-censored-by' => '{{GENDER:$1|Zensiert}} von $1 $2',
	'flow-post-actions' => 'Aktionen',
	'flow-topic-actions' => 'Aktionen',
	'flow-cancel' => 'Abbrechen',
	'flow-newtopic-title-placeholder' => 'Betreff der Nachricht',
	'flow-newtopic-content-placeholder' => 'Nachrichtentext. Sei freundlich!',
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
	'flow-post-action-post-history' => 'Beitragsgeschichte',
	'flow-post-action-censor-post' => 'Beitrag zensieren',
	'flow-post-action-delete-post' => 'Beitrag löschen',
	'flow-post-action-hide-post' => 'Beitrag verstecken',
	'flow-post-action-edit-post' => 'Beitrag bearbeiten',
	'flow-post-action-edit' => 'Bearbeiten',
	'flow-post-action-restore-post' => 'Beitrag wiederherstellen',
	'flow-topic-action-view' => 'Permanentlink',
	'flow-topic-action-watchlist' => 'Beobachtungsliste',
	'flow-topic-action-edit-title' => 'Titel bearbeiten',
	'flow-topic-action-history' => 'Themengeschichte',
	'flow-error-http' => 'Beim Kontaktieren des Servers ist ein Fehler aufgetreten. Dein Beitrag wurde nicht gespeichert.',
	'flow-error-other' => 'Ein unerwarteter Fehler ist aufgetreten. Dein Beitrag wurde nicht gespeichert.',
	'flow-error-external' => 'Beim Speichern deines Beitrags ist ein Fehler aufgetreten. Dein Beitrag wurde nicht gespeichert.<br /><small>Die empfangene Fehlermeldung lautete: $1</small>',
	'flow-error-edit-restricted' => 'Du bist nicht berechtigt, diesen Beitrag zu bearbeiten.',
	'flow-error-external-multi' => 'Beim Speichern deines Beitrags sind Fehler aufgetreten. Dein Beitrag wurde nicht gespeichert. <br /> $1',
	'flow-error-missing-content' => 'Der Beitrag hat keinen Inhalt. Dieser ist erforderlich, um einen neuen Beitrag zu speichern.',
	'flow-error-missing-title' => 'Das Thema hat keinen Titel. Dieser ist erforderlich, um ein neues Thema zu speichern.',
	'flow-error-parsoid-failure' => 'Aufgrund eines Parsoid-Fehlers konnte der Inhalt nicht geparst werden.',
	'flow-error-missing-replyto' => 'Es wurde kein Parameter „Antworten an“ angegeben. Dieser Parameter ist für die „Antworten“-Aktion erforderlich.',
	'flow-error-invalid-replyto' => 'Der Parameter „Antworten an“ war ungültig. Der angegebene Beitrag konnte nicht gefunden werden.',
	'flow-error-delete-failure' => 'Das Löschen dieses Objektes ist fehlgeschlagen.',
	'flow-error-hide-failure' => 'Das Verstecken dieses Objektes ist fehlgeschlagen.',
	'flow-error-missing-postId' => 'Es wurde kein Parameter „postId“ angegeben. Dieser Parameter ist zum Löschen/Wiederherstellen eines Beitrags erforderlich.',
	'flow-error-invalid-postId' => 'Der Parameter „postId“ war ungültig. Der angegebene Beitrag konnte nicht gefunden werden.',
	'flow-error-restore-failure' => 'Das Wiederherstellen dieses Objektes ist fehlgeschlagen.',
	'flow-edit-header-submit' => 'Überschrift speichern',
	'flow-edit-title-submit' => 'Titel ändern',
	'flow-rev-message-edit-post' => 'Beitragsinhalt bearbeitet',
	'flow-rev-message-reply' => 'Neue Antwort hinterlassen',
	'flow-rev-message-new-post' => 'Thema erstellt',
	'flow-rev-message-hid-post' => 'Beitrag versteckt',
	'flow-rev-message-edit-title' => 'Thementitel bearbeitet',
	'flow-rev-message-create-header' => 'Überschrift erstellt',
	'flow-rev-message-edit-header' => 'Überschrift bearbeitet',
	'flow-rev-message-restored-post' => 'Beitrag eingeblendet',
	'flow-rev-message-deleted-post' => 'Beitrag gelöscht',
	'flow-rev-message-censored-post' => 'Beitrag zensiert',
	'flow-topic-history' => 'Themengeschichte',
	'flow-topic-participants' => '{{PLURAL:$1|$3 startete dieses Thema|{{GENDER:$3|$3}}, {{GENDER:$4|$4}} und {{PLURAL:$2|ein anderer|andere}}|0=Noch keine Teilnehmer|2={{GENDER:$3|$3}} und {{GENDER:$4|$4}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Sei der erste!|Kommentieren ($1)}}',
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
	'flow-notification-mention' => '$1 erwähnte dich in {{GENDER:$1|seinem|ihrem}} [$2 Beitrag] in „$3“ auf der Seite „$4“',
	'flow-notification-link-text-view-post' => 'Beitrag ansehen',
	'flow-notification-link-text-view-board' => 'Brett ansehen',
	'flow-notification-link-text-view-topic' => 'Thema ansehen',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|antwortete}} auf deinen Beitrag',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|antwortete}} auf deinen Beitrag in $2 auf „$3“',
	'flow-notification-reply-email-batch-bundle-body' => '$1 und {{PLURAL:$5|ein anderer|$4 andere}} {{GENDER:$1|antworteten}} auf deinen Beitrag in $2 auf „$3“',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|erwähnte}} dich auf $2',
	'flow-notification-mention-email-batch-body' => '$1 erwähnte dich in {{GENDER:$1|seinem|ihrem}} Beitrag in „$2“ auf der Seite „$3“',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|bearbeitete}} deinen Beitrag',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|bearbeitete}} deinen Beitrag in $2 auf der Seite „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 und {{PLURAL:$5|ein anderer|$4 andere}} {{GENDER:$1|bearbeiteten}} einen Beitrag in $2 auf der Seite „$3“',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|benannte}} dein Thema um',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|benannte}} dein Thema „$2“ in „$3“ auf der Seite „$4“ um',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|erstellte}} ein neues Thema auf $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|erstellte}} ein neues Thema mit dem Titel „$2“ auf $3',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Diskussion|Diskussionen}}',
	'echo-pref-tooltip-flow-discussion' => 'Benachrichtige mich, wenn mich betreffende Aktionen auf dem Diskussionsbrett erscheinen.',
	'flow-link-post' => 'Beitrag',
	'flow-link-topic' => 'Thema',
	'flow-link-history' => 'Versionsgeschichte',
);

/** Spanish (español)
 * @author Ovruni
 */
$messages['es'] = array(
	'flow-post-deleted-by' => '{{GENDER:$1|Eliminado}} por $1 $2',
	'flow-post-actions' => 'acciones',
	'flow-topic-actions' => 'acciones',
	'flow-cancel' => 'Cancelar',
	'flow-newtopic-title-placeholder' => 'Asunto del mensaje',
	'flow-newtopic-header' => 'Añadir un nuevo tema',
	'flow-newtopic-save' => 'Añadir tema',
	'flow-newtopic-start-placeholder' => 'Haga clic aquí para iniciar una nueva discusión. Ser amable!', # Fuzzy
	'flow-reply-placeholder' => 'Haga clic para {{GENDER:$1|responder}} a $1. Ser amable!',
	'flow-reply-submit' => 'Publicar respuesta', # Fuzzy
	'flow-edit-post-submit' => 'Enviar cambios',
	'flow-post-edited' => 'Mensaje {{GENDER:$1|editado}} por $1 $2',
	'flow-post-action-view' => 'Enlace permanente',
	'flow-post-action-post-history' => 'Publicar historia',
	'flow-post-action-censor-post' => 'Censurar mensaje',
	'flow-post-action-delete-post' => 'Eliminar mensaje',
	'flow-post-action-hide-post' => 'Ocultar mensaje',
	'flow-post-action-edit-post' => 'Editar mensaje',
	'flow-post-action-edit' => 'Editar',
	'flow-post-action-restore-post' => 'Restaurar mensaje',
	'flow-topic-action-edit-title' => 'Editar título',
);

/** Finnish (suomi)
 * @author Nike
 * @author Stryn
 */
$messages['fi'] = array(
	'flow-edit-summary-link' => 'Muokkauksen yhteenveto',
	'flow-disclaimer' => 'Tallentamalla muutokset osoitat hyväksyväsi, että muokkauksesi julkaistaan pysyvästi Creative Commons Nimeä-Tarttuva 3.0- ja GFDL-lisenssien ehdoin. Hyväksyt, että hyperlinkki tai URL on riittävä Creative Commons -lisenssillä.',
	'flow-post-hidden' => '[viesti piilotettu]',
	'flow-post-deleted' => '[viesti poistettu]',
	'flow-post-censored' => '[viesti sensuroitu]',
	'flow-post-actions' => 'toiminnot',
	'flow-topic-actions' => 'toiminnot',
	'flow-cancel' => 'Peru',
	'flow-newtopic-title-placeholder' => 'Viestin aihe',
	'flow-newtopic-content-placeholder' => 'Viestin teksti. Ole mukava!',
	'flow-newtopic-header' => 'Lisää uusi aihe',
	'flow-newtopic-save' => 'Lisää aihe',
	'flow-newtopic-start-placeholder' => 'Aloita uusi aihe napsauttamalla tästä. Muistathan kohteliaat käytöstavat!',
	'flow-reply-placeholder' => 'Paina tästä vastataksesi käyttäjälle $1. Ole mukava!', # Fuzzy
	'flow-reply-submit' => 'Lähetä vastaus',
	'flow-edit-post-submit' => 'Lähetä muutokset',
	'flow-post-action-view' => 'Ikilinkki',
	'flow-post-action-edit' => 'Muokkaa',
	'flow-post-action-restore-post' => 'Palauta viesti',
	'flow-topic-action-edit-title' => 'Muokkaa otsikkoa',
	'flow-topic-action-history' => 'Aiheen historia',
	'flow-summaryedit-submit' => 'Tallenna yhteenveto',
	'flow-edit-title-submit' => 'Muuta otsikkoa',
);

/** French (français)
 * @author Gomoko
 * @author Sherbrooke
 */
$messages['fr'] = array(
	'flow-desc' => 'Système de gestion du flux de travail',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-user-anonymous' => 'Anonyme',
	'flow-user-moderated' => 'Utilisateur modéré',
	'flow-edit-header-link' => 'Modifier l’entête',
	'flow-header-empty' => 'Cette page de discussion n’a pas d’entête pour l’instant.',
	'flow-post-moderated-toggle-show' => '[Afficher]',
	'flow-post-moderated-toggle-hide' => '[Masquer]',
	'flow-post-hidden' => '[note masquée]',
	'flow-post-hidden-by' => '{{GENDER:$1|Masqué}} par $1 $2',
	'flow-post-deleted' => '[message supprimé]',
	'flow-post-deleted-by' => '{{GENDER:$1|Supprimé}} par $1 $2',
	'flow-post-censored' => '[note censurée]',
	'flow-post-censored-by' => '{{GENDER:$1|Censuré}} par $1 $2',
	'flow-post-actions' => 'actions',
	'flow-topic-actions' => 'actions',
	'flow-cancel' => 'Annuler',
	'flow-newtopic-title-placeholder' => 'Objet du message',
	'flow-newtopic-content-placeholder' => 'Texte du message. Soyez gentil !',
	'flow-newtopic-header' => 'Ajouter un nouveau sujet',
	'flow-newtopic-save' => 'Ajouter sujet',
	'flow-newtopic-start-placeholder' => 'Commencer un nouveau sujet',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Commenter}} « $2 »',
	'flow-reply-placeholder' => 'Cliquez ici pour {{GENDER:$1|répondre}} à $1. Soyez gentil !', # Fuzzy
	'flow-reply-submit' => '{{GENDER:$1|Répondre}}',
	'flow-reply-link' => '{{GENDER:$1|Répondre}}',
	'flow-thank-link' => '{{GENDER:$1|Remercier}}',
	'flow-talk-link' => 'Parler à {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Soumettre les modifications',
	'flow-post-edited' => 'Note {{GENDER:$1|modifiée}} par $1 $2',
	'flow-post-action-view' => 'Lien permanent',
	'flow-post-action-post-history' => 'Historique des publications',
	'flow-post-action-censor-post' => 'Censurer la note',
	'flow-post-action-delete-post' => 'Supprimer le message',
	'flow-post-action-hide-post' => 'Masquer la note',
	'flow-post-action-edit-post' => 'Modifier la publication',
	'flow-post-action-edit' => 'Modifier',
	'flow-post-action-restore-post' => 'Restaurer le message',
	'flow-topic-action-view' => 'Lien permanent',
	'flow-topic-action-watchlist' => 'Liste de surveillance',
	'flow-topic-action-edit-title' => 'Modifier le titre',
	'flow-topic-action-history' => 'Historique des sujets',
	'flow-error-http' => "Une erreur s'est produite en communiquant avec le serveur. Votre message n'a pas été enregistré.",
	'flow-error-other' => "Une erreur inattendue s'est produite. Votre message n'a pas été enregistré.",
	'flow-error-external' => "Une erreur s'est produite lors de l'enregistrement de votre message. Il n'a pas été enregistré.<br /><small>Le message d'erreur reçu était :$1</small>",
	'flow-error-edit-restricted' => 'Vous n’êtes pas autorisé à modifier cette note',
	'flow-error-external-multi' => "Des erreurs se sont produites lors de l'enregistrement de votre message. Votre message n'a pas été enregistré.<br /> $1",
	'flow-error-missing-content' => "Le message n'a aucun contenu. C'est requis pour enregistrer un nouveau message.",
	'flow-error-missing-title' => "Le sujet n'a aucun titre. C'est requis pour enregistrer un nouveau sujet.",
	'flow-error-parsoid-failure' => "Impossible d'analyser le contenu en raison d'une panne de Parsoid.",
	'flow-error-missing-replyto' => 'Aucun paramètre « replyTo » n’a été fourni. Ce paramètre est requis pour l’action « répondre ».',
	'flow-error-invalid-replyto' => 'Le paramètre « replyTo » n’était pas valide. Le message spécifié n’a pas pu être trouvé.',
	'flow-error-delete-failure' => 'Échec de la suppression de cette entrée.',
	'flow-error-hide-failure' => 'Le masquage de cet élément a échoué.',
	'flow-error-missing-postId' => 'Aucun paramètre « postId » n’a été fourni. Ce paramètre est obligatoire pour manipuler un message.',
	'flow-error-invalid-postId' => 'Le paramètre « postId » n’était pas valide. Le message spécifié n’a pas pu être trouvé.',
	'flow-error-restore-failure' => 'Échec de la restauration de cette entrée.',
	'flow-edit-header-submit' => 'Enregistrer l’entête',
	'flow-edit-title-submit' => 'Changer le titre',
	'flow-rev-message-edit-post' => 'Contenu de la note modifié',
	'flow-rev-message-reply' => 'Nouvelle réponse publiée',
	'flow-rev-message-new-post' => 'Sujet créé',
	'flow-rev-message-hid-post' => 'Masquer la note',
	'flow-rev-message-edit-title' => 'Titre du sujet modifié',
	'flow-rev-message-create-header' => 'Entête créé',
	'flow-rev-message-edit-header' => 'Entête modifié',
	'flow-rev-message-restored-post' => 'Note démasquée',
	'flow-rev-message-deleted-post' => 'Note supprimée',
	'flow-rev-message-censored-post' => 'Note censurée',
	'flow-topic-history' => 'Historique des sujets',
	'flow-topic-participants' => '{{PLURAL:$1|0=Encore aucune participation|$3 a démarré ce sujet|{{GENDER:$3|$3}} et {{GENDER:$4|$4}}|{{GENDER:$3|$3}}, {{GENDER:$4|$4}} et $2 autres}}', # Fuzzy
	'flow-topic-comments' => '{{PLURAL:$1|0=Soyez le premier à laisser un commentaire !|Commenter ($1)}}',
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
	'flow-notification-mention' => '$1 vous {{GENDER:$1|a mentionné}} dans leur [$2 note] sur « $3 » en « $4 »',
	'flow-notification-link-text-view-post' => 'Afficher la note',
	'flow-notification-link-text-view-board' => 'Afficher le tableau',
	'flow-notification-link-text-view-topic' => 'Afficher le sujet',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|a répondu}} à votre note',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|a répondu}} a votre note concernant $2 sur « $3 »',
	'flow-notification-reply-email-batch-bundle-body' => '$1 et $4 {{PLURAL:$5|autre|autres}} {{GENDER:$1|ont répondu}} à votre note concernant $2 sur « $3 »',
	'flow-notification-mention-email-subject' => '$1 vous {{GENDER:$1|a mentionné}} en $2',
	'flow-notification-mention-email-batch-body' => '$1 vous {{GENDER:$1|a mentionné}} dans leur note sur « $2 » en « $3 »',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|a modifié}} votre note',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|a modifié}} votre note sur $2 en « $3 »',
	'flow-notification-edit-email-batch-bundle-body' => '$1 et $4 {{PLURAL:$5|autre|autres}} {{GENDER:$1|ont modifié}} une note sur $2 en « $3 »',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|a renommé}} votre sujet',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|a renommé}} votre sujet « $2 » en « $3 » sur « $4 »',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|a créé}} un nouveau sujet sur $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|a créé}} un nouveau sujet avec le titre « $2 » en $3',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Discussion|Discussions}}',
	'echo-pref-tooltip-flow-discussion' => 'M’informer quand des actions me concernant ont lieu dans le cercle de discussion.',
	'flow-link-post' => 'note',
	'flow-link-topic' => 'sujet',
	'flow-link-history' => 'historique',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'flow-desc' => 'Sistema de xestión do fluxo de traballo',
	'flow-specialpage' => '$1 &ndash; Fluxo',
	'flow-edit-header-link' => 'Editar a cabeceira',
	'flow-header-empty' => 'Actualmente, esta páxina de conversa non ten cabeceira.',
	'flow-post-moderated-toggle-show' => '[Mostrar]',
	'flow-post-moderated-toggle-hide' => '[Agochar]',
	'flow-post-hidden' => '[mensaxe agochada]',
	'flow-post-hidden-by' => '{{GENDER:$1|Agochada}} por $1 $2',
	'flow-post-deleted' => '[mensaxe borrada]',
	'flow-post-deleted-by' => '{{GENDER:$1|Borrada}} por $1 $2',
	'flow-post-censored' => '[mensaxe censurada]',
	'flow-post-censored-by' => '{{GENDER:$1|Censurada}} por $1 $2',
	'flow-post-actions' => 'accións',
	'flow-topic-actions' => 'accións',
	'flow-cancel' => 'Cancelar',
	'flow-newtopic-title-placeholder' => 'Asunto da mensaxe',
	'flow-newtopic-content-placeholder' => 'Texto da mensaxe. Sexa amable!',
	'flow-newtopic-header' => 'Engadir un novo fío',
	'flow-newtopic-save' => 'Nova sección',
	'flow-newtopic-start-placeholder' => 'Iniciar un novo fío',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Comentario}} en "$2"',
	'flow-reply-placeholder' => 'Prema para {{GENDER:$1|responder}} a $1. Sexa amable!',
	'flow-reply-submit' => '{{GENDER:$1|Responder}}',
	'flow-reply-link' => '{{GENDER:$1|Responder}}',
	'flow-thank-link' => '{{GENDER:$1|Agradecer}}',
	'flow-talk-link' => 'Falarlle a {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Enviar os cambios',
	'flow-post-edited' => 'Mensaxe {{GENDER:$1|editada}} por $1 $2',
	'flow-post-action-view' => 'Ligazón permanente',
	'flow-post-action-post-history' => 'Historial da mensaxe',
	'flow-post-action-censor-post' => 'Censurar a mensaxe',
	'flow-post-action-delete-post' => 'Borrar a mensaxe',
	'flow-post-action-hide-post' => 'Agochar a mensaxe',
	'flow-post-action-edit-post' => 'Editar a mensaxe',
	'flow-post-action-edit' => 'Editar',
	'flow-post-action-restore-post' => 'Restaurar a mensaxe',
	'flow-topic-action-view' => 'Ligazón permanente',
	'flow-topic-action-watchlist' => 'Lista de vixilancia',
	'flow-topic-action-edit-title' => 'Editar o título',
	'flow-topic-action-history' => 'Historial do fío',
	'flow-error-http' => 'Produciuse un erro ao contactar co servidor. Non se gardou a súa mensaxe.',
	'flow-error-other' => 'Produciuse un erro inesperado. Non se gardou a súa mensaxe.',
	'flow-error-external' => 'Produciuse un erro ao gardar a súa mensaxe. Non se gardou a súa mensaxe.<br /><small>A mensaxe de erro recibida foi: $1</small>',
	'flow-error-edit-restricted' => 'Non lle está permitido editar esta mensaxe.',
	'flow-error-external-multi' => 'Producíronse erros ao gardar a súa mensaxe. Non se gardou a súa mensaxe.<br />$1',
	'flow-error-missing-content' => 'A mensaxe non ten contido. O contido é obrigatorio para gardar unha nova mensaxe.',
	'flow-error-missing-title' => 'O fío non ten título. O título é obrigatorio para gardar un novo fío.',
	'flow-error-parsoid-failure' => 'Non é posible analizar o contido debido a un fallo do Parsoid.',
	'flow-error-missing-replyto' => 'Non se achegou ningún parámetro de resposta. Este parámetro é obrigatorio para a acción "responder".',
	'flow-error-invalid-replyto' => 'O parámetro de resposta non é válido. Non se puido atopar a mensaxe especificada.',
	'flow-error-delete-failure' => 'Houbo un erro ao borrar este elemento.',
	'flow-error-hide-failure' => 'Houbo un erro ao agochar este elemento.',
	'flow-error-missing-postId' => 'Non se achegou ningún parámetro de identificación. Este parámetro é obrigatorio para a manipular unha mensaxe.',
	'flow-error-invalid-postId' => 'O parámetro de identificación non é válido. Non se puido atopar a mensaxe especificada.',
	'flow-error-restore-failure' => 'Houbo un erro ao restaurar este elemento.',
	'flow-edit-header-submit' => 'Gardar a cabeceira',
	'flow-edit-title-submit' => 'Cambiar o título',
	'flow-rev-message-edit-post' => 'Editouse o contido da mensaxe',
	'flow-rev-message-reply' => 'Publicouse unha nova resposta',
	'flow-rev-message-new-post' => 'Creouse un fío',
	'flow-rev-message-hid-post' => 'Agochouse a mensaxe',
	'flow-rev-message-edit-title' => 'Editouse o título do fío',
	'flow-rev-message-create-header' => 'Creouse a cabeceira',
	'flow-rev-message-edit-header' => 'Editouse a cabeceira',
	'flow-rev-message-restored-post' => 'Descubriuse a mensaxe',
	'flow-rev-message-deleted-post' => 'Borrouse a mensaxe',
	'flow-rev-message-censored-post' => 'Censurouse a mensaxe',
	'flow-topic-history' => 'Historial do fío',
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
	'flow-notification-mention' => '$1 {{GENDER:$1|fíxolle unha mención}} na súa [$2 mensaxe] "$3" en "$4"',
	'flow-notification-link-text-view-post' => 'Ver a mensaxe',
	'flow-notification-link-text-view-board' => 'Ver o taboleiro',
	'flow-notification-link-text-view-topic' => 'Ver o fío',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|respondeu}} á súa mensaxe',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|respondeu}} á súa mensaxe "$2" en "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 e {{PLURAL:$5|outra persoa|outras $4 persoas}} {{GENDER:$1|responderon}} á súa mensaxe "$2" en "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|fíxolle unha mención}} en "$2"',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|fíxolle unha mención}} na súa mensaxe "$2" en "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|editou}} a súa mensaxe',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|editou}} a súa mensaxe "$2" en "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 e {{PLURAL:$5|outra persoa|outras $4 persoas}} {{GENDER:$1|editaron}} a mensaxe "$2" en "$3".',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|renomeou}} o seu fío',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|renomeou}} o seu fío "$2" a "$3" en "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|creou}} un novo fío en "$2"',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|creou}} un novo fío co título "$2" en "$3"',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Conversa|Conversas}}',
	'echo-pref-tooltip-flow-discussion' => 'Notificádeme cando sucedan accións relacionadas comigo no taboleiro de conversas.',
	'flow-link-post' => 'mensaxe',
	'flow-link-topic' => 'fío',
	'flow-link-history' => 'historial',
);

/** Gujarati (ગુજરાતી)
 * @author KartikMistry
 */
$messages['gu'] = array(
	'flow-notification-link-text-view-topic' => 'વિષય જુઓ',
);

/** Hebrew (עברית)
 * @author Amire80
 * @author Orsa
 */
$messages['he'] = array(
	'flow-desc' => 'מערכת לניהול זרימת עבודה',
	'flow-specialpage' => '$1 – זרימה',
	'flow-edit-summary-link' => 'תקציר העריכה',
	'flow-disclaimer' => 'לחיצה על כפתור "הוספת הודעה" היא הסכמה לתנאי שימוש,
לפרסום של כל התרומות שלך לפי תנאי רישיונות CC-BY-SA 3.0 ו־GFDL
ולכן שהיפר־קישור או כתובת URL הם ייחוס מספק לפי תנאי רישיון קריאייטיב קומונז.',
	'flow-post-deleted' => '[הרשומה נמחקה]',
	'flow-post-actions' => 'פעולות',
	'flow-topic-actions' => 'פעולות',
	'flow-cancel' => 'ביטול',
	'flow-newtopic-title-placeholder' => 'כותרת הודעה',
	'flow-newtopic-content-placeholder' => 'תוכן ההודעה. זה צריך להיות משהו נחמד!',
	'flow-newtopic-header' => 'הוספת נושא חדש',
	'flow-newtopic-save' => 'נוספת נושא',
	'flow-newtopic-start-placeholder' => 'יש ללחוץ כאן כדי להתחיל דיון חדש. זה צריך להיות משהו נחמד!',
	'flow-reply-placeholder' => 'יש ללחוץ כדי לענות ל{{GRAMMAR:תחילית|$1}}. נא לכתוב דברים נחמדים!', # Fuzzy
	'flow-reply-submit' => 'שליחת תשובה',
	'flow-edit-post-submit' => 'שליחת שינויים',
	'flow-post-action-view' => 'קישור קבוע',
	'flow-post-action-post-history' => 'היסטוריית הרשומה',
	'flow-post-action-delete-post' => 'מחיקת הרשומה',
	'flow-post-action-edit-post' => 'עריכת הרשומה',
	'flow-post-action-edit' => 'עריכה',
	'flow-post-action-restore-post' => 'שחזור הרשומה',
	'flow-topic-action-edit-title' => 'עריכת כותרת',
	'flow-topic-action-history' => 'היסטוריית הנושא',
	'flow-error-http' => 'אירעה שגיאה בעת התחברות לשרת. הרשומה שלך לא נשמרה.',
	'flow-error-other' => 'אירעה שגיאה בלתי־צפויה. הרשומה שלך לא נשמרה.',
	'flow-error-external' => 'אירעה שגיאה בעת ניסיון לשמור את הרשומה שלך. הרשומה שלך לא נשמרה.<br /><small>התקבלה ההודעה הבאה: $1</small>',
	'flow-error-external-multi' => 'אירעו שגיאות בעת שמירת הרשומה שלך. הרשומה שלך לא נשמרה.<br />
$1',
	'flow-error-missing-content' => 'ברשומה אין תוכן. דרוש תוכן כדי לשמור רשומה חדשה.',
	'flow-error-missing-title' => 'לנושא אין כותרת. דרושה כותרת כדי לשמור נושא חדש.',
	'flow-error-parsoid-failure' => 'לא ניתן לפענח את התוכן עקב כשל בפרסואיד.',
	'flow-error-missing-replyto' => 'לא נשלח פרמטר replyTo. הפרמטר הזה דרוש לפעולת "reply".', # Fuzzy
	'flow-error-invalid-replyto' => 'פרמטר replyTo שנשלח היה בלתי־תקין. לא נמצאה הרשומה שצוינה.', # Fuzzy
	'flow-error-delete-failure' => 'מחירת הפריט הזה נכשלה.',
	'flow-error-missing-postId' => 'לא ניתן פרמטר postId. הפרמטר הזה דרוש כדי לשנות רשומה.', # Fuzzy
	'flow-error-invalid-postId' => 'פרמטר postId שנשלח היה בלתי־תקין. הרשומה לא נמצאה.', # Fuzzy
	'flow-error-restore-failure' => 'שחזור הפריט נכשל.',
	'flow-summaryedit-submit' => 'שמירת סיכום',
	'flow-edit-title-submit' => 'שינוי כותרת',
	'flow-rev-message-reply' => 'נשלחה תשובה חדשה',
	'flow-rev-message-new-post' => 'נוצר נושא',
	'flow-topic-history' => 'היסטוריית הנושא',
	'flow-comment-restored' => 'הערה משוחזרת',
	'flow-comment-deleted' => 'הערה מחוקה',
);

/** Armenian (Հայերեն)
 * @author Vadgt
 */
$messages['hy'] = array(
	'flow-post-deleted-by' => '$1-ը {{GENDER:$1|ջնջեց}} $2',
	'flow-reply-placeholder' => 'Սեղմեք {{GENDER:$1|պատասխանել}} $1-ում: Կլինի լա՜վ',
	'flow-notification-edit' => '$1՝ {{GENDER:$1|խմբագրեց}} ձեր [$5 գրառում(ներ)ը] $2-ում [[$3|$4]]ի վրա:', # Fuzzy
	'flow-notification-rename' => '$1՝ {{GENDER:$1|փոխեց}} վերնագրիրը [$2 $3]-ի "$4"-ում [[$5|$6]]-ի վրա:',
);

/** Interlingua (interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'flow-desc' => 'Systema de gestion de fluxo de travalio',
	'flow-specialpage' => '$1 &ndash; Fluxo',
	'flow-user-anonymous' => 'Anonyme',
	'flow-user-moderated' => 'Usator moderate',
	'flow-edit-header-link' => 'Modificar titulo',
	'flow-header-empty' => 'Iste pagina de discussion actualmente non ha titulo.',
	'flow-post-moderated-toggle-show' => '[Monstrar]',
	'flow-post-moderated-toggle-hide' => '[Celar]',
	'flow-post-hidden' => '[entrata celate]',
	'flow-post-hidden-by' => '{{GENDER:$1|Celate}} per $1 $2',
	'flow-post-deleted' => '[entrata delite]',
	'flow-post-deleted-by' => '{{GENDER:$1|Delite}} per $1 $2',
	'flow-post-censored' => '[entrata censurate]',
	'flow-post-censored-by' => '{{GENDER:$1|Censurate}} per $1 $2',
	'flow-post-actions' => 'actiones',
	'flow-topic-actions' => 'actiones',
	'flow-cancel' => 'Cancellar',
	'flow-newtopic-title-placeholder' => 'Subjecto del message',
	'flow-newtopic-content-placeholder' => 'Texto del message. Sia gentil!',
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
	'flow-post-action-censor-post' => 'Censurar entrata',
	'flow-post-action-delete-post' => 'Deler entrata',
	'flow-post-action-hide-post' => 'Celar entrata',
	'flow-post-action-edit-post' => 'Modificar entrata',
	'flow-post-action-edit' => 'Modificar',
	'flow-post-action-restore-post' => 'Restaurar entrata',
	'flow-topic-action-view' => 'Permaligamine',
	'flow-topic-action-watchlist' => 'Observatorio',
	'flow-topic-action-edit-title' => 'Modificar titulo',
	'flow-topic-action-history' => 'Historia del topico',
	'flow-error-http' => 'Un error occurreva durante le communication con le servitor. Tu message non ha essite salveguardate.',
	'flow-error-other' => 'Un error inexpectate ha occurrite. Tu message non ha essite salveguardate.',
	'flow-error-external' => 'Un error ha occurrite durante le salveguarda de tu message. Tu message non ha essite salveguardate.<br /><small>Le message de error recipite es: $1</small>',
	'flow-error-edit-restricted' => 'Tu non es autorisate a modificar iste entrata.',
	'flow-error-external-multi' => 'Errores ha essite incontrate durante le salveguarda de tu message. Tu message non ha essite salveguardate.<br />$1',
	'flow-error-missing-content' => 'Le message non ha contento. Contento es necessari pro salveguardar un nove message.',
	'flow-error-missing-title' => 'Le topico non ha titulo. Le titulo es necessari pro salveguardar un nove topico.',
	'flow-error-parsoid-failure' => 'Impossibile interpretar le contento a causa de un fallimento de Parsoid.',
	'flow-error-missing-replyto' => 'Nulle parametro "replyTo" ha essite fornite. Iste parametro es necessari pro le action "responder".',
	'flow-error-invalid-replyto' => 'Le parametro "replyTo" es invalide. Le entrata specificate non pote esser trovate.',
	'flow-error-delete-failure' => 'Le deletion de iste elemento ha fallite.',
	'flow-error-hide-failure' => 'Le celamento de iste elemento ha fallite.',
	'flow-error-missing-postId' => 'Nulle parametro "postId" ha essite specificate. Iste parametro es necessari pro manipular un entrata.',
	'flow-error-invalid-postId' => 'Le parametro "postId" es invalide. Le entrata specificate non poteva esser trovate.',
	'flow-error-restore-failure' => 'Le restauration de iste elemento ha fallite.',
	'flow-edit-header-submit' => 'Salveguardar titulo',
	'flow-edit-title-submit' => 'Cambiar titulo',
	'flow-rev-message-edit-post' => 'Contento del entrata modificate',
	'flow-rev-message-reply' => 'Nove responsa publicate',
	'flow-rev-message-new-post' => 'Topico create',
	'flow-rev-message-hid-post' => 'Entrata celate',
	'flow-rev-message-edit-title' => 'Titulo del discussion modificate',
	'flow-rev-message-create-header' => 'Titulo create',
	'flow-rev-message-edit-header' => 'Titulo modificate',
	'flow-rev-message-restored-post' => 'Entrata revelate',
	'flow-rev-message-deleted-post' => 'Entrata delite',
	'flow-rev-message-censored-post' => 'Entrata censurate',
	'flow-topic-history' => 'Historia de topicos',
	'flow-comment-restored' => 'Commento restaurate',
	'flow-comment-deleted' => 'Commento delite',
	'flow-comment-hidden' => 'Commento celate',
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
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Discussion|Discussiones}}',
	'echo-pref-tooltip-flow-discussion' => 'Notificar me quando actiones concernente me occurre in le tabuliero de discussion.',
);

/** Italian (italiano)
 * @author Beta16
 * @author Gianfranco
 */
$messages['it'] = array(
	'flow-desc' => 'Sistema di gestione del flusso di lavoro',
	'flow-specialpage' => '$1 &ndash; Flusso',
	'flow-user-anonymous' => 'Anonimo',
	'flow-user-moderated' => 'Utente moderato',
	'flow-edit-header-link' => 'Modifica intestazione',
	'flow-header-empty' => 'Questa pagina di discussione attualmente non ha alcuna intestazione.',
	'flow-post-moderated-toggle-show' => '[Mostra]',
	'flow-post-moderated-toggle-hide' => '[Nascondi]',
	'flow-post-hidden' => '[messaggio nascosto]',
	'flow-post-hidden-by' => '{{GENDER:$1|Nascosto}} da $1 $2',
	'flow-post-deleted' => '[messaggio cancellato]',
	'flow-post-deleted-by' => '{{GENDER:$1|Cancellato}} da $1 $2',
	'flow-post-censored' => '[messaggio censurato]',
	'flow-post-censored-by' => '{{GENDER:$1|Censurato}} da $1 $2',
	'flow-post-actions' => 'azioni',
	'flow-topic-actions' => 'azioni',
	'flow-cancel' => 'Annulla',
	'flow-newtopic-title-placeholder' => 'Oggetto del messaggio',
	'flow-newtopic-content-placeholder' => 'Testo del messaggio. Sii gentile!',
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
	'flow-post-action-censor-post' => 'Censura messaggio',
	'flow-post-action-delete-post' => 'Cancella messaggio',
	'flow-post-action-hide-post' => 'Nascondi messaggio',
	'flow-post-action-edit-post' => 'Modifica messaggio',
	'flow-post-action-edit' => 'Modifica',
	'flow-post-action-restore-post' => 'Ripristina messaggio',
	'flow-topic-action-view' => 'Link permanente',
	'flow-topic-action-watchlist' => 'Osservati speciali',
	'flow-topic-action-edit-title' => 'Modifica titolo',
	'flow-topic-action-history' => 'Cronologia della discussione',
	'flow-error-http' => 'Si è verificato un errore durante la comunicazione con il server. Il tuo messaggio non è stato salvato.',
	'flow-error-other' => 'Si è verificato un errore imprevisto. Il tuo messaggio non è stato salvato.',
	'flow-error-external' => 'Si è verificato un errore durante il salvataggio del tuo messaggio. Il tuo messaggio, perciò, non è stato salvato.<br /><small>Il messaggio di errore ricevuto è: $1</small>',
	'flow-error-edit-restricted' => 'Non è consentito modificare questo messaggio.',
	'flow-error-external-multi' => 'Si sono verificati errori durante il salvataggio del tuo messaggio. Il tuo messaggio, perciò, non è stato salvato.<br />$1',
	'flow-error-missing-content' => 'Il tuo messaggio non ha contenuto. Un minimo di contenuto è necessario per poter salvare un nuovo messaggio.',
	'flow-error-missing-title' => 'La discussione non ha titolo. Serve un titolo per salvare una nuova discussione.',
	'flow-error-parsoid-failure' => 'Impossibile analizzare il contenuto a causa di un errore di Parsoid.',
	'flow-error-missing-replyto' => 'Non è stato indicato un parametro "rispondi_a". Questo parametro è richiesto per la funzione "rispondi".',
	'flow-error-invalid-replyto' => 'Il parametro "rispondi_a" non era valido. Il messaggio indicato non è stato trovato.',
	'flow-error-delete-failure' => 'La cancellazione di questo elemento non è riuscita.',
	'flow-error-hide-failure' => 'Il tentativo di nascondere questo elemento non è riuscito.',
	'flow-error-missing-postId' => 'Non è stato fornito alcun parametro "ID_messaggio". Questo parametro è necessario per poter elaborare un messaggio.',
	'flow-error-invalid-postId' => 'Il parametro "ID_messaggio" non era valido. Il messaggio indicato non è stato trovato.',
	'flow-error-restore-failure' => 'Il ripristino di questo elemento non è riuscito.',
	'flow-edit-header-submit' => 'Salva intestazione',
	'flow-edit-title-submit' => 'Cambia titolo',
	'flow-rev-message-edit-post' => 'Modificato contenuto del messaggio',
	'flow-rev-message-reply' => 'Nuova risposta inviata',
	'flow-rev-message-new-post' => 'Discussione creata',
	'flow-rev-message-hid-post' => 'Messaggio nascosto',
	'flow-rev-message-edit-title' => 'Modificato titolo della discussione',
	'flow-rev-message-create-header' => 'Crea intestazione',
	'flow-rev-message-edit-header' => 'Intestazione modificata',
	'flow-rev-message-restored-post' => 'Messaggio reso visibile',
	'flow-rev-message-deleted-post' => 'Messaggio cancellato',
	'flow-rev-message-censored-post' => 'Messaggio censurato',
	'flow-topic-history' => 'Cronologia della discussione',
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
	'flow-notification-mention' => '$1 ti {{GENDER:$1|ha menzionato}} nel suo [$2 messaggio] in "$3" su "$4"',
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
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Discussione|Discussioni}}',
	'echo-pref-tooltip-flow-discussion' => 'Avvisami quando vengono eseguite azioni connesse a me nella bacheca delle discussioni.',
	'flow-link-post' => 'messaggio',
	'flow-link-topic' => 'discussione',
	'flow-link-history' => 'cronologia',
);

/** Japanese (日本語)
 * @author Fryed-peach
 * @author Shirayuki
 */
$messages['ja'] = array(
	'flow-desc' => 'ワークフロー管理システム',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-user-anonymous' => '匿名',
	'flow-edit-header-link' => 'ヘッダーを編集',
	'flow-header-empty' => '現在、このトークページにはヘッダーがありません。',
	'flow-post-moderated-toggle-show' => '[表示]',
	'flow-post-moderated-toggle-hide' => '[非表示]',
	'flow-post-hidden' => '[非表示の投稿]',
	'flow-post-hidden-by' => '$1 が$2に{{GENDER:$1|非表示化}}',
	'flow-post-deleted' => '[削除された投稿]',
	'flow-post-deleted-by' => '$1 が$2に{{GENDER:$1|削除}}',
	'flow-post-actions' => '操作',
	'flow-topic-actions' => '操作',
	'flow-cancel' => 'キャンセル',
	'flow-newtopic-title-placeholder' => 'メッセージの件名',
	'flow-newtopic-content-placeholder' => 'メッセージの本文',
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
	'flow-post-action-delete-post' => '投稿を削除',
	'flow-post-action-hide-post' => '投稿を非表示にする',
	'flow-post-action-edit-post' => '投稿を編集',
	'flow-post-action-edit' => '編集',
	'flow-post-action-restore-post' => '投稿を復元',
	'flow-topic-action-view' => '固定リンク',
	'flow-topic-action-watchlist' => 'ウォッチリスト',
	'flow-topic-action-edit-title' => 'タイトルを編集',
	'flow-topic-action-history' => '話題の履歴',
	'flow-error-http' => 'サーバーと通信する際にエラーが発生しました。投稿内容は保存されませんでした。',
	'flow-error-other' => '予期しないエラーが発生しました。投稿内容は保存されませんでした。',
	'flow-error-external' => '投稿内容を保存する際にエラーが発生しました。投稿内容は保存されませんでした。<br /><small>エラー メッセージ: $1</small>',
	'flow-error-edit-restricted' => 'あなたはこの投稿を編集を許可されていません。',
	'flow-error-external-multi' => '投稿内容を保存する際にエラーが発生しました。投稿内容は保存されませんでした。<br /> $1',
	'flow-error-missing-content' => '投稿の本文がありません。新しい投稿を保存するには本文が必要です。',
	'flow-error-missing-title' => '話題のタイトルがありません。新しい話題を保存するにはタイトルが必要です。',
	'flow-error-parsoid-failure' => 'Parsoid でエラーが発生したため、本文を構文解析できませんでした。',
	'flow-error-missing-replyto' => '「返信先」のパラメーターを指定していません。「返信」するには、このパラメーターが必要です。',
	'flow-error-invalid-replyto' => '「返信先」のパラメーターが無効です。指定した投稿が見つかりませんでした。',
	'flow-error-delete-failure' => 'この項目を削除できませんでした。',
	'flow-error-hide-failure' => 'この項目を非表示にできませんでした。',
	'flow-error-missing-postId' => '「投稿 ID」のパラメーターを指定していません。投稿を操作するには、このパラメーターが必要です。',
	'flow-error-invalid-postId' => '「投稿 ID」のパラメーターが無効です。指定した投稿が見つかりませんでした。',
	'flow-error-restore-failure' => 'この項目を復元できませんでした。',
	'flow-edit-header-submit' => 'ヘッダーを保存',
	'flow-edit-title-submit' => 'タイトルを変更',
	'flow-rev-message-edit-post' => '投稿の本文を編集',
	'flow-rev-message-reply' => '新しい返信を投稿',
	'flow-rev-message-new-post' => '話題を作成',
	'flow-rev-message-edit-title' => '話題のタイトルを編集',
	'flow-rev-message-create-header' => 'ヘッダーを作成',
	'flow-rev-message-edit-header' => 'ヘッダーを編集',
	'flow-rev-message-deleted-post' => '投稿を削除',
	'flow-topic-history' => '話題の履歴',
	'flow-topic-participants' => '{{PLURAL:$1|$3 がこの話題を開始|{{GENDER:$3|$3}}、{{GENDER:$4|$4}} と他 $2 {{PLURAL:$2|人}}|0=まだ誰も参加していません|2={{GENDER:$3|$3}} と {{GENDER:$4|$4}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=最初のコメントを書きましょう!|コメント ($1)}}',
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
	'flow-notification-mention' => '$1 が「$4」の「$3」での自身の[$2 投稿]であなたに{{GENDER:$1|言及しました}}',
	'flow-notification-link-text-view-post' => '投稿を閲覧',
	'flow-notification-link-text-view-board' => '掲示板を閲覧',
	'flow-notification-link-text-view-topic' => '話題を閲覧',
	'flow-notification-reply-email-subject' => '$1 があなたの投稿に{{GENDER:$1|返信しました}}',
	'flow-notification-reply-email-batch-body' => '$1 が「$3」の $2 でのあなたの投稿に{{GENDER:$1|返信しました}}',
	'flow-notification-reply-email-batch-bundle-body' => '$1 と他 $4 {{PLURAL:$5|人}}が「$3」の $2 でのあなたの投稿に{{PLURAL:$1|返信しました}}',
	'flow-notification-mention-email-subject' => '$1 が $2 であなたに{{GENDER:$1|言及しました}}',
	'flow-notification-mention-email-batch-body' => '$1 が「$3」の「$2」での自身の投稿であなたに{{GENDER:$1|言及しました}}',
	'flow-notification-edit-email-subject' => '$1 があなたの投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-edit-email-batch-body' => '$1 が「$3」の $2 でのあなたの投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-edit-email-batch-bundle-body' => '$1 と他 $4 {{PLURAL:$5|人}}が「$3」の $2 での投稿を{{GENDER:$1|編集しました}}',
	'flow-notification-rename-email-subject' => '$1 があなたの話題の{{GENDER:$1|名前を変更しました}}',
	'flow-notification-rename-email-batch-body' => '$1 が「$4」のあなたの話題「$2」の名前を「$3」に{{GENDER:$1|変更しました}}',
	'flow-notification-newtopic-email-subject' => '$1 が $2 に新しい話題を{{GENDER:$1|作成しました}}',
	'flow-notification-newtopic-email-batch-body' => '$1 が $3 で新しい話題「$2」を{{GENDER:$1|作成しました}}',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|議論}}',
	'echo-pref-tooltip-flow-discussion' => '議論掲示板で私に関連する操作がなされたときに通知する。',
	'flow-link-post' => '投稿',
	'flow-link-topic' => '話題',
	'flow-link-history' => '履歴',
);

/** Korean (한국어)
 * @author Hym411
 * @author 아라
 */
$messages['ko'] = array(
	'flow-desc' => '워크플로우 관리 시스템',
	'flow-specialpage' => '$1 &ndash; 플로우',
	'flow-user-anonymous' => '익명',
	'flow-edit-header-link' => '머리말 고치기',
	'flow-header-empty' => '이 토론 문서에는 머릿말이 없습니다.',
	'flow-post-moderated-toggle-show' => '[보기]',
	'flow-post-moderated-toggle-hide' => '[숨김]',
	'flow-newtopic-start-placeholder' => '새 주제',
	'flow-reply-topic-placeholder' => '$1의 "$2"에 대한 의견',
	'flow-reply-submit' => '답변', # Fuzzy
	'flow-talk-link' => '$1에게 말하기',
	'flow-topic-action-view' => '고유링크',
	'flow-topic-action-watchlist' => '주시문서 목록',
	'flow-error-edit-restricted' => '이 문서의 편집을 허용하지 않습니다.',
	'flow-rev-message-hid-post' => '내용 숨겨짐',
	'flow-rev-message-edit-title' => '주제 제목이 편집되었습니다',
	'flow-rev-message-edit-header' => '수정된 머리말',
	'flow-rev-message-restored-post' => '게시글 숨김 해제',
	'flow-rev-message-deleted-post' => '삭제된 게시글',
	'flow-topic-comments' => '{{PLURAL:$1|0=첫 댓글을 달아 보세요!|댓글 ($1개)}}',
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
	'flow-user-anonymous' => 'Anonym',
	'flow-header-empty' => 'Dës Diskussiounssäit huet elo keng Iwwerschrëft',
	'flow-post-moderated-toggle-show' => '[Weisen]',
	'flow-post-moderated-toggle-hide' => '[Verstoppen]',
	'flow-post-hidden-by' => '{{GENDER:$1|Verstoppt}} vum $1 $2',
	'flow-post-deleted-by' => '{{GENDER:$1|Geläscht}} vum $1 $2',
	'flow-post-censored-by' => '{{GENDER:$1|Zensuréiert}} vum $1 $2',
	'flow-post-actions' => 'Aktiounen',
	'flow-topic-actions' => 'Aktiounen',
	'flow-cancel' => 'Ofbriechen',
	'flow-newtopic-title-placeholder' => 'Sujet vum Message',
	'flow-newtopic-content-placeholder' => 'Text vum Message. Sidd frëndlech!',
	'flow-newtopic-header' => 'En neit Thema derbäisetzen',
	'flow-newtopic-save' => 'Thema derbäisetzen',
	'flow-newtopic-start-placeholder' => 'En neit Thema ufänken',
	'flow-reply-placeholder' => 'Dem $1 {{GENDER:$1|äntwerten}}',
	'flow-reply-link' => '{{GENDER:$1|Äntwerten}}',
	'flow-edit-post-submit' => 'Ännerunge späicheren',
	'flow-post-action-view' => 'Permanentlink',
	'flow-post-action-edit' => 'Änneren',
	'flow-topic-action-watchlist' => 'Iwwerwaachungslëscht',
	'flow-topic-action-edit-title' => 'Titel änneren',
	'flow-error-missing-title' => "D'Thema huet keen Titel. Den Titel ass obligatoresch fir een neit Thema ze späicheren.",
	'flow-error-delete-failure' => "D'Läsche vun dësem Element huet net funktionéiert.",
	'flow-error-hide-failure' => 'Verstoppe vun dësem Element huet net funktionéiert.',
	'flow-error-restore-failure' => "D'Restauréiere vun dësem Element huet net funktionéiert.",
	'flow-edit-title-submit' => 'Titel änneren',
	'flow-rev-message-new-post' => 'Thema ugeluecht',
	'flow-topic-comments' => '{{PLURAL:$1|0=Sidd deen éischten deen enge Bemierkung mecht!|Bemierkung ($1)}}',
	'flow-comment-restored' => 'Restauréiert Bemierkung',
	'flow-comment-deleted' => 'Geläscht Bemierkung',
	'flow-comment-hidden' => 'Verstoppte Bemierkung',
	'flow-paging-rev' => 'Méi rezent Themen',
	'flow-paging-fwd' => 'Méi al Themen',
	'flow-last-modified' => "Fir d'lescht geännert ongeféier $1",
	'flow-notification-rename' => '$1 {{GENDER:$1|huet}} den Titel vu(n) [$2 $3] op "$4" op [[$5|$6]] geännert.',
	'flow-notification-link-text-view-board' => 'Tableau weisen',
	'flow-notification-link-text-view-topic' => 'Thema weisen',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Diskussioun|Diskussiounen}}',
	'echo-pref-tooltip-flow-discussion' => 'Mech informéieren wann Aktiounen déi mech betreffen um Diskussiouns-Board geschéien.',
	'flow-link-topic' => 'Thema',
	'flow-link-history' => 'Versiounen',
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
	'flow-rev-message-edit-post' => 'Labot ieraksta saturu',
	'flow-rev-message-create-header' => 'Izveidoja galveni',
	'flow-rev-message-edit-header' => 'Izmainīja galveni',
	'flow-rev-message-deleted-post' => 'Dzēsts ieraksts',
	'flow-rev-message-censored-post' => 'Cenzēts ieraksts',
	'flow-link-topic' => 'tēma',
	'flow-link-history' => 'vēsture',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'flow-desc' => 'Систем за раководење со работниот тек',
	'flow-specialpage' => '$1 &ndash; Тек',
	'flow-user-anonymous' => 'Анонимен',
	'flow-user-moderated' => 'Модериран корисник',
	'flow-edit-header-link' => 'Измени наслов',
	'flow-header-empty' => 'Страницава засега нема заглавие.',
	'flow-post-moderated-toggle-show' => '[Прикажи]',
	'flow-post-moderated-toggle-hide' => '[Скриј]',
	'flow-post-hidden' => '[пораката е скриена]',
	'flow-post-hidden-by' => '{{GENDER:$1|Скриена}} од $1 $2',
	'flow-post-deleted' => '[пораката е избришана]',
	'flow-post-deleted-by' => '{{GENDER:$1|Избришана}} од $1 $2',
	'flow-post-censored' => '[пораката е цензурирана]',
	'flow-post-censored-by' => '{{GENDER:$1|Цензурирана}} од $1 $2',
	'flow-post-actions' => 'дејства',
	'flow-topic-actions' => 'дејства',
	'flow-cancel' => 'Откажи',
	'flow-newtopic-title-placeholder' => 'Наслов на пораката',
	'flow-newtopic-content-placeholder' => 'Текст на пораката. Бидете фини!',
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
	'flow-post-action-censor-post' => 'Цензурирај ја пораката',
	'flow-post-action-delete-post' => 'Избриши ја пораката',
	'flow-post-action-hide-post' => 'Скриј ја пораката',
	'flow-post-action-edit-post' => 'Уреди ја пораката',
	'flow-post-action-edit' => 'Измени',
	'flow-post-action-restore-post' => 'Поврати ја пораката',
	'flow-topic-action-view' => 'Постојана врска',
	'flow-topic-action-watchlist' => 'Набљудувања',
	'flow-topic-action-edit-title' => 'Уреди наслов',
	'flow-topic-action-history' => 'Историја на темата',
	'flow-error-http' => 'Се јави грешка при поврзувањето со опслужувачот. Пораката не е зачувана.',
	'flow-error-other' => 'Се појави неочекувана грешка. Пораката не е зачувана',
	'flow-error-external' => 'Се појави грешка при зачувувањето на пораката, и затоа не е зачувана.<br /><small>Добиена е грешката: $1</small>',
	'flow-error-edit-restricted' => 'Не ви е дозволено да ја менувате објавата.',
	'flow-error-external-multi' => 'Наидов на грешки при зачувувањето на пораката, и затоа не е зачувана.<br />$1',
	'flow-error-missing-content' => 'Пораката нема содржина. За да се зачува, мора да има содржина.',
	'flow-error-missing-title' => 'Темата нема наслов. Се бара наслов за да може да се зачува темата.',
	'flow-error-parsoid-failure' => 'Не можам да ја парсирам содржината поради проблем со Parsoid.',
	'flow-error-missing-replyto' => 'Нема зададено параметар „replyTo“. Овој параметар е потребен за да може да се даде одговор.',
	'flow-error-invalid-replyto' => 'Параметарот на „replyTo“ е неважечки. Не можев да ја најдам укажаната порака.',
	'flow-error-delete-failure' => 'Бришењето на ставката не успеа.',
	'flow-error-hide-failure' => 'Не успеав да ја скријам ставката.',
	'flow-error-missing-postId' => 'Нема зададено параметар „postId“. Овој параметар е потребен за работа со пораката.',
	'flow-error-invalid-postId' => 'Параметарот на „postId“ е неважечки. Не можев да ја најдам укажаната порака.',
	'flow-error-restore-failure' => 'Повраќањето на ставката не успеа.',
	'flow-edit-header-submit' => 'Зачувај заглавие',
	'flow-edit-title-submit' => 'Измени наслов',
	'flow-rev-message-edit-post' => 'Изменета содржина во објава',
	'flow-rev-message-reply' => 'Објавен нов одговор',
	'flow-rev-message-new-post' => 'Создадена тема',
	'flow-rev-message-hid-post' => 'Скриена објава',
	'flow-rev-message-edit-title' => 'Изменет наслов на тема',
	'flow-rev-message-create-header' => 'Создадено заглавие',
	'flow-rev-message-edit-header' => 'Изменето заглавие',
	'flow-rev-message-restored-post' => 'Откриена објава',
	'flow-rev-message-deleted-post' => 'Избришана објава',
	'flow-rev-message-censored-post' => 'Цензурирана објава',
	'flow-topic-history' => 'Историја на темата',
	'flow-topic-participants' => '{{PLURAL:$1|Темата ја започна $3|{{GENDER:$3|$3}}, {{GENDER:$4|$4}} и {{PLURAL:$2|еден друг|други}}|0=Досега ниокј не учествувал|2={{GENDER:$3|$3}} и {{GENDER:$4|$4}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Бидете први со коментар!|Коментари ($1)}}',
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
	'flow-notification-mention' => '$1 ве спомна во {{GENDER:$1|неговата|нејзината}} [$2 објава] во „$3“ на „$4“',
	'flow-notification-link-text-view-post' => 'Погл. објавата',
	'flow-notification-link-text-view-board' => 'Погл. таблата',
	'flow-notification-link-text-view-topic' => 'Погл. темата',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ви одговори}} на објавата',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|ви одговори}} на вашата објава во $2 на „$3“',
	'flow-notification-reply-email-batch-bundle-body' => '$1 и уште {{PLURAL:$5|еден друг|$4 други}} {{GENDER:$1|ви одговорија}} на вашата објава во $2 на „$3“',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|ве спомна}} на $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|ве спомна во неговата|ве спомна во нејзината}} во објава во „$2“ на „$3“',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|ја измени}} вашата објава',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|ја измени}} вашата објава во $2 на „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 и {{PLURAL:$5|уште еден друг|уште $4 други}} {{GENDER:$1|ја изменија}} вашата објава во $2 на „$3“',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|ја преименуваше}} вашата тема',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|ја преименуваше}} вашата тема „$2“ во „$3“ на „$4“',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|создаде}} нова тема на $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|создаде}} нова тема со наслов „$2“ на $3',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Дискусија|Дискусии}}',
	'echo-pref-tooltip-flow-discussion' => 'Извести ме кога на таблата со дискусија ќе се случи нешто поврзано со мене.',
	'flow-link-post' => 'објава',
	'flow-link-topic' => 'тема',
	'flow-link-history' => 'историја',
);

/** Marathi (मराठी)
 * @author V.narsikar
 */
$messages['mr'] = array(
	'flow-edit-summary-link' => 'संपादन सारांश',
	'flow-newtopic-title-placeholder' => 'संदेशाचा विषय',
	'flow-error-external' => 'आपले उत्तर जतन करण्यात त्रूटी घडली.आपले उत्तर जतन झाले नाही.<br /><small>मिळालेला त्रूटी संदेश असा होता: $1</small>',
	'flow-error-external-multi' => 'आपले उत्तर जतन करण्यात त्रूटी आढळल्या.आपले उत्तर जतन झाले नाही.<br />$1',
);

/** Norwegian Bokmål (norsk bokmål)
 * @author Danmichaelo
 */
$messages['nb'] = array(
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-user-anonymous' => 'Anonym',
	'flow-edit-header-link' => 'Rediger overskrift',
	'flow-header-empty' => 'Denne diskusjonssiden har ingen overskrift.',
	'flow-post-moderated-toggle-show' => '[Vis]',
	'flow-post-moderated-toggle-hide' => '[Skjul]',
	'flow-post-hidden' => '[skjult melding]',
	'flow-post-hidden-by' => '{{GENDER:$1|Skjult}} av $1 $2',
	'flow-post-deleted' => '[melding slettet]',
	'flow-post-deleted-by' => '{{GENDER:$1|Slettet}} av $1 $2',
	'flow-post-censored' => '[melding sensurert]',
	'flow-post-censored-by' => '{{GENDER:$1|Sensurert}} av $1 $2',
	'flow-post-actions' => 'handlinger',
	'flow-topic-actions' => 'handlinger',
	'flow-cancel' => 'Avbryt',
	'flow-newtopic-title-placeholder' => 'Meldingsemne',
	'flow-newtopic-content-placeholder' => 'Meldingstekst. Vær hyggelig!',
	'flow-newtopic-header' => 'Legg til et nytt emne',
	'flow-newtopic-save' => 'Legg til emne',
	'flow-newtopic-start-placeholder' => 'Start en ny diskusjon',
	'flow-reply-topic-placeholder' => '{{GENDER:$1|Kommentér}} «$2»',
	'flow-reply-placeholder' => 'Trykk for å {{GENDER:$1|svare}} $1. Vær hyggelig!', # Fuzzy
	'flow-reply-submit' => '{{GENDER:$1|Svar}}',
	'flow-reply-link' => '{{GENDER:$1|Svar}}',
	'flow-thank-link' => '{{GENDER:$1|Takk}}',
	'flow-talk-link' => 'Diskuter med {{GENDER:$1|$1}}',
	'flow-edit-post-submit' => 'Send inn endringer',
	'flow-post-edited' => 'Melding {{GENDER:$1|redigert}} av $1 $2',
	'flow-post-action-view' => 'Permanent lenke',
	'flow-post-action-post-history' => 'Meldingshistorikk',
	'flow-post-action-censor-post' => 'Sensurér melding',
	'flow-post-action-delete-post' => 'Slett melding',
	'flow-post-action-hide-post' => 'Skjul melding',
	'flow-post-action-edit-post' => 'Rediger melding',
	'flow-post-action-edit' => 'Rediger',
	'flow-post-action-restore-post' => 'Gjenopprett melding',
	'flow-topic-action-view' => 'Permalenke',
	'flow-topic-action-watchlist' => 'Overvåkningsliste',
	'flow-topic-action-edit-title' => 'Rediger tittel',
	'flow-topic-action-history' => 'Emnehistorikk',
	'flow-error-http' => 'Det oppsto en nettverksfeil. Meldingen din ble ikke lagret.',
	'flow-error-other' => 'Det oppsto en ukjent feil. Meldingen din ble ikke lagret.',
	'flow-error-external' => 'Det oppsto en feil under lagring av meldingen. Meldingen din ble ikke lagret.<br /><small>Feilmeldingen var: $1</small>',
	'flow-error-edit-restricted' => 'Du har ikke tilgang til å redigere denne meldingen.',
	'flow-error-external-multi' => 'Feil oppsto under lagring av meldingen. Meldingen din ble ikke lagret.<br />$1',
	'flow-error-missing-content' => 'Meldingen har ikke noe innhold. Innhold er påkrevd for at meldingen skal bli lagret.',
	'flow-error-missing-title' => 'Meldingen har ingen tittel. En tittel er påkrevd for at meldingen skal bli lagret.',
	'flow-error-parsoid-failure' => 'Innholdet kunne ikke parseres pga. et Parsord-problem.',
	'flow-error-missing-replyto' => 'Ingen "replyTo"-parameter ble sendt inn. Parameteren er påkrevd for "reply"-handlingen.',
	'flow-error-invalid-replyto' => 'Parameteren "replyTo" var ugyldig. Det angitte innlegget ble ikke funnet.',
	'flow-error-delete-failure' => 'Sletting av dette innlegget feilet.',
	'flow-error-hide-failure' => 'Skjuling av dette innlegget feilet.',
	'flow-error-missing-postId' => 'Ingen "postId"-parameter ble sendt inn. Parameteren er påkrevd for å redigere et innlegg.',
	'flow-error-invalid-postId' => 'Parameteren "postId" var ugyldig. Det angitte innlegget ble ikke funnet.',
	'flow-error-restore-failure' => 'Gjenoppretting av dette innlegget feilet.',
	'flow-edit-header-submit' => 'Lagre overskrift',
	'flow-edit-title-submit' => 'Endre tittel',
	'flow-rev-message-edit-post' => 'Rediger meldingsinnhold',
	'flow-rev-message-reply' => 'Nytt svar lagt inn',
	'flow-rev-message-new-post' => 'Samtale opprettet',
	'flow-rev-message-hid-post' => 'Skjult melding',
	'flow-rev-message-edit-title' => 'Redigering emne',
	'flow-rev-message-create-header' => 'Opprettet overskrift',
	'flow-rev-message-edit-header' => 'Redigerte overskrift',
	'flow-rev-message-restored-post' => 'Skjulte innlegg',
	'flow-rev-message-deleted-post' => 'Slettet melding',
	'flow-rev-message-censored-post' => 'Sensurerte melding',
	'flow-topic-history' => 'Samtalehistorikk',
	'flow-topic-participants' => '{{PLURAL:$1|0=Ingen deltakelse enda|$3 startet denne diskusjonen|{{GENDER:$3|$3}} og {{GENDER:$4|$4}}|{{GENDER:$3|$3}}, {{GENDER:$4|$4}} og $2 andre}}', # Fuzzy
	'flow-topic-comments' => '{{PLURAL:$1|0=Bli den første til å kommentere!|Kommentér ($1)}}',
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
	'flow-notification-mention' => '$1 {{GENDER:$1|nevnte}} deg i [$2 innlegget] sitt under «$3» på «$4»',
	'flow-notification-link-text-view-post' => 'Vis innlegg',
	'flow-notification-link-text-view-topic' => 'Vis samtale',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|svarte}} på meldingen din',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|svarte}} på innlegget ditt under $2 på «$3»',
);

/** Dutch (Nederlands)
 * @author Krinkle
 * @author Siebrand
 * @author Southparkfan
 * @author TBloemink
 */
$messages['nl'] = array(
	'flow-post-hidden' => '[bericht verborgen]',
	'flow-post-hidden-by' => '$2 {{GENDER:$1|verborgen}} door $1',
	'flow-post-deleted' => '[bericht verwijderd]',
	'flow-post-deleted-by' => '$2 {{GENDER:$1|verwijderd}} door $1',
	'flow-post-censored' => '[bericht gecensureerd]',
	'flow-post-censored-by' => '$2 {{GENDER:$1|gecensureerd}} door $1',
	'flow-cancel' => 'Annuleren',
	'flow-newtopic-title-placeholder' => 'Onderwerp',
	'flow-newtopic-content-placeholder' => 'Tekst van het bericht. Wees beleefd!',
	'flow-newtopic-header' => 'Nieuw onderwerp toevoegen',
	'flow-newtopic-save' => 'Onderwerp toevoegen',
	'flow-newtopic-start-placeholder' => 'Nieuw onderwerp',
	'flow-reply-placeholder' => 'Klik hier om te {{GENDER:$1|reageren}} op $1. Wees beleefd!', # Fuzzy
	'flow-reply-submit' => '{{GENDER:$1|Reageren}}',
	'flow-edit-post-submit' => 'Wijzigingen opslaan',
	'flow-post-edited' => 'Bericht $2 {{GENDER:$1|bewerkt}} door $1',
	'flow-post-action-view' => 'Permanente koppeling',
	'flow-post-action-post-history' => 'Berichtgeschiedenis',
	'flow-post-action-censor-post' => 'Bericht censureren',
	'flow-post-action-delete-post' => 'Bericht verwijderen',
	'flow-post-action-hide-post' => 'Bericht verbergen',
	'flow-post-action-edit-post' => 'Bericht bewerken',
	'flow-post-action-edit' => 'Bewerken',
	'flow-post-action-restore-post' => 'Bericht terugplaatsen',
	'flow-topic-action-edit-title' => 'Titel wijzigen',
	'flow-error-other' => 'Er is een onverwachte fout opgetreden. Uw bericht is niet opgeslagen.',
	'flow-error-edit-restricted' => 'U mag dit bericht niet bewerken.',
	'flow-notification-edit' => '$1 {{GENDER:$1|heeft}} een [$5 bericht] geplaatst in $2 op [[$3|$4]].',
	'flow-notification-edit-bundle' => '$1 en $5 {{PLURAL:$6|andere gebruiker|anderen}} {{GENDER:$1|hebben}} een [$4 bericht] geplaatst in $2 op "$3".',
	'flow-notification-mention' => '$1 heeft u genoemd in {{GENDER:$1|zijn|haar|zijn/haar}} [$2 bericht] in "$3" op "$4"',
	'flow-notification-link-text-view-post' => 'Bericht bekijken',
	'flow-notification-link-text-view-topic' => 'Onderwerp bekijken',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|heeft}} u genoemd op $2',
	'flow-notification-mention-email-batch-body' => '$1 heeft u genoemd in {{GENDER:$1|zijn|haar|zijn/haar}} bericht in "$2" op "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|heeft}} uw bericht bewerkt',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|heeft}} uw bericht bewerkt in $2 op "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 en $4 {{PLURAL:$5|andere gebruiker|anderen}} {{GENDER:$1|hebben}} een bericht bewerkt in $2 op "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|heeft}} uw onderwerp een andere naam gegeven',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|heeft}} uw onderwerp $2 op "$4" hernoemd naar $3', # Fuzzy
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|heeft}} een nieuw onderwerp aangemaakt op $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|heeft}} op $3 een nieuw onderwerp aangemaakt met de naam "$2"',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Overleg|Overleggen}}',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'flow-desc' => 'Sistèma de gestion del flux de trabalh',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-edit-summary-link' => 'Resumit',
	'flow-disclaimer' => "En clicant sul boton « Apondre un messatge », acceptatz nos [https://wikimediafoundation.org/wiki/Terms_of_Use/fr condicions d'utilizacion] e acceptatz de plaçar irrevocablament vòstra contribucion jos [http://creativecommons.org/licenses/by-sa/3.0/deed.fr licéncia Creative Commons paternitat-partiment de las condicions inicialas a l'identica 3.0] e [http://www.gnu.org/copyleft/fdl.html GFDL]. Acceptatz d’èsser creditat pels reütilizaires al minimum via un iperligam o una URL jos la licéncia Creative Commons.",
	'flow-post-hidden' => '[nòta amagada]',
	'flow-post-hidden-by' => 'Amagat per $1 $2', # Fuzzy
	'flow-post-deleted' => '[messatge suprimit]',
	'flow-post-deleted-by' => 'Suprimit per $1 $2', # Fuzzy
	'flow-post-censored' => '[nòta censurada]',
	'flow-post-censored-by' => 'Censurat per $1 $2', # Fuzzy
	'flow-post-actions' => 'accions',
	'flow-topic-actions' => 'accions',
	'flow-cancel' => 'Anullar',
	'flow-newtopic-title-placeholder' => 'Objècte del messatge',
	'flow-newtopic-content-placeholder' => 'Tèxte del messatge. Siatz gent !',
	'flow-newtopic-header' => 'Apondre un subjècte novèl',
	'flow-newtopic-save' => 'Apondre un subjècte',
	'flow-newtopic-start-placeholder' => 'Clicatz aicí per començar una novèla discussion. Siatz gent !',
	'flow-reply-placeholder' => 'Clicatz aicí per respondre a $1. Siatz gent !', # Fuzzy
	'flow-reply-submit' => 'Postar una responsa',
	'flow-edit-post-submit' => 'Sometre las modificacions',
	'flow-post-action-view' => 'Ligam permanent',
	'flow-post-action-post-history' => 'Istoric de las publicacions',
	'flow-post-action-censor-post' => 'Censurar la nòta',
	'flow-post-action-delete-post' => 'Suprimir lo messatge',
	'flow-post-action-hide-post' => 'Amagar la nòta',
	'flow-post-action-edit-post' => 'Modificar la publicacion',
	'flow-post-action-edit' => 'Modificar',
	'flow-post-action-restore-post' => 'Restablir lo messatge',
	'flow-topic-action-edit-title' => 'Modificar lo títol',
	'flow-topic-action-history' => 'Istoric dels subjèctes',
	'flow-error-http' => "Una error s'es producha en comunicant amb lo servidor. Vòstre messatge es pas estat enregistrat.",
	'flow-error-other' => "Una error imprevista s'es producha. Vòstre messatge es pas estat enregistrat.",
	'flow-error-external' => "Una error s'es producha al moment de l'enregistrament de vòstre messatge. Es pas estat enregistrat.<br /><small>Lo messatge d'error recebut èra :$1</small>",
	'flow-error-external-multi' => "D'errors se son produchas al moment de l'enregistrament de vòstre messatge. Vòstre messatge es pas estat enregistrat.<br /> $1",
	'flow-error-missing-content' => 'Lo messatge a pas cap de contengut. Es requesit per enregistrar un messatge novèl.',
	'flow-error-missing-title' => 'Lo subjècte a pas cap de títol. Es requesit per enregistrar un subjècte novèl.',
	'flow-error-parsoid-failure' => "Impossible d'analisar lo contengut a causa d'una pana de Parsoid.",
	'flow-error-missing-replyto' => "Cap de paramètre replyTo es pas estat provesit. Aqueste paramètre es requesit per l'accion « respondre ».", # Fuzzy
	'flow-error-invalid-replyto' => 'Lo paramètre replyTo èra pas valid. Lo messatge especificat es introbable.', # Fuzzy
	'flow-error-delete-failure' => "Fracàs de la supression d'aquesta entrada.",
	'flow-error-hide-failure' => "L'amagatge d'aqueste element a fracassat.",
	'flow-error-missing-postId' => 'Cap de paramètre postId es pas estat provesit. Aqueste paramètre es requesit per manipular un messatge.', # Fuzzy
	'flow-error-invalid-postId' => 'Lo paramètre postId èra pas valid. Lo messatge especificat es introbable.', # Fuzzy
	'flow-error-restore-failure' => "Fracàs del restabliment d'aquesta entrada.",
	'flow-summaryedit-submit' => 'Enregistrar lo resumit',
	'flow-edit-title-submit' => 'Cambiar lo títol',
	'flow-rev-message-reply' => 'Novèla responsa publicada',
	'flow-rev-message-new-post' => 'Subjècte creat',
	'flow-topic-history' => 'Istoric dels subjèctes',
	'flow-comment-restored' => 'Comentari restablit',
	'flow-comment-deleted' => 'Comentari suprimit',
	'flow-comment-hidden' => 'Comentari amagat',
	'flow-paging-rev' => 'Subjèctes los mai recents',
	'flow-paging-fwd' => 'Subjèctes mai ancians',
	'flow-last-modified' => 'Darrièr cambiament $1',
	'flow-notification-reply' => '$1 a respondut a vòstra [$5 nòta] sus $2 en [[$3|$4]].', # Fuzzy
	'flow-notification-edit' => '$1 a modificat vòstra [$5 nòta] sus $2 en [[$3|$4]].', # Fuzzy
	'flow-notification-newtopic' => '$1 a creat un [$5 subjècte novèl] en [[$2|$3]] : $4.', # Fuzzy
	'flow-notification-rename' => '$1 a modificat lo títol de [$2 $3] en « $4 » sus [[$5|$6]]', # Fuzzy
);

/** Polish (polski)
 * @author Chrumps
 * @author Woytecr
 */
$messages['pl'] = array(
	'flow-post-moderated-toggle-show' => '[Pokaż]',
	'flow-post-moderated-toggle-hide' => '[Ukryj]',
	'flow-post-hidden' => '[post ukryty]',
	'flow-post-hidden-by' => '{{GENDER:$1|Ukryty}} przez $1 $2',
	'flow-post-deleted' => '[post usunięty]',
	'flow-post-deleted-by' => '{{GENDER:$1|Usunięty}} przez $1 $2',
	'flow-cancel' => 'Anuluj',
	'flow-newtopic-title-placeholder' => 'Temat wiadomości',
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
 */
$messages['pt-br'] = array(
	'flow-desc' => 'Sistema de Gerenciamento do Fluxo de Trabalho',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'flow-desc' => 'Sisteme de Gestione de le Flusse de fatìe',
	'flow-specialpage' => '$1 &ndash; Flusse',
	'flow-edit-summary-link' => "Cange 'u Riepiloghe",
	'flow-post-deleted' => '[messàgge scangellate]',
	'flow-post-actions' => 'aziune',
	'flow-topic-actions' => 'aziune',
	'flow-cancel' => 'Annulle',
	'flow-newtopic-title-placeholder' => "Oggette d'u messàgge",
	'flow-newtopic-content-placeholder' => 'Messàgge de teste. Si belle!',
	'flow-newtopic-header' => "Aggiunge 'n'argomende nuève",
	'flow-newtopic-save' => "Aggiunge 'n'argomende",
	'flow-newtopic-start-placeholder' => "Cazze aqquà pe accumenzà 'nu 'ngazzamende nuève. Sì belle!",
	'flow-reply-placeholder' => 'Cazze pe responnere a $1. Sì belle!', # Fuzzy
	'flow-reply-submit' => "Manne 'na resposte",
	'flow-post-action-delete-post' => "Scangìlle 'u messàgge",
	'flow-post-action-restore-post' => "Repristine 'u messàgge",
	'flow-topic-action-edit-title' => "Cange 'u titole",
	'flow-error-http' => "Ha assute 'n'errore condattanne 'u server. 'U messàgge tune non g'ha state reggistrate.",
	'flow-error-other' => "Ha assute 'n'errore. 'U messàgge tune non g'ha state reggistrate.",
	'flow-summaryedit-submit' => "Reggistre 'u riepiloghe",
	'flow-edit-title-submit' => "Cange 'u titole",
);

/** Russian (русский)
 * @author Okras
 */
$messages['ru'] = array(
	'flow-desc' => 'Система управления потоками работ',
	'flow-specialpage' => '$1 &ndash; Поток',
	'flow-post-moderated-toggle-show' => '[Показать]',
	'flow-post-moderated-toggle-hide' => '[Скрыть]',
	'flow-post-hidden' => '[сообщение скрыто]',
	'flow-post-hidden-by' => 'Скрыто {{GENDER:$1|участником|участницей}} $1 $2',
	'flow-post-deleted' => '[сообщение удалено]',
	'flow-post-deleted-by' => 'Удалено {{GENDER:$1|участником|участницей}} $1 $2',
	'flow-post-actions' => 'действия',
	'flow-topic-actions' => 'действия',
	'flow-cancel' => 'Отменить',
	'flow-newtopic-title-placeholder' => 'Тема сообщения',
	'flow-newtopic-content-placeholder' => 'Текст сообщения. Будьте вежливы!',
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
	'flow-post-action-delete-post' => 'Удалить сообщение',
	'flow-post-action-hide-post' => 'Скрыть сообщение',
	'flow-post-action-edit-post' => 'Редактировать сообщение',
	'flow-post-action-edit' => 'Править',
	'flow-post-action-restore-post' => 'Восстановить сообщение',
	'flow-topic-action-edit-title' => 'Редактировать заголовок',
	'flow-topic-action-history' => 'История темы',
	'flow-error-http' => 'Произошла ошибка при обращении к серверу. Ваше сообщение не было сохранено.',
	'flow-error-other' => 'Произошла непредвиденная ошибка. Ваше сообщение не было сохранено.',
	'flow-error-external' => 'Произошла ошибка при обращении к серверу. Ваше сообщение не было сохранено.<br /><small>Было получено следующее сообщение об ошибке: $1</small>',
	'flow-error-edit-restricted' => 'Вам не разрешено редактировать это сообщение.',
	'flow-error-missing-content' => 'Сообщение не имеет содержимого. Для сохранения нового сообщения требуется содержимое.',
	'flow-error-missing-title' => 'Тема не имеет заголовка. Заголовок необходим для сохранения новой темы.',
	'flow-error-parsoid-failure' => 'Не удаётся выполнить разбор содержимого из-за сбоя Parsoid.',
	'flow-error-delete-failure' => 'Не удалось удалить этот элемент.',
	'flow-error-hide-failure' => 'Не удалось скрыть этот элемент.',
	'flow-error-restore-failure' => 'Не удалось восстановить этот элемент.',
	'flow-edit-title-submit' => 'Изменить название',
	'flow-rev-message-reply' => 'Новый ответ размещён',
	'flow-rev-message-new-post' => 'Тема создана',
	'flow-rev-message-deleted-post' => 'Удалённое сообщение',
	'flow-topic-history' => 'История темы',
	'flow-comment-restored' => 'Восстановленный комментарий',
	'flow-comment-deleted' => 'Удалённый комментарий',
	'flow-comment-hidden' => 'Скрытый комментарий',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|ответил|ответила}} на ваше сообщение',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|упомянул|упомянула}} вас в $2',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|отредактировал|отредактировала}} ваше сообщение',
);

/** Slovenian (slovenščina)
 * @author Dbc334
 * @author Eleassar
 */
$messages['sl'] = array(
	'flow-post-hidden-by' => '{{GENDER:$1|Skril uporabnik|Skrila uporabnica}} $1 $2',
	'flow-post-deleted-by' => '{{GENDER:$1|Izbrisal uporabnik|Izbrisala uporabnica}} $1 $2',
	'flow-post-censored-by' => '{{GENDER:$1|Cenzuriral uporabnik|Cenzurirala uporabnica}} $1 $2',
	'flow-reply-placeholder' => 'Kliknite za {{GENDER:$1|odgovor uporabniku|odgovor uporabnici}} $1. Bodite prijazni!', # Fuzzy
	'flow-error-missing-replyto' => 'Podan ni bil noben parameter »odgovori na«. Ta parameter je za dejanje »odgovorite« obvezen.',
	'flow-error-invalid-replyto' => 'Parameter »odgovori« je bil neveljaven. Navedene objave ni bilo mogoče najti.',
	'flow-error-missing-postId' => 'Podan ni bil noben parameter »postId«. Ta parameter je za upravljanje z objavo obvezen.',
	'flow-error-invalid-postId' => 'Parameter »postId« je bil neveljaven. Navedene objave ni bilo mogoče najti.',
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
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Pogovor|Pogovori}}',
	'echo-pref-tooltip-flow-discussion' => 'Obvesti me, ko se na pogovornih deskah pojavijo dejanja v zvezi z mano.',
);

/** Swedish (svenska)
 * @author Ainali
 * @author Jopparn
 * @author Lokal Profil
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'flow-desc' => 'Arbetsflödeshanteringssystem',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-user-anonymous' => 'Anonym',
	'flow-user-moderated' => 'Modererad användare',
	'flow-edit-header-link' => 'Redigera sidhuvud',
	'flow-header-empty' => 'Denna diskussionssida har för närvarande ingen rubrik.',
	'flow-post-moderated-toggle-show' => '[Visa]',
	'flow-post-moderated-toggle-hide' => '[Dölj]',
	'flow-post-hidden' => '[inlägg dolt]',
	'flow-post-hidden-by' => '{{GENDER:$1|Dold}} av $1 $2',
	'flow-post-deleted' => '[inlägg raderat]',
	'flow-post-deleted-by' => '{{GENDER:$1|Raderad}} av $1 $2',
	'flow-post-censored' => '[inlägg censurerat]',
	'flow-post-censored-by' => '{{GENDER:$1|Censurerad}} av $1 $2',
	'flow-post-actions' => 'åtgärder',
	'flow-topic-actions' => 'åtgärder',
	'flow-cancel' => 'Avbryt',
	'flow-newtopic-title-placeholder' => 'Meddelandeämne',
	'flow-newtopic-content-placeholder' => 'Meddelandetext. Var trevlig!',
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
	'flow-post-action-censor-post' => 'Censurera inlägg',
	'flow-post-action-delete-post' => 'Ta bort inlägg',
	'flow-post-action-hide-post' => 'Dölj inlägg',
	'flow-post-action-edit-post' => 'Redigera inlägg',
	'flow-post-action-edit' => 'Redigera',
	'flow-post-action-restore-post' => 'Återställ inlägg',
	'flow-topic-action-view' => 'Permanent länk',
	'flow-topic-action-watchlist' => 'Bevakningslista',
	'flow-topic-action-edit-title' => 'Redigera titel',
	'flow-topic-action-history' => 'Ämneshistorik',
	'flow-error-http' => 'Ett fel uppstod när servern kontaktades. Ditt inlägg har inte sparats.',
	'flow-error-other' => 'Ett oväntat fel uppstod. Ditt inlägg har inte sparats.',
	'flow-error-external' => 'Ett fel uppstod när ditt inlägg sparades. Ditt inlägg har inte sparats.<br /><small>Felmeddelandet var: $1</small>',
	'flow-error-edit-restricted' => 'Du har inte rätt att redigera detta inlägg.',
	'flow-error-external-multi' => 'Fel uppstod när ditt inlägg sparades. Ditt inlägg har inte sparats.<br />$1',
	'flow-error-missing-content' => 'Inlägget har inget innehåll. Innehåll krävs för att spara ett nytt inlägg.',
	'flow-error-missing-title' => 'Ämnet har ingen titel. En titel krävs för att spara ett nytt ämne.',
	'flow-error-parsoid-failure' => 'Det gick inte att parsa innehållet på grund av ett Parsoid-fel.',
	'flow-error-missing-replyto' => 'Ingen "replyTo" parameter tillhandahölls. Den här parametern krävs för åtgärden "svara".',
	'flow-error-invalid-replyto' => '"replyTo" parametern var ogiltig. Det angivna inlägget kunde inte hittas.',
	'flow-error-delete-failure' => 'Borttagning av detta objekt misslyckades.',
	'flow-error-hide-failure' => 'Döljandet av detta objekt misslyckades.',
	'flow-error-missing-postId' => 'Ingen "postId" parameter tillhandahölls. Denna parameter krävs för att påverka ett inlägg.',
	'flow-error-invalid-postId' => '"postId" parametern var ogiltig. Det angivna inlägget kunde inte hittas.',
	'flow-error-restore-failure' => 'Det gick inte att återställa objektet.',
	'flow-edit-header-submit' => 'Spara rubrik',
	'flow-edit-title-submit' => 'Ändra titel',
	'flow-rev-message-edit-post' => 'Redigerade inläggets innehåll',
	'flow-rev-message-reply' => 'Nytt svar postat',
	'flow-rev-message-new-post' => 'Ämnet skapat',
	'flow-rev-message-hid-post' => 'Dölj inlägg',
	'flow-rev-message-edit-title' => 'Redigerade ämnestiteln',
	'flow-rev-message-create-header' => 'Skapade rubrik',
	'flow-rev-message-edit-header' => 'Redigera rubrik',
	'flow-rev-message-restored-post' => 'Visa inlägg',
	'flow-rev-message-deleted-post' => 'Raderade inlägg',
	'flow-rev-message-censored-post' => 'Censurerade inlägg',
	'flow-topic-history' => 'Ämneshistorik',
	'flow-topic-participants' => '{{PLURAL:$1|$3 påbörjade detta ämne|{{GENDER:$3|$3}}, {{GENDER:$4|$4}} och {{PLURAL:$2|annan|andra}}|0=Inget deltagande ännu|2={{GENDER:$3|$3}} och {{GENDER:$4|$4}}}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Var först med att kommentera!|Kommentar ($1)}}',
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
	'flow-notification-mention' => '$1 {{GENDER:$1|nämnde}} dig i deras [$2 inlägg] i "$3" på "$4"',
	'flow-notification-link-text-view-post' => 'Visa inlägg',
	'flow-notification-link-text-view-board' => 'Visa forum',
	'flow-notification-link-text-view-topic' => 'Visa ämne',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|svarade}} på ditt inlägg',
	'flow-notification-reply-email-batch-body' => '$1 {{GENDER:$1|svarade}} på ditt inlägg i $2 på "$3"',
	'flow-notification-reply-email-batch-bundle-body' => '$1 och $4 {{PLURAL:$5|annan|andra}} {{GENDER:$1|svarade}} på ditt inlägg i $2 på "$3"',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|omnämnde}} dig på $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|omnämnde}} dig i deras inlägg i "$2" på "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|redigerade}} ditt inlägg',
	'flow-notification-edit-email-batch-body' => '$1 {{GENDER:$1|redigerade}} ditt inlägg i $2 på "$3"',
	'flow-notification-edit-email-batch-bundle-body' => '$1 och $4 {{PLURAL:$5|annan|andra}} {{GENDER:$1|redigerade}} ett inlägg i $2 på "$3"',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|byt namn på}} ditt ämne',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|byt namn på}} ditt ämne "$2" till "$3" på "$4"',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|skapade}} ett nytt ämne på $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|skapade}} ett ny ämne med titeln "$2" på $3',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Diskussion|Diskussioner}}',
	'echo-pref-tooltip-flow-discussion' => 'Meddela mig när åtgärder som rör mig förekommer i diskussionsforumet.',
	'flow-link-post' => 'inlägg',
	'flow-link-topic' => 'ämne',
	'flow-link-history' => 'historik',
);

/** Turkish (Türkçe)
 * @author Rapsar
 */
$messages['tr'] = array(
	'flow-notification-mention' => '$1, "$4" sayfasındaki "$3" başlığındaki [$2 değişikliğinde] sizden {{GENDER:$1|bahsetti}}',
	'flow-notification-mention-email-subject' => '$1, $2 sayfasında sizden {{GENDER:$1|bahsetti}}',
	'flow-notification-mention-email-batch-body' => '$1, "$3" sayfasındaki "$2" başlığında sizden {{GENDER:$1|bahsetti}}',
	'flow-link-history' => 'geçmiş',
);

/** Ukrainian (українська)
 * @author Andriykopanytsia
 */
$messages['uk'] = array(
	'flow-desc' => 'Система управління робочими процесами',
	'flow-specialpage' => '$1 &ndash; Потік',
	'flow-user-anonymous' => 'Анонім',
	'flow-user-moderated' => 'Обмежений користувач',
	'flow-edit-header-link' => 'Редагувати заговок',
	'flow-header-empty' => 'Ця сторінка обговорення не має зараз заголовка.',
	'flow-post-moderated-toggle-show' => '[Показати]',
	'flow-post-moderated-toggle-hide' => '[Сховати]',
	'flow-post-hidden' => '[прихована публікація]',
	'flow-post-hidden-by' => '$1 {{GENDER:$1|приховав|приховала}} о $2',
	'flow-post-deleted' => '[пост видалено]',
	'flow-post-deleted-by' => '$1 {{GENDER:$1|вилучив|вилучила}} о $2',
	'flow-post-censored' => '[цензурна публікація]',
	'flow-post-censored-by' => '$1 {{GENDER:$1|процензурував|процензурував}} о $2',
	'flow-post-actions' => 'дії',
	'flow-topic-actions' => 'дії',
	'flow-cancel' => 'Скасувати',
	'flow-newtopic-title-placeholder' => 'Тема повідомлення',
	'flow-newtopic-content-placeholder' => 'Текст повідомлення. Будьте приємним!',
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
	'flow-post-action-censor-post' => 'Цензурувати публікацію',
	'flow-post-action-delete-post' => 'Видалити публікацію',
	'flow-post-action-hide-post' => 'Приховати публікацію',
	'flow-post-action-edit-post' => 'Редагувати публікацію',
	'flow-post-action-edit' => 'Редагувати',
	'flow-post-action-restore-post' => 'Відновити публікацію',
	'flow-topic-action-view' => 'Постійне посилання',
	'flow-topic-action-watchlist' => 'Список спостереження',
	'flow-topic-action-edit-title' => 'Змінити заголовок',
	'flow-topic-action-history' => 'Історія теми',
	'flow-error-http' => 'Сталася помилка при зверненні до сервера. Ваша публікація не збережена.',
	'flow-error-other' => 'Неочікувана помилка. Ваш публікація не врятована.',
	'flow-error-external' => 'Сталася помилка під час збереження Вашого вкладу. Ваше повідомлення не було збережено.<br /><small>Отримане повідомлення було:$1</small>',
	'flow-error-edit-restricted' => 'Вам не дозволено редагувати цей допис.',
	'flow-error-external-multi' => 'Сталася помилка під час збереження Вашого внеску. Ваше повідомлення не було збережено.<br /> $1',
	'flow-error-missing-content' => 'Публікація не має ніякого вмісту. Необхідний вміст, щоб зберегти нову публікацію.',
	'flow-error-missing-title' => 'Тема не має назви. Потрібна назва, щоб зберегти нову тему.',
	'flow-error-parsoid-failure' => 'Не вдалося проаналізувати вміст через помилку Parsoid.',
	'flow-error-missing-replyto' => 'Параметр „reply-to“ не був наданий. Цей параметр є обов\'язковим для дії "відповідь".',
	'flow-error-invalid-replyto' => 'Параметр „replyTo“ неприпустимий. Не вдалося знайти вказану публікацію.',
	'flow-error-delete-failure' => 'Не вдалося видалити цей елемент.',
	'flow-error-hide-failure' => 'Приховання цього елементу не вдалося.',
	'flow-error-missing-postId' => 'Параметр „postId“ не був наданий. Цей параметр вимагає, щоб маніпулювати публікацією.',
	'flow-error-invalid-postId' => 'Параметр „postId“ неприпустимий. Не вдалося знайти вказану публікацію.',
	'flow-error-restore-failure' => 'Не вдалося виконати відновлення цього елемента.',
	'flow-edit-header-submit' => 'Зберегти заголовок',
	'flow-edit-title-submit' => 'Змінити заголовок',
	'flow-rev-message-edit-post' => 'Відредаговано вміст допису',
	'flow-rev-message-reply' => 'Нова відповідь опублікована',
	'flow-rev-message-new-post' => 'Тема створена',
	'flow-rev-message-hid-post' => 'Прихований допис',
	'flow-rev-message-edit-title' => 'Відредагована назва теми',
	'flow-rev-message-create-header' => 'Створений заголовок',
	'flow-rev-message-edit-header' => 'Відредагований заголовок',
	'flow-rev-message-restored-post' => 'Неприхований допис',
	'flow-rev-message-deleted-post' => 'Видалений допис',
	'flow-rev-message-censored-post' => 'Цензурований допис',
	'flow-topic-history' => 'Історія теми',
	'flow-topic-participants' => '{{PLURAL:$1|0=Ще немає учасників|{{GENDER:$3|$3 розпочав|$3 розпочала}} цю тему|{{GENDER:$3|$3}} та {{GENDER:$4|$4}}|{{GENDER:$3|$3}}, {{GENDER:$4|$4}} та $2 інші}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Залиште перший коментар!|Коментар ($1)|Коментарі ($1)|Коментарів ($1)}}',
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
	'flow-notification-mention' => '$1 {{GENDER:$1|згадав|згадала}} вас у своєму [$2 дописі] у "$3" на "$4"',
	'flow-notification-link-text-view-post' => 'Переглянути допис',
	'flow-notification-link-text-view-board' => 'Переглянути стіну',
	'flow-notification-link-text-view-topic' => 'Перегляд теми',
	'flow-notification-reply-email-subject' => '$1 {{GENDER:$1|відповів|відповіла}} на ваш допис',
	'flow-notification-reply-email-batch-body' => '$1  {{GENDER:$1|відповів|відповіла}} на ваш допис у $2 на $3.',
	'flow-notification-reply-email-batch-bundle-body' => '$1 та $4 {{PLURAL:$5|інший|інші|інших}} {{GENDER:$1|відповіли}} на ваш допис у $2 на "$3".',
	'flow-notification-mention-email-subject' => '$1 {{GENDER:$1|згадав|згадала}} вас на $2',
	'flow-notification-mention-email-batch-body' => '$1 {{GENDER:$1|згадав|згадала}} вас у своєму дописі у "$2" на "$3"',
	'flow-notification-edit-email-subject' => '$1 {{GENDER:$1|відредагував|відредагувала}} ваш допис',
	'flow-notification-edit-email-batch-body' => '$1  {{GENDER:$1|відредагував|відредагувала}} ваш допис у $2 на „$3“',
	'flow-notification-edit-email-batch-bundle-body' => '$1 та $4 {{PLURAL:$5|інший|інші|інших}} {{GENDER:$1|відредагував|відредагувала}} допис у $2 на "$3".',
	'flow-notification-rename-email-subject' => '$1 {{GENDER:$1|перейменував|перейменувала}} вашу тему',
	'flow-notification-rename-email-batch-body' => '$1 {{GENDER:$1|перейменував|перейменувала}} вашу тему   з „$2“ на „$3“  у „$4“',
	'flow-notification-newtopic-email-subject' => '$1 {{GENDER:$1|створив|створила}} нову тему на $2',
	'flow-notification-newtopic-email-batch-body' => '$1 {{GENDER:$1|створив|створила}} нову тему під назвою "$2" на $3',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|Обговорення|Обговорення|Обговорень}}',
	'echo-pref-tooltip-flow-discussion' => "Повідомити мене, коли дії, пов'язані зі мною, виникнуть на стіні обговорення.",
	'flow-link-post' => 'допис',
	'flow-link-topic' => 'тема',
	'flow-link-history' => 'історія',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'flow-desc' => 'Hệ thống quản lý luồng công việc',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-user-anonymous' => 'Vô danh',
	'flow-user-moderated' => 'Người dùng bị kiểm duyệt',
	'flow-edit-header-link' => 'Sửa đầu đề',
	'flow-header-empty' => 'Trang thảo luận này hiện không có đầu đề.',
	'flow-post-moderated-toggle-show' => '[Xem]',
	'flow-post-moderated-toggle-hide' => '[Ẩn]',
	'flow-post-hidden' => '[bài đăng bị ẩn]',
	'flow-post-hidden-by' => 'Ẩn bởi $1 $2',
	'flow-post-deleted' => '[bài đăng bị xóa]',
	'flow-post-deleted-by' => 'Xóa bởi $1 $2',
	'flow-post-censored' => '[bài đăng bị kiểm duyệt]',
	'flow-post-censored-by' => 'Kiểm duyệt bởi $1 $2',
	'flow-post-actions' => 'tác vụ',
	'flow-topic-actions' => 'tác vụ',
	'flow-cancel' => 'Hủy bỏ',
	'flow-newtopic-title-placeholder' => 'Tiêu đề tin nhắn',
	'flow-newtopic-content-placeholder' => 'Văn bản tin nhắn. Hãy có thái độ thân thiện!',
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
	'flow-post-action-censor-post' => 'Kiểm duyệt bài đăng',
	'flow-post-action-delete-post' => 'Xóa bài đăng',
	'flow-post-action-hide-post' => 'Ẩn bài đăng',
	'flow-post-action-edit-post' => 'Sửa bài đăng',
	'flow-post-action-edit' => 'Sửa đổi',
	'flow-post-action-restore-post' => 'Phục hồi bài đăng',
	'flow-topic-action-view' => 'Liên kết thường trực',
	'flow-topic-action-watchlist' => 'Danh sách theo dõi',
	'flow-topic-action-edit-title' => 'Sửa tiêu đề',
	'flow-topic-action-history' => 'Lịch sử chủ đề',
	'flow-error-http' => 'Đã xuất hiện lỗi khi liên lạc với máy chủ. Bài đăng của bạn không được lưu.',
	'flow-error-other' => 'Đã xuất hiện lỗi bất ngờ. Bài đăng của bạn không được lưu.',
	'flow-error-external' => 'Đã xuất hiện lỗi khi lưu bài đăng của bạn. Bài đăng của bạn không được lưu.<br /><small>Lỗi nhận được là: $1</small>',
	'flow-error-edit-restricted' => 'Bạn không có quyền sửa đổi bài đăng này.',
	'flow-error-external-multi' => 'Đã xuất hiện lỗi khi lưu bài đăng của bạn. Bài đăng của bạn không được lưu.<br />$1',
	'flow-error-missing-content' => 'Bài đăng không có nội dung. Bài đăng mới phải có nội dung để lưu.',
	'flow-error-missing-title' => 'Chủ đề không có tiêu đề. Chủ đề phải có tiêu đề để lưu.',
	'flow-error-parsoid-failure' => 'Không thể phân tích nội dung vì Parsoid bị thất bại.',
	'flow-error-missing-replyto' => 'Tham số “replyTo” không được cung cấp. Tham số này cần để thực hiện tác vụ “trả lời”.',
	'flow-error-invalid-replyto' => 'Tham số “replyTo” có giá trị không hợp lệ. Không tìm thấy bài đăng.',
	'flow-error-delete-failure' => 'Thất bại khi xóa mục này.',
	'flow-error-hide-failure' => 'Thất bại khi ẩn mục này.',
	'flow-error-missing-postId' => 'Tham số “postId” không được cung cấp. Tham số này cần để xóa hoặc phục hồi bài đăng.',
	'flow-error-invalid-postId' => 'Tham số “postId” có giá trị không hợp lệ. Không tìm thấy bài đăng.',
	'flow-error-restore-failure' => 'Thất bại khi phục hồi mục này.',
	'flow-edit-header-submit' => 'Lưu đầu đề',
	'flow-edit-title-submit' => 'Thay đổi tiêu đề',
	'flow-rev-message-edit-post' => 'Đã sửa nội dung bài đăng',
	'flow-rev-message-reply' => 'Đã đăng bài trả lời',
	'flow-rev-message-new-post' => 'Đã tạo chủ đề',
	'flow-rev-message-hid-post' => 'Đã ẩn bài đăng',
	'flow-rev-message-edit-title' => 'Đã sửa đổi tiêu đề của chủ đề',
	'flow-rev-message-create-header' => 'Đã tạo đầu đề',
	'flow-rev-message-edit-header' => 'Đã sửa đổi đầu đề',
	'flow-rev-message-restored-post' => 'Đã hiện bài đăng',
	'flow-rev-message-deleted-post' => 'Đã xóa bài đăng',
	'flow-rev-message-censored-post' => 'Kiểm duyệt bài đăng',
	'flow-topic-history' => 'Lịch sử chủ đề',
	'flow-topic-participants' => '{{PLURAL:$1|$3 đã bắt đầu chủ đề này|$3, $4, và {{PLURAL:$2|một người|những người}} khác|0=Chưa có ai tham gia|2=$3 và $4}}',
	'flow-topic-comments' => '{{PLURAL:$1|0=Hãy là người đầu tiên bình luận!|Bình luận ($1)}}',
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
	'flow-notification-mention' => '$1 đã nói đến bạn trong [$2 bài đăng] của họ về “$3” tại “$4”',
	'flow-notification-link-text-view-post' => 'Xem bài đăng',
	'flow-notification-link-text-view-board' => 'Xem bảng tin',
	'flow-notification-link-text-view-topic' => 'Xem chủ đề',
	'flow-notification-reply-email-subject' => '$1 đã trả lời bài đăng của bạn',
	'flow-notification-reply-email-batch-body' => '$1 đã trả lời bài đăng của bạn về $2 tại “$3”',
	'flow-notification-reply-email-batch-bundle-body' => '$1 và $4 {{PLURAL:$5}}người khác đã trả lời bài đăng của bạn về $2 tại “$3”',
	'flow-notification-mention-email-subject' => '$1 đã nói đến bạn tại $2',
	'flow-notification-mention-email-batch-body' => '$1 đã nói đến bạn trong bài đăng của họ về “$2” tại “$3”',
	'flow-notification-edit-email-subject' => '$1 đã sửa đổi bài đăng của bạn',
	'flow-notification-edit-email-batch-body' => '$1 đã sửa đổi bài đăng của bạn về $2 tại “$3”',
	'flow-notification-edit-email-batch-bundle-body' => '$1 và $4 {{PLURAL:$5}}người khác đã sửa đổi một bài đăng về $2 tại “$3”',
	'flow-notification-rename-email-subject' => '$1 đã đổi tên chủ đề của bạn',
	'flow-notification-rename-email-batch-body' => '$1 đã đổi tên chủ đề của bạn từ “$2” thành “$3” tại “$4”',
	'flow-notification-newtopic-email-subject' => '$1 đã bắt đầu một chủ đề mới tại $2',
	'flow-notification-newtopic-email-batch-body' => '$1 đã bắt đầu một chủ đề mới với tiêu đề “$2” tại $3',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1}}Thảo luận',
	'echo-pref-tooltip-flow-discussion' => 'Thông báo cho tôi khi các hành động có liên quan đến tôi xảy ra trên bảng tin nhắn.',
	'flow-link-post' => 'bài đăng',
	'flow-link-topic' => 'chủ đề',
	'flow-link-history' => 'lịch sử',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'flow-edit-header-link' => 'רעדאקטירט קעפל',
	'flow-header-empty' => 'דער דאזיקער שמועס־בלאט האט נישט קיין קעפל.',
	'flow-post-moderated-toggle-show' => '[ווייזן]',
	'flow-post-moderated-toggle-hide' => '[באהאלטן]',
	'flow-newtopic-title-placeholder' => 'מעלדונג סוביעקט',
	'flow-newtopic-content-placeholder' => 'מעלדונג טעקסט. זייט פריינדלעך!',
	'flow-newtopic-save' => 'צושטעלן טעמע',
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
	'flow-rev-message-new-post' => 'טעמע געשאפן',
	'flow-rev-message-edit-title' => 'רעדאקטירט טעמע טיטל',
	'flow-rev-message-create-header' => 'געשאפן קעפל',
	'flow-rev-message-edit-header' => 'רעדאקטירט קעפל',
	'flow-topic-history' => 'טעמע היסטאריע',
	'flow-comment-restored' => 'צוריקגעשטעלט הערה',
	'flow-comment-deleted' => 'אויסגעמעקט הערה',
	'flow-comment-hidden' => 'באהאלטענע הערה',
	'flow-comment-moderated' => 'מאדערירטע הערה',
	'flow-paging-fwd' => 'עלטערע טעמעס',
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|דיסקוסיע|דיסקוסיעס}}',
	'flow-link-topic' => 'טעמע',
	'flow-link-history' => 'היסטאריע',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Dreamism
 * @author Hzy980512
 * @author Liuxinyu970226
 * @author Qiyue2001
 */
$messages['zh-hans'] = array(
	'flow-user-anonymous' => '匿名',
	'flow-edit-header-link' => '编辑页顶',
	'flow-post-moderated-toggle-show' => '[显示]',
	'flow-post-moderated-toggle-hide' => '[隐藏]',
	'flow-post-hidden' => '[发布隐藏]',
	'flow-post-hidden-by' => '由$1$2{{GENDER:$1|隐藏}}',
	'flow-post-deleted' => '[回复已删除]',
	'flow-post-deleted-by' => '由$1$2{{GENDER:$1|删除}}',
	'flow-post-censored-by' => '由$1$2{{GENDER:$1|审查}}',
	'flow-post-actions' => '操作',
	'flow-topic-actions' => '操作',
	'flow-cancel' => '取消',
	'flow-newtopic-title-placeholder' => '信息工程',
	'flow-newtopic-content-placeholder' => '消息正文。祝好！',
	'flow-newtopic-header' => '添加新主题',
	'flow-newtopic-save' => '添加主题',
	'flow-newtopic-start-placeholder' => '开启一个新话题',
	'flow-reply-topic-placeholder' => '在“$2”发表的{{GENDER:$1|评论}}',
	'flow-reply-placeholder' => '点此{{GENDER:$1|回复}}$1。祝好！', # Fuzzy
	'flow-reply-submit' => '{{GENDER:$1|帖子回复}}',
	'flow-reply-link' => '{{GENDER:$1|回复}}',
	'flow-thank-link' => '{{GENDER:$1|感谢}}',
	'flow-talk-link' => '讨论{{GENDER:$1|$1}}',
	'flow-post-edited' => '评论由$1 $2{{GENDER:$1|编辑}}',
	'flow-post-action-post-history' => '发布历史',
	'flow-post-action-delete-post' => '删除回复',
	'flow-post-action-hide-post' => '隐藏帖子',
	'flow-post-action-edit-post' => '编辑帖子',
	'flow-post-action-edit' => '编辑',
	'flow-post-action-restore-post' => '恢复帖子',
	'flow-topic-action-view' => '永久链接',
	'flow-topic-action-watchlist' => '监视列表',
	'flow-topic-action-edit-title' => '编辑标题',
	'flow-topic-action-history' => '主题历史',
	'flow-error-http' => '与服务器联系时出错。未保存您的帖子。',
	'flow-error-other' => '出现意外的错误。未保存您的帖子。',
	'flow-error-edit-restricted' => '您无权编辑此帖子。',
	'flow-error-external-multi' => '保存帖子时遇到错误。帖子未保存。<br />$1',
	'flow-error-missing-content' => '帖子无内容。只能保存有内容的帖子。',
	'flow-error-hide-failure' => '隐藏此项失败。',
	'flow-edit-title-submit' => '更改标题',
	'flow-rev-message-edit-post' => '编辑帖子内容',
	'flow-rev-message-create-header' => '已创建的页顶',
	'flow-rev-message-edit-header' => '已编辑的页顶',
	'flow-rev-message-deleted-post' => '已删除的回复',
	'flow-topic-history' => '主题历史',
	'flow-comment-restored' => '恢复的评论',
	'flow-comment-deleted' => '已删除的评论',
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
	'echo-category-title-flow-discussion' => '$1个讨论',
	'echo-pref-tooltip-flow-discussion' => '在讨论版发生有关我的动作时通知我。',
	'flow-link-post' => '帖子',
	'flow-link-topic' => '主题',
	'flow-link-history' => '历史',
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
	'echo-category-title-flow-discussion' => '{{PLURAL:$1|討論|討論}}',
	'echo-pref-tooltip-flow-discussion' => '通知我，當有與我相關的動作發生在討論版時',
);
