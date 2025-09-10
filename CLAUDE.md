# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP-based ISP (Internet Service Provider) management system with MikroTik router integration. The system manages clients, billing, network services, and infrastructure for internet service providers.

## Architecture

### Core Framework
- **Custom MVC Framework**: Built on a custom PHP MVC architecture
- **Service Layer Pattern**: Business logic separated into dedicated service classes
- **Event-Driven Architecture**: Uses listeners and event managers for decoupled operations
- **Repository Pattern**: Database operations handled through custom Mysql class with query builders

### Key Components

1. **Controllers/** - Handle HTTP requests and coordinate between models and views
2. **Models/** - Data layer with database operations (suffix: `Model.php`)
3. **Views/** - PHP templates organized by controller name
4. **Services/** - Business logic layer (extend BaseService, registered in `Kernel/ServiceRegister.php`)
5. **Listeners/** - Event handlers for client state changes (activated, suspended, cancelled)
6. **Libraries/Core/** - Framework core (MVC, database, autoloader)
7. **Libraries/MikroTik/** - Router API integration for network management
8. **Helpers/** - Utility functions and system information

### Database
- MySQL database with comprehensive schema documented in `Squema.md`
- Database schema includes: clients, contracts, bills, payments, products, users, network infrastructure
- SQL dump available in `base_de_datos.sql`

## Development Commands

**Database Setup:**
```bash
# Import database schema
mysql -u username -p database_name < base_de_datos.sql
```

**Configuration:**
- Copy `Config/Config.example.php` to `Config/Config.php`
- Update database credentials and system settings in `Config/Config.php`

## Project Structure Conventions

### Controllers
- Extend `Controllers` base class
- Auto-load corresponding model: `{ControllerName}Model`
- Session management and permissions handled in constructor
- Methods correspond to routes: `/controller/method/params`

### Models  
- Extend database functionality through `Mysql` class
- Use query builders for complex operations
- File naming: `{Name}Model.php`

### Services
- Extend `BaseService` for event management capabilities
- Register new services in `Kernel/ServiceRegister.php`
- Handle business logic and external integrations (MikroTik, WhatsApp, email)

### Views
- Organized by controller name in `Views/{ControllerName}/`
- Use shared templates in `Views/Resources/`
- Modals in `Views/Resources/modals/`

## Key Features

### Network Management
- MikroTik router integration via API (`Libraries/MikroTik/`)
- Client network provisioning (PPPoE, Simple Queues)
- Bandwidth management and monitoring
- Network zones and access point management

### Billing System
- Automated billing generation
- Payment processing and tracking
- Multiple voucher types and currencies
- PDF invoice generation with custom templates

### Client Management
- Full client lifecycle (activation, suspension, cancellation)
- Service contracts and plan management
- Installation tracking and technical support
- Geographic location tracking (GPS coordinates)

### Communication
- WhatsApp integration for notifications
- Email services for invoices and notifications
- Campaign management for mass communications

## Important Constants

Defined in `Config/Config.php`:
- Module constants (DASHBOARD=1, CLIENTS=2, etc.)
- User roles (ADMINISTRATOR=1, TECHNICAL=2, CHARGES=3) 
- Client states and payment methods
- System configuration (timezone, encryption keys, API keys)

## File Uploads

Organized in `Assets/uploads/`:
- `business/` - Company logos and branding
- `users/` - User profile images  
- `products/` - Product images
- `gallery/` - Client and installation photos
- `pdf/` - Generated invoices and documents

## External Libraries

- **phpspreadsheet** - Excel import/export functionality
- **dompdf** - PDF generation for invoices
- **phpmailer** - Email services
- **phpqrcode** - QR code generation for payments
- **tinymce** - Rich text editor
- Various JavaScript libraries (Bootstrap, jQuery, DataTables)

## Development Notes

- No traditional testing framework setup - testing done via `Libraries/MikroTik/test.php` for router connections
- Custom autoloader in `Libraries/Core/Autoload.php`
- Uses composer for some vendor libraries in `Libraries/` subdirectories
- Frontend uses Bootstrap 4+ with custom CSS in `Assets/css/`
- JavaScript organized by functionality in `Assets/js/functions/`