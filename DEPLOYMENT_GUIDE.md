# Content Filtering Module - Deployment Guide

## Overview
This module adds comprehensive content filtering capabilities to the ISP management system, allowing administrators to block access to social media, YouTube, pornography, and other categories of content through MikroTik routers.

## Features
- **Content Categories**: Pre-defined categories (Social Media, YouTube, Adult Content, Entertainment, Shopping, Streaming)
- **Filtering Policies**: Create custom policies combining different categories
- **Client Management**: Apply/remove filtering policies per client
- **Bulk Operations**: Apply filtering to multiple clients at once
- **Activity Logging**: Track all filtering operations
- **MikroTik Integration**: Uses REST API for DNS blocking and web proxy rules

## Installation Steps

### 1. Database Setup
Execute the database schema to create the required tables:

```sql
-- Run the following SQL file
mysql -u [username] -p [database_name] < database_content_filtering.sql
```

### 2. File Structure
The following files have been added/modified:

**New Files:**
- `Services/ContentFilterService.php` - Business logic for content filtering
- `Models/ContentfilterModel.php` - Database operations
- `Controllers/Network.php` - Extended with content filtering methods
- `Views/Network/contentfilter.php` - Main interface
- `Assets/js/functions/contentfilter.js` - Frontend functionality
- `Libraries/MikroTik/Router.php` - Extended with DNS and proxy methods

**Modified Files:**
- `Kernel/ServiceRegister.php` - Added ContentFilterService
- `Views/Resources/includes/sidemenu.php` - Added menu item

### 3. Router Configuration Requirements

For the content filtering to work with MikroTik routers:

#### Enable REST API on MikroTik:
```bash
# Connect to router via SSH or Winbox
/ip service enable api
/ip service enable api-ssl
/ip service set api port=8728
/ip service set api-ssl port=8729
```

#### Create API User (recommended):
```bash
# Create a dedicated user for API access
/user add name=api_user password=secure_password group=full
```

#### Enable DNS Server (if not already enabled):
```bash
/ip dns set allow-remote-requests=yes servers=8.8.8.8,1.1.1.1
```

### 4. Access Control
The content filtering module uses the same permission system as the network management. Users need the "INSTALLATIONS" module permission to access content filtering features.

## Usage Guide

### 1. Access the Module
Navigate to: **Gestión de Red → Filtro de Contenido**

### 2. Create Filtering Policies
1. Click "Nueva Política"
2. Enter policy name and description
3. Select categories to block
4. Optionally set as default policy
5. Save

### 3. Apply Filtering to Clients
**Individual Client:**
1. Go to "Clientes sin Filtro" tab
2. Click "Aplicar Filtro" for desired client
3. Select policy and confirm

**Bulk Application:**
1. Select multiple clients using checkboxes
2. Click "Aplicar Masivo"
3. Choose policy and confirm

### 4. Monitor Activity
- View recent activity in "Registro de Actividad" tab
- Check filtering statistics on dashboard
- Monitor success/error rates

## Technical Implementation

### Content Blocking Methods
The system uses two complementary approaches:

1. **DNS Blocking**: Redirects blocked domains to 0.0.0.0
2. **Web Proxy Rules**: Creates deny rules for specific client IPs and domains

### Database Schema
- `content_filter_categories` - Content categories (Social Media, YouTube, etc.)
- `content_filter_domains` - Domains associated with each category
- `content_filter_policies` - Filtering policy definitions
- `content_filter_policy_categories` - Links policies to categories
- `content_filter_client_policies` - Client-policy assignments
- `content_filter_custom_domains` - Client-specific custom rules
- `content_filter_logs` - Activity logging

### API Endpoints
- `POST /network/apply_content_filter` - Apply policy to client
- `POST /network/remove_content_filter` - Remove policy from client
- `POST /network/create_filter_policy` - Create new policy
- `POST /network/bulk_apply_filter` - Apply policy to multiple clients
- `GET /network/get_filter_categories` - Get available categories
- `GET /network/get_filter_policies` - Get available policies

## Router Connection Troubleshooting

### Common Issues:

1. **"Could not connect to router"**
   - Check IP, port, username, password
   - Verify API service is enabled
   - Check firewall rules

2. **"Operation timed out"**
   - REST API may not be enabled
   - Try enabling via: `/ip service enable api`

3. **"Empty reply from server"**
   - SSL port (8729) may have certificate issues
   - Use non-SSL port (8728) instead

### Test Connection:
Use the provided test scripts:
```bash
# Test basic router connection
php test_router_connection.php

# Test legacy API if REST doesn't work
php test_legacy_api.php

# Test content filtering functionality
php test_content_filter.php
```

## Production Deployment Checklist

- [ ] Database schema applied successfully
- [ ] All files uploaded to production server
- [ ] MikroTik router API enabled and accessible
- [ ] Test content filtering with a single client
- [ ] Verify DNS blocking works correctly
- [ ] Test policy creation and management
- [ ] Validate bulk operations work properly
- [ ] Check logging functionality
- [ ] Confirm menu integration works
- [ ] Test user permissions
- [ ] Create backup of system before deployment

## Security Considerations

- API credentials are encrypted using the system's encryption keys
- All filtering operations are logged for audit purposes
- Client IP validation prevents unauthorized access
- Bulk operations are limited to prevent system overload

## Performance Notes

- DNS blocking is more efficient than proxy rules
- Bulk operations may take time for large client lists
- Router performance may be affected by numerous filtering rules
- Monitor router CPU usage after implementing filtering

## Support and Maintenance

For ongoing support:
1. Monitor the activity logs for errors
2. Regularly update blocked domain lists
3. Review and optimize filtering policies
4. Monitor router performance
5. Keep MikroTik firmware updated

## Version Information
- **Module Version**: 1.0
- **Compatible with**: MikroTik RouterOS 6.x and 7.x
- **Required APIs**: REST API, DNS, Web Proxy
- **Framework Version**: Custom PHP MVC
- **Database**: MySQL 5.7+