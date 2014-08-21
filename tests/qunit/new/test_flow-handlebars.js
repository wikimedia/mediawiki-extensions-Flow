( function ( $ ) {
QUnit.module( 'ext.flow: Handlebars helpers', {
	setup: function() {
		var stub = this.sandbox.stub( mw.mantle.template, 'get' ),
			stubUser;

		stub.withArgs( 'foo.handlebars' ).returns ( {
			render: function( data ) {
				return data && data.val ? '<div>Magic.</div>' : 'Stubbed.';
			}
		} );
		this.handlebarsProto = mw.flow.FlowHandlebars.prototype;
		this.handlebarsProto._qunit_helper_test = function( a, b ) {
			return a + b;
		};

		// Stub user
		stubUser = this.sandbox.stub( mw.user, 'isAnon' );
		stubUser.onCall( 0 ).returns( true );
		stubUser.onCall( 1 ).returns( false );
		this.opts = {
			fn: function() {
				return 'ok';
			},
			inverse: function() {
				return 'nope';
			}
		};
	}
} );

QUnit.test( 'Handlebars.prototype.processTemplate', 1, function( assert ) {
	assert.strictEqual( this.handlebarsProto.processTemplate( 'foo', { val: 'Hello' } ),
		'<div>Magic.</div>', 'Getting a template works.' );
} );

QUnit.test( 'Handlebars.prototype.processTemplateGetFragment', 1, function( assert ) {
	assert.strictEqual( this.handlebarsProto.processTemplateGetFragment( 'foo', { val: 'Hello' } ).childNodes.length,
		1, 'Return a fragment with the div child node' );
} );

QUnit.test( 'Handlebars.prototype.getTemplate', 2, function( assert ) {
	assert.strictEqual( this.handlebarsProto.getTemplate( 'foo' )(), 'Stubbed.', 'Getting a template works.' );
	assert.strictEqual( this.handlebarsProto.getTemplate( 'foo' )(), 'Stubbed.', 'Getting a template from cache works.' );
} );

// Helpers
QUnit.test( 'Handlebars.prototype.callHelper', 1, function( assert ) {
	assert.strictEqual( this.handlebarsProto.callHelper( '_qunit_helper_test', 1, 2 ),
		3, 'Check the helper was called.' );
} );

QUnit.test( 'Handlebars.prototype.eachPost', 3, function( assert ) {
	var ctx = {
		posts: {
			1: [ 300 ],
			// Purposely points to a missing revision to deal with edge case
			2: [ 500 ]
		},
		revisions: {
			300: { content: 'a' }
		}
	};

	assert.deepEqual( this.handlebarsProto.eachPost( ctx, 1, {} ), { content: 'a' }, 'Matches given id.' );
	assert.deepEqual( this.handlebarsProto.eachPost( ctx, 1, this.opts ), 'ok', 'Runs fn when given.' );
	assert.deepEqual( this.handlebarsProto.eachPost( ctx, 2, {} ), { content: null }, 'Missing revision id.' );
} );

QUnit.test( 'Handlebars.prototype.ifEquals', 2, function( assert ) {
	assert.strictEqual( this.handlebarsProto.ifEquals( 'foo', 'bar', this.opts ), 'nope', 'not equal' );
	assert.strictEqual( this.handlebarsProto.ifEquals( 'foo', 'foo', this.opts ), 'ok', 'equal' );
} );

QUnit.test( 'Handlebars.prototype.ifCond', 4, function() {
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( true, 'or', false, this.opts ), 'ok', 'true || false' );
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( true, 'or', true, this.opts ), 'ok', 'true || true' );
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( false, 'or', false, this.opts ), 'nope', 'false || false' );
	strictEqual( mw.flow.FlowHandlebars.prototype.ifCond( false, 'monkeypunch', this.opts ), '', 'Unknown operator' );
} );

QUnit.test( 'Handlebars.prototype.ifAnonymous', 2, function() {
	strictEqual( this.handlebarsProto.ifAnonymous( this.opts ), 'ok', 'User should be anonymous first time.' );
	strictEqual( this.handlebarsProto.ifAnonymous( this.opts ), 'nope', 'User should be logged in on second call.' );
} );

