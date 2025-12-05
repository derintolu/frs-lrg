# Deployment Instructions

## Prerequisites

Your production server at `beta.frs.works` needs Composer installed.

## Deployment Methods

### Method 1: Automated Script (Recommended)

SSH into your server and run the deployment script:

```bash
# SSH into server
ssh -p 222 derin@my.frs.works

# Navigate to plugin directory
cd ~/public_html/wp-content/plugins/frs-lrg

# Run deployment script
bash deploy-production.sh
```

The script will:
- Pull latest code from GitHub
- Install Composer dependencies
- Clear WordPress caches
- Show current deployment status

### Method 2: Manual Deployment

If you prefer manual control:

```bash
# SSH into server
ssh -p 222 derin@my.frs.works

# Navigate to plugin directory
cd ~/public_html/wp-content/plugins/frs-lrg

# Pull latest code
git fetch origin main
git reset --hard origin/main

# Install production dependencies (no dev packages, optimized)
composer install --no-dev -o

# Clear WordPress caches
wp cache flush
wp rewrite flush
```

### Method 3: GitHub Actions (Auto-Deploy)

Automatically deploy on every push to `main` branch:

1. Add secrets to your GitHub repository:
   - Go to Settings → Secrets and variables → Actions
   - Add `SSH_USERNAME` (value: `derin`)
   - Add `SSH_PASSWORD` (your SSH password)

2. Push to `main` branch - deployment happens automatically!

3. Monitor deployment: Go to Actions tab in GitHub

**To disable auto-deploy:** Delete `.github/workflows/deploy.yml`

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
