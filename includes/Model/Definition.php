<?php

namespace Flow\Model;

class Definition {

	protected $id;
	protected $type;
	protected $wiki;
	protected $name;
	protected $options = array();

	static public function fromStorageRow( array $row ) {
		$obj = new self;
		if ( ! $row['definition_wiki'] ) {
			die( var_dump( $row ) );
			throw new \MWException( "No definition_wiki" );
		}
		$obj->id = UUID::create( $row['definition_id'] );
		$obj->type = $row['definition_type'];
		$obj->wiki = $row['definition_wiki'];
		$obj->name = $row['definition_name'];
		$obj->options = $row['definition_options'] ? unserialize( $row['definition_options'] ) : array();
		return $obj;
	}

	static public function toStorageRow( Definition $obj ) {
		return array(
			'definition_id' => $obj->id->getBinary(),
			'definition_type' => $obj->type,
			'definition_wiki' => $obj->wiki,
			'definition_name' => $obj->name,
			'definition_options' => $obj->options ? serialize( $obj->options ) : null,
		);
	}

	static public function create( $name, $type, array $options = array() ) {
		$obj = new self;
		$obj->id = UUID::create();
		$obj->wiki = wfWikiId();
		$obj->name = $name;
		$obj->type = $type;
		$obj->options = $options;
		return $obj;
	}

	public function getId() { return $this->id; }
	public function getWiki() { return $this->wiki; }
	public function getName() { return $this->name; }
	public function getType() { return $this->type; }
	public function getOptions() { return $this->options; }
	public function getOption( $key, $default = null ) {
		return array_key_exists( $key, $this->options ) ? $this->options[$key] : $default;
	}

	public function createWorkflow( \User $user, \Title $title ) {
		return Workflow::create( $this, $user, $title );
	}
}

