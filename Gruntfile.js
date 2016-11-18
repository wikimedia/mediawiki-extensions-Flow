/*!
 * Grunt file
 *
 * @package Flow
 */

/* eslint-env node: */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );
	grunt.loadNpmTasks( 'grunt-tyops' );

	grunt.initConfig( {
		tyops: {
			options: {
				typos: 'build/typos.json'
			},
			src: [
				'**/*',
				'!{node_modules,vendor,docs}/**',
				'!build/typos.json'
			]
		},
		eslint: {
			all: [
				'**/*.js',
				'!{node_modules,vendor,docs}/**/*.js'
			]
		},
		stylelint: {
			options: {
				syntax: 'less'
			},
			all: [
				'modules/**/*.css',
				'modules/**/*.less'
			]
		},
		banana: {
			all: 'i18n/'
		},
		watch: {
			files: [
				'.{stylelintlintrc,eslintrc.json}',
				'<%= eslint.all %>',
				'<%= stylelintlint.all %>'
			],
			tasks: 'test'
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**'
			]
		}
	} );

	grunt.registerTask( 'lint', [ 'tyops', 'eslint', 'stylelint', 'jsonlint', 'banana' ] );
	grunt.registerTask( 'test', 'lint' );
	grunt.registerTask( 'default', 'test' );
};
