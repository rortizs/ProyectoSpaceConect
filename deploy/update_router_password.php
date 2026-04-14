<?php
/**
 * Script to update router password in database with correctly encrypted value
 * 
 * IMPORTANT: Run this in production (LXC 101) to update the database
 */

const SECRET_IV = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

function encrypt_aes($text, $key)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// Database connection
$host = "localhost";
$dbname = "online";
$username = "spaceconnect";
$password = "Sp4c3C0nn3ct2026";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database successfully\n\n";
    
    // The correct password for MK Muni router
    $correct_password = "digilab123";
    
    // Encrypt the password
    $encrypted_password = encrypt_aes($correct_password, SECRET_IV);
    
    echo "Encrypting password: $correct_password\n";
    echo "Encrypted value: $encrypted_password\n\n";
    
    // Update the router password in database (router ID = 6 is MK Muni)
    $sql = "UPDATE network_routers SET password = :password WHERE id = 6";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':password', $encrypted_password);
    
    if ($stmt->execute()) {
        echo "✅ Password updated successfully for router ID 6 (MK Muni)\n\n";
        
        // Verify the update
        $verify_sql = "SELECT id, name, ip, username, password FROM network_routers WHERE id = 6";
        $verify_stmt = $pdo->query($verify_sql);
        $router = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Verification:\n";
        echo "-------------\n";
        echo "ID: " . $router['id'] . "\n";
        echo "Name: " . $router['name'] . "\n";
        echo "IP: " . $router['ip'] . "\n";
        echo "Username: " . $router['username'] . "\n";
        echo "Password (encrypted): " . $router['password'] . "\n";
        
    } else {
        echo "❌ Failed to update password\n";
    }
    
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
