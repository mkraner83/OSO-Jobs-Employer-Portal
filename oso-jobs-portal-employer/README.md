# OSO Jobs Portal - Employer Extension

**Version:** 1.1.0  
**Requires:** OSO Jobs Portal (Core Plugin)  
**WordPress Version:** 5.0 or higher

## Description

The Employer Extension adds a complete employer portal to the OSO Jobs Portal system, allowing employers to register, manage their profiles, browse jobseeker candidates, and view detailed candidate profiles.

## Features

### Employer Registration
- WPForms integration for employer registration (Form ID: 1917)
- Automatic user account creation with employer role
- Password setup via email notification
- Custom employer post type (CPT) creation
- Profile data storage (name, email, phone, company)

### Employer Dashboard
- Accessible via shortcode: `[oso_employer_dashboard]` or `[oso_employer_profile]`
- Full-width purple banner with "Browse Jobseekers" link
- "Your Job Postings" section (shows placeholder if no jobs)
- Complete profile information display with all employer fields
- Edit profile button with icon
- Login form for non-authenticated users
- Role-based access control
- Logout button with proper spacing

### Employer Profile Editing
- Accessible via shortcode: `[oso_employer_edit_profile]`
- Edit all employer information:
  - **Camp Information**: Camp Name, Contact Email, Website, Brief Description, Type of Camp (checkboxes)
  - **Location**: State, Address, Closest Major City
  - **Images**: Logo upload with preview, Photo uploads (up to 6 photos with grid display and remove buttons)
  - **Additional Details**: Start of Staff Training (date picker), Housing Provided (dropdown), Social Media Links (textarea)
  - **Subscription Type**: View-only field (disabled)
