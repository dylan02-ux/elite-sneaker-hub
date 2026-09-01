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
1. Clone this repository into your XAMPP `htdocs` folder:
```bash
   git clone https://github.com/dylan02-ux/elite-sneaker-hub.git
```
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Create the database by importing `database.sql` via phpMyAdmin (`http://localhost/phpmyadmin`):
   - Create a database named `shoe_store`
   - Import `database.sql` into it
4. Visit the site in your browser: