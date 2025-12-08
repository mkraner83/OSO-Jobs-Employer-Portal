# OSO Jobs Portal

WordPress plugin for managing job postings, employer profiles, and jobseeker profiles for OSO Jobs.

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

- Job posting management
- Employer profile management
- Jobseeker profile management
- WPForms integration for registration
- Custom post types for jobs, employers, and jobseekers
- File upload handling with Media Library integration
- Duplicate email prevention
- Custom styled welcome emails

## Installation

1. Upload the plugin files to `/wp-content/plugins/oso-jobs-portal/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure settings under OSO Jobs in the admin menu

## Restore Points

- **restore-point-2025-12-08**: Email system overhaul, file upload improvements, duplicate prevention

To restore to this point:
```bash
git checkout restore-point-2025-12-08
```

## Technical Details

### File Structure
- `includes/wpforms/class-oso-jobs-wpforms-handler.php` - Handles form submissions, user creation, email sending, duplicate validation
- `includes/shortcodes/class-oso-jobs-shortcodes.php` - AJAX file upload handler with Media Library integration
- `assets/js/employer-portal.js` - Client-side file validation (v1.0.17)
- `assets/css/frontend.css` - Styling including WPForms confirmation messages (v1.0.20)

### Custom Filters
- `wp_new_user_notification_email` - Disables default WordPress user emails for custom roles
- `wp_new_user_notification_email_admin` - Keeps admin notifications enabled

### Shortcodes
- `[oso_employer_password_link email="{field_id}"]` - Generates password reset button with actual reset key