QUnit.test( 'Handlebars.prototype.concat', 2, function() {
	strictEqual( this.handlebarsProto.concat( 'a', 'b', 'c', this.opts ), 'abc', 'Check concat working fine.' );
	strictEqual( this.handlebarsProto.concat( this.opts ), '', 'Without arguments.' );
} );

QUnit.test( 'Handlebars.prototype.progressiveEnhancement', 5, function() {
	var opts = $.extend( { hash: { type: 'insert', target: 'abc', id: 'def' } }, this.opts ),
		$div = $( document.createElement( 'div' ) );

	// Render script tag
	strictEqual(
		this.handlebarsProto.progressiveEnhancement( opts ).string,
		'<scr' + 'ipt' +
			' type="text/x-handlebars-template-progressive-enhancement"' +
			' data-type="' + opts.hash.type + '"' +
			' data-target="' + opts.hash.target +'"' +
			' id="' + opts.hash.id + '">' +
			'ok' +
			'</scr' + 'ipt>',
		'Should output exact replica of script tag.'
	);

	// Replace itself: no target (default to self), no type (default to insert)
	$div.empty().append( this.handlebarsProto.processTemplateGetFragment(
		Handlebars.compile( "{{#progressiveEnhancement}}hello{{/progressiveEnhancement}}" )
	) );
	strictEqual(
		$div.html(),
		'hello',
		'progressiveEnhancement should be processed in template string.'
	);

	// Replace a target entirely: target + type=replace
	$div.empty().append( this.handlebarsProto.processTemplateGetFragment(
		Handlebars.compile( '{{#progressiveEnhancement target="~ .pgetest" type="replace"}}hello{{/progressiveEnhancement}}<div class="pgetest">foo</div>' )
	) );
	strictEqual(
		$div.html(),
		'hello',
		'progressiveEnhancement should replace target node.'
	);

	// Insert before a target: target + type=insert
	$div.empty().append(
		this.handlebarsProto.processTemplateGetFragment(
			Handlebars.compile( '{{#progressiveEnhancement target="~ .pgetest" type="insert"}}hello{{/progressiveEnhancement}}<div class="pgetest">foo</div>' )
		)
	);
	strictEqual(
		$div.html(),
		'hello<div class="pgetest">foo</div>',
		'progressiveEnhancement should insert before target.'
	);

	// Replace target's content: target + type=content
	$div.empty().append(
		this.handlebarsProto.processTemplateGetFragment(
			Handlebars.compile( '{{#progressiveEnhancement target="~ .pgetest" type="content"}}hello{{/progressiveEnhancement}}<div class="pgetest">foo</div>' )
		)
	);
	strictEqual(
		$div.html(),
		'<div class="pgetest">hello</div>',
		'progressiveEnhancement should replace target content.'
	);
} );

QUnit.test( 'FlowHandlebars.prototype.l10n', 11, function( assert ) {
	assert.strictEqual( this.handlebarsProto.l10n( 'time_ago', 2 ), '2 seconds ago', 'Check seconds.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'time_ago', 120 ), '2 minutes ago', 'Check minutes.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'time_ago',  60 * 60 * 2 ), '2 hours ago', 'Check hour.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'time_ago', 60 * 60 * 24 * 2 ), '2 days ago', 'Check day.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'time_ago', 60 * 60 * 24 * 7 * 2 ), '2 weeks ago', 'Check week.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'active_ago', 60 * 60 * 24 * 7 * 2 ), 'Active 2 weeks ago', 'Check week.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'started_ago', 60 * 60 * 24 * 7 * 2 ), 'Started 2 weeks ago', 'Check week.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'edited_ago', 60 * 60 * 24 * 7 * 2 ), 'Edited 2 weeks ago', 'Check week.' );

	assert.strictEqual( this.handlebarsProto.l10n( 'active_ago', 1 ), 'Active 1 second ago', 'Check non-plural.' );
	assert.strictEqual( this.handlebarsProto.l10n( 'started_ago', 60 * 60 * 24 * 7 * 1 ), 'Started 1 week ago', 'Check non-plural' );
	assert.strictEqual( this.handlebarsProto.l10n( 'edited_ago', 60 * 60 * 24 * 1 ), 'Edited 1 day ago', 'Check non-plural' );
} );

} ( jQuery ) );
