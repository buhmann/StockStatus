# Magento 2 Buhmann StockStatus
<img src="https://img.shields.io/badge/magento-v2.4.6-green?style=plastic&logo=magento">

##Main Functionalities
Enabels filtering by stock status (simple  and configurable)

Added additional stock_status_filter attribute. The attribute is updated by cron, also added the ability to force the update using the command: `bin/magento buhmann:init:stockStatus`

With active filter `stock_status_filter=1`:

 - For simple products works default filter functionality.
 - For configurable products, if a simple product is defined (based on active filters) and that product is out of stock, then the parent configurable product is hidden.

## Installation guide
Please create a "Buhmann" folder in Your Magento modules directory (app) and copy the "CmsContent" folder into "Buhmann" folder

 ```
  bin/magento setup:upgrade
  bin/magento setup:di:compile
  bin/magento setup:static-content:deploy -f
  bin/magento indexer:reindex
  bin/magento c:f
  ```
