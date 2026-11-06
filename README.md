# Secure Simple SMTP

WordPress plugin that sends all site email through an SMTP server configured in the admin panel, and lets an administrator send a test email.

## What it does

- Switches WordPress mail (`wp_mail`) to SMTP through the `phpmailer_init` hook, with authentication always on.
- Uses the SMTP username as the sender address and the site name as the sender name.
- Provides a settings page with SMTP host, port, username, password, and encryption.
- Sends a test email to any address from the settings page and shows whether it was sent.

## Requirements

- WordPress with administrator access (`manage_options`).
- An SMTP server with username and password authentication.

## Installation

1. Copy the plugin folder to `wp-content/plugins/`.
2. Activate **Secure Simple SMTP** in **Plugins**.
3. Open **Settings → Secure SMTP**, fill in the SMTP settings, and save.
4. Send a test email from the same page.

## Settings

All settings are stored in one option, `secure_smtp_options`.

| Setting | Key | Notes |
|---|---|---|
| SMTP Host | `smtp_host` | for example `smtp.example.com` |
| SMTP Port | `smtp_port` | 587 for TLS, 465 for SSL |
| SMTP Username | `smtp_user` | also used as the From address |
| SMTP Password | `smtp_pass` | |
| Encryption | `smtp_secure` | `tls`, `ssl`, or none |

## Project structure

```
trues-smtp.php   The whole plugin: settings page, settings registration and sanitization, mailer setup, test email
```

## Known limitations

- SMTP is forced for all mail as soon as the plugin is active, even before the settings are filled in; until then the site cannot send email.
- The username is sanitized as an email address, so non-email usernames (for example `apikey`) are erased on save.
- The password is sanitized as a text field, which can change passwords that contain characters such as `<` or `%`.
- The password is stored in plain text in the database and is printed into the settings page HTML as the field value.
- The Encryption setting renders both a text input and a select with the same name.
- An empty port is saved as `0` instead of falling back to 587.
