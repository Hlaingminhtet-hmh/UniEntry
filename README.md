# UniEntry - Student Portal & Event Management System

UniEntry is a professional, high-performance web platform engineered for modern academic environments. It digitizes traditional manual student registrations and campus event coordination into a single unified platform. The system's core feature is its asynchronous processing capability, utilizing **Redis Message Queuing** and **Background Workers** to handle extreme concurrent peak-load spikes during event registration periods while maintaining strict database integrity.

## Key Features

- **Asynchronous Registration Queue:** Employs a Redis-backed "First-In-First-Out" (FIFO) queue architecture to seamlessly absorb massive traffic spikes without crashing the primary database.
- **Reliable Worker Architecture:** Built-in fault tolerance background worker featuring an automatic database retry mechanism that triggers up to 3 times if an unexpected network failure occurs.
- **Cloud Infrastructure Integration:** Connects seamlessly with **Supabase (PostgreSQL)** providing secure, highly accessible, centralized relational data storage.
- **Automated SMTP Alerts:** Integrated with **PHPMailer** to dispatch instant verification links and portal status access notifications to students upon admin approval.
- **Granular Security (RBAC):** Strict Role-Based Access Control dividing user segments into verified Student dashboards and Admin control centers.

## 🛠️ System Design & Flow

### System Operations (Proposed vs Existing)
- **Existing System:** Relies heavily on slow paper trails, manual spreadsheets, lacks instant validation, and is prone to synchronization errors and missing data.
- **Proposed Architecture (UniEntry):** Handles requests asynchronously via memory caches, utilizes standardized cloud instances, and operates automated triggers to maintain system speed.

### Data Access Operations Matrix (CRUD)
The logical layout determines structural database access rights across various platform models:

| Entity Modules | Create (C) | Read (R) | Update (U) | Delete (D) |
| :--- | :--- | :--- | :--- | :--- |
| **Application Profiles** | Student | Admin / Student | Admin | Admin |
| **Event Listings** | Admin | Admin / Student | Admin | Admin |
| **Participant Lists** | System | Admin | Admin | Admin |
| **Event Notifications** | System | Student | — | — |

---

##  Tech Stack & Requirements

- **Backend Logic:** Native PHP (Modular scripting)
- **Database Engine:** Supabase Cloud API (PostgreSQL Core)
- **Message Broker & Cache:** Redis Server (Memory-store queue handling)
- **Dependencies & Packages:** PHPMailer, Composer Package Manager

---

##  Quick Installation

Ensure a working Redis engine instance and PHP 8.x web environment on your workstation before setup.

1. **Verify Your Local Working Repository**
   Make sure you are running commands within your local `dbapp` directory.

2. **Establish Environment Dependencies**
   Run composer to lock vendor components:
   ```bash
   composer install
