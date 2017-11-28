<?php

function _a() {
	if (!isset($GLOBALS['_t'])) {
		require __DIR__.'/../lang/fr.lang.php';
		$GLOBALS['_a'] = $___;
		$GLOBALS['_t'] = $__;
	}


	$args = func_get_args();
	$string = array_shift($args);

	if (isset($GLOBALS['_a'][$string])) {
		if (count($args)) {
			return $GLOBALS['_a'][$string][$args[0]];
		} else {
			return $GLOBALS['_a'][$string];
		}
	} else {
		return $string;
	}
}

function __() {
	if (!isset($GLOBALS['_t'])) {
		require __DIR__.'/../lang/fr.lang.php';
		$GLOBALS['_a'] = $___;
		$GLOBALS['_t'] = $__;
	}


	$args = func_get_args();
	$string = array_shift($args);

	if (isset($GLOBALS['_t'][$string])) {
		return call_user_func_array('sprintf', array_merge([$GLOBALS['_t'][$string]], $args));
	} else {
		return $string;
	}
}

$__ = '__';
