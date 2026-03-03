# Stripe Setup

This document explains how to configure Stripe to work with a generic sales/payment flow.

## 1. Create a Stripe Account

1. Go to [https://stripe.com](https://stripe.com)
2. Create an account (or sign in if you already have one)
3. Complete the account verification process

## 2. Get API Keys

### Test Mode (Development)

1. In the Stripe Dashboard, go to **Developers** → **API keys**
2. Make sure you are in **Test mode** (toggle in the top-right corner)
3. Copy the following keys:
   - **Publishable key** (starts with `pk_test_...`)
   - **Secret key** (starts with `sk_test_...`)

### Production Mode (Production)

1. In the Stripe Dashboard, switch to **Live mode** (toggle in the top-right corner)
2. Copy the production keys:
   - **Publishable key** (starts with `pk_live_...`)
   - **Secret key** (starts with `sk_live_...`)

## 3. Configure Environment Variables

Add the following variables to your `.env` file:

```env
# Stripe Keys
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...

# Stripe Webhook Secret (will be configured in the next step)
STRIPE_WEBHOOK_SECRET=whsec_...
```

**Important:**
- Use **test** keys (`pk_test_` and `sk_test_`) during development
- Use **production** keys (`pk_live_` and `sk_live_`) only when the site is in production
- **NEVER** share your secret keys publicly

## 4. Configure Webhooks

Webhooks allow Stripe to notify your application when important events happen (such as completed payment).

### 4.1. Configure Local Webhook (Development)

#### 4.1.1. Container
```
    stripe-cli:
        image: stripe/stripe-cli:latest
        container_name: stripe-cli
        entrypoint: /bin/sh
        command: >
            -c "
            sleep 10 &&
            stripe listen 
            --forward-to http://laravel.test/webhooks/stripe 
            --api-key $${STRIPE_SECRET}
            --skip-verify
            "
        environment:
            STRIPE_API_KEY: "${STRIPE_SECRET}"
        volumes:
            - './.docker/stripe:/root/.config/stripe'
        networks:
            - sail
        restart: unless-stopped
        depends_on:
            laravel.test:
                condition: service_started
```

The project is already configured with Stripe CLI as a Docker service. No manual installation is required.

1. **Start containers:**
   ```bash
   ./vendor/bin/sail up -d
   ```
   
   The `stripe-cli` container will start automatically and begin forwarding webhooks to your application.

2. **Get the webhook signing secret:**
   
   Check Stripe CLI container logs:
   ```bash
   ./vendor/bin/sail logs stripe-cli
   ```
   
   Look for a line that shows:
   ```
   > Ready! Your webhook signing secret is whsec_...
   ```

3. **Add the secret to `.env`:**
   
   Copy the `whsec_...` value and add it to your `.env` file:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

4. **Restart the application (if needed):**
   ```bash
   ./vendor/bin/sail restart
   ```

**Note:** 
- The `stripe-cli` container automatically uses the `STRIPE_SECRET` variable from your `.env` for authentication. Make sure this variable is configured correctly.
- Stripe CLI settings are persisted in a Docker volume (`.stripe/` at the project root), which helps keep the same webhook signing secret even when containers are recreated. However, if you run `sail down` and `sail up`, a new secret may be generated. To keep the same secret, use only `sail restart`.

**Alternative (without Docker):**
If you prefer using Stripe CLI locally instead of the container:

1. **Install Stripe CLI:**
   - macOS: `brew install stripe/stripe-cli/stripe`
   - Windows: Download from [https://github.com/stripe/stripe-cli/releases](https://github.com/stripe/stripe-cli/releases)
   - Linux: Follow instructions at [https://stripe.com/docs/stripe-cli](https://stripe.com/docs/stripe-cli)

2. **Log in:**
   ```bash
   stripe login
   ```

3. **Start webhook forwarding:**
   ```bash
   stripe listen --forward-to http://localhost/webhooks/stripe
   ```

### 4.2. Configure Webhook in Production

1. In the Stripe Dashboard, go to **Developers** → **Webhooks**
2. Click **Add endpoint**
3. Configure:
   - **Endpoint URL**: `https://yourdomain.com/webhooks/stripe`
   - **Events to send**: Select the following events:
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `payment_intent.canceled`
4. Click **Add endpoint**
5. Copy the **Signing secret** (starts with `whsec_...`)
6. Add it to production `.env`:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

## 5. Test the Integration

**Important:** The application never uses environment variables or backdoors to simulate approved/declined payment. All payment outcomes (success, declined, insufficient funds) are driven by **Stripe test cards** below. Use test keys (`pk_test_`, `sk_test_`) in development and test; use live keys only in production.

### 5.1. Test Cards

Stripe provides test cards to simulate payments:

**Successful card:**
- Number: `4242 4242 4242 4242`
- Expiration date: Any future date (e.g. `12/25`)
- CVC: Any 3 digits (e.g. `123`)
- ZIP code: Any valid ZIP code (e.g. `12345`)

**Other test cards:**
- `4000 0000 0000 0002` - Card declined
- `4000 0000 0000 9995` - Insufficient funds
- See more at: [https://stripe.com/docs/testing](https://stripe.com/docs/testing)

### 5.2. Test the Full Flow

1. Make sure containers are running:
   ```bash
   ./vendor/bin/sail ps
   ```
   
   You should see the `stripe-cli` container in the list.

2. Access your checkout page (for example, `/checkout`)
3. Start a test purchase flow
4. Fill in the required data:
   - Full name
   - Email
   - Phone (optional)
   - Credit card data
5. Use test card `4242 4242 4242 4242`
   - Expiration date: Any future date (e.g. `12/25`)
   - CVC: Any 3 digits (e.g. `123`)
6. Click your checkout submit button (for example, "Pay now")
7. Complete payment (payment happens on the same page, without redirect)
8. Verify you were redirected to the success page
9. Verify payment appears in Stripe Dashboard
10. Check Stripe CLI logs to see webhooks being processed:
    ```bash
    ./vendor/bin/sail logs stripe-cli -f
    ```

## 6. Check Logs

If something does not work, check logs:

```bash
# Laravel logs
./vendor/bin/sail artisan tail

# Or
tail -f storage/logs/laravel.log

# Stripe CLI logs (webhooks)
./vendor/bin/sail logs stripe-cli

# Stripe CLI logs in real time
./vendor/bin/sail logs stripe-cli -f

# Check if Stripe CLI container is running
./vendor/bin/sail ps | grep stripe-cli
```

## 7. Production Checklist

Before going live, make sure you:

- [ ] Have a verified Stripe account
- [ ] Use production keys (`pk_live_` and `sk_live_`)
- [ ] Configure production webhook with the correct URL
- [ ] Add production webhook secret to `.env`
- [ ] Test a real payment with a low amount
- [ ] Configure email notifications (optional, but recommended)
- [ ] Review refund policies and terms of service

## 8. Additional Resources

- [Laravel Cashier Documentation](https://laravel.com/docs/cashier)
- [Stripe Elements Documentation](https://stripe.com/docs/stripe-js)
- [Stripe Payment Intents Documentation](https://stripe.com/docs/payments/payment-intents)
- [Stripe Dashboard](https://dashboard.stripe.com)
- [Stripe Testing](https://stripe.com/docs/testing)
- [Stripe CLI Docker Image](https://hub.docker.com/r/stripe/stripe-cli)

## 9. Troubleshooting

### Stripe CLI Container Problems

**Container does not start:**
```bash
# Check if container is running
./vendor/bin/sail ps | grep stripe-cli

# View container logs
./vendor/bin/sail logs stripe-cli

# Restart container
./vendor/bin/sail restart stripe-cli
```

**Webhook secret does not appear in logs:**
- Wait a few seconds after starting containers
- Stripe CLI needs time to connect to Stripe
- Verify `STRIPE_SECRET` is configured correctly in `.env`

**Webhooks are not arriving:**
- Check that `stripe-cli` container is running: `./vendor/bin/sail ps`
- Check real-time logs: `./vendor/bin/sail logs stripe-cli -f`
- Make sure `STRIPE_WEBHOOK_SECRET` in `.env` matches the secret shown in logs
- Verify `/webhooks/stripe` route is accessible

**Authentication error:**
- Verify `STRIPE_SECRET` is configured in `.env`
- Make sure you are using the secret key (starts with `sk_test_` or `sk_live_`), not the public key

## 10. Support

If you have issues:
1. Check Laravel logs: `./vendor/bin/sail artisan tail`
2. Check Stripe CLI logs: `./vendor/bin/sail logs stripe-cli -f`
3. Check Stripe Dashboard logs (Developers → Logs)
4. Check official Stripe documentation
5. Contact Stripe support if needed

