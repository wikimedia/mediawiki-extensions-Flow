<?php

global $IP;
if ( !isset( $IP ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' ) ?: realpath( __DIR__ . '/../../..' );
}

require_once "$IP/maintenance/Maintenance.php";
