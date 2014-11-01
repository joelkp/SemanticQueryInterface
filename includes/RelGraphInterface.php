<?php

namespace SQI;

/**
 * Class that can be used to build page relationship graphs based on
 * semantic properties. It implements recursive querying that follows a
 * specified property (relation) between pages.
 *
 * The result is returned in an array format that makes it easy to
 * further handle and/or print the information about the pages, including
 * their relation one to one another. For example:
 * - It is very easy to format and print a tree that orders the pages
 *   with the start page of the search as the root. Branches can be
 *   handled differently based on whether or not they lead to the target
 *   page of the search.
 *
 * @package SemanticQueryInterface
 */
class RelGraphInterface {

	/** @var  QueryInterface */
	protected $queryInterface;
	/** @var  Array */
	protected $config;
	/** @var  int */
	protected $maxQueries;
	/** @var  int */
	protected $queryCount;
	/** @var  Array */
	protected $ingoingProperties;
	/** @var  Array */
	protected $outgoingProperties;

	/**
	 * Constructor. A configuration array can be passed to override
	 * options; the array keys include those for the QueryInterface
	 * class, and the configuration is passed on when using
	 * QueryInterface internally. The defaults are:
	 * - 'fetch_default_properties' (false) - This overrides the
	 *   default value for QueryInterface.
	 * - Except for the values overridden, the same as in
	 *   QueryInterface.
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
		$this->config = array(
			// Don't fetch default semantic properties (like
			// category) for every subject page; this
			// overrides the QueryInterface default
			'fetch_default_properties' => false,
		);
		if( $config !== null ) {
			$this->config = array_merge( $this->config, $config );
		}
		$this->queryInterface = new QueryInterface( $this->config );
		$this->maxQueries = 1000;
		$this->queryCount = 0;
		$this->ingoingProperties = array();
		$this->outgoingProperties = array();
	}

	/**
	 * Set the query results limit for each individual query performed.
	 *
	 * The default is the same as the default query results limit of
	 * the QueryInterface class.
	 *
	 * @param $limit
	 */
	public function maxQueryResults( $limit ) {
		$this->queryInterface->limit($limit);
	}

	/**
	 * Set the maximum number of queries to perform. As long as
	 * this limit is not exceeded, each query result which is an
	 * unvisited page will result in a new query.
	 *
	 * The default is 1000.
	 *
	 * @param $limit
	 */
	public function maxQueries( $limit ) {
		$this->maxQueries = $limit;
	}

	/**
	 * Return the number of queries performed.
	 *
	 * @return int
	 */
	public function queryCount() {
		return $this->queryCount;
	}

/*
	private $qInterface;
	private $searchNext;
	private $pagesFound;
	private $result;

	private function __construct( $startPages, $linkProperty ) {
		$qIConfig = array( 'fetch_default_properties' => false );
		$this->qInterface = new QueryInterface( $qIConfig );
		$this->searchNext = $startPages;
		$this->pagesFound = array();
		$this->result = array();
	}

	public static function searchOutgoing( $startPages, $linkProperty,
			$printoutProperties ) {
		if( is_string( $startPages ) ) {
			$startPages = array( $startPages );
			if( !is_string( $linkProperty ) ) {
				return $startPages;
			}
		} else if( !is_array( $startPages ) ) {
			return array();
		}
		$qI = new RelGraphInterface( $startPages, $linkProperty );
	}

	private function depthFirstOutgoing( $pageTitle ) {
		this->$pagesFound[] = $pageTitle;
		$qResult = this->$qInterface->from( $pageTitle )->toArray();
		
	}
*/

	public static function search( $startPage, $linkProperty, /*$linkIsForwards = true,*/ $printoutProperties ) {
		$resultArray = array();
		if( gettype($startPage) != string ||
			gettype($linkProperty) != string) {
			return $resultArray;
		}
		$searchNext = array($startPage);
		$qi = new QueryInterface($qiConfig);
		$pagesFound = array();
		while( count( $searchNext ) ) {
			$currentFound = array();
			foreach( $searchNext as $titleString ) {
				echo $titleString . " contains:\n\n";
				$qResult = $qi->from($titleString)->toArray();
				if( count( $qResult ) <= 1 ) {
					break 2;
				}
				foreach( $qResult as $qRItemArray ) {
					if( !is_array( $qRItemArray ) ) {
						continue;
					}
					if( !isset( $qRItemArray[$linkProperty] ) ) {
						break;
					}
					$qRItem = $qRItemArray[$linkProperty];
					foreach( $qRItem as $linkedPage ) {
						$currentFound[] = $linkedPage;
						echo "* $linkedPage\n";
					}
				}
			}
			$searchNext = array_diff( $lastFound, $pagesFound );
			$pagesFound = array_unique( array_merge( $pagesFound, $lastFound ) );

			echo "All pages found so far:\n\n";
			foreach( $pagesFound as $pageTitle ) {
				echo "* $pageTitle\n";
			}
		}
		return $resultArray();
	}
}
