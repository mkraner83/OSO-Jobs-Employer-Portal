# OSO Jobs Portal - Employer Section
## Complete Setup & Update Instructions

**Version:** 1.0.0  
**Last Updated:** December 4, 2025  
**Restore Point:** v1.0.0-working (Git tag)

---

## 📋 Table of Contents
- [Overview](#overview)
- [Requirements](#requirements)
- [Installation](#installation)
- [Features](#features)
- [Configuration](#configuration)
- [Usage](#usage)
- [Troubleshooting](#troubleshooting)
- [Update History](#update-history)

---

## Overview

This plugin extends the OSO Jobs Portal with employer-specific functionality including registration, user account management, and a dedicated employer dashboard.

### Key Components
- **Employer Registration** via WPForms (Form ID: 1917)
- **Employer Dashboard** shortcode: `[oso_employer_dashboard]`
- **Automatic WordPress user creation** with employer role
- **Password setup links** via email and confirmation page
- **Custom post type** for employer profiles

---

## Requirements

### Prerequisites
1. **WordPress** 6.0 or higher
2. **PHP** 7.4 or higher
3. **OSO Jobs Portal** plugin (main plugin) - **MUST be installed and activated first**
4. **WPForms** plugin (for registration form)

### Plugin Dependencies
- Main Plugin Folder: `/wp-content/plugins/oso-jobs-portal/`
- This Plugin Folder: `/wp-content/plugins/oso-jobs-portal-employer/`

---

## Installation

### Step 1: Install Main Plugin
1. Upload `oso-jobs-portal.zip` to WordPress
2. Activate the **OSO Jobs Portal** plugin
3. Verify it's active before proceeding

### Step 2: Install Employer Plugin
1. Upload `oso-jobs-portal-employer.zip` to WordPress
2. Activate the **OSO Jobs Portal - Employer Section** plugin
3. Verify no error messages appear

### Step 3: Verify Installation
✅ No error messages in WordPress admin  
✅ Employer role (`oso_employer`) exists  
✅ Employer post type is registered  

---

## Features

### 1. Employer Registration (WPForms ID: 1917)

#### Required Form Fields (case-insensitive):
- **Full Name** - Employer's full name
- **Email** - Email address (used for login)
- **Phone** - Contact phone number
- **Company** - Company/organization name

#### Automatic Actions on Form Submission:
1. Creates WordPress user account with `oso_employer` role
2. Generates unique username from name/email
3. Creates Employer custom post type entry
4. Links user account to employer profile
5. Saves all profile metadata
6. Sends password setup email automatically
7. Displays password setup link in confirmation message

#### Password Setup Options:
- **Option 1 (Email):** Automatic WordPress email with password reset link
- **Option 2 (Confirmation):** Instant link displayed after form submission using `{employer_password_link}` smart tag

---

### 2. Employer Dashboard Shortcode

#### Shortcode
```
[oso_employer_dashboard]
```

#### Features
- **For logged-out users:** Shows login form with password recovery link
- **For logged-in employers:** Displays:
  - Profile information (name, email, phone, company)
  - List of all posted jobs
  - Job status indicators (Published, Draft, Pending Review)
  - View/Edit links for each job
  - Logout button

#### Access Control
- Only users with `oso_employer` role can access the dashboard
- Non-employer users see permission denied message

---

## Configuration

### WPForms Setup (Form ID: 1917)

#### 1. Create/Edit Form Fields
Ensure your form has these fields with exact labels:
- Full Name (Text field)
- Email (Email field)
- Phone (Phone field)
- Company (Text field)

#### 2. Configure Confirmation Message
Go to: **Settings → Confirmations**

Add this message:
```html
<h3>Thank you for registering!</h3>
<p>Your employer account has been created successfully.</p>
<p>An email with password setup instructions has been sent to your email address.</p>
<p>Alternatively, you can set up your password right now:</p>
<p>{employer_password_link}</p>
```

#### 3. Save Form
Form ID must be **1917** (or update `class-oso-employer-registration.php` line 8)

---

### Dashboard Page Setup

#### Create Dashboard Page
1. Create new page: **Employer Dashboard**
2. Add shortcode: `[oso_employer_dashboard]`
3. Publish page
4. Recommended slug: `/employer-dashboard/`

#### Optional: Add to Menu
Add the dashboard page to your site navigation for easy access.

---

## Usage

### For Employers

#### Registration Process
1. Visit registration page with Form 1917
2. Fill out all required fields
3. Submit form
4. Receive password setup email
5. Click link in confirmation or email
6. Set password and log in
7. Access dashboard at `/employer-dashboard/`

#### Dashboard Access
- Direct URL: `https://yoursite.com/employer-dashboard/`
- Shows login form if not logged in
- Displays profile and job listings when logged in

---

### For Administrators

#### Manual Role Assignment (Testing)
To test dashboard without form submission:
1. Go to **Users → All Users**
2. Edit a user
3. Change role to **Employer**
4. Save user

#### Verify Employer Accounts
1. Go to **Posts → Employers** (custom post type)
2. View all employer profiles
3. Check linked user accounts

---

## Troubleshooting

### Error: "The OSO Jobs Portal plugin must be installed and active"

**Cause:** Main plugin not detected  
**Solution:**
1. Verify main plugin is in `/wp-content/plugins/oso-jobs-portal/`
2. Activate main plugin first
3. Deactivate and reactivate employer plugin
4. Check for `OSO_JOBS_PORTAL_DIR` constant

---

### Error: "You do not have permission to access the employer dashboard"

**Cause:** User doesn't have employer role  
**Solution:**
1. Register through Form 1917, OR
2. Manually assign `oso_employer` role to user
3. Log out and log back in

---

### Smart Tag Not Working: {employer_password_link}

**Cause:** Smart tag not processed  
**Solution:**
1. Verify plugin is active
2. Check form ID is 1917
3. Test by submitting form
4. Check for transient storage issues

---

### Registration Not Creating User

**Cause:** Form field labels don't match  
**Solution:**
1. Check field labels are exactly: "Full Name", "Email", "Phone", "Company"
2. Labels are case-insensitive but must match
3. Check WPForms hooks are firing

---

## Update History

### Version 1.0.0 - December 4, 2025 ✅ RESTORE POINT
**Git Tag:** `v1.0.0-working`

#### Features Implemented
- ✅ Employer registration via WPForms (Form ID: 1917)
- ✅ Automatic WordPress user creation with employer role
- ✅ Employer custom post type integration
- ✅ Password setup email (Option 1)
- ✅ Password setup link in confirmation (Option 2)
- ✅ Custom smart tag: `{employer_password_link}`
- ✅ Employer dashboard shortcode: `[oso_employer_dashboard]`
- ✅ Dashboard login form for non-authenticated users
- ✅ Profile information display
- ✅ Job listings with status indicators
- ✅ View/Edit job links
- ✅ Role-based access control
- ✅ Plugin dependency checking with detailed error messages

#### Files Created
- `/includes/shortcodes/class-oso-employer-shortcodes.php`
- `/includes/shortcodes/views/employer-dashboard.php`
- `/includes/class-oso-employer-registration.php`
- `/includes/class-oso-employer-portal.php`
- `/includes/helpers/class-oso-employer-utils.php`

#### Files Modified
- `oso-employer-portal.php` - Enhanced dependency checking and initialization

---

## Developer Notes

### Restoring to This Version
```bash
git checkout v1.0.0-working
```

### Key Classes
- `OSO_Employer_Registration` - Handles WPForms submissions
- `OSO_Employer_Shortcodes` - Registers and renders shortcodes
- `OSO_Employer_Portal` - Main plugin initialization
- `OSO_Employer_Utils` - Helper functions

### Hooks Used
- `wpforms_process_complete_1917` - Form submission handler
- `wpforms_smart_tags` - Register custom smart tag
- `wpforms_process_smart_tags` - Process smart tag
- `plugins_loaded` (priority 20) - Initialize plugin after main plugin

### Custom Post Types & Database Structure

#### Employer Post Type
- **Slug:** `oso_employer`
- **Supports:** title, editor, thumbnail, excerpt
- **Public:** Yes
- **Archive:** No

**Meta Fields:**
| Meta Key | Description | Type | Required |
|----------|-------------|------|----------|
| `_oso_employer_user_id` | Linked WordPress user ID | Integer | Yes |
| `_oso_employer_full_name` | Employer's full name | Text | Yes |
| `_oso_employer_email` | Email address | Email | Yes |
| `_oso_employer_phone` | Contact phone number | Text | No |
| `_oso_employer_company` | Company/organization name | Text | No |

---

#### Jobseeker Post Type
- **Slug:** `oso_jobseeker`
- **Supports:** title, editor, thumbnail, excerpt
- **Public:** Yes
- **Archive:** No

**Meta Fields:**
| Meta Key | Description | Type | Example/Options |
|----------|-------------|------|-----------------|
| `_oso_jobseeker_user_id` | Linked WordPress user ID | Integer | - |
| `_oso_jobseeker_full_name` | Jobseeker's full name | Text | - |
| `_oso_jobseeker_email` | Email address | Email | - |
| `_oso_jobseeker_location` | US State location | Select | See states list |
| `_oso_jobseeker_over_18` | Age verification | Checkbox | "Yes, I am over 18" |
| `_oso_jobseeker_resume` | Resume file URL | URL | - |
| `_oso_jobseeker_photo` | Profile photo URL | URL | - |
| `_oso_jobseeker_availability_start` | Earliest start date | Date | YYYY-MM-DD |
| `_oso_jobseeker_availability_end` | Latest end date | Date | YYYY-MM-DD |
| `_oso_jobseeker_job_interests` | Job categories interested in | Checkboxes (CSV) | General Counselor, Sports, Arts, Adventure, Waterfront, Medical, Kitchen/Dining, Maintenance/Grounds, Office/Administration, Any! |
| `_oso_jobseeker_sports_skills` | Sports abilities | Checkboxes (CSV) | Archery, Baseball, Basketball, Soccer, Tennis, Volleyball |
| `_oso_jobseeker_arts_skills` | Arts abilities | Checkboxes (CSV) | 3D Printing, Arts & Crafts, Music/Instruments, Photography, Theater |
| `_oso_jobseeker_adventure_skills` | Outdoor/adventure skills | Checkboxes (CSV) | Backpacking, Camping, Hiking, High Ropes, Kayaking |
| `_oso_jobseeker_waterfront_skills` | Water-based skills | Checkboxes (CSV) | Canoeing, Kayaking, Lifeguard, Swim Instructor, Sailing |
| `_oso_jobseeker_support_skills` | Support service abilities | Checkboxes (CSV) | Administration, Dining/Kitchen, Maintenance, Groundskeeping, Media/Communications |
| `_oso_jobseeker_certifications` | Professional certifications | Checkboxes (CSV) | AED Certification, First Aid & CPR, Lifeguard Certification, WSI, Wilderness First Aid/WFR, Other |
| `_oso_jobseeker_wpforms_entry` | Original WPForms entry ID | Integer | - |

**Note:** Checkbox fields store values as comma-separated strings. Use `OSO_Jobs_Utilities::meta_string_to_array()` to convert to array.

**Post Content Field:**
- `post_content` - "Why Are You Interested in Summer Camp?" (textarea)

---

### User Roles

#### Employer Role
- **Slug:** `oso_employer`
- **Capabilities:** `read` only
- **Created by:** Main OSO Jobs Portal plugin
- **Purpose:** Access employer dashboard and manage job postings

#### Candidate/Jobseeker Role
- **Slug:** `oso_candidate`
- **Capabilities:** `read` only
- **Created by:** Main OSO Jobs Portal plugin
- **Purpose:** Access jobseeker profile and application features

---

## Support

### Checking Plugin Status
1. Go to **Plugins** page in WordPress admin
2. Both plugins should show as active
3. No error messages should appear

### Debug Mode
To enable WordPress debug mode, add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check `/wp-content/debug.log` for errors.

---

## Next Steps / Planned Features

**Future enhancements to be added here as they're implemented:**
- [ ] Employer profile editing from dashboard
- [ ] Job posting from dashboard
- [ ] Application management
- [ ] Email notifications for new applications
- [ ] Enhanced dashboard statistics

---

*This document will be updated with each major change or feature addition.*
