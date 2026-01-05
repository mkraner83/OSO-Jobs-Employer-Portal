# OSO Jobs Portal

WordPress plugin for managing job postings, employer profiles, and jobseeker profiles for OSO Jobs.

## Current Version: 1.1.0

## 🚀 Latest Changes (January 5, 2026)

### Email Template System Enhancements
- **10 Customizable Templates**: All automated emails now editable from OSO Tools → Email Templates
- **Individual Reset Buttons**: Reset single templates to default without affecting others  
- **Application Rejected Template**: New professional email template for rejection notifications
- **Consistent Design**: All emails now use purple gradient header with table-based HTML structure
- **Available Variables**: Each template displays merge tags for easy personalization
- **Visual & HTML Editors**: Edit with WordPress rich text editor or raw HTML
- **WhatsApp Integration**: Jobseeker welcome email includes WhatsApp group link (#527D80 green)

### Email Templates Available
1. Employer Welcome Email
2. Jobseeker Welcome Email
3. Jobseeker Profile Approved
4. New Application (Employer)
5. Application Approved (Jobseeker)
6. Application Approved (Admin)
7. **Application Rejected (Jobseeker)** - NEW
8. Application Cancelled (Jobseeker)
9. Application Cancelled (Employer)
10. Employer Interest Notification

---

## Version History

### January 5, 2026 - Email System Overhaul (v1.1.0)
- Added "Application Rejected (Jobseeker)" email template
- Individual "Reset Template" button for each email (no longer resets all)
- Updated Employer and Jobseeker welcome emails to proper table-based format
- All templates now use consistent purple gradient design
- Email variables displayed above each template editor
- WhatsApp integration in jobseeker welcome with OSO green color

### December 12, 2025 - Jobseeker Approval System (v1.0.14)

#### Complete Approval Workflow
- **Admin Approval Controls**: Checkbox on jobseeker edit screen to approve/unapprove users
- **Inline Toggle**: Click approval status directly on jobseeker list page to toggle instantly
- **Color-Coded Badges**: 
  - ✓ Yes (Approved): Light green background with dark green text
  - ✗ Pending: Light red background with dark red text
- **Default Status**: New jobseekers default to "Pending" (unapproved) status
- **Filtering**: Unapproved jobseekers hidden from employer jobseeker browser
- **Access Control**: Unapproved jobseekers cannot apply for jobs
- **Dashboard Warning**: Yellow banner on jobseeker dashboard when pending approval

#### Email Notifications
- **Approval Email**: Styled HTML email sent automatically when jobseeker is approved
- **Admin Notification**: Email to admin when new jobseeker registers requiring approval
- **Direct Links**: Emails include links to review/approve and jobseeker dashboard

#### User Experience
- **Browse Access**: Unapproved users can still browse jobs and edit their profile
- **Clear Messaging**: Informative messages on job details and dashboard about approval status
- **Client & Server Validation**: Application blocking on both frontend and backend

#### Bug Fixes
- **Infinite Loop Fix**: Fixed memory exhaustion error when saving jobseeker approval (v1.0.11)
- **CSS Enhancements**: Proper styling for clickable approval badges with hover effects

## Version History

### December 8, 2025 - Major Email & Upload System Overhaul

#### Email System
- **Styled HTML Welcome Emails**: Created beautiful, responsive HTML emails for both employers and jobseekers
  - Employers: Teal theme (#548A8F) with exact text from Josh & Caleb
  - Jobseekers: Purple gradient theme (#667eea → #764ba2) with "Your Summer Starts Here" messaging
- **Password Reset Integration**: Generate actual password reset keys in welcome emails
- **Email Consolidation**: Disabled duplicate WordPress default user emails while keeping admin notifications
- **Single Email Solution**: Users receive only one styled welcome email with valid password reset link

#### File Upload System
- **Media Library Integration**: All file uploads now appear in WordPress Media Library
- **Custom Upload Folder**: Created "Camp-Uploads" folder for organized file storage
- **Uploader Tracking**: Added metadata to track who uploaded files (username, user ID, upload type)
- **Media Library Column**: Added "Uploaded By" column in Media Library for easy identification
- **File Restrictions**:
  - Camp Photos: JPG, JPEG, WEBP only, 20MB total maximum
  - Camp Logo: JPG, JPEG, PNG, WEBP, PDF allowed, 6MB maximum
- **Client & Server Validation**: Both frontend JavaScript and backend PHP validation for file types and sizes

#### Duplicate Email Prevention
- **Server-Side Validation**: Prevent users from registering with email already in use
- **Role-Specific Error Messages**: Different messages for employer vs jobseeker duplicate emails
- **WPForms Integration**: Validation occurs during form submission with proper error display

#### Form Confirmation Messages
- **Styled Confirmation Pages**: Created HTML templates for post-registration confirmation messages
- **Password Reset Shortcode**: Custom shortcode `[oso_employer_password_link]` generates password reset buttons
- **Consistent Branding**: Teal buttons and typography matching the overall design

## Features

### Core Features
- **Job Posting Management**: Full CRUD operations for job listings
- **Employer Profile Management**: Complete employer profiles with logos, photos, and details
- **Jobseeker Profile Management**: Comprehensive jobseeker profiles with resumes and preferences
- **Custom Post Types**: Dedicated post types for jobs, employers, and jobseekers

### Approval & Access Control
- **Jobseeker Approval System**: Admin-controlled approval workflow
- **Inline Toggle**: One-click approval/unapproval from list page
- **Color-Coded Status**: Visual badges for instant status recognition
- **Access Restrictions**: Unapproved users cannot apply for jobs
- **Filtered Browsing**: Employers only see approved jobseekers

### File Management
- **Media Library Integration**: All uploads tracked in WordPress Media Library
- **Custom Upload Folder**: Organized "Camp-Uploads" folder structure
- **Uploader Tracking**: Metadata showing who uploaded each file
- **Logo Upload Fix**: Single URL storage, no WebP conversion for logos
- **File Restrictions**: Type and size validation (client & server-side)

### Email System
- **Styled HTML Emails**: Beautiful, responsive email templates
- **Welcome Emails**: Custom emails for employers (teal) and jobseekers (purple)
- **Approval Notifications**: Automatic emails on jobseeker approval
- **Admin Alerts**: Notifications when new users register
- **Password Reset Integration**: Valid password reset links in welcome emails

### Form & Validation
- **WPForms Integration**: Seamless registration form processing
- **Duplicate Email Prevention**: Server-side validation preventing duplicate registrations
- **Role-Specific Messages**: Different error messages for employer vs jobseeker
- **Styled Confirmations**: Custom HTML confirmation pages post-registration

## Installation

1. Upload the plugin files to `/wp-content/plugins/oso-jobs-portal/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure settings under OSO Jobs in the admin menu

## Restore Points

- **restore-point-approval-system-complete** (v1.0.14 - Dec 12, 2025): Complete jobseeker approval system with inline toggle and color-coded badges
- **restore-point-infinite-loop-fix** (v1.0.11 - Dec 12, 2025): Fixed infinite loop in jobseeker approval causing memory exhaustion
- **restore-point-2025-12-12**: Status badges styling fixed, job cards improved with gray badges and better spacing
- **restore-point-2025-12-08**: Email system overhaul, file upload improvements, duplicate prevention

To restore to a specific point:
```bash
git checkout <restore-point-name>
```

Example:
```bash
git checkout restore-point-approval-system-complete
```

## Technical Details

### File Structure
- `includes/class-oso-jobs-portal.php` - Core plugin class with approval system, columns, AJAX handlers
- `includes/wpforms/class-oso-jobs-wpforms-handler.php` - Form submissions, user creation, email sending, duplicate validation, default approval status
- `includes/shortcodes/class-oso-jobs-shortcodes.php` - AJAX file upload handler, jobseeker filtering, approval checks
- `assets/js/admin.js` - Inline approval toggle JavaScript with AJAX (v1.0.14)
- `assets/css/admin.css` - Admin styling including color-coded approval badges (v1.0.14)
- `assets/js/employer-portal.js` - Client-side file validation (v1.0.18)
- `assets/css/employer-portal.css` - Frontend styling with button hover fixes (v1.0.22)

### Database Schema
- **Meta Keys**:
  - `_oso_jobseeker_approved` - Approval status ('1' = approved, '0' = pending)
  - `_oso_jobseeker_user_id` - Linked WordPress user ID
  - `_oso_jobseeker_email` - Jobseeker email address
  - `_oso_employer_*` - Various employer profile fields

### AJAX Actions
- `oso_toggle_jobseeker_approval` - Toggle approval status inline
- `oso_refresh_approval_nonce` - Refresh nonce after toggle
- `oso_submit_job_application` - Submit job application (with approval check)
- `oso_upload_profile_file` - Upload files to Media Library

### Custom Filters
- `wp_new_user_notification_email` - Disables default WordPress user emails for custom roles
- `wp_new_user_notification_email_admin` - Keeps admin notifications enabled
- `intermediate_image_sizes_advanced` - Prevents thumbnail generation for logos
- `wp_image_editors` - Disables WebP conversion for logo uploads

### Shortcodes
- `[oso_employer_password_link email="{field_id}"]` - Generates password reset button with actual reset key

### Version Management
- Plugin Version: 1.0.14
- CSS Version: 1.0.22 (employer-portal.css), 1.0.14 (admin.css)
- JS Version: 1.0.18 (employer-portal.js), 1.0.14 (admin.js)
