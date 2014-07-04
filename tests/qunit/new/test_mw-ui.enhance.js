( function ( $ ) {
	QUnit.module( 'ext.flow: mediawiki.ui.enhance' );

	QUnit.test( 'Forms with required fields have certain buttons disabled by default', 6, function( assert ) {
		var $forms = [
			$( '<form><input class="mw-ui-input" required><button data-role="action" class="mw-ui-button">go</button></form>' ),
			$( '<form><input class="mw-ui-input" required><button data-role="submit" class="mw-ui-button">go</button></form>' ),
			$( '<form><textarea class="mw-ui-input"></textarea><input class="mw-ui-input"><button data-role="submit" class="mw-ui-button">go</button></form>' ),
			$( '<form><textarea class="mw-ui-input" required></textarea><button data-role="submit" class="mw-ui-button">go</button></form>' ),
			$( '<form><textarea class="mw-ui-input" required>foo</textarea><button data-role="submit" class="mw-ui-button">go</button></form>' ),
			$( '<form><textarea class="mw-ui-input" required>foo</textarea><input class="mw-ui-input" required><button data-role="submit" class="mw-ui-button">go</button></form>' )
		];

		$.each( $forms, function() {
			this.appendTo( '#qunit-fixture' );
			this.find( '.mw-ui-input' ).trigger( 'keyup' );
		} );

		assert.strictEqual( $forms[0].find( 'button' ).is( ':disabled' ), true,
			'Buttons with data-role=action are disabled when required fields are empty.' );
		assert.strictEqual( $forms[1].find( 'button' ).is( ':disabled' ), true,
			'Buttons with data-role=action are disabled when required fields are empty.' );
		assert.strictEqual( $forms[2].find( 'button' ).is( ':disabled' ), false,
			'Buttons with are enabled when no required fields in form.' );
		assert.strictEqual( $forms[3].find( 'button' ).is( ':disabled' ), true,
			'Buttons are disabled when textarea is required but empty.' );
		assert.strictEqual( $forms[4].find( 'button' ).is( ':disabled' ), false,
			'Buttons are enabled when required textarea has text.' );
		assert.strictEqual( $forms[5].find( 'button' ).is( ':disabled' ), true,
			'Buttons are disabled when required textarea but required input does not.' );
	} );

	QUnit.test( 'mw-ui-tooltip', 4, function( assert ) {
		assert.ok( mw.tooltip, 'mw.tooltip exists' );

		// Create a tooltip using body
		$( 'body' ).attr( 'title', 'test' );
		assert.ok( mw.tooltip.show( $( 'body' ) ), 'mw.ui.tooltip.show returned something' );
		assert.strictEqual( $('.flow-ui-tooltip-content' ).filter(':contains("test"):visible').length, 1,
			'Tooltip with text "test" is visible' );
		mw.tooltip.hide( $( 'body' ) );
		assert.strictEqual( $('.flow-ui-tooltip-content' ).filter(':contains("test")').length, 0,
			'Tooltip with text "test" is removed' );
		$( 'body' ).attr( 'title', '' );
	} );

} ( jQuery ) );
