# RightMo Test API (Laravel Backend)

RESTful API backend for the Product Management System built with Laravel 11.

## Features

- Token-based authentication (Laravel Sanctum)
- Full CRUD operations for products
- Search products by name
- Filter by category and price range
- Sort by price, rating, name, or date
- Pagination support
- Image upload and management
- Comprehensive validation
- PHPUnit feature tests
- CORS enabled for frontend access

## Installation & Setup

See the main README.md in the parent directory for complete setup instructions.

## API Endpoints

### Authentication
- POST /api/register - Register user
- POST /api/login - Login
- POST /api/logout - Logout (auth required)
- GET /api/me - Get current user (auth required)

### Products (All require authentication)
- GET /api/products - List products with filters
- GET /api/products/{id} - Get single product
- POST /api/products - Create product
- PUT /api/products/{id} - Update product
- DELETE /api/products/{id} - Delete product

## Running Tests

```bash
php artisan test
```

## Default Credentials

Email: test@example.com
Password: password
