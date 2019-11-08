# Freeway analystic

資料來源為 http://tisvcloud.freeway.gov.tw/history/TDCS/M06A/ 各旅次路徑原始資料


# 建置步驟

1. 確認環境已備有
    1. PHP 7.2 以上
    1. PHP 需安裝 mysql\_xdevapi module
    1. mysql 8.0 以上
    1. composer(PHP 套件管理程式)
1. 修改 .evn.example 內的 DB\_DATABASE/DB\_USERNAME/DB\_PASSWORD/DB\_HOST 等資料庫連線資訊，並複製為 `.evn` 提供本機系統使用
1. `$ composer install`
1. `$ php artisan migrate`
1. 下載好原始資料並解壓縮
1. `$ php artisan freeway:import [解壓縮路徑包含至 M06A/]`

# 建置步驟(2)

如果你有 `docker` 可以直接用 `docker-compose up -d` 啟動，如果不要跑在背景 `docker-compose up`

匯入資料就變成

1. `$ docker-compose exec web php artisan migrate`
1. 下載好原始資料並解壓縮
1. `$ docker-compose exec web php artisan freeway:import [解壓縮路徑包含至 M06A/]`


# 疑難排解

1. 如果在 100% 之前程式就自行停止，且沒有任何錯誤訊息，確認一下進度條後面的記憶體使用量，與 `php -i | grep memory_limit` 是否相符，是的話請先從 `$ php --ini` 取得你的 PHP 食用的設定檔，並調整可使用的記憶體量

### mysql\_xdevapi 安裝說明

1. 有安裝 pecl 管理程式的話，直接執行 `$ pecl install mysql_xdevapi`
1. 沒有 pecl 管理程式
    1. 請下載 PHP 主程式原始碼，下一個步驟會使用到裡面 bin/phpize
    1. 下載 mysql\_xdevapi 原始碼，並在專案目錄執行 `$ /path/to/your/phpize`
    1. 順利產生 configure 檔案後，執行該檔案 `$ ./configure`
    1. 順利執行完成後，執行指令 `$ make install`，指令順利的話，就會幫你把 module 放在該放的位子
    1. 根據 `$ php --ini` 的結果，調整你的 ini 設定，讓 php 載入剛剛安裝好的 module
