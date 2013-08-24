<?php

// Internationalisation file for Flow extension.

$messages = array();

/**
 * English
 */
$messages['en'] = array(
	'flow-desc' => 'Workflow management system',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-edit-summary-link' => 'Edit summary',

	'flow-disclaimer' => "By clicking the \"Add message\" button, you agree to the Terms of Use,
and you irrevocably agree to release your contribution under the CC-BY-SA 3.0 License and the GFDL.
You agree that a hyperlink or URL is sufficient attribution under the Creative Commons license.",
	'flow-post-hidden' => '[post hidden]',
	'flow-post-hidden-by' => 'Hidden by $1 $2',
	'flow-post-deleted' => '[post deleted]',
	'flow-post-deleted-by' => 'Deleted by $1 $2',
	'flow-post-censored' => '[post censored]',
	'flow-post-censored-by' => 'Censored by $1 $2',
	'flow-post-actions' => 'actions',
	'flow-topic-actions' => 'actions',
	'flow-cancel' => 'Cancel',

	'flow-newtopic-title-placeholder' => 'Message subject',
	'flow-newtopic-content-placeholder' => 'Message text. Be nice!',
	'flow-newtopic-header' => 'Add a new topic',
	'flow-newtopic-save' => 'Add topic',
	'flow-newtopic-start-placeholder' => 'Click here to start a new discussion. Be nice!',

	'flow-reply-placeholder' => 'Click to reply to $1. Be nice!',
	'flow-reply-submit' => 'Post reply',

	'flow-edit-post-submit' => 'Submit changes',

	'flow-post-action-view' => 'Permalink',
	'flow-post-action-post-history' => 'Post history',
	'flow-post-action-censor-post' => 'Censor post',
	'flow-post-action-delete-post' => 'Delete post',
	'flow-post-action-hide-post' => 'Hide post',
	'flow-post-action-edit-post' => 'Edit post',
	'flow-post-action-edit' => 'Edit',
	'flow-post-action-restore-post' => 'Restore post',

	'flow-topic-action-edit-title' => 'Edit title',
	'flow-topic-action-history' => 'Topic history',

	'flow-error-http' => 'An error occurred while contacting the server. Your post was not saved.', // Needs real copy
	'flow-error-other' => 'An unexpected error occurred. Your post was not saved.',
	'flow-error-external' => 'An error occurred while saving your post. Your post was not saved.<br /><small>The error message received was: $1</small>',
	'flow-error-external-multi' => 'Errors were encountered while saving your post. Your post was not saved.<br />$1',

	'flow-error-missing-content' => 'Post has no content. Content is required to save a new post.',
	'flow-error-missing-title' => 'Topic has no title. Title is required to save a new topic.',
	'flow-error-parsoid-failure' => 'Unable to parse content due to a Parsoid failure.',
	'flow-error-missing-replyto' => 'No replyTo parameter was supplied. This parameter is required for the "reply" action.',
	'flow-error-invalid-replyto' => 'replyTo parameter was invalid. The specified post could not be found.',
	'flow-error-delete-failure' => 'Deletion of this item failed.',
	'flow-error-hide-failure' => 'Hiding this item failed.',
	'flow-error-missing-postId' => 'No postId parameter was supplied. This parameter is required to manipulate a post.',
	'flow-error-invalid-postId' => 'postId parameter was invalid. The specified post could not be found.',
	'flow-error-restore-failure' => 'Restoration of this item failed.',

	'flow-summaryedit-submit' => 'Save summary',

	'flow-edit-title-submit' => 'Change title',

	'flow-rev-message-reply' => 'New reply posted',
	'flow-rev-message-new-post' => 'Topic created',

	'flow-topic-history' => 'Topic history',

	'flow-comment-restored' => 'Restored comment',
	'flow-comment-deleted' => 'Deleted comment',
	'flow-comment-hidden' => 'Hid comment',
	'flow-comment-moderated' => 'Moderated comment',

	'flow-paging-rev' => 'More recent topics',
	'flow-paging-fwd' => 'Older topics',
	'flow-last-modified' => 'Last modified about $1',
	'flow-days-ago' => '$1 {{PLURAL:$1|day|days}} ago',
	'flow-months-ago' => '$1 {{PLURAL:$1|month|months}} ago',
	'flow-years-ago' => '$1 {{PLURAL:$1|year|years}} ago',

	'flow-notification-reply' => '$1 replied to your [$5 post] in $2 on [[$3|$4]].',
	'flow-notification-edit' => '$1 edited your [$5 post] in $2 on [[$3|$4]].',
	'flow-notification-newtopic' => '$1 created a [$5 new topic] on [[$2|$3]]: $4.',
	'flow-notification-rename' => '$1 changed the title of [$2 $3] to "$4" on [[$5|$6]]',

	'flow-link-post' => 'post',
	'flow-link-topic' => 'topic',
	'flow-link-history' => 'history',
);

/** Message documentation (Message documentation)
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'flow-desc' => '{{desc|name=Flow|url=http://www.mediawiki.org/wiki/Extension:Flow}}',
	'flow-specialpage' => 'Used as page title in [[Special:Flow]]. Parameters:
* $1 - page title',
	'flow-edit-summary-link' => 'Used as text for the link which points to the "Edit summary" page.',
	'flow-disclaimer' => 'Used as disclaimer text at the bottom of the form.

Preceded by the Submit button which has the label either {{msg-mw|Flow-reply-submit}} or {{msg-mw|Flow-newtopic-save}}.

"Add message" seems to refer these Submit buttons.

See also:
* {{msg-mw|Wikimedia-copyrightwarning}}',
	'flow-post-hidden' => 'Used as username/content if the post was hidden.',
	'flow-post-deleted' => 'Used as username/content if the post was deleted.',
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
	'flow-reply-placeholder' => 'Used as placeholder for the Content textarea. Parameters:
* $1 - username',
	'flow-reply-submit' => 'Used as label for the Submit button.',
	'flow-edit-post-submit' => 'Used as label for the Submit button.',
	'flow-post-action-view' => 'Used as text for the link which is used to view.
{{Identical|Permalink}}',
	'flow-post-action-post-history' => 'Used as text for the link which is used to view post-history of the topic.',
	'flow-post-action-delete-post' => 'Used as label for the Submit button.

See also:
* {{msg-mw|Flow-post-action-restore-post}}',
	'flow-post-action-hide-post' => 'Used as label for the Submit button.',
	'flow-post-action-edit-post' => 'Used as text for the link which is used to edit the post.',
	'flow-post-action-edit' => 'Unused at this time.

Translate as label for the link or the Submit button.
{{Identical|Edit}}',
	'flow-post-action-restore-post' => 'Used as label for the Submit button.

See also:
* {{msg-mw|Flow-post-action-delete-post}}',
	'flow-topic-action-edit-title' => 'Used as title for the link which is used to edit the title.',
	'flow-topic-action-history' => 'Used as text for the link which is used to view topic-history.
{{Identical|Topic history}}',
	'flow-error-http' => 'Used as error message on HTTP error.',
	'flow-error-other' => 'Used as generic error message.',
	'flow-error-external' => 'Uses as error message. Parameters:
* $1 - error message
See also:
* {{msg-mw|Flow-error-external-multi}}',
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
	'flow-error-invalid-postId' => 'Used as error message when deleting/restoring a post.',
	'flow-error-restore-failure' => 'Used as error message when restoring a post.

"this item" seems to refer "this post".',
	'flow-summaryedit-submit' => 'Used as label for the Submit button.',
	'flow-edit-title-submit' => 'Used as label for the Submit button.',
	'flow-rev-message-reply' => 'Used as comment when the new reply has been posted.',
	'flow-rev-message-new-post' => 'Used as comment when the topic has been created.',
	'flow-topic-history' => 'Used as <code><nowiki><h2></nowiki></code> heading in the "Topic history" page.
{{Identical|Topic history}}',
	'flow-comment-restored' => 'Used as comment when the comment has been restored.

See also:
* {{msg-mw|Flow-comment-deleted}}',
	'flow-comment-deleted' => 'Used as comment when the comment has been deleted.

See also:
* {{msg-mw|Flow-comment-restored}}',
	'flow-comment-hidden' => 'Used as comment when the comment has been hidden.',
	'flow-paging-rev' => 'Label for paging link that shows more recently modified topics',
	'flow-paging-fwd' => 'Label for paging link that shows less recently modified topics',

	'flow-notification-reply' => 'Notification text for when a user receives a reply. Parameters:
* $1: Username of the person who replied.
* $2: Title of the topic.
* $3: Title for the Flow board.
* $4: Title for the page that the Flow board is attached to.
* $5: Permanent URL for the post.',
	'flow-notification-edit' => 'Notification text for when a user\'s post is edited. Parameters:
* $1: Username of the person who edited the post.
* $2: Title of the topic.
* $3: Title for the Flow board.
* $4: Title for the page that the Flow board is attached to.
* $5: Permanent URL for the post.',
	'flow-notification-newtopic' => 'Notification text for when a new topic is created. Parameters:
* $1: Username of the person who created the topic.
* $2: Title for the Flow board.
* $3: Title for the page that the Flow board is attached to.
* $4: Title of the topic.
* $5: Permanent URL for the topic.',
	'flow-notification-rename' => 'Notification text for when the subject of a topic is changed. Parameters:
* $1: Username of the person who edited the title.
* $2: Permalink to the topic.
* $3: Old topic subject.
* $4: New topic subject.
* $5: Title for the Flow board.
* $6: Title for the page that the Flow board is attached to.
',
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'flow-desc' => 'Sistema de xestión del fluxu de trabayu',
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
	'flow-edit-summary-link' => 'Bearbeitungszusammenfassung',
	'flow-disclaimer' => 'Durch Klicken auf „Nachricht hinzufügen“ stimmst du den Nutzungsbedingungen
und der unwiderruflichen Veröffentlichung deines Beitrags unter der CC-BY-SA-3.0-Lizenz sowie der GFDL zu.
Du stimmst zu, dass ein Hyperlink oder eine URL unter ausreichender Namensnennung der Creative-Commons-Lizenz steht.',
	'flow-post-deleted' => '[Beitrag gelöscht]',
	'flow-post-actions' => 'Aktionen',
	'flow-topic-actions' => 'Aktionen',
	'flow-cancel' => 'Abbrechen',
	'flow-newtopic-title-placeholder' => 'Betreff der Nachricht',
	'flow-newtopic-content-placeholder' => 'Nachrichtentext. Sei freundlich!',
	'flow-newtopic-header' => 'Ein neues Thema hinzufügen',
	'flow-newtopic-save' => 'Thema hinzufügen',
	'flow-newtopic-start-placeholder' => 'Hier klicken, um eine neue Diskussion zu starten. Sei freundlich!',
	'flow-reply-placeholder' => 'Klicke, um $1 zu antworten. Sei freundlich!',
	'flow-reply-submit' => 'Antworten',
	'flow-edit-post-submit' => 'Änderungen übertragen',
	'flow-post-action-view' => 'Permanentlink',
	'flow-post-action-post-history' => 'Beitragsgeschichte',
	'flow-post-action-delete-post' => 'Beitrag löschen',
	'flow-post-action-edit-post' => 'Beitrag bearbeiten',
	'flow-post-action-edit' => 'Bearbeiten',
	'flow-post-action-restore-post' => 'Beitrag wiederherstellen',
	'flow-topic-action-edit-title' => 'Titel bearbeiten',
	'flow-topic-action-history' => 'Themengeschichte',
	'flow-error-http' => 'Beim Kontaktieren des Servers ist ein Fehler aufgetreten. Dein Beitrag wurde nicht gespeichert.',
	'flow-error-other' => 'Ein unerwarteter Fehler ist aufgetreten. Dein Beitrag wurde nicht gespeichert.',
	'flow-error-external' => 'Beim Speichern deines Beitrags ist ein Fehler aufgetreten. Dein Beitrag wurde nicht gespeichert.<br /><small>Die empfangene Fehlermeldung lautete: $1</small>',
	'flow-error-external-multi' => 'Beim Speichern deines Beitrags sind Fehler aufgetreten. Dein Beitrag wurde nicht gespeichert. <br /> $1',
	'flow-error-missing-content' => 'Der Beitrag hat keinen Inhalt. Dieser ist erforderlich, um einen neuen Beitrag zu speichern.',
	'flow-error-missing-title' => 'Das Thema hat keinen Titel. Dieser ist erforderlich, um ein neues Thema zu speichern.',
	'flow-error-parsoid-failure' => 'Aufgrund eines Parsoid-Fehlers konnte der Inhalt nicht geparst werden.',
	'flow-error-missing-replyto' => 'Es wurde kein Parameter „replyTo“ angegeben. Dieser Parameter ist für die „Antworten“-Aktion erforderlich.',
	'flow-error-invalid-replyto' => 'Der Parameter „replyTo“ war ungültig. Der angegebene Beitrag konnte nicht gefunden werden.',
	'flow-error-delete-failure' => 'Das Löschen dieses Objektes ist fehlgeschlagen.',
	'flow-error-missing-postId' => 'Es wurde kein Parameter „postId“ angegeben. Dieser Parameter ist zum Ändern eines Beitrags erforderlich.',
	'flow-error-invalid-postId' => 'Der Parameter „postId“ war ungültig. Der angegebene Beitrag konnte nicht gefunden werden.',
	'flow-error-restore-failure' => 'Das Wiederherstellen dieses Objektes ist fehlgeschlagen.',
	'flow-summaryedit-submit' => 'Zusammenfassung speichern',
	'flow-edit-title-submit' => 'Titel ändern',
	'flow-rev-message-reply' => 'Neue Antwort hinterlassen',
	'flow-rev-message-new-post' => 'Thema erstellt',
	'flow-topic-history' => 'Themengeschichte',
	'flow-comment-restored' => 'Kommentar wiederhergestellt',
	'flow-comment-deleted' => 'Kommentar gelöscht',
);

/** French (français)
 * @author Gomoko
 * @author Sherbrooke
 */
$messages['fr'] = array(
	'flow-desc' => 'Système de gestion du flux de travail',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-edit-summary-link' => 'Résumé',
	'flow-disclaimer' => "En cliquant sur le bouton « Ajouter un message », vous acceptez nos [https://wikimediafoundation.org/wiki/Terms_of_Use/fr conditions d'utilisation] et acceptez de placer irrévocablement votre contribution sous [http://creativecommons.org/licenses/by-sa/3.0/deed.fr licence Creative Commons paternité-partage des conditions initiales à l'identique 3.0] et [http://www.gnu.org/copyleft/fdl.html GFDL]. Vous acceptez d’être crédité par les ré-utilisateurs au minimum via un hyperlien ou une URL sous la licence Creative Commons.",
	'flow-post-deleted' => '[message supprimé]',
	'flow-post-actions' => 'actions',
	'flow-topic-actions' => 'actions',
	'flow-cancel' => 'Annuler',
	'flow-newtopic-title-placeholder' => 'Objet du message',
	'flow-newtopic-content-placeholder' => 'Texte du message. Soyez gentil !',
	'flow-newtopic-header' => 'Ajouter un nouveau sujet',
	'flow-newtopic-save' => 'Ajouter sujet',
	'flow-newtopic-start-placeholder' => 'Cliquez ici pour commencer une nouvelle discussion. Soyez gentil !',
	'flow-reply-placeholder' => 'Cliquez ici pour répondre à $1. Soyez gentil !',
	'flow-reply-submit' => 'Poster une réponse',
	'flow-edit-post-submit' => 'Soumettre les modifications',
	'flow-post-action-view' => 'Lien permanent',
	'flow-post-action-post-history' => 'Historique des publications',
	'flow-post-action-delete-post' => 'Supprimer le message',
	'flow-post-action-edit-post' => 'Modifier la publication',
	'flow-post-action-edit' => 'Modifier',
	'flow-post-action-restore-post' => 'Restaurer le message',
	'flow-topic-action-edit-title' => 'Modifier le titre',
	'flow-topic-action-history' => 'Historique des sujets',
	'flow-error-http' => "Une erreur s'est produite en communiquant avec le serveur. Votre message n'a pas été enregistré.",
	'flow-error-other' => "Une erreur inattendue s'est produite. Votre message n'a pas été enregistré.",
	'flow-error-external' => "Une erreur s'est produite lors de l'enregistrement de votre message. Il n'a pas été enregistré.<br /><small>Le message d'erreur reçu était :$1</small>",
	'flow-error-external-multi' => "Des erreurs se sont produites lors de l'enregistrement de votre message. Votre message n'a pas été enregistré.<br /> $1",
	'flow-error-missing-content' => "Le message n'a aucun contenu. C'est requis pour enregistrer un nouveau message.",
	'flow-error-missing-title' => "Le sujet n'a aucun titre. C'est requis pour enregistrer un nouveau sujet.",
	'flow-error-parsoid-failure' => "Impossible d'analyser le contenu en raison d'une panne de Parsoid.",
	'flow-error-missing-replyto' => "Aucun paramètre replyTo n'a été fourni. Ce paramètre est requis pour l'action « répondre ».",
	'flow-error-invalid-replyto' => "paramètre replyTo n'était pas valide. Le message spécifié est introuvable.",
	'flow-error-delete-failure' => 'Échec de la suppression de cette entrée.',
	'flow-error-missing-postId' => 'Aucun paramètre postId a été fourni. Ce paramètre est requis pour manipuler un message.',
	'flow-error-invalid-postId' => "Le paramètre postId n'était pas valide. Le message spécifié est introuvable.",
	'flow-error-restore-failure' => 'Échec de la restauration de cette entrée.',
	'flow-summaryedit-submit' => 'Enregistrer le résumé',
	'flow-edit-title-submit' => 'Changer le titre',
	'flow-rev-message-reply' => 'Nouvelle réponse publiée',
	'flow-rev-message-new-post' => 'Sujet créé',
	'flow-topic-history' => 'Historique des sujets',
	'flow-comment-restored' => 'Commentaire rétabli',
	'flow-comment-deleted' => 'Commentaire supprimé',
);

/** Hebrew (עברית)
 * @author Amire80
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
	'flow-reply-placeholder' => 'יש ללחוץ כדי לענות ל{{GRAMMAR:תחילית|$1}}. נא לכתוב דברים נחמדים!',
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
	'flow-error-missing-replyto' => 'לא נשלח פרמטר replyTo. הפרמטר הזה דרוש לפעולת "reply".',
	'flow-error-invalid-replyto' => 'פרמטר replyTo שנשלח היה בלתי־תקין. לא נמצאה הרשומה שצוינה.',
	'flow-error-delete-failure' => 'מחירת הפריט הזה נכשלה.',
	'flow-error-missing-postId' => 'לא ניתן פרמטר postId. הפרמטר הזה דרוש כדי לשנות רשומה.',
	'flow-error-invalid-postId' => 'פרמטר postId שנשלח היה בלתי־תקין. הרשומה לא נמצאה.',
	'flow-error-restore-failure' => 'שחזור הפריט נכשל.',
	'flow-summaryedit-submit' => 'שמירת סיכום',
	'flow-edit-title-submit' => 'שינוי כותרת',
	'flow-rev-message-reply' => 'נשלחה תשובה חדשה',
	'flow-rev-message-new-post' => 'נוצר נושא',
	'flow-topic-history' => 'היסטוריית הנושא',
	'flow-comment-restored' => 'הערה משוחזרת',
	'flow-comment-deleted' => 'הערה מחוקה',
);

/** Italian (italiano)
 * @author Beta16
 */
$messages['it'] = array(
	'flow-desc' => 'Sistema di gestione del flusso di lavoro',
	'flow-edit-summary-link' => 'Modifica oggetto',
	'flow-disclaimer' => 'Facendo click sul pulsante "Aggiungi messaggio", accetti le condizioni d\'uso, e accetti irrevocabilmente di rilasciare il tuo contributo sotto le licenze Creative Commons Attribuzione-Condividi allo stesso modo 3.0 e GFDL.
Accetti inoltre che un collegamento ipertestuale o URL sia sufficiente per l\'attribuzione in base alla licenza Creative Commons.',
	'flow-post-deleted' => '[messaggio cancellato]',
	'flow-post-actions' => 'azioni',
	'flow-topic-actions' => 'azioni',
	'flow-cancel' => 'Annulla',
	'flow-newtopic-title-placeholder' => 'Oggetto del messaggio',
	'flow-newtopic-content-placeholder' => 'Testo del messaggio. Sii gentile!',
	'flow-newtopic-header' => 'Aggiungi una nuova discussione',
	'flow-newtopic-save' => 'Aggiungi discussione',
	'flow-newtopic-start-placeholder' => 'Clicca qui per iniziare una nuova discussione. Sii gentile!',
	'flow-reply-placeholder' => 'Clicca per rispondere a $1. Sii gentile!',
	'flow-reply-submit' => 'Invia risposta',
	'flow-edit-post-submit' => 'Invia modifiche',
	'flow-post-action-view' => 'Link permanente',
	'flow-post-action-post-history' => 'Cronologia del messaggio',
	'flow-post-action-delete-post' => 'Cancella messaggio',
	'flow-post-action-edit-post' => 'Modifica messaggio',
	'flow-post-action-edit' => 'Modifica',
	'flow-post-action-restore-post' => 'Ripristina messaggio',
	'flow-topic-action-edit-title' => 'Modifica titolo',
	'flow-topic-action-history' => 'Cronologia della discussione',
	'flow-summaryedit-submit' => 'Salva oggetto',
	'flow-edit-title-submit' => 'Cambia titolo',
	'flow-rev-message-reply' => 'Nuova risposta inviata',
	'flow-rev-message-new-post' => 'Discussione creata',
	'flow-topic-history' => 'Cronologia della discussione',
	'flow-comment-restored' => 'Commento ripristinato',
	'flow-comment-deleted' => 'Commento cancellato',
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'flow-desc' => 'ワークフロー管理システム',
	'flow-specialpage' => '$1 &ndash; Flow',
	'flow-edit-summary-link' => '要約を編集',
	'flow-disclaimer' => '「メッセージを追加」ボタンをクリックすると、利用規約に同意するとともに、
自分の投稿内容を CC-BY-SA 3.0 ライセンスおよび GFDL のもとで公開することに同意したことになります。この同意は取り消せません。
また、あなたはハイパーリンクまたは URL がクリエイティブ・コモンズライセンスにおける帰属表示として十分であると認めたことになります。',
	'flow-post-deleted' => '[削除された投稿]',
	'flow-post-actions' => '操作',
	'flow-topic-actions' => '操作',
	'flow-cancel' => 'キャンセル',
	'flow-newtopic-title-placeholder' => 'メッセージの件名',
	'flow-newtopic-content-placeholder' => 'メッセージの本文',
	'flow-newtopic-header' => '新しい話題の追加',
	'flow-newtopic-save' => '話題を追加',
	'flow-newtopic-start-placeholder' => '新しい議論を開始するにはここをクリックしてください。',
	'flow-reply-placeholder' => '$1 に返信するにはクリックしてください。',
	'flow-reply-submit' => '返信を投稿',
	'flow-edit-post-submit' => '変更を保存',
	'flow-post-action-view' => '固定リンク',
	'flow-post-action-post-history' => '投稿履歴',
	'flow-post-action-delete-post' => '投稿を削除',
	'flow-post-action-edit-post' => '投稿を編集',
	'flow-post-action-edit' => '編集',
	'flow-post-action-restore-post' => '投稿を復元',
	'flow-topic-action-edit-title' => 'タイトルを編集',
	'flow-topic-action-history' => '話題の履歴',
	'flow-error-http' => 'サーバーと通信する際にエラーが発生しました。投稿内容は保存されませんでした。',
	'flow-error-other' => '予期しないエラーが発生しました。投稿内容は保存されませんでした。',
	'flow-error-external' => '投稿内容を保存する際にエラーが発生しました。投稿内容は保存されませんでした。<br /><small>エラー メッセージ: $1</small>',
	'flow-error-external-multi' => '投稿内容を保存する際にエラーが発生しました。投稿内容は保存されませんでした。<br /> $1',
	'flow-error-missing-content' => '投稿の本文がありません。新しい投稿を保存するには本文が必要です。',
	'flow-error-missing-title' => '話題のタイトルがありません。新しい話題を保存するにはタイトルが必要です。',
	'flow-error-parsoid-failure' => 'Parsoid でエラーが発生したため、本文を構文解析できませんでした。',
	'flow-error-missing-replyto' => '返信先を指定していません。「返信」するには、このパラメーターが必要です。',
	'flow-error-invalid-replyto' => '返信先のパラメーターが無効です。指定した投稿が見つかりませんでした。',
	'flow-error-delete-failure' => 'この項目を削除できませんでした。',
	'flow-error-missing-postId' => '投稿 ID のパラメーターを指定していません。投稿を操作するには、このパラメーターが必要です。',
	'flow-error-invalid-postId' => '投稿 ID が無効です。指定した投稿が見つかりませんでした。',
	'flow-error-restore-failure' => 'この項目を復元できませんでした。',
	'flow-summaryedit-submit' => '要約を保存',
	'flow-edit-title-submit' => 'タイトルを変更',
	'flow-rev-message-reply' => '新しい返信を投稿',
	'flow-rev-message-new-post' => '話題を作成',
	'flow-topic-history' => '話題の履歴',
	'flow-comment-restored' => 'コメントを復元',
	'flow-comment-deleted' => 'コメントを削除',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'flow-desc' => 'Workflow-Management-System',
	'flow-edit-summary-link' => 'Resumé vun der Ännerung',
	'flow-post-actions' => 'Aktiounen',
	'flow-topic-actions' => 'Aktiounen',
	'flow-cancel' => 'Ofbriechen',
	'flow-newtopic-title-placeholder' => 'Sujet vum Message',
	'flow-newtopic-content-placeholder' => 'Text vum Message. Sidd frëndlech!',
	'flow-newtopic-header' => 'En neit Thema derbäisetzen',
	'flow-newtopic-save' => 'Thema derbäisetzen',
	'flow-newtopic-start-placeholder' => 'Klickt hei fir eng nei Diskussioun unzefänken. Sidd frëndlech!',
	'flow-reply-placeholder' => "Klickt op $1 fir z'äntwerten. Sidd frëndlech!",
	'flow-edit-post-submit' => 'Ännerunge späicheren',
	'flow-post-action-edit' => 'Änneren',
	'flow-topic-action-edit-title' => 'Titel änneren',
	'flow-error-missing-title' => "D'Thema huet keen Titel. Den Titel ass obligatoresch fir een neit Thema ze späicheren.",
	'flow-error-delete-failure' => "D'Läsche vun dësem Element huet net fonctionnéiert.",
	'flow-error-restore-failure' => "D'Restauréiere vun dësem Element huet net fonctionnéiert.",
	'flow-summaryedit-submit' => 'Resumé späicheren',
	'flow-edit-title-submit' => 'Titel änneren',
	'flow-rev-message-new-post' => 'Thema ugeluecht',
	'flow-comment-restored' => 'Restauréiert Bemierkung',
	'flow-comment-deleted' => 'Geläscht Bemierkung',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'flow-desc' => 'Систем за раководење со работниот тек',
	'flow-specialpage' => '$1 &ndash; Тек',
	'flow-edit-summary-link' => 'Опис на уредувањето',
	'flow-disclaimer' => 'Стискајќи на копчето „Додај порака“, се согласувате со Условите на употреба,
и неотповикливо се согласувате дека ги објавувате вашите придонеси под лиценцата CC-BY-SA 3.0 и ГЛСД.
Се согласувате дека хиперврска или URL претставува достатно оддавање на заслуга согласно лиценцата на Криејтив комонс.',
	'flow-post-deleted' => '[пораката е избришана]',
	'flow-post-actions' => 'дејства',
	'flow-topic-actions' => 'дејства',
	'flow-cancel' => 'Откажи',
	'flow-newtopic-title-placeholder' => 'Наслов на пораката',
	'flow-newtopic-content-placeholder' => 'Текст на пораката. Бидете фини!',
	'flow-newtopic-header' => 'Додај нова тема',
	'flow-newtopic-save' => 'Додај тема',
	'flow-newtopic-start-placeholder' => 'Стиснете тука за да почнете нова дискусија. Бидете фини!',
	'flow-reply-placeholder' => 'Стиснете за да одговорите на $1. Бидете фини!',
	'flow-reply-submit' => 'Објави одговор',
	'flow-edit-post-submit' => 'Спроведи измени',
	'flow-post-action-view' => 'Постојана врска',
	'flow-post-action-post-history' => 'Историја на пораки',
	'flow-post-action-delete-post' => 'Избриши ја пораката',
	'flow-post-action-edit-post' => 'Уреди ја пораката',
	'flow-post-action-edit' => 'Измени',
	'flow-post-action-restore-post' => 'Поврати ја пораката',
	'flow-topic-action-edit-title' => 'Уреди наслов',
	'flow-topic-action-history' => 'Историја на темата',
	'flow-error-http' => 'Се јави грешка при поврзувањето со опслужувачот. Пораката не е зачувана.',
	'flow-error-other' => 'Се појави неочекувана грешка. Пораката не е зачувана',
	'flow-error-external' => 'Се појави грешка при зачувувањето на пораката, и затоа не е зачувана.<br /><small>Добиена е грешката: $1</small>',
	'flow-error-external-multi' => 'Наидов на грешки при зачувувањето на пораката, и затоа не е зачувана.<br />$1',
	'flow-error-missing-content' => 'Пораката нема содржина. За да се зачува, мора да има содржина.',
	'flow-error-missing-title' => 'Темата нема наслов. Се бара наслов за да може да се зачува темата.',
	'flow-error-parsoid-failure' => 'Не можам да ја парсирам содржината поради проблем со Parsoid.',
	'flow-error-missing-replyto' => 'Нема зададено параметар „replyTo“. Овој параметар е потребен за да може да се даде одговор.',
	'flow-error-invalid-replyto' => 'Параметарот на „replyTo“ е неважечки. Не можев да ја најдам укажаната порака.',
	'flow-error-delete-failure' => 'Бришењето на ставката не успеа.',
	'flow-error-missing-postId' => 'Нема зададено параметар „postId“. Овој параметар е потребен за работа со пораката.',
	'flow-error-invalid-postId' => 'Параметарот на „postId“ е неважечки. Не можев да ја најдам укажаната порака.',
	'flow-error-restore-failure' => 'Повраќањето на ставката не успеа.',
	'flow-summaryedit-submit' => 'Зачувај опис',
	'flow-edit-title-submit' => 'Измени наслов',
	'flow-rev-message-reply' => 'Објавен нов одговор',
	'flow-rev-message-new-post' => 'Создадена тема',
	'flow-topic-history' => 'Историја на темата',
	'flow-comment-restored' => 'Повратен коментар',
	'flow-comment-deleted' => 'Избришан коментар',
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

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'flow-post-deleted' => '[messatge suprimit]',
	'flow-post-actions' => 'accions',
	'flow-topic-actions' => 'accions',
	'flow-cancel' => 'Anullar',
	'flow-edit-post-submit' => 'Sometre las modificacions',
	'flow-post-action-view' => 'Ligam permanent',
	'flow-post-action-edit-post' => 'Modificar la publicacion',
	'flow-post-action-edit' => 'Modificar',
	'flow-topic-action-edit-title' => 'Modificar lo títol',
	'flow-rev-message-new-post' => 'Subjècte creat',
);

/** Polish (polski)
 * @author Chrumps
 */
$messages['pl'] = array(
	'flow-cancel' => 'Anuluj',
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
	'flow-reply-placeholder' => 'Cazze pe responnere a $1. Sì belle!',
	'flow-reply-submit' => "Manne 'na resposte",
	'flow-post-action-delete-post' => "Scangìlle 'u messàgge",
	'flow-post-action-restore-post' => "Repristine 'u messàgge",
	'flow-topic-action-edit-title' => "Cange 'u titole",
	'flow-error-http' => "Ha assute 'n'errore condattanne 'u server. 'U messàgge tune non g'ha state reggistrate.",
	'flow-error-other' => "Ha assute 'n'errore. 'U messàgge tune non g'ha state reggistrate.",
	'flow-summaryedit-submit' => "Reggistre 'u riepiloghe",
	'flow-edit-title-submit' => "Cange 'u titole",
);

/** Swedish (svenska)
 * @author Ainali
 */
$messages['sv'] = array(
	'flow-cancel' => 'Avbryt',
	'flow-newtopic-title-placeholder' => 'Meddelandeämne',
	'flow-newtopic-content-placeholder' => 'Meddelandetext. Var trevlig!',
	'flow-newtopic-header' => 'Lägg till ett nytt ämne',
	'flow-newtopic-save' => 'Lägg till ämne',
	'flow-newtopic-start-placeholder' => 'Klicka här för att starta en ny diskussion. Var trevlig!',
	'flow-reply-placeholder' => 'Klicka för att svara på $1. Var trevlig!',
	'flow-reply-submit' => 'Skicka svar',
	'flow-edit-post-submit' => 'Skicka ändringar',
	'flow-post-action-view' => 'Permanent länk',
	'flow-post-action-delete-post' => 'Ta bort inlägg',
	'flow-post-action-edit-post' => 'Redigera inlägg',
	'flow-post-action-edit' => 'Redigera',
	'flow-post-action-restore-post' => 'Återställ inlägg',
	'flow-topic-action-edit-title' => 'Redigera titel',
	'flow-topic-action-history' => 'Ämneshistorik',
	'flow-error-http' => 'Ett fel uppstod när servern kontaktades. Ditt inlägg har inte sparats.',
	'flow-summaryedit-submit' => 'Spara sammanfattning',
	'flow-edit-title-submit' => 'Ändra titel',
	'flow-rev-message-reply' => 'Nytt svar postat',
	'flow-rev-message-new-post' => 'Ämnet skapat',
	'flow-topic-history' => 'Ämneshistorik',
	'flow-comment-restored' => 'Återställd kommentar',
	'flow-comment-deleted' => 'Raderad kommentar',
);

/** Ukrainian (українська)
 * @author Andriykopanytsia
 */
$messages['uk'] = array(
	'flow-desc' => 'Система управління робочими процесами',
	'flow-specialpage' => '$1 &ndash; Потік',
	'flow-edit-summary-link' => 'Редагувати підсумок',
	'flow-disclaimer' => 'Натиснувши кнопку "Додати Повідомлення", ви погоджуєтеся з Умовами Використання
і ви безповоротно погоджуєтесь здійснювати свій внесок відповідно до ліцензій CC-BY-SA 3.0 та GFDL.
Ви згідні, що гіперпосилання або URL-адреса є достатнім внеском під ліцензією Creative Commons license.',
	'flow-post-hidden' => '[прихована публікація]',
	'flow-post-hidden-by' => 'Приховано $1 $2',
	'flow-post-deleted' => '[пост видалено]',
	'flow-post-deleted-by' => 'Вилучено $1 $2',
	'flow-post-censored' => '[цензурна публікація]',
	'flow-post-actions' => 'дії',
	'flow-topic-actions' => 'дії',
	'flow-cancel' => 'Скасувати',
	'flow-newtopic-title-placeholder' => 'Тема повідомлення',
	'flow-newtopic-content-placeholder' => 'Текст повідомлення. Будьте приємним!',
	'flow-newtopic-header' => 'Додати нову тему',
	'flow-newtopic-save' => 'Додати тему',
	'flow-newtopic-start-placeholder' => 'Натисніть тут, щоб почати нове обговорення. Будьте приємним!',
	'flow-reply-placeholder' => 'Натисніть, щоб відповісти на  $1. Будьте приємним!',
	'flow-reply-submit' => 'Опублікувати відповідь',
	'flow-edit-post-submit' => 'Подати зміни',
	'flow-post-action-view' => 'Постійне посилання',
	'flow-post-action-post-history' => 'Опублікувати історію',
	'flow-post-action-delete-post' => 'Видалити публікацію',
	'flow-post-action-hide-post' => 'Приховати публікацію',
	'flow-post-action-edit-post' => 'Редагувати публікацію',
	'flow-post-action-edit' => 'Редагувати',
	'flow-post-action-restore-post' => 'Відновити публікацію',
	'flow-topic-action-edit-title' => 'Змінити заголовок',
	'flow-topic-action-history' => 'Історія теми',
	'flow-error-http' => 'Сталася помилка при зверненні до сервера. Ваша публікація не збережена.',
	'flow-error-other' => 'Неочікувана помилка. Ваш публікація не врятована.',
	'flow-error-external' => 'Сталася помилка під час збереження Вашого вкладу. Ваше повідомлення не було збережено.<br /><small>Отримане повідомлення було:$1</small>',
	'flow-error-external-multi' => 'Сталася помилка під час збереження Вашого внеску. Ваше повідомлення не було збережено.<br /> $1',
	'flow-error-missing-content' => 'Публікація не має ніякого вмісту. Необхідний вміст, щоб зберегти нову публікацію.',
	'flow-error-missing-title' => 'Тема не має назви. Потрібна назва, щоб зберегти нову тему.',
	'flow-error-parsoid-failure' => 'Не вдалося проаналізувати вміст через помилку Parsoid.',
	'flow-error-missing-replyto' => 'Параметр reply-to не був наданий. Цей параметр є обов\'язковим для дії "відповідь".',
	'flow-error-invalid-replyto' => 'Параметр replyTo неприпустимий. Не вдалося знайти вказану публікацію.',
	'flow-error-delete-failure' => 'Не вдалося видалити цей елемент.',
	'flow-error-hide-failure' => 'Приховання цього елементу не вдалося.',
	'flow-error-missing-postId' => 'Параметр postId не був наданий. Цей параметр вимагає, щоб маніпулювати публікацією.',
	'flow-error-invalid-postId' => 'Параметр postId неприпустимий. Не вдалося знайти вказану публікацію.',
	'flow-error-restore-failure' => 'Не вдалося виконати відновлення цього елемента.',
	'flow-summaryedit-submit' => 'Зберегти підсумок',
	'flow-edit-title-submit' => 'Змінити заголовок',
	'flow-rev-message-reply' => 'Нова відповідь опублікована',
	'flow-rev-message-new-post' => 'Тема створена',
	'flow-topic-history' => 'Історія теми',
	'flow-comment-restored' => 'Відновлений коментар',
	'flow-comment-deleted' => 'Видалений коментар',
	'flow-comment-hidden' => 'Прихований коментар',
	'flow-paging-rev' => 'Новіші теми',
	'flow-paging-fwd' => 'Старіші теми',
	'flow-last-modified' => 'Остання зміна про $1',
	'flow-days-ago' => '$1 {{PLURAL:$1|день|дні|днів}} тому',
	'flow-months-ago' => '$1 {{PLURAL:$1|місяць|місяці|місяців}} тому',
	'flow-years-ago' => '$1 {{PLURAL:$1|рік|роки|років}} тому',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'flow-edit-summary-link' => 'רעדאקטירונג רעזומע',
	'flow-disclaimer' => 'ביים קליקן אויפן "צושטעלן מעלדונג" קנעפל, טוט איר מסכים זיין צו די ניצבאדינגונגען, אומאפשפריילעך צו פארעפנטלעכן אייער ביישטייער אונטערן CC-BY-SA 3.0 ליצענץ און דעם GFDL.
איר זענט מסכים אז א היפערלינק אדער URL איז גענוג צושרייבן אונטערן קריעטיוו־קאמאנס ליצענס.',
	'flow-newtopic-title-placeholder' => 'מעלדונג סוביעקט',
	'flow-newtopic-content-placeholder' => 'מעלדונג טעקסט. זייט פריינדלעך!',
	'flow-newtopic-save' => 'צושטעלן טעמע',
	'flow-reply-submit' => 'שיקן ענטפער',
	'flow-post-action-edit' => 'רעדאַקטירן',
);
