# Supplier Management

This module maintains the database of vendors and supply sources.

## Page Sections

### 1. Suppliers Table
- **Company**: Displays the business name with a letter-avatar icon.
- **Contact Person**: The primary point of contact at the vendor's company.
- **Contact Info**: Combined view of Email and Phone number.
- **Address**: The physical location of the supplier.
- **Actions**: Edit and Delete.

### 2. Modals
- **Add Supplier**: Form for Name, Contact Person, Email, Phone, and Address.
- **Edit Supplier**: Update existing vendor information.
- **Delete Supplier**: Confirmation for removal.

## System Flow Integration
- **Foundation**: Suppliers must be registered here before they can be assigned to products in the **Product Management** module.
- **Procurement**: All Purchase Orders are linked to a profile managed in this module.
