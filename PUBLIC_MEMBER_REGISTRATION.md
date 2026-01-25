# Public Member Registration Feature

## Overview
This feature allows users from the public internet to create their own member accounts without requiring authentication. All registered members are automatically assigned to the owner user with ID=2 (`parent_id=2`).

**Registration Types:**
- **Self Registration**: Users 18+ can register themselves. They must confirm their age before submitting.
- **Parent Registration**: Parents can register multiple children (under 18). The parent account is created with the parent's information, and each child is linked to the parent account with their own membership plan.

## Parent-Child Member Architecture

### Data Structure

**Parent Members**:
- Have a user account (can log in)
- `is_parent = 1`
- `relationship = 'parent'`
- `user_id` = their own User record ID
- Can have multiple children linked to them

**Child Members**:
- Do NOT have user accounts (cannot log in independently)
- `user_id = NULL` (no login access)
- `parent_member_id` = ID of their parent Member record
- `is_parent = 0`
- `relationship = 'child'`
- Each child has their own membership plan
- Managed entirely through parent's login

### Parent Portal Features

When a parent logs in, they can:
- View all their children in the dashboard
- See each child's membership plan and status
- Manage payments for all children's memberships
- View all children's membership history
- Access all payment records for the family

### Database Schema

**members table** (updated):
```sql
id                  - Primary key
user_id             - User ID for login (NULL for children, set for parents/self-registered)
parent_id           - Always 2 (owner ID for multi-tenancy)
parent_member_id    - ID of parent Member (0 for parent/self, parent's ID for children)
is_parent           - Boolean (1 for parent accounts, 0 for others)
relationship        - 'self', 'parent', or 'child'
first_name          - Member's first name
last_name           - Member's last name
email               - Email address
phone               - Phone number
dob                 - Date of birth
gender              - Gender
...other fields
```

## Implementation Details

### Files Created

1. **Controller**: `app/Http/Controllers/PublicMemberRegistrationController.php`
   - Handles public member registration logic
   - Creates both User and Member records
   - Assigns members to parent_id=2
   - Supports optional membership plan selection
   - Sends email/SMS notifications

2. **Routes**: Added to `routes/web.php`
   - `GET /register-member` - Display registration form
   - `POST /register-member` - Process registration
   - `GET /register-success` - Show success page

3. **Views**:
   - `resources/views/public/register.blade.php` - Registration form
   - `resources/views/public/register-success.blade.php` - Success page

### Key Features

#### Registration Type Selection
- **Self Registration (18+)**:
  - Requires age confirmation checkbox
  - Creates member account with registrant's information
  - User logs in with their own credentials
  
- **Parent Registration**:
  - Parent provides their contact information
  - Creates parent member account (with parent's name, email, phone)
  - Creates child member record linked to parent
  - Parent logs in with their credentials to manage all children
  - Parents can add multiple children to their account

#### Security & Validation
- Email uniqueness validation
- Password confirmation required (minimum 6 characters)
- All required fields validated
- XSS protection middleware applied
- Optional ReCAPTCHA support (if enabled for parent_id=2)

#### Member Data Collected
- **Required Fields**:
  - First Name
  - Last Name
  - Email (unique)
  - Password (with confirmation)
  - Phone Number (with country code)
  - Date of Birth
  - Gender (Male/Female)
  - Address

- **Optional Fields**:
  - Profile Image
  - Emergency Contact Information
  - Membership Plan Selection
  - Additional Notes

#### Automatic Processes
1. **Self Registration**:
   - Creates user account with member's info
   - Email is automatically verified
   - Password is hashed
   - Assigned to parent_id=2

2. **Parent Registration**:
   - Creates parent user account with parent's info (name, email, phone)
   - Creates parent member record (marked as `is_parent=true`, `relationship='parent'`)
   - Creates child member record(s) linked to parent (`parent_member_id` set, `user_id=NULL`)
   - Each child has their own membership plan
   - Parent logs in to view and manage all children
   - Children cannot log in independently (no user account)

3. **Optional Membership Assignment**:
   - If plan selected, creates Membership record
   - Status set to 'Pending' (awaiting payment)
   - Expiry date calculated based on plan duration
   - Start date set to registration date

4. **Notifications**:
   - Sends welcome email (if configured)
   - Sends SMS notification (if Twilio configured)
   - Uses notification templates from settings

## Usage

### Accessing the Registration Form

**URL**: `https://your-domain.com/register-member`

This URL is publicly accessible (no authentication required).

### Registration Flow

1. **User fills out registration form**:
   - Enters personal information
   - Optionally selects a membership plan
   - Uploads profile image (optional)

2. **Form submission**:
   - Data validated
   - User and Member records created with parent_id=2
   - Membership created if plan selected
   - Welcome notifications sent

3. **Success page displayed**:
   - Confirmation message shown
   - Instructions to log in
   - Next steps outlined

4. **User can log in**:
   - Use registered email and password
   - Access member dashboard
   - View/update profile

### Administrator Actions

The owner (user_id=2) can:
- View all registered members in the admin panel
- Approve/manage membership payments
- Activate memberships after payment
- Edit member information
- Assign additional membership plans

## Configuration

### Prerequisites

1. **User ID 2 must exist** with type='owner'
2. **Member role must exist** for parent_id=2
3. **Settings configured** for parent_id=2 (company logo, email, etc.)

### Optional Configuration

#### Email Notifications
Configure SMTP settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
```

#### SMS Notifications (Twilio)
Configure Twilio in settings for parent_id=2:
- `twilio_sid`
- `twilio_token`
- `twilio_from`

#### ReCAPTCHA (Optional)
Enable in settings for parent_id=2:
- `google_recaptcha` = 'on'
- `recaptcha_key`
- `recaptcha_secret`

## Membership Plans

### Creating Membership Plans

1. Log in as owner (user_id=2)
2. Navigate to Membership Plans
3. Create plans with:
   - Plan name
   - Duration (Monthly, 3-Month, 6-Month, Yearly)
   - Price
   - Description

### How Plans Work in Registration

- Plans shown in dropdown during registration
- User can select a plan or skip
- If selected:
  - Membership record created with status='Pending'
  - Payment required for activation
  - Admin can approve and activate

## Testing the Feature

### Manual Testing Steps

1. **Access registration page**:
   ```
   Navigate to: /register-member
   ```

2. **Fill out form** with test data:
   - Use unique email address
   - Provide valid phone number
   - Complete all required fields

3. **Submit form**:
   - Should redirect to success page
   - Check for validation errors if any

4. **Verify in database**:
   ```sql
   -- Check user created
   SELECT * FROM users WHERE parent_id=2 ORDER BY id DESC LIMIT 1;
   
   -- Check member created
   SELECT * FROM members WHERE parent_id=2 ORDER BY id DESC LIMIT 1;
   
   -- Check membership if plan selected
   SELECT * FROM memberships WHERE parent_id=2 ORDER BY id DESC LIMIT 1;
   ```

5. **Test login**:
   - Navigate to /login
   - Use registered email and password
   - Should successfully log in

### Test Data Example

```
First Name: John
Last Name: Doe
Email: john.doe@example.com
Password: Test@123
Phone: +1234567890
DOB: 1990-01-15
Gender: Male
Address: 123 Test Street, Test City
```

## Troubleshooting

### Common Issues

**Issue**: "Member role not found"
- **Solution**: Ensure a role named 'member' exists for parent_id=2

**Issue**: Email not unique error
- **Solution**: User with that email already exists; use different email

**Issue**: No membership plans showing
- **Solution**: Create membership plans for parent_id=2

**Issue**: Notifications not sending
- **Solution**: 
  - Check SMTP/Twilio configuration
  - Verify notification templates exist for parent_id=2
  - Check notification module 'member_create' is enabled

**Issue**: Image upload fails
- **Solution**: 
  - Ensure `storage/upload/member/` directory exists
  - Check directory permissions (775 or 777)
  - Run: `php artisan storage:link`

## Customization

### Modifying Required Fields

Edit `PublicMemberRegistrationController.php` line ~40:
```php
$validator = \Validator::make(
    $request->all(),
    [
        // Add or remove field validations here
    ]
);
```

### Changing Parent ID

If you want to assign to a different owner:

1. Update controller - change all `2` to your desired parent_id
2. Update settings retrieval in views to use correct parent_id

### Customizing Form Layout

Edit `resources/views/public/register.blade.php` to:
- Change field order
- Add/remove fields
- Modify styling
- Add custom JavaScript validation

### Adding Custom Fields

1. Add field to migration (if needed)
2. Add to `$fillable` in Member model
3. Add to registration form view
4. Add validation rule in controller
5. Assign value when creating member

## Security Considerations

### Best Practices Implemented

- ✅ Password hashing with bcrypt
- ✅ Email uniqueness validation
- ✅ CSRF protection (Laravel default)
- ✅ XSS middleware applied
- ✅ Input validation and sanitization
- ✅ File upload restrictions (images only)
- ✅ Rate limiting (Laravel default)

### Additional Security Recommendations

1. **Enable ReCAPTCHA** to prevent bot registrations
2. **Email Verification**: Consider adding email verification flow
3. **Rate Limiting**: Add custom rate limiting for registration endpoint
4. **Password Requirements**: Enforce stronger password rules
5. **File Size Limits**: Set max upload size for images
6. **Spam Prevention**: Implement honeypot fields or similar

## Future Enhancements

Potential improvements to consider:

- [ ] Email verification before account activation
- [ ] Payment gateway integration for immediate membership activation
- [ ] Multi-step registration form with progress indicator
- [ ] Social media registration (Google, Facebook)
- [ ] Custom registration fields configuration in admin
- [ ] Bulk import/export functionality
- [ ] Member referral system
- [ ] Auto-login after successful registration

## API Documentation

### Public Registration Endpoint

**Endpoint**: `POST /register-member`

**Headers**:
```
Content-Type: multipart/form-data
X-CSRF-TOKEN: {csrf_token}
```

**Request Parameters**:
```json
{
  "first_name": "string (required)",
  "last_name": "string (required)",
  "email": "email (required, unique)",
  "password": "string (required, min:6)",
  "password_confirmation": "string (required)",
  "phone": "string (required)",
  "dob": "date (required)",
  "gender": "string (required, Male|Female)",
  "address": "string (required)",
  "emergency_contact_information": "string (optional)",
  "notes": "string (optional)",
  "image": "file (optional, image/*)",
  "plan_id": "integer (optional)"
}
```

**Success Response** (302 Redirect):
```
Location: /register-success
```

**Error Response** (302 Redirect):
```
Location: /register-member (with errors in session)
```

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check database records for parent_id=2
4. Verify all prerequisites are met

## License

This feature is part of the Membership CRM system and follows the same license as the main application.
