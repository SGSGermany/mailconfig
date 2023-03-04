<?php
/**
 * MailConfig
 * A php-fpm container running an E-Mail Client Configuration service.
 *
 * Copyright (c) 2023  SGS Serious Gaming & Simulations GmbH
 *
 * This work is licensed under the terms of the MIT license.
 * For a copy, see LICENSE file or <https://opensource.org/licenses/MIT>.
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

/**
 * Microsoft Autodiscover v2 
 *
 * This is undocumented Microsoft bullcrap that simply isn't working
 * No autodiscovery for Office 2019, Office 365 and mobile, please send Microsoft your regards
 * Fuck you for breaking previously working Office with 3rd party mailservers Microsoft :middle_finger:
 */

define('SERVER_PROTOCOL', !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');

$responseData = [];
switch (strtolower($_GET['Protocol'] ?? '')) {
    case 'autodiscoverv1':
        header(SERVER_PROTOCOL . ' 200 OK');
        $responseData = [
            'Protocol' => 'AutodiscoverV1',
            'Url' => 'https://' . $_SERVER['HTTP_HOST'] . '/Autodiscover/Autodiscover.xml',
        ];
        break;

    default:
        header(SERVER_PROTOCOL . ' 400 Bad Request');
        $responseData = [
            'ErrorCode' => 'InvalidProtocol',
            'ErrorMessage' => 'The given protocol value "' . $protocol . '" is invalid. Supported values are "AutodiscoverV1"',
        ];
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($responseData);
