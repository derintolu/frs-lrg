# Deployment Instructions

## Prerequisites

Your production server at `beta.frs.works` needs Composer installed.

## Deployment Methods

### Method 1: Git Pull + Docker Console (Recommended)

Your server uses Docker containers, so deployment is simple:

```bash
# 1. SFTP or git pull to get latest code
# From server (or via SFTP client):
cd ~/public_html/wp-content/plugins/frs-lrg
git fetch origin main
git reset --hard origin/main

# 2. Access Docker container console
# (Method varies by hosting - Cloudways, RunCloud, etc.)
# Example for typical Docker setup:
docker exec -it <container-name> bash

# 3. Inside container, run:
cd /var/www/html/wp-content/plugins/frs-lrg
composer install --no-dev -o
wp cache flush
wp rewrite flush
exit
```

**Automated Script:** Run `deploy-production.sh` inside the Docker container

### Method 2: SFTP + Docker Console

If git isn't available, use SFTP to upload files:

```bash
# Run locally to upload via SFTP
bash deploy-sftp.sh

# Then access Docker container and run:
docker exec -it <container-name> bash
cd /var/www/html/wp-content/plugins/frs-lrg
composer install --no-dev -o
wp cache flush
exit
```

### Method 3: Include Vendor Files (No Composer in Docker)

If Composer isn't available inside the Docker container, vendor files are already committed to git

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
