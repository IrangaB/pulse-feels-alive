PULSE · FEELS ALIVE — WEBSITE (DEVELOPER NOTES)
================================================

WHAT THIS IS
- A small PHP website with a built-in admin page at /admin. There is no database, no Wix and no
  third-party service.
- The customer edits products, fabrics, texts, photos, contact details and the footer, and reads
  enquiries, at  https://<their-domain>/admin
- All content is saved as files in /data. Uploaded images and videos go into /uploads.

HOSTING REQUIREMENTS
- Any normal web hosting with PHP 8.0 or newer (cPanel/Plesk hosts, e.g. Zoner, one.com,
  Hostinger, Louhi). Apache is best: the included .htaccess files protect /data and /includes.
- The "data" and "uploads" folders must be writable by PHP. On most hosts this works by default.
  Otherwise set them to 755 (or 775).
- PHP mail() must be enabled for enquiry emails. Enquiries are always saved in the admin page,
  even if email fails.
- For video uploads, the host's upload_max_filesize / post_max_size should be at least 64M.
  Images need about 10M.
- Nginx hosts: the data files cannot be read anyway (each starts with <?php exit; ?>), but also
  deny /data, /includes and /tools in the server config.

GOING LIVE (FIRST TIME)
1. Upload everything in this folder to the hosting's web root (public_html) by FTP or the file
   manager. Do NOT upload: .git, .github, _site.
   DO upload data/setup-code.php. It is not in GitHub on purpose, so take it from this computer.
2. Open https://<domain>/admin. Enter the setup code (inside data/setup-code.php), then choose the
   admin username, password and the email address for enquiries.
   The setup code file deletes itself after use.
   Do this straight after uploading: until it is done, anyone with the setup code could create
   the login.
3. Settings → "Send a test email" → confirm that it arrives. Check the spam folder too.
4. Submit one test enquiry on the website. Check that it appears under Enquiries, then delete it.
5. Give the customer CUSTOMER-GUIDE.txt.

If the setup code is lost, create data/setup-code.php with this exact content (any new code):
<?php exit; ?>
{"code": "choose-a-long-random-code"}

If the customer forgets the password, delete data/config.php on the hosting. /admin then shows
setup again, which needs a new setup-code.php as above. Content and enquiries are kept.

UPDATING THE CODE LATER
Upload the changed files only. Never overwrite or delete /data or /uploads on the hosting: they
hold the customer's content.

BACKUPS
Download the /data and /uploads folders now and then. Together they are the full customer content.

REVIEW PREVIEW (GITHUB PAGES)
Each push to main runs .github/workflows/review-preview.yml. It publishes a static, read-only copy
of the site to GitHub Pages for customer review. In that copy the admin page and enquiry form are
switched off, and search engines are told not to index it.
One-time: GitHub repo → Settings → Pages → Source: "GitHub Actions".

LOCAL TESTING
php -S 127.0.0.1:8000   (run in this folder), then open http://127.0.0.1:8000 and /admin
Local test data is written to /data and /uploads. Both are excluded from Git.

FILES
index.php              the website (same design as before, content filled in from /data)
contact.php            receives the enquiry form: saves it and emails it
admin/                 the admin page
includes/bootstrap.php storage, uploads, email and security helpers
includes/defaults.php  the original content, used until the customer saves changes
tools/build-preview.php builds the static review copy
