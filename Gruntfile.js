/*!
 * Grunt file
 *
 * @package Flow
 */

/* eslint-env node */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-eslint' );
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
			options: {
				reportUnusedDisableDirectives: true,
				extensions: [ '.js', '.json' ],
				cache: true
			},
			all: [
				'**/*.js{,on}',
				'!{modules/libs,docs,vendor,node_modules}/**'
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
		banana: conf.MessagesDirs,
		watch: {
			files: [
				'.{stylelintrc,eslintrc}.json',
				'<%= eslint.all %>',
				'<%= stylelint.all %>'
			],
			tasks: 'test'
		}
	} );

	grunt.registerTask( 'lint', [ 'tyops', 'eslint', 'stylelint', 'banana' ] );
	grunt.registerTask( 'test', 'lint' );
	grunt.registerTask( 'default', 'test' );
};
