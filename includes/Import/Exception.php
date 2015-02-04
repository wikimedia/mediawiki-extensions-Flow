<?php

namespace Flow\Import;

class ImportException extends \Flow\Exception\FlowException {
}

class ApiNotFoundException extends ImportException {
}

class ApiNullResponseException extends ImportException {
}
