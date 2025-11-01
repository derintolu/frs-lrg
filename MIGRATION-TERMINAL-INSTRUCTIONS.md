# TERMINAL CLAUDE - EXECUTE NOW

## Current Status: Step 1 ✅ Complete | Steps 2-8 Execute Below

---

## STEP 2: Copy React Files

```bash
cd /app/public/wp-content/plugins/frs-lrg
mkdir -p assets/src/components assets/src/utils assets/src/types
cp ../frs-partnership-portal/assets/src/main.tsx assets/src/
cp ../frs-partnership-portal/assets/src/index.css assets/src/
cp ../frs-partnership-portal/assets/src/LoanOfficerPortal.tsx assets/src/
cp -r ../frs-partnership-portal/assets/src/components/* assets/src/components/
cp -r ../frs-partnership-portal/assets/src/utils/* assets/src/utils/
cp -r ../frs-partnership-portal/assets/src/types/* assets/src/types/ 2>/dev/null || true
rm -rf assets/src/components/portal-v3
ls -la assets/src/
```

## STEP 3: Update Config

```bash
cd /app/public/wp-content/plugins/frs-lrg/assets/src
sed -i '' 's/frs-partnership-portal-root/lrh-portal-root/g' main.tsx
sed -i '' 's/frsPortalConfig/lrhPortalConfig/g' main.tsx
```

## STEP 4: Create Build Files

```bash
cd /app/public/wp-content/plugins/frs-lrg/assets

cat > vite.config.js << 'EOF'
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';
export default defineConfig({
  plugins: [react()],
  resolve: { alias: { '@': path.resolve(__dirname, './src') } },
  build: {
    outDir: './js/portal',
    emptyOutDir: true,
    rollupOptions: {
      input: { 'portal-dashboards': path.resolve(__dirname, 'src/main.tsx') },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) return '../css/portal/[name][extname]';
          return 'assets/[name]-[hash][extname]';
        }
      }
    }
  }
});
EOF

cat > package.json << 'EOF'
{
  "name": "lending-resource-hub",
  "version": "1.0.0",
  "scripts": { "dev": "vite", "build": "vite build", "preview": "vite preview" },
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.20.0",
    "@radix-ui/react-avatar": "^1.0.4",
    "@radix-ui/react-dialog": "^1.0.5",
    "@radix-ui/react-dropdown-menu": "^2.0.6",
    "@radix-ui/react-select": "^2.0.0",
    "@radix-ui/react-tabs": "^1.0.4",
    "@radix-ui/react-label": "^2.0.2",
    "@radix-ui/react-slot": "^1.0.2",
    "lucide-react": "^0.294.0",
    "clsx": "^2.0.0",
    "tailwind-merge": "^2.0.0",
    "class-variance-authority": "^0.7.0"
  },
  "devDependencies": {
    "@types/react": "^18.2.43",
    "@types/react-dom": "^18.2.17",
    "@vitejs/plugin-react": "^4.2.1",
    "typescript": "^5.3.3",
    "vite": "^5.0.8",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.16",
    "postcss": "^8.4.32"
  }
}
EOF

cat > tailwind.config.js << 'EOF'
module.exports = {
  content: ["./src/**/*.{js,jsx,ts,tsx}"],
  theme: { extend: { colors: { primary: { DEFAULT: '#2563eb', foreground: '#ffffff' }, secondary: { DEFAULT: '#2dd4da', foreground: '#ffffff' } } } },
  plugins: []
}
EOF

cat > tsconfig.json << 'EOF'
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "baseUrl": ".",
    "paths": { "@/*": ["./src/*"] }
  },
  "include": ["src"]
}
EOF

cat > postcss.config.js << 'EOF'
export default { plugins: { tailwindcss: {}, autoprefixer: {} } }
EOF
```

## STEP 5: NPM Install

```bash
cd /app/public/wp-content/plugins/frs-lrg/assets
npm install
```

## STEP 6: Build

```bash
npm run build
ls -lh js/portal/portal-dashboards.js
ls -lh css/portal/portal-dashboards.css
```

## STEP 7: Activate

```bash
cd /app/public/wp-content/plugins
wp plugin deactivate frs-partnership-portal
wp plugin activate frs-lrg
wp db query "SHOW TABLES LIKE 'wp_partnerships'"
wp shortcode list | grep lrh
```

## STEP 8: Test

```bash
wp post create --post_type=page --post_title="Portal" --post_content="[lrh_portal]" --post_status=publish
```

---

**START: Execute Step 2 now**
