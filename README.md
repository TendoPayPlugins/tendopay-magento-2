# Instructions


## Step 1: Add TendoPay Module to Magento

Copy the files of this module into `<Magento root directory>/app/code/TendoPay/TendopayPayment`

## Step 2: Enable Magento 2 Module in terminal

Run the commands below to make sure the module is added and activated.

```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```
## Step 3: Run the command below

```bash
php bin/magento s:d:c
```

## Step 4: Add client_id and client_secret to Magento Config

Ensure TendoPay module has been activated. Create a V2 API in your [ TendoPay Merchant Dashboard](https://app.tendopay.ph/merchants/login) by navigating to **Apps** page and generating a V2 `client_id` and `client_secret`.

In your Magento Dashboard, navigate to `Stores > Configuration > Sales > Payment Methods > TendoPay - Buy now, pay later with TendoPay`. Enter the correct client_id and client_secret.

Flush Magento cache and you should be all set. Happy shopping
