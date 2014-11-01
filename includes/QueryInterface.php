<?php

namespace SQI;

use SMW\DataValueFactory;
use SMW\Subobject;

/**
 * Class QueryInterface
 * @package SemanticQueryInterface
 */
class QueryInterface {

	/** @var  \SMWStore */
	protected $store;
	/** @var  array */
	protected $config;
	/** @var  string */
	protected $page;
	/** @var  array[] */
	protected $conditions;
	/** @var  string[] */
	protected $printouts;
	/** @var  string[] */
	protected $printoutLabels;
	/** @var  string[] */
	protected $categories;
	/** @var  int */
	protected $limit;
	/** @var  int */
	protected $offset;

	/** @var \SMWQueryResult */
	protected $result;

	/**
	 * Constructor. A configuration array can be passed to override
	 * default options; the array keys and default values are:
	 * - 'flat_property_values' => false
	 *   Fetch only last (first) property value instead of one
	 *   element array.
	 * - 'printouts' => $sqigQIDefaultPrintouts
	 *   The globally configured default printouts for every subject
	 *   page.
	 * - 'printout_labels' => $sqigQIDefaultPrintoutLabels
	 *   The globally configured default printout labels (used for
	 *   printouts in favor of property names retrieved from
	 *   Semantic MediaWiki).
	 * - 'fetch_all_properties' => false
	 *   Fetch all (non-special) subject properties and their values
	 *   by default.
	 *
	 * @param array|null $config
	 */
	function __construct( $config = null ) {
		$this->reset( $config );
	}

	/**
	 * Reset the state. This includes configuration, which is either
	 * set to the default, or using the passed configuration option
	 * array as in the constructor.
	 *
	 * @param array|null $config
	 */
	public function reset( $config = null ) {
		/*
		 * Semantic store.
		 */
		$this->store = smwfGetStore();

		/*
		 * Default configuration.
		 *
		 * See constructor documentation regarding the options.
		 */
		$this->config = array(
			'flat_property_values' => false,
			'printouts' => $GLOBALS['sqigQIDefaultPrintouts'],
			'printout_labels' => $GLOBALS['sqigQIDefaultPrintoutLabels'],
			'fetch_all_properties' => false,
			'flat_result_array' => false
		);
		if( $config !== null ) {
			$this->config = array_merge( $this->config, $config );
		}

		/*
		 * The rest.
		 */
		$this->page = null;
		$this->conditions = array();
		$this->printouts = $this->config['printouts'];
		$this->printoutLabels = $this->config['printout_labels'];
		$this->categories = array();
		$this->result = null;
		$this->limit = ( $GLOBALS['sqigQDefaultLimit'] !== null ) ?
			$GLOBALS['sqigQDefaultLimit'] :
			$GLOBALS['smwgQMaxInlineLimit'];
		$this->offset = 0;
		$this->result = null;
	}

	/**
	 * Set the query results offset.
	 *
	 * @param int $offset
	 */
	public function setOffset( $offset ) {
		$this->offset = $offset;
	}

	/**
	 * Set the query results limit.
	 *
	 * The limit set by the Semantic MediaWiki $smwgQMaxLimit setting
	 * also holds, and exceeding it has no effect.
	 *
	 * The default is determined by the $sqigQDefaultLimit setting.
	 *
	 * @param int $limit
	 */
	public function setLimit( $limit ) {
		$this->limit = $limit;
	}

	/**
	 * Apply some condition to query.
	 * @param array $condition should be array like (propertyName) | (propertyName,propertyValue)
	 * @param null $conditionValue
	 * @return $this
	 */
	public function condition( $condition, $conditionValue = null ) {
		if(!is_array($condition)) {
			if( $conditionValue !== null ) {
				//Lets handle free-way calling, why not?
				$condition = array( $condition, $conditionValue );
			}else{
				$condition = array( $condition );
			}
		}
		$this->conditions[] = $condition;
		return $this;
	}

