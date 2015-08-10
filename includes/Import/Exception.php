<?php

namespace Flow\Import;
use Flow\Exception\FlowException;

/**
 * Base class for errors in the Flow\Import module
 */
class ImportException extends FlowException {
}

/**
 * A failure occurred trying to read or write to the
 * permanant storage backing the ImportSourceStore.
 */
class ImportSourceStoreException extends ImportException {
}

