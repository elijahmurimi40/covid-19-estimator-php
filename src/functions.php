<?php
require_once 'constants.php';
class MyFunctions {
    // show error code and message
    public static function throwError($code, $message) {
        header('content-type: application/json');
        $errorMessage = json_encode(['error' => ['status' => $code, 'message' => $message]]);
        echo $errorMessage;
        die();
    }
    
    // check request method
    public static function checkRequestMethod($requestMethod) {
        if ($requestMethod == 'POST') :
            $jsonData = file_get_contents('php://input');
            return $jsonData;
        else :
            self::throwError(REQUEST_METHOD_NOT_VALID, 'Only POST requests are allowed');
        endif;
    }

    // Make sure Content-Type is application/json
    public static function checkContentType($data) {
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') :
            self::throwError(
                REQUEST_CONTENT_TYPE_NOT_VALID, 
                'Request content type is not valid. JSON REQUIRED'
            );
        else :
            return $data;
        endif;
    }

    // convertToDays 
    public static function convertToDays($periodType, $timeToElapse) {
        $days = 0;
        if ($periodType == 'days') :
            $days = $timeToElapse;
        elseif ($periodType == 'weeks') :
            $days = 7 * $timeToElapse;
        elseif ($periodType == 'months') :
            $days = 30 * $timeToElapse;
        else :
            MyFunctions::throwError(
                PERIOD_TYPE_NOT_VALID, 
                'Enter a Valid period type. i.e: days or weeks or months'
            );
        endif;
        return $days;
    }

    // write log messges
    public static function writeLogMessages($time) {
        $timeTaken = ($time - $_SERVER['REQUEST_TIME_FLOAT']);
        $time2 = microtime(true);
        
        $actualLink = (
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
            . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $message = time()."\t\t".$actualLink."\t\t".date("H:i:s",$timeTaken);
        file_put_contents('logs.txt', 
            $message."\n", 
            FILE_APPEND | LOCK_EX);
    }

    // challenge one
    // estimate of currently infected people impact
    public static function currentlyInfectedImpact($reportedCases) {
        return $reportedCases * 10;
    }

    // estimate of currently infected people severe impact
    public static function currentlyInfectedSevereImpact($reportedCases) {
        return $reportedCases * 50;
    }

    // estimate of infected people in a given number days impact/ severe impact
    public static function infectionsByRequestedTime($currentlyInfected, $periodType, $timeToElapse) {
        $factor = floor(self::convertToDays($periodType, $timeToElapse) / 3);
        return $currentlyInfected * floor(2 ** $factor);
    }

    // challenge two
    // estimate of severe positive cases that will require hospitalization to recover
    public static function severeCasesByRequestedTime($infectionsByRequestedTime) {
        return floor(15 / 100 * $infectionsByRequestedTime);
    }

    // estimate of the number of available beds
    public static function hospitalBedsByRequestedTime($totalHospitalBeds, $severeCasesByRequestedTime) {
        $availableBeds = ((35/ 100) * $totalHospitalBeds) - $severeCasesByRequestedTime;

        if ($availableBeds < 0) :
            return ceil($availableBeds);
        else :
            return floor($availableBeds);
        endif;
    }

    // challenge three
    // estimate of number of severe positive cases that will require ICU care
    public static function casesForICUByRequestedTime($infectionsByRequestedTime) {
        return floor((5/ 100) * $infectionsByRequestedTime);
    }

    // estimated number of severe positive cases that will require ventilators
    public static function casesForVentilatorsByRequestedTime($infectionsByRequestedTime) {
        return floor((2/ 100) * $infectionsByRequestedTime);
    }

    // estimate how much money the economy is likely to lose over the said period
    public static function dollarsInFlight($infectionsByRequestedTime, $periodType, $timeToElapse, 
        $avgDailyIncomeInUSD, $avgDailyIncomePopulation) {
            $days = self::convertToDays($periodType, $timeToElapse) ;
            return floor(
                ($infectionsByRequestedTime * $avgDailyIncomeInUSD * $avgDailyIncomePopulation) / $days);
    }

}