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
- Displays employer profile information
- Lists employer's posted jobs
- Quick link to browse jobseekers
- Login form for non-authenticated users
- Role-based access control

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

## WPForms Setup

The employer registration form (ID: 1917) should include:
- Full Name
- Email
- Phone
- Company

## Changelog

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
- `_oso_employer_full_name` - Employer full name
- `_oso_employer_email` - Email address
- `_oso_employer_phone` - Phone number
- `_oso_employer_company` - Company name

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