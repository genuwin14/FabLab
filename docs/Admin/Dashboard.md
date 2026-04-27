# Admin Dashboard

The Admin Dashboard provides a high-level overview of the Inventory Monitoring System's performance and key activities. It serves as the landing page for administrators after logging in.

## Page Sections

### 1. Welcome Banner
- **Content**: Displays a personalized greeting to the logged-in administrator.
- **Visuals**: Modern gradient background (#0e2e45 to #1a4b6e) with a speedometer icon decoration.

### 2. Statistics Cards (Key Metrics)
Four cards highlight critical system data:
- **Total Revenue**: Displays the total accumulated revenue (Sample: $12,450).
- **Active Users**: Shows the count of active users in the system (Sample: 1,240).
- **Low Stock Items**: Alerts the admin to the number of products below their stock threshold (Sample: 5).
- **Pending Orders**: Shows the count of customer orders awaiting processing (Sample: 8).

### 3. Recent Orders Table
A summarized view of the latest customer orders.
- **Columns**: Order ID, Customer, Amount, Status, and Action.
- **Actions**: "View All" link to navigate to the full Order Management page; "Eye" icon to view specific order details.

### 4. System Alerts
Displays critical notifications for immediate attention:
- **Low Stock Warnings**: Specific SKU alerts.
- **New User Registrations**: Logs of recent sign-ups.

### 5. Navigation Sidebar
- **Purpose**: Provides quick access to all administrative modules.
- **Responsive**: Standard sidebar for desktop; Off-canvas sidebar for mobile devices.

### 6. Top Navbar
- **Purpose**: Displays the current page title and user profile options.
- **Features**: Includes a toggle for the mobile sidebar and access to the Logout modal.

## System Flow Integration
- **From Login**: The dashboard is the primary destination after successful authentication.
- **To Modules**: Clicking on "Low Stock Items" or "Pending Orders" leads the administrator to the Inventory Monitoring or Order Management modules, respectively.
