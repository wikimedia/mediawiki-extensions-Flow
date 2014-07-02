( function ( $ ) {
QUnit.module( 'ext.flow: mediawiki.ui.enhance' );

QUnit.test( 'Forms with required fields have certain buttons disabled by default', 6, function( assert ) {
	var $forms = [
		$( '<form><input required><button data-role="action" class="flow-ui-button">go</button></form>' ),
		$( '<form><input required><button data-role="submit" class="flow-ui-button">go</button></form>' ),
		$( '<form><textarea></textarea><input><button data-role="submit" class="flow-ui-button">go</button></form>' ),
		$( '<form><textarea required></textarea><button data-role="submit" class="flow-ui-button">go</button></form>' ),
		$( '<form><textarea required>foo</textarea><button data-role="submit" class="flow-ui-button">go</button></form>' ),
		$( '<form><textarea required>foo</textarea><input required><button data-role="submit" class="flow-ui-button">go</button></form>' )
	];
	$.each( $forms, function() {
		mw.flow.enhance.enableFormWithRequiredFields( this );
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

} ( jQuery ) );
