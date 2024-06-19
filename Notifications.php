<?php

use MediaWiki\Extension\Notifications\AttributeManager;
use MediaWiki\Extension\Notifications\UserLocator;

$notificationTemplate = [
	'category' => 'flow-discussion',
	'group' => 'other',
];

$newTopicNotification = [
	'presentation-model' => \Flow\Notifications\NewTopicPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
		'expandable' => true,
	],
] + $notificationTemplate;

$descriptionEditedNotification = [
	'presentation-model' => \Flow\Notifications\HeaderEditedPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
	],
] + $notificationTemplate;

$postEditedNotification = [
	'presentation-model' => \Flow\Notifications\PostEditedPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
	],
] + $notificationTemplate;

$postReplyNotification = [
	'presentation-model' => \Flow\Notifications\PostReplyPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
		'expandable' => true,
	],
] + $notificationTemplate;

$topicRenamedNotification = [
	'presentation-model' => \Flow\Notifications\TopicRenamedPresentationModel::class,
	'primary-link' => [
		'message' => 'flow-notification-link-text-view-post',
		'destination' => 'flow-post'
	],
] + $notificationTemplate;

$summaryEditedNotification = [
	'presentation-model' => \Flow\Notifications\SummaryEditedPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
	],
] + $notificationTemplate;

$topicResolvedNotification = [
	'presentation-model' => \Flow\Notifications\TopicResolvedPresentationModel::class,
] + $notificationTemplate;

$notifications = [
	'flow-new-topic' => [
		'section' => 'message',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateUsersWatchingTitle' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
	] + $newTopicNotification,
	'flowusertalk-new-topic' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $newTopicNotification,
	'flow-post-reply' => [
		'section' => 'message',
		AttributeManager::ATTR_LOCATORS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateUsersWatchingTopic' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
	] + $postReplyNotification,
	'flowusertalk-post-reply' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $postReplyNotification,
	'flow-post-edited' => [
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locatePostAuthors' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $postEditedNotification,
	'flowusertalk-post-edited' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $postEditedNotification,
	'flow-topic-renamed' => [
		'section' => 'message',
		AttributeManager::ATTR_LOCATORS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateUsersWatchingTopic' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
	] + $topicRenamedNotification,
	'flowusertalk-topic-renamed' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $topicRenamedNotification,
	'flow-summary-edited' => [
		'section' => 'message',
		AttributeManager::ATTR_LOCATORS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateUsersWatchingTopic' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
	] + $summaryEditedNotification,
	'flowusertalk-summary-edited' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $summaryEditedNotification,
	'flow-description-edited' => [
		'section' => 'message',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateUsersWatchingTitle' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $descriptionEditedNotification,
	'flowusertalk-description-edited' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $descriptionEditedNotification,
	'flow-mention' => [
		'category' => 'mention',
		'presentation-model' => \Flow\Notifications\MentionPresentationModel::class,
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateMentionedUsers' ] ],
		],
	] + $notificationTemplate,
	'flow-enabled-on-talkpage' => [
		'category' => 'system',
		'presentation-model' => \Flow\Notifications\FlowEnabledOnTalkpagePresentationModel::class,
		'section' => 'message',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
		'canNotifyAgent' => true,
	] + $notificationTemplate,
	'flow-topic-resolved' => [
		'section' => 'message',
		AttributeManager::ATTR_LOCATORS => [
			[ [ \Flow\Notifications\UserLocator::class, 'locateUsersWatchingTopic' ] ],
		],
		AttributeManager::ATTR_FILTERS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
	] + $topicResolvedNotification,
	'flowusertalk-topic-resolved' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateTalkPageOwner' ] ],
		],
	] + $topicResolvedNotification,
	'flow-mention-failure-too-many' => [
		AttributeManager::ATTR_LOCATORS => [
			[ [ UserLocator::class, 'locateEventAgent' ] ],
		],
		'canNotifyAgent' => true,
		'section' => 'alert',
		'presentation-model' => \Flow\Notifications\MentionStatusPresentationModel::class,
	] + $notificationTemplate,
];

return $notifications;
