<?php

/**
 * Global functions specified and used by SemanticQueryInterface. These
 * functions have more generic usefulness than the classes they are used
 * in, and as such are provided for independent use.
 * @package SemanticQueryInterface
 */

/**
 * Return property ID corresponding to the given label. The
 * label is assumed to be well-formed; no correction is attempted.
 *
 * If the property is not a pre-defined one, then the label is
 * normalized to DBKey form and serves as the ID.
 *
 * @param string $label The property label
 * @return string       The property ID
 */
function sqifGetPropertyID( $label ) {
	$id = \SMWDIProperty::findPropertyID( $label );
	if ( $id !== false ) {
		return $id;
	} else {
		return smwfNormalTitleDBKey( $label );
	}
}

/**
 * Return property label corresponding to the given ID. The
 * ID is assumed to be well-formed; no correction is attempted.
 *
 * If no pre-defined property label is found, the ID is assumed to
 * be a page name, and is returned normalized - unless it begins
 * with an underscore, in which case it is assumed to be a
 * nameless property, and is returned unchanged.
 *
 * @param string $id The property ID
 * @return string    The property label
 */
function sqifGetPropertyLabel( $id ) {
	$label = \SMWDIProperty::findPropertyLabel( $id );
	if ( $label !== '' ) {
		return $label;
	} elseif ( ( $id !== '' ) && ( $id[0] == '_' ) ) {
		return $id;
	} else {
		return smwfNormalTitleText( $id );
	}
}

/**
 * Return the value of a \SMWDataItem.
 *
 * @param \SMWDataItem $di
 * @param bool $toString
 * @return array|bool|int|mixed|\numeric|string
 */
function sqifGetDataItemValue( \SMWDataItem $di, $toString = false ) {
	switch($di->getDIType()) {
		case \SMWDataItem::TYPE_BLOB:
			/** @var \SMWDIBlob $di */
			return $di->getString();
			break;
		case \SMWDataItem::TYPE_NUMBER:
			/** @var \SMWDINumber $di */
			return $di->getNumber();
			break;
		case \SMWDataItem::TYPE_WIKIPAGE:
			/** @var \SMWDIWikiPage $di */
			if($toString) {
				return $di->getTitle()->getText();
			}
			return $di->getTitle();
			break;
		case \SMWDataItem::TYPE_TIME:
			/** @var \SMWDITime $di */
			return $di->getMwTimestamp();
			break;
		case \SMWDataItem::TYPE_GEO:
			/** @var \SMWDIGeoCoord $di */
			if($toString) {
				return implode(',',$di->getCoordinateSet());
			}
			return $di->getCoordinateSet();
			break;
		case \SMWDataItem::TYPE_ERROR:
			/** @var \SMWDIError $di */
			if($toString) {
				return implode(',',$di->getErrors());
			}
			return $di->getErrors();
			break;
		case \SMWDataItem::TYPE_URI:
			/** @var \SMWDIUri $di */
			return $di->getURI();
			break;
		case \SMWDataItem::TYPE_NOTYPE:
			/** @var \SMWDIBlob $di */
			return false;
			break;
		default:
			return $di->getSerialization();
			break;
	}
}

function sqifGetSubobjectProperties( \Title $title, $properties = array('*') ) {
	$propValues = array();

	//Single property to single item array
	if( !is_array($properties) ) {
		$properties = array( $properties );
	}

	/** @var \SMWSql3StubSemanticData $subObjectSemanticData */
	//$subObjectSemanticData = smwfGetStore()->getSemanticData( $subObject->getSemanticData()->getSubject() );
	$wikiPage = new \SMWDIWikiPage( $title->getDBkey(), $title->getNamespace(), '', '_'.trim($title->getFragment()) );
	$subObjectSemanticData = smwfGetStore()->getSemanticData( $wikiPage );

	//Fetch all props from page
	if ( $properties[0] == '*' ) {
		$propList = $subObjectSemanticData->getProperties();
		$properties = array();
		/** @var \SMWDIProperty $propDI */
		foreach($propList as $propDI) {
			$properties[] = $propDI->getLabel();
		}
	}

	//Fetch all props values from smwfStore
	foreach( $properties as $property ) {

		$property = smwfNormalTitleDBKey($property);

		if(empty($property) || $property == '') {
			continue;
		}

		$propertyDi = new \SMWDIProperty( $property );
		//$pageDi = new \SMWDIWikiPage( $subObject->getSemanticData(), $title->getNamespace(), '' );
		$valueDis = $subObjectSemanticData->getPropertyValues( $propertyDi );

		//If we have at least one value
		if( count($valueDis) ) {

			//Fetch all Dv values
			foreach( $valueDis as $valueDi ) {

				//SMW >= 1.9
				if( class_exists('SMW\DataValueFactory') ) {
					/** @var \SMWDataValue $valueDv */
					$valueDv = \SMW\DataValueFactory::newDataItemValue( $valueDi, $propertyDi );
				}else{
					//SMW < 1.9
					$valueDv = \SMWDataValueFactory::newDataItemValue( $valueDi, $propertyDi );
				}

				$propValues[smwfNormalTitleText($property)][] = $valueDv->getWikiValue();
			}

		}else{
			$propValues[smwfNormalTitleText($property)] = array();
		}

	}

	return $propValues;
}

