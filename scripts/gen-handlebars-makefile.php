<?php

require __DIR__ . '/../../../vendor/autoload.php';

class PartialLocator extends LightnCandy {
	static function getPartials( $template, $options ) {
		$context = self::buildContext( $options );
		if (static::handleError($context)) {
			return false;
		}

		// Strip extended comments
		$template = preg_replace(static::EXTENDED_COMMENT_SEARCH, '{{!*}}', $template);

		// Do first time scan to find out used feature, detect template error.
		static::setupToken($context);
		static::verifyTemplate($context, $template);

		if (static::handleError($context)) {
		    return false;
		}

		// Do PHP code generation.
		static::setupToken($context);
		$code = static::compileTemplate($context, static::escapeTemplate($template));

		// return false when fatal error
		if (static::handleError($context)) {
		    return false;
		}

		return array_keys( $context['usedPartial'] );
	}
}

$options = array(
	'basedir' => array( __DIR__ . '/../handlebars' ),
	'fileext' => array( '.handlebars' ),
	'flags' =>
		LightnCandy::FLAG_RUNTIMEPARTIAL |
		LightnCandy::FLAG_WITH |
		LightnCandy::FLAG_THIS |
		LightnCandy::FLAG_PARENT
);

$templates = array();
$skipPrefix = "{{! partial~}}\n";
foreach ( glob( __DIR__ . '/../handlebars/*.handlebars' ) as $template ) {
	$content = file_get_contents( $template );
	if ( substr( $content, 0, strlen( $skipPrefix ) ) === $skipPrefix ) {
		continue;
	}
	$templates[] = array(
		'template' => substr( basename( $template ), 0, -strlen( '.handlebars' ) ),
		'partials' => PartialLocator::getPartials( file_get_contents( $template ), $options ),
	);
}


$result = LightnCandy::compile( '{{> makefile_template}}', $options );
if ( is_array( $result ) ) {
	// failure
	var_dump( $result );
} else {
	$renderer = eval( substr( $result, 5 ) );
	echo $renderer( array( 'templates' => $templates ) );
}

