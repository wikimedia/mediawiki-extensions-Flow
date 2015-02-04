<?php

namespace Flow\Import;

/**
 * Base class for errors in the Flow\Import module
 */
class ImportException extends \Flow\Exception\FlowException {
}

/**
 * Thrown when the liquidthreads api reports that a
 * requested page or revision does not exist.
 */
class ApiNotFoundException extends ImportException {
}

/**
 * A failure occured trying to read or write to the
 * permanant storage backing the ImportSourceStore.
 */
class ImportSourceStoreException extends ImportException {
}

