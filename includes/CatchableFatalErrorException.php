<?php

namespace Flow;

use ErrorException;

/**
 * This class is not necessary, but having this so we could
 * have a specific exception type to catch?
 */
class CatchableFatalErrorException extends ErrorException {

}
