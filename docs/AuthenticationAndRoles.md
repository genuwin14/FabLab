# Authentication and Role Management System

This document outlines the authentication mechanisms and role-based access control (RBAC) implemented in the Inventory Monitoring System.

## 1. User Roles

The system is built around three distinct user roles, defined in the `users` database table as an enum: `['customer', 'staff', 'admin']`.

| Role | Description | Default Dashboard |
| :--- | :--- | :--- |
| **Admin** | Full system access. manages users, products, inventory, settings, and reports. | `/admin/dashboard` |
| **Staff** | Operational access. Manages orders, supplies, and day-to-day inventory tasks. | `/staff/dashboard` |
| **Customer** | End-user access. Can browse products, manage cart, place orders, and customize items. | `/customer/dashboard` |

### Role Determination
- **Registration**: All new users registering via the signup form or Google Login are assigned the **Customer** role by default.
- **Login Redirection**: 
  - Users with `@admin.com` emails are redirected to the Admin Dashboard.
  - Users with `@staff.com` emails are redirected to the Staff Dashboard.
  - Others are redirected to the Customer Dashboard.
- **Middleware Enforcement**: Strict access control is enforced via the `CheckRole` middleware, ensuring users can only access routes authorized for their specific role.

## 2. Authentication Mechanisms

The system utilizes **Laravel Sanctum** to provide a robust authentication system. This setup supports both stateful authentication for the web interface (SPA-style) and token-based authentication for potential API consumers.

### A. Core Authentication (Sanctum)
- **Mechanism**: Laravel Sanctum.
- **Stateful Mode**: Uses cookie-based sessions for the web frontend, ensuring CSRF protection and secure session management.
- **API Tokens**: Capable of issuing personal access tokens for external devices or mobile apps if required.

### B. Registration
- **Restrictions**: 
  - Email domain must be either `@gmail.com` or `@my.cspc.edu.ph`.
  - Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.
  - Contact number is required and formatted to Philippine standard (+63).
- **Process**:
  1. Validates input.
  2. Creates `User` record (role: 'customer').
  3. Creates `UserInformation` record.
  4. Generates a 6-digit OTP (One-Time Password).
  5. Sends OTP via a local SMS gateway (MacroDroid).
  6. Redirects user to Phone Verification page.

### C. Login
- **Credentials**: Email and Password.
- **Rate Limiting**: Limited to 3 attempts every 3 minutes per email/IP pair.
- **Throttling**: Users are locked out temporarily after exceeding attempts.
- **Google OAuth**: Users can log in using their Google account via `Laravel Socialite`. If the user does not exist, a new account is auto-created with a random password.

### D. Password Reset
The system uses a custom session-based password reset flow:
1. **Forgot Password**: User requests a reset; a 6-digit code is generated and stored in the **Session**.
2. **Email Dispatch**: The code is sent via email (`Mail::raw`).
3. **Verification**: User enters the code, which is matched against the session data.
4. **Reset**: Upon successful verification, the user can set a new password.

### E. Phone Verification (OTP)
- **Method**: Local SMS Gateway (MacroDroid) via HTTP request.
- **Logic**: 
  - OTP is generated during registration.
  - Stored in `user_information` table.
  - Verified by user input on the frontend.
  - Used primarily for ensuring valid contact numbers for orders.

## 3. Security Implementation

### Middleware
- **`CheckRole`**: Protects route groups by verifying `Auth::user()->role`.
  - Usage: `middleware([CheckRole::class . ':admin'])`
- **`RedirectIfAuthenticated`**: Prevents logged-in users from accessing guest pages (login/register).
- **`PreventBackHistory`**: Prevents users from navigating back to protected pages after logout.

### Validation
- **Input Sanitization**: Standard Laravel validation rules.
- **Password Policy**: Enforced regex for complexity.
- **Domain Whitelisting**: Restricts registration to specific email domains.

## 4. Code References

- **Middleware**: `app/Http/Middleware/CheckRole.php`
- **Auth Controller**: `app/Http/Controllers/Auth/AuthController.php`
- **Routes**: `routes/web.php`
- **Migrations**: `database/migrations/0001_01_01_000000_create_users_table.php`

## 5. Implementation Phase Checklist

This checklist tracks the implementation progress of the authentication and role system using Laravel Sanctum.

### Phase 1: Setup & Configuration
- [x] **Install Sanctum**: Run `composer require laravel/sanctum`.
- [x] **Publish Configuration**: Publish the Sanctum configuration and migration files.
- [x] **Database Migration**: Run migrations to create `personal_access_tokens` table.
- [x] **Environment Config**: Ensure `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN` are correctly set in `.env` (refer to `docs/EnvironmentConfig.md` for credential handling).

### Phase 2: Backend Implementation
- [x] **Model Update**: Add `HasApiTokens` trait to the `User` model.
- [x] **Middleware**: Ensure `EnsureFrontendRequestsAreStateful` is added to the `api` middleware group (if using API routes for SPA).
- [x] **Auth Controller**: Refactor `AuthController` to support Sanctum's authentication logic.
- [x] **Routes**: Define authentication routes protected by `auth:sanctum`.

### Phase 3: Frontend Integration
- [x] **CSRF Protection**: Configure Axios/Fetch to handle the `X-XSRF-TOKEN` cookie (Sanctum CSRF cookie).
- [x] **Login Flow**: Update login forms to hit the Sanctum login endpoints.
- [x] **Role Handling**: Update frontend logic to handle role-based responses/redirects from the authenticated user data.

### Phase 4: SMS & OTP Integration
- [x] **Database Setup**: Create `UserInformation` migration (for phone/OTP storage) and Model.
- [x] **SMS Service**: Implement `SmsService` to handle PhilSMS integration (using credentials from `.env`).
- [x] **Registration Update**: Modify `AuthController@register` to create `UserInformation`, generate OTP, and trigger SMS sending.
- [x] **Verification Mode Selection**: Modal prompt to choose between Email or SMS for OTP.
- [x] **OTP Verification**: Create `OtpController` and verification views/routes.

### Phase 5: Verification & Security
- [ ] **Test Registration**: Verify new user registration (OTP flow compatibility).
- [ ] **Test Login**: Verify successful login and token/cookie generation.
- [ ] **Role Access**: Verify Admin/Staff/Customer redirection and access rights.
- [ ] **Rate Limiting**: Confirm rate limits on auth routes.

### Phase 6: Socialite (Google Login) Integration
- [x] **Installation**: Run `composer require laravel/socialite`.
- [x] **Configuration**: Add Google Client ID, Secret, and Redirect URI to `.env` and `config/services.php`.
- [x] **Backend Controller**:
  - [x] Implement `redirectToGoogle()` method in `AuthController`.
  - [x] Implement `handleGoogleCallback()` method in `AuthController`.
  - [x] Logic:
    - If user exists but is unverified: Redirect to verification modal.
    - If user does not exist: Create account, mark Email as verified, set default role, but enforce Phone verification (if required) or redirect to dashboard.
- [x] **Frontend Updates**:
  - [x] Bind "Sign Up with Google" button in `register.blade.php`.
  - [x] Bind "Continue with Google" button in `login.blade.php`.
- [x] **Testing**: Verify flow for new and existing Google users.
