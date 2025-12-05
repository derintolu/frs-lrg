# Deployment Instructions

## Prerequisites

Your production server at `beta.frs.works` needs Composer installed.

## Deployment Steps

### 1. Push Code (Excludes vendor/)

```bash
git add .
git commit -m "your commit message"
git push origin main
```

### 2. Deploy to Production

SSH into your server and run:

```bash
# Navigate to plugin directory
cd /path/to/wp-content/plugins/frs-lrg

# Pull latest code
git pull origin main

# Install production dependencies (no dev packages, optimized)
composer install --no-dev -o

# Clear WordPress caches
wp cache flush
wp rewrite flush
```

## One-Time Setup on Production

If Composer is not installed on your production server:

```bash
# Install Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer --version
```

## Benefits of This Approach

- ✅ Smaller git repository (no 140+ vendor files)
- ✅ Faster commits and pulls
- ✅ Production gets exact dependency versions
- ✅ Cleaner git history
- ✅ No merge conflicts in vendor files

## MCP Adapter Configuration

After deployment, create an application password for Claude Desktop:

```bash
wp user application-password create admin "Claude Desktop MCP" --porcelain
```

Then update your local `~/.claude-desktop-mcp-config.json` with the credentials.
