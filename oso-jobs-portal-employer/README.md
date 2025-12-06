# OSO Jobs Portal - Employer Extension

**Version:** 1.3.0  
**Requires:** OSO Jobs Portal (Core Plugin)  
**WordPress Version:** 5.0 or higher

## 🔖 Restore Point - December 6, 2025 (v1.3.0)

**Stable Release:** Complete Jobseeker Dashboard + Job Application System - All features working  
**Git Commit:** [23835b9](https://github.com/mkraner83/OSO-Jobs-Employer-Portal/commit/23835b9)  
**Downloads:**
- [oso-jobs-portal.zip](https://github.com/mkraner83/OSO-Jobs-Employer-Portal/raw/main/oso-jobs-portal.zip) (Core Plugin)
- [oso-jobs-portal-employer.zip](https://github.com/mkraner83/OSO-Jobs-Employer-Portal/raw/main/oso-jobs-portal-employer.zip) (Employer Extension v1.0.9)

### Major Features Implemented
✅ Complete employer registration and profile management  
✅ Jobseeker browser with advanced filtering (location, skills, certifications)  
✅ Full profile editing with image uploads (logo + 6 photos)  
✅ Admin approval system with subscription expiration control  
✅ Custom admin columns (camp name, logo, email, subscription info)  
✅ Dashboard with card-based profile display and hover effects  
✅ Access control: Unapproved employers see pending approval message  
✅ Expired subscriptions block jobseeker browsing with clear warnings  
✅ "Back to Search" links only visible to employers (hidden for jobseekers)  
✅ OSO Jobs admin menu: Always expanded, purple title, positioned after Dashboard  
✅ Complete Job Posting System with Add/Edit/Delete functionality  
✅ Public job browser with filters, search, and pagination  
✅ Job details page with application submission  
✅ Application management dashboard with status tracking  
✅ Custom admin screens for job postings and applications  
✅ Per-employer job posting limits (1-20 or unlimited)  
✅ Automatic job expiration based on end date  
✅ Email notifications for job applications  
✅ **NEW: Complete Jobseeker Dashboard with profile, applications, and camps**  
✅ **NEW: Job browser requires login - shows login form if not authenticated**  
✅ **NEW: Job application system with cover letter and consent**  
✅ **NEW: Application status tracking (pending/approved/rejected)**  
✅ **NEW: My Applications section showing all submitted applications**  
✅ **NEW: All Camps section showing approved employers**  
✅ **NEW: Fixed job application nonce verification issue**

## 🔖 Previous Restore Point - December 6, 2025 (v1.2.0)

**Git Commit:** [2cd78ea](https://github.com/mkraner83/OSO-Jobs-Employer-Portal/commit/2cd78ea)  
**Status:** Job Posting System Complete - Before jobseeker dashboard implementation

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

### Access Control & Approval System
- **Approval Required**: Admin-only "Approved" checkbox in employer edit screen (unchecked by default)
- **Pending Approval**: Unapproved employers see yellow warning message, cannot browse jobseekers
- **Subscription Expiration**: Admin sets "Subscription Ends" date
  - Expired subscriptions block jobseeker browsing with red warning message
  - Expiration date shown in profile with "(Expired)" label if past
  - Admins bypass all access restrictions
- **Login Redirect**: Employers auto-redirect to dashboard, jobseekers to their profile
- **Role-Based Access**: Employers cannot access wp-admin (auto-redirected to dashboard)
- **Back to Search Links**: Only visible to employers/admins (hidden from jobseekers viewing their own profile)

### Admin Features
- **Custom List Columns**: Employer admin list shows:
  1. Camp Name
  2. Logo (thumbnail linked to website if available)
  3. Email (clickable mailto link)
  4. Subscription Type
  5. Subscription Ends (color-coded: green if active, red with "Expired" if past)
- **Custom Meta Box**: All employer fields editable in WordPress admin
  - 5 organized sections: Camp Information, Location, Additional Details, Images, Linked User Account
  - Admin-only fields: Approved checkbox, Subscription Ends date picker
  - Camp types as comma-separated input (converts to newlines on save)
  - Image previews for logo and photos
- **OSO Jobs Menu**: Always expanded, purple title (#8051B0), positioned after Dashboard

### Styling & Design
- **Card-Based Profile Display**: Individual white boxes with shadows and hover effects
  - Each field in its own card with border and subtle shadow
  - Hover effect: Cards lift slightly with enhanced shadow
  - Teal labels (#548A8F), purple links (#8051B0)
  - Full-width cards for description and social media
- **Teal Primary Buttons**: rgb(82, 125, 128)
- **Purple Accents**: #8051B0 (badges, links, advanced filters toggle, menu title)
- **Professional Layouts**: Responsive grid system with auto-fit columns
- **Transitions & Effects**: Smooth hover animations
- **Photo Placeholder**: Matching color scheme with dashicons
- **Lightbox Functionality**: Profile photos and employer images

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

### Version 1.3.0 (December 6, 2025) - CURRENT RESTORE POINT ✨
**Jobseeker Dashboard & Job Application System Complete**

**Jobseeker Dashboard:**
- New shortcode `[oso_jobseeker_dashboard]` for jobseeker landing page
- Full-width purple "Browse All Jobs" banner (matches employer dashboard style)
- My Applications section:
  - Grid of application cards showing submitted applications
  - Displays: Employer logo, job title, company name, location, application date
  - Color-coded status badges (yellow=pending, green=approved, red=rejected)
  - "View Job" button linking to job details page
  - Empty state message when no applications exist
- All Camps section:
  - Grid of approved employer cards with logos
  - Shows: Camp name, location, number of open positions
  - "Visit Website" and "View Jobs" buttons
  - Only displays approved employers
- My Profile section:
  - Complete profile information display (name, email, phone, location, availability, skills, certifications)
  - Card-based layout matching employer dashboard style
  - Teal "Edit Profile" button in header
- Login form for non-authenticated users
- Logout button
- Role verification (ensures user is a jobseeker)

**Job Browser Access Control:**
- Job browser now requires login
- Non-authenticated users see login form with lock icon
- "Login Required to View Jobs" message
- Returns early if user not logged in (prevents job display)

**Job Application System:**
- Fixed nonce verification issue (changed from `oso_job_nonce` to `oso-job-nonce`)
- Added comprehensive error logging for debugging
- Enhanced JavaScript error handling with console logging
- Better error messages for specific failure scenarios
- AJAX application submission with cover letter and consent
- Application creates `oso_job_application` custom post
- Links application to job, jobseeker, and employer
- Sends email notification to employer
- Redirects to success page after submission
- Prevents duplicate applications

**Login System Updates:**
- Jobseeker login redirect changed from `/jobseeker-profile/` to `/job-portal/jobseeker-dashboard/`
- Role-based redirects: employers → dashboard, jobseekers → jobseeker dashboard

**Bug Fixes:**
- Fixed nonce verification causing "An error occurred. Please try again." error
- Fixed edit profile URL from `/job-portal/job-portal/jobseeker-edit-profile/` to `/job-portal/edit-jobseeker-profile/`
- Bumped plugin version to 1.0.9 to force cache refresh
- Bumped asset versions to 1.0.11 for cache busting

**Documentation:**
- Added comprehensive TROUBLESHOOTING.md guide
- Detailed debugging instructions for job application errors
- Common issues and solutions documented
- Step-by-step error log checking procedures

### Version 1.2.0 (December 6, 2025) - Previous Restore Point
**Complete Job Posting System with Applications**

**Job Posting System:**
- Custom post type `oso_job_posting` with full CRUD operations
- Add/Edit job form with 10 fields (accessible via `[oso_employer_add_job]`)
  - Job Title, Description (rich text), Job Type (Full-Time/Part-Time/Seasonal/Internship/Contract)
  - Required Skills/Qualifications, Start Date, End Date, Compensation, Number of Positions, Application Instructions
- Dynamic job listing on employer dashboard with card-based layout
- Edit and Delete buttons with confirmation dialogs
- Empty state with "Post Your First Job" button
- Job count display showing "X / Y jobs posted" (only counts active, non-expired jobs)
- Add New Job button (disabled when limit reached)
- Per-employer job posting limits (configurable in admin: 1-20 or Unlimited dropdown)
- Automatic job expiration based on end date (hidden from public after end date)
- Jobs remain in database after expiration but marked as "Expired"

**Public Job Browser:**
- Public job listing page with `[oso_job_browser]` shortcode
- Advanced filtering: Location (state), Job Type (Full-Time, Part-Time, etc.), Sort options
- Search functionality across job title and description
- Pagination support (12 jobs per page)
- Responsive card grid layout with job details
- Auto-hides expired jobs from public view
- Links to individual job detail pages

**Job Details & Applications:**
- Individual job detail page with `[oso_job_details]` shortcode
- Full job information display with employer card (logo, name, location)
- Application form for logged-in jobseekers (cover letter required)
- Custom post type `oso_job_application` for application submissions
- AJAX submission with email notifications to employer
- Duplicate application prevention
- Application status tracking (pending/approved/rejected)

**Application Management:**
- Applications section added to employer dashboard
- Statistics cards showing Pending, Approved, Rejected, and Total counts
- Full applications table with:
  - Applicant name (linked to jobseeker profile)
  - Job position applied for
  - Application status with color-coded badges
  - Submission date
  - Action buttons: View Cover Letter, Approve, Reject, Reset Status
- View cover letter modal with full text display
- Status update actions with AJAX (no page reload)

**Admin Features:**
- Custom admin edit screen for job postings with all fields
- Job Postings and Applications added to OSO Jobs admin menu
- Custom admin columns for job postings:
  - Job Title, Employer, Job Type, Start/End Dates, Number of Positions, Status (Active/Expired)
- Custom admin columns for applications:
  - Applicant, Job Position, Employer, Status, Date
- Status badges with color coding (green for active, red for expired/rejected)
- Automatic duplicate menu item removal system
- Job posting limit field in employer admin (dropdown selector 1-20 or unlimited)

**Bug Fixes:**
- Fixed job count calculation to exclude expired jobs from limits
- Fixed job browser URL to /job-portal/all-jobs/
- Fixed employer dashboard redirect to /job-portal/employer-profile/
- Fixed duplicate admin menu items
- Hidden old unused "Jobs" post type from admin UI

**Styling Updates:**
- Gray background (#f0f0f0) for subscription fields on employer dashboard
- Purple labels (#8051B0) for subscription type and ends date
- Gray background for empty jobs state for better visual separation
- Consistent card-based styling across all components
- Responsive design for mobile and tablet devices

### Version 1.1.0 (December 5, 2025)
**Complete Approval System, Subscription Management, Admin Enhancements**

**Approval & Access Control:**
- Added "Approved" checkbox in admin employer editor (admin-only, unchecked by default)
- Unapproved employers see yellow pending approval message on dashboard
- Unapproved employers cannot access jobseeker browser or profiles
- "Browse Jobseekers" button hidden for unapproved employers

**Subscription Expiration System:**
- Added "Subscription Ends" date field in admin (admin-only, date picker)
- Expired subscriptions block jobseeker browser access with red warning message
- Expired subscriptions shown in profile with "(Expired)" label in red
- Active subscriptions shown in green in admin list
- Access checks combined: Must be approved AND not expired

**Admin Improvements:**
- Custom list columns for employer post type:
  - Camp Name (text)
  - Logo (thumbnail image, linked to website URL)
  - Email (clickable mailto link)
  - Subscription Type (full plan name)
  - Subscription Ends (color-coded: green if active, red with "Expired" if past)
- Enhanced custom meta box with 5 organized sections
- Camp types converted to comma-separated input (was textarea)
- Added admin-only fields section (Approved checkbox, Subscription Ends date)

**Dashboard & Profile Styling:**
- Card-based profile display with individual white boxes
- Each field has shadow, border, rounded corners
- Hover effects: Cards lift with enhanced shadow
- Better spacing and typography
- Full-width cards for description and social media
- Purple links (#8051B0), teal labels (#548A8F)
- Improved version cache busting (v1.0.9)

**Core Plugin Updates:**
- OSO Jobs admin menu always expanded
- Menu title in purple (#8051B0)
- Positioned after Dashboard (position 2)
- Custom CSS and JavaScript for menu behavior

**Bug Fixes:**
- Fixed fatal error: get_employer_by_user_id() → get_employer_by_user()
- "Back to Search" links now only visible to employers (hidden from jobseekers)
- Subscription expiration check prevents access even with approval
- Proper role-based access control throughout

**Technical Details:**
- Added 18 employer meta fields total (including approval and subscription_ends)
- Hook priority 5 prevents conflicts with core plugin
- Admin scripts move menu to position 2 on page load
- CSS keeps submenu expanded at all times

### Version 1.0.8 (December 4, 2025) - Previous Restore Point
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
- `oso_job_posting` - Job postings (NEW in v1.2.0)
- `oso_job_application` - Job applications (NEW in v1.2.0)
- `oso_job` - Old job postings (deprecated, hidden from UI)

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
- `_oso_employer_subscription_ends` - Subscription expiration date (admin-only)
- `_oso_employer_approved` - Approval status (admin-only checkbox)
- `_oso_employer_job_limit` - Job posting limit (admin-only, 1-20 or 0 for unlimited)
- `_oso_employer_logo` - Logo image URL
- `_oso_employer_photos` - Photo URLs (newline-separated)
- `_oso_employer_wpforms_entry` - WPForms entry ID

### Meta Fields (Job Posting) - NEW in v1.2.0
- `_oso_job_employer_id` - Employer post ID (links job to employer)
- `_oso_job_type` - Job Type (Full-Time, Part-Time, Seasonal, Internship, Contract)
- `_oso_job_required_skills` - Required skills/qualifications (textarea)
- `_oso_job_start_date` - Position start date
- `_oso_job_end_date` - Position end date (auto-expire jobs after this date)
- `_oso_job_compensation` - Salary/wage information
- `_oso_job_positions` - Number of open positions
- `_oso_job_application_instructions` - Special instructions for applicants

### Meta Fields (Job Application) - NEW in v1.2.0
- `_oso_application_job_id` - Job posting ID
- `_oso_application_jobseeker_id` - Jobseeker post ID
- `_oso_application_employer_id` - Employer post ID
- `_oso_application_status` - Application status (pending/approved/rejected)
- `_oso_application_date` - Submission date

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

### Shortcodes Available

**Employer Shortcodes:**
- `[oso_employer_dashboard]` or `[oso_employer_profile]` - Employer dashboard with jobs and applications
- `[oso_employer_edit_profile]` - Employer profile editing form
- `[oso_employer_add_job]` - Add/edit job posting form
- `[oso_employer_password_link]` - Password reset link

**Jobseeker Shortcodes:**
- `[oso_jobseeker_dashboard]` - Jobseeker landing page/dashboard with applications and camps (NEW in v1.3.0)
- `[oso_jobseeker_edit_profile]` - Jobseeker profile editing form
- `[oso_jobseeker_profile]` - View individual jobseeker profile (for employers)

**Job Browsing Shortcodes:**
- `[oso_jobseeker_browser]` - Browse jobseekers (employers only)
- `[oso_job_browser]` - Public job listings with filters (requires login as of v1.3.0)
- `[oso_job_details]` - Individual job page with application form

### Hooks Used
- `wpforms_process_complete_{FORM_ID}` - Handle employer registration
- `login_redirect` - Redirect employers to dashboard
- `admin_init` - Block wp-admin access for employers
- `wp_ajax_oso_save_job_posting` - AJAX handler for job save
- `wp_ajax_oso_delete_job_posting` - AJAX handler for job delete
- `wp_ajax_oso_submit_job_application` - AJAX handler for application submission
- `wp_ajax_oso_update_application_status` - AJAX handler for application status updates
- `pre_get_posts` - Filter to hide expired jobs from public queries

## Support

For issues or questions, please contact the plugin developer.

## License

This plugin is proprietary software developed for OSO Jobs Portal.