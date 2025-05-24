# WordPress Plugin Deployment Guide

This guide covers deploying WP-SCSS to the WordPress.org plugin repository using GitHub Actions.

## Prerequisites

### Repository Secrets
Set up these secrets in your GitHub repository (Settings → Secrets and variables → Actions):

1. **SVN_USERNAME**: Your WordPress.org username
2. **SVN_PASSWORD**: Your WordPress.org password

### WordPress.org Plugin Setup
- Ensure your plugin is approved and listed on WordPress.org
- Verify you have commit access to the plugin's SVN repository

## Deployment Methods

### 1. Tag-Based Deployment (Recommended)
Automatically deploys when you create a new tag:

```bash
# Create and push a new tag
git tag 4.0.4
git push origin 4.0.4
```

### 2. Manual Deployment
Use the workflow dispatch option:

1. Go to Actions → Deploy to WordPress.org
2. Click "Run workflow"
3. Optionally enable "dry-run" to test without deploying

## Pre-Deployment Checklist

### Version Consistency
Ensure these files have matching version numbers:
- [ ] `wp-scss.php` (Plugin header and `WPSCSS_VERSION_NUM`)
- [ ] `readme.txt` (Stable tag)

### Code Quality
- [ ] Test plugin functionality locally
- [ ] Verify no PHP errors or warnings
- [ ] Check WordPress coding standards compliance

### Documentation
- [ ] Update `readme.txt` changelog
- [ ] Update `README.md` if needed
- [ ] Document any breaking changes

## Deployment Process

1. **Update Version Numbers**
   ```bash
   # Update version in wp-scss.php (line 6 and line 47)
   # Update stable tag in readme.txt (line 8)
   ```

2. **Commit Changes**
   ```bash
   git add .
   git commit -m "Version bump to X.X.X"
   git push origin master
   ```

3. **Create Release Tag**
   ```bash
   git tag X.X.X
   git push origin X.X.X
   ```

4. **Monitor Deployment**
   - GitHub Actions will automatically trigger
   - Check the Actions tab for deployment status
   - Verify the plugin updates on WordPress.org

## Troubleshooting

### Common Issues

**Authentication Failed**
- Verify SVN_USERNAME and SVN_PASSWORD secrets
- Check WordPress.org account permissions

**Version Conflicts**
- Ensure tag version matches plugin file versions
- Check that the tag doesn't already exist

**Build Failures**
- Review GitHub Actions logs
- Test deployment with dry-run first

### Manual SVN Deployment (Fallback)

If GitHub Actions fails, deploy manually:

```bash
# Clone SVN repository
svn co https://plugins.svn.wordpress.org/wp-scss wp-scss-svn

# Copy files to trunk
cp -r wp-scss/* wp-scss-svn/trunk/

# Create tag directory
svn cp trunk tags/X.X.X

# Commit changes
svn ci -m "Version X.X.X"
```

## Workflow Configuration

The deployment workflow (`.github/workflows/deploy.yml`) includes:
- Automatic deployment on tag push
- Manual dispatch with dry-run option
- Uses 10up/action-wordpress-plugin-deploy@v2
- Runs on Ubuntu latest

## Best Practices

1. **Test First**: Use dry-run mode for new deployments
2. **Semantic Versioning**: Follow semver (major.minor.patch)
3. **Changelog**: Always update readme.txt changelog
4. **Backup**: Keep local backups before major releases
5. **Monitor**: Watch WordPress.org for user feedback post-deployment

## Release Checklist

- [ ] Version numbers updated consistently
- [ ] Code tested locally
- [ ] Changelog updated in readme.txt
- [ ] Breaking changes documented
- [ ] Git tag created and pushed
- [ ] GitHub Actions deployment successful
- [ ] WordPress.org plugin page updated
- [ ] User notifications sent (if needed)