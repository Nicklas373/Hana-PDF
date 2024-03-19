## HANA PDF 

<br>
<div align="center">
  <img src="screenshot/logo.png" alt="HANA" width="300" height="300">
</div>
<br>

__HANA PDF__ is your go-to solution for effortlessly managing your PDFs. We've designed it with simplicity in mind, so you can combine,
shrink, convert, and personalize your PDFs with just a few clicks. It was implemented with front-end framework like Tailwind CSS and
used of Flowbite library to maintain responsive and materialize interface. And powered with iLovePDF and Aspose Cloud API as one of the back-end.

---

![HANA](screenshot/1.png)

---

### Requirements [For development with local environment]
- [Apache 2.4](https://httpd.apache.org) or [Nginx](https://www.nginx.com)
- [Composer](http://getcomposer.org/)
- [Docker](https://www.docker.com/)
    * On Windows use Docker Desktop
    * On Linux use docker-compose and docker.io
- [Node JS 20.11](https://nodejs.org/en)
- [PHP 8.2.12](https://www.php.net/downloads.php)
- [PostgreSQL 16.2](https://www.postgresql.org/)
- [Postman](https://www.postman.com/)
- [Python 3.10.x](https://www.python.org/downloads/release/python-31011/)

---

### Python Module Requirements
- Requests

---

### Build Status
- [![CodeQL](https://github.com/Nicklas373/Hana-PDF/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/Nicklas373/Hana-PDF/actions/workflows/github-code-scanning/codeql)
- [![HANA Container SIT](https://github.com/Nicklas373/Hana-PDF/actions/workflows/docker-sit-env.yml/badge.svg)](https://github.com/Nicklas373/Hana-PDF/actions/workflows/docker-sit-env.yml)
- [![HANA Container Production](https://github.com/Nicklas373/hana-ci-docker-prod/actions/workflows/docker-prod-env.yml/badge.svg)](https://github.com/Nicklas373/hana-ci-docker-prod/actions/workflows/docker-prod-env.yml)

---

### Commit History
- [Old branch - master](https://github.com/Nicklas373/Hana-PDF/tree/master)
- [Frontend Services - fe/master](https://github.com/Nicklas373/Hana-PDF/tree/fe/master)
- [Backend Services - fe/master](https://github.com/Nicklas373/Hana-PDF/tree/be/master)

---

### Deployment On Docker
#### Step to configure
1. Clone the repository with branch __docker/master__
2. Go to root directory from this project
3. Start to deploy
    ````bash
        - docker compose up -d
        - docker compose exec hana-api-service php artisan migrate
        - docker compose exec hana-api-service php artisan db:seed
    ````
4. Configure Server Host
     ````bash
        - docker compose exec hana-api-services echo "TELEGRAM_BOT_ID=YOUR_TELEGRAM_BOT_ID" >> .env
        - docker compose exec hana-api-services echo "TELEGRAM_CHAT_ID=YOUR_TELEGRAM_CHANNEL_ID" >> .env
        - docker compose exec hana-api-services echo "HANA_UNIQUE_TOKEN=YOUR_SHA512_UNIQUE_TOKEN" >> .env
        - docker compose exec hana-api-services sed -i "s/ASPOSE_CLOUD_CLIENT_ID=xxxx/ASPOSE_CLOUD_CLIENT_ID=YOUR_ASPOSE_CLOUD_CLIENT_ID/" >> .env
        - docker compose exec hana-api-services sed -i "s/ASPOSE_CLOUD_TOKEN=xxxx/ASPOSE_CLOUD_TOKEN=YOUR_ASPOSE_CLOUD_TOKEN" >> .env
        - docker compose exec hana-api-services sed -i "s/FTP_USERNAME=xxxx/FTP_USERNAME=YOUR_FTP_USERNAME/" >> .env
        - docker compose exec hana-api-services sed -i "s/FTP_USERPASS=xxxx/FTP_USERNAME=YOUR_FTP_USERPASS/" >> .env
        - docker compose exec hana-api-services sed -i "s/FTP_ROOT=xxxx/FTP_USERNAME=YOUR_FTP_ROOT_DIR/" >> .env
        - docker compose exec hana-api-services sed -i "s/ILOVEPDF_PUBLIC_KEY=xxxx/FTP_USERNAME=YOUR_ILOVEPDF_PUBLIC_KEY/" >> .env
        - docker compose exec hana-api-services sed -i "s/ILOVEPDF_SECRET_KEY=xxxx/FTP_USERNAME=YOUR_ILOVEPDF_SECRET_KEY/" >> .env
    ````
5. Configure Client Host
     ````bash
        - docker compose exec hana-app-pdf sed -i "s/VITE_ADOBE_CLIENT_ID=xxxx/VITE_ADOBE_CLIENT_ID=YOUR_ADOBE_CLIENT_ID/" >> .env
        - docker compose exec hana-app-pdf echo "TELEGRAM_BOT_ID=YOUR_TELEGRAM_BOT_ID" >> .env
        - docker compose exec hana-app-pdf echo "TELEGRAM_CHAT_ID=YOUR_TELEGRAM_CHANNEL_ID" >> .env
     ````
6. Configure REST API
    - Install Postman
    - Create a new HTTP request with POST format
        - URL: http://YOUR_LOCAL_IP:YOUR_LOCAL_PORT/api/v1/auth/token
        - Body: form-data
            - email: eureka@hana-ci.com
            - password: YOUR_SHA512_UNIQUE_TOKEN
    - Send a POST request to get access token
    - Set token to FE services
        ````bash
            - docker compose exec hana-app-pdf sed -i 's|Bearer STATIC_BEARER|Bearer YOUR_CURRENT_BEARER|' public/build/assets/kao-logic-CHECK_LATEST_REVISION.js
            - docker compose exec hana-app-pdf sed -i 's|STATIC_BEARER|YOUR_CURRENT_BEARER|' public/build/assets/kao-logic-CHECK_LATEST_REVISION.js
            - docker compose exec hana-app-pdf sed -i 's|http://192.168.0.2|YOUR_BACKEND_URL:PORT|' public/build/assets/kao-logic-CHECK_LATEST_REVISION.js
            - docker compose exec hana-app-pdf sed -i 's|STATIC_CLIENT_ID|YOUR_ADOBE_CLIENT_ID|' public/build/assets/kao-logic-CHECK_LATEST_REVISION.js
        ````
7. Refresh page and done.

---

### Deployment On Native OS Host
#### Step to configure
1. Clone the repository with branch __dev/fe/master__ [Frontend Services]
    
    A. Copy __.env.example__ file to __.env__ and modify database credentials
    ````bash
        - VITE_ADOBE_CLIENT_ID="ADOBE_CLIENT_ID" [https://developer.adobe.com/document-services/docs/overview/pdf-embed-api/]
        - VITE_JWT_TOKEN="YOUR_CURRENT_BEARER_TOKEN" [Get it from Backend with route api/v1/auth/token]
        - TELEGRAM_BOT_ID="YOUR_TELEGRAM_BOT_ID" [https://telegram-bot-sdk.com/docs/getting-started/installation]
        - TELEGRAM_CHAT_ID="YOUR_TELEGRAM_CHANNEL_ID" [https://telegram-bot-sdk.com/docs/getting-started/installation]
    ````
    B. Run the following command [Make sure to configure database connectivity before use migrate function]
    ````bash
        - composer install
        - npm run install
        - php artisan key:generate
        - php artisan storage:link
    ````
    C. Start to deploy
    ```bash
        - php artisan serve --host=localhost --port=81
    ```
2. Clone the repository with branch __dev/be/master [Backend Services]

    A. Copy __.env.example__ file to __.env__ and modify database credentials
    ````bash
        - ASPOSE_CLOUD_CLIENT_ID="ASPOSE_CLOUD_CLIENT_ID" [https://dashboard.aspose.cloud/]
        - ASPOSE_CLOUD_TOKEN="ASPOSE_CLOUD_TOKEN" [https://dashboard.aspose.cloud/]
        - FTP_USERNAME="FTP_USERNAME" [https://dashboard.aspose.cloud/]
        - FTP_USERPASS="FTP_USERPASS" [https://dashboard.aspose.cloud/]
        - FTP_SERVER="FTP_SERVER" [https://dashboard.aspose.cloud/]
        - ILOVEPDF_ENC_KEY="ILOVEPDF_ENC_KEY" [Generate your hash key (Max. 25 digits)]
        - ILOVEPDF_PUBLIC_KEY="ILOVEPDF_PUBLIC_KEY" [https://developer.ilovepdf.com/]
        - ILOVEPDF_SECRET_KEY="ILOVEPDF_SECRET_KEY" [https://developer.ilovepdf.com/]
        - PDF_IMG_POOL="image"
        - PDF_BATCH="batch"
        - PDF_UPLOAD="upload"
        - PDF_DOWNLOAD="download"
        - PDF_POOL="pool"
        - TELEGRAM_BOT_ID="YOUR_TELEGRAM_BOT_ID" [https://telegram-bot-sdk.com/docs/getting-started/installation]
        - TELEGRAM_CHAT_ID="YOUR_TELEGRAM_CHANNEL_ID" [https://telegram-bot-sdk.com/docs/getting-started/installation]
        - HANA_UNIQUE_TOKEN="YOUR_SHA512_UNIQUE_TOKEN"
    ````
    B. Run the following command [Make sure to configure database connectivity before use migrate function]
    ````bash
        - composer install
        - php artisan key:generate
        - php artisan jwt:secret
        - php artisan storage:link
    ````

    C. Create new directory inside storage/app/public
    * image
    * batch
    * upload
    * download
    * pool

    D. Start to deploy
    ````bash
        - npm run dev -- --host
        - php artisan serve --host=localhost --port=80
    ````
    E. Configure apiUrl variable on resources/js/kao-logic.js to your considered BE address and port !
---

### Technology Stack
- [Aspose](https://www.aspose.cloud/)
- [Docker](https://www.docker.com/)
- [DropzoneJS](https://www.dropzone.dev/)
- [Flowbite](https://flowbite.com/)
- [iLovePDF](https://developer.ilovepdf.com/)
- [Laravel](https://laravel.com/)
- [Node JS](https://nodejs.org/en)
- [Mozilla PDFJS](https://mozilla.github.io/pdf.js/)
- [PHPOffice](https://github.com/PHPOffice)
- [Python](https://www.python.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Vite JS](https://vitejs.dev/)

---

### License
The HANA PDF is a open source Laravel Project that has licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## HANA-CI Build Project 2016 - 2024
