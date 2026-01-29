# fitjob-back

## セットアップ手順

```bash
# 1. envファイル作成
cp laravel-app/.env.example laravel-app/.env

# 2. 依存関係インストール & APP_KEY生成
docker compose run --rm api bash
composer install
php artisan key:generate
exit

# 3. コンテナ起動
docker compose build
docker compose up -d

# 4. マイグレーション実行
docker compose exec api php artisan migrate
