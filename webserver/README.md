## Web Service [web server]

### Keterangan Teknologi
- Menggunakan PHP versi 5++, sebagai Core/Engine Rest API
- Menggunakan PostgreSQL versi 4++, sebagai penyimpanan database & active record
- Menggunakan beberapa plugin pihak ke 3 (_OfanCoreFramework/package/XXXXXX_)
- Menggunakan Rest API Platform pihak ke 3
  - Shipping > Tokped/BL/RajaOngkir
  - Payment > Doku/iPaymu
  - SMS Gateway > .....
  
### Informasi Direktori
```
--- api
  |
  |-- .htaccess
  |-- index.php
  |
  |--- OfanCoreFramework
    |
    |-- load.php
    |
    |--- app
    | |
    | |--- controller
    | |--- service
    | |--- i18n
    |
    |
    |--- manifest
    |--- method
    |--- package
    |--- system
      |
      |-- error.php
      |
      |--- library
      |--- modular
      |--- snippet
```
