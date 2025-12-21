# Email Notifications - Study Tracker

## Pregled / Overview

Ta projekt zdaj podpira pošiljanje email obvestil uporabnikom preko Inbucket SMTP testnega strežnika.

This project now supports sending email notifications to users via the Inbucket SMTP test server.

## Funkcionalnosti / Features

### 1. Obvestilo ob ustvarjanju predmeta / Subject Creation Notification
Ko uporabnik ustvari nov predmet, sistem samodejno pošlje email z:
- Imenom predmeta
- Datumom izpita
- Datumom ustvarjanja

When a user creates a new subject, the system automatically sends an email with:
- Subject name
- Exam date
- Creation date

### 2. Opomnik 2 dni pred izpitom / 2-Day Exam Reminder
Sistem avtomatsko pošilja opomnike uporabnikom 2 dni pred izpitom z:
- Pregledom vseh poglavij
- Časom učenja po poglavjih
- Skupnim časom učenja
- Motivacijskim sporočilom

The system automatically sends reminders to users 2 days before the exam with:
- Overview of all chapters
- Study time per chapter
- Total study time
- Motivational message

## Konfiguracija / Configuration

### Inbucket
- **Web vmesnik / Web Interface**: http://localhost:8002
- **SMTP vrata / SMTP Port**: 2500
- **SMTP gostitelj / SMTP Host**: inbucket

### Email nastavitve uporabnika / User Email Settings

Uporabniki lahko izberejo, ali želijo prejemati email obvestila med registracijo:
- Označite polje "Želim biti obveščen/a o svojem napredku preko emaila"
- To bo nastavilo `TK_tip_osebe` na `1` (email_yes)

Users can choose whether to receive email notifications during registration:
- Check the box "Želim biti obveščen/a o svojem napredku preko emaila"
- This sets `TK_tip_osebe` to `1` (email_yes)

## Testiranje / Testing

### Ogled poslanih emailov / View Sent Emails

1. Odprite Inbucket web vmesnik: http://localhost:8002
2. Vnesite uporabniško ime (del pred @ v email naslovu)
3. Oglejte si vse poslane emaile

1. Open the Inbucket web interface: http://localhost:8002
2. Enter the username (the part before @ in the email address)
3. View all sent emails

### Ročno testiranje cron posla / Manual Cron Job Testing

```bash
docker compose exec email-cron php /var/www/html/checkExamReminders.php
```

## Tehnične podrobnosti / Technical Details

### Datoteke / Files

- `sendEmail.php` - Funkcije za pošiljanje emailov in generiranje HTML vsebine
- `checkExamReminders.php` - Cron job skript za preverjanje izpitov in pošiljanje opomnikov
- `dodajPredmet.php` - Posodobljen za pošiljanje emailov ob ustvarjanju predmeta

- `sendEmail.php` - Functions for sending emails and generating HTML content
- `checkExamReminders.php` - Cron job script for checking exams and sending reminders
- `dodajPredmet.php` - Updated to send emails when creating a subject

### Docker storitve / Docker Services

- `inbucket` - SMTP test strežnik z web vmesnikom
- `email-cron` - PHP CLI zabojnik za izvajanje cron poslov

- `inbucket` - SMTP test server with web interface
- `email-cron` - PHP CLI container for running cron jobs

### Cron urnik / Cron Schedule

Cron job se izvaja vsakih 5 minut:
```
*/5 * * * * php /var/www/html/checkExamReminders.php >> /var/log/cron.log 2>&1
```

The cron job runs every 5 minutes:
```
*/5 * * * * php /var/www/html/checkExamReminders.php >> /var/log/cron.log 2>&1
```

### Ogled cron logov / View Cron Logs

```bash
docker compose logs email-cron
```

## Prihodnje izboljšave / Future Improvements

- Podpora za produkcijske SMTP strežnike (Gmail, SendGrid, itd.)
- Prilagodljivi email predlogi
- Dodatne vrste obvestil (novo poglavje, dosežki, itd.)
- Email nastavitve na uporabnikovem profilu

- Support for production SMTP servers (Gmail, SendGrid, etc.)
- Customizable email templates
- Additional notification types (new chapter, achievements, etc.)
- Email settings in user profile
