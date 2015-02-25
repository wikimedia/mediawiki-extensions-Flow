<?php

namespace Flow\Tests\Parsoid\Fixer;

use Flow\Model\UUID;
use Flow\Parsoid\ContentFixer;
use Flow\Parsoid\Fixer\Redlinker;
use Flow\Parsoid\Utils;
use Flow\Tests\PostRevisionTestCase;
use Html;
use Title;

class MethodReturnsConstraint extends \PHPUnit_Framework_Constraint {
	public function __construct( $method, \PHPUnit_Framework_Constraint $constraint ) {
		$this->method = $method;
		$this->constraint = $constraint;
	}

	protected function matches( $other ) {
		return $this->constraint->matches( call_user_func( array( $other, $this->method ) ) );
	}

	public function toString() {
		return $this->constraint->toString();
	}

	protected function failureDescription( $other ) {
		return $this->constraint->failureDescription( $other ) . " from {$this->method} method";
	}
}
