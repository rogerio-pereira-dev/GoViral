# High Level Design (HLD)

# Project Name

GoViral

# Date

2026-02-24

# Document Owner

Rogerio Pereira

------------------------------------------------------------------------

# 1. Overview

This document describes the high-level architecture of GoViral, a micro
SaaS platform that provides AI-powered TikTok profile analysis for
beginner and small creators.

The system is designed to be:

-   Lightweight
-   Cost-efficient
-   Asynchronous
-   Horizontally scalable
-   Minimal in storage usage

------------------------------------------------------------------------

# 2. Technology Stack

## Hosting

-   Laravel Cloud\
    Documentation: https://cloud.laravel.com/docs/intro

## Backend

-   Laravel (latest stable version)\
    Documentation: https://laravel.com/docs

## Frontend

-   Laravel + Inertia  
    Documentation: https://laravel.com/docs/12.x/frontend
-   Vue.js  
    Documentation: https://vuejs.org/guide/introduction.html
-   Vuetify  
    Documentation: https://vuetifyjs.com

## Database

-   PostgreSQL (minimal usage)

## Queue System

-   Redis Queue Driver\
    Documentation: https://laravel.com/docs/12.x/queues

## Email Provider

-   AWS SES\
    Documentation: https://docs.aws.amazon.com/ses/

## Payment Provider

-   Laravel Cashier\
    Documentation: https://laravel.com/docs/12.x/billing
-   Stripe Checkout + Webhooks\
    Documentation: https://docs.stripe.com/payments/checkout\
    Webhooks: https://docs.stripe.com/webhooks

------------------------------------------------------------------------

# 3. System Architecture Overview

User → Landing Page → Stripe Checkout → Stripe Webhook → Laravel API →
Database → Redis Queue → Worker → LLM Provider → HTML Report → AWS SES →
User Email

The system is fully asynchronous after payment confirmation.

------------------------------------------------------------------------

# 4. Database Design

Single table: `analysis_requests`

Fields:

-   id (UUID)
-   email
-   tiktok_username
-   bio
-   aspiring_niche
-   video_url_1
-   video_url_2
-   video_url_3
-   notes (optional)
-   locale (en/es/pt)
-   stripe_checkout_session_id
-   stripe_payment_intent_id (optional)
-   payment_status (pending \| paid \| failed)
-   processing_status (queued \| processing \| sent \| failed)
-   attempt_count
-   last_error (nullable text)
-   created_at
-   updated_at

Data retention policy:

Delete record when:

1.  Report successfully sent
2.  12 processing failures (≈ 1 hour)
3.  Older than 24 hours (scheduled cleanup)

Scheduling Documentation: https://laravel.com/docs/12.x/scheduling

------------------------------------------------------------------------

# 5. Payment Flow

1.  User completes Stripe Checkout
2.  Stripe sends webhook event (`checkout.session.completed`)
3.  Laravel validates webhook signature
4.  System updates `payment_status = paid`
5.  Job pushed to Redis queue

Webhook validation documentation: https://docs.stripe.com/webhooks

------------------------------------------------------------------------

# 6. Queue & Processing Flow

Queue Driver: Redis

Documentation: https://laravel.com/docs/12.x/queues

Processing steps:

1.  Worker pulls job
2.  Update `processing_status = processing`
3.  Call LLM provider
4.  Generate structured HTML report
5.  Send email via AWS SES
6.  Update `processing_status = sent`
7.  Delete record

Retry policy:

-   Retry every 5 minutes
-   Max 12 attempts
-   On final failure → mark as failed and delete

------------------------------------------------------------------------

# 7. LLM Integration (TBD -- Requires ADR)

LLM provider not yet finalized.

Candidate providers: - OpenAI - Gemini - Anthropic

Goal: cost-efficient model with acceptable quality.

Two possible integration approaches:

Option A: Adapter/Strategy Pattern inside Laravel\
- Interface: `LlmClient` - Implementations per provider - Selection via
environment variable

Option B: External orchestration (e.g., n8n)

A technical spike and Architecture Decision Record (ADR) must be created
before implementation.

------------------------------------------------------------------------

# 8. Email Delivery

Provider: AWS SES

Sender: report@goviral.you

Requirements:

-   Domain verification
-   DKIM setup
-   SPF configuration
-   Production access approval

SES Documentation:
https://docs.aws.amazon.com/ses/latest/dg/creating-identities.html

Laravel Mail: https://laravel.com/docs/12.x/mail

------------------------------------------------------------------------

# 9. Scalability Considerations

The architecture supports horizontal scaling by:

-   Increasing worker instances
-   Scaling Redis
-   Scaling Laravel Cloud instances

No long-term report storage. No dashboard. No historical analytics.

Designed for low operational overhead.

------------------------------------------------------------------------

# 10. Security Considerations

-   Validate Stripe webhook signatures
-   Rate limit public endpoints
-   Sanitize all user input
-   Enforce HTTPS
-   Restrict SES sending identity
-   Use environment variables for API keys

------------------------------------------------------------------------

# 11. Operational Monitoring

Recommended monitoring:

-   Queue failure monitoring
-   Stripe webhook error logs
-   SES bounce/complaint tracking
-   LLM API error tracking

Laravel logging: https://laravel.com/docs/12.x/logging

------------------------------------------------------------------------

# 12. Future Extensions (Post-MVP)

1.  Advanced Content Plan Upsell
2.  Reanalysis with Comparison
3.  Multi-provider LLM failover
4.  Lightweight admin dashboard
5.  Cost monitoring per request

------------------------------------------------------------------------

# 13. Key Architectural Principles

-   Minimal storage
-   Async processing
-   Provider-agnostic AI integration
-   Clear separation of concerns
-   Operational simplicity
-   Cost awareness

------------------------------------------------------------------------

End of Document
