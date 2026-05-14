# User Management System

A full-stack user management system built with Docker, Nginx, PHP, MySQL, and a React + Vite frontend.

This project demonstrates a complete authentication flow that includes registration, email verification, login, logout, password reset, user actions, and admin actions.

[**Live Demo**](https://user-management-system-zeta-two.vercel.app/) | [**API Documentation**](https://user-management-system-db.onrender.com/api)

## 🔗 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Setup](#-setup)
  - [Requirements](#requirements)
  - [Run the project](#run-the-project)
  - [Access URLs](#access-urls)
- [Environment Configuration](#-environment-configuration)
  - [Backend environment](#backend-environment)
  - [Frontend environment](#frontend-environment)
- [Database](#-database)
- [API Endpoints](#-api-endpoints)
  - [Authentication](#authentication)
  - [Email verification](#email-verification)
  - [Password reset](#password-reset)
  - [Inbox](#inbox)
  - [User actions](#user-actions)
  - [Admin actions](#admin-actions)
- [API Documentation](#-api-documentation)
- [Notes](#-notes)
- [Useful Files](#-useful-files)
- [Contribution](#-contribution)

## 🚀 Features

| Feature | Description |
| --- | --- |
| Role-based access | Supports `admin` and `user` roles |
| Registration | Validation with username, email, and password rules, plus secure password hashing |
| Email verification | Mock mail delivery with hashed verification tokens and link-based verification |
| Login | JWT access token + HttpOnly refresh token cookie on success |
| Logout | Clears refresh token and returns user to login flow |
| Password reset | Token generation, mock mail delivery, and password update flow |
| Auth-protected actions | Access token required for protected user endpoints |
| Admin actions | Role verification for admin-only endpoints |
| API delivery | Backend served through Nginx and PHP-FPM |
| Frontend delivery | React app served by Nginx |

## 🧱 Tech Stack

| Technology | Purpose |
| --- | --- |
| Docker | Containerized deployment for frontend, backend, and database |
| Nginx | Reverse proxy for API and static hosting for frontend |
| PHP 8.4 FPM | Backend API server |
| MySQL | Application database |
| React + Vite | Frontend UI and client application |
| JWT | Access token authentication |

## 📁 Project Structure

- `app/` – frontend React application
- `api/` – PHP backend API and authentication logic
- `database/` – MySQL initialization SQL
- `compose.yaml` – Docker services for frontend, backend, Nginx, and database

## 🛠️ Setup

### Requirements

- Docker
- Docker Compose

### Run the project

From the repository root:

```bash
docker compose up -d --build
```

### Access URLs

- Frontend: `http://localhost:5173`
- Backend API: `http://localhost:8080`

## 🌐 Environment Configuration

### Backend environment

The backend reads variables from `api/.env`.
If the file is missing, create it with values similar to:

```env
MYSQL_HOST=db
MYSQL_DB_NAME=user_management_system
MYSQL_USERNAME=user
MYSQL_PASS=1234
CLIENT_URL=http://localhost:5173
JWT_SECRET=your_jwt_secret_here
DEV=true
STATIC_ADMIN_ID=1
```

### Frontend environment

The frontend uses `app/.env` with:

```env
VITE_AUTH_API_BASE_URL=http://localhost:8080/api/v1/auth
```

## 🧾 Database

The MySQL service initializes schema from `database/init.sql`.

It creates the following tables:

- `accounts`
- `password_resets`
- `email_verifications`
- `inbox`
- `refresh_tokens`

## 📌 API Endpoints

The backend routes are defined under `/api/v1/auth`.

### Authentication

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET  /api/v1/auth/login/refresh-token`

### Email verification

- `POST /api/v1/auth/email/verify-request`
- `POST /api/v1/auth/email/verified`

### Password reset

- `POST /api/v1/auth/password/forget`
- `POST /api/v1/auth/password/reset`

### Inbox

- `GET  /api/v1/auth/inbox`
- `PATCH /api/v1/auth/inbox/mark-as-read/{id}`

### User actions

- `GET  /api/v1/auth/user/fetch-user`
- `PATCH /api/v1/auth/user/change-username`

### Admin actions

- `GET  /api/v1/auth/admin/fetch-all-users`
- `PATCH /api/v1/auth/admin/edit-user-info`

## 📄 API Documentation

- Root docs: `http://localhost:8080/`
- API docs: `http://localhost:8080/api`

## 🧪 Notes

- Email sending is simulated via the mock mail interface.
- Registration enforces:
  - username length ≥ 3
  - fake email domain for PDPA safety
  - password length ≥ 8
- Passwords are hashed before being saved to the database.
- Verification and password-reset tokens are hashed and stored in the database.
- Login success sets an HttpOnly refresh cookie and returns a JWT access token to the client.

## 💡 Useful Files

- `api/auth/docs_endpoints.php` — API endpoint reference data
- `api/Dockerfile` — backend PHP service
- `app/Dockerfile` — frontend build and Nginx deployment
- `api/nginx.conf` — backend Nginx config
- `database/init.sql` — initial database schema

## 🙌 Contribution

Feel free to extend the project with:

- real email delivery
- admin dashboard enhancements
- improved validation and security hardening
- production-ready Docker networking and secrets management
