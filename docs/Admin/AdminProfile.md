# Admin Profile

The Profile module allows administrators to manage their own account information and security settings via a premium modal interface.

## Modal Sections

### 1. Photo & Summary (Left Sidebar)
- **Profile Photo**: Displays the current user's photo or a generated avatar.
- **Photo Upload**: Camera icon overlay to trigger the local file selector.
- **Summary**: Displays the Full Name and Email of the logged-in admin.

### 2. Personal Information (Right Content)
- **Fields**: Full Name, Address, Email Address, and Contact Number.
- **Interface**: Uses floating labels for a modern, clean look.

### 3. Security Section
- **Password Status**:
    - If no password is set: Shows a "Set Account Password" button to enable email login.
    - If password is set: Displays a "Password Secured" status with a link to the "Forgot Password" feature for changes.

## Actions
- **Save Changes**: Submits the multipart form to `admin.profile.update`.
- **Close**: Dismisses the modal without saving.

## System Flow Integration
- **Identity**: Changes made here update the information displayed in the Top Navbar and Welcome Banner on the Dashboard.
- **Authentication**: Setting a password allows the administrator to use standard email/password login instead of relying solely on external SSO or temporary access.
