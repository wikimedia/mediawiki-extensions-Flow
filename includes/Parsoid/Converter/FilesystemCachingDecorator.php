<?php

namespace Flow\Parsoid\Converter;

use Flow\Parsoid\ContentConverter;
use Title;

/**
 * Primarily intended to be used from unit tests to remove external
 * dependencies on parsoid. Decorates an existing ContentConverter to
 * cache its values into a directory which can be commited to git.
 *
 * Does not handle updating the fixtures, every once in awhile
 * developers will need to purge the fixture directory and run the
 * tests from scratch.
 *
 * Be carefull to match your fixture directories to the converter
 * being decorated.  Instances decorating different converters must
 * use separate directories
 */
class FilesystemCachingDecorator implements ContentConverter {

	/**
	 * @var ContentConverter The converter being decorated
	 */
	protected $converter;

	/**
	 * @var string The directory where fixtures are stored
	 */
	protected $fixtureDir;

	/**
	 * @var ContentConverter $converter The converter being decorated
	 * @var string $fixtureDir The directory where fixtures are stored
	 */
	public function __construct( ContentConverter $converter, $fixtureDir ) {
		$this->converter = $converter;
		$this->fixtureDir = $fixtureDir;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequiredModules() {
		return $this->converter->getRequiredModules();
	}

	/**
	 * {@inheritDoc}
	 */
	public function convert( $from, $to, $content, Title $title ) {
		$key = md5( $from . $to . $content . $title->getPrefixedText() );
		$file = "{$this->fixtureDir}/$key";
		if ( file_exists( $file ) ) {
			$content = file_get_contents( $file );
		} else {
			$content = $this->converter->convert( $from, $to, $content, $title );
			file_put_contents( $file, $content );
		}

		return $content;
	}
}

