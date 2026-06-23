# Build Guide - iNikah TWA (Android App)

## Prasyarat
- Android Studio (install dari https://developer.android.com/studio)
- Java 17 (sudah ada ✅)

## Langkah Build

### 1. Buka Project di Android Studio
- Buka Android Studio → File → Open → pilih folder `twa-project`
- Tunggu Gradle sync selesai

### 2. Tambahkan Icon App
- Siapkan icon app 512x512px
- Letakkan di `app/src/main/res/mipmap-xxxhdpi/ic_launcher.png`
- Atau gunakan Android Studio: klik kanan res → New → Image Asset

### 3. Build AAB (untuk Play Store)
Di terminal Android Studio:
```bash
./gradlew bundleRelease
```
Output: `app/build/outputs/bundle/release/app-release.aab`

### 4. Sign AAB dengan Upload Key
```bash
jarsigner -verbose -sigalg SHA256withRSA -digestalg SHA-256 \
  -keystore /path/to/inikah-upload.keystore \
  app/build/outputs/bundle/release/app-release.aab \
  inikah
```
Password: (yang kamu set saat generate)

### 5. Upload ke Play Store
- Play Console → app iNikah → Production → Create new release
- Upload file .aab yang sudah di-sign
- Isi release notes
- Submit for review

## Catatan Penting

### Link Eksternal
Semua link selain `inikah.pages.dev` otomatis dibuka di browser luar:
- Google Forms
- Google Drive
- WhatsApp
- Google Sheets

### Package Name
`com.nucleapp.na_76ba41` — harus sama dengan app lama supaya jadi update.

### Version
- versionCode: 2 (harus lebih besar dari versi terakhir di Play Store)
- versionName: 2.0.0

Kalau versi lama sudah versionCode 2 atau lebih, naikkan di `app/build.gradle`.

### Digital Asset Links
File `/.well-known/assetlinks.json` sudah ada di website.
Pastikan accessible di: https://inikah.pages.dev/.well-known/assetlinks.json
