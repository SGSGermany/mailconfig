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

define('SERVER_PROTOCOL', !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');

function e(string $string, int $flags = 0): string {
    return htmlspecialchars($string, ENT_HTML5 | ENT_SUBSTITUTE | $flags, 'UTF-8');
}

if (file_exists('/etc/mailconfig/config.inc.php')) {
    require('/etc/mailconfig/config.inc.php');
}

$webmail = $config['webmail'] ?? '';

$imapHost = $config['imap_host'] ?? '';
$imapPort = (isset($config['imap_port']) && ($config['imap_port'] !== '')) ? (int) $config['imap_port'] : 143;
$imapSSL = in_array(strtoupper($config['imap_ssl'] ?? ''), [ 'SSL', 'STARTTLS' ], true) ? strtoupper($config['imap_ssl']) : '';
$imapNTLM = in_array(strtolower($config['imap_ntlm'] ?? ''), [ 'yes', 'true', 'on', '1' ], true);

$smtpHost = $config['smtp_host'] ?? '';
$smtpPort = (isset($config['smtp_port']) && ($config['smtp_port'] !== '')) ? (int) $config['smtp_port'] : 25;
$smtpSSL = in_array(strtoupper($config['smtp_ssl'] ?? ''), [ 'SSL', 'STARTTLS' ], true) ? strtoupper($config['smtp_ssl']) : '';
$smtpNTLM = in_array(strtolower($config['smtp_ntlm'] ?? ''), [ 'yes', 'true', 'on', '1' ], true);

$serverId = $config['server_id'] ?? SERVER_NAME ?? $imapHost ?? $smtpHost ?? 'mailconfig';
$serverName = $config['server_name'] ?? '';

header(SERVER_PROTOCOL . ' 200 OK');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width">
        <title>E-Mail Client Configuration</title>
        <style type="text/css">*{box-sizing:border-box;border:0;margin:0;padding:0}body,html{height:100%;background:#fff;font-family:Lucida Grande,Geneva,Verdana,sans-serif}#header,#main{width:100%;padding:0 .5em}#header{background:#428bca}.container{max-width:48em;margin:0 auto;padding:1em}#main .container{margin:4em auto;background:#f3f3f3}h1,h1+p{color:#fff}h1{font-size:2rem;font-weight:bold;padding:3rem 0}h1+p{font-style:italic;margin-top:-1.5rem;padding-bottom:3rem}h2{padding:0 0 .5em;margin:0 0 1em;border-bottom:1px solid #ccc;font-size:1.5rem}a{color: #428bca;text-decoration: none;transition: color .2s ease-in}a:hover{color: #444}.align-center{text-align:center}.content{padding:.75em 1em;background:#fff}.content p{margin-bottom:1em;line-height:150%}.content p:last-child{margin-bottom:0;}.content form{display:flex;flex-wrap:wrap;justify-content:space-evenly;gap:0.5em 1em}input{padding:0.5em 1em;outline: 0 none;border:1px solid #ccc;border-radius:5px;transition:none .2s ease-in;transition-property:border-color,box-shadow}input:focus,input[type="submit"]:hover{border-color:#428bca;box-shadow:0 0 8px #428bca;}input[type="submit"]{background:#428bca;border-color:#428bca;color:#fff;cursor: pointer}.content .table-wrapper{overflow-x:auto}table{border-spacing:0;min-width:100%}th{font-weight:bold;text-align:center;border-bottom:1px solid #ccc}td,th{padding:.25em .5em;background:#fff}td:first-child{background:#f3f3f3}td:not(:first-child){min-width:9em}td:not(:last-child),th:not(:last-child){border-right:1px solid #ccc}tbody tr:last-child td:first-child{border-bottom-left-radius:5px}code{font-family:Lucida Console,Monaco,Courier New,monospace;font-size:.9em}#main .container,.content,#main{border:1px solid #ccc;border-radius:5px}</style>
    </head>
    <body<?php if ($serverId !== '') { echo " data-server-id=\"" . e($serverId, ENT_COMPAT) . "\""; } ?>>
        <div id="header">
            <div class="container">
                <h1>E-Mail Client Configuration</h1>
                <?php if ($serverName !== '') { ?>
                    <p><?php echo e($serverName); ?></p>
                <?php } ?>
            </div>
        </div>

        <div id="main">
            <?php if ($webmail !== '') { ?>
                <div class="container">
                    <h2>Webmail</h2>
                    <div class="content">
                        <p>You can find the webmailer at the following URL:</p>
                        <p class="align-center"><a href="<?php echo e($webmail, ENT_QUOTES); ?>"><?php echo e($webmail); ?></a></p>
                    </div>
                </div>
            <?php } ?>

            <?php if ($imapHost !== '') { ?>
                <div class="container">
                    <h2>Incoming Server (IMAP)</h2>
                    <div class="content">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Protocol</td>
                                        <td>IMAP</td>
                                    </tr>
                                    <tr>
                                        <td>Server address</td>
                                        <td><code><?php echo e($imapHost); ?></code></td>
                                    </tr>
                                    <tr>
                                        <td>Server port</td>
                                        <td><code><?php echo $imapPort; ?></code></td>
                                    </tr>
                                    <tr>
                                        <td>Encryption</td>
                                        <?php
                                            if ($imapSSL !== '') {
                                                echo "<td>Yes (" . $imapSSL . ")</td>\n";
                                            } else {
                                                echo "<td>No</td>\n";
                                            }
                                        ?>
                                    </tr>
                                    <tr>
                                        <td>Authentication</td>
                                        <?php
                                            if ($imapNTLM) {
                                                echo "<td>NTLM / SPA</td>\n";
                                            } else {
                                                echo "<td>Plain / Cleartext</td>\n";
                                            }
                                        ?>
                                    </tr>
                                    <tr>
                                        <td>Login name</td>
                                        <td>Your full email address</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if ($smtpHost !== '') { ?>
                <div class="container">
                    <h2>Outgoing Server (SMTP)</h2>
                    <div class="content">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Protocol</td>
                                        <td>SMTP</td>
                                    </tr>
                                    <tr>
                                        <td>Server address</td>
                                        <td><code><?php echo e($smtpHost); ?></code></td>
                                    </tr>
                                    <tr>
                                        <td>Server port</td>
                                        <td><code><?php echo $smtpPort; ?></code></td>
                                    </tr>
                                    <tr>
                                        <td>Encryption</td>
                                        <?php
                                            if ($imapSSL !== '') {
                                                echo "<td>Yes (" . $imapSSL . ")</td>\n";
                                            } else {
                                                echo "<td>No</td>\n";
                                            }
                                        ?>
                                    </tr>
                                    <tr>
                                        <td>Authentication</td>
                                        <?php
                                            if ($smtpNTLM) {
                                                echo "<td>NTLM / SPA</td>\n";
                                            } else {
                                                echo "<td>Plain / Cleartext</td>\n";
                                            }
                                        ?>
                                    </tr>
                                    <tr>
                                        <td>Login name</td>
                                        <td>Your full email address</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="container">
                <h2>Apple Configuration Profile</h2>
                <div class="content">
                    <p>Enter your full email address and click the download button to setup your Apple mobile device.</p>
                    <form method="GET" action="email.mobileconfig">
                        <input type="email" name="email" value="" minlength="3" required="required"
                            placeholder="Email address" style="min-width:20em;flex-grow:2" />
                        <input type="submit" value="Download" style="min-width:10em;flex-grow:1" />
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
