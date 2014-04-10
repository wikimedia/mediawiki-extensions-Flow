<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use LightnCandy;
use LCSafeString;

class TemplateHelper {

	protected $renderers;

	/**
	 * @param string $templateDir
	 * @param string|null $tempDir Temporary directory
	 */
	public function __construct( $templateDir, $tempDir ) {
		$this->templateDir = $templateDir;
		$this->tempDir = $tempDir;
	}

	public function getTemplate( $templateName ) {
		if ( isset( $this->renderers[$templateName] ) ) {
			return $this->renderers[$templateName];
		}

		$template = "{$this->templateDir}/{$templateName}.html.handlebars";
		$compiled = "$template.php";

		if ( !file_exists( $compiled ) ) {
			if ( !file_exists( $template ) ) {
				throw new FlowException( "Could not locate template: $template" );
			}

			$code = LightnCandy::compile(
				file_get_contents( $template ),
				array(
					'flags' => LightnCandy::FLAG_ERROR_EXCEPTION
						| LightnCandy::FLAG_EXTHELPER
						| LightnCandy::FLAG_WITH
						| LightnCandy::FLAG_PARENT,
					'basedir' => array( $this->templateDir ),
					'fileext' => array( '.html.handlebars' ),
					'helpers' => array(
						'l10n' => 'Flow\TemplateHelper::l10n',
						'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
						'timestamp' => 'Flow\TemplateHelper::timestamp',
						'html' => 'Flow\TemplateHelper::html',
						'block' => 'Flow\TemplateHelper::block',
						'author' => 'Flow\TemplateHelper::author',
						'url' => 'Flow\TemplateHelper::url',
						'formElement' => 'Flow\TemplateHelper::formElement',
						'math' => 'Flow\TemplateHelper::math',
						'post' => 'Flow\TemplateHelper::post',
					),
					'blockhelpers' => array(
						'eachPost' => 'Flow\TemplateHelper::eachPost',
					),
				)
			);
			if ( !$code ) {
				throw new \Exception( 'Not possible?' );
			}
			file_put_contents( $compiled, $code );
		}

		return $this->renderers[$templateName] = include $compiled;
	}

	static public function processTemplate( $templateName, $args, array $scopes = array() ) {
		// Undesirable, but lightncandy helpers have to be static methods
		$template = Container::get( 'lightncandy' )->getTemplate( $templateName );
		return call_user_func( $template, $args, $scopes );
	}

	static public function l10n( $str, $args = array(), $options = array() ) {
		return "<$str>";
		return self::html( wfMessage( $str, $args )->escaped() );
	}

	static public function uuidTimestamp( $uuid, $str, $timeAgoOnly = false ) {
		$obj = UUID::create( $uuid );
		if ( !$obj ) {
			return null;
		}

		$timestamp = $obj->getTimestampObj()->getTimestamp() * 1000;
		return self::timestamp( $timestamp, $str, $timeAgoOnly );
	}

	static public function timestamp( $timestamp, $str, $timeAgoOnly = false ) {
		if ( !$timestamp || !$str ) {
			return;
		}

		$secondsAgo = time() - $timestamp / 1000;
		if ( $secondsAgo < 2419200 ) {
			$timeAgo = self::l10n( $str, $secondsAgo );
			if ( $timeAgoOnly === true ) {
				return $timeAgo;
			}
		} elseif ( $timeAgoOnly === true ) {
			return;
		} else {
			$timeAgo = null;
		}

		return self::html( self::processTemplate(
			'timestamp',
			array(
				'time_iso' => $timestamp,
				'time_readable' => self::l10n( 'datetime', $timestamp ),
				'time_ago' => $timeAgo,
				'guid' => null,
			)
		) );
	}

	static public function html( $string ) {
		return new LCSafeString( $string );
	}

	static public function block( $block ) {
		return self::html( self::processTemplate(
			"flow_block_" . $block['type'],
			$block
		) );
	}

	/**
	 * @param array $context The 'this' value of the calling context
	 * @param array $arguments Arguments passed into the helper
	 * @param array $options blockhelper specific invocation options
	 */
	static public function eachPost( $context, $arguments, $options ) {
		list( $data, $postIds ) = $arguments;
		if ( $data === null ) {
			var_dump( $arguments ); throw new \Exception;
		}
		if ( count( $postIds ) === 0 ) {
			return call_user_func( $options['inverse'], $options['cx'], array() );
		}
		$fn = $options['fn'];
		$ret = array();
		$i = 0;
		$last = count( $postIds ) - 1;
		foreach ( $postIds as $id ) {
			$revId = $data['posts'][$id][0];

			// iteration variables
			$options['cx']['sp_vars'] = array(
				'index' => $i,
				'first' => $i === 0,
				'last' => $i === $last
			);


			$cx['scopes'][] = $data['revisions'][$revId];
			// $fn is always safe return value, its the inner template content
			$ret[] = call_user_func( $fn, $options['cx'], $data['revisions'][$revId] );
			array_pop( $cx['scopes'] );
			$i++;
		}

		return implode( '', $ret );
	}

	static public function author() {
	}

	static public function url() {
	}

	static public function formElement() {
	}

	static public function math() {
	}

	// Required to prevent recursion loop
	static public function post( $rootBlock, $revision ) {
		return self::html( self::processTemplate( 'flow_post', array(
			'revision' => $revision,
			'rootBlock' => $rootBlock,
		) ) );
	}
}
