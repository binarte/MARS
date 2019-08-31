#!/usr/bin/php
<?php

namespace mars;

require ('mars.php');

class A {

	function __construct() {
		throw new \Exception ();
	}
}

function f() {
	$a = new Test ();
}

f ();