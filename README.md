# PakBazar

pakbazaar_final is an online marketplace for customers and suppliers.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `cart`, `categories`, `orders`, `order_items`, `products`, `reviews`, `users`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Root Pages | [`about.php`](about.php), [`login.php`](login.php), [`register.php`](register.php) |
| Admin | [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php), [`admin/admin_sidebar.php`](admin/admin_sidebar.php), [`admin/view_orders.php`](admin/view_orders.php), [`admin/view_products.php`](admin/view_products.php) |
| Customer | [`customer/add_review.php`](customer/add_review.php), [`customer/cart_action.php`](customer/cart_action.php), [`customer/checkout.php`](customer/checkout.php), [`customer/customer_sidebar.php`](customer/customer_sidebar.php), [`customer/dashboard.php`](customer/dashboard.php) |
| Supplier | [`supplier/categories.php`](supplier/categories.php), [`supplier/dashboard.php`](supplier/dashboard.php), [`supplier/order_details.php`](supplier/order_details.php), [`supplier/products.php`](supplier/products.php), [`supplier/reviews.php`](supplier/reviews.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/pakbazaar_final.git
cd pakbazaar_final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/pakbazaar_db.sql`](Database/pakbazaar_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php), [`customer/config.php`](customer/config.php), [`supplier/config.php`](supplier/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`index.php`](index.php) | Web entry page |
| [`uploads`](uploads) | Upload storage |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
