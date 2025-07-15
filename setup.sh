#!/bin/bash

# Thematic Analysis - Setup Script
# This script automates the initial setup process

set -e  # Exit on any error

echo "🚀 Thematic Analysis - Setup Script"
echo "===================================="
echo ""

# Source NVM if it exists
if [ -s "$HOME/.nvm/nvm.sh" ]; then
    echo "📦 Loading NVM..."
    source "$HOME/.nvm/nvm.sh"
elif [ -s "/usr/local/opt/nvm/nvm.sh" ]; then
    echo "📦 Loading NVM (Homebrew)..."
    source "/usr/local/opt/nvm/nvm.sh"
elif [ -s "/opt/homebrew/opt/nvm/nvm.sh" ]; then
    echo "📦 Loading NVM (Apple Silicon)..."
    source "/opt/homebrew/opt/nvm/nvm.sh"
else
    echo "❌ NVM not found. Please install NVM first:"
    echo "   https://github.com/nvm-sh/nvm"
    echo ""
    echo "💡 After installing NVM, restart your terminal or run:"
    echo "   source ~/.bashrc  # or ~/.zshrc"
    exit 1
fi

# Check if NVM is now available
if ! type nvm > /dev/null 2>&1; then
    echo "❌ NVM function not available. Please restart your terminal and try again."
    exit 1
fi

# Use Node.js 20
echo "📦 Setting up Node.js environment..."
nvm use 20

# Copy environment file
echo "📝 Creating .env file from example..."
if [ -f ".env" ]; then
    read -p "⚠️  .env file already exists. Overwrite? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "ℹ️  Skipping .env creation..."
    else
        cp .env.example .env
        echo "✅ .env file created"
    fi
else
    cp .env.example .env
    echo "✅ .env file created"
fi

# Prompt for OpenAI API Key
echo ""
echo "🔑 OpenAI API Key Setup"
echo "----------------------"
echo "Please enter your OpenAI API Key:"
echo "(You can get one from: https://platform.openai.com/api-keys)"
echo ""

# Read API key securely (without showing input)
read -s -p "OpenAI API Key: " OPENAI_KEY
echo ""

# Validate that a key was entered
if [ -z "$OPENAI_KEY" ]; then
    echo "❌ No API key provided. You can add it manually to the .env file later."
    echo "   Look for: OPENAI_API_KEY=your-api-key-here"
else
    # Add or update the OpenAI API key in .env file
    if grep -q "^OPENAI_API_KEY=" .env; then
        # Update existing key
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS
            sed -i '' "s/^OPENAI_API_KEY=.*/OPENAI_API_KEY=$OPENAI_KEY/" .env
        else
            # Linux
            sed -i "s/^OPENAI_API_KEY=.*/OPENAI_API_KEY=$OPENAI_KEY/" .env
        fi
        echo "✅ OpenAI API key updated in .env file"
    else
        # Add new key
        echo "OPENAI_API_KEY=$OPENAI_KEY" >> .env
        echo "✅ OpenAI API key added to .env file"
    fi
fi

# Generate Laravel application key
echo ""
echo "🔐 Generating Laravel application key..."
php artisan key:generate

echo ""
echo "✅ Setup complete!"
echo ""
echo "🚀 Starting the application..."
echo "   - Vue.js Frontend"
echo "   - Laravel API Backend" 
echo "   - Queue Worker"
echo ""
echo "Press Ctrl+C to stop all services"
echo ""

# Start the development environment
composer run dev &

# Wait a moment for services to start
sleep 3

# Open the analysis page in the default browser
echo "🌐 Opening analysis dashboard..."
if command -v open &> /dev/null; then
    # macOS
    open http://127.0.0.1:8000/analysis
elif command -v xdg-open &> /dev/null; then
    # Linux
    xdg-open http://127.0.0.1:8000/analysis
elif command -v start &> /dev/null; then
    # Windows
    start http://127.0.0.1:8000/analysis
else
    echo "📍 Navigate to: http://127.0.0.1:8000/analysis"
fi

# Keep the script running to maintain the services
wait
