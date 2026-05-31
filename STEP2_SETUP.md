# STEP 2: Role & Permission Management - Setup Guide

## Overview

STEP 2 mengimplementasikan **Admin Panel** untuk mengelola Roles dan Permissions secara dinamis.

Sistem role dan permission sudah terintegrasi dengan Spatie Permission dari STEP 1.

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── RoleController.php
│   │       └── PermissionController.php
│   └── Requests/
│       ├── StoreRoleRequest.php
│       ├── UpdateRoleRequest.php
│       ├── StorePermissionRequest.php
│       └── UpdatePermissionRequest.php

routes/
└── web.php (updated with admin routes)

resources/views/
└── admin/
    ├── roles/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    └── permissions/
        ├── index.blade.php
        ├── create.blade.php
        └── edit.blade.php
```

## Installation

### 1. No additional packages needed
Semua dependencies sudah ada dari STEP 1 (Spatie Permission).

### 2. Run this command if needed

```bash
# Clear route cache
php artisan route:clear

# Verify routes
php artisan route:list | grep admin
```

## Features

### Role Management
- **List Roles**: Lihat semua roles dengan pagination
- **Create Role**: Buat role baru dan assign permissions
- **Edit Role**: Update role name dan permissions
- **Delete Role**: Hapus role (system roles protected)
- **Search**: Cari role berdasarkan nama

### Permission Management
- **List Permissions**: Lihat semua permissions dengan info roles yang menggunakan
- **Create Permission**: Tambah permission baru
- **Edit Permission**: Update permission name
- **Delete Permission**: Hapus permission (system permissions protected)
- **Search**: Cari permission

## Security

### Authorization
- ✅ `middleware('auth')` - Semua routes require authentication
- ✅ `middleware('permission:manage-roles')` - Role management require permission
- ✅ `middleware('permission:manage-permissions')` - Permission management require permission

### Validation
- ✅ Unique validation untuk role dan permission names
- ✅ Form request validation
- ✅ Authorization check di controller

### Protected Data
- ✅ System roles tidak bisa dihapus (Super Admin, Manager, Customer Service, Auditor)
- ✅ System permissions tidak bisa dihapus

## Usage

### Login sebagai Super Admin
```
Email: admin@example.com
Password: password
```

### Access Admin Panel
1. Login
2. Di navbar, klik "Admin" dropdown
3. Pilih "Roles" atau "Permissions"

### Create New Role
1. Click "Create Role" button
2. Input role name
3. Select permissions (checkbox)
4. Submit

### Create New Permission
1. Click "Create Permission" button
2. Input permission name (format: kebab-case)
3. Submit

## Database

Spatie Permission membuat tabel secara otomatis:
- `roles`
- `permissions`
- `role_has_permissions`
- `model_has_roles`
- `model_has_permissions`

Tidak perlu migration tambahan.

## Testing

### Test as Super Admin
1. Login dengan admin@example.com
2. Akses /admin/roles
3. Akses /admin/permissions
4. Create, edit, delete
5. Verify permissions di role assign

### Test Authorization
1. Login sebagai Manager
2. Try akses /admin/roles
3. Should show forbidden error (jika tidak punya permission)

### Test Permission Assignment
1. Create new role
2. Assign beberapa permissions
3. Assign role ke user
4. Check user permissions
5. Verify user dapat akses menu sesuai permission

## API Endpoints

```
GET    /admin/roles               - List roles
GET    /admin/roles/create        - Show create form
POST   /admin/roles               - Store role
GET    /admin/roles/{id}/edit     - Show edit form
PUT    /admin/roles/{id}          - Update role
DELETE /admin/roles/{id}          - Delete role

GET    /admin/permissions         - List permissions
GET    /admin/permissions/create  - Show create form
POST   /admin/permissions         - Store permission
GET    /admin/permissions/{id}/edit - Show edit form
PUT    /admin/permissions/{id}    - Update permission
DELETE /admin/permissions/{id}    - Delete permission
```

## Next Steps (STEP 3)

STEP 3 akan mengimplementasikan **User Management**:
- User CRUD
- Assign roles ke users
- User status management
- User listing dengan roles info
