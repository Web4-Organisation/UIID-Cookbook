# UIID PHP Demosuite

This is a demonstration application to showcase how to integrate with the UIID-Service for authentication and API access.

UIID API & oAuth 2.0 Documentation: https://uiid.linkspreed.com/api-docs

## Requirements

- PHP >= 8.0
- Composer

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Web4-Organisation/uiid-cookbook.git
   cd uiid-cookbook/demosuite
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up your environment:**
   Copy the example environment file and fill in your UIID application credentials.
   ```bash
   cp .env.example .env
   ```
   You will need to edit the `.env` file with the credentials from your UIID application.

## Usage

1. **Start the PHP built-in web server:**
   ```bash
   php -S localhost:8000 -t public
   ```

2. **Open your browser:**
   Navigate to `http://localhost:8000/public` in your web browser.

3. **Log in:**
   Click the "Login with UIID" button to start the OAuth2 flow.

4. **Use the Dashboard:**
   After successful login, you will be redirected to the dashboard where you can make test calls to the UIID API.