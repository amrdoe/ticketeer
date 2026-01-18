# Ticketeer

Ticketeer is a modern, full-stack event management and ticketing platform built with Laravel 12 and Vue 3. It allows users to browse events, purchase tickets, and manage their bookings, while empowering organizers to create and manage their own events with ease.

## 🚀 Live Demo

Check out the live application here: **[https://ticketeer-a1v2.onrender.com](https://ticketeer-a1v2.onrender.com)**

## ✨ Features

- **Event Discovery**: Browse featured and upcoming events.
- **Event Management**: Organizers can create, edit, and manage events and ticket types.
- **Secure Payments**: Integrated Stripe payments for secure ticket purchasing.
- **Ticket System**:
    - Multiple ticket types per event.
    - QR Code generation for tickets.
    - Ticket scanning and redemption for organizers.
- **User Accounts**: Authentication and profile management via Laravel Sanctum.
- **Responsive Design**: Built with Tailwind CSS v4 for a seamless mobile and desktop experience.

## 🛠️ Technology Stack

### Backend

- **Framework**: Laravel 12
- **Database**: PostgreSQL
- **API**: RESTful API
- **Authentication**: Laravel Sanctum

### Frontend

- **Framework**: Vue 3 (Composition API)
- **Language**: TypeScript
- **Routing**: Vue Router
- **State Management**: Pinia
- **Styling**: Tailwind CSS v4
- **UI Components**: Headless UI / Custom Components

## 📦 Installation

Follow these steps to set up the project locally.

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & Yarn
- PostgreSQL

### Steps

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd ticketeer
    ```

2. **Install Backend Dependencies**

    ```bash
    composer install
    ```

3. **Install Frontend Dependencies**

    ```bash
    yarn install
    ```

4. **Environment Setup**
   Copy the example environment file and configure your database and Stripe credentials.

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    _Make sure to update `DB\__`and`STRIPE\__`variables in your`.env` file._

5. **Database Migration**

    ```bash
    php artisan migrate
    ```

6. **Run the Application**
   Start the development servers:

    ```bash
    # Terminal 1: Backend
    php artisan serve

    # Terminal 2: Frontend
    yarn dev
    ```

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
