/*!
 * Grunt file
 *
 * @package Flow
 */

/*jshint node:true */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-contrib-csslint' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-jscs' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
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
		jshint: {
			options: {
				jshintrc: true
			},
			all: [
				'*.js',
				'modules/**/*.js',
				'tests/qunit/**/*.js'
			]
		},
		jscs: {
			fix: {
				options: {
					config: true,
					fix: true
				},
				src: '<%= jshint.all %>'
			},
			main: {
				src: '<%= jshint.all %>'
			}
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
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**'
			]
		}
	} );

	grunt.registerTask( 'lint', [ 'tyops', 'jshint', 'jscs:main', 'csslint', 'jsonlint', 'banana' ] );
	grunt.registerTask( 'fix', 'jscs:fix' );
	grunt.registerTask( 'test', 'lint' );
	grunt.registerTask( 'default', 'test' );
};
