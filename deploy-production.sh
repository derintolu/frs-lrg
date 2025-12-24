#!/bin/bash
# Production Deployment Script for beta.frs.works
# Run this script directly on the server

set -e  # Exit on error

echo "🚀 Starting deployment for FRS Lending Resource Hub..."

# Navigate to plugin directory
cd ~/public_html/wp-content/plugins/frs-lrg || exit 1

echo "📥 Pulling latest code from GitHub..."
git fetch origin main
git reset --hard origin/main

echo "📦 Installing Composer dependencies..."
composer install --no-dev -o

echo "🧹 Clearing WordPress caches..."
wp cache flush
wp rewrite flush

echo "✅ Deployment complete!"
echo "🔍 Current git commit:"
git log -1 --oneline

echo ""
echo "📝 Next steps:"
echo "1. Test the site: https://beta.frs.works"
echo "2. Check for any PHP errors in error logs"
echo "3. Verify plugin is active: wp plugin list | grep frs-lrg"
