# Technology Stack (v1)

This document outlines the technologies, frameworks, and libraries currently used in the Inventory Monitoring System (Version 1).

## 1. Backend Framework
- **Framework**: [Laravel](https://laravel.com/) (v12.0)
- **Language**: PHP (v8.2+)
- **Architecture**: MVC (Model-View-Controller)

## 2. Front-End Technologies
- **Templating Engine**: [Blade](https://laravel.com/docs/blade) (Laravel Native)
- **CSS Framework**: 
  - [Bootstrap 5](https://getbootstrap.com/) (CDN)
  - Custom CSS (Inline & separate files)
- **JavaScript Libraries**:
  - [jQuery](https://jquery.com/) (v3.6.0) - logic handling
  - [Chart.js](https://www.chartjs.org/) - dashboard visualization
  - [SweetAlert2](https://sweetalert2.github.io/) - interactive alerts/modals
  - [jsPDF](https://github.com/parallax/jsPDF) & `jspdf-autotable` - client-side PDF generation
- **Icons**:
  - [FontAwesome](https://fontawesome.com/) (v6.4.0)
  - [Bootstrap Icons](https://icons.getbootstrap.com/)

## 3. Database
- **Database Engine**: MySQL (or MariaDB)
- **ORM**: [Eloquent](https://laravel.com/docs/eloquent)

## 4. Authentication & Security
- **Core Auth**: Laravel Native Authentication (Guard based: `web`)
- **API Authentication**: [Laravel Sanctum](https://laravel.com/docs/sanctum)
- **Social Login**: `laravel/socialite` (v5.21) - **Google** Login support
- **Role Management**: Custom Middleware (`CheckRole` for Admin, Staff, Customer)

## 5. Communication Services
- **Email**: Laravel `Mail` Facade (SMTP)
- **SMS**: Custom Local Gateway
  - **Tool**: [MacroDroid](https://www.macrodroid.com/) (Android Automation App)
  - **Mechanism**: The system sends an HTTP GET request to a local Android device running a MacroDroid Web Server (e.g., `http://192.168.0.114:8080/sms`), which then sends the actual SMS via the phone's SIM card.

## 6. Server Environment
- **Web Server**: Apache/Nginx (via XAMPP/Laragon or equivalent)
- **Dependency Manager**: [Composer](https://getcomposer.org/) (PHP), [NPM](https://www.npmjs.com/) (JS Assets)

## 7. Development Tools
- **Version Control**: Git
- **API Testing**: Postman (implied)
