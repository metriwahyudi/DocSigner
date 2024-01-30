## Requirement
1. php 8.1
2. nodejs v20.8.0
3. npm 10.1.0

## Install
1. `composer install`
2. `npm install`
3. `npm run build`
4. Import `doc_signer.sql` to desired database.

## Configure
Configure Bitrix inbound API 
1. Open `.env`
2. Set value for `B24_INBOUND_API` to Btrix REST API endpoint. Must be `/` at the end of the URL string.
