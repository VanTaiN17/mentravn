# Mail Configuration — Owner Guide (Phase 7)

How to finish wiring real email delivery for Mentra Vietnam's contact/career/newsletter forms. This is an operational wp-admin guide — no code changes are required to complete it, and no secrets are recorded here or anywhere else in this repository.

## Current state (as found, this install)

- **WP Mail SMTP**: installed (v4.9.0), **active**.
- **Mailer**: `mail` — WordPress's default PHP `mail()` function. No real SMTP/API provider (Gmail, Outlook, SendLayer, Brevo, SMTP.com, etc.) has been selected yet.
- **From Email**: `dev-email@wpengine.local`, **forced on**. This is a leftover local-environment placeholder, not a Mentra Vietnam address — it must be changed before production.
- **From Name**: `mentra-vn`, not forced (Mentra's code never sets a From header itself, so this doesn't currently matter in practice — see Mentra Mail Architecture below).
- **No other mailer sections** (SMTP host/user/pass, Gmail OAuth, or any API key) have real values — only default/empty scaffold fields.
- **No competing mail plugin** is active — only WP Mail SMTP.

In short: the plugin is ready and correctly positioned in front of `wp_mail()`, but nobody has pointed it at a real outbound mail provider yet. This is expected and normal for a pre-launch site — it is an owner/admin configuration step, not something Claude can or should complete by inventing credentials.

## What the owner needs to do

1. **WordPress Admin → WP Mail SMTP → Settings**
2. **Mailer**: choose a real provider (any of WP Mail SMTP's supported options — a transactional email API like SendLayer/Brevo/SMTP.com, or a real SMTP relay, or Gmail/Outlook if using a business mailbox). This document deliberately does not recommend one specific provider — that's a business decision, not a technical one.
3. **From Email**: set to a real, domain-owned address (e.g. something under the site's actual production domain once decided — see Domain Alignment below). Enable **Force From Email** so no code path can override it.
4. **From Name**: set to the desired display name (e.g. "Mentra Việt Nam"). Force it too if consistency matters.
5. **Authentication / provider-specific settings**: follow whichever provider's own instructions WP Mail SMTP shows once selected (API key, SMTP host/port/username/password, or an OAuth connection flow). Enter these directly in wp-admin — **never** in a file that gets committed to Git.
6. **Save**, then use WP Mail SMTP's own **Email Test** tab (WP Mail SMTP → Tools → Email Test) to send a real test message to a mailbox the owner can check.
7. **Check the inbox (and spam folder)** of that test address to confirm real end-to-end delivery — this is the one step that genuinely requires a human, since Claude has no mailbox access.

## Domain alignment

The current business recipient (`contact@domain.vn`, Phase 5) and the From address discussed above both depend on a real production domain that has **not yet been finalized in this project** — `domain.vn` is a documented placeholder (see `docs/project-status.md`). Do not treat either address as final:

- Do not silently swap in a different placeholder (e.g. inventing `noreply@domain.vn`) — that's still a guess.
- Once the real production domain is chosen, both the WP Mail SMTP From Email and the Mentra `mentra_vn_recipient_email`/`mentra_vn_contact_email` option (Settings → Mentra Việt Nam) should be updated to match it, as a deliberate configuration step at that time.

## SPF / DKIM / DMARC (production requirement, not a code task)

Once a real domain and provider are chosen, the domain's DNS must be configured so mail authenticates cleanly:

- **SPF**: the domain's SPF TXT record must include the chosen provider's sending servers.
- **DKIM**: enable DKIM signing through the provider (most transactional providers generate the DNS records to add) and add the corresponding DNS records.
- **DMARC**: a DMARC TXT record is recommended once SPF/DKIM are both passing, so receiving mail servers know how to treat messages that fail authentication.
- The **From domain** should match (or be properly aligned via SPF/DKIM) the domain actually sending the mail — this is exactly why Mentra's code never puts a visitor's email address in the From header (see below).

This project does not (and should not) modify DNS. If WP Mail SMTP itself reports a domain-verification status for the chosen provider, that status can be recorded (see `docs/phase-7-report.md`) — but no DNS state should ever be claimed as correct without direct verification.

## Mentra mail architecture (already correct, no change needed)

Confirmed by code audit (Phase 7): every `wp_mail()` call in `mentra-vietnam-core.php`'s `send_form_mail()` sets exactly two headers —

```
Content-Type: text/plain; charset=UTF-8
Reply-To: {sanitized visitor name} <{validated visitor email}>
```

— and **never** a `From:` header. This already matches the recommended architecture: the visitor's email is only ever the **Reply-To**, so WP Mail SMTP's forced From Email (once configured per the steps above) becomes the sole, authenticated sender for every outgoing message, with no risk of a visitor's arbitrary address ending up in From and damaging SPF/DKIM/DMARC alignment. No code change was required for this phase.
