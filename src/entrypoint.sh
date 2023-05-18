#!/bin/sh
# MailConfig
# A php-fpm container running an E-Mail Client Configuration service.
#
# Copyright (c) 2023  SGS Serious Gaming & Simulations GmbH
#
# This work is licensed under the terms of the MIT license.
# For a copy, see LICENSE file or <https://opensource.org/licenses/MIT>.
#
# SPDX-License-Identifier: MIT
# License-Filename: LICENSE

set -e

[ $# -gt 0 ] || set -- php-fpm "$@"
if [ "$1" == "php-fpm" ]; then
    # update MailConfig source files
    echo "Initializing MailConfig..."
    rsync -rlptog --delete --chown www-data:www-data \
        "/usr/src/mailconfig/" \
        "/var/www/html/"

    # update MailConfig config from env variables
    if [ -n "$MAILCONFIG_IMAP_HOST" ] || [ -n "$MAILCONFIG_SMTP_HOST" ]; then
        echo "Creating MailConfig config file..."
        {
            printf '<?php\n';
            [ -z "$MAILCONFIG_SERVER_ID" ] || printf "\$config['server_id'] = %s;\n" \
                "$(echo "$MAILCONFIG_SERVER_ID" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_SERVER_NAME" ] || printf "\$config['server_name'] = %s;\n" \
                "$(echo "$MAILCONFIG_SERVER_NAME" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_SERVER_NAME_SHORT" ] || printf "\$config['server_name_short'] = %s;\n" \
                "$(echo "$MAILCONFIG_SERVER_NAME_SHORT" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_IMAP_HOST" ] || printf "\$config['imap_host'] = %s;\n" \
                "$(echo "$MAILCONFIG_IMAP_HOST" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_WEBMAIL" ] || printf "\$config['webmail'] = %s;\n" \
                "$(echo "$MAILCONFIG_WEBMAIL" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_IMAP_PORT" ] || printf "\$config['imap_port'] = %s;\n" \
                "$(echo "$MAILCONFIG_IMAP_PORT" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_IMAP_SSL" ] || printf "\$config['imap_ssl'] = %s;\n" \
                "$(echo "$MAILCONFIG_IMAP_SSL" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_IMAP_NTLM" ] || printf "\$config['imap_ntlm'] = %s;\n" \
                "$(echo "$MAILCONFIG_IMAP_NTLM" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_SMTP_HOST" ] || printf "\$config['smtp_host'] = %s;\n" \
                "$(echo "$MAILCONFIG_SMTP_HOST" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_SMTP_PORT" ] || printf "\$config['smtp_port'] = %s;\n" \
                "$(echo "$MAILCONFIG_SMTP_PORT" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_SMTP_SSL" ] || printf "\$config['smtp_ssl'] = %s;\n" \
                "$(echo "$MAILCONFIG_SMTP_SSL" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_SMTP_NTLM" ] || printf "\$config['smtp_ntlm'] = %s;\n" \
                "$(echo "$MAILCONFIG_SMTP_NTLM" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_APPLE_IDENTIFIER" ] || printf "\$config['apple_identifier'] = %s;\n" \
                "$(echo "$MAILCONFIG_APPLE_IDENTIFIER" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_APPLE_UUID" ] || printf "\$config['apple_uuid'] = %s;\n" \
                "$(echo "$MAILCONFIG_APPLE_UUID" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
            [ -z "$MAILCONFIG_APPLE_MAIL_UUID" ] || printf "\$config['apple_mail_uuid'] = %s;\n" \
                "$(echo "$MAILCONFIG_APPLE_MAIL_UUID" | php -r 'var_export(trim(fgets(STDIN)) ?: null);')";
        } > "/etc/mailconfig/config.inc.php"
    fi

    exec "$@"
fi

exec "$@"
