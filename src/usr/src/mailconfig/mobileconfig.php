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
 * Apple Configuration Profile Reference
 * <https://developer.apple.com/business/documentation/Configuration-Profile-Reference.pdf>
 *
 * Version 2019-05-03
 */

define('SERVER_PROTOCOL', !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');

function e(string $string, int $flags = 0): string {
    return htmlspecialchars($string, ENT_XML1 | ENT_SUBSTITUTE | $flags, 'UTF-8');
}

function uuidgen() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

if (file_exists('/etc/mailconfig/config.inc.php')) {
    require('/etc/mailconfig/config.inc.php');
}

header(SERVER_PROTOCOL . ' 200 OK');

$mail = $domain = '';
if (!empty($_GET['email']) && (filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) !== false)) {
    $mail = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
    $domain = substr(strrchr($mail, '@'), 1);
} else {
    header(SERVER_PROTOCOL . ' 400 Bad Request');
    exit(0);
}

$imapHost = $config['imap_host'] ?? '';
$imapPort = (isset($config['imap_port']) && ($config['imap_port'] !== '')) ? (int) $config['imap_port'] : 143;
$imapSSL = in_array(strtoupper($config['imap_ssl'] ?? ''), [ 'SSL', 'STARTTLS' ], true) ? strtoupper($config['imap_ssl']) : '';
$imapNTLM = in_array(strtolower($config['imap_ntlm'] ?? ''), [ 'yes', 'true', 'on', '1' ], true);

$smtpHost = $config['smtp_host'] ?? '';
$smtpPort = (isset($config['smtp_port']) && ($config['smtp_port'] !== '')) ? (int) $config['smtp_port'] : 25;
$smtpSSL = in_array(strtoupper($config['smtp_ssl'] ?? ''), [ 'SSL', 'STARTTLS' ], true) ? strtoupper($config['smtp_ssl']) : '';
$smtpNTLM = in_array(strtolower($config['smtp_ntlm'] ?? ''), [ 'yes', 'true', 'on', '1' ], true);

$serverName = $config['server_name'] ?? '';

$appleIdentifier = $config['apple_identifier'] ?? implode('.', array_reverse(explode('.', $domain)));
$appleUUID = $config['apple_uuid'] ?? uuidgen();
$appleMailUUID = $config['apple_mail_uuid'] ?? uuidgen();

header('Content-Type: application/x-apple-aspen-config; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $domain . '.mobileconfig"');
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
    <dict>
        <key>HasRemovalPasscode</key>
        <false/>
        <key>PayloadContent</key>
        <array>
            <dict>
                <key>EmailAccountDescription</key>
                <string><?php echo e($mail); ?></string>
                <key>EmailAccountName</key>
                <string><?php echo e($mail); ?></string>
                <key>EmailAccountType</key>
                <string>EmailTypeIMAP</string>
                <key>EmailAddress</key>
                <string><?php echo e($mail); ?></string>
                <?php if ($imapHost !== '') { ?>
                    <key>IncomingMailServerHostName</key>
                    <string><?php echo e($imapHost); ?></string>
                    <key>IncomingMailServerPortNumber</key>
                    <integer><?php echo $imapPort; ?></integer>
                    <key>IncomingMailServerUseSSL</key>
                    <?php echo ($imapSSL !== '') ? "<true/>\n" : "<false/>\n"; ?>
                    <key>IncomingMailServerUsername</key>
                    <string><?php echo e($mail); ?></string>
                    <key>IncomingMailServerAuthentication</key>
                    <string><?php echo $imapNTLM ? 'EmailAuthNTLM' : 'EmailAuthPassword'; ?></string>
                <?php } ?>
                <?php if ($smtpHost !== '') { ?>
                    <key>OutgoingMailServerHostName</key>
                    <string><?php echo e($smtpHost); ?></string>
                    <key>OutgoingMailServerPortNumber</key>
                    <integer><?php echo $smtpPort; ?></integer>
                    <key>OutgoingMailServerUseSSL</key>
                    <?php echo ($smtpSSL !== '') ? "<true/>\n" : "<false/>\n"; ?>
                    <key>OutgoingMailServerUsername</key>
                    <string><?php echo e($mail); ?></string>
                    <key>OutgoingMailServerAuthentication</key>
                    <string><?php echo $smtpNTLM ? 'EmailAuthNTLM' : 'EmailAuthPassword'; ?></string>
                    <key>OutgoingPasswordSameAsIncomingPassword</key>
                    <true/>
                <?php } ?>
                <key>SMIMEEnabled</key>
                <false/>
                <key>SMIMEEnablePerMessageSwitch</key>
                <false/>
                <key>SMIMEEnableEncryptionPerMessageSwitch</key>
                <false/>
                <key>disableMailRecentsSyncing</key>
                <false/>
                <?php if ($serverName !== '') { ?>
                    <key>PayloadDescription</key>
                    <string><?php echo e($serverName); ?></string>
                <?php } ?>
                <key>PayloadDisplayName</key>
                <string><?php echo e($mail); ?></string>
                <key>PayloadIdentifier</key>
                <string><?php echo e($appleIdentifier) . '.com.apple.mail.managed.' . e($appleMailUUID); ?></string>
                <key>PayloadType</key>
                <string>com.apple.mail.managed</string>
                <key>PayloadUUID</key>
                <string><?php echo e($appleMailUUID); ?></string>
                <key>PayloadVersion</key>
                <real>1</real>
            </dict>
        </array>
        <?php if ($serverName !== '') { ?>
            <key>PayloadDescription</key>
            <string><?php echo e($serverName); ?></string>
            <key>PayloadDisplayName</key>
            <string><?php echo e($serverName); ?></string>
        <?php } ?>
        <key>PayloadIdentifier</key>
        <string><?php echo e($appleIdentifier); ?></string>
        <key>PayloadOrganization</key>
        <string><?php echo e($domain); ?></string>
        <key>PayloadRemovalDisallowed</key>
        <false/>
        <key>PayloadType</key>
        <string>Configuration</string>
        <key>PayloadUUID</key>
        <string><?php echo e($appleUUID); ?></string>
        <key>PayloadVersion</key>
        <integer>2</integer>
    </dict>
</plist>
