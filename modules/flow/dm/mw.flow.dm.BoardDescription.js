/**
 * Flow board description
 *
 * @constructor
 *
 * @extends mw.flow.dm.RevisionedContent
 *
 * @param {Object} data API data to build topic header with
 * @param {Object} [config] Configuration options
 */
mw.flow.dm.BoardDescription = function mwFlowDmBoardDescription( data, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.dm.BoardDescription.parent.call( this, config );

	this.populate( data );
};

/* Initialization */

OO.inheritClass( mw.flow.dm.BoardDescription, mw.flow.dm.RevisionedContent );
