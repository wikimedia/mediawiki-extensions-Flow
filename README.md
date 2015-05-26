Documentation
=========

### Configuation

Parsoid is required with default and recommended settings

```php
// $wgFlowCluster will define what external DB server should be used.
// If set to false, the current database (wfGetDB) will be used to read/write
// data from/to. If Flow data is supposed to be stored on an external database,
// set the value of this variable to the $wgExternalServers key representing
// that external connection.
$wgFlowCluster = false;
```

Database to use for Flow metadata.

Options to use

* Set to false to use the wiki db.

```php
// Database to use for Flow metadata.  Set to false to use the wiki db.  Any number of wikis can
// and should share the same Flow database.
$wgFlowDefaultWikiDb = false;
```

<big>WARNING:</big> ONLY enable this on private wikis and ONLY IF you understand the SECURITY IMPLICATIONS <br> of sending Cookie headers to Parsoid over HTTP. For security reasons, it is strongly recommended <br> that $wgVisualEditorParsoidURL be pointed to localhost if this setting is enabled.

```php
// Forward users' Cookie: headers to Parsoid. Required for private wikis (login required to read).
// If the wiki is not private (i.e. $wgGroupPermissions['*']['read'] is true) this configuration
// variable will be ignored.
//
// This feature requires a non-locking session store. The default session store will not work and
// will cause deadlocks when trying to use this feature. If you experience deadlock issues, enable
// $wgSessionsInObjectCache.
$wgFlowParsoidForwardCookies = false;
```

Options avalible to use

* 'visualeditor' means VE. Requires the VisualEditor extension avalible at https://www.mediawiki.org/wiki/Extension:VisualEditor als requires Parsoid.
* 'none' means wikitext.

This is also related to $wgFlowEditorList.

```php
// Default editor to use in Flow
$wgDefaultUserOptions['flow-editor'] = 'wikitext';
```
