<?php

// NOTE: you must clone https://github.com/nikic/PHP-Parser into
// vendor manually
require __DIR__ . '/../vendor/PHP-Parser/lib/bootstrap.php';

$wgAutoloadClasses = [];
require __DIR__ . '/../autoload.php';

class OneClassPerFile {

	public function __construct( PhpParser\Parser $parser ) {
		$this->parser = $parser;
	}

	public function splitCode( $code ) {
		$stmts = $this->parser->parse( $code );

		// expect single top level namespace
		if ( count( $stmts ) !== 1 || !$stmts[0] instanceof PhpParser\Node\Stmt\Namespace_ ) {
			throw new RuntimeException( 'no toplevel namespace' );
		}

		$lines = explode( "\n", $code );
		$namespaceName = $stmts[0]->name->toString();

		$classes = $this->getClasses( $stmts[0] );
		$common = $this->getCommonHeader( $lines, $classes );

		foreach ( $classes as $stmt ) {
			$fqcn = $namespaceName . '\\' . $stmt->name;
			$result[$fqcn] = $common . $this->getCodeForStatement( $lines, $stmt );
		}

		return $result;
	}

	protected function getClasses( $stmt ) {
		$classes = [];
		foreach ( $stmt->stmts as $stmt ) {
			if (
				$stmt instanceof PhpParser\Node\Stmt\Interface_
				|| $stmt instanceof PhpParser\Node\Stmt\Class_
			) {
				$classes[] = $stmt;
			}
		}

		return $classes;
	}

	protected function getStartingLine( PhpParser\Node\Stmt $stmt ) {
		if ( $stmt->hasAttribute( 'comments' ) ) {
			$comments = $stmt->getAttribute( 'comments' );
			return $comments[0]->getLine() - 1;
		} else {
			return $stmt->getAttribute( 'startLine' ) - 1;
		}
	}

	protected function getEndingLine( $stmt ) {
		return $stmt->getAttribute( 'endLine' );
	}

	protected function getCommonHeader( array $lines, array $classes ) {
		$lastCommonLine = $this->getStartingLine( $classes[0] );
		return implode( "\n", array_slice( $lines, 0, $lastCommonLine ) ) . "\n";
	}

	protected function getCodeForStatement( array $lines, PhpParser\Node\Stmt $stmt ) {
		$start = $this->getStartingLine( $stmt );
		$end = $this->getEndingLine( $stmt );
		return implode( "\n", array_slice( $lines, $start, $end - $start ) ) . "\n";
	}
}

$files = [];
foreach ( $wgAutoloadClasses as $class => $file ) {
	$files[$file][] = $class;
}

$files = array_filter( $files, function ( $classes ) {
	return count( $classes ) > 1;
} );

$ocpf = new OneClassPerFile( new PhpParser\Parser( new PhpParser\Lexer ) );
foreach ( array_keys( $files ) as $file ) {
	$classes = $ocpf->splitCode( file_get_contents( $file ) );

	echo "Clearing out $file\n";
	unlink( $file );

	foreach ( $classes as $fqcn => $code ) {
		if ( false === strpos( $fqcn, '\\Tests\\' ) ) {
			// normal class
			$dest = 'includes/' . strtr( substr( $fqcn, 5 ), '\\', '/' ) . '.php';
		} else {
			// test class
			$dest = 'tests/phpunit/' . strtr( substr( $fqcn, 11 ), '\\', '/' ) . '.php';
		}

		echo "Writing $fqcn to $dest\n";
		file_put_contents( __DIR__ . '/../' . $dest, $code );
	}
}
