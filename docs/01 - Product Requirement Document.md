# Product Requirements Document (PRD)

# Product Name

GoViral

# Date

2026-02-24

# Document Owner

Rogerio Pereira

------------------------------------------------------------------------

# 1. Product Overview

GoViral is a micro SaaS product designed to provide fast, AI-powered
TikTok profile analysis for beginner and small creators.

The product analyzes user-provided profile information and generates
actionable recommendations focused on growth and monetization. It is
positioned as an affordable, impulse-buy entry product rather than a
marketing consultancy.

Primary value proposition: 
- Fast AI-driven analysis 
- Clear, practical recommendations 
- 30-day action plan 
- No learning curve required

------------------------------------------------------------------------

# 2. Problem Statement

Beginner and small TikTok creators face the following challenges:

- They want fast monetization opportunities.
- They do not understand how to create viral content.
- They lack structured content planning.
- They do not have a clear growth strategy.
- They do not understand how the TikTok algorithm works.
- They do not want to spend time studying complex marketing strategies.

GoViral addresses the psychological drivers of: 
- Laziness to learn deeply 
- Desire for fast results

------------------------------------------------------------------------

# 3. Target Audience

## Included

- Global market
- Beginner creators
- Small creators with low to medium follower count
- Individuals unfamiliar with TikTok strategy
- Creators seeking monetization and growth

## Excluded

- Established brands
- Marketing teams
- Companies with structured editorial calendars
- Professional social media agencies

## Supported Languages

- English
- Spanish
- Portuguese

The report language is selected on the landing page and passed to the
LLM to ensure output consistency.

------------------------------------------------------------------------

# 4. Business Model

- One-time payment model
- Initial target price: $20 (to be validated)
- USD currency only
- Stripe as payment provider
- No refunds (digital delivery via email)
- Unlimited repurchases allowed
- No subscription model in MVP

------------------------------------------------------------------------

# 5. MVP Scope

## 5.1 User Flow

1. User accesses landing page
2. Selects preferred language
3. Fills form:
    - Email
    - TikTok username
    - Current bio
    - Aspiring Niche
    - Links to last 3 videos
    - Notes (optional)
4. User completes Stripe payment
5. System validates payment confirmation
6. Data is pushed into a processing queue
7. Worker/Lambda processes request
8. LLM generates analysis
9. HTML report is sent via email

Target SLA: 
- Delivery within 10 minutes (typically 1-3 minutes)

------------------------------------------------------------------------

# 6. Data Collection Strategy

MVP uses 100% manual input.

No: 
- TikTok API integration 
- Scraping 
- Third-party data services

This minimizes: 
- Legal risk 
- Infrastructure complexity 
- Maintenance cost

------------------------------------------------------------------------

# 7. Report Structure (HTML Email)

The report will include the following sections in this order:

1. Executive Summary
2. Profile Score
3. Inferred Niche Analysis
4. Username Suggestions
5. Optimized Bio
6. Profile Optimization Suggestions
7. Content Ideas
    - Optimization & Setup
    - Testing Hooks
    - Scaling Winning Format
    - Monetization Positioning
    - **30 day Content plan**
8. Viralization Tips
9. 30-Day Action Plan

## Profile Score

- Score from 0 to 100
- Includes 3 explanation bullets:
    - Key strength
    - Main weakness
    - Biggest improvement opportunity

------------------------------------------------------------------------

# 8. LLM Output Requirements

The LLM must generate:

- 3-5 attention-grabbing username suggestions
- 1 optimized bio aligned with monetization
- 1-3 profile optimization suggestions
- 10 content ideas
- 10-30 viral growth tips
- Structured 30-day action plan
- Informative score with explanation bullets

All output must match the user-selected language.

------------------------------------------------------------------------

# 9. Technical Architecture (High-Level)

- Frontend: Landing + form
- Payment: Stripe
- Backend:
    - Payment webhook validation
    - Queue system
    - Worker or Lambda-based processor
- LLM API integration
- Email service for HTML report delivery

Architecture must support: 
- Queue-based async processing 
- Horizontal scalability 
- Minimal storage (no long-term report storage)

No dashboard. No history retention. No PDF generation.

------------------------------------------------------------------------

# 10. Roadmap (Post-MVP)

## Priority 2 - Advanced Content Plan Upsell (+$27)

- Immediate post-checkout one-click upsell (CTA included in report email)
- 30 fully structured scripts
- Hooks + CTA suggestions
- Fully automated

## Priority 3 - Reanalysis with Evolution Comparison (+$15)

- Second analysis after 30 days
- Comparative scoring
- Progress insights
- Requires minimal data retention

------------------------------------------------------------------------

# 11. Success Metrics

## Financial

- Minimum success target: $2,000/month net profit
- Aspirational target: $10,000-12,000/month

## Operational

- Delivery time < 10 minutes
- Error rate < 2%
- Gross margin > 60%

## Marketing

- Landing conversion target: 2-5%
- Target sales volume for $2k net: 150-200 sales/month

------------------------------------------------------------------------

# 12. Risks

- Overpromising results
- High LLM cost per request
- Email deliverability issues
- Low perceived value at $20
- Copy positioning misalignment

------------------------------------------------------------------------

# 13. Positioning Statement

GoViral is not a marketing consultancy. It is a fast, AI-powered growth
direction tool for creators who want immediate clarity and actionable
steps.

It is designed for impulse buying, accessibility, and simplicity.
