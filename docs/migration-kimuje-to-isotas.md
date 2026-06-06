# Kimuje → Isotas Migration Runbook

**Audience**: Super Admin operators
**Purpose**: Move field devices from the legacy KIMUJE company to the new ISOTAS company without losing historical data.

---

## Background

KIMUJE was the original sister operation. All its historical collections remain under the `kimuje` company slug and are preserved forever — no data is renamed or deleted.

New operations are registered under `ISOTAS` (slug: `isotas`). When a collector on a Kimuje device is moved to Isotas, you issue them new credentials; they log out and back in on the device. The device then reports as an Isotas machine from that point forward.

---

## Pre-flight

1. Log in to the admin dashboard at `https://dev.pos.hype.co.tz/admin/dashboard` as `super_admin`.
2. Confirm the **ISOTAS** company exists at `/admin/companies`. If not, create it:
   - Name: `ISOTAS` (or full legal name)
   - Slug: `isotas`
   - Check "Pre-create unknown-client-isotas fallback client"

---

## Per-device migration steps

Repeat for each Kimuje device being migrated.

### Step 1 — Create the Isotas machine + collector

Go to `/admin/machines/create` and fill in:

| Field | Value |
|-------|-------|
| Machine name | `IsotasXXX` (next available number, e.g. `Isotas007`) |
| Company | ISOTAS |
| Location | (as appropriate) |
| Collector name | (collector's real name) |
| Collector phone | (collector's phone) |
| Password | Generate a new password; **copy it before saving** |

Click **Create Machine**. This creates both the machine record and the collector account in one transaction.

### Step 2 — Hand credentials to the collector

Give the collector:
- **Machine name** (login username): e.g. `Isotas007`
- **Password**: the one generated in Step 1

### Step 3 — Collector logs out on the device

On the POSH5 device, the collector:
1. Opens the app menu → **Logout**
2. Waits for the logout to complete

### Step 4 — Collector logs in with new credentials

On the device:
1. Enter machine name: `Isotas007`
2. Enter password (provided in Step 2)
3. Tap **Login**

The app will attempt online login. On success, the device is now registered as an Isotas machine and all future collections will sync under ISOTAS.

### Step 5 — Verify

In the admin dashboard:
- Go to `/admin/collectors` → confirm the new Isotas collector shows their machine
- Go to `/admin/machines` → confirm the Isotas machine shows as Active
- Deactivate the old Kimuje collector account at `/admin/collectors` if no longer needed

---

## Deactivating the old Kimuje collector

Once the device is migrated and verified:

1. Go to `/admin/collectors`
2. Find the old `KimujeXXX` collector
3. Click **Deactivate**

The Kimuje machine record and all its historical collections remain untouched. Only the collector account is deactivated.

---

## Bulk migration

If migrating many devices at once, do Steps 1–2 for all devices first (preparing all credential sets), then co-ordinate with collectors in the field for Steps 3–5 on a single day.

---

## Rollback

If a collector cannot log in with new Isotas credentials before the end of a shift:
- Reactivate the old Kimuje collector at `/admin/collectors` → Edit → (re-enable)
- The collector logs back in with the old credentials
- Retry the migration when convenient

Historical Kimuje data is never at risk — the migration only changes which credentials the device uses for future logins.

---

## Audit trail

Every machine creation, collector update, and deactivation is logged in `/admin/audit-logs` and visible to super_admins.