- AJAX form submission with loading states
- Auto-formatting for URLs (adds https:// if missing)
- Date format conversion (HTML date input ↔ display format)
- File validation (16MB max per file)
- Success message and auto-redirect to profile
- Password reset link shortcode: `[oso_employer_password_link]`

### Jobseeker Browser
- Accessible via shortcode: `[oso_jobseeker_browser]`
- Card-based grid layout with responsive design
- Full-width search functionality
- Advanced filtering system:
  - Location filter (all US states)
  - Age verification filter (Over 18: Yes/No/Any)
  - Job interests
  - Sports skills
  - Arts skills
  - Adventure skills
  - Waterfront skills
  - Support services skills
  - Certifications
- Sorting options:
  - Newest first
  - Oldest first
  - Name (A-Z)
  - Name (Z-A)
- Collapsible advanced filters
- Pagination support
- Search across name, location, and email
- Profile cards display:
  - Photo (with placeholder fallback)
  - Name and location
  - Availability dates
  - "Why interested" text (200 char preview)
  - Skills badges (first 5 + counter)

### Jobseeker Profile View
- Accessible via shortcode: `[oso_jobseeker_profile]`
- Detailed candidate information:
  - Full profile photo with lightbox popup
  - Contact information (email with purple link)
  - Location with icon
  - Availability dates
  - Full "Why interested in summer camp" text
  - Age verification (displayed as plain text)
  - All skills as purple badges (#8051B0)
  - Resume download link
- Back to search navigation (purple link)
- Contact candidate email button

### Access Control
- Employers cannot access wp-admin (auto-redirected to dashboard)
- Administrators retain full wp-admin access
- Login redirect sends employers to their dashboard page
- Role-based shortcode access

### Styling & Design
- Teal primary buttons: rgb(82, 125, 128)
- Purple accents: #8051B0 (badges, links, advanced filters toggle)
- Professional card-based layouts
- Responsive grid system
- Hover effects and transitions
- Photo placeholder with matching color scheme
- Lightbox functionality for profile photos

## Installation

1. Ensure the core **OSO Jobs Portal** plugin is installed and activated
2. Upload `oso-jobs-portal-employer.zip` to WordPress
3. Activate the plugin
4. Create pages with the required shortcodes

## Required Shortcodes

### Employer Dashboard
```
[oso_employer_dashboard]
```
or
```
[oso_employer_profile]
```

### Browse Jobseekers
```
[oso_jobseeker_browser]
```

### Jobseeker Profile
```
[oso_jobseeker_profile]
```

## Database Structure & WPForms Integration

### Employer Data Flow (WPForms ID: 1917 → Database)

**WPForms Field Label** → **Database Meta Key** → **Field Type**

| WPForms Field | Database Meta Key | Type | Notes |
|--------------|-------------------|------|-------|
| Camp Name | `_oso_employer_company` | text | Also stored as post_title |
| Upload Logo | `_oso_employer_logo` | file URL | Uploaded to WordPress media library |
| Brief Description | `_oso_employer_description` | textarea | Camp overview |
| Type of Camp | `_oso_employer_camp_types` | checkbox array | Day Camp, Overnight, Sport, Arts, etc. Stored as newline-separated |
| State | `_oso_employer_state` | dropdown | US state |
| Address | `_oso_employer_address` | text | Street address |
| Closest Major City | `_oso_employer_major_city` | text | Reference city |
| Start of Staff Training Date | `_oso_employer_training_start` | date | Format: MM/DD/YYYY |
| Housing Provided | `_oso_employer_housing` | dropdown | Yes/No |
| Contact Email | `_oso_employer_email` | email | Primary contact |
| Website/URL | `_oso_employer_website` | URL | Auto-adds https:// if missing |
| Social Media Links | `_oso_employer_social_links` | textarea | Multiple links, newline-separated |
| Subscription Type | `_oso_employer_subscription_type` | dropdown | Billing tier (read-only in profile) |

**Additional Meta Fields (System Generated):**
- `_oso_employer_user_id` - Linked WordPress user ID
- `_oso_employer_photos` - Photo URLs (newline-separated, up to 6)
- `_oso_employer_wpforms_entry` - WPForms entry ID for reference

**Post Type:** `oso_employer` (stored in wp_posts table)
**Meta Storage:** wp_postmeta table

**Registration Hook:** `wpforms_process_complete_1917` (priority 5)

---

### Jobseeker Data Flow (WPForms → Database)

**Note:** Jobseeker registration is handled by the core OSO Jobs Portal plugin.

**WPForms Field Label** → **Database Meta Key** → **Field Type**

| WPForms Field | Database Meta Key | Type | Notes |
|--------------|-------------------|------|-------|
| Full Name | `_oso_jobseeker_full_name` | text | Also stored as post_title |
| Email | `_oso_jobseeker_email` | email | Primary contact |
| Phone | `_oso_jobseeker_phone` | text | Contact number |
| Location | `_oso_jobseeker_location` | text | City, State |
| Are You Over 18? | `_oso_jobseeker_over_18` | radio | Yes/No |
| Upload Photo | `_oso_jobseeker_photo` | file URL | Profile photo |
| Upload Resume | `_oso_jobseeker_resume` | file URL | PDF/DOC resume |
| Availability Start | `_oso_jobseeker_availability_start` | date | Season start date |
| Availability End | `_oso_jobseeker_availability_end` | date | Season end date |
| Why Interested in Summer Camp | `_oso_jobseeker_why_interested` | textarea | Personal statement |
| Job Interests | `_oso_jobseeker_job_interests` | checkbox array | Counselor, Lifeguard, etc. |
| Sports Skills | `_oso_jobseeker_sports_skills` | checkbox array | Soccer, Basketball, etc. |
| Arts Skills | `_oso_jobseeker_arts_skills` | checkbox array | Music, Drama, etc. |
| Adventure Skills | `_oso_jobseeker_adventure_skills` | checkbox array | Hiking, Climbing, etc. |
| Waterfront Skills | `_oso_jobseeker_waterfront_skills` | checkbox array | Swimming, Sailing, etc. |
| Support Services Skills | `_oso_jobseeker_support_skills` | checkbox array | Kitchen, Maintenance, etc. |
| Certifications | `_oso_jobseeker_certifications` | checkbox array | CPR, First Aid, etc. |

**Additional Meta Fields (System Generated):**
- `_oso_jobseeker_user_id` - Linked WordPress user ID
- `_oso_jobseeker_wpforms_entry` - WPForms entry ID for reference

**Post Type:** `oso_jobseeker` (stored in wp_posts table)
**Meta Storage:** wp_postmeta table

---

## WPForms Setup Requirements

### Employer Registration Form (ID: 1917)
**Required Fields:**
- Camp Name (text) - **REQUIRED**
- Contact Email (email) - **REQUIRED**
- Subscription Type (dropdown) - **REQUIRED**

**Optional Fields:**
- Upload Logo, Brief Description, Type of Camp, State, Address, Closest Major City, Start of Staff Training Date, Housing Provided, Website/URL, Social Media Links

**Important Notes:**
- Registration handler runs at **priority 5** to prevent conflicts with core plugin (priority 10)
- Logo files uploaded to WordPress media library automatically
- URLs auto-formatted with https:// if protocol missing
- Date format converted between MM/DD/YYYY (storage) and YYYY-MM-DD (HTML input)
- Checkbox arrays stored as newline-separated strings

### Jobseeker Registration Form
Handled by core OSO Jobs Portal plugin. Refer to core plugin documentation.

## Changelog

### Version 1.0.8 (December 4, 2025) - RESTORE POINT
**Employer Profile Editing & Dashboard Redesign**
- Added employer profile editing functionality with all WPForms fields
- Edit form includes: Camp Name, Email, Website, Description, Camp Types, State, Address, Major City, Training Start Date, Housing, Social Links
- Fixed WPForms registration hook conflict (priority 5 vs 10)
- All 12+ employer fields now save correctly to database
- Profile view displays all saved employer data dynamically
- Added logo upload with preview (max 16MB)
- Added photo uploads (up to 6 photos, max 16MB each)
- Photo grid display with remove buttons
- Fixed training date format conversion (MM/DD/YYYY ↔ YYYY-MM-DD)
- Made Subscription Type field disabled/read-only
- AJAX save handler with auto https:// URL formatting
- File upload handler supports both employers and jobseekers
- Redesigned employer dashboard layout:
  - Removed "Employer Profile" h2 title
  - Full-width purple banner (#8051B0) for "Browse Jobseekers" link
  - Added "Your Job Postings" section below banner
  - New order: Quick Link Banner → Job Postings → Your Profile
  - Added 8px border radius to Browse Jobseekers banner
  - Added 40px spacing before logout button
- Password reset shortcode [oso_employer_password_link] for WPForms confirmation

### Version 1.1.0 (December 4, 2025)
- Added full-width search field in jobseeker browser
- Added "Over 18?" filter (Yes/No/Any) to jobseeker browser
- Updated all skill badges to purple (#8051B0) with white text
- Updated "Show Advanced Filters" toggle to purple (#8051B0)
- Redesigned filter button layout (Apply Filters and Clear side-by-side)
- Added lightbox functionality for profile photos (click to zoom)
- Changed "Are You Over 18?" to display as plain text instead of badge
- Updated email links to purple (#8051B0)
- Updated "Back to Search" link to purple (#8051B0)
- Added hover effect with magnifying glass icon on profile photos
- Improved filter form layout with separate rows for search and controls

### Version 1.0.0 (December 4, 2025)
- Initial release
- Employer registration via WPForms
- Employer dashboard with profile and job listings
- Jobseeker browser with search and filter
- Individual jobseeker profile viewer
- Card-based grid layout
- Advanced filtering system
- Login redirect for employers
- wp-admin access control
- "Why interested" text display (plain text, 200 char limit in cards)
- Skills display as badges (excluding "Over 18")
- Removed "Browse Jobseekers" header from listing page
- Photo placeholder styling to match button colors

## Developer Notes

### Post Types Used
- `oso_employer` - Employer profiles
- `oso_jobseeker` - Jobseeker profiles (from core plugin)
- `oso_job` - Job postings (from core plugin)

### User Roles
- `oso_employer` - Employer role with limited access
- `oso_candidate` - Jobseeker role (from core plugin)

### Meta Fields (Employer)
- `_oso_employer_user_id` - Linked WordPress user ID
- `_oso_employer_company` - Camp Name
- `_oso_employer_email` - Contact Email
- `_oso_employer_website` - Website URL
- `_oso_employer_description` - Brief Description
- `_oso_employer_camp_types` - Type of Camp (multi-select)
- `_oso_employer_state` - State
- `_oso_employer_address` - Address
- `_oso_employer_major_city` - Closest Major City
- `_oso_employer_training_start` - Start of Staff Training Date
- `_oso_employer_housing` - Housing Provided
- `_oso_employer_social_links` - Social Media Links
- `_oso_employer_subscription_type` - Subscription Type (read-only)
- `_oso_employer_logo` - Logo image URL
- `_oso_employer_photos` - Photo URLs (newline-separated)
- `_oso_employer_wpforms_entry` - WPForms entry ID

### Meta Fields (Jobseeker - from core plugin)
- `_oso_jobseeker_full_name`
- `_oso_jobseeker_email`
- `_oso_jobseeker_location`
- `_oso_jobseeker_over_18`
- `_oso_jobseeker_photo`
- `_oso_jobseeker_resume`
- `_oso_jobseeker_availability_start`
- `_oso_jobseeker_availability_end`
- `_oso_jobseeker_job_interests`
- `_oso_jobseeker_sports_skills`
- `_oso_jobseeker_arts_skills`
- `_oso_jobseeker_adventure_skills`
- `_oso_jobseeker_waterfront_skills`
- `_oso_jobseeker_support_skills`
- `_oso_jobseeker_certifications`

### Hooks Used
- `wpforms_process_complete_{FORM_ID}` - Handle employer registration
- `login_redirect` - Redirect employers to dashboard
- `admin_init` - Block wp-admin access for employers

## Support

For issues or questions, please contact the plugin developer.

## License

This plugin is proprietary software developed for OSO Jobs Portal.