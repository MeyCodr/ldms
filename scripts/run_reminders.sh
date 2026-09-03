#!/usr/bin/env bash
#
# Daily reminder runner - the Linux/VPS replacement for script.bat.
#
# Runs every reminder script in order, one after another, logging what each one
# printed. Each script is individually responsible for deciding whether it has
# anything to send today (they all keep a per-day log row in the database), so
# running this more than once a day is harmless.
#
# INSTALL ON THE VPS
# ------------------
#   chmod +x scripts/run_reminders.sh
#   crontab -e
#
# then add ONE of these lines:
#
#   # 8am Malaysia time, if the server clock is already Asia/Kuala_Lumpur
#   0 8 * * * /var/www/ldms/scripts/run_reminders.sh
#
#   # 8am Malaysia time, if the server clock is UTC (the usual VPS default)
#   0 0 * * * /var/www/ldms/scripts/run_reminders.sh
#
# Adjust the path to wherever the app actually lives. Check which case you are
# in with `timedatectl` (or `date`) before choosing. Nothing else in the line is
# needed - no `cd`, no output redirection - this script handles its own paths
# and logging.
#
# The PHP scripts pin their own timezone to Asia/Kuala_Lumpur internally, so
# their date arithmetic is correct either way; the crontab hour is only about
# what time of day the mail actually goes out.

set -uo pipefail

# Resolve the app directory from this script's own location rather than
# hardcoding it, so the same file works whatever path the VPS deploys to.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"

# cron runs with a near-empty PATH, so find PHP explicitly rather than assuming
# it is on it. PHP_BIN can be set in the environment to override.
PHP_BIN="${PHP_BIN:-$(command -v php || true)}"
if [ -z "$PHP_BIN" ]; then
    for candidate in /usr/bin/php /usr/local/bin/php /opt/cpanel/ea-php82/root/usr/bin/php; do
        if [ -x "$candidate" ]; then PHP_BIN="$candidate"; break; fi
    done
fi
if [ -z "$PHP_BIN" ] || [ ! -x "$PHP_BIN" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] FATAL: no php binary found; set PHP_BIN in the crontab line" >&2
    exit 1
fi

# The app directory is served by Apache, so the default log location sits under
# the web root and would be downloadable at /ldms/logs/reminders.log - and the
# log names staff and their email addresses. Best practice is to point
# LDMS_LOG_DIR somewhere outside the web root entirely:
#     LDMS_LOG_DIR=/var/log/ldms
# Where that is not possible, the deny rules dropped in below keep Apache from
# serving the directory (2.4 "Require all denied" plus the 2.2 form, so it works
# whichever the VPS runs).
LOG_DIR="${LDMS_LOG_DIR:-$APP_DIR/logs}"
mkdir -p "$LOG_DIR"
if [ -z "${LDMS_LOG_DIR:-}" ] && [ ! -f "$LOG_DIR/.htaccess" ]; then
    cat >"$LOG_DIR/.htaccess" <<'HTACCESS'
# Not for public consumption - these logs name staff and their email addresses.
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order allow,deny
    Deny from all
</IfModule>
HTACCESS
fi
LOG_FILE="$LOG_DIR/reminders.log"

# Stop two runs overlapping if one is slow (SMTP hanging) and cron fires again.
# Without this a long run could still be sending while the next starts up.
LOCK_FILE="$LOG_DIR/reminders.lock"
if command -v flock >/dev/null 2>&1; then
    exec 9>"$LOCK_FILE"
    if ! flock -n 9; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Another run is still in progress; skipping." >>"$LOG_FILE"
        exit 0
    fi
fi

# Scripts to run, in order. Paths are relative to the app directory.
SCRIPTS=(
    "reminder.php"
    "scripts/notify_pme_incomplete.php"
    "scripts/notify_attendance_incomplete.php"
)

{
    echo "===================================================================="
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Reminder run starting (php: $PHP_BIN)"

    overall_status=0
    for script in "${SCRIPTS[@]}"; do
        full_path="$APP_DIR/$script"
        if [ ! -f "$full_path" ]; then
            echo "--- SKIP $script (not found at $full_path)"
            continue
        fi
        echo "--- RUN $script"
        # Each script is run independently so one failing (SMTP outage, PHP
        # error) does not stop the others from going out.
        if "$PHP_BIN" -f "$full_path"; then
            echo "--- OK $script"
        else
            status=$?
            echo "--- FAILED $script (exit $status)"
            overall_status=1
        fi
    done

    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Reminder run finished (status $overall_status)"
} >>"$LOG_FILE" 2>&1

# Keep the log from growing without bound - trim to the last 5000 lines.
if [ -f "$LOG_FILE" ]; then
    tail -n 5000 "$LOG_FILE" >"$LOG_FILE.tmp" 2>/dev/null && mv "$LOG_FILE.tmp" "$LOG_FILE"
fi

exit "${overall_status:-0}"
