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
 * Mozilla ISPDB mail autoconfiguration procotol
 * <https://wiki.mozilla.org/Thunderbird:Autoconfiguration:ConfigFileFormat>
 *
 * Version 1.1
 */

define('SERVER_NAME', !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (gethostname() ?: null));
define('SERVER_PROTOCOL', !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');

function e(string $string, int $flags = 0): string {
    return htmlspecialchars($string, ENT_XML1 | ENT_SUBSTITUTE | $flags, 'UTF-8');
}

if (file_exists('/etc/mailconfig/config.inc.php')) {
    require('/etc/mailconfig/config.inc.php');
}

$mail = $domain = '';
if (!empty($_GET['emailaddress']) && (filter_var($_GET['emailaddress'], FILTER_VALIDATE_EMAIL) !== false)) {
    $mail = filter_var($_GET['emailaddress'], FILTER_SANITIZE_EMAIL);
    $domain = substr(strrchr($_GET['emailaddress'], '@'), 1);
}

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
$serverNameShort = $config['server_name_short'] ?? '';

header(SERVER_PROTOCOL . ' 200 OK');
header('Content-Type: application/xml');
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<clientConfig version="1.1">
    <emailProvider id="<?php echo e($serverId, ENT_COMPAT); ?>">
        <?php if ($domain !== '') { ?>
            <domain><?php echo e($domain); ?></domain>
        <?php } ?>
        <?php if ($serverName !== '') { ?>
            <displayName><?php echo e($serverName); ?></displayName>
        <?php } ?>
        <?php if ($serverNameShort !== '') { ?>
            <displayNameShort><?php echo e($serverNameShort); ?></displayNameShort>
        <?php } ?>
        <?php if ($imapHost !== '') { ?>
            <incomingServer type="imap">
                <hostname><?php echo e($imapHost); ?></hostname>
                <port><?php echo $imapPort; ?></port>
                <socketType><?php echo $imapSSL; ?></socketType>
                <authentication><?php echo $imapNTLM ? 'NTLM' : 'password-cleartext'; ?></authentication>
                <username><?php echo ($mail !== '') ? e($mail) : '%EMAILADDRESS%'; ?></username>
            </incomingServer>
        <?php } ?>
        <?php if ($smtpHost !== '') { ?>
            <outgoingServer type="smtp">
                <hostname><?php echo e($smtpHost); ?></hostname>
                <port><?php echo $smtpPort; ?></port>
                <socketType><?php echo $smtpSSL; ?></socketType>
                <authentication><?php echo $smtpNTLM ? 'NTLM' : 'password-cleartext'; ?></authentication>
                <username><?php echo ($mail !== '') ? e($mail) : '%EMAILADDRESS%'; ?></username>
            </outgoingServer>
        <?php } ?>
    </emailProvider>
</clientConfig>
