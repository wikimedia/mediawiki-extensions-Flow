<?php

// NOTE: you must clone https://github.com/nikic/PHP-Parser into
// vendor manually
require __DIR__ . '/../vendor/PHP-Parser/lib/bootstrap.php';

$wgAutoloadClasses = array();
require __DIR__ . '/../autoload.php';

$files = array();
foreach ( $wgAutoloadClasses as $class => $file ) {
	$files[$file][] = $class;
}

$files = array_filter( $files, function( $classes ) {
	return count( $classes ) > 1;
} );

$parser = new PhpParser\Parser( new PhpParser\Lexer );
foreach ( array_keys( $files ) as $file ) {
	$content = file_get_contents( $file );
	$stmts = $parser->parse( $content );

	// expect single top level namespace
	if ( count( $stmts ) !== 1 || !$stmts[0] instanceof PhpParser\Node\Stmt\Namespace_ ) {
		die( 'no toplevel namespace: ' . $file );
	}

	$ns = $stmts[0];

	$unique = array();
	$lastCommonLine = null;
	foreach ( $ns->stmts as $stmt ) {
		if ( $stmt instanceof PhpParser\Node\Stmt\Interface_ || $stmt instanceof PhpParser\Node\Stmt\Class_ ) {
			if ( $lastCommonLine === null ) {
				$lastCommonLine = $stmt->getAttribute( 'startLine' ) - 1;
			}
			$unique[] = $stmt;
		}
	}

	if ( $lastCommonLine === null ) {
		die( 'wtf?!' );
	}

	$lines = explode( "\n", $content );
	$common = array_slice( $lines, 0, $lastCommonLine );
	echo "Clearing out $file\n";
	unlink( $file );
	foreach ( $unique as $stmt ) {
		$fqcn = $ns->name->toString() . '\\' . $stmt->name;

		$start = $stmt->getAttribute( 'startLine' );
		if ( count( $stmt->stmts ) ) {
			// more magic, not sure why necessary but sometimes is.
			$end = end( $stmt->stmts )->getAttribute( 'endLine' ) + 1;
		} else {
			$end = $stmt->getAttribute( 'endLine' );
		}
		// magic numbers :(
		$class = array_slice( $lines, $start - 1, $end - $start + 2 );

		if ( false === strpos( $fqcn, '\\Tests\\' ) ) {
			// normal class
			$dest = 'includes/' . strtr( substr( $fqcn, 5 ), '\\', '/' ) . '.php';
		} else {
			// test class
			$dest = 'tests/phpunit/' . strtr( substr( $fqcn, 11 ), '\\', '/' ) . '.php';
		}

		echo "Writing $fqcn to $dest\n";
		file_put_contents(
			__DIR__ . '/../' . $dest,
			implode( "\n", $common ) . "\n" . implode( "\n", $class ) . "\n"
		);
	}
}
