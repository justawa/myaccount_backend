<?php

return [
	"urls" => [
        "ACCESSTOKEN" => [
            "test" => "http://gstsandbox.charteredinfo.com/ewaybillapi/dec/v1.03/auth",
            //"production" => "http://api.taxprogsp.co.in/v1.03/dec/authenticate"
            "production" => "https://einvapi.charteredinfo.com/v1.03/dec/auth"
        ],
        "GENEWAYBILL" => [
            "test" => "http://gstsandbox.charteredinfo.com/ewaybillapi/dec/v1.03/ewayapi",
            //"production" => "http://api.taxprogsp.co.in/v1.03/dec/ewayapi"
            "production" => "https://einvapi.charteredinfo.com/v1.03/dec/ewayapi"
        ],
        "PRINTEWB" => [
            "test" => "http://testapi.taxprogsp.co.in/aspapi/v1.0"
        ],
        "CANEWB" => [
            "production" => "https://einvapi.charteredinfo.com/v1.03/dec/ewayapi"
        ]
    ],
    "actions" => [
        "ACCESSTOKEN" => "ACCESSTOKEN",
        "GENEWAYBILL" => "GENEWAYBILL",
        "PRINTEWB" => "PRINTEWB",
        "CANEWB" => "CANEWB"
    ]
    
];