# ADR-006: Email Provider

## Status

Approved

## Context

The analysis report must be delivered to the user by email in HTML, with good deliverability and compliance (SPF, DKIM). The sender defined in the manual is report@goviral.you.

## Decision

Use **AWS SES (Simple Email Service)** as the email delivery provider.

References:
- [AWS SES](https://docs.aws.amazon.com/ses/)
- [Laravel Mail](https://laravel.com/docs/12.x/mail)
- [Creating identities (SES)](https://docs.aws.amazon.com/ses/latest/dg/creating-identities.html)

## Consequences

- **Positive:** Low cost per email, integration via Laravel driver, verified domain and DKIM/SPF support; suitable for moderate volume.
- **Negative:** Domain verification and production approval required for SES; bounces and complaints must be monitored.
- **Neutral:** Identity configuration (report@goviral.you) and credentials via environment variables.
