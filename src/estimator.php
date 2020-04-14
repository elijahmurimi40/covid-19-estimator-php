<?php
require_once 'functions.php';

function covid19ImpactEstimator($data)
{
  $name = $data["region"]["name"];
  $avgAge = $data["region"]["avgAge"];
  $avgDailyIncomeInUSD = $data["region"]["avgDailyIncomeInUSD"];
  $avgDailyIncomePopulation = $data["region"]["avgDailyIncomePopulation"];
  $periodType = $data["periodType"];
  $timeToElapse = $data["timeToElapse"];
  $reportedCases = $data["reportedCases"];
  $population = $data["population"];
  $totalHospitalBeds = $data["totalHospitalBeds"];

  // challenge one
  // currently infected impact/ severe impact
  $currentlyInfectedI = MyFunctions::currentlyInfectedImpact($reportedCases);
  $currentlyInfectedS = MyFunctions::currentlyInfectedSevereImpact($reportedCases);

  // estimate of infected people 30 days impact/ severe impact
  $infectionsByRequestedTimeI = 
    MyFunctions::infectionsByRequestedTime($currentlyInfectedI, $periodType, $timeToElapse);
  $infectionsByRequestedTimeS = 
    MyFunctions::infectionsByRequestedTime($currentlyInfectedS, $periodType, $timeToElapse);

  // challenge two
  // severe positive cases that will require hospitalization to recover
  $severeCasesByRequestedTimeI = MyFunctions::severeCasesByRequestedTime($infectionsByRequestedTimeI);
  $severeCasesByRequestedTimeS = MyFunctions::severeCasesByRequestedTime($infectionsByRequestedTimeS);

  $hospitalBedsByRequestedTimeI = 
    MyFunctions::hospitalBedsByRequestedTime($totalHospitalBeds, $severeCasesByRequestedTimeI);
  $hospitalBedsByRequestedTimeS = 
    MyFunctions::hospitalBedsByRequestedTime($totalHospitalBeds, $severeCasesByRequestedTimeS);

  // challenge three
  // number of severe positive cases that will require ICU care
  $casesForICUByRequestedTimeI = MyFunctions::casesForICUByRequestedTime($infectionsByRequestedTimeI);
  $casesForICUByRequestedTimeS = MyFunctions::casesForICUByRequestedTime($infectionsByRequestedTimeS);

  // estimated number of severe positive cases that will require ventilators
  $casesForVentilatorsByRequestedTimeI = 
    MyFunctions::casesForVentilatorsByRequestedTime($infectionsByRequestedTimeI);
  $casesForVentilatorsByRequestedTimeS = 
    MyFunctions::casesForVentilatorsByRequestedTime($infectionsByRequestedTimeS);

  // estimate how much money the economy is likely to lose over the said period
  $dollarsInFlightI = MyFunctions::dollarsInFlight(
    $infectionsByRequestedTimeI, $periodType, $timeToElapse, $avgDailyIncomeInUSD, $avgDailyIncomePopulation);
  $dollarsInFlightS = MyFunctions::dollarsInFlight(
    $infectionsByRequestedTimeS, $periodType, $timeToElapse, $avgDailyIncomeInUSD, $avgDailyIncomePopulation);

  $impact = array(
    'currentlyInfected' => $currentlyInfectedI,
    'infectionsByRequestedTime' => $infectionsByRequestedTimeI,
    'severeCasesByRequestedTime' => $severeCasesByRequestedTimeI,
    'hospitalBedsByRequestedTime' => $hospitalBedsByRequestedTimeI,
    'casesForICUByRequestedTime' => $casesForICUByRequestedTimeI,
    'casesForVentilatorsByRequestedTime' => $casesForVentilatorsByRequestedTimeI,
    'dollarsInFlight' => $dollarsInFlightI
  );

  $severeImpact = array(
    'currentlyInfected' => $currentlyInfectedS,
    'infectionsByRequestedTime' => $infectionsByRequestedTimeS,
    'severeCasesByRequestedTime' => $severeCasesByRequestedTimeS,
    'hospitalBedsByRequestedTime' => $hospitalBedsByRequestedTimeS,
    'casesForICUByRequestedTime' => $casesForICUByRequestedTimeS,
    'casesForVentilatorsByRequestedTime' => $casesForVentilatorsByRequestedTimeS,
    'dollarsInFlight' => $dollarsInFlightS
  );

  $data = array(
    'data' => $data,
    'impact' => $impact,
    'severeImpact' => $severeImpact
  );
  return $data;
}

function showResults() {
  $jsonData = MyFunctions::checkRequestMethod($_SERVER['REQUEST_METHOD']);
  if (!empty($jsonData)) :
    $checkedJsonData = MyFunctions::checkContentType($jsonData);
    $decodedData = json_decode($checkedJsonData, true);
    $finalData = covid19ImpactEstimator($decodedData); 
    return $finalData;
  else :
    MyFunctions::throwError(REQUEST_CONTENT_TYPE_NOT_VALID, 
    'Request content type is not valid. JSON REQUIRED');
  endif;
}