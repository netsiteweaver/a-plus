# a-plus
A Plus Technology

## Deployment Scripts

After pulling code updates, you have two options:

### Full Deployment (Production)
```bash
./deploy.sh
```
This script:
- ✅ Puts app in maintenance mode
- ✅ Updates Composer dependencies
- ✅ Updates NPM dependencies
- ✅ Clears all caches
- ✅ Prompts for database migrations
- ✅ Builds frontend assets
- ✅ Optimizes application (caches configs, routes, views)
- ✅ Sets proper permissions
- ✅ Brings app back online

**Use this for production deployments.**

### Quick Update (Development)
```bash
./quick-update.sh
```
This script:
- ✅ Updates Composer dependencies
- ✅ Updates NPM dependencies
- ✅ Clears caches
- ✅ Builds frontend assets
- ✅ Sets permissions

**Use this for quick development updates (no maintenance mode, no migrations prompt).**
