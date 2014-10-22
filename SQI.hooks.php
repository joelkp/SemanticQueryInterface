<?php
/**
 * Hooks class declaration for mediawiki extension SemanticQuery
 *
 * @file SQI.hooks.php
 * @ingroup SemanticQueryInterface
 * @package SemanticQueryInterface
 *
 */
class SemanticQueryInterfaceHooks {

	public static function onUnitTestsList( &$files ) {
		$testDir = __DIR__ . '/tests/phpunit';
		$files = array_merge( $files, glob( "$testDir/*Test.php" ) );
		return true;
	}

}
