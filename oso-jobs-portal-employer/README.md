# OSO Jobs Portal - Employer Section

Add-on plugin for employer registration, dashboards, and admin tools.

## Requirements

**This plugin requires the OSO Jobs Portal plugin to be installed and activated first.**

## Installation

1. Install and activate the main **OSO Jobs Portal** plugin
2. Upload this plugin to `/wp-content/plugins/oso-jobs-portal-employer/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Important Notes

- The main plugin folder should be: `oso-jobs-portal`
- This plugin folder should be: `oso-jobs-portal-employer`
- Both plugins must be activated for the employer features to work

## Features

- Employer registration via WPForms (Form ID: 1917)
- Employer dashboard shortcode: `[oso_employer_dashboard]`
- Automatic WordPress user account creation for employers
- Employer custom post type integration

## Troubleshooting

If you see an error message "The OSO Jobs Portal plugin must be installed and active":

1. Verify the main OSO Jobs Portal plugin is installed in `/wp-content/plugins/oso-jobs-portal/`
2. Ensure the main plugin is **activated** in WordPress admin
3. Check that the main plugin file is: `oso-jobs-portal/oso-jobs-portal.php`
4. Deactivate and reactivate this employer plugin after the main plugin is active

## Usage

### Employer Dashboard Shortcode

Add this shortcode to any page to display the employer dashboard:

```
[oso_employer_dashboard]
```

The dashboard will:
- Show a login form for non-logged-in users
- Display employer profile information
- List all jobs posted by the employer
- Provide access to edit and manage jobs
