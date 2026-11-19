'use strict';

const { defineConfig } = require( 'eslint/config' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );

// Directories with their own config, which other configs don't apply to.
// These were directories with `"root": true` in .eslintrc.json.
const rootDirs = {
	modules: [ 'modules/**' ],
	apiTesting: [ 'tests/api-testing/**' ],
	qunit: [ 'tests/qunit/**' ]
};

// Shared by modules and tests (was modules/.eslintrc.json)
const modulesConfig = [
	...wikimedia( 'client', 'jquery', 'mediawiki' ),
	{
		languageOptions: {
			globals: {
				Handlebars: 'readonly',
				moment: 'readonly'
			}
		},
		rules: {
			'no-underscore-dangle': 'off',
			camelcase: 'off',
			'max-len': 'off',
			'no-shadow': 'warn',
			'no-jquery/no-extend': 'warn',
			'es-x/no-object-assign': 'warn',
			'prefer-const': 'warn',
			'no-jquery/no-done-fail': 'warn',
			'mediawiki/no-unlabeled-buttonwidget': 'warn',
			'no-jquery/no-internal-array-methods': 'warn'
		}
	}
];

module.exports = defineConfig( [
	// Gruntfile.js, etc.
	{
		ignores: Object.values( rootDirs ).flat(),
		extends: [ wikimedia( 'server' ) ]
	},

	// Code which runs in MediaWiki
	{
		files: rootDirs.modules,
		extends: modulesConfig
	},
	{
		files: [ 'modules/editor/editors/visualeditor/**' ],
		languageOptions: {
			globals: {
				ve: 'readonly'
			}
		}
	},

	// API tests
	{
		files: rootDirs.apiTesting,
		extends: [ wikimedia( 'server', 'mocha' ) ]
	},

	// QUnit tests
	{
		files: rootDirs.qunit,
		extends: [ modulesConfig, wikimedia( 'qunit' ) ],
		languageOptions: {
			// wikimedia/qunit resets this to 'script'
			sourceType: 'commonjs'
		},
		rules: {
			'no-jquery/no-parse-html-literal': 'off',
			'no-jquery/no-global-selector': 'off',
			'no-shadow': 'warn',
			'no-jquery/no-extend': 'warn',
			'es-x/no-object-assign': 'warn',
			'prefer-const': 'warn'
		}
	}
] );
