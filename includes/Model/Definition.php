<?php

namespace Flow\Model;

use Flow\Exception\DataModelException;
use Flow\Exception\InvalidDataException;

class Definition {
	/**
	 * @var UUID
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $wiki;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @param array $row
	 * @param object|null $obj
	 * @return Definition
	 * @throws InvalidDataException
	 * @throws DataModelException
	 */
	static public function fromStorageRow( array $row, $obj = null ) {
		if ( !$row['definition_wiki'] ) {
			throw new InvalidDataException( "No definition_wiki", 'fail-load-data' );
		}
		if ( $obj === null ) {
			$obj = new self;
		} elseif ( !$obj instanceof self ) {
			throw new DataModelException( 'Wrong obj type: ' . get_class( $obj ), 'process-data' );
		}
		$obj->id = UUID::create( $row['definition_id'] );
		$obj->type = $row['definition_type'];
		$obj->wiki = $row['definition_wiki'];
		$obj->name = $row['definition_name'];
		$obj->options = $row['definition_options'] ? unserialize( $row['definition_options'] ) : array();
		return $obj;
	}

	/**
	 * @param Definition $obj
	 * @return array
	 */
	static public function toStorageRow( Definition $obj ) {
		return array(
			'definition_id' => $obj->id->getAlphadecimal(),
			'definition_type' => $obj->type,
			'definition_wiki' => $obj->wiki,
			'definition_name' => $obj->name,
			'definition_options' => $obj->options ? serialize( $obj->options ) : null,
		);
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param array $options
	 * @return Definition
	 */
	static public function create( $name, $type, array $options = array() ) {
		$obj = new self;
		$obj->id = UUID::create();
		$obj->wiki = wfWikiId();
		$obj->name = $name;
		$obj->type = $type;
		$obj->options = $options;
		return $obj;
	}

	/**
	 * @return UUID
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getWiki() {
		return $this->wiki;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getOption( $key, $default = null ) {
		return array_key_exists( $key, $this->options ) ? $this->options[$key] : $default;
	}

	/**
	 * @param \User $user
	 * @param \Title $title
	 * @return Workflow
	 */
	public function createWorkflow( \User $user, \Title $title ) {
		return Workflow::create( $this, $user, $title );
	}
}

