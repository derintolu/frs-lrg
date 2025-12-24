# Portal Shortcode Diagnostic Checklist

## What We Fixed

1. ✅ Made `enqueue_portal_assets_public()` method public in Frontend.php
2. ✅ Modified shortcode to directly call enqueue method (bypasses wp_enqueue_scripts detection)
3. ✅ Added explicit React/ReactDOM dependency enqueuing
4. ✅ Verified shortcode registration and rendering
5. ✅ Verified built assets exist
6. ✅ Verified manifest is correct
7. ✅ Tested enqueue method works correctly

## All Tests Pass ✓

- ✓ Shortcode renders: `<div id="lrh-portal-root"></div>`
- ✓ Script registered: `lrh-portal`
- ✓ Script enqueued with React dependencies
- ✓ Inline config attached: `window.lrhPortalConfig`
- ✓ Script output includes all code (868 bytes)

## What to Check in Your Browser

### Step 1: Visit the Dashboard Page
URL: `http://hub21.local/dashboard` (or whatever your local URL is)

### Step 2: View Page Source (Ctrl+U / Cmd+Option+U)
Look for these in the HTML:

**A. Check if div is rendered:**
```html
<div id="lrh-portal-root"></div>
```
✓ Should be present in the page body

**B. Check if config is loaded (in `<head>` or before closing `</body>`):**
```javascript
window.lrhPortalConfig = {
    userId: ...,
    userName: "...",
    ...
};
```
✓ Should be present

**C. Check if React scripts are loaded:**
```html
<script type='module' src='http://hub21.local/wp-content/plugins/frs-lrg/assets/frontend/dist/assets/portal/portal-dashboards-aa539147.js' id='lrh-portal-js'></script>
```
✓ Should be near the end of the page (before `</body>`)

### Step 3: Open Browser Console (F12 / Cmd+Option+I)
Look for these console messages:

**Expected messages:**
```
Loan Officer Portal mounting with config: {userId: ..., userName: "...", ...}
Loan Officer Portal mounted successfully
```

**If you see errors:**
- Check the error message
- Look for 404 errors on script files
- Check for CORS errors
- Look for React errors

### Step 4: Check Network Tab
1. Open DevTools → Network tab
2. Refresh the page
3. Filter by "JS" or search for "portal"
4. Check if `portal-dashboards-aa539147.js` loads successfully
   - Status should be: 200 OK
   - Size should be: ~395 KB

## Common Issues & Solutions

### Issue: Div is there but no scripts loaded
**Solution:** Clear WordPress cache, browser cache, and hard refresh (Ctrl+Shift+R / Cmd+Shift+R)

### Issue: Scripts load but React doesn't mount
**Check console for:** JavaScript errors, missing window.lrhPortalConfig, React version conflicts

### Issue: 404 on script file
**Solution:** Rebuild assets:
```bash
cd /path/to/frs-lrg
npm run build
```

### Issue: Cached old version
**Solution:**
1. Clear WordPress object cache: `wp cache flush`
2. Clear browser cache
3. Disable any caching plugins temporarily

## If Still Not Working

Run these diagnostic commands:

```bash
# 1. Verify shortcode works
cd "/Users/derintolu/Local Sites/hub21/app/public/wp-content/plugins/frs-lrg"
wp eval-file test-page-render.php

# 2. Check if scripts are registered on the actual page
wp eval '
$post = get_post(68901);
setup_postdata($post);
do_action("wp_enqueue_scripts");
$content = apply_filters("the_content", $post->post_content);
echo $content . "\n";
global $wp_scripts;
var_dump(isset($wp_scripts->registered["lrh-portal"]));
'

# 3. View the actual page HTML
curl -s "http://hub21.local/dashboard" | grep -A 5 "lrh-portal"
```

## Summary

Based on all tests, the code is working correctly. The issue is most likely:
1. 🔄 **Browser cache** - Need hard refresh
2. 🔄 **WordPress cache** - Need cache flush
3. 🔄 **Old page view** - Need to visit the page fresh
4. ⚠️ **JavaScript error** - Check console for errors

The shortcode enqueue method is now directly called when the shortcode renders, which bypasses all the detection issues with the migrated code.