	/**
	 * Adds one or more properties to be fetched and printed out; use
	 * '*' to print out all properties.
	 *
	 * @param string|string[] $idOrLabel
	 * @return $this
	 */
	public function printout( $idOrLabel, $label = null ) {
		if( $idOrLabel == '*' ) {
			$this->config['fetch_all_properties'] = true;
			return $this;
		}

		$id = Utils::getPropertyID( $idOrLabel );
		$this->printouts[] = $id;
		if ( $label === null ) {
			if ( isset( $this->printoutLabels[$id] ) ) {
				$label = $this->printoutLabels[$id];
			} else {
				$label = Utils::getPropertyLabel( $id );
				$this->printoutLabels[$id] = $label;
			}
		} else {
			$this->printoutLabels[$id] = $label;
		}

		return $this;

/*
		if( $printout == '*' ) {
			$this->config['fetch_all_properties'] = true;
			return $this;
		}
		if( is_array( $printout ) ) {
			foreach ( $printout as $pt ) {
				$this->printouts[] = $pt;
			}

		}else{
			$this->printouts[] = $printout;
		}
*/
		return $this;
	}

	/**
	 * Sets query limitation to category(ies)
	 * @param $category
	 * @return $this
	 */
	public function category( $category ) {
		$this->categories[] = smwfNormalTitleDBKey($category);
		return $this;
	}

	/**
	 * Sets query limitation to specified page (title as string)
	 *
	 * @param $page
	 * @return $this
	 */
	public function from( $page, $flatResult = false ) {
		if( $flatResult ) {
			$this->config['flat_result_array'] = true;
		}
		$this->page = smwfNormalTitleDBKey($page);
		return $this;
	}

	/**
	 * Return raw query result as SMWQueryResult object.
	 *
	 * Unless already done, this executes the query.
	 *
	 * @return \SMWQueryResult
	 */
	public function getResult() {
		if( $this->result === null ) {
			$this->executeQuery();
		}
		return $this->result;
	}

	/**
	 * Return query result count.
	 *
	 * Unless already done, this executes the query.
	 *
	 * @return int
	 */
	public function getResultCount() {
		if( $this->result === null ) {
			$this->executeQuery();
		}
		return $this->result->getCount();
	}

	/**
	 * Return query result subjects (pages and/or subobjects) as
	 * Title instances.
	 *
	 * Unless already done, this executes the query.
	 *
	 * @return \Title[]
	 */
	public function getResultSubjects() {
		if ( $this->result === null ) {
			$this->executeQuery();
		}
		$subjects = array();
		$result = $this->result->getResults();
		/** @var \SMWDIWikiPage $subject */
		foreach ($result as $subject) {
			$title = $subject->getTitle();
			if ( $subject->getSubobjectName() !== '' ) {
				$subjects[] = $title;
				continue;
			}
			if ( !in_array( $title, $subjects ) ) {
				$subjects[] = $title;
			}
		}

		return $subjects;
	}

	/**
	 * Main method to get query results. Converts raw semantic result
	 * to human-readable array.
	 *
	 * Unless already done, this executes the query.
	 *
	 * @todo This method needs slight refactoring about array keys organisation.
	 * @todo Querying for 'fetch_all_properties' inside this method is broken design
	 *
	 * @param bool $stringifyPropValues cast all properties values types to string
	 * @return array
	 */
	public function getResultArray( $stringifyPropValues = false ) {
		if( $this->result === null ) {
			$this->executeQuery();
		}

		$array = array();

		//Main mystery here, that if we have some printouts - $result->getNext will work,
		//but if there no printouts, only pages - method will return false!

		//Fill array with subjects
		$resultSubjects = $this->getResultSubjects();
		foreach($resultSubjects as $title) {
			$properties = array();

			//TODO: There should be more beautiful way to form array keys ...
			$arrayKey = $title->getArticleID() . ( ($title->getFragment()) ? '#'.trim($title->getFragment(),'_ ') : '' );

			//Fetch all subject properties if config set to true
			if( $this->config['fetch_all_properties'] ) {
				if( $title->getFragment() ) {
					$properties = Utils::getSubobjectProperties( $title );
				}else{
					$properties = Utils::getPageProperties( $title->getText(), $title->getNamespace() );
				}
				if( $this->config['flat_property_values'] ) {
					foreach ( $properties as &$property ) {
						if( is_array($property) && count($property) ) {
							$property = $property[0];
						}
					}

				}
			}
			//Push subject to array
			$array[$arrayKey] = array(
				'title' => $title,
				'properties' => $properties
			);
		}

		//If there is something to "print"
		$test = clone $this->result;
		$check = $test->getNext();
		if( $check !== false && count($check) ) {
			//We have something to "print"
			//Copy result object to iterate
			$result = clone $this->result;
			/** @var \SMWResultArray[] $row */
			while( $row = $result->getNext() ) {
				//Iterate through properties and subjects
				foreach( $row as $rowItem ) {
					$subject = $rowItem->getResultSubject();

					//TODO: There should be more beautiful way to form array keys ...
					$arrayKey = $subject->getTitle()->getArticleID() . ( ($subject->getSubobjectName()) ? '#'.trim($subject->getSubobjectName(),'_ ') : '' );

					/** @var \SMWDataItem[] $propValues */
					$propValues = $rowItem->getContent();
					$propName = $rowItem->getPrintRequest()->getLabel();
					$propName = smwfNormalTitleText($propName);
					foreach($propValues as $propValue) {
						$value = Utils::getPropertyValue( $propValue, $stringifyPropValues );
						//If option enabled, flat every property except system arrays
						if( $this->config['flat_property_values'] && $propName != 'Categories' && $propName != 'SubcategoryOf' ) {
							$array[$arrayKey]['properties'][$propName] = $value;
						}else{
							$array[$arrayKey]['properties'][$propName][] = $value;
						}
					}
				}
			}
		}

		if( $this->config['flat_result_array'] && count($array) ) {
			return array_shift($array);
		}
		return $array;
	}

