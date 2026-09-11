# Elite Sneaker Hub

A full-stack sneaker e-commerce web application built with PHP and MySQL. Customers can browse, search, and purchase sneakers, while admins manage inventory, brands, and view sales reports through a dedicated dashboard.

## Features

### Customer
- Browse and search the product catalog
- Filter by category, sort by price or newest
- Add items to cart, adjust quantities, checkout
- View personal order history
- Secure login with hashed passwords

### Admin
- Dashboard to add, edit, and delete shoes
- Manage brands (add, edit, delete)
- Reports page with sales stats, top sellers, and low-stock alerts
- Role-based access control (admin-only routes protected)

## Tech Stack
- **Backend:** PHP (PDO for database access, prepared statements throughout)
- **Database:** MySQL
- **Frontend:** Bootstrap 5, Bootstrap Icons
- **Auth:** PHP sessions, `password_hash()` / `password_verify()` for secure password storage

## Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP 8+ + MySQL setup)

### Setup
## Requirements

- PHP
- MySQL
- XAMPP, MAMP, or equivalent local server environment

## Setup

1. Clone the repository.
2. Place the project folder inside `htdocs`.
3. Start Apache and MySQL.
4. Import `database.sql` into MySQL.
5. Run the project:

```bash
php -d session.save_path=/tmp -S localhost:8000