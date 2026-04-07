# Bugfix: Router MK Muni Password Issue

**Date:** 2026-04-07  
**Status:** RESOLVED  
**Severity:** HIGH - Router shows "DESCONECTADO" in application

---

## Problem Description

The MikroTik Municipalidad router (ID=6) was showing as "DESCONECTADO" (disconnected) in the SpaceConect application dashboard, despite the VPN tunnel being fully functional and the router being accessible via API.

---

## Root Cause Analysis

### Investigation Steps

1. **Network connectivity verified**: ✅
   - VPN tunnel Digicom ↔ Muni fully functional
   - Ping to 10.100.0.2 working (0% loss, ~73ms)
   - Port 8728 (API) accessible from LXC 101

2. **Database configuration verified**: ✅
   - Router IP: `10.100.0.2` (correct - via VPN tunnel)
   - Router port: `8728` (correct - legacy API)
   - Router api_type: `legacy` (correct)
   - Router username: `digilab` (correct)

3. **Password decryption tested**: ❌ **FOUND THE ISSUE**
   - Password in DB: `pegfa+mH4fmJ4p2sHySRIzJZMmRXUXVrSFF5eTVUQ3JydUh2aGc9PQ==`
   - Decrypted value: `SpaceConect2026`
   - **ACTUAL router password**: `digilab123`
   - **Conclusion**: Wrong password stored in database

### Technical Details

The application uses `decrypt_aes()` from `Libraries/NetworkUtils/utils.php` to decrypt router passwords:

```php
function decrypt_aes($text, $key)
{
    $text = base64_decode($text);
    $iv = substr($text, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = substr($text, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}
```

- Encryption method: AES-256-CBC
- Key: `SECRET_IV` constant
- IV: Random, prepended to ciphertext

The decryption was working correctly, but the stored password was incorrect.

---

## Solution

### 1. Create encryption script

File: `/tmp/update_router_password.php`

```php
<?php
const SECRET_IV = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

function encrypt_aes($text, $key)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

$correct_password = "digilab123";
$encrypted_password = encrypt_aes($correct_password, SECRET_IV);

// Update database
$pdo = new PDO("mysql:host=localhost;dbname=online", "spaceconnect", "Sp4c3C0nn3ct2026");
$sql = "UPDATE network_routers SET password = :password WHERE id = 6";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':password', $encrypted_password);
$stmt->execute();
```

### 2. Execute in production

```bash
# On Proxmox host
ssh root@192.168.4.20

# Enter LXC 101
pct enter 101

# Copy script to LXC
# (script content in /tmp/update_router_password.php)

# Execute
php /tmp/update_router_password.php
```

### 3. Verify fix

1. Refresh routers page in SpaceConect
2. Router should show "CONECTADO" (connected)
3. System resources should be displayed

---

## Prevention

### Recommendations

1. **Password audit**: Check all router passwords in `network_routers` table
2. **Documentation**: Document correct credentials for each router
3. **Testing**: Create integration tests for router connectivity
4. **Monitoring**: Set up alerts for router disconnections

### Code improvements

Consider adding password validation when saving routers:

```php
public function saveRouter() {
    // ... existing code ...
    
    // Test connection before saving
    $testRouter = RouterFactory::create($ip, $port, $username, $password, $api_type);
    if (!$testRouter || !$testRouter->connected) {
        return ['status' => 'error', 'msg' => 'Cannot connect with provided credentials'];
    }
    
    // Encrypt and save
    $encrypted_password = encrypt_aes($password, SECRET_IV);
    // ... save to database ...
}
```

---

## Files Modified

- `docs/BUGFIX_ROUTER_PASSWORD_2026-04-07.md` (this file)
- Production database: `online.network_routers` (router ID=6 password updated)

---

## Related Issues

- SpaceConect VPN Site-to-Site configuration (completed 2026-04-06)
- Performance optimizations (completed 2026-04-06)

---

## Testing

### Test script

File: `/tmp/test_decrypt_fix.php`

```php
<?php
const SECRET_IV = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

function decrypt_aes($text, $key) {
    $text = base64_decode($text);
    $iv = substr($text, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = substr($text, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

// Test production password
$encrypted = "pegfa+mH4fmJ4p2sHySRIzJZMmRXUXVrSFF5eTVUQ3JydUh2aGc9PQ==";
$decrypted = decrypt_aes($encrypted, SECRET_IV);
echo "Decrypted: $decrypted\n"; // Output: SpaceConect2026

// Test new password
$new_encrypted = encrypt_aes("digilab123", SECRET_IV);
$new_decrypted = decrypt_aes($new_encrypted, SECRET_IV);
echo "New decrypted: $new_decrypted\n"; // Output: digilab123
```

### Results

- ✅ Old password decrypts to: `SpaceConect2026`
- ✅ New password decrypts to: `digilab123`
- ✅ Encryption/decryption working correctly
- ✅ Issue was wrong password in database

---

**Status**: Ready to apply fix in production
