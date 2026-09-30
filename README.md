# Oefenwebsite 1ste leerjaar

A practice website for children in their first year of primary school (ages 6–7). Built for a classroom setting: multiple children share one installation, each with their own name and 4-digit PIN.

The UI is entirely in Dutch.

## Features

- **Arithmetic**: addition, subtraction, mixed, times tables, three numbers, splitting, missing number, commutative sums, halves, comparing, ordering, counting & neighbours, jumps, clock reading, money, number snake
- **Word problems**: context-based arithmetic
- **Logical thinking**: more/less, reading pictograms/tables
- **Speed test**: answer as many sums as possible in 2 minutes (with highscore)
- **Progress tracking** per child (stars, progress bar, correct/total)
- **Per-child settings**: max number, highest times table, clock difficulty, jump step size

## Requirements

- PHP 7.4 or higher
- Apache with `mod_rewrite` and `mod_headers` enabled
- `AllowOverride All` set for the document root
- Write permissions on the `data/` directory

## Installation

1. Upload all files to your web server (or clone the repo).
2. Create the data directories and verify permissions by running `setup.php` (copy from `setup.php.example` if not present, or create it manually):
   ```
   mkdir -p data/users data/progress data/ratelimit
   chmod 755 data data/users data/progress data/ratelimit
   ```
3. Make sure `data/.htaccess` contains:
   ```
   Order deny,allow
   Deny from all
   ```
4. Open the site in a browser. Children can create an account with their name and a 4-digit PIN.

> **Note:** `setup.php` is excluded from the repository and from deployment. Run it once, then delete it.

## Security

- PINs are hashed with bcrypt (`password_hash`)
- User data is stored in `data/` which is blocked from web access via `.htaccess`
- CSRF protection on all state-changing requests
- Brute-force protection: 5 failed PIN attempts locks an account for 5 minutes
- Session cookies are `HttpOnly`, `SameSite=Lax`, and `Secure` on HTTPS
- Security headers: `Content-Security-Policy`, `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`

## Project structure

```
├── api/
│   ├── answer.php          # Submit and validate an answer
│   ├── exercise.php        # Generate a new exercise
│   ├── settings.php        # Save user settings
│   └── check-name.php      # Check whether a username exists
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── includes/
│   ├── auth.php            # Session, CSRF helpers
│   ├── config.php          # Exercise category definitions
│   ├── flatfile.php        # Flat-file storage (users, progress, rate limiting)
│   └── exercises/
│       ├── arithmetic.php
│       └── logical.php
├── data/                   # Runtime data (gitignored)
│   ├── users/
│   ├── progress/
│   └── ratelimit/
├── dashboard.php
├── exercise.php
├── settings.php
├── speedtest.php
├── logout.php
└── index.php
```

## License

MIT — see [LICENSE](LICENSE)
