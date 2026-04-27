# User Management

The User Management module allows administrators to control system access and manage different user roles.

## Page Sections (Tabbed Interface)

### 1. Administrators Tab
- **Purpose**: Manage high-level system admins.
- **Details**: Name, Email, Contact, Joined Date, and Password Status.
- **Actions**: Enable/Disable account.

### 2. Staff Tab
- **Purpose**: Manage warehouse or operational staff.
- **Details**: Displays staff-specific contact and status info.
- **Actions**: Enable/Disable account.

### 3. Customers Tab
- **Purpose**: Manage registered customers/clients.
- **Academic Info**: Displays Degree, Year, and Section (specific to the system's target environment).
- **Contact Details**: Quick view of phone and email.
- **Actions**: Enable/Disable account.

### 4. Status Modal
- **Function**: Triggered when Clicking Enable/Disable.
- **Safety**: Requires confirmation to change a user's access status.

## System Flow Integration
- **Access Control**: This module is the central authority for who can log into the Admin, Staff, or Customer portals.
- **Order Attribution**: Customer records here are linked to the transactions seen in **Order Management**.
