# Deploy su Aruba (Apache/Nginx)

## Prerequisiti
- PHP 8.2+ con estensioni standard Laravel (`mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`).
- Composer disponibile in locale o sul server.
- Database MySQL/MariaDB.

## Opzione A: Deploy con SSH (consigliato)
1. Carica il progetto sul server (git clone o upload zip).
2. Installa dipendenze:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Configura `.env` di produzione:
   ```bash
   cp .env.example .env
   php artisan key:generate --force
   ```
4. Esegui migrazioni:
   ```bash
   php artisan migrate --force
   ```
5. Cache di produzione:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
6. Crea symlink storage (se permesso):
   ```bash
   php artisan storage:link
   ```

## Opzione B: Deploy senza SSH (hosting condiviso)
1. In locale:
   - esegui `composer install --no-dev --optimize-autoloader`;
   - prepara `.env` con variabili di produzione;
   - comprimi il progetto.
2. Carica via FTP nel path dell’applicazione.
3. Imposta `DocumentRoot` verso la cartella `public/`.
4. Verifica che `public/.htaccess` sia presente (rewrite Laravel).
5. Esegui migrazioni tramite pannello/strumento DB Aruba.

## Deploy in sottodirectory (es. `/karaoke`)
- Imposta `APP_URL=https://dominio.tld/karaoke`.
- Configura Apache/Nginx per puntare sempre a `public/index.php`.
- Verifica URL assets/media (`/media/...`) e route pubbliche.

## Apache (snippet)
- Usare la `.htaccess` standard Laravel in `public/.htaccess`.
- Abilitare `mod_rewrite`.

## Nginx (snippet minimo)
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
}
```

## Variabili ambiente minime
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=...`
- `DB_*`
- `SESSION_DRIVER=database`
- `SESSION_SECURE_COOKIE=true` (se HTTPS)

## Rollback
1. Mantieni backup di DB pre-deploy.
2. Mantieni archivio release precedente.
3. In caso di errore:
   - ripristina codice release precedente,
   - ripristina backup DB,
   - ricostruisci cache Laravel.
