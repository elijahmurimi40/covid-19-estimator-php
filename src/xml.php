<?php
header("Content-Type: application/xml");
include_once 'estimator.php';
$results = showResults();

echo generateXML($results);  

function generateXml($data) {
    $dataR = $data['data'];
    unset($dataR['region']);
    
    $xmlDoc = new DOMDocument();

    $root = $xmlDoc->appendChild($xmlDoc->createElement('results'));
    $tabData = $root->appendChild($xmlDoc->createElement('data'));
    $tabRegion = $tabData->appendChild($xmlDoc->createElement('region'));
    $tabImpact = $root->appendChild($xmlDoc->createElement('impact'));
    $tabSevereImpact = $root->appendChild($xmlDoc->createElement('severeImpact'));

    // data
    foreach($dataR as $key => $val) :
        $tabData->appendChild($xmlDoc->createElement($key, $val));
    endforeach;

    // region
    foreach($data['data']['region'] as $key => $val) :
        $tabRegion->appendChild($xmlDoc->createElement($key, $val));
    endforeach;

    // Impact
    foreach($data['impact'] as $key => $val) :
        $tabImpact->appendChild($xmlDoc->createElement($key, $val));
    endforeach; 
    
    // SevereImpact
    foreach($data['severeImpact'] as $key => $val) :
        $tabSevereImpact->appendChild($xmlDoc->createElement($key, $val));
    endforeach;

    return $xmlDoc->saveXML($root);
}