# Project Context: MorLancer

## 1. Vision and Objective
**MorLancer** is a modern freelance marketplace designed to connect independent professionals (Freelancers) with businesses (Clients). Unlike traditional job boards, it emphasizes a "social" portfolio approach through "Briefs" and a highly reactive, unified dashboard experience.

---

## 2. Core Project Information
- **Project Name:** MorLancer
- **Type:** Decoupled Web Application (Full-stack)
- **Status:** In Development (Core infrastructure and authentication validated).

---

## 3. Technology Stack
The project uses a modern, high-performance stack:

### **Backend (API RESTful)**
- **Framework:** Laravel 13 (PHP 8.3)
- **Database:** PostgreSQL 15
- **Authentication:** Laravel Sanctum (Token-based)
- **Real-time:** Laravel Reverb (WebSockets for messaging)
- **Environment:** Docker (PHP-FPM, Nginx, Postgres, Redis)

### **Frontend (SPA)**
- **Framework:** Vue.js 3 (Composition API)
- **State Management:** Pinia
- **Build Tool:** Vite
- **Styling:** Tailwind CSS
- **Experience:** Unified Dashboard approach (Single Page Application)

---

## 4. User Roles and Key Responsibilities
| Role | Primary Functions |
| :--- | :--- |
| **Admin** | Moderation, user approval, platform statistics, category management. |
| **Client** | Mission posting, freelancer discovery (via Briefs), contract management, payments. |
| **Freelancer** | Brief/Portfolio publication, mission search, contract fulfillment, profile management. |

---

## 5. Main Functional Workflows
1. **Onboarding:** Multi-role registration with mandatory Admin approval for trust.
2. **Portfolio/Social (Briefs):** Freelancers showcase work; Clients interact via Likes, Comments, and Favorites.
3. **Mission Lifecycle:** 
   - Client posts a Mission.
   - Freelancer applies/discovers.
   - **Contract** is generated automatically upon agreement.
4. **Communication:** Real-time messaging contextually linked to specific contracts/missions.
5. **Contract Completion:** Workflow transitions from `Draft` to `Complete` or `Refund`.

---

## 6. Current Project State & Roadmap
### **What's Completed ✅**
- Decoupled architecture setup.
- Full Auth system with Sanctum and role-based redirection.
- Unified Freelancer Dashboard (Single-page experience).
- Core database schema implemented.

### **Current Focus & Missing Elements 🚧**
- **Messaging:** Finalizing real-time integration (Laravel Reverb).
- **Financials:** Integration of Stripe/PayPal for escrow and payments.
- **QA:** Enhancing test coverage (PHPUnit & E2E).
- **SEO:** Optimizing the Landing Page.

### **Identified Fixes/Improvements**
- Token expiration handling in the SPA.
- Mobile responsiveness verification for the unified dashboards.
