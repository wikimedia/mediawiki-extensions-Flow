/*!
 * Grunt file
 *
 * @package Flow
 */

/* eslint-env node: */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );
	grunt.loadNpmTasks( 'grunt-tyops' );
	grunt.loadNpmTasks( 'grunt-browserify' );

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
		banana: conf.MessagesDirs,
		watch: {
			files: [
				'.{stylelintrc,eslintrc}.json',
				'<%= eslint.all %>',
				'<%= stylelint.all %>'
			],
			tasks: 'test'
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		browserify: {
			client: {
				src: [
					'modules/fleact/src/client.js'
				],
				dest: 'modules/fleact/dist/client.js',
				options: {
					transform: [ [ 'babelify', { presets: [ "es2015", "react" ] } ] ]
				}
			},
			server: {
				src: [
					'modules/fleact/src/server.js'
				],
				dest: 'modules/fleact/dist/server.js',
				options: {
					transform: [ [ 'babelify', { presets: [ "es2015", "react" ] } ] ]
				}
			}
		}
	} );

	grunt.registerTask( 'lint', [ 'tyops', 'eslint', 'stylelint', 'jsonlint', 'banana' ] );
	grunt.registerTask( 'test', 'lint' );
	grunt.registerTask( 'fleact', [ 'browserify:client', 'browserify:server' ] );
	grunt.registerTask( 'default', 'test' );
};
