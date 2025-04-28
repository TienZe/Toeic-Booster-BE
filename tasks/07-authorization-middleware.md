# Task Overview
Implement a role-based authorization system using middleware that supports two roles: Admin and User. The system will leverage the existing JWT authentication and add the ability to restrict certain routes to specific roles using a many-to-many relationship between User and Role.

# Task Status
`Done`

# Requirements
1. Create a Role model with predefined roles (Admin and User)
2. Create a many-to-many relationship between User and Role models
3. Create database migrations for:
   - roles table
   - role_user pivot table
4. Create a RequireAdminPermission middleware that checks if the authenticated user has Admin role
5. Update the auth flow to handle roles during registration and login
6. Register the new middleware in the application
7. Apply the middleware to appropriate routes

# Scratchpad
Based on the existing authentication implementation using JWT, we'll add role-based authorization with a many-to-many relationship between users and roles. There's already a JwtMiddleware class that handles authentication, and we'll build on top of that for authorization.

## Step 1: Create Migrations
- [X] Create a migration for the roles table with id and name columns
- [X] Create a migration for the role_user pivot table with user_id and role_id columns
- [X] Add timestamps to both tables

## Step 2: Create Role Model
- [X] Create a Role model with fillable property for name
- [X] Add a users() method for the many-to-many relationship with User model
- [X] Add constants for role names (ADMIN, USER)

## Step 3: Update the User Model
- [X] Add roles() method for the many-to-many relationship with Role model
- [X] Add helper methods like hasRole($role), isAdmin(), and isUser() to check roles

## Step 4: Create Database Seeders
- [X] Create a RoleSeeder to populate the roles table with Admin and User roles
- [X] Update the database seeder to create at least one admin user

## Step 5: Create the RequireAdminPermission Middleware
- [X] Create RequireAdminPermission middleware class
- [X] Implement handle method to check if authenticated user has Admin role
- [X] Return 403 Forbidden response if user doesn't have required permissions

# Task Lessons
