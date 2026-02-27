# ADR-017: General Security Measures

## Status

Approved

## Context

The system handles emails, profile data, and payment integration. It is necessary to reduce risks of abuse, data leakage, and misuse of APIs/resources.

## Decision

Adopt the following measures as **project standard**:

1. **Stripe webhook validation:** always validate signature (detailed in ADR-016).
2. **Rate limiting:** apply request limits on public endpoints (e.g. form, checkout creation) to mitigate abuse and DDoS.
3. **Input sanitization:** validate and sanitize all user-submitted data (email, URLs, text) to prevent injection and XSS in the report and emails.
4. **HTTPS:** ensure all communication with the application is over HTTPS in production.
5. **Sending identity (SES):** restrict SES usage to the configured domain/identity (e.g. report@goviral.you); do not send from arbitrary addresses.
6. **Secrets:** use **environment variables** (or a secret manager) for API keys (Stripe, SES, LLM); do not commit credentials to the repository.

References: HLD section 10 (Security); [Laravel Logging](https://laravel.com/docs/12.x/logging) for auditing when needed.

## Consequences

- **Positive:** Reduced attack surface and leakage risk; basic compliance with good practices.
- **Negative:** Rate limiting may affect legitimate users at peak; limit and env configuration per environment is required.
- **Neutral:** Monitoring of logs (rejected webhook, rate limit, send errors) should be considered for operations.
