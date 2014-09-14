<?php

$base = realpath( __DIR__ . '/../includes' );

function collectDir( $base, $dir, array $classes ) {
	$dir = realpath( $dir );
	$base = realpath( $base );
	$it = new RecursiveDirectoryIterator( $dir );
	$it = new RecursiveIteratorIterator( $it );

	foreach ( $it as $path => $file ) {
		$ext = pathinfo( $path, PATHINFO_EXTENSION );
		if ( $ext !== 'php' ) {
			continue;
		}

		$classes = collectFile( $base, $path, $classes );
	}

	return $classes;
}

function collectFile( $base, $path, array $classes ) {
	$shortpath = substr( $path, strlen( $base ) + 1 );
	$tokens = token_get_all( file_get_contents( $path ) );
	$expect = null;
	$callback = null;
	$collector = array();
	$namespace = '';
	$start = count( $classes );
	foreach ( $tokens as $source ) {
		if ( $expect !== null ) {
			$collector[] = $source;
			$isMatch = is_string( $source )
				? $expect === $source
				: $expect === $source[0];

			if ( $isMatch ) {
				$callback( $collector );
				$collector = array();
				$callback = $expect = null;
			}
			continue;
		}

		if ( is_string( $source ) ) {
			continue;
		}

		list( $token, $content, $lineno ) = $source;
		switch( $token ) {
		case T_NAMESPACE:
			// Capture until a semi-coloon is reached
			$expect = ';';
			$callback = function( $collector ) use ( &$namespace ) {
				$content = implodeTokens( $collector );
				$namespace = $content . '\\';
			};
			break;

		case T_CLASS:
		case T_INTERFACE;
			// capture the next string
			$expect = T_STRING;
			$callback = function( $collector ) use ( &$classes, &$namespace, $shortpath ) {
				$content = implodeTokens( $collector );
				$classes[$shortpath][] = $new = $namespace . $content;
			};
		}
	}

	return $classes;
}

function implodeTokens( array $collector ) {
	$content = '';
	foreach ( $collector as $source ) {
		$content .= is_string( $source ) ? $source : $source[1];
	}
	return trim( $content, " ;\t\n" );
}

function generateAutoload( array $classes ) {
	$content = array( "<?php\n\n" );
	foreach ( $classes as $path => $contained ) {
		$exportedPath = var_export( '/' . $path, true );
		foreach ( $contained as $fqcn ) {
			$content[] = sprintf(
				'$wgAutoloadClasses[%s] = __DIR__ . %s;' . "\n",
				var_export( $fqcn, true ),
				$exportedPath
			);
		}
	}

	return implode( '', $content );
}


function main() {
	$base = realpath( __DIR__ . '/../' );
	$classes = array();
	foreach ( array( 'includes', 'tests/phpunit', 'vendor' ) as $dir ) {
		$classes = collectDir( $base, $dir, $classes );
	}
	foreach ( glob( $base . '/*.php' ) as $file ) {
		$classes = collectFile( $base, $file, $classes );
	}

	file_put_contents(
		__DIR__ . '/../autoload.php',
		generateAutoload( $classes )
	);

	echo "Done.\n\n";
}

main();
