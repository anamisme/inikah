# Setup Sistem Notifikasi iNikah

## Langkah 1: Buat Google Sheets
1. Buka https://sheets.google.com → buat spreadsheet baru
2. Beri nama: **iNikah Notifikasi**
3. Di Sheet1, buat header di baris 1:
   - A1: `id`
   - B1: `judul`
   - C1: `pesan`
   - D1: `tanggal`

## Langkah 2: Buat Google Apps Script
1. Dari spreadsheet, klik **Extensions** → **Apps Script**
2. Hapus isi default, paste kode berikut:

```javascript
const SHEET_ID = '1vffB-1WXlSqtY_yCOw9iiv3ZTPNst_4_QVHxBl91N1o';

function doGet(e) {
  const action = e.parameter.action;
  const ss = SpreadsheetApp.openById(SHEET_ID);
  const sheet = ss.getSheetByName('Sheet1');
  
  if (action === 'get') {
    return getNotifs(sheet);
  } else if (action === 'add') {
    return addNotif(sheet, e.parameter.judul, e.parameter.pesan);
  } else if (action === 'delete') {
    return deleteNotif(sheet, e.parameter.id);
  }
  
  return ContentService.createTextOutput(JSON.stringify({error: 'Invalid action'}))
    .setMimeType(ContentService.MimeType.JSON);
}

function getNotifs(sheet) {
  const data = sheet.getDataRange().getValues();
  const result = [];
  for (let i = 1; i < data.length; i++) {
    if (data[i][0]) {
      result.push({
        id: data[i][0].toString(),
        judul: data[i][1],
        pesan: data[i][2],
        tanggal: data[i][3]
      });
    }
  }
  result.reverse();
  return ContentService.createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}

function addNotif(sheet, judul, pesan) {
  const id = Date.now().toString();
  const tanggal = Utilities.formatDate(new Date(), 'Asia/Jakarta', 'dd MMM yyyy, HH:mm');
  sheet.appendRow([id, judul, pesan, tanggal]);
  return ContentService.createTextOutput(JSON.stringify({success: true, id: id}))
    .setMimeType(ContentService.MimeType.JSON);
}

function deleteNotif(sheet, id) {
  const data = sheet.getDataRange().getValues();
  for (let i = 1; i < data.length; i++) {
    if (data[i][0].toString() === id) {
      sheet.deleteRow(i + 1);
      break;
    }
  }
  return ContentService.createTextOutput(JSON.stringify({success: true}))
    .setMimeType(ContentService.MimeType.JSON);
}
```

3. Ganti `GANTI_DENGAN_ID_SPREADSHEET_ANDA` dengan ID spreadsheet 
   (dari URL: `https://docs.google.com/spreadsheets/d/ID_DISINI/edit`)

## Langkah 3: Deploy Apps Script
1. Klik **Deploy** → **New deployment**
2. Type: **Web app**
3. Execute as: **Me**
4. Who has access: **Anyone**
5. Klik **Deploy** → copy URL yang muncul

## Langkah 4: Update URL di Project
Ganti teks `GANTI_DENGAN_DEPLOY_URL_ANDA` di 2 file:
1. `script.js` — baris `const NOTIF_SCRIPT_URL = ...`
2. `admin.html` — baris `const NOTIF_SCRIPT_URL = ...`

## Langkah 5: Selesai!
- User buka app → lihat notifikasi via icon 🔔
- Admin buka `/admin.html` → login → kirim/hapus notifikasi
- Password admin default: `kuakarangdadap2024`

## Cara Ganti Password Admin
Edit file `admin.html`, cari baris:
```javascript
const ADMIN_PASSWORD = 'kuakarangdadap2024';
```
Ganti dengan password yang diinginkan.
