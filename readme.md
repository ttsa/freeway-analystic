# Freeway analystic

資料來源為 http://tisvcloud.freeway.gov.tw/history/TDCS/M06A/ 各旅次路徑原始資料

# 建置步驟

1. 確認環境已備有 PHP 7.2 以上，以及 mysql 8.0 以上
1. 修改 .evn.example 內的 DB_DATABASE/DB_USERNAME/DB_PASSWORD/DB_HOST 等資料庫連線資訊
1. $ php artisan migrate
1. 下載好原始資料並解壓縮
1. $ php artisan freeway:import [解壓縮路徑包含至 M06A/]
