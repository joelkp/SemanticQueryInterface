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
$wgAutoloadClasses['SQI\\RelGraphInterface'] = __DIR__ . '/includes/RelGraphInterface.php';

/* Include global functions */
require_once __DIR__ . '/includes/GlobalFunctions.php';

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

/*
 * Configuration globals
 */

/**
 * Default limit for the number of results per query, for queries made
 * using the \SQI\QueryInterface class (and by other classes which use
 * this class).
 *
 * The limit set by the Semantic MediaWiki $smwgQMaxLimit setting also
 * holds, and exceeding it has no effect.
 *
 * If set to null (the default), then $smwgQMaxInlineLimit will be used.
 *
 * @var int|null $sqigQDefaultLimit
 */
$sqigQDefaultLimit = null;

/**
 * Default properties to include in query results, for queries made
 * directly using the \SQI\QueryInterface class.
 *
 * The default is:
 * @code
 * $sqigQIDefaultPrintouts = array(
 *     '_SKEY', // Default sortkey (unnamed in SMW)
 *     '_INST', // Categories      (unnamed in SMW)
 *     '_SUBC', // Subcategory of
 * );
 * @endcode
 *
 * Any value in the array should be either the internal ID of a special
 * property (like those in the default setting), or the name of a
 * user-defined property page (without the namespace) written with
 * underscores instead of spaces, e.g. "Has_description".
 *
 * If you want to add properties defined by Semantic Extra Special
 * Properties, there is a gotcha to keep in mind: the property names used
 * in the configuration of that extension differ from the internal IDs.
 * To add such properties, use the names given in the "id" definitions in
 * the file
 * "SemanticExtraSpecialProperties/src/Definition/definitions.json".
 * (Similar caveats could apply to special properties from some other
 * extensions, and if problems occur, make sure you've entered the
 * correct internal ID for the properties used.)
 *
 * @var string[] $sqigQIDefaultPrintouts
 */
$sqigQIDefaultPrintouts = array(
	'_SKEY', // Default sortkey (unnamed in SMW)
	'_INST', // Categories      (unnamed in SMW)
	'_SUBC', // Subcategory of
);

/**
 * Default printout labels, for queries made directly using the
 * \SQI\QueryInterface class. This is set as an array of 'ID' => 'Label'
 * pairs for properties, where the given label becomes:
 * - In the array output format, the array key for accessing the set of
 *   values for the property.
 * - An alternative name for the property, which can be used to specify a
 *   printout.
 * .
 * 'ID' should be either a special property ID (e.g. '_MDAT', '_SKEY'), or
 * the name of a user-defined property page (without the namespace),
 * written with underscores instead of spaces (e.g. 'Has_description').
 *
 * These alternative names only apply within \SQI\QueryInterface; this is
 * meant as a safe way of giving names to unnamed properties, or if one
 * wishes, to "rename" properties while querying.
 *
 * If a label is not configured here, the name retrieved from Semantic
 * MediaWiki is:
 * - For nearly all special properties, the name in the local language,
 *   e.g. "Modification date" in English vs. "Endringsdato" in Norwegian.
 *   If no name exists (as in the case of the "_SKEY" property), then the
 *   ID with spaces instead of underscores (" SKEY") ends up used.
 * - For user-defined properties, the name of the property page (without
 *   the namespace), using spaces instead of underscores.
 *
 * The default is the following:
 * @code
 * $sqigQIDefaultPrintoutLabels = array(
 *     '_INST' => 'Categories',      // Otherwise unnamed
 *     '_SKEY' => 'Default sortkey', // Otherwise unnamed
 * );
 * @endcode
 *
 * @var string[] $sqigQIDefaultPrintoutLabels
 */
$sqigQIDefaultPrintoutLabels = array(
	'_INST' => 'Categories',      // Otherwise unnamed
	'_SKEY' => 'Default sortkey', // Otherwise unnamed
);

