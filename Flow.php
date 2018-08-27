<?php
/**
 * MediaWiki Extension: Flow
 *
 * Flow, a discussion system for MediaWiki
 * Copyright (C) 2013-2015 Flow contributors
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * ---
 * Older parts of Flow are also available under the terms:
 *
 * ---
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * This program is distributed WITHOUT ANY WARRANTY.
 * ---
 *
 * Third-party libraries are under their own licenses.  See vendor and modules/vendor.
 */

/**
 *
 * @file
 * @ingroup Extensions
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Flow' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Flow'] = [
		__DIR__ . '/i18n',
		__DIR__ . '/i18n/api',
	];
	$wgExtensionMessagesFiles['FlowAlias'] = __DIR__ . '/Flow.alias.php';
	$wgExtensionMessagesFiles['FlowNamespaces'] = __DIR__ . '/Flow.namespaces.php';
	wfWarn(
		'Deprecated PHP entry point used for Flow extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the Flow extension requires MediaWiki 1.32+' );
}
