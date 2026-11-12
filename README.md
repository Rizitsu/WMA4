# Rental POS / Sales Management System (Midterm)

**WMA4 – Advanced Web Development · Tarlac State University, College of Computer Studies**
Assigned system type: **Point-of-Sale (POS) / Sales Management System**, implemented as an equipment-rental POS.

Native PHP 8 (OOP, PDO) + MySQL/MariaDB, with a single-page HTML/JS front end that talks to JSON endpoints.

## Features (Midterm requirements)

| Requirement | Where |
|---|---|
| Server-side data-entry forms with validation and friendly errors | `index.html` forms → `customers.php`, `inventory.php`, `process_new_rental.php`, `process_return.php` (`ValidationException` → HTTP 422) |
| Normalized MySQL DB (4 related tables) | `rental_system.sql` – `customers`, `inventory`, `rental_contracts`, `contract_items` (ERD: `docs/ERD.png`) |
| Full CRUD with PDO prepared statements | Customers and Inventory (Create / Read / Update / Delete in UI and API) |
| Searchable / filterable / sortable list with `WHERE`, `ORDER BY`, `LIMIT` | `Customer::all()`, `InventoryManager::all()`, `RentalContract::all()` via `BaseModel::build_sort_and_limit()` |
| OOP: ≥3 related classes, encapsulation, inheritance/interface, exceptions | `CrudInterface` ← `BaseModel` ← `Customer`, `InventoryManager`, `RentalContract`; `ValidationException`; transactions with rollback |
| Dashboard / summary | `DashboardService` → `dashboard.php` (stock, contracts, deposits, late fees, sales report) |

## Requirements
- PHP 8.1+ with the `pdo_mysql` extension
- MySQL 5.7+ or MariaDB 10.4+
- Apache/Nginx, XAMPP, or PHP's built-in server

## Local setup
1. Clone the repo into your web root (e.g. `htdocs/WM4Mid-main`).
2. Create a database and import the schema:
   ```sql
   CREATE DATABASE rental_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   ```bash
   mysql -u root -p rental_system < rental_system.sql
   ```
3. Edit the connection constants in `Database.php` (`HOST`, `DB`, `USER`, `PASS`) to match your local MySQL.
4. Open `http://localhost/WM4Mid-main/index.html`.
   Without Apache: `php -S localhost:8000` in the project folder, then open `http://localhost:8000/index.html`.

> **Security note:** do not commit real hosting credentials. Replace the values in `Database.php` with local ones before pushing, and rotate any password that was already pushed.

## API endpoints (JSON)
| Endpoint | Methods | Purpose |
|---|---|---|
| `customers.php[?id=]` | GET, POST, PUT/PATCH, DELETE | Customer CRUD; GET supports `search`, `sort`, `direction`, `page`, `per_page` |
| `inventory.php[?id=]` | GET, POST, PUT/PATCH, DELETE | Inventory CRUD; GET supports `status`, `search`, `sort`, `direction`, `page`, `per_page` |
| `active_rentals.php` | GET | Contract list; `status`, `search`, `sort`, `direction`, paging |
| `process_new_rental.php` | POST | Create a contract with one or more items (transactional) |
| `process_return.php` | POST | Return items, compute late fee, close contract |
| `dashboard.php` | GET | Summary metrics |

Status codes: 200 OK, 201 Created, 404 Not found, 405 Wrong method, 409 Conflict (FK / duplicate), 422 Validation error, 500 Server error.
A Postman collection is in `postman/Rental_POS_API.postman_collection.json` (set the `base_url` variable).

## Project structure
```
WM4Mid-main/
├── index.html                 UI (dashboard, customers, inventory, new rental, active rentals/returns)
├── *.php                      JSON endpoints
├── CrudInterface.php  BaseModel.php  Customer.php  InventoryManager.php
├── RentalContract.php  DashboardService.php  Database.php  ValidationException.php
├── rental_system.sql          Schema + sample data
├── docs/                      ERD.png, logos, Midterm_Documentation_Report.docx
└── postman/                   Exported API collection
```

## Disclosure
AI assistance was used for documentation drafting and small UI/error-handling additions (see `README_CHANGES.md`). All code is understood and can be explained and modified by the group.
