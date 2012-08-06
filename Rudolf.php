<?php

set_include_path(get_include_path() . PATH_SEPARATOR . './easyrdf/lib/');

require_once('Zend/Http/Client.php');
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);

// Load the parsers and serialisers that we are going to use
$autoloader->autoload('EasyRdf_Parser_RdfXml');

class Rudolf{

	protected $_httpClient;

	public function __construct($client = NULL){
		if($client instanceof Zend_Http_client){
			$this->_httpClient = $client;
		} else {
			$this->_httpClient = $this->getHttpClient();
		}
	}

	public function main(){
		$programmeArray = Array();

		$pids = $this->getUpcomingSciFiProgrammes();
		foreach($pids as $pid){
			$programmeArray[$pid] = $this->getProgrammeDetails($pid);
		}

		return $programmeArray; 

	}

	protected function getHttpClient($uri = NULL){
		$config = array(
			'adapter'    => 'Zend_Http_Client_Adapter_Proxy',
			'proxy_host' => 'www-cache.reith.bbc.co.uk',
			'proxy_port' => 80,
		);

		return new Zend_Http_Client($uri, $config);
	}

	protected function getUpcomingSciFiProgrammes(){
		$pids = Array();

		$jsonuri = "http://www.bbc.co.uk/tv/programmes/genres/drama/scifiandfantasy/schedules/upcoming.json";
		$this->_httpClient->setUri($jsonuri);
			
		$response = $this->_httpClient->request('GET');
		if ($response->isSuccessful()){
			$programmes = Zend_Json::decode($response->getBody());
		} else {
			throw new Exception("Couldn't get programmes");
		}

		foreach($programmes["broadcasts"] as $broadcast){
			$pids[] = $broadcast['programme']['pid'];
		}

		return $pids;
		
	}

	protected function getProgrammeDetails($pid){
		EasyRdf_Http::setDefaultHttpClient($this->_httpClient);
		$details = Array();

		EasyRdf_Namespace::set('po', 'http://purl.org/ontology/po/');
		EasyRdf_Namespace::set('dc', 'http://purl.org/dc/elements/1.1/');
		$docuri = "http://www.bbc.co.uk/programmes/$pid.rdf";

		$graph = new EasyRdf_Graph($docuri);
		$graph->load();

		$resource = $graph->resource("/programmes/$pid#programme");
		$details['title'] = $resource->get('dc:title');
		$details['synopsis'] = $resource->get('po:long_synopsis');

		return $details;
	}
}
/*

$rudolf = new Rudolf();
$programmeDetails = $rudolf->main();

foreach($programmeDetails as $programme){
	print $programme['title'] . "\n";
	print $programme['synopsis'] . "\n";
	print "\n\n";
}

*/
