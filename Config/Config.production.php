<?php
/**
 * Production Configuration - LXC 101
 * Copy this file to Config.php on the production server
 *
 * Prerequisites:
 * 1. Create MySQL user:
 *    CREATE USER 'spaceconect_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';
 *    GRANT SELECT, INSERT, UPDATE, DELETE ON online.* TO 'spaceconect_user'@'localhost';
 *    FLUSH PRIVILEGES;
 *
 * 2. SSL handled by Caddy on LXC 161 (digilab.digicom.com.gt)
 */

/* RUTA DEL SISTEMA */
const BASE_URL = "https://digilab.digicom.com.gt";
/* ZONA HORARIA*/
date_default_timezone_set('America/Guatemala');
const MONTHS = [
  "01" => "*ENERO*",
  "02" => "*FEBRERO*",
  "03" => "*MARZO*",
  "04" => "*ABRIL*",
  "05" => "*MAYO*",
  "06" => "*JUNIO*",
  "07" => "*JULIO*",
  "08" => "*AGOSTO*",
  "09" => "*SEPTIEMBRE*",
  "10" => "*OCTUBRE*",
  "11" => "*NOVIEMBRE*",
  "12" => "*DICIEMBRE*"
];
/* CONSTANTE DE CONEXION */
const DB_HOST = "localhost";
const DB_NAME = "online";
const DB_USER = "spaceconect_user";
const DB_PASSWORD = "CHANGE_ME_TO_SECURE_PASSWORD";
const DB_PORT = "3306";
const DB_CHARSET = "utf8";
/* BACKUP */
const TABLES_NAME = "Tables_in_online";
/* DESARROLLADOR*/
const DEVELOPER = "MANUEL ";
const DEVELOPER_WEBSITE = "digilab.digicom.com.gt";
const DEVELOPER_EMAIL = "online@gmail.com";
const DEVELOPER_MOBILE = "+502 5555 5555";
/* SISTEMA */
const NAME_SYSTEM = "INTERNET SISTEMA";
/* CONSTANTES DE ENCRIPTACION */
const METHOD = "AES-256-CBC";
const SECRET_KEY = 'SISTWISP';
const SECRET_IV = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
/* CONSTANTES DE MODULOS */
const DASHBOARD = 1;
const CLIENTS = 2;
const USERS = 3;
const TICKETS = 4;
const INCIDENTS = 5;
const BILLS = 6;
const PRODUCTS = 7;
const CATEGORIES = 8;
const SUPPLIERS = 9;
const PAYMENTS = 10;
const SERVICES = 11;
const BUSINESS = 12;
const INSTALLATIONS = 13;
const CURRENCYS = 14;
const RUNWAY = 15;
const VOUCHERS = 16;
const UNITS = 17;
const EMAIL = 18;
const RED = 19;
const WHATSAPP = 20;
const MUNI = 21;
/* DELIMITADORES */
const SPD = ".";
const SPM = ",";
/* USUARIOS */
const ADMINISTRATOR = 1;
const TECHNICAL = 2;
const CHARGES = 3;
