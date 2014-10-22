<?php
/**
 * Initialization file for the SemanticQueryInterface extension.
 *
 * @file SQI.php
 * @ingroup SemanticQueryInterface
 * @package SemanticQueryInterface
 *
 * @licence GNU GPL v3
 * @author Wikivote llc < http://wikivote.ru >
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( version_compare( $wgVersion, '1.17', '<' ) ) {
	die( '<b>Error:</b> This version of SemanticQueryInterface requires MediaWiki 1.17 or above.' );
}

global $wgSemanticQueryInterface;
$wgSemanticQueryInterfaceDir = __DIR__;

$extensionName = 'SemanticQueryInterface';

if (isset($wgInternalURLLink)) {
		$extensionUrl=$wgInternalURLLink.$extensionName;
}
else {
		$extensionUrl="https://www.mediawiki.org/wiki/User:Vedmaka/Semantic_Query_Interface";
}

$wgExtensionCredits['specialpage'][] = array(
		'path' => __FILE__,
		'name' => $extensionName,
		'version' => '0.1',
		'author' => 'WikiVote!',
		'url' => $extensionUrl,
		'descriptionmsg' => strtolower($extensionName).'-credits',
);

/* Resource modules */
$wgResourceModules['ext.SemanticQueryInterface.main'] = array(
    'localBasePath' => __DIR__ . '/',
    'remoteExtPath' => 'SemanticQueryInterface/',
    'group' => 'ext.SemanticQueryInterface',
    'scripts' => '',
    'styles' => ''
);

/* Message Files */
$wgExtensionMessagesFiles['SemanticQueryInterface'] = __DIR__ . '/SQI.i18n.php';

/* Autoload classes */
$wgAutoloadClasses['SemanticQueryInterfaceSpecial'] = __DIR__ . '/SQISpecial.class.php';
$wgAutoloadClasses['SemanticQueryInterfaceHooks'] = __DIR__ . '/SQI.hooks.php';

$wgAutoloadClasses['SQI\\QueryInterface'] = __DIR__ . '/includes/QueryInterface.php';
$wgAutoloadClasses['SQI\\Utils'] = __DIR__ . '/includes/Utils.php';

/* Rights */
$wgAvailableRights[] = 'usesemanticqueryinterface';

/* Permissions */
$wgGroupPermissions['sysop']['usesemanticqueryinterface'] = true;

/* Special Pages */
$wgSpecialPages['SemanticQueryInterface'] = 'SemanticQueryInterfaceSpecial';

/* Hooks */
#$wgHooks['example_hook'][] = 'SemanticQueryInterfaceHooks::onExampleHook';

/* Unit Tests */
$wgHooks['UnitTestsList'][] = 'SemanticQueryInterfaceHooks::onUnitTestsList';
