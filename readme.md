# Freeway analystic

資料來源為 http://tisvcloud.freeway.gov.tw/history/TDCS/M06A/ 各旅次路徑原始資料

# 建置步驟

1. 確認環境已備有
    1. PHP 7.2 以上
    1. mysql 8.0 以上
    1. composer(PHP 套件管理程式)
1. 修改 .evn.example 內的 DB\_DATABASE/DB\_USERNAME/DB\_PASSWORD/DB\_HOST 等資料庫連線資訊
1. `$ composer install`
1. `$ php artisan migrate`
1. 下載好原始資料並解壓縮
1. `$ php artisan freeway:import [解壓縮路徑包含至 M06A/]`

# 疑難排解

1. 如果在 100% 之前程式就自行停止，且沒有任何錯誤訊息，確認一下進度條後面的記憶體使用量，與 `php -i | grep memory_limit` 是否相符，是的話請先從 `$ php --ini` 取得你的 PHP 食用的設定檔，並調整可使用的記憶體量
