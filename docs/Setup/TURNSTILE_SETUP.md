# Cloudflare Turnstile Setup

This document explains how to configure Cloudflare Turnstile to protect lead forms against spam.

## What is Cloudflare Turnstile?

Cloudflare Turnstile is an alternative to reCAPTCHA that provides bot protection without requiring users to solve visual challenges. It is free and more user-friendly.

## Package Used

This project uses the official Laravel package: [ryangjchandler/laravel-cloudflare-turnstile](https://github.com/ryangjchandler/laravel-cloudflare-turnstile)

## ⚠️ No need to move DNS!

**IMPORTANT**: Cloudflare Turnstile works **without moving your DNS** to Cloudflare. You can:
- Keep your DNS where it is (GoDaddy, Namecheap, Route53, etc.)
- Use Turnstile normally
- Just create a free Cloudflare account to access Turnstile

Turnstile is a standalone service that does not require DNS management by Cloudflare.

## How to get credentials

### 1. Access the Cloudflare Dashboard

1. Go to [https://dash.cloudflare.com/](https://dash.cloudflare.com/)
2. **Create a free account** if you don't have one (no need to add a domain)
3. If you already have an account, log in

### 2. Navigate to Turnstile (without adding a domain)

1. Go directly to: [https://dash.cloudflare.com/?to=/:account/turnstile](https://dash.cloudflare.com/?to=/:account/turnstile)
2. Or in the sidebar menu, look for **"Turnstile"** (it may be under "Security" or directly in the menu)
3. **Ignore any prompt to add your domain to Cloudflare DNS** — you don't need that!

### 3. Create a new site in Turnstile

1. On the Turnstile page, click **"Add Site"**
2. Fill in the fields:
   - **Site name**: Descriptive name (e.g. "Rogerio Pereira Lead Forms")
   - **Domain**: Your full domain (e.g. `yoursite.com` or `*.yoursite.com` to include subdomains)
     - **Important**: Use the domain where the form will be used, even if DNS is not on Cloudflare
   - **Widget mode**: Choose **"Managed"** (recommended) or **"Non-interactive"**
3. Click **"Create"**
4. **Don't worry** if a message about DNS appears — you can ignore it; Turnstile will work normally

### 4. Get the keys

After creating the site, you will see:
- **Site Key**: Public key (used on the frontend)
- **Secret Key**: Private key (used on the backend — keep it secure!)

## Laravel configuration

### 1. The package is already installed

The package `ryangjchandler/laravel-cloudflare-turnstile` is already installed in the project.

### 2. Add environment variables

Add the following lines to your `.env` file:

```env
TURNSTILE_SITE_KEY=your_site_key_here
TURNSTILE_SECRET_KEY=your_secret_key_here
```

### 3. Example values

```env
TURNSTILE_SITE_KEY=0x4AAAAAAABkMYinukE8K5X0
TURNSTILE_SECRET_KEY=0x4AAAAAAABkMYinukE8K5X0_abcdefghijklmnopqrstuvwxyz
```

**⚠️ IMPORTANT**:
- Never commit the `.env` file to Git
- The Secret Key must be kept confidential
- Use different keys for development and production

### 4. Automatic configuration

Configuration is already set up in `config/services.php`. The package automatically detects the environment variables.

## Test mode

To test locally without configuring a real domain, you can use the test keys provided by Cloudflare:

- **Test Site Key**: `1x00000000000000000000AA`
- **Test Secret Key**: `1x0000000000000000000000000000000AA`

These keys always return success on validation, allowing you to test the integration locally.

**Note**: In automated tests, the package uses `Turnstile::fake()` to simulate successful validations automatically.

## Verification

After configuring:

1. Access the lead form: `/start-growth`

2. You should see the Turnstile widget before the submit button

3. When submitting the form, the token will be validated automatically

4. If validation fails, an error message will be displayed

## Troubleshooting

### Cloudflare asks to move DNS

**Solution**: Ignore that message! Turnstile works without moving DNS. Just:
1. Go directly to: https://dash.cloudflare.com/?to=/:account/turnstile
2. Create the site in Turnstile as usual
3. Use the generated keys — they will work even without DNS on Cloudflare

### Widget does not appear

- Check that `TURNSTILE_SITE_KEY` is set in `.env`
- Check that the Turnstile script is loading (inspect the browser console)
- Ensure the domain is registered in Cloudflare Turnstile (even if DNS is not on Cloudflare)

### Validation always fails

- Check that `TURNSTILE_SECRET_KEY` is correct in `.env`
- Check Laravel logs for error messages
- Ensure the site domain is in the list of allowed domains in Turnstile
- **Important**: The domain must be registered in Turnstile, but DNS can be with any provider

### "Invalid site key" error

- Check that the Site Key is correct
- Ensure the current domain is registered in Turnstile
- For local development, use the test keys

## Resources

- [Official Turnstile documentation](https://developers.cloudflare.com/turnstile/)
- [Turnstile Dashboard](https://dash.cloudflare.com/?to=/:account/turnstile)
- [Integration guide](https://developers.cloudflare.com/turnstile/get-started/server-side-rendering/)
