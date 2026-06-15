# Magento 2 Buhmann Stock Status Filter
[![Magento](https://img.shields.io/badge/magento-2.4.7+-green.svg?logo=magento)](https://magento.com)
[![Elasticsearch](https://img.shields.io/badge/elasticsearch-7.x%20%7C%208.x-brightgreen.svg?logo=elasticsearch)](https://www.elastic.co)
[![OpenSearch](https://img.shields.io/badge/opensearch-1.x%20%7C%202.x-brightgreen.svg?logo=opensearch)](https://opensearch.org)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Overview

Adds a configurable stock status filter to Magento layered navigation (category page). The filter is fully integrated with Elasticsearch/OpenSearch and supports custom positioning, configurable URL parameters, and multi-language labels.

## Features

- ✅ **Full Elasticsearch/OpenSearch Integration** — filter works via search engine aggregates, no performance impact
- ✅ **Configurable Filter Position** — place filter at the beginning, end, after specific attribute, or at custom position
- ✅ **Custom URL Parameter** — change URL parameter name (default: `stock_status`)
- ✅ **Selectable Options** — show "In Stock", "Out of Stock", or both
- ✅ **Custom Filter Title** — change the filter display name in layered navigation
- ✅ **Admin Configuration** — all settings available in Stores → Configuration → Catalog → Layered Navigation
- ✅ **Multi-language Ready** — includes English and German translations
- ✅ **Magento 2.4.7+** — compatible with OpenSearch and Elasticsearch

## Requirements

- Magento 2.4.7 or higher
- Elasticsearch 7.x / 8.x or OpenSearch

## Installation guide
This module is available on [Github](https://github.com/buhmann/StockStatus).
```bash 
composer require buhmann/module-stock-status
```

 ```
php bin/magento module:enable Buhmann_StockStatus
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento indexer:reindex
php bin/magento cache:flush
  ```
