<?php

return [
	'gcm' => [
			'priority' => 'normal',
			'dry_run' => false,
			'apiKey' => 'AIzaSyAEojzJWjdYK8XT8qTchwAAuuRhdjN393s',
	],
	'fcm' => [
				'priority' => 'normal',
				'dry_run' => false,
				'apiKey' => 'AIzaSyAEojzJWjdYK8XT8qTchwAAuuRhdjN393s',
	],
	'apn' => [
			'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
			'passPhrase' => '1234', //Optional
			'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
			'dry_run' => true
	]
];