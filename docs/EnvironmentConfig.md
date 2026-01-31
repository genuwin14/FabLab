# Environment Configuration (v1)

This document details the specific environmental configurations and third-party integrations used in the current system. Standard Laravel defaults are omitted.

## 1. Database Configuration
- **Connection**: MySQL
- **Database Name**: `fablabdesigner`
- **Host**: `127.0.0.1` (Localhost)
- **Port**: 3306
- **Username**: `root`
- **Password**: (Empty)

## 2. Mail Service (Brevo / Sendinblue)
The system uses **Brevo** (formerly Sendinblue) as the SMTP relay for sending emails (notifications, resets, etc.).
- **Mailer**: SMTP
- **Host**: `smtp-relay.brevo.com`
- **Port**: 587
- **Encryption**: TLS
- **Username**: `956c02001@smtp-brevo.com`
- **Password**: `h0JOfDn4QHq1AWEw`
- **From Address**: `kerayun5@gmail.com` ("CSPC - Fablabs")

## 3. SMS Integration
The system contains configurations for multiple SMS gateways.

### A. PhilSMS (Primary API)
- **Provider**: [PhilSMS](https://philsms.com/)
- **API Version**: v3
- **Sender ID**: `FabLabs`
- **API Token**: `740|ybb4mPWIsHaGvbrOcu5cwFKQJ0rY1pyhDPEvU2hP14e7ef13`
- **Status**: Active (Token configured)

### B. MacroDroid (Local Gateway - Fallback/Legacy)
- **Mechanism**: HTTP GET Request to a local Android device.
- **Status**: Currently commented out in config.
- **Endpoint**: `http://192.168.0.114:8080/sms`

## 4. Authentication Services

### Google OAuth (Socialite)
Used for "Login with Google" functionality.
- **Client ID**: `67541703953-jnhphsu8iddr42q92r8la3ab1cik4ciu.apps.googleusercontent.com`
- **Client Secret**: `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`
- **Redirect URI**: `http://127.0.0.1:8000/login/google/callback`

### Recaptcha
- **Status**: Commented out / Disabled.

## 5. System Drivers
- **Session Driver**: `database` (Sessions are stored in the database `sessions` table).
- **Queue Connection**: `database` (Jobs are stored in the `jobs` table).
- **Cache Store**: `database`.
