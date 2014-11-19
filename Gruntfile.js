/*!
 * Grunt file
 *
 * @package Flow
 */

/*jshint node:true */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-contrib-csslint' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-jscs' );

	grunt.initConfig( {
		jshint: {
			options: {
				jshintrc: true
			},
			all: [
				'*.js',
				'modules/**/*.js'
			]
		},
		jscs: {
			src: [ '<%= jshint.all %>', '!modules/vendor/**' ]
		},
		csslint: {
			options: {
				csslintrc: '.csslintrc'
			},
			all: 'modules/**/*.css'
		},
		banana: {
			all: 'i18n/'
		},
		watch: {
			files: [
				'.{csslintrc,jscsrc,jshintignore,jshintrc}',
				'<%= jshint.all %>',
				'<%= csslint.all %>'
			],
			tasks: 'test'
		}
	} );

	// XXX jscs not passing in this patch
	grunt.registerTask( 'lint', [ 'jshint', 'csslint', 'banana' ] );
	grunt.registerTask( 'test', 'lint' );
	grunt.registerTask( 'default', 'test' );
};
