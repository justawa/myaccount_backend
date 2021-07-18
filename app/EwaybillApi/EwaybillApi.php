<?php

namespace App\EwaybillApi;

use Config;

class EwaybillApi
{
    private static $aspid = '1631791419';
    private static $password = 'kohli@12345';
    
    private static $gstin = '05AAACG1625Q1ZK';
    private static $username = '05AAACG1625Q1ZK';
    private static $ewbpwd = 'abc123@@';

    public static function getAspid()
    {
        return static::$aspid;
    }

    public static function getPassword()
    {
        return static::$password;
    }

    public static function getGstin()
    {
        return static::$gstin;
    }

    public static function getUsername()
    {
        return static::$username;
    }

    public static function getEwbpwd()
    {
        return static::$ewbpwd;
    }

    public static function getAuthToken($endPoint, $gstin, $username, $ewbpwd)
    {
        $client = new \GuzzleHttp\Client();
        $action = Config::get('ewaybill.actions.ACCESSTOKEN', 'ACCESSTOKEN');

        $response = $client->request('GET', $endPoint, [
            'query' => [
                'action' => $action, 
                'aspid' => self::$aspid,
                'password' => self::$password,
                'gstin' => $gstin,
                'username' => $username,
                'ewbpwd' => $ewbpwd
            ]
        ]);

        $statusCode = $response->getStatusCode();
        
        if($statusCode == '200'){
            $content = $response->getBody();
            $content = json_decode($content, true);
        }

        if(isset($content)){
            return $content['authtoken'];
        }

        return null;
    }

    public static function generateEwayBill($endPoint, $gstin, $username, $ewbpwd, $authToken, $dataArray)
    {
        $client = new \GuzzleHttp\Client();
        $action = Config::get('ewaybill.actions.GENEWAYBILL', 'GENEWAYBILL');

        $response = $client->request('POST', $endPoint, [
            'query' => [
                'action' => $action, 
                'aspid' => self::$aspid,
                'password' => self::$password,
                'gstin' => $gstin,
                'username' => $username,
                'ewbpwd' => $ewbpwd,
                'authtoken' => $authToken
            ],
            'json' => $dataArray
        ]);

        return $response;

    }

    public static function printEwayBill($endPoint, $gstin, $username, $ewbpwd, $authToken)
    {
        $client = new \GuzzleHttp\Client();
        $action = Config::get('ewaybill.actions.PRINTEWB', 'PRINTEWB');

        $response = $client->request('POST', $endPoint, [
            'query' => [
                'action' => $action, 
                'aspid' => self::$aspid,
                'password' => self::$password,
                'gstin' => $gstin,
                'username' => $username,
                'ewbpwd' => $ewbpwd,
                'authtoken' => $authToken
            ],
            'json' => $dataArray
        ]);

        return $response;
    }

    public static function cancelEwayBill($endPoint, $gstin, $username, $ewbpwd, $authToken, $dataArray)
    {
        $client = new \GuzzleHttp\Client();
        $action = Config::get('ewaybill.actions.CANEWB', 'CANEWB');

        try {
            $response = $client->request('POST', $endPoint, [
                'query' => [
                    'action' => $action, 
                    'aspid' => self::$aspid,
                    'password' => self::$password,
                    'gstin' => $gstin,
                    'username' => $username,
                    'ewbpwd' => $ewbpwd,
                    'authtoken' => $authToken
                ],
                'json' => $dataArray
            ]);
    
            return $response;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}