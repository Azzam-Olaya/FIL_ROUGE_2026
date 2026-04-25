# Software Requirements Specification (SRS): MorLancer

## 1. Introduction
### 1.1 Purpose
This document provides a comprehensive overview of the **MorLancer** platform. It defines the functional and non-functional requirements for the decoupled freelancer marketplace, serving as the primary reference for developers, stakeholders, and testers.

### 1.2 Scope
MorLancer is a web-based ecosystem connecting independent professionals (Freelancers) with businesses (Clients). The system leverages a **Laravel 13** backend and a **Vue.js 3** SPA to deliver real-time mission management, portfolio showcasing (Briefs), and secure contract handling.

---

## 2. System Architecture & Tech Stack
MorLancer utilizes a modern, decoupled architecture designed for scalability and performance.

| Component | Technology | Description |
| :--- | :--- | :--- |
| **API Layer** | Laravel 12| RESTful API handling business logic and RBAC. |
| **Client Layer** | Vue.js 3 (Pinia) | Reactive SPA utilizing the Composition API. |
| **Data Store** | PostgreSQL 15 | Relational database for transactional integrity. |
| **Auth** | Laravel Sanctum | State-based/Token authentication for SPA. |
| **Real-time** | Laravel Reverb | WebSocket support for messaging and notifications. |
| **DevOps** | Docker | Orchestration for PHP-FPM, Nginx, Postgres, and Redis. |

---

## 3. Functional Requirements

### 3.1 User Management & Auth
* **Registration/Login:** Multi-role onboarding (Admin, Client, Freelancer).
* **Role-Based Access Control (RBAC):** Implementation of Laravel gates/policies to restrict dashboard access based on user type.
* **Profile Management:** Custom avatars, bio, skill tags, and social links.

### 3.2 Briefs & Portfolio (The "Social" Element)
* **Creation:** Freelancers can upload "Briefs" (portfolio items) with images and descriptions.
* **Engagement:** Social-style interactions including **Likes**, **Comments**, and **Favorites**.
* **Discovery:** Search and filter functionality for Clients to find talent based on Brief quality.

### 3.3 Mission & Contract Lifecycle
* **Mission Posting:** Clients define project scopes, budgets, and deadlines.
* **Contract Generation:** Automated creation of digital contracts upon freelancer selection.
* **Status Tracking:** Transitions through states: `Draft` → `Active` → `In Review` → `Completed` → `Paid`.
* **Dispute Resolution:** Admin intervention logic for refunds or mediation.

### 3.4 Communication & Real-time
* **Integrated Messaging:** Direct messaging threads linked to specific Missions.
* **Live Notifications:** Real-time alerts for contract updates, new messages, or mission bids via WebSockets.

---

## 4. Non-Functional Requirements

### 4.1 Performance
* **SPA Transitions:** Frontend navigation must feel instantaneous using Vue Router and Pinia state caching.
* **API Response:** Standard CRUD operations should maintain a latency of $< 200ms$.

### 4.2 Security
* **Environment Safety:** Strict use of `.env` management and sanitized inputs (via Eloquent/Laravel Request validation).
* **Data Integrity:** Foreign key constraints in PostgreSQL to prevent orphaned records in contracts and missions.

### 4.3 Scalability
* The decoupled nature allows the Frontend to be served via CDN while the Laravel API scales horizontally across multiple containers.

---

## 5. Database Schema (High-Level)
| Table | Key Relationships |
| :--- | :--- |
| **Freelancer** | HasMany (Briefs, Missions, Messages) |
| **Briefs** | BelongsTo (Freelancer), HasMany (Comments, Likes) |
| **Missions** | BelongsTo (Client), HasOne (Contract) |
| **Contracts** | BelongsTo (Mission, Freelancer, Client) |
| **Messages** | BelongsTo (Sender, Receiver), MorphTo (Context/Mission) |

---

## 6. Roadmap & Implementation Gaps

### Phase 1: Infrastructure (Current Focus)
* Finalize **Laravel Reverb** integration for real-time messaging.
* Optimize Docker configurations for production-ready environments.

### Phase 2: Financials
* Integrate **Stripe/PayPal** API.
* Develop the Escrow logic (holding funds until mission milestone completion).

### Phase 3: QA & Deployment
* Achieve $> 80\%$ test coverage using **Pest/PHPUnit**.
* Configure **GitHub Actions** for automated linting and deployment to staging.