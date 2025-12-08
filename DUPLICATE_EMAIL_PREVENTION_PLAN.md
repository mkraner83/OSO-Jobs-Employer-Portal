# Duplicate Email Prevention Plan

## Problem Statement
Currently, both Employer and Jobseeker registration forms use WPForms, which doesn't natively prevent duplicate emails across different forms. We need to implement validation to ensure:
1. No two employers can register with the same email
2. No two jobseekers can register with the same email
3. An employer cannot register with an email already used by a jobseeker (and vice versa)

## Current Architecture
- **Registration Method**: WPForms plugin
- **User Roles**: `oso_employer` and `oso_jobseeker`
- **Handler Location**: `/oso-jobs-portal/includes/wpforms/class-oso-jobs-wpforms-handler.php`
- **Form Processing**: `wpforms_process_complete` hook

## Implementation Plan

### Step 1: Add Email Validation Hook
**File**: `class-oso-jobs-wpforms-handler.php`

Add a new action hook that fires BEFORE form submission is processed:
```php
add_action( 'wpforms_process', array( $this, 'validate_unique_email' ), 10, 3 );
```

### Step 2: Create Validation Method
```php
/**
 * Validate that email is unique across all users.
 *
 * @param array $fields    Form fields.
 * @param array $entry     Entry data.
 * @param array $form_data Form data.
 */
public function validate_unique_email( $fields, $entry, $form_data ) {
    // Get form ID to identify if it's employer or jobseeker registration
    $form_id = $form_data['id'];
    
    // Define your form IDs (get from WPForms)
    $employer_form_id = XXX;  // Replace with actual employer form ID
    $jobseeker_form_id = YYY; // Replace with actual jobseeker form ID
    
    // Check if this is one of our registration forms
    if ( $form_id != $employer_form_id && $form_id != $jobseeker_form_id ) {
        return;
    }
    
    // Find the email field
    $email = '';
    $email_field_id = '';
    
    foreach ( $fields as $field_id => $field ) {
        if ( $field['type'] === 'email' ) {
            $email = sanitize_email( $field['value'] );
            $email_field_id = $field_id;
            break;
        }
    }
    
    if ( empty( $email ) ) {
        return;
    }
    
    // Check if email already exists in WordPress users
    if ( email_exists( $email ) ) {
        wpforms()->process->errors[ $form_id ][ $email_field_id ] = 
            __( 'This email address is already registered. Please use a different email or log in.', 'oso-jobs-portal' );
    }
}
```

### Step 3: Add User-Friendly Error Messages
Customize error messages based on context:
```php
// If checking employer registration
if ( $form_id == $employer_form_id ) {
    $user = get_user_by( 'email', $email );
    if ( $user && in_array( 'oso_jobseeker', $user->roles ) ) {
        wpforms()->process->errors[ $form_id ][ $email_field_id ] = 
            __( 'This email is already registered as a Job Seeker. Please use a different email or contact support.', 'oso-jobs-portal' );
    } else if ( $user && in_array( 'oso_employer', $user->roles ) ) {
        wpforms()->process->errors[ $form_id ][ $email_field_id ] = 
            __( 'This email is already registered as an Employer. Please log in instead.', 'oso-jobs-portal' );
    }
}
```

### Step 4: Alternative - AJAX Validation (More User-Friendly)
For real-time validation as users type:

**JavaScript** (`frontend.js`):
```javascript
// Add email validation on blur
jQuery(document).on('blur', '.wpforms-form input[type="email"]', function() {
    var $input = jQuery(this);
    var email = $input.val();
    var formId = $input.closest('form').data('formid');
    
    // Only check for registration forms
    if (formId != EMPLOYER_FORM_ID && formId != JOBSEEKER_FORM_ID) {
        return;
    }
    
    jQuery.ajax({
        url: osoJobsPortal.ajaxUrl,
        type: 'POST',
        data: {
            action: 'oso_check_email_exists',
            email: email,
            nonce: osoJobsPortal.nonce
        },
        success: function(response) {
            if (!response.success) {
                // Show error message
                $input.addClass('wpforms-error');
                $input.after('<label class="wpforms-error">' + response.data.message + '</label>');
            } else {
                // Clear any previous errors
                $input.removeClass('wpforms-error');
                $input.siblings('.wpforms-error').remove();
            }
        }
    });
});
```

**PHP Handler**:
```php
add_action( 'wp_ajax_oso_check_email_exists', array( $this, 'ajax_check_email_exists' ) );
add_action( 'wp_ajax_nopriv_oso_check_email_exists', array( $this, 'ajax_check_email_exists' ) );

public function ajax_check_email_exists() {
    check_ajax_referer( 'oso-wpforms-nonce', 'nonce' );
    
    $email = sanitize_email( $_POST['email'] );
    
    if ( empty( $email ) || ! is_email( $email ) ) {
        wp_send_json_success();
    }
    
    if ( email_exists( $email ) ) {
        wp_send_json_error( array(
            'message' => __( 'This email address is already registered.', 'oso-jobs-portal' )
        ) );
    }
    
    wp_send_json_success();
}
```

## Implementation Steps Summary

1. **Identify WPForms IDs**
   - Go to WPForms → All Forms
   - Note the Form IDs for Employer and Jobseeker registration forms

2. **Add Server-Side Validation**
   - Modify `class-oso-jobs-wpforms-handler.php`
   - Add `validate_unique_email()` method
   - Hook into `wpforms_process`

3. **Test Cases**
   - Try registering employer with existing employer email → Should fail
   - Try registering employer with existing jobseeker email → Should fail
   - Try registering jobseeker with existing employer email → Should fail
   - Try registering jobseeker with existing jobseeker email → Should fail
   - Try registering with new email → Should succeed

4. **Optional Enhancement - AJAX Validation**
   - Add real-time email checking as user types
   - Improves UX by catching duplicates early
   - Add JavaScript validation in `frontend.js`
   - Add AJAX handler in PHP

5. **Error Message Customization**
   - Provide clear, actionable error messages
   - Suggest alternative actions (login, use different email)
   - Distinguish between employer/jobseeker conflicts

## Files to Modify

1. `/oso-jobs-portal/includes/wpforms/class-oso-jobs-wpforms-handler.php`
   - Add validation method
   - Add AJAX handler (optional)

2. `/oso-jobs-portal/assets/js/frontend.js` (optional for AJAX)
   - Add real-time email validation

3. `/oso-jobs-portal/includes/class-oso-jobs-portal.php` (optional for AJAX)
   - Localize script with form IDs

## Security Considerations

- Always sanitize email input: `sanitize_email()`
- Verify nonces for AJAX requests
- Use `email_exists()` WordPress function (secure and tested)
- Don't expose which role already has the email (privacy)
- Rate limit AJAX checks to prevent enumeration attacks

## Future Enhancements

1. **Merge Accounts Feature**
   - Allow users to link employer and jobseeker accounts
   - Single login, multiple roles

2. **Email Change Process**
   - Allow users to update their email
   - Verify new email before switching
   - Update all linked profiles

3. **Admin Notification**
   - Notify admin when duplicate attempts occur
   - May indicate confusion or potential issues

## Testing Checklist

- [ ] Employer cannot register with existing employer email
- [ ] Employer cannot register with existing jobseeker email
- [ ] Jobseeker cannot register with existing jobseeker email
- [ ] Jobseeker cannot register with existing employer email
- [ ] Error messages are clear and helpful
- [ ] Valid new emails can register successfully
- [ ] AJAX validation works in real-time (if implemented)
- [ ] Form submission blocked when email exists
- [ ] No PHP errors in debug log
- [ ] No JavaScript console errors

## Resources

- [WPForms Hooks Documentation](https://wpforms.com/developers/wpforms-hooks/)
- [WordPress email_exists() Function](https://developer.wordpress.org/reference/functions/email_exists/)
- [WPForms Validation](https://wpforms.com/developers/how-to-add-custom-validation-to-a-field/)

## Notes

- WPForms Pro may have built-in duplicate email validation
- Check WPForms settings first before custom implementation
- Consider using WPForms conditional logic if available
- This plan works for WPForms Free and Pro versions
