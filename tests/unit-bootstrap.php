<?php
require dirname(__DIR__).'/vendor/autoload.php';

function apply_filters( $tag, $value ) {
	$args    = func_get_args();
	$args    = array_slice( $args, 1 );
	$args[0] = $value;

	return \WP_Mock::onFilter( $tag )->apply( [$value] );
}

WP_Mock::bootstrap();

require dirname(__DIR__).'/wpdev.php';
