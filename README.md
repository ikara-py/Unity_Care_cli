# Unity Care Clinic - CLI Edition 🏥

A robust **Command Line Interface (CLI)** tool designed to manage the Unity Care Clinic system. This project refactors the original procedural web logic into a strict **Object-Oriented Programming (OOP)** architecture using **PHP 8**.

This CLI serves as an internal tool for rapid data management and statistical analysis.

---

## Project Overview

### Context
Following the development of the procedural web version of Unity Care Clinic, the technical team initiated this console version to adopt a modular Object-Oriented Architecture.

### Main Objectives
* **Refactor:** Migrate business logic to PHP 8 OOP.
* **Structure:** Implement Encapsulation, Inheritance, and Interfaces.
* **Data Access:** Use `MySQLi` with an OOP approach.
* **Interaction:** Build an interactive console menu for CRUD operations.
* **Analytics:** Generate statistics using static methods.

---

## Features & Architecture

### 1. Class Structure
The project is built on a hierarchy of business classes:
* **BaseModel:** Abstract class handling generic DB operations (`save`, `delete`, `findById`).
* **Personne:** Abstract parent class.
* **User:** Parent class for system users.
* **Patient:** Extends `Personne`.
* **Doctor:** Extends `User`.
* **Department:** Manages clinic departments.

### 2. Core Utilities
* **Validator:** Static class for validating emails, phone numbers, dates, and input sanitization.
* **ConsoleTable:** Utility to render data in formatted ASCII grids.

### 3. Statistics
Real-time calculation of key metrics:
* Average Patient Age.
* Average Doctor Years of Service.
* Most Populated Department.
* Patient Count per Department.

---

## Requirements

* **PHP:** 8.0+
* **Database:** MySQL
* **Extensions:** `mysqli` enabled in `php.ini`

---

## Installation

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/ikara-py/Unity_Care_cli](https://github.com/ikara-py/Unity_Care_cli)
    ```

2.  **Database Setup:**
    * Import the provided SQL file into your MySQL database.
    * Configure connection settings in `Config/Database.php`.

---

## Usage

The application uses a numbered menu system.

**Main Menu:**
1.  **Manage Patients** (Create, List, Search, Update, Delete)
2.  **Manage Doctors** (Create, List, Search, Update, Delete)
3.  **Manage Departments** (Create, List, Update, Delete)
4.  **Statistics** (View clinic analytics)
5.  **Exit**

**Example Output (ASCII Table):**
```text
+----+------------+-----------+-------------+
| ID | First Name | Last Name | Department  |
+----+------------+-----------+-------------+
| 1  | Mohammed   | Alami     | Cardiology  |
| 2  | Fatima     | Bennis    | Pediatrics  |
+----+------------+-----------+-------------+