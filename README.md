# User Authentication System

## 1. Purpose

This project implements a secure user registration and login system using PHP and MySQL.

The focus is not only functionality, but real-world security practices commonly used in production systems.


## 2. System Overview

The system consists of:
-	Backend PHP scripts responsible for authentication logic
-	A database used to store user information
-	A web page used to test registration and login behavior
-	PHP unit tests used to verify the functionality and security of the authentication system

All communication between the web page and backend scripts uses JSON responses, which makes result handling consistent and predictable. The PHP code follows PSR-12 coding standards to ensure readability, maintainability, and consistency across the project.


## 3. File Responsibilities

### 3.1 Registration Interface (register.php)

The registration file is responsible for:
-	Receiving user registration requests
-	Validating username and password rules
-	Hashing passwords before storage
-	Preventing duplicate usernames
-	Protecting against forged requests (CSRF)
-	Limiting excessive registration attempts
-	Returning standardized success or error messages

Its goal is to ensure that only valid and secure user data is stored.


### 3.2 Login Interface (login.php)

The login file handles:
-	User identity verification
-	Password hash comparison
-	Session creation after successful login
-	Failed login attempt tracking

It ensures that only legitimate users can access authenticated sessions.


### 3.3 Web Test Page (auth_test.php)

The web page is used to:
-	Provide forms for user registration and login
-	Submit user input to backend interfaces
-	Display success or error messages to users
-	Automatically include required security tokens
-	Simulate real-world user authentication behavior

This file serves as a functional test interface rather than a production UI.


### 3.4 Database Configuration File (db.php)

The database configuration file is responsible for establishing a connection between the application and the database.

Its main responsibilities include:
-	Defining database connection parameters such as host, database name, username, and password
-	Creating a reusable database connection instance
-	Providing a unified entry point for all database operations
-	Ensuring consistent database behavior across the entire application


### 3.5Tests Directory (tests/)

The tests folder contains PHP unit tests that verify the authentication system’s functionality.

Its responsibilities include:
-	Testing the registration interface under different scenarios (success, duplicate username, invalid input, missing CSRF token)
-	Testing the login interface under different scenarios (success, wrong password, failed attempts, missing CSRF token)
-	Ensuring that security mechanisms such as CSRF protection and brute-force prevention behave as expected
-	Providing a repeatable and automated way to validate backend logic without manual interaction

This directory is essential for maintaining code reliability, catching regressions, and supporting future enhancements.


## 4. Local Testing Guide

This section describes how to run and test the authentication system in a local environment.

The following steps outline a typical local testing workflow.


### 4.1 Create Database and User Table

Before running the application, a database must be prepared.

Execute the following command in the database:
```bash
CREATE TABLE users (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```


### 4.2 Modify db.php file

Modify the database information in the db.php file to match the database information of your local system.


### 4.3 Test function in the browser

Enter the website address in the browser to access the file "auth_test.php"


## 5. PHP Unit Testing

In this project, PHP unit tests are used to verify that the authentication system behaves correctly under various conditions.

Run the following command and execute the unit tests you want in your code editor.
```bash
composer require --dev phpunit/phpunit
```