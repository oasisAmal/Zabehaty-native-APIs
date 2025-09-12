#!/bin/bash

# Zabehaty Native APIs Setup Script
# This script helps developers set up the project for the first time

set -e

echo "🚀 Starting Zabehaty Native APIs Setup..."
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check if Docker is installed
check_docker() {
    if command -v docker &> /dev/null && command -v docker-compose &> /dev/null; then
        print_status "Docker and Docker Compose are installed"
        return 0
    else
        print_warning "Docker or Docker Compose not found"
        return 1
    fi
}

# Check if PHP and Composer are installed
check_php() {
    if command -v php &> /dev/null && command -v composer &> /dev/null; then
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        print_status "PHP $PHP_VERSION and Composer are installed"
        return 0
    else
        print_warning "PHP or Composer not found"
        return 1
    fi
}

# Setup with Docker
setup_docker() {
    echo ""
    echo "🐳 Setting up with Docker..."
    echo "============================"
    
    # Create .env file if it doesn't exist
    if [ ! -f .env ]; then
        print_info "Creating .env file..."
        cat > .env << EOF
# Application Configuration
APP_NAME="Zabehaty Native APIs"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8080

# Database Configuration - Default Connection
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=zabehaty_default
DB_USERNAME=root
DB_PASSWORD=root

# Multi-Country Database Connections
DB_AE_HOST=db
DB_AE_PORT=3306
DB_AE_DATABASE=zabehaty_ae
DB_AE_USERNAME=root
DB_AE_PASSWORD=root

DB_SA_HOST=db
DB_SA_PORT=3306
DB_SA_DATABASE=zabehaty_sa
DB_SA_USERNAME=root
DB_SA_PASSWORD=root

DB_OM_HOST=db
DB_OM_PORT=3306
DB_OM_DATABASE=zabehaty_om
DB_OM_USERNAME=root
DB_OM_PASSWORD=root

DB_KW_HOST=db
DB_KW_PORT=3306
DB_KW_DATABASE=zabehaty_kw
DB_KW_USERNAME=root
DB_KW_PASSWORD=root

DB_BH_HOST=db
DB_BH_PORT=3306
DB_BH_DATABASE=zabehaty_bh
DB_BH_USERNAME=root
DB_BH_PASSWORD=root

# Cache Configuration
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

# Twilio Configuration (WhatsApp Integration)
TWILIO_ACCOUNT_SID_ZABEHATY_APP=your_twilio_account_sid_here
TWILIO_AUTH_TOKEN_ZABEHATY_APP=your_twilio_auth_token_here
TWILIO_WHATSAPP_NUMBER_ZABEHATY_APP=your_whatsapp_number_here

TWILIO_ACCOUNT_SID_HALAL_APP=your_twilio_account_sid_here
TWILIO_AUTH_TOKEN_HALAL_APP=your_twilio_auth_token_here
TWILIO_WHATSAPP_NUMBER_HALAL_APP=your_whatsapp_number_here

# Firebase Configuration (FCM Integration)
FIREBASE_PROJECT_ID_ZABEHATY_APP=zabehaty-98bce
FIREBASE_PROJECT_ID_HALAL_APP=halal-bca9c
EOF
        print_status ".env file created"
    else
        print_info ".env file already exists"
    fi
    
    # Build and start Docker containers
    print_info "Building and starting Docker containers..."
    docker-compose up -d --build
    
    # Wait for database to be ready
    print_info "Waiting for database to be ready..."
    sleep 10
    
    # Install dependencies and setup Laravel
    print_info "Installing dependencies and setting up Laravel..."
    docker-compose exec app composer install
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate
    docker-compose exec app php artisan country:db migrate --all
    docker-compose exec app php artisan db:seed
    
    print_status "Docker setup completed!"
    echo ""
    echo "🌐 Access URLs:"
    echo "  - Application: http://localhost:8080"
    echo "  - phpMyAdmin: http://localhost:8081"
    echo "  - Mailpit: http://localhost:8025"
    echo ""
    echo "📝 Next steps:"
    echo "  1. Update Twilio credentials in .env file"
    echo "  2. Add Firebase service account files to storage/app/firebase/"
    echo "  3. Test the API endpoints"
}

# Setup without Docker
setup_local() {
    echo ""
    echo "💻 Setting up locally..."
    echo "======================="
    
    # Create .env file if it doesn't exist
    if [ ! -f .env ]; then
        print_info "Creating .env file..."
        cat > .env << EOF
# Application Configuration
APP_NAME="Zabehaty Native APIs"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database Configuration - Default Connection
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zabehaty_default
DB_USERNAME=root
DB_PASSWORD=

# Multi-Country Database Connections
DB_AE_HOST=127.0.0.1
DB_AE_PORT=3306
DB_AE_DATABASE=zabehaty_ae
DB_AE_USERNAME=root
DB_AE_PASSWORD=

DB_SA_HOST=127.0.0.1
DB_SA_PORT=3306
DB_SA_DATABASE=zabehaty_sa
DB_SA_USERNAME=root
DB_SA_PASSWORD=

DB_OM_HOST=127.0.0.1
DB_OM_PORT=3306
DB_OM_DATABASE=zabehaty_om
DB_OM_USERNAME=root
DB_OM_PASSWORD=

DB_KW_HOST=127.0.0.1
DB_KW_PORT=3306
DB_KW_DATABASE=zabehaty_kw
DB_KW_USERNAME=root
DB_KW_PASSWORD=

DB_BH_HOST=127.0.0.1
DB_BH_PORT=3306
DB_BH_DATABASE=zabehaty_bh
DB_BH_USERNAME=root
DB_BH_PASSWORD=

# Cache Configuration
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

# Twilio Configuration (WhatsApp Integration)
TWILIO_ACCOUNT_SID_ZABEHATY_APP=your_twilio_account_sid_here
TWILIO_AUTH_TOKEN_ZABEHATY_APP=your_twilio_auth_token_here
TWILIO_WHATSAPP_NUMBER_ZABEHATY_APP=your_whatsapp_number_here

TWILIO_ACCOUNT_SID_HALAL_APP=your_twilio_account_sid_here
TWILIO_AUTH_TOKEN_HALAL_APP=your_twilio_auth_token_here
TWILIO_WHATSAPP_NUMBER_HALAL_APP=your_whatsapp_number_here

# Firebase Configuration (FCM Integration)
FIREBASE_PROJECT_ID_ZABEHATY_APP=zabehaty-98bce
FIREBASE_PROJECT_ID_HALAL_APP=halal-bca9c
EOF
        print_status ".env file created"
    else
        print_info ".env file already exists"
    fi
    
    # Install dependencies
    print_info "Installing PHP dependencies..."
    composer install
    
    print_info "Installing Node.js dependencies..."
    npm install
    
    # Setup Laravel
    print_info "Setting up Laravel..."
    php artisan key:generate
    
    # Create Firebase directory
    mkdir -p storage/app/firebase
    print_status "Firebase directory created"
    
    print_warning "Please create the following databases manually:"
    echo "  - zabehaty_default"
    echo "  - zabehaty_ae"
    echo "  - zabehaty_sa"
    echo "  - zabehaty_om"
    echo "  - zabehaty_kw"
    echo "  - zabehaty_bh"
    echo ""
    echo "Then run:"
    echo "  php artisan migrate"
    echo "  php artisan country:db migrate --all"
    echo "  php artisan db:seed"
    echo "  php artisan serve"
    
    print_status "Local setup completed!"
}

# Main setup function
main() {
    echo "Choose setup method:"
    echo "1) Docker (Recommended)"
    echo "2) Local setup"
    echo "3) Exit"
    echo ""
    read -p "Enter your choice (1-3): " choice
    
    case $choice in
        1)
            if check_docker; then
                setup_docker
            else
                print_error "Docker is required for this setup method"
                exit 1
            fi
            ;;
        2)
            if check_php; then
                setup_local
            else
                print_error "PHP and Composer are required for this setup method"
                exit 1
            fi
            ;;
        3)
            print_info "Setup cancelled"
            exit 0
            ;;
        *)
            print_error "Invalid choice"
            exit 1
            ;;
    esac
}

# Run main function
main
