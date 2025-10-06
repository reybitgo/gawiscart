# MLM System Testing Documentation

## Overview

This document provides comprehensive testing procedures for the Multi-Level Marketing (MLM) system implementation. Each phase includes detailed test cases, expected results, and validation criteria.

---

## Phase 1: Core MLM Package & Sponsor-Based Registration

**Status**: Ready for Testing
**Estimated Testing Time**: 2-3 hours
**Prerequisites**: Database reset seeder has been run

---

## Test Environment Setup

### Initial Setup

```bash
# 1. Reset the database
php artisan db:seed --class=DatabaseResetSeeder

# 2. Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Start the development server
php artisan serve
```

### Test Accounts

-   **Admin**: admin / Admin123!@# (email: admin@gawisherbal.com)
-   **Member**: member / Member123!@# (email: member@gawisherbal.com)

**Note**: Users can now login using either username or email. Email is optional during registration.

### Default URLs

-   Application: `http://coreui_laravel_deploy.test/` or `http://localhost:8000/`
-   Admin Login: `/login`
-   Public Registration: `/register`
-   Member Registration (Logged-in): `/register-member`
-   Admin Packages: `/admin/packages`
-   MLM Settings: `/admin/packages/starter-package/mlm-settings`

---

## Test Suite 1: Database Schema & Migrations

### Test Case 1.1: Verify MLM Tables Exist

**Objective**: Ensure all MLM-related database tables and columns exist

**Steps**:

1. Connect to MySQL database
2. Run the following SQL queries:

```sql
-- Check mlm_settings table exists
DESCRIBE mlm_settings;

-- Check users table has MLM columns
SHOW COLUMNS FROM users WHERE Field IN ('sponsor_id', 'referral_code');

-- Check packages table has MLM columns
SHOW COLUMNS FROM packages WHERE Field IN ('is_mlm_package', 'max_mlm_levels');

-- Check wallets table has segregated balances
SHOW COLUMNS FROM wallets WHERE Field IN ('mlm_balance', 'purchase_balance');
```

**Expected Results**:

-   ✅ `mlm_settings` table exists with columns: `id`, `package_id`, `level`, `commission_amount`, `is_active`, `created_at`, `updated_at`
-   ✅ `users` table has `sponsor_id` (bigint, nullable) and `referral_code` (varchar(20), unique)
-   ✅ `packages` table has `is_mlm_package` (boolean) and `max_mlm_levels` (tinyint)
-   ✅ `wallets` table has `mlm_balance` (decimal) and `purchase_balance` (decimal)

**Pass Criteria**: All tables and columns exist with correct data types

---

### Test Case 1.2: Verify MLM Settings Data

**Objective**: Ensure MLM commission structure is seeded correctly

**Steps**:

1. Run SQL query:

```sql
SELECT
    ms.level,
    ms.commission_amount,
    ms.is_active,
    p.name as package_name,
    p.price as package_price
FROM mlm_settings ms
JOIN packages p ON ms.package_id = p.id
WHERE p.slug = 'starter-package'
ORDER BY ms.level;
```

**Expected Results**:

```
level | commission_amount | is_active | package_name    | package_price
------|-------------------|-----------|-----------------|---------------
1     | 200.00           | 1         | Starter Package | 1000.00
2     | 50.00            | 1         | Starter Package | 1000.00
3     | 50.00            | 1         | Starter Package | 1000.00
4     | 50.00            | 1         | Starter Package | 1000.00
5     | 50.00            | 1         | Starter Package | 1000.00
```

**Pass Criteria**:

-   ✅ Exactly 5 MLM settings records exist
-   ✅ Level 1 commission = ₱200
-   ✅ Levels 2-5 commission = ₱50 each
-   ✅ All levels are active
-   ✅ Total commission = ₱400 (40% of ₱1,000)

---

### Test Case 1.3: Verify User MLM Relationships

**Objective**: Ensure default users have proper sponsor relationships and referral codes

**Steps**:

1. Run SQL query:

```sql
SELECT
    id,
    username,
    email,
    sponsor_id,
    referral_code,
    email_verified_at,
    CASE
        WHEN sponsor_id IS NULL THEN 'No Sponsor'
        ELSE CONCAT('Sponsored by User #', sponsor_id)
    END as sponsor_info
FROM users
WHERE username IN ('admin', 'member')
ORDER BY id;
```

**Expected Results**:

-   **Admin (ID: 1)**:

    -   `sponsor_id` = NULL
    -   `referral_code` = REF[8 uppercase alphanumeric characters] (e.g., REFX448MDSM)
    -   `email` = 'admin@gawisherbal.com' (or could be NULL if optional)
    -   `email_verified_at` = timestamp or NULL
    -   sponsor_info = "No Sponsor"

-   **Member (ID: 2)**:
    -   `sponsor_id` = 1
    -   `referral_code` = REF[8 uppercase alphanumeric characters] (e.g., REFMR8QHLWU)
    -   `email` = 'member@gawisherbal.com' (or could be NULL if optional)
    -   `email_verified_at` = timestamp or NULL
    -   sponsor_info = "Sponsored by User #1"

**Pass Criteria**:

-   ✅ Admin has no sponsor (sponsor_id = NULL)
-   ✅ Member is sponsored by Admin (sponsor_id = 1)
-   ✅ Both users have unique referral codes starting with "REF"
-   ✅ Referral codes are exactly 11 characters (REF + 8 chars)
-   ✅ Email can be NULL (optional field)

---

### Test Case 1.4: Verify Wallet Segregated Balances

**Objective**: Ensure wallets have MLM and purchase balances properly set

**Steps**:

1. Run SQL query:

```sql
SELECT
    u.username,
    u.email,
    w.balance as legacy_balance,
    w.mlm_balance,
    w.purchase_balance,
    (w.mlm_balance + w.purchase_balance) as total_available
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE u.email IN ('admin@gawisherbal.com', 'member@gawisherbal.com')
ORDER BY u.id;
```

**Expected Results**:

```
username | email                    | legacy_balance | mlm_balance | purchase_balance | total_available
---------|--------------------------|----------------|-------------|------------------|----------------
admin    | admin@gawisherbal.com   | 0.00          | 0.00        | 1000.00         | 1000.00
member   | member@gawisherbal.com  | 0.00          | 0.00        | 1000.00         | 1000.00
```

**Pass Criteria**:

-   ✅ Both users have ₱0 legacy balance (deprecated field)
-   ✅ Both users have ₱0 MLM balance (no commissions earned yet)
-   ✅ Both users have ₱1,000 purchase balance (can buy Starter Package)
-   ✅ Total available = ₱1,000

---

## Test Suite 2: User Registration with Sponsor System

**IMPORTANT NOTE**: Email is now **OPTIONAL** during registration. Users can register with or without an email address. If an email is provided, email verification will be required (based on system settings). If no email is provided, users can still access the system and add an email later via their profile.

---

### Test Case 2.1: Registration Without Sponsor (Default to Admin)

**Objective**: Verify new users default to Admin sponsor when no sponsor provided

**Steps**:

1. Open browser and navigate to registration page: `/register`
2. Fill in registration form:
    - **Full Name**: John Doe
    - **Username**: johndoe
    - **Email**: johndoe@test.com (OPTIONAL - can be left blank)
    - **Sponsor Name**: (leave blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check "I agree to terms" checkbox
3. Click "Create Account"
4. Verify success (redirected to dashboard or email verification if email was provided)
5. Check database:

```sql
SELECT id, username, email, sponsor_id, referral_code, email_verified_at
FROM users
WHERE username = 'johndoe';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = 1 (Admin)
-   ✅ `referral_code` generated automatically (format: REF[8 chars])
-   ✅ Small text shows "Leave blank to be assigned to default sponsor (Admin)"
-   ✅ Email placeholder shows "Email (Optional)"
-   ✅ Helper text below email: "Email is optional. If provided, you will need to verify it. You can add it later in your profile."
-   ✅ If email was provided: `email_verified_at` = NULL (pending verification)
-   ✅ If email was blank: `email` = NULL, `email_verified_at` = NULL

**Pass Criteria**:

-   ✅ New user created with Admin as default sponsor
-   ✅ Unique referral code generated
-   ✅ No validation errors
-   ✅ Email can be omitted without errors

**Screenshot Required**: ✅ Registration form showing optional email field

---

### Test Case 2.2: Registration With Valid Sponsor Username (No Email)

**Objective**: Verify users can register with sponsor's username without providing email

**Steps**:

1. Navigate to `/register`
2. Fill in registration form:
    - **Full Name**: Jane Smith
    - **Username**: janesmith
    - **Email**: (leave blank)
    - **Sponsor Name**: `admin` (enter admin's username)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Create Account"
4. Verify database:

```sql
SELECT id, username, email, sponsor_id, referral_code, email_verified_at
FROM users
WHERE username = 'janesmith';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = 1 (Admin found by username)
-   ✅ Referral code generated
-   ✅ `email` = NULL
-   ✅ `email_verified_at` = NULL
-   ✅ User can login immediately without email verification

**Pass Criteria**:

-   ✅ Sponsor correctly identified by username
-   ✅ sponsor_id matches admin user
-   ✅ Registration succeeds without email

**Screenshot Required**: ✅ Registration form with no email, sponsor username filled

---

### Test Case 2.3: Registration With Valid Sponsor Referral Code

**Objective**: Verify users can register using sponsor's referral code

**Steps**:

1. Get admin's referral code from database:

```sql
SELECT referral_code FROM users WHERE email = 'admin@gawisherbal.com';
```

2. Navigate to `/register`
3. Fill in registration form:
    - **Full Name**: Bob Wilson
    - **Username**: bobwilson
    - **Email**: bobwilson@test.com
    - **Sponsor Name**: [Paste admin's referral code, e.g., REFX448MDSM]
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
4. Click "Create Account"
5. Verify database:

```sql
SELECT id, username, sponsor_id
FROM users
WHERE email = 'bobwilson@test.com';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = 1 (Admin found by referral code)

**Pass Criteria**:

-   ✅ Sponsor correctly identified by referral code
-   ✅ sponsor_id matches admin user

**Screenshot Required**: ✅ Registration form with referral code

---

### Test Case 2.4: Registration With Referral Code in URL

**Objective**: Verify referral code from URL auto-fills sponsor field

**Steps**:

1. Get member's referral code:

```sql
SELECT referral_code FROM users WHERE email = 'member@gawisherbal.com';
```

2. Navigate to `/register?ref=[MEMBER_REFERRAL_CODE]` (e.g., `/register?ref=REFMR8QHLWU`)
3. **Observe** the sponsor field:
    - Should be auto-filled with referral code
    - Blue info alert should appear: "Referral Code Applied: [CODE]"
4. Fill in remaining fields:
    - **Full Name**: Alice Cooper
    - **Username**: alicecooper
    - **Email**: alicecooper@test.com
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
5. Click "Create Account"
6. Verify database:

```sql
SELECT id, username, sponsor_id
FROM users
WHERE email = 'alicecooper@test.com';
```

**Expected Results**:

-   ✅ Sponsor field auto-filled with referral code from URL
-   ✅ Blue alert displays: "Referral Code Applied: [CODE]"
-   ✅ User successfully registered
-   ✅ `sponsor_id` = 2 (Member user)

**Pass Criteria**:

-   ✅ Referral code correctly parsed from URL query parameter
-   ✅ Sponsor field auto-populated
-   ✅ Visual confirmation shown to user
-   ✅ sponsor_id matches member user (not admin)

**Screenshot Required**:

-   ✅ Registration page with referral code alert
-   ✅ Auto-filled sponsor field

---

### Test Case 2.5: Registration With Invalid Sponsor Name (With Email)

**Objective**: Verify system handles invalid sponsor gracefully with email verification

**Steps**:

1. Navigate to `/register`
2. Fill in registration form:
    - **Full Name**: Charlie Brown
    - **Username**: charliebrown
    - **Email**: charliebrown@test.com
    - **Sponsor Name**: `nonexistentuser` (invalid)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Create Account"
4. Verify database:

```sql
SELECT id, username, email, sponsor_id, email_verified_at
FROM users
WHERE username = 'charliebrown';
```

**Expected Results**:

-   ✅ Registration **fails** with validation error
-   ✅ Error message displays: "The sponsor 'nonexistentuser' could not be found. Please check the username, referral code, or full name."
-   ✅ Error appears under sponsor name field with red styling
-   ✅ User is **NOT** created in database (query returns no rows)
-   ✅ Form retains entered values (except passwords for security)
-   ✅ System does **NOT** silently default to admin sponsor
-   ✅ No email verification sent (since user not created)

**Pass Criteria**:

-   ✅ Invalid sponsor name causes registration failure
-   ✅ Clear validation error message guides user to fix the issue
-   ✅ No user created with invalid sponsor
-   ✅ User can correct sponsor name and retry registration
-   ✅ Email verification only triggered after successful registration

**Screenshot Required**:

-   ✅ Validation error message under sponsor field
-   ✅ Form showing error state with retained values
-   ✅ Database query showing no user created

---

### Test Case 2.6: Profile Email Management with Automatic Verification

**Objective**: Verify users can add/update email in profile with automatic verification email sending

**Steps**:

1. Register a new user WITHOUT email:
    - Username: `testuser`
    - Email: (leave blank)
    - Password: Test123!@#
2. Login as `testuser`
3. Navigate to `/profile`
4. Observe email field:
    - Should show "Email Address (Optional)"
    - Should have placeholder: "your.email@example.com"
    - Should show helper text: "Add an email to receive notifications. A verification email will be sent automatically."
    - **No "Verify Email" button** (no email present)
5. Add email: `testuser@example.com`
6. Click "Save Changes"
7. Observe success message and profile page after save:
    - Success message: "Email address updated successfully. A verification email has been sent to testuser@example.com."
    - Top alert shows: "Email address is not verified. Email verification is optional."
    - Email field shows warning icon and text: "Your email address is not verified. Email verification is optional."
    - **No "Verify Email" button** - verification email already sent automatically
8. Check email inbox for verification email (automatic)
9. Check database:

```sql
SELECT username, email, email_verified_at
FROM users
WHERE username = 'testuser';
```

**Expected Results**:

-   ✅ Email field clearly marked as optional
-   ✅ Helper text indicates automatic verification: "A verification email will be sent automatically"
-   ✅ Email successfully updated
-   ✅ `email_verified_at` = NULL (not verified yet)
-   ✅ Success message: "Email address updated successfully. A verification email has been sent to testuser@example.com."
-   ✅ **Verification email sent automatically** - no manual button needed
-   ✅ **No "Verify Email" button** displayed (all automatic)
-   ✅ Alert message shows: "Your email address is not verified. Email verification is optional."

**Additional Test - After Email Verification**: 10. Click verification link from email (sent automatically) 11. Return to `/profile` 12. Observe email section: - Top alert changed to: "Email Verified" (green success alert) - Email field shows green checkmark and text: "Your email address is verified." - Still **no "Verify Email" button** (not needed) 13. Check database:

```sql
SELECT username, email, email_verified_at
FROM users
WHERE username = 'testuser';
```

14. Expected: `email_verified_at` has timestamp (not NULL)

**Additional Test - Email Update (Change Email)**: 15. Change email from `testuser@example.com` to `newemail@example.com` 16. Click "Save Changes" 17. Verify: - Success message: "Email address updated successfully. A verification email has been sent to newemail@example.com." - **New verification email sent automatically** to new address - `email_verified_at` reset to NULL (new email unverified) - Alert shows unverified status again

**Pass Criteria**:

-   ✅ Users can add email after registration
-   ✅ **Verification email sent automatically** when email is added
-   ✅ **Verification email sent automatically** when email is changed
-   ✅ **No manual "Verify Email" button** - all automatic
-   ✅ Alert message changes based on verification status
-   ✅ Clear user feedback provided at each step
-   ✅ Success messages confirm email was sent

**Screenshot Required**:

-   ✅ Profile page with no email (showing automatic helper text)
-   ✅ Success message after adding email (showing verification email sent)
-   ✅ Profile page showing unverified status (no button, just status indicators)
-   ✅ Profile page after email verification (showing verified status)

---

### Test Case 2.7: Profile Email Removal

**Objective**: Verify users can remove email from their profile

**Steps**:

1. Login as user with email (e.g., `testuser@example.com`)
2. Navigate to `/profile`
3. Clear the email field (make it blank)
4. Click "Save Changes"
5. Verify database:

```sql
SELECT username, email, email_verified_at
FROM users
WHERE username = 'testuser';
```

**Expected Results**:

-   ✅ Email successfully removed
-   ✅ `email` = NULL
-   ✅ `email_verified_at` = NULL
-   ✅ Success message: "Email address removed successfully."
-   ✅ User can still login with username

**Pass Criteria**:

-   ✅ Email removal works correctly
-   ✅ User account remains functional without email

**Screenshot Required**: ✅ Profile after email removal

---

### Test Case 2.8: Member Registration (Logged-in User Registers Another User)

**Objective**: Verify logged-in users can register new members with flexible sponsor assignment

**Steps**:

1. Login as any user (e.g., `admin` / `Admin123!@#`)
2. Navigate to dashboard sidebar
3. Locate "Register New Member" link under "Member Actions" section
4. Click to navigate to `/register-member`
5. Verify **Sponsor Name/Username field** is displayed at the top of the form
6. Verify field is pre-filled with logged-in user's username (e.g., `admin`)
7. Verify helper text shows: "Default: Admin (admin). You can change this to assign a different sponsor."
8. Fill in registration form (using default sponsor):
    - **Sponsor Name/Username**: admin (leave as default)
    - **Full Name**: David Miller
    - **Username**: davidmiller
    - **Email**: davidmiller@test.com (OPTIONAL - can be left blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
9. Click "Register New Member"
10. Verify success message appears with new member's details
11. Check database:

```sql
SELECT id, username, email, sponsor_id, referral_code
FROM users
WHERE username = 'davidmiller';
```

**Expected Results**:

-   ✅ "Register New Member" link visible in sidebar under "Member Actions" section
-   ✅ Page displays with sidebar and header (consistent admin layout)
-   ✅ **Sponsor Name/Username field** editable and pre-filled with logged-in user's username
-   ✅ Helper text shows default sponsor with full name and username
-   ✅ User successfully registered
-   ✅ `sponsor_id` = ID of logged-in user (e.g., 1 if admin) when using default
-   ✅ `referral_code` generated automatically (format: REF[8 chars])
-   ✅ Success message: "Member 'David Miller' has been registered successfully! Username: davidmiller"
-   ✅ Email is optional (can be left blank)
-   ✅ If email provided: `email_verified_at` = NULL (pending verification)
-   ✅ If email blank: `email` = NULL, `email_verified_at` = NULL
-   ✅ Form remains on same page ready for another registration
-   ✅ No "Back to Dashboard" button (uses sidebar navigation)

**Pass Criteria**:

-   ✅ Sidebar link accessible to all logged-in users
-   ✅ Sponsor field editable (can be changed to any valid username)
-   ✅ Sponsor defaults to logged-in user when field is empty or unchanged
-   ✅ New user created successfully
-   ✅ Unique referral code generated
-   ✅ No validation errors
-   ✅ Email remains optional
-   ✅ Success message displays clearly
-   ✅ Page maintains consistent admin layout with sidebar/header

**Screenshot Required**:

-   ✅ Sidebar showing "Register New Member" link
-   ✅ Registration form with editable sponsor field
-   ✅ Success message after registration

---

### Test Case 2.9: Multiple Members Registration by Same Sponsor

**Objective**: Verify a single user can register multiple downline members

**Steps**:

1. Login as `member` user
2. Navigate to `/register-member`
3. Register first downline member:
    - **Full Name**: Test Member 1
    - **Username**: testmember1
    - **Email**: (leave blank)
    - **Password**: Test123!@#
4. Verify success message appears
5. Register second downline member (form should be ready):
    - **Full Name**: Test Member 2
    - **Username**: testmember2
    - **Email**: testmember2@test.com
    - **Password**: Test123!@#
6. Register third downline member:
    - **Full Name**: Test Member 3
    - **Username**: testmember3
    - **Email**: (leave blank)
    - **Password**: Test123!@#
7. Check database:

```sql
SELECT id, username, email, sponsor_id, referral_code
FROM users
WHERE username IN ('testmember1', 'testmember2', 'testmember3')
ORDER BY id;
```

**Expected Results**:

-   ✅ All 3 members successfully registered
-   ✅ All have `sponsor_id` = 2 (member user's ID)
-   ✅ Each has unique `referral_code`
-   ✅ Mix of with/without email works correctly
-   ✅ Success message appears after each registration
-   ✅ Form remains accessible for next registration

**Pass Criteria**:

-   ✅ Multiple registrations by same sponsor work smoothly
-   ✅ All sponsor relationships correct
-   ✅ Workflow efficient for bulk registration

**Screenshot Required**: ✅ Database query showing all 3 members with correct sponsor_id

---

### Test Case 2.10: Member Registration with Sponsor Override

**Objective**: Verify user can change the sponsor to register member under a different upline

**Steps**:

1. Login as `admin` user
2. Navigate to `/register-member`
3. Verify sponsor field is pre-filled with `admin`
4. **Change sponsor field** to `member` (another existing user)
5. Fill in registration form:
    - **Sponsor Name/Username**: member (changed from admin)
    - **Full Name**: Sarah Johnson
    - **Username**: sarahjohnson
    - **Email**: (leave blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
6. Click "Register New Member"
7. Verify success message appears
8. Check database:

```sql
SELECT u.id, u.username, u.sponsor_id, s.username as sponsor_username
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'sarahjohnson';
```

**Expected Results**:

-   ✅ Sponsor field is editable (not locked)
-   ✅ Changed sponsor value is accepted
-   ✅ User successfully registered
-   ✅ `sponsor_id` = ID of 'member' user (NOT admin)
-   ✅ Database shows correct sponsor relationship
-   ✅ Success message displays

**Pass Criteria**:

-   ✅ Sponsor override works correctly
-   ✅ Sponsor assignment follows the changed value
-   ✅ No errors when using different sponsor
-   ✅ Allows flexibility for network building strategies

**Screenshot Required**:

-   ✅ Form with changed sponsor field
-   ✅ Database query showing correct sponsor_id

---

### Test Case 2.11: Invalid Sponsor Name Validation

**Objective**: Verify system shows validation error for invalid sponsor names

**Steps**:

1. Navigate to `/register` (public registration)
2. Fill in registration form:
    - **Full Name**: Test User
    - **Username**: testuser123
    - **Email**: (leave blank)
    - **Sponsor Name/Username**: invalidusername123 (non-existent sponsor)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Register"
4. Verify validation error is displayed

**Expected Results**:

-   ✅ Registration fails with validation error
-   ✅ Error message displays: "The sponsor 'invalidusername123' could not be found. Please check the username, referral code, or full name."
-   ✅ Error appears under sponsor name field
-   ✅ User is NOT created in database
-   ✅ Form retains entered values (except password)
-   ✅ System does NOT silently default to admin sponsor

**Pass Criteria**:

-   ✅ Invalid sponsor name is properly validated
-   ✅ Clear error message guides user to correct the issue
-   ✅ No user created with invalid sponsor
-   ✅ Same validation applies to `/register-member` route

**Screenshot Required**:

-   ✅ Validation error message displayed
-   ✅ Form with error state

---

### Test Case 2.12: Empty Sponsor Name Defaults to Admin

**Objective**: Verify empty sponsor field defaults to admin sponsor

**Steps**:

1. Navigate to `/register`
2. Fill in registration form:
    - **Full Name**: Default Sponsor Test
    - **Username**: defaultsponsortest
    - **Email**: (leave blank)
    - **Sponsor Name/Username**: (leave completely blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Register"
4. Verify user is created successfully
5. Check database:

```sql
SELECT u.id, u.username, u.sponsor_id, s.username as sponsor_username
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'defaultsponsortest';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = ID of admin user (email: admin@gawisherbal.com)
-   ✅ Database shows sponsor_username = 'admin'
-   ✅ Success message displays
-   ✅ No validation errors

**Pass Criteria**:

-   ✅ Empty sponsor field triggers admin default
-   ✅ Default behavior only applies when field is blank/empty
-   ✅ Admin fallback works correctly

**Screenshot Required**: ✅ Database query showing admin as sponsor

---

### Test Case 2.13: Member Registration Access Control

**Objective**: Verify only logged-in users can access member registration

**Steps**:

1. Logout completely
2. Attempt to access `/register-member` directly via URL
3. Verify redirect behavior

**Expected Results**:

-   ✅ Redirected to login page
-   ✅ Cannot access registration page without authentication
-   ✅ After login, can access `/register-member` successfully

**Pass Criteria**:

-   ✅ Proper authentication required
-   ✅ No unauthorized access possible

**Screenshot Required**: ✅ Redirect to login when accessing while logged out

---

## Test Suite 3: Admin MLM Settings Interface

### Test Case 3.1: Access MLM Settings Page

**Objective**: Verify admin can access MLM settings for Starter Package

**Steps**:

1. Login as admin: `admin@gawisherbal.com` / `Admin123!@#`
2. Navigate to `/admin/packages`
3. Locate "Starter Package" in the list
4. Click "Edit" button or navigate directly to `/admin/packages/starter-package/mlm-settings`

**Expected Results**:

-   ✅ MLM Settings page loads successfully
-   ✅ Page title: "MLM Settings: Starter Package"
-   ✅ Subtitle: "Configure 5-level commission structure"
-   ✅ "Back to Packages" button visible
-   ✅ Package price displayed: ₱1,000.00

**Pass Criteria**:

-   ✅ Page accessible without errors
-   ✅ Proper authorization (admin role required)

**Screenshot Required**: ✅ Full MLM Settings page view

---

### Test Case 3.2: Verify Default MLM Commission Values

**Objective**: Ensure MLM settings display correctly with seeded data

**Steps**:

1. On MLM Settings page, verify the commission table displays:

**Expected Table Display**:

| Level       | Description      | Commission (₱) | Percentage | Active |
| ----------- | ---------------- | -------------- | ---------- | ------ |
| **Level 1** | Direct Referrals | 200.00         | 20.00%     | ☑️     |
| **Level 2** | Indirect Level 2 | 50.00          | 5.00%      | ☑️     |
| **Level 3** | Indirect Level 3 | 50.00          | 5.00%      | ☑️     |
| **Level 4** | Indirect Level 4 | 50.00          | 5.00%      | ☑️     |
| **Level 5** | Indirect Level 5 | 50.00          | 5.00%      | ☑️     |

**Table Footer**:

-   Total MLM Commission: ₱400.00 (40.00%)
-   Company Profit (60% target): ₱600.00 (60.00%)

**Sidebar Summary**:

-   Package Price: ₱1,000.00
-   Total Commission: ₱400.00 (40.00% of price)
-   Company Profit: ₱600.00 (60.00% margin)

**Expected Results**:

-   ✅ All 5 levels displayed
-   ✅ Level 1 shows "Direct Referrals" badge (green)
-   ✅ Levels 2-5 show "Indirect Level X" badges (blue)
-   ✅ Commission amounts match database values
-   ✅ Percentages calculated correctly
-   ✅ All levels marked as active (checkboxes checked)
-   ✅ Total commission = ₱400 (40%)
-   ✅ Company profit = ₱600 (60%)
-   ✅ Warning message: "Total commission should not exceed 40% of package price"

**Pass Criteria**:

-   ✅ All values accurate
-   ✅ Calculations correct
-   ✅ Visual hierarchy clear

**Screenshot Required**:

-   ✅ Commission table with all 5 levels
-   ✅ Table footer with totals
-   ✅ Sidebar summary

---

### Test Case 3.3: Update MLM Commission Amounts (Valid)

**Objective**: Verify admin can update commission structure within valid limits

**Steps**:

1. On MLM Settings page, modify commission values:
    - **Level 1**: Change from 200.00 to **250.00**
    - **Level 2**: Change from 50.00 to **30.00**
    - **Level 3**: Keep at 50.00
    - **Level 4**: Keep at 50.00
    - **Level 5**: Change from 50.00 to **20.00**
2. **Observe real-time calculations**:
    - Total should update to ₱400.00 (250 + 30 + 50 + 50 + 20)
    - Company profit should update to ₱600.00
    - Percentages should recalculate
3. Click "Save MLM Settings"
4. Verify success message appears
5. Refresh page and verify changes persisted
6. Check database:

```sql
SELECT level, commission_amount
FROM mlm_settings
WHERE package_id = (SELECT id FROM packages WHERE slug = 'starter-package')
ORDER BY level;
```

**Expected Results**:

-   ✅ Real-time calculation updates as you type
-   ✅ Total commission: ₱400.00 (40%)
-   ✅ Company profit: ₱600.00 (60%)
-   ✅ No validation errors (within 40% limit)
-   ✅ Success message: "MLM settings updated successfully!"
-   ✅ Database reflects new values
-   ✅ Sidebar summary updates in real-time

**Pass Criteria**:

-   ✅ Changes saved successfully
-   ✅ Real-time calculations accurate
-   ✅ Database updated correctly

**Screenshot Required**:

-   ✅ Before update (original values)
-   ✅ During update (showing real-time calculation)
-   ✅ Success message after save
-   ✅ After page refresh (persistence check)

---

### Test Case 3.4: Attempt Invalid Update (Exceeds 40% Limit)

**Objective**: Verify validation prevents commission total from exceeding 40% limit

**Steps**:

1. On MLM Settings page, modify commission values to exceed limit:
    - **Level 1**: Change to **300.00**
    - **Level 2**: Change to **100.00**
    - **Level 3**: Keep at 50.00
    - **Level 4**: Keep at 50.00
    - **Level 5**: Keep at 50.00
2. **Observe visual feedback**:
    - Total should show ₱550.00 (55%)
    - Total row should turn red (table-danger)
    - Total amount should display in red text
3. Click "Save MLM Settings"
4. Verify error message appears

**Expected Results**:

-   ✅ Real-time visual warning (red highlighting)
-   ✅ Total commission: ₱550.00 (55%)
-   ✅ Company profit: ₱450.00 (45%)
-   ✅ Error message displayed: "Total MLM commission (₱550.00) exceeds 40% of package price (₱400.00)"
-   ✅ Changes NOT saved to database
-   ✅ Form returns with original values or input values preserved

**Pass Criteria**:

-   ✅ Validation works correctly
-   ✅ Clear error message
-   ✅ Database remains unchanged
-   ✅ Visual feedback helps user understand issue

**Screenshot Required**:

-   ✅ Form with excessive values (red highlighting)
-   ✅ Error message after save attempt

---

### Test Case 3.5: Toggle Commission Level Active/Inactive

**Objective**: Verify admin can enable/disable specific commission levels

**Steps**:

1. On MLM Settings page, uncheck the "Active" checkbox for **Level 5**
2. **Observe**: Total should recalculate to exclude Level 5
    - New total: ₱350.00 (if using default values: 200 + 50 + 50 + 50)
3. Click "Save MLM Settings"
4. Verify success message
5. Check database:

```sql
SELECT level, commission_amount, is_active
FROM mlm_settings
WHERE package_id = (SELECT id FROM packages WHERE slug = 'starter-package')
AND level = 5;
```

6. Refresh page and verify Level 5 checkbox is unchecked

**Expected Results**:

-   ✅ Checkbox toggle works smoothly
-   ✅ Total recalculates when toggling (optional feature)
-   ✅ Success message: "MLM settings updated successfully!"
-   ✅ Database `is_active` = 0 for Level 5
-   ✅ After refresh, Level 5 remains unchecked

**Pass Criteria**:

-   ✅ Toggle functionality works
-   ✅ Database updated correctly
-   ✅ Changes persist after page refresh

**Screenshot Required**:

-   ✅ Level 5 checkbox unchecked before save
-   ✅ After refresh showing persisted state

---

### Test Case 3.6: Verify Real-Time Calculation Accuracy

**Objective**: Ensure JavaScript calculations match server-side calculations

**Test Data**: Try multiple scenarios:

**Scenario A**: Equal distribution

-   Level 1: 80.00
-   Level 2: 80.00
-   Level 3: 80.00
-   Level 4: 80.00
-   Level 5: 80.00
-   **Expected Total**: ₱400.00 (40%)
-   **Expected Profit**: ₱600.00 (60%)

**Scenario B**: Front-loaded commissions

-   Level 1: 300.00
-   Level 2: 25.00
-   Level 3: 25.00
-   Level 4: 25.00
-   Level 5: 25.00
-   **Expected Total**: ₱400.00 (40%)
-   **Expected Profit**: ₱600.00 (60%)

**Scenario C**: Minimal commissions

-   Level 1: 100.00
-   Level 2: 10.00
-   Level 3: 10.00
-   Level 4: 10.00
-   Level 5: 10.00
-   **Expected Total**: ₱140.00 (14%)
-   **Expected Profit**: ₱860.00 (86%)

**Steps for Each Scenario**:

1. Enter commission values
2. **Verify real-time updates**:
    - Total commission amount
    - Total commission percentage
    - Company profit amount
    - Company profit percentage
    - Individual level percentages
    - Sidebar summary values
3. Click "Save" and verify server accepts the values
4. Refresh and verify persistence

**Pass Criteria**:

-   ✅ All calculations accurate to 2 decimal places
-   ✅ Real-time updates responsive (no lag)
-   ✅ Server-side validation matches client-side
-   ✅ All scenarios save successfully

**Screenshot Required**: ✅ One scenario showing calculations

---

## Test Suite 4: Package Management Integration

### Test Case 4.1: Verify Starter Package MLM Properties

**Objective**: Ensure Starter Package has correct MLM flags

**Steps**:

1. Login as admin
2. Navigate to `/admin/packages`
3. Find "Starter Package" in the list
4. Click "Edit" to view package details
5. Verify package properties

**Expected Results**:

-   ✅ Package name: "Starter Package"
-   ✅ Price: ₱1,000.00
-   ✅ `is_mlm_package` = true (or checkbox checked)
-   ✅ `max_mlm_levels` = 5
-   ✅ Metadata includes:
    -   `total_commission`: 400.00
    -   `company_profit`: 600.00
    -   `profit_margin`: "60%"

**Pass Criteria**:

-   ✅ MLM properties correctly set
-   ✅ Metadata accurate

**Screenshot Required**: ✅ Package edit page showing MLM properties

---

### Test Case 4.2: Access MLM Settings from Package List

**Objective**: Verify easy navigation to MLM settings from package management

**Steps**:

1. Navigate to `/admin/packages`
2. Locate "Starter Package"
3. Look for a link/button to access MLM settings (may need to add UI element)
4. Click to navigate to MLM settings

**Expected Results**:

-   ✅ Clear link/button available (e.g., "MLM Settings" button)
-   ✅ Navigates to `/admin/packages/starter-package/mlm-settings`

**Pass Criteria**:

-   ✅ Easy access from package list

**Note**: If no UI element exists, this is a UX improvement recommendation

**Screenshot Required**: ✅ Package list showing navigation element

---

## Test Suite 5: User Experience & UI/UX Validation

### Test Case 5.1: Registration Form UX

**Objective**: Verify registration form provides clear guidance

**Checklist**:

-   ✅ Sponsor field clearly labeled
-   ✅ Placeholder text helpful: "Sponsor Name or Referral Code (Optional)"
-   ✅ Helper text visible: "Leave blank to be assigned to default sponsor (Admin)"
-   ✅ Referral code alert appears when URL has ?ref parameter
-   ✅ Referral code alert is visually distinct (blue/info styling)
-   ✅ All form fields properly aligned
-   ✅ Form responsive on mobile devices
-   ✅ Icons display correctly for all fields
-   ✅ Validation messages clear and helpful

**Pass Criteria**:

-   ✅ Form intuitive for new users
-   ✅ No confusing or missing instructions

**Screenshot Required**:

-   ✅ Desktop view
-   ✅ Mobile view
-   ✅ With referral code alert

---

### Test Case 5.2: MLM Settings Page UX

**Objective**: Verify MLM settings interface is professional and intuitive

**Checklist**:

-   ✅ Page layout clean and organized
-   ✅ Table readable with proper spacing
-   ✅ Commission inputs easy to modify
-   ✅ Percentage displays update in real-time
-   ✅ Badge colors appropriate (green for direct, blue for indirect)
-   ✅ Warning message clearly visible
-   ✅ Sidebar summary helpful and not cluttered
-   ✅ Save button prominent
-   ✅ Back button easily accessible
-   ✅ Responsive design works on tablet/mobile
-   ✅ Number inputs allow decimals
-   ✅ Active/Inactive toggles easy to use

**Pass Criteria**:

-   ✅ Professional appearance
-   ✅ Intuitive workflow
-   ✅ No usability issues

**Screenshot Required**:

-   ✅ Full page view (desktop)
-   ✅ Tablet view
-   ✅ Mobile view

---

### Test Case 5.3: Error Handling & User Feedback

**Objective**: Verify system provides appropriate feedback

**Test Scenarios**:

**A. Successful Save**:

-   Action: Update MLM settings with valid values
-   Expected: Green success alert, message stays visible
-   ✅ Pass/Fail: **\_\_\_**

**B. Validation Error**:

-   Action: Try to save with total > 40%
-   Expected: Red error alert, specific error message, input values preserved
-   ✅ Pass/Fail: **\_\_\_**

**C. Database Error** (simulate by modifying database connection):

-   Action: Try to save with database offline
-   Expected: Error message explaining issue
-   ✅ Pass/Fail: **\_\_\_**

**D. Unauthorized Access**:

-   Action: Logout and try to access `/admin/packages/starter-package/mlm-settings`
-   Expected: Redirect to login page
-   ✅ Pass/Fail: **\_\_\_**

**E. Non-MLM Package**:

-   Action: Try to access MLM settings for non-MLM package (if one exists)
-   Expected: Redirect with error message
-   ✅ Pass/Fail: **\_\_\_**

**Pass Criteria**:

-   ✅ All scenarios handled gracefully
-   ✅ User always knows what happened

---

## Test Suite 6: Data Integrity & Edge Cases

### Test Case 6.1: Referral Code Uniqueness

**Objective**: Ensure referral codes are always unique

**Steps**:

1. Create 10 new users via registration
2. Extract all referral codes:

```sql
SELECT referral_code, COUNT(*) as count
FROM users
GROUP BY referral_code
HAVING count > 1;
```

**Expected Results**:

-   ✅ Query returns 0 rows (no duplicates)
-   ✅ All referral codes follow REF[8 chars] format
-   ✅ All codes are uppercase

**Pass Criteria**:

-   ✅ 100% uniqueness guaranteed

---

### Test Case 6.2: Sponsor Relationship Integrity

**Objective**: Verify sponsor relationships cannot be circular

**Steps**:

1. Attempt to create circular reference manually in database:

```sql
-- This should fail due to foreign key constraints
UPDATE users SET sponsor_id = 2 WHERE id = 1;
UPDATE users SET sponsor_id = 1 WHERE id = 2;
```

**Expected Results**:

-   ✅ Database prevents circular references
-   ✅ Foreign key constraint works correctly

**Pass Criteria**:

-   ✅ Circular references impossible

---

### Test Case 6.3: Wallet Balance Segregation Integrity

**Objective**: Ensure MLM and purchase balances remain separate

**Prerequisites**:

-   ✅ Migration `2025_10_06_173759_add_mlm_commission_type_to_transactions_table.php` must be run
-   ✅ `mlm_commission` transaction type added to transactions table enum

**Steps**:

1. Create test transaction to add to MLM balance:

```sql
-- Note: Use user_id, not wallet_id (transactions table uses user_id)
INSERT INTO transactions (user_id, type, amount, description, status, metadata, created_at, updated_at)
VALUES (1, 'mlm_commission', 100.00, 'Test MLM Income', 'completed', '{"level":1}', NOW(), NOW());

-- Update wallet by user_id for consistency
UPDATE wallets SET mlm_balance = mlm_balance + 100.00 WHERE user_id = 1;
```

2. Verify balances:

```sql
SELECT
    user_id,
    mlm_balance,
    purchase_balance,
    (mlm_balance + purchase_balance) as total_available
FROM wallets WHERE user_id = 1;
```

3. Verify transaction was recorded:

```sql
SELECT id, user_id, type, amount, description, status
FROM transactions
WHERE user_id = 1 AND type = 'mlm_commission'
ORDER BY id DESC LIMIT 1;
```

**Expected Results**:

-   ✅ `mlm_balance` increased by 100.00 (now 100.00)
-   ✅ `purchase_balance` unchanged (remains 1000.00)
-   ✅ `total_available` = 1100.00 (mlm_balance + purchase_balance)
-   ✅ Transaction recorded with type `mlm_commission`

**Pass Criteria**:

-   ✅ Balances properly segregated
-   ✅ No cross-contamination between MLM and purchase balances
-   ✅ Transaction correctly linked to user via `user_id`
-   ✅ MLM commission transaction type properly stored

**Important Notes**:

-   **Column Name**: Transactions table uses `user_id`, NOT `wallet_id`
-   **Transaction Type**: `mlm_commission` must be in the type enum
-   **Wallet Lookup**: Wallets can be queried by `user_id` or `id`
-   **1:1 Relationship**: Each user has exactly one wallet

---

### Test Case 6.4: Commission Structure Update Impact

**Objective**: Verify changing MLM settings doesn't affect existing data

**Steps**:

1. Note current MLM settings
2. Update Level 1 commission from ₱200 to ₱250
3. Verify package metadata updated
4. Check that no other data changed:

```sql
-- Ensure no historical commissions were modified
SELECT COUNT(*) FROM transactions WHERE type = 'mlm_commission';
-- Should be 0 since no commissions have been paid yet
```

**Expected Results**:

-   ✅ MLM settings updated
-   ✅ Package metadata updated
-   ✅ No impact on existing transactions (if any)

**Pass Criteria**:

-   ✅ Updates only affect future commissions
-   ✅ Historical data preserved

---

## Test Suite 7: Security & Authorization

### Test Case 7.1: MLM Settings Access Control

**Objective**: Verify only admins can access MLM settings

**Test Matrix**:

| User Type     | Action                                                | Expected Result           |
| ------------- | ----------------------------------------------------- | ------------------------- |
| Not Logged In | Access `/admin/packages/starter-package/mlm-settings` | Redirect to login         |
| Member User   | Access MLM settings URL                               | 403 Forbidden or redirect |
| Admin User    | Access MLM settings URL                               | ✅ Page loads             |
| Not Logged In | POST to update endpoint                               | 401/403 error             |
| Member User   | POST to update endpoint                               | 403 Forbidden             |
| Admin User    | POST to update endpoint                               | ✅ Update succeeds        |

**Steps for Each**:

1. Login as specified user (or don't login)
2. Navigate to MLM settings page
3. Verify access result

**Pass Criteria**:

-   ✅ All unauthorized access blocked
-   ✅ Only admins can view and modify

**Screenshot Required**: ✅ Access denied page for member user

---

### Test Case 7.2: CSRF Protection

**Objective**: Verify CSRF tokens protect update endpoints

**Steps**:

1. Open browser developer tools
2. Navigate to MLM settings page
3. Inspect form HTML
4. Verify `@csrf` token present
5. Try to submit form via curl without token:

```bash
curl -X PUT http://coreui_laravel_deploy.test/admin/packages/starter-package/mlm-settings \
  -d "settings[1][level]=1&settings[1][commission_amount]=200"
```

**Expected Results**:

-   ✅ CSRF token present in form
-   ✅ Curl request fails with 419 error (CSRF token mismatch)

**Pass Criteria**:

-   ✅ CSRF protection active

---

### Test Case 7.3: SQL Injection Prevention

**Objective**: Verify input sanitization prevents SQL injection

**Steps**:

1. On MLM settings page, try malicious input:
    - Commission amount: `200'; DROP TABLE mlm_settings;--`
2. Click "Save"
3. Check database:

```sql
SHOW TABLES LIKE 'mlm_settings';
```

**Expected Results**:

-   ✅ Validation error (non-numeric input)
-   ✅ Table still exists
-   ✅ No SQL injection successful

**Pass Criteria**:

-   ✅ Input validation prevents injection

---

## Test Suite 8: Performance & Scalability

### Test Case 8.1: Page Load Performance

**Objective**: Verify MLM settings page loads quickly

**Steps**:

1. Open browser developer tools (Network tab)
2. Navigate to MLM settings page
3. Measure total load time

**Expected Results**:

-   ✅ Page loads in < 2 seconds (first load)
-   ✅ Page loads in < 1 second (cached)
-   ✅ No excessive database queries (check Laravel Debugbar if installed)

**Pass Criteria**:

-   ✅ Acceptable performance

---

### Test Case 8.2: Real-Time Calculation Performance

**Objective**: Verify JavaScript calculations don't lag

**Steps**:

1. On MLM settings page, rapidly change commission values
2. Observe calculation updates

**Expected Results**:

-   ✅ Updates appear instant (< 100ms delay)
-   ✅ No UI freezing or lag
-   ✅ All values update simultaneously

**Pass Criteria**:

-   ✅ Smooth user experience

---

## Test Suite 9: Browser Compatibility

### Test Case 9.1: Cross-Browser Testing

**Objective**: Verify system works across major browsers

**Test Matrix**:

| Browser | Version | Registration | MLM Settings | Pass/Fail |
| ------- | ------- | ------------ | ------------ | --------- |
| Chrome  | Latest  | ⬜           | ⬜           | ⬜        |
| Firefox | Latest  | ⬜           | ⬜           | ⬜        |
| Safari  | Latest  | ⬜           | ⬜           | ⬜        |
| Edge    | Latest  | ⬜           | ⬜           | ⬜        |

**For Each Browser**:

1. Test user registration with referral code
2. Test MLM settings page functionality
3. Verify real-time calculations work
4. Check for console errors

**Pass Criteria**:

-   ✅ All features work in all browsers
-   ✅ No critical console errors

---

## Test Suite 10: Mobile Responsiveness

### Test Case 10.1: Mobile Registration

**Objective**: Verify registration works on mobile devices

**Steps**:

1. Open site on mobile device or use browser dev tools mobile emulation
2. Navigate to `/register`
3. Fill out registration form
4. Submit

**Checklist**:

-   ✅ All fields visible and accessible
-   ✅ Sponsor field not cut off
-   ✅ Keyboard doesn't obscure submit button
-   ✅ Form submission works
-   ✅ Success/error messages visible

**Pass Criteria**:

-   ✅ Full functionality on mobile

**Screenshot Required**: ✅ Mobile registration view

---

### Test Case 10.2: Mobile MLM Settings (Tablet)

**Objective**: Verify MLM settings usable on tablet

**Steps**:

1. Open site on tablet (or iPad emulation)
2. Navigate to MLM settings
3. Try to update commission values

**Checklist**:

-   ✅ Table readable (may scroll horizontally)
-   ✅ Input fields accessible
-   ✅ Sidebar visible or collapses appropriately
-   ✅ Save button accessible
-   ✅ Real-time calculations work

**Pass Criteria**:

-   ✅ Usable on tablet devices

**Screenshot Required**: ✅ Tablet view of MLM settings

---

## Summary & Sign-Off

### Phase 1 Test Results Summary

**Total Test Cases**: 48+ (updated to include optional email, member registration, and sponsor validation)
**Tests Passed**: **\_\_\_**
**Tests Failed**: **\_\_\_**
**Critical Issues**: **\_\_\_**
**Minor Issues**: **\_\_\_**
**Recommendations**: **\_\_\_**

### Key Changes from Original Plan:

1. **Optional Email Registration**: Users can now register without an email address
2. **Email Verification**: Only triggered if email is provided during registration
3. **Email Verification Fix**: Fortify email verification enabled with custom logic for users without email
4. **Profile Email Management**: Users can add, update, or remove email from their profile
5. **Login Flexibility**: Users can login with username or email
6. **Database Changes**: `email` column is now nullable with application-level uniqueness validation
7. **Member Registration**: Logged-in users can register new members via `/register-member`
8. **Flexible Sponsor Assignment**: Editable sponsor field pre-filled with logged-in user (positioned after email)
9. **Sponsor Validation**: Invalid sponsor names show validation errors (not silently defaulted to admin)
10. **Sidebar Navigation**: "Register New Member" link added to Member Actions section for easy access
11. **Sponsor Override**: Users can register members under different sponsors for strategic network building

---

### Critical Issues Found

(List any critical bugs that prevent system functionality)

1. ***
2. ***
3. ***

---

### Minor Issues / UX Improvements

(List non-critical issues or suggested improvements)

1. ***
2. ***
3. ***

---

### Sign-Off

**Tester Name**: ****\*\*\*\*****\_\_\_****\*\*\*\*****
**Date**: ****\*\*\*\*****\_\_\_****\*\*\*\*****
**Status**: ⬜ Approved for Production ⬜ Requires Fixes ⬜ Needs Re-testing

**Developer Notes**:

---

---

---

---

## Appendix A: Test Data Reference

### Default Users After Database Reset

| ID  | Username | Email                  | Role   | Sponsor | Referral Code | Purchase Balance |
| --- | -------- | ---------------------- | ------ | ------- | ------------- | ---------------- |
| 1   | admin    | admin@gawisherbal.com  | admin  | None    | REF[8chars]   | ₱1,000           |
| 2   | member   | member@gawisherbal.com | member | Admin   | REF[8chars]   | ₱1,000           |

### Default MLM Settings

| Level     | Commission  | Percentage | Description        |
| --------- | ----------- | ---------- | ------------------ |
| 1         | ₱200.00     | 20%        | Direct Referrals   |
| 2         | ₱50.00      | 5%         | 2nd Level Indirect |
| 3         | ₱50.00      | 5%         | 3rd Level Indirect |
| 4         | ₱50.00      | 5%         | 4th Level Indirect |
| 5         | ₱50.00      | 5%         | 5th Level Indirect |
| **Total** | **₱400.00** | **40%**    | -                  |

### Starter Package Details

-   **Name**: Starter Package
-   **Slug**: starter-package
-   **Price**: ₱1,000.00
-   **MLM Enabled**: Yes
-   **Max Levels**: 5
-   **Company Profit**: ₱600.00 (60%)

---

## Appendix B: SQL Queries for Testing

### Check All MLM Data

```sql
-- View all MLM settings with package info
SELECT
    p.name as package_name,
    p.price,
    ms.level,
    ms.commission_amount,
    ms.is_active,
    CONCAT(ROUND((ms.commission_amount / p.price) * 100, 2), '%') as percentage
FROM mlm_settings ms
JOIN packages p ON ms.package_id = p.id
ORDER BY p.id, ms.level;
```

### View Sponsor Network

```sql
-- View user sponsor relationships (tree structure)
SELECT
    u1.id,
    u1.username,
    u1.referral_code,
    u1.sponsor_id,
    u2.username as sponsor_username,
    u2.referral_code as sponsor_ref_code
FROM users u1
LEFT JOIN users u2 ON u1.sponsor_id = u2.id
ORDER BY u1.id;
```

### Check Wallet Balances

```sql
-- View all wallet balances
SELECT
    u.username,
    w.balance as legacy,
    w.mlm_balance,
    w.purchase_balance,
    (w.mlm_balance + w.purchase_balance) as total
FROM users u
JOIN wallets w ON u.id = w.user_id
ORDER BY u.id;
```

---

**End of Phase 1 Testing Documentation**

---

## Next Steps After Phase 1

After completing Phase 1 testing:

1. Address all critical issues found
2. Consider UX improvements from feedback
3. Proceed to Phase 2: Referral Link System implementation
4. Continue testing with Phase 2 test cases (see below)

---

# Phase 2: Referral Link System & Auto-Fill Sponsor

**Status**: Ready for Testing
**Estimated Testing Time**: 2-3 hours
**Prerequisites**:

-   Phase 1 tests completed successfully
-   Database reset seeder has been run
-   At least 2 test users exist (Admin and one Member)

---

## Test Suite 7: Database Schema - Referral Clicks

### Test Case 7.1: Verify Referral Clicks Table Exists

**Objective**: Ensure referral_clicks table exists with correct schema

**Steps**:

1. Connect to MySQL database
2. Run the following SQL query:

```sql
DESCRIBE referral_clicks;
```

**Expected Results**:

-   ✅ Table exists with columns:
    -   `id` (bigint, primary key, auto_increment)
    -   `user_id` (bigint, foreign key to users)
    -   `ip_address` (varchar(45), nullable)
    -   `user_agent` (text, nullable)
    -   `clicked_at` (timestamp)
    -   `registered` (boolean, default false)
    -   `created_at` (timestamp)
    -   `updated_at` (timestamp)

**Pass Criteria**: All columns exist with correct data types and constraints

---

### Test Case 7.2: Verify Referral Clicks Indexes

**Objective**: Ensure proper indexes exist for query optimization

**Steps**:

1. Run SQL query:

```sql
SHOW INDEXES FROM referral_clicks;
```

**Expected Results**:

-   ✅ Primary key index on `id`
-   ✅ Foreign key index on `user_id`
-   ✅ Composite index on `(user_id, clicked_at)`

**Pass Criteria**: All expected indexes exist

---

## Test Suite 8: Referral Dashboard & Link Generation

### Test Case 8.1: Access Referral Dashboard

**Objective**: Verify referral dashboard is accessible to logged-in users

**Steps**:

1. Login as `member` user
2. Navigate to sidebar → "Member Actions" → "My Referral Link"
3. Verify URL is `/referral`
4. Check page loads successfully

**Expected Results**:

-   ✅ Page loads without errors
-   ✅ "My Referral Link" heading displays
-   ✅ Page shows referral code section
-   ✅ Page shows referral link section
-   ✅ Page shows QR code section
-   ✅ Page shows social share buttons
-   ✅ Page shows statistics cards

**Pass Criteria**: Dashboard displays all required sections

---

### Test Case 8.2: Verify Referral Code Display

**Objective**: Ensure user's unique referral code is displayed correctly

**Steps**:

1. On referral dashboard, locate "Your Unique Referral Code" section
2. Note the referral code displayed
3. Verify code format matches pattern `REF[A-Z0-9]{8}`

**Expected Results**:

-   ✅ Referral code displays in readonly input field
-   ✅ Code format is `REFXXXXXXXX` (3 letters + 8 alphanumeric characters)
-   ✅ "Copy Code" button is present next to input

**Pass Criteria**: Referral code displays with correct format

---

### Test Case 8.3: Verify Referral Link Display

**Objective**: Ensure shareable referral link is generated correctly

**Steps**:

1. On referral dashboard, locate "Your Referral Link" section
2. Check the link format
3. Verify link includes referral code parameter

**Expected Results**:

-   ✅ Referral link displays in readonly input field
-   ✅ Link format is `http://domain.com/register?ref=REFXXXXXXXX`
-   ✅ Referral code in link matches user's referral code
-   ✅ "Copy Link" button is present

**Pass Criteria**: Link generates correctly with ref parameter

---

### Test Case 8.4: Test Copy to Clipboard (Referral Code)

**Objective**: Verify copy to clipboard functionality works for referral code

**Steps**:

1. Click "Copy Code" button next to referral code
2. Observe toast notification
3. Paste into a text editor (Ctrl+V or Cmd+V)

**Expected Results**:

-   ✅ Toast notification appears with message "Referral code copied!"
-   ✅ Toast has green/success styling
-   ✅ Pasted text matches the displayed referral code exactly
-   ✅ Toast auto-dismisses after 3 seconds

**Pass Criteria**: Referral code copies successfully to clipboard

---

### Test Case 8.5: Test Copy to Clipboard (Referral Link)

**Objective**: Verify copy to clipboard functionality works for referral link

**Steps**:

1. Click "Copy Link" button next to referral link
2. Observe toast notification
3. Paste into a text editor

**Expected Results**:

-   ✅ Toast notification appears with message "Referral link copied!"
-   ✅ Toast has green/success styling
-   ✅ Pasted text matches the displayed referral link exactly
-   ✅ Link is a valid URL with ref parameter

**Pass Criteria**: Referral link copies successfully to clipboard

---

### Test Case 8.6: Verify QR Code Generation

**Objective**: Ensure QR code generates correctly for referral link

**Steps**:

1. On referral dashboard, locate QR code section
2. Verify QR code image displays
3. Use a QR code scanner app on mobile device
4. Scan the QR code

**Expected Results**:

-   ✅ QR code image displays (150x150 pixels)
-   ✅ QR code is scannable
-   ✅ Scanned URL matches the referral link
-   ✅ Caption "Scan to register with your referral" displays below QR code

**Pass Criteria**: QR code generates and scans to correct referral link

---

### Test Case 8.7: Verify Social Media Share Buttons

**Objective**: Ensure social sharing buttons work correctly

**Steps**:

1. On referral dashboard, locate social share buttons section
2. Check all buttons are present: Facebook, WhatsApp, Messenger, Twitter
3. Right-click each button and copy link address (don't actually open)
4. Verify each link contains the referral link URL

**Expected Results**:

-   ✅ 4 social share buttons display
-   ✅ Facebook button links to `https://www.facebook.com/sharer/sharer.php?u=...`
-   ✅ WhatsApp button links to `https://wa.me/?text=...`
-   ✅ Messenger button links to `https://www.messenger.com/t/?link=...`
-   ✅ Twitter button links to `https://twitter.com/intent/tweet?text=...`
-   ✅ All links contain URL-encoded referral link

**Pass Criteria**: All social share buttons generate correct sharing URLs

---

### Test Case 8.8: Verify Referral Statistics Display

**Objective**: Ensure referral statistics display correctly

**Steps**:

1. On referral dashboard, locate the three statistics cards
2. Verify each card displays:
    - Total Link Clicks
    - Direct Referrals
    - Conversion Rate

**Expected Results**:

-   ✅ Three statistics cards display in a row
-   ✅ "Total Link Clicks" card shows a number (default: 0)
-   ✅ "Direct Referrals" card shows a number (default: 0)
-   ✅ "Conversion Rate" card shows percentage with 1 decimal (default: 0.0%)
-   ✅ Numbers are styled prominently (large heading size)

**Pass Criteria**: All three statistics display with correct formatting

---

## Test Suite 9: Referral Click Tracking

### Test Case 9.1: Track Referral Click (New Session)

**Objective**: Verify referral clicks are tracked when clicking referral link

**Steps**:

1. Copy the `member` user's referral link from dashboard
2. Open an incognito/private browser window
3. Paste the referral link and press Enter
4. Note the current time
5. Check database for new referral click record

**SQL Query**:

```sql
SELECT * FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member')
ORDER BY clicked_at DESC
LIMIT 1;
```

**Expected Results**:

-   ✅ Registration page loads with ref parameter in URL
-   ✅ New record exists in `referral_clicks` table
-   ✅ `user_id` matches the member user's ID
-   ✅ `ip_address` is populated
-   ✅ `user_agent` is populated
-   ✅ `clicked_at` timestamp is recent (within last minute)
-   ✅ `registered` is false (default)

**Pass Criteria**: Referral click is tracked in database

---

### Test Case 9.2: Test Referral Code Session Storage

**Objective**: Verify referral code is stored in session

**Steps**:

1. In incognito window (from Test 9.1), verify still on registration page
2. Open browser DevTools → Application/Storage → Session Storage
3. Look for session data containing referral code

**Alternative Method** (if session storage isn't visible):

1. Inspect the sponsor field on registration form
2. Check if it's pre-filled with referral code

**Expected Results**:

-   ✅ Sponsor field is pre-filled with referral code (e.g., `REFXXXXXXXX`)
-   ✅ Sponsor field has `readonly` attribute
-   ✅ Success alert displays: "Referral Code Applied: REFXXXXXXXX"
-   ✅ Alert has green checkmark icon

**Pass Criteria**: Referral code is stored and pre-fills sponsor field

---

### Test Case 9.3: Test Multiple Clicks (Same IP)

**Objective**: Verify multiple clicks from same IP are tracked separately

**Steps**:

1. From incognito window, navigate away from site
2. Click the same referral link again
3. Check database for click records

**SQL Query**:

```sql
SELECT COUNT(*) as click_count
FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member')
AND clicked_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE);
```

**Expected Results**:

-   ✅ Multiple click records exist
-   ✅ Each click has its own record
-   ✅ All clicks from same IP show same IP address
-   ✅ Dashboard statistics update (Total Link Clicks increments)

**Pass Criteria**: Multiple clicks are tracked individually

---

### Test Case 9.4: Test Referral Statistics Update

**Objective**: Verify dashboard statistics reflect actual clicks

**Steps**:

1. Login as `member` user (in normal browser)
2. Navigate to referral dashboard
3. Check "Total Link Clicks" statistic
4. Compare with database count

**SQL Query**:

```sql
SELECT COUNT(*) FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member');
```

**Expected Results**:

-   ✅ Dashboard "Total Link Clicks" matches database count
-   ✅ "Direct Referrals" shows count of users with member as sponsor
-   ✅ "Conversion Rate" calculates correctly (referrals / clicks \* 100)

**Pass Criteria**: Statistics are accurate and update in real-time

---

## Test Suite 10: Auto-Fill Sponsor on Registration

### Test Case 10.1: Test Sponsor Field Pre-Fill

**Objective**: Verify sponsor field auto-fills from referral link

**Steps**:

1. Get `member` user's referral link
2. Open incognito window
3. Click referral link
4. Examine registration form

**Expected Results**:

-   ✅ Sponsor field displays referral code
-   ✅ Field has `readonly` attribute (cannot be edited)
-   ✅ Green success alert displays above field
-   ✅ Alert text: "Referral Code Applied: REFXXXXXXXX"
-   ✅ Alert includes green checkmark icon

**Pass Criteria**: Sponsor field is pre-filled and readonly

---

### Test Case 10.2: Test Registration with Referral Code

**Objective**: Verify new user registration with referral code works correctly

**Steps**:

1. On registration page with pre-filled sponsor (from Test 10.1)
2. Fill in registration form:
    - Full Name: `Test Referral User`
    - Username: `testreferral`
    - Email: `testreferral@example.com` (optional)
    - Password: `Test123!@#`
    - Confirm Password: `Test123!@#`
    - Accept Terms: ✓
3. Click "Create Account"
4. Wait for registration to complete

**Expected Results**:

-   ✅ Registration succeeds without errors
-   ✅ User is redirected to dashboard
-   ✅ Success message displays (check profile page)
-   ✅ User can see dashboard content

**Pass Criteria**: Registration completes successfully with referral code

---

### Test Case 10.3: Verify New User Sponsor Assignment

**Objective**: Ensure new user is assigned correct sponsor

**Steps**:

1. After registration (from Test 10.2), check database

**SQL Query**:

```sql
SELECT
    u.username,
    u.sponsor_id,
    s.username as sponsor_username,
    u.referral_code
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'testreferral';
```

**Expected Results**:

-   ✅ `testreferral` user exists
-   ✅ `sponsor_id` matches `member` user's ID
-   ✅ `sponsor_username` shows `member`
-   ✅ New user has unique `referral_code` (auto-generated)

**Pass Criteria**: New user is correctly assigned to sponsor

---

### Test Case 10.4: Verify Referral Click Marked as Registered

**Objective**: Ensure referral click is marked as converted

**Steps**:

1. Check `referral_clicks` table for the click that led to registration

**SQL Query**:

```sql
SELECT * FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member')
AND registered = true
ORDER BY clicked_at DESC
LIMIT 1;
```

**Expected Results**:

-   ✅ At least one record has `registered = true`
-   ✅ Most recent registered click corresponds to `testreferral` registration
-   ✅ IP address matches the registration IP
-   ✅ Timestamp aligns with registration time

**Pass Criteria**: Referral click is marked as registered

---

### Test Case 10.5: Verify Sponsor Statistics Update

**Objective**: Ensure sponsor's statistics update after successful referral

**Steps**:

1. Login as `member` user
2. Navigate to referral dashboard
3. Check statistics

**Expected Results**:

-   ✅ "Direct Referrals" count incremented by 1
-   ✅ "Total Link Clicks" remains same or higher
-   ✅ "Conversion Rate" percentage updated

**Pass Criteria**: Sponsor's statistics reflect new referral

---

## Test Suite 11: Registration Without Referral Code

### Test Case 11.1: Test Direct Registration (No Ref Parameter)

**Objective**: Verify registration works without referral code

**Steps**:

1. Open incognito window
2. Navigate directly to `/register` (no ref parameter)
3. Fill registration form:
    - Full Name: `Test Direct User`
    - Username: `testdirect`
    - Email: `testdirect@example.com`
    - Password: `Test123!@#`
    - Leave sponsor field blank
4. Submit registration

**Expected Results**:

-   ✅ Registration succeeds
-   ✅ User is created in database
-   ✅ No error about missing sponsor

**Pass Criteria**: Registration without referral code works

---

### Test Case 11.2: Verify Default Sponsor Assignment

**Objective**: Ensure users without sponsor get assigned to admin

**Steps**:

1. Check database for newly created user

**SQL Query**:

```sql
SELECT
    u.username,
    u.sponsor_id,
    s.username as sponsor_username
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'testdirect';
```

**Expected Results**:

-   ✅ User exists
-   ✅ `sponsor_id` matches admin user's ID
-   ✅ `sponsor_username` shows `admin`

**Pass Criteria**: User defaulted to admin sponsor

---

### Test Case 11.3: Test Manual Sponsor Entry (Valid)

**Objective**: Verify user can manually enter valid sponsor

**Steps**:

1. Open incognito window
2. Go to `/register`
3. Fill form with sponsor field = `member` (username)
4. Complete registration

**Expected Results**:

-   ✅ Registration succeeds
-   ✅ User is assigned to `member` as sponsor
-   ✅ No referral click is marked as registered

**Pass Criteria**: Manual sponsor entry works

---

### Test Case 11.4: Test Manual Sponsor Entry (Invalid)

**Objective**: Verify validation error for invalid sponsor

**Steps**:

1. Open incognito window
2. Go to `/register`
3. Fill form with sponsor field = `nonexistentuser`
4. Submit registration

**Expected Results**:

-   ✅ Registration fails with validation error
-   ✅ Error message: "The sponsor 'nonexistentuser' could not be found..."
-   ✅ Form retains entered data (except password)
-   ✅ User remains on registration page

**Pass Criteria**: Invalid sponsor is rejected with clear error

---

## Test Suite 12: Sidebar Navigation & UI

### Test Case 12.1: Verify Sidebar Link Exists

**Objective**: Ensure "My Referral Link" appears in sidebar

**Steps**:

1. Login as any user (member or admin)
2. Check sidebar navigation
3. Locate "Member Actions" section
4. Look for "My Referral Link" menu item

**Expected Results**:

-   ✅ "Member Actions" section exists in sidebar
-   ✅ "My Referral Link" menu item displays
-   ✅ Item has share icon (cil-share-alt)
-   ✅ Item appears below "Register New Member"

**Pass Criteria**: Sidebar link is visible and properly positioned

---

### Test Case 12.2: Test Active State Highlighting

**Objective**: Verify active state when on referral page

**Steps**:

1. Navigate to `/referral`
2. Check sidebar "My Referral Link" item

**Expected Results**:

-   ✅ "My Referral Link" has `active` class
-   ✅ Link is highlighted/styled differently from other links
-   ✅ Active state is visually clear

**Pass Criteria**: Active state displays correctly

---

### Test Case 12.3: Test Navigation Click

**Objective**: Verify clicking sidebar link navigates correctly

**Steps**:

1. From dashboard, click "My Referral Link" in sidebar
2. Check URL and page content

**Expected Results**:

-   ✅ URL changes to `/referral`
-   ✅ Referral dashboard loads
-   ✅ No console errors
-   ✅ Navigation completes smoothly

**Pass Criteria**: Navigation works without errors

---

## Test Suite 13: Edge Cases & Error Handling

### Test Case 13.1: Test Expired Session

**Objective**: Verify behavior when session expires

**Steps**:

1. Click referral link in incognito window
2. Wait for session to expire (or clear session storage manually)
3. Try to register

**Expected Results**:

-   ✅ Registration still works
-   ✅ Sponsor field may be empty (session cleared)
-   ✅ User gets assigned to default admin sponsor
-   ✅ No JavaScript errors

**Pass Criteria**: Expired session doesn't break registration

---

### Test Case 13.2: Test Invalid Referral Code in URL

**Objective**: Verify handling of invalid referral code

**Steps**:

1. Manually construct URL: `/register?ref=INVALIDCODE123`
2. Navigate to this URL
3. Check registration form

**Expected Results**:

-   ✅ Page loads normally
-   ✅ No referral click is tracked
-   ✅ Sponsor field is empty (not pre-filled)
-   ✅ No success alert displays
-   ✅ No error messages display
-   ✅ User can still register normally

**Pass Criteria**: Invalid code is gracefully ignored

---

### Test Case 13.3: Test Referral Click Without User Agent

**Objective**: Verify tracking works without user agent (rare case)

**Steps**:

1. Use curl or API tool to access referral link
2. Check if click is tracked

**Command**:

```bash
curl -I "http://localhost:8000/register?ref=MEMBER_REFERRAL_CODE"
```

**Expected Results**:

-   ✅ Click is tracked in database
-   ✅ `user_agent` field is NULL or empty
-   ✅ `ip_address` is populated
-   ✅ No errors in logs

**Pass Criteria**: Tracking works without user agent

---

### Test Case 13.4: Test Concurrent Referral Clicks

**Objective**: Verify system handles multiple simultaneous clicks

**Steps**:

1. Open 5 browser tabs/windows
2. Simultaneously click referral link in all tabs
3. Check database for duplicate tracking

**Expected Results**:

-   ✅ All clicks are tracked (5 records)
-   ✅ No database locking errors
-   ✅ Each record has correct timestamp
-   ✅ Statistics update correctly

**Pass Criteria**: Concurrent clicks are handled properly

---

### Test Case 13.5: Test Very Long User Agent String

**Objective**: Ensure long user agent strings don't break tracking

**Steps**:

1. Use browser extension to modify user agent to very long string (>1000 chars)
2. Click referral link
3. Check if tracking succeeds

**Expected Results**:

-   ✅ Click is tracked
-   ✅ User agent is stored (may be truncated if TEXT field has limit)
-   ✅ No database errors
-   ✅ Registration proceeds normally

**Pass Criteria**: Long user agent doesn't cause errors

---

## Test Suite 14: Security & Data Integrity

### Test Case 14.1: Test SQL Injection in Referral Code

**Objective**: Verify referral code is sanitized against SQL injection

**Steps**:

1. Construct malicious URL: `/register?ref=' OR '1'='1`
2. Navigate to URL
3. Check application behavior and database

**Expected Results**:

-   ✅ No SQL injection occurs
-   ✅ Invalid code is treated as non-existent
-   ✅ No referral click is tracked
-   ✅ Registration proceeds normally without pre-fill

**Pass Criteria**: SQL injection is prevented

---

### Test Case 14.2: Test XSS in Referral Code Display

**Objective**: Verify referral code output is escaped

**Steps**:

1. Attempt to create user with malicious referral code (if possible)
2. Display referral link containing special characters
3. Check if HTML is rendered or escaped

**Expected Results**:

-   ✅ Special characters are escaped in HTML
-   ✅ No JavaScript execution occurs
-   ✅ No XSS vulnerability

**Pass Criteria**: XSS is prevented through output escaping

---

### Test Case 14.3: Test CSRF Protection on Copy Actions

**Objective**: Ensure copy actions don't expose CSRF tokens

**Steps**:

1. Open referral dashboard
2. Check page source for CSRF tokens
3. Test copy functionality

**Expected Results**:

-   ✅ Copy operations are client-side only
-   ✅ No CSRF token in copied content
-   ✅ No sensitive data exposed through clipboard

**Pass Criteria**: No security data leaks through copy

---

### Test Case 14.4: Test Referral Code Uniqueness

**Objective**: Verify all referral codes are unique

**Steps**:

1. Create 10 new test users
2. Check database for duplicate referral codes

**SQL Query**:

```sql
SELECT referral_code, COUNT(*) as count
FROM users
GROUP BY referral_code
HAVING count > 1;
```

**Expected Results**:

-   ✅ Query returns 0 rows (no duplicates)
-   ✅ All users have unique referral codes
-   ✅ All codes follow format `REF[A-Z0-9]{8}`

**Pass Criteria**: All referral codes are unique

---

### Test Case 14.5: Test Unauthorized Access to Others' Referral Stats

**Objective**: Ensure users can only see their own referral data

**Steps**:

1. Login as `member` user
2. Note member's user ID
3. Try to access referral dashboard via direct manipulation
4. Check if user can see other users' statistics

**Expected Results**:

-   ✅ User can only see their own referral link
-   ✅ User can only see their own statistics
-   ✅ No way to view others' referral codes
-   ✅ No API endpoints expose other users' referral data

**Pass Criteria**: Referral data is properly isolated per user

---

## Test Suite 15: Performance & Scalability

### Test Case 15.1: Test Dashboard Load Time

**Objective**: Verify referral dashboard loads quickly

**Steps**:

1. Open browser DevTools → Network tab
2. Navigate to `/referral`
3. Measure page load time

**Expected Results**:

-   ✅ Page loads in < 2 seconds
-   ✅ QR code generates without delay
-   ✅ No slow database queries
-   ✅ Statistics calculate quickly

**Pass Criteria**: Dashboard loads within acceptable time

---

### Test Case 15.2: Test Large Click History Performance

**Objective**: Verify performance with many referral clicks

**Steps**:

1. Manually insert 1000+ referral clicks for a user
2. Load referral dashboard
3. Check query performance

**SQL to Insert Test Data**:

```sql
INSERT INTO referral_clicks (user_id, ip_address, user_agent, clicked_at, registered)
SELECT
    (SELECT id FROM users WHERE username = 'member'),
    CONCAT('192.168.1.', FLOOR(RAND() * 255)),
    'Test User Agent',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 365) DAY),
    RAND() > 0.8
FROM
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t1,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t2,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t3,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t4
LIMIT 1000;
```

**Expected Results**:

-   ✅ Statistics still load quickly (< 3 seconds)
-   ✅ Conversion rate calculates correctly
-   ✅ No timeout errors
-   ✅ Database indexes are used efficiently

**Pass Criteria**: Performance remains acceptable with large dataset

---

### Test Case 15.3: Test Concurrent Dashboard Access

**Objective**: Verify multiple users can access dashboard simultaneously

**Steps**:

1. Open 10 browser windows/tabs
2. Login as different users in each
3. Navigate to `/referral` in all tabs simultaneously
4. Check for errors or conflicts

**Expected Results**:

-   ✅ All dashboards load successfully
-   ✅ No database locking issues
-   ✅ Each user sees their own data
-   ✅ No data mixing between users

**Pass Criteria**: Concurrent access works smoothly

---

## Test Suite 16: Mobile & Responsive Design

### Test Case 16.1: Test Mobile Dashboard Layout

**Objective**: Verify referral dashboard is mobile-friendly

**Steps**:

1. Open Chrome DevTools → Device Toolbar
2. Select mobile device (e.g., iPhone 12 Pro)
3. Navigate to `/referral`
4. Check layout and usability

**Expected Results**:

-   ✅ Dashboard is responsive
-   ✅ QR code displays correctly
-   ✅ All buttons are tappable
-   ✅ Statistics cards stack vertically
-   ✅ Text is readable without zooming
-   ✅ Copy buttons work on touch devices

**Pass Criteria**: Dashboard is fully functional on mobile

---

### Test Case 16.2: Test QR Code Scanning from Mobile

**Objective**: Verify QR code scans correctly on mobile devices

**Steps**:

1. Display referral dashboard on desktop
2. Use mobile phone camera/QR scanner app
3. Scan QR code from screen

**Expected Results**:

-   ✅ QR code scans successfully
-   ✅ Mobile browser opens registration page
-   ✅ Referral code is in URL
-   ✅ Sponsor field pre-fills on mobile

**Pass Criteria**: Mobile QR scanning works end-to-end

---

### Test Case 16.3: Test Social Share on Mobile

**Objective**: Verify social sharing works on mobile browsers

**Steps**:

1. Open referral dashboard on mobile browser
2. Tap Facebook share button
3. Check if Facebook app/page opens

**Expected Results**:

-   ✅ Social app opens (or web version)
-   ✅ Referral link is included in share
-   ✅ Share text is appropriate
-   ✅ User can complete share action

**Pass Criteria**: Social sharing functional on mobile

---

## Test Suite 17: Integration Testing

### Test Case 17.1: Test End-to-End Referral Flow

**Objective**: Complete full referral cycle from link to registration

**Steps**:

1. Login as `member`, get referral link
2. Share link via any method
3. Open link in incognito window
4. Complete registration with pre-filled sponsor
5. Verify new user appears in member's referrals
6. Check all statistics update correctly

**Expected Results**:

-   ✅ Click tracked
-   ✅ Registration succeeds
-   ✅ Sponsor assigned correctly
-   ✅ Click marked as registered
-   ✅ Statistics update immediately
-   ✅ New user receives unique referral code

**Pass Criteria**: Complete flow works without intervention

---

### Test Case 17.2: Test Member Registration After Referral Click

**Objective**: Verify member registration works with referral tracking

**Steps**:

1. Login as `admin`, get referral link
2. Click link in incognito window
3. Login as different member user
4. Navigate to `/register-member`
5. Register a new member

**Expected Results**:

-   ✅ Member registration page works
-   ✅ New member is registered successfully
-   ✅ New member's sponsor is the logged-in member (not admin)
-   ✅ Original referral click may or may not be marked (depends on session)

**Pass Criteria**: Member registration doesn't interfere with referral system

---

### Test Case 17.3: Test Referral Statistics After Multiple Registrations

**Objective**: Verify statistics accuracy with multiple referrals

**Steps**:

1. Register 5 new users using member's referral link
2. Register 3 users without referral link
3. Check member's statistics

**Expected Results**:

-   ✅ "Direct Referrals" shows 5
-   ✅ "Total Link Clicks" shows at least 5 (may be higher if clicks without registration)
-   ✅ "Conversion Rate" calculates correctly
-   ✅ Database counts match dashboard display

**Pass Criteria**: Statistics are accurate across multiple registrations

---

## Phase 2 Test Summary

### Critical Tests (Must Pass)

1. ✅ Test 7.1: Referral clicks table exists
2. ✅ Test 8.1: Referral dashboard accessible
3. ✅ Test 9.1: Referral clicks tracked
4. ✅ Test 10.2: Registration with referral code
5. ✅ Test 10.3: Sponsor assigned correctly
6. ✅ Test 10.4: Click marked as registered
7. ✅ Test 14.4: Referral codes are unique
8. ✅ Test 17.1: End-to-end flow works

### High Priority Tests

-   Test 8.4, 8.5: Copy to clipboard
-   Test 8.6: QR code generation
-   Test 8.7: Social share buttons
-   Test 11.4: Invalid sponsor validation
-   Test 14.1: SQL injection prevention
-   Test 14.5: Data isolation

### Medium Priority Tests

-   Test 8.8: Statistics display
-   Test 12.1-12.3: Sidebar navigation
-   Test 13.1-13.5: Edge cases
-   Test 15.1-15.3: Performance

### Optional Tests

-   Test 16.1-16.3: Mobile/responsive
-   Test 17.2-17.3: Integration scenarios

---

## Known Issues & Limitations

### Current Limitations

1. Referral clicks track by IP address - same user from same IP will create multiple records
2. No fraud detection for suspicious click patterns
3. QR code generation is client-side only (requires JavaScript)
4. Social share buttons open in new window (may be blocked by popup blockers)

### Future Enhancements

1. Add referral click analytics dashboard
2. Implement fraud detection (e.g., click rate limiting)
3. Add email notification when someone uses your referral
4. Create referral leaderboard
5. Add referral rewards/incentives

---

## Troubleshooting Guide

### Issue: QR Code Not Displaying

**Solution**:

1. Check browser console for JavaScript errors
2. Verify qrcodejs library is loaded
3. Check network tab for CDN availability

### Issue: Referral Code Not Pre-Filling

**Solution**:

1. Check browser allows session storage
2. Verify referral code in URL is valid
3. Check FortifyServiceProvider tracking code
4. Clear session and try again

### Issue: Statistics Not Updating

**Solution**:

1. Hard refresh page (Ctrl+Shift+R)
2. Check database query is correct
3. Verify relationship methods in User model
4. Check for caching issues

### Issue: Copy to Clipboard Not Working

**Solution**:

1. Check browser supports clipboard API
2. Verify HTTPS connection (required for clipboard)
3. Try in different browser
4. Check for JavaScript errors

---

## Next Steps After Phase 2

After completing Phase 2 testing:

1. Address all critical and high-priority issues found
2. Document any workarounds for known limitations
3. Prepare for Phase 3: Real-Time MLM Commission Distribution Engine
4. Review Phase 3 requirements and test cases
