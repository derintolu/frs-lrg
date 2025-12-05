#!/bin/bash
# SFTP Deployment Script for beta.frs.works
# Uploads files via SFTP (no SSH access)

set -e

echo "🚀 Starting SFTP deployment to beta.frs.works..."

# Load credentials
source .sftp-credentials

# Create SFTP batch file
cat > /tmp/sftp-deploy-batch.txt << EOF
cd public_html/wp-content/plugins/frs-lrg
lcd /Users/derintolu/Local Sites/hub21/app/public/wp-content/plugins/frs-lrg
put -r includes/
put -r database/
put -r assets/
put -r blocks/
put -r src/
put plugin.php
put composer.json
put DEPLOY.md
put deploy-production.sh
bye
EOF

echo "📤 Uploading files via SFTP..."
sshpass -p "$PASSWORD" sftp -P "$PORT" -o StrictHostKeyChecking=no -b /tmp/sftp-deploy-batch.txt "$USER@$HOST"

echo ""
echo "✅ Files uploaded successfully!"
echo ""
echo "⚠️  IMPORTANT: You still need to run on the server:"
echo "   1. Navigate to cPanel File Manager or Terminal"
echo "   2. Go to: ~/public_html/wp-content/plugins/frs-lrg"
echo "   3. Run: composer install --no-dev -o"
echo "   4. Run: wp cache flush && wp rewrite flush"
echo ""
echo "   OR use the WP Admin to deactivate/reactivate the plugin"

# Clean up
rm /tmp/sftp-deploy-batch.txt