	/**
	 * Builds query from options set initially
	 *
	 * @return \SMWConjunction
	 */
	protected function buildQuery() {
		$queryDescription = new \SMWThingDescription();
		$conditionDescriptions = array();

		//Target page
		if( $this->page !== null ) {
			$page = new \SMWWikiPageValue('_wpg');
			$page->setUserValue($this->page);
			$pageDescription = new \SMWValueDescription( $page->getDataItem() );
			$conditionDescriptions[] = $pageDescription;
		}

		//Create category scope
		if( count($this->categories) ) {
			foreach($this->categories as $category) {
				$categoryTitle = new \SMWDIWikiPage( $category, NS_CATEGORY, '' );
				$categoryDescription = new \SMWClassDescription($categoryTitle);
				$conditionDescriptions[] = $categoryDescription;
			}
		}

		//Create conditions array
		foreach($this->conditions as $condition) {
			$property = \SMWDIProperty::newFromUserLabel($condition[0]);
			$valueDescription = new \SMWThingDescription();
			if( isset($condition[1]) ) {
				//SMW >= 1.9
				if( class_exists('SMW\DataValueFactory') ) {
					/** @var \SMWDataValue $value */
					$value = DataValueFactory::newPropertyValue( $condition[0], $condition[1] );
				}else{
				//SMW < 1.9
					$prop = \SMWDIProperty::newFromUserLabel($condition[0]);
					$value = \SMWDataValueFactory::newPropertyObjectValue( $prop, $condition[1] );
				}
				$valueDescription = new \SMWValueDescription( $value->getDataItem() );
			}
			$description = new \SMWSomeProperty( $property, $valueDescription );
			//Add condition properties to printouts
			if(!in_array($condition[0],$this->printouts)) {
				$this->printouts[] = $condition[0];
			}
			//Store description in conditions array
			$conditionDescriptions[] = $description;
		}

		//Build up query
		if( count($conditionDescriptions) > 1 ) {
			//Conjunction
			$queryDescription = new \SMWConjunction( $conditionDescriptions );
		}else{
			//Simple
			$queryDescription = $conditionDescriptions[0];
		}

		//Create printouts array if was not set
		if( (count($this->printouts) == 0) && ($this->page !== null) ) {
			//Everything
			$propList = Utils::getPageProperties( $this->page );
			$propList = array_keys($propList);
			$this->printouts = $propList;
		}

		//Add printouts to query
		foreach ( $this->printouts as $printout ) {
			$printout = Utils::getPropertyID( $printout ); // TODO: remove need
			if ( isset ( $this->printoutLabels[$printout] ) ) {
				$printLabel = $this->printoutLabels[$printout];
			} else {
				$printLabel = Utils::getPropertyLabel( $printout );
			}
			$printProp = \SMWPropertyValue::makeProperty( $printout );
			$queryDescription->addPrintRequest( new \SMWPrintRequest( \SMWPrintRequest::PRINT_PROP, $printLabel, $printProp ) );
		}

		return $queryDescription;
	}

	/**
	 * Executes the query. Unless already done, this is called by all
	 * methods that return results.
	 *
	 * @return $this
	 */
	protected function executeQuery() {
		$queryDescription = $this->buildQuery();
		$query = new \SMWQuery( $queryDescription );
		$query->setOffset( $this->offset );
		$query->setLimit( $this->limit );
		$this->result = $this->store->getQueryResult( $query );
		return $this;
	}

}
