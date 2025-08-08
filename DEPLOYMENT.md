# MindMate Deployment (Docker + Railway)

This guide deploys the PHP app with Apache to Railway using a Dockerfile.

## Prerequisites

- GitHub repo containing this project
- Railway account and CLI (optional)
- Your MySQL database already hosted on Railway (you have this)

## Files Added

- `Dockerfile` — Builds a production image using `php:8.2-apache`
- `.dockerignore` — Keeps the image lean and excludes secrets

## Build Locally (optional)

If you have Docker installed, you can test the image locally:

```bash
# From project root
docker build -t mindmate:latest .
# Run on localhost:8080
docker run --rm -p 8080:80 \
  -e DB_HOST=YOUR_DB_HOST:YOUR_DB_PORT \
  -e DB_USER=YOUR_DB_USER \
  -e DB_PASS=YOUR_DB_PASSWORD \
  -e DB_NAME=YOUR_DB_NAME \
  mindmate:latest
```

Open http://localhost:8080

Note: Provide DB env vars to match your Railway DB (or a local DB). The app prefers `DATABASE_URL` when available, otherwise reads individual vars. Supported envs:

- `DATABASE_URL` (mysql://user:pass@host:port/dbname)
- or `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`
- or Railway-provided `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`

## Deploy to Railway (recommended)

1. Push this project to GitHub (public or private).
2. In Railway dashboard:
   - New Project → Deploy from GitHub Repo → Select this repo.
   - Railway will auto-detect the Dockerfile and build.
3. Configure variables in the service → Variables tab:
   - Either set `DATABASE_URL` provided by your Railway MySQL plugin
   - Or set individual variables (`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME`), or use the Railway-provided `MYSQL*` variables
4. Deploy. After build completes, open the assigned `*.up.railway.app` URL.

### Notes

- No custom start command is required; the image runs `apache2-foreground` and listens on port 80. Railway maps `$PORT` automatically.
- Logs are visible in Railway Logs.
- If you add more Composer packages, redeploy to rebuild the image.

## Troubleshooting

- 500 error after deploy: verify DB env vars are set and correct.
- If using `DATABASE_URL`, ensure it is the MySQL URL from Railway (e.g., `mysql://user:pass@host:port/db`).
- Check logs in Railway dashboard for connection messages from `config/database.php`.
- Ensure `api/mood.php` and other endpoints are reachable at `/api/...`.
