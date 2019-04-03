![kiosdesa logo](https://i.pinimg.com/564x/c4/17/ad/c417adcccc9ac7b572492ca2bdef46d1.jpg)

# MOBILE APP - KIOSDESA.COM
Ini adalah repository untuk source code aplikasi kiosdesa.com versi mobile.
Untuk melakukan kolaborasi projek silahkan gunakan branch version (1, 2, 3, dst) dan **"jangan menggunakan branch master"**.
Lakukan _pull request_ untuk memulai update source code.

#### WARNING!
```
- usahakan teliti dan test terlebih dahulu code tersebut sebelum di upload ke github
- gunakan perintah git init pada command line sebelum pull request
```

### Informasi Source
- Semua file masuk ke dalam configurasi .git kecuali
  - node_modules
  - plugins
  - www
  - platforms
  
  ##### Catatan:
  Untuk file dalam folder .gitignore harus di tambahkan manual menggunakan **Comand Line** (CLI) pada NPM
  - untuk _node modules_ dan _plugins_ gunakan perintah:
    ```
    npm install
    ```
  - untuk _www_ gunakan perintah:
    ```
    npm run ionic:build
    ```
  - untuk _platform_ gunakan perintah:
    ```
    ionic cordova platform add android
    ionic cordova platform add ios
    ```
  - Semua perintah **CLI** NPM ada di file **_package.json_**