/**
 * Get property values from the requested wiki-page. By default
 * '*' returns all properties. An array of properties to return
 * can be passed.
 *
 * @param string $title
 * @param int $namespace
 * @param string|string[] $properties
 * @return array
 */
function sqifGetPageProperties( $title, $namespace = NS_MAIN , $properties = array('*') ) {
	$propValues = array();

	$title = smwfNormalTitleDBKey($title);

	//Single property to single item array
	if( !is_array($properties) ) {
		$properties = array( $properties );
	}

	//Fetch all props from page
	if ( $properties[0] == '*' ) {
		$propList = smwfGetStore()->getProperties( new \SMWDIWikiPage( $title, $namespace, '' ) );
		$properties = array();
		/** @var \SMWDIProperty $propDI */
		foreach($propList as $propDI) {
			$properties[] = $propDI->getLabel();
		}
	}

	//Fetch all props values from smwfStore
	foreach( $properties as $property ) {

		$property = smwfNormalTitleDBKey($property);

		if(empty($property) || $property == '') {
			continue;
		}

		$propertyDi = new \SMWDIProperty( $property );
		$pageDi = new \SMWDIWikiPage( $title, $namespace, '' );
		$valueDis = smwfGetStore()->getPropertyValues( $pageDi, $propertyDi );

		//If we have at least one value
		if( count($valueDis) ) {

			//Fetch all Dv values
			/** @var \SMWDataItem $valueDi */
			foreach( $valueDis as $valueDi ) {

				//SMW >= 1.9
				if( class_exists('SMW\DataValueFactory') ) {
					/** @var \SMWDataValue $valueDv */
					$valueDv = \SMW\DataValueFactory::newDataItemValue( $valueDi, $propertyDi );
				}else{
					//SMW < 1.9
					$valueDv = \SMWDataValueFactory::newDataItemValue( $valueDi, $propertyDi );
				}

				$propValues[smwfNormalTitleText($property)][] = $valueDv->getWikiValue();
			}

		}else{
			$propValues[smwfNormalTitleText($property)] = array();
		}

	}

	return $propValues;
}

/**
 * Convenience wrapper for getPageProperties in case of getting a
 * single property. Returns array of requested property values.
 *
 * @param string $title
 * @param int $namespace
 * @param string $property
 * @return array
 */
function sqifGetPageProperty( $title, $namespace, $property ) {
	$value = sqifGetPageProperties( $title, $namespace, $property );
	return array_shift($value);
}

/**
 * Set property values for the requested wiki-page. The
 * properties are to be passed as an array of
 * 'property' => 'value' entries. Returns whether or not all
 * property-value pairs were valid and the properties set.
 *
 * @param string $title
 * @param int $namespace
 * @param array $properties
 * @return bool
 */
function sqifSetPageProperties( $title, $namespace = NS_MAIN, $properties = array() ) {
	$allValid = true;

	if ( !count( $properties ) ) {
		return $allValid;
	}

	$semanticData = smwfGetStore()->getSemanticData( new SMWDIWikiPage( $title, $namespace, '' ) );

	foreach ( $properties as $propertyName => $propertyValue ) {

		$propertyDv = SMWPropertyValue::makeUserProperty( $propertyName );
		$propertyDi = $propertyDv->getDataItem();

		$result = SMWDataValueFactory::newPropertyObjectValue(
			$propertyDi,
			$propertyValue,
			$propertyName,
			$semanticData->getSubject()
		);
		if ( $result->isValid() ) {
			$semanticData->addPropertyObjectValue( $propertyDi,
				$result->getDataItem() );
		} else {
			$allValid = false;
		}
	}

	smwfGetStore()->updateData( $semanticData );
	$a = Title::newFromText( $title )->getDBkey();
	smwfGetStore()->refreshData( Title::newFromText( $title )->getArticleID(), 1 );

	//echo '<pre>';print_r( $result );echo '</pre>';

	return $allValid;
}

/**
 * Convenience wrapper for setPageProperties in case of setting a
 * single property. Returns whether or not the property-value
 * pair was valid and the property set.
 *
 * @param string $title
 * @param int $namespace
 * @param string $property
 * @param mixed $value
 * @return bool
 */
function sqifSetPageProperty( $title, $namespace, $property, $value ) {
	$result = sqifSetPageProperties( $title, $namespace, array($property => $value) );
	return $result;
}

