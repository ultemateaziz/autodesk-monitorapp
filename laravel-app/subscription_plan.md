# License-Based Yearly Subscription System

## Overview
This document outlines the possible approaches to implement a key-based yearly subscription system for the Autodesk Monitor application.

---

## Current State

Your app already has:
- **User** model (with role, department, occupation)
- **UserLicense** model (tracks software assignments)
- Basic auth system with roles

**Missing:** No subscription/license key tracking system exists yet.

---

## Possible Implementation Approaches

### 1. License Key Model (Recommended)

Create a new `Subscription` or `LicenseKey` table with:

| Field | Type | Description |
|-------|------|-------------|
| `license_key` | string | Unique key (format: XXXX-XXXX-XXXX-XXXX) |
| `user_id` | foreign_id | Who activated the key |
| `plan_type` | string | basic, pro, enterprise |
| `start_date` | date | When subscription started |
| `end_date` | date | When subscription expires |
| `max_users` | integer | Max allowed users (optional) |
| `max_monitored_accounts` | integer | Max monitored accounts |
| `is_active` | boolean | Whether subscription is active |

**Pros:**
- Flexible, supports multiple plans
- Easy to validate and manage
- Can track multiple subscriptions

---

### 2. API Key Validation Middleware

Create Laravel middleware that:
- Checks valid license key on every request
- Stores key in config or cache for performance
- Returns 403 or redirect when expired

**Pros:**
- Simple to implement
- Works at app level
- Centralized validation

---

### 3. Hybrid: Key + User Subscription

- Admin enters a key in settings to activate
- System calculates expiration from key type (1-year, 2-year)
- Store `subscription_end_date` directly on `users` table

**Pros:**
- Minimal tables required
- Fast queries
- Simple to understand

---

### 4. Stripe/Payment Integration (If Monetizing)

- Integrate Stripe for actual payments
- Store Stripe subscription ID
- Use webhooks to update local DB

**Pros:**
- Real payments support
- Recurring billing automation
- Professional payment handling

---

## Quick Recommendation

For a **key-based yearly subscription**, use **Approach #1 (License Key Model)** combined with **Approach #2 (Middleware validation)**.

This gives you:
- Easy key generation & distribution
- Plan-based limits
- Automatic expiration checks
- Dashboard to view subscription status

---

## Next Steps

1. Create migration for `license_keys` or `subscriptions` table
2. Create `LicenseKey` model
3. Create middleware for validation
4. Add key activation functionality in admin panel
5. Add subscription status display in dashboard

---

*Generated on: 2026-03-07*
