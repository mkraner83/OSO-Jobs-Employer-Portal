# Changelog - OSO Jobs Employer Extension

## [v1.5.0] - January 5, 2026
- **Public Jobs Listing**: Added [oso_public_jobs] shortcode for unregistered users
  - Browse all active jobs without contact information
  - Employer logos with 80px rectangle design
  - Filters: search, location, job type, sort
  - Login/Register prompts to access contact details
  - Alternating row backgrounds for job metadata
  - Purple gradient theme with OSO green accents
  
- **Public Camps Listing**: Added [oso_public_camps] shortcode for unregistered users
  - Browse all employers/camps without contact info
  - Shows: logo, name, location, description, active job count
  - Contact details (email, phone, website) hidden until login
  - "View Open Positions" button links to filtered jobs
  - Register links to general registration page
  
- **Public Jobseekers Listing**: Added [oso_public_jobseekers] shortcode for unregistered users
  - Limited info display: first name only (last name as ***)
  - Placeholder photos (no real images)
  - First 3 skills shown with "+X more" indicator
  - Truncated "Why interested" text preview
  - Login/Register buttons instead of View Profile
  - Register links to employer registration page
  - Filters: search, location, over 18, sort
  
- **Email Template Improvements**: Variables and descriptions now persist after customization
- **Design Enhancements**: 
  - Fixed employer logo display (direct URL vs attachment ID)
  - Updated jobseeker dashboard View Profile button (OSO green + correct link)
  - White titles on purple gradient headers
  - Fixed filter field spacing to prevent button overlap
  - Responsive grid layouts for all public pages
  
- **Build Optimization**: Selective ZIP rebuilding (only modified plugins)

## [v1.4.0] - January 5, 2026
- **Application System**: Removed duplicate restriction - jobseekers can now apply to unlimited jobs (but not same job twice)
- **Rejection Notifications**: Added email notification when employers reject applications ("No Thanks!")
- **Button Labels**: Updated employer dashboard buttons - "Approve" → "Let's Chat!", "Reject" → "No Thanks!"
- **WhatsApp Integration**: Added WhatsApp contact banner on jobseeker dashboard (OSO green #527D80)
- **Email Templates**: Rejection email now pulls from customizable template system
- **Template Management**: Individual "Reset Template" button for each email template
- **Email Design**: Updated Employer and Jobseeker welcome emails to proper table-based HTML format
- **Email Consistency**: All email templates now use consistent purple gradient design

## [v1.3.1] - December 23, 2025
- Project restored to December 12, 2025 session (v1.0.37-session-end)
- Fixed: Long URLs in "Application Instructions" field now break lines
- Rebuilt and pushed updated employer plugin zip

## [v1.3.0] - December 12, 2025
- Complete Jobseeker Approval System
- Inline Approval Toggle
- Color-Coded Badges
- Email Notifications
- Access Control
- Employer Registration & Profile Management
- Jobseeker Browser & Filtering
- Job Posting System
- File Uploads & Media Library Integration
- Styled HTML Emails
