# Deployment Guide

## Overview

This plugin uses automated GitHub Actions workflows for deploying to staging and production environments.

## Prerequisites

1. **GitHub Repository Secrets** - Add these to your GitHub repository settings (Settings → Secrets and variables → Actions):

   - `STAGING_SFTP_HOST` - Staging server hostname
   - `STAGING_SFTP_USER` - SSH/SFTP username
   - `STAGING_SFTP_PORT` - SSH port (default: 22)
   - `STAGING_SSH_KEY` - Private SSH key for authentication
   - `STAGING_PLUGIN_PATH` - Full path to plugin directory on staging (e.g., `/var/www/html/wp-content/plugins/frs-lrg`)
   - `STAGING_WP_PATH` - Full path to WordPress root on staging (e.g., `/var/www/html`)

2. **SSH Key Setup**:
   ```bash
   # Generate SSH key pair
   ssh-keygen -t ed25519 -C "github-actions@staging"

   # Add public key to staging server's ~/.ssh/authorized_keys
   # Add private key to STAGING_SSH_KEY secret in GitHub
   ```

## Deployment Workflows

### Staging Deployment

**Triggers:**
- Push to `staging` branch
- Push to `develop` branch
- Manual dispatch via GitHub Actions UI

**Process:**
1. Checkout code
2. Install dependencies (npm + composer)
3. Build production assets
4. Deploy via SFTP to staging server
5. Activate plugin and flush caches

**To deploy to staging:**
```bash
# Merge your feature branch to staging
git checkout staging
git merge feature/your-feature-name
git push origin staging

# Or create a PR to staging branch on GitHub
```

### Manual Deployment via GitHub UI

1. Go to GitHub repository → Actions tab
2. Select "Deploy to Staging" workflow
3. Click "Run workflow"
4. Select branch to deploy
5. Click "Run workflow" button

## Branch Strategy

- `main` - Production branch (protected)
- `staging` - Staging environment branch
- `develop` - Development branch (auto-deploys to staging)
- `feature/*` - Feature branches (merge to develop when ready)

## Deployment Checklist

Before deploying to staging:

- [ ] All tests pass locally
- [ ] Production build succeeds (`npm run build`)
- [ ] No console errors in browser
- [ ] Database migrations tested (if any)
- [ ] WordPress version compatibility verified
- [ ] PHP version compatibility verified (8.1+)

## Post-Deployment Verification

After staging deployment:

1. **Visit staging site** - Verify plugin is active
2. **Check frontend** - Test all portal pages
3. **Check admin** - Verify admin pages load
4. **Test key features**:
   - Portal navigation
   - Tools customization
   - Mortgage calculator
   - Property valuation
   - Lead submissions
5. **Check logs** - Review server error logs
6. **Browser console** - No JavaScript errors

## Rollback Procedure

If deployment fails or causes issues:

1. **Quick rollback via Git:**
   ```bash
   # On staging branch
   git revert HEAD
   git push origin staging
   ```

2. **Manual rollback via SSH:**
   ```bash
   ssh user@staging-server
   cd /path/to/wordpress
   wp plugin deactivate frs-lrg
   # Restore previous version from backup
   wp plugin activate frs-lrg
   ```

## Production Deployment

Production deployments should follow a more controlled process:

1. **Test on staging first** - All features verified on staging
2. **Create release branch** - `release/v1.x.x`
3. **Update version** - Bump version in `frs-lrg.php`
4. **Create PR to main** - Include release notes
5. **Manual approval required** - Team review and approval
6. **Deploy during maintenance window** - Schedule with team
7. **Monitor closely** - Watch logs and user reports

## Troubleshooting

### Deployment fails at build step
- Check `package.json` and `composer.json` are valid
- Verify all dependencies are available
- Check build logs in GitHub Actions

### SFTP connection fails
- Verify SSH key is correct and has no passphrase
- Check firewall rules allow GitHub Actions IPs
- Verify SFTP_HOST and SFTP_PORT are correct

### Plugin not activating
- Check PHP version compatibility (8.1+)
- Verify all Composer dependencies installed
- Check WordPress error logs

### Assets not loading
- Verify `assets/` directory was uploaded
- Check file permissions (644 for files, 755 for directories)
- Clear WordPress object cache

## Support

For deployment issues:
- Check GitHub Actions logs
- Review server error logs
- Contact DevOps team
- Create issue in GitHub repository

## Security Notes

- Never commit SSH keys or passwords
- Always use GitHub Secrets for credentials
- Rotate SSH keys periodically
- Use least-privilege access for deployment users
- Enable 2FA on GitHub
