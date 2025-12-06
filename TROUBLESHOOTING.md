# Troubleshooting Guide - OSO Jobs Portal

## Job Application Error: "An error occurred. Please try again."

### What the Apply Button Should Do

When a jobseeker clicks **"Submit Application"** on a job details page, the system should:

1. ✅ **Validate the form** - Ensure cover letter is filled and consent checkbox is checked
2. ✅ **Submit via AJAX** - Send application data to WordPress backend
3. ✅ **Create application record** - Create an `oso_job_application` custom post with:
   - Job ID (which job they're applying for)
   - Jobseeker ID (who is applying)
   - Cover letter content
   - Application status: "pending"
   - Employer ID (linked from the job posting)
   - Application date (current timestamp)
4. ✅ **Send email notification** - Email the employer about the new application
5. ✅ **Show success message** - Redirect to job details page with success message
6. ✅ **Display on dashboards** - Application appears in:
   - Jobseeker's dashboard under "My Applications"
   - Employer's dashboard under "Job Applications"

### How to Debug the Error

**Step 1: Check Browser Console**

1. Open browser DevTools (F12 or Right-click → Inspect)
2. Go to Console tab
3. Try submitting an application
4. Look for error messages (red text)

The JavaScript now logs detailed error information including:
- Network errors
- Server response errors
- AJAX status codes

**Step 2: Check WordPress Debug Log**

1. Enable WordPress debugging in `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

2. Check the debug log file at: `wp-content/debug.log`

3. Look for entries starting with `OSO Job Application:` - these show:
   - When AJAX handler is called
   - Nonce verification status
   - User login status
   - Job ID and Jobseeker ID values
   - Application creation success/failure
   - Specific error messages

**Step 3: Common Causes and Solutions**

### Error: "Security check failed"
**Cause:** Nonce verification failed (could be caching or session issue)
**Solution:**
- Clear browser cache (Ctrl+Shift+Delete)
- Log out and log back in
- Clear WordPress cache (WP Rocket, W3 Total Cache, etc.)
- Check if page is being cached - disable caching for job details pages

### Error: "You must be logged in to apply"
**Cause:** User session lost or not recognized
**Solution:**
- Verify you're logged in with a jobseeker account
- Check if multiple tabs caused session conflict
- Clear cookies and log in again

### Error: "Please fill in all required fields"
**Cause:** Form data not being sent properly
**Solution:**
- Ensure cover letter has content
- Check consent checkbox is checked
- Try typing directly in the cover letter field (not copy/paste)
- Disable browser extensions that might interfere with forms

### Error: "Invalid jobseeker profile"
**Cause:** Jobseeker profile not found or doesn't match logged-in user
**Solution:**
- Go to jobseeker dashboard and verify profile is complete
- Check if profile exists: WordPress Admin → OSO Jobseekers → find your profile
- Ensure `_oso_jobseeker_user_id` meta matches your WordPress user ID

### Error: "You have already applied for this position"
**Cause:** Duplicate application attempt
**Solution:**
- This is intentional - each jobseeker can only apply once per job
- Check your jobseeker dashboard to see existing application
- If you need to update your application, contact the employer directly

### Error: "Failed to create application post"
**Cause:** Database or permission error
**Solution:**
- Check WordPress error logs for database errors
- Verify `oso_job_application` custom post type is registered
- Check database user has INSERT permissions
- Ensure hosting has sufficient memory (check PHP memory_limit)

**Step 4: Test AJAX Endpoint**

You can manually test if the AJAX endpoint is working:

1. Open browser console
2. Run this command (replace values):
```javascript
jQuery.post(osoEmployerPortal.ajaxUrl, {
    action: 'oso_submit_job_application',
    nonce: osoEmployerPortal.jobNonce,
    job_id: 123,  // Replace with real job ID
    jobseeker_id: 456,  // Replace with real jobseeker ID
    cover_letter: 'Test application'
}, function(response) {
    console.log('Success:', response);
}).fail(function(xhr, status, error) {
    console.error('Error:', status, error, xhr.responseText);
});
```

### Required Data for Application

The application form needs:
- ✅ User must be logged in
- ✅ User must have a jobseeker profile (`oso_jobseeker` post)
- ✅ Job posting must exist and be active (not expired)
- ✅ Cover letter must be filled (required field)
- ✅ Consent checkbox must be checked
- ✅ Valid nonce for security
- ✅ Jobseeker profile `_oso_jobseeker_user_id` must match logged-in user ID

### File Locations

If you need to modify the application functionality:

- **Frontend Template:** `oso-jobs-portal-employer/includes/shortcodes/views/job-details.php`
- **AJAX Handler:** `oso-jobs-portal-employer/includes/shortcodes/class-oso-employer-shortcodes.php` (line 960+)
- **JavaScript:** Inline in `job-details.php` (line 614+)
- **CSS:** `oso-jobs-portal-employer/assets/css/employer-portal.css`

### Still Having Issues?

1. **Check server error logs** - Contact hosting provider for server-level errors
2. **Check PHP version** - Ensure PHP 7.4+ is running
3. **Check WordPress version** - Ensure WordPress 5.0+ is running
4. **Disable plugins** - Temporarily disable other plugins to check for conflicts
5. **Test with default theme** - Switch to Twenty Twenty-Three theme temporarily
6. **Contact support** - Provide debug log entries and browser console output

## Other Common Issues

### Profile Edit Button Wrong URL
**Solution:** Update plugin to v1.0.8+ and clear all caches

### Job Browser Not Showing Jobs
**Solution:** Check if user is logged in - job browser requires login

### Dashboard Not Displaying
**Solution:** 
- Verify shortcode is on the page: `[oso_jobseeker_dashboard]` or `[oso_employer_dashboard]`
- Check user role (jobseekers vs employers have different dashboards)
- Verify profile post exists in WordPress admin
