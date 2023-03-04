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
 * [MS-OXDSCLI]: Autodiscover Publishing and Lookup Protocol
 * <https://learn.microsoft.com/en-us/openspecs/exchange_server_protocols/ms-oxdscli/78530279-d042-4eb0-a1f4-03b18143cd19>
 *
 * Version 10.0, released 2012-10-08
 * See <https://interoperability.blob.core.windows.net/files/MS-OXDSCLI/%5bMS-OXDSCLI%5d-121008.pdf> for details
 */

define('SERVER_NAME', !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (gethostname() ?: null));
define('SERVER_PROTOCOL', !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');

function e(string $string, int $flags = 0): string {
    return htmlspecialchars($string, ENT_XML1 | ENT_SUBSTITUTE | $flags, 'UTF-8');
}

if (file_exists('/etc/mailconfig/config.inc.php')) {
    require('/etc/mailconfig/config.inc.php');
}

header(SERVER_PROTOCOL . ' 200 OK');

preg_match('#<EMailAddress>(.*?)</EMailAddress>#', file_get_contents('php://input'), $matches);

$mail = $domain = $error = '';
if (!empty($matches[1])) {
    if (filter_var($matches[1], FILTER_VALIDATE_EMAIL) === false) {
        header(SERVER_PROTOCOL . ' 400 Bad Request');
        $error = 'Invalid request';
    } else {
        $mail = filter_var($matches[1], FILTER_SANITIZE_EMAIL);
        $domain = substr(strrchr($mail, '@'), 1);
    }
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

header('Content-Type: application/xml');
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
    <Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a">
        <?php if ($error !== '') { ?>
            <Error Time="<?php echo date('H:i:s.u'); ?>" Id="<?php printf('%u', crc32($serverId)); ?>">
                <ErrorCode>600</ErrorCode>
                <Message><?php echo e($error); ?></Message>
                <DebugData />
            </Error>
        <?php } else { ?>
            <?php if ($serverName !== '') { ?>
                <User>
                    <DisplayName><?php echo e($serverName); ?></DisplayName>
                </User>
            <?php } ?>
            <Account>
                <AccountType>email</AccountType>
                <Action>settings</Action>
                <?php if ($imapHost !== '') { ?>
                    <Protocol>
                        <Type>IMAP</Type>
                        <Server><?php echo e($imapHost); ?></Server>
                        <Port><?php echo $imapPort; ?></Port>
                        <?php
                            if ($imapSSL === 'STARTTLS') {
                                echo "<Encryption>TLS</Encryption>\n";
                            } elseif ($imapSSL === 'SSL') {
                                echo "<SSL>on</SSL>\n";
                            } else {
                                echo "<SSL>off</SSL>\n";
                            }
                        ?>
                        <AuthRequired>on</AuthRequired>
                        <?php if ($mail !== '') { ?>
                            <LoginName><?php echo e($mail); ?></LoginName>
                        <?php } ?>
                        <SPA><?php echo $imapNTLM ? 'on' : 'off'; ?></SPA>
                    </Protocol>
                <?php } ?>
                <?php if ($smtpHost !== '') { ?>
                    <Protocol>
                        <Type>SMTP</Type>
                        <Server><?php echo e($smtpHost); ?></Server>
                        <Port><?php echo $smtpPort; ?></Port>
                        <?php
                            if ($smtpSSL === 'STARTTLS') {
                                echo "<Encryption>TLS</Encryption>\n";
                            } elseif ($smtpSSL === 'SSL') {
                                echo "<SSL>on</SSL>\n";
                            } else {
                                echo "<SSL>off</SSL>\n";
                            }
                        ?>
                        <AuthRequired>on</AuthRequired>
                        <?php if ($mail !== '') { ?>
                            <LoginName><?php echo e($mail); ?></LoginName>
                        <?php } ?>
                        <SPA><?php echo $smtpNTLM ? 'on' : 'off'; ?></SPA>
                    </Protocol>
                <?php } ?>
            </Account>
        <?php } ?>
    </Response>
</Autodiscover>
