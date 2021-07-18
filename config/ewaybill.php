<?php

return [
	"urls" => [
        "ACCESSTOKEN" => [
            "test" => "http://testapi.taxprogsp.co.in/ewaybillapi/dec/v1.03/authenticate",
            "production" => "https://api.taxprogsp.co.in/v1.03/dec/authenticate"
        ],
        "EWAYBILL" => [
            "test" => "http://testapi.taxprogsp.co.in/ewaybillapi/dec/v1.03/ewayapi",
            "production" => "https://api.taxprogsp.co.in/v1.03/dec/ewayapi"
        ],
        "PRINTEWB" => [
            "test" => "http://testapi.taxprogsp.co.in/aspapi/v1.0"
        ]
    ],
    "actions" => [
        "ACCESSTOKEN" => "ACCESSTOKEN",
        "GENEWAYBILL" => "GENEWAYBILL",
        "PRINTEWB" => "PRINTEWB",
        "CANEWB" => "CANEWB"
    ]
];