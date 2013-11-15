<?php

namespace Flow\Rendering;

class TemplateRenderer extends UIElement {
	protected $templating;
	protected $template = null;
	protected $params = array();

	public function instantiate( array $parameters ) {
		$this->templating = $parameters['templating'];
		$this->template = $parameters['template'];		
	}

	public function getValidParameters() {
		return array(
			'templating' => array(
				'required' => true,
			),
			'template' => array(
				'required' => true,
			),
		);
	}

	public function render( ) {
		return $this->templating->render( $this->getTemplate(), $this->getParameters() );
	}

	public function getTemplate() {
		return $this->template;
	}

	public function getParams() {
		return $this->params;
	}
}