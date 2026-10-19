# AI活用研修 練習用リポジトリ

Laravel 12 公式スターターキット(Livewire)に、練習用の業務機能「ライセンス管理」を足したものです。
データはすべて架空です。研修の第1回から第6回まで、このリポジトリを使います。

## 初回のセットアップ

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan test        # 全部緑になることを確認
composer run dev        # http://localhost:8000
```

ログイン: `test@example.com` / `password`
業務機能: http://localhost:8000/licenses

## よく使うコマンド

| 目的 | コマンド |
|---|---|
| テスト | `php artisan test` |
| 整形 | `vendor/bin/pint` |
| 静的解析 | `vendor/bin/phpstan analyse --memory-limit=1G` |
| 期限切れバッチ | `php artisan licenses:expire` |
| DB を見本データに戻す | `php artisan migrate:fresh --seed` |

## 業務機能の場所

- `app/Models/License.php`, `app/Models/LicenseAssignment.php`
- `app/Services/LicenseService.php`
- `app/Http/Controllers/LicenseController.php`
- `app/Console/Commands/ExpireLicenses.php`
- `resources/views/licenses/`
- `tests/Feature/Licenses/`

## 約束

- このリポジトリは自分のプライベートリポジトリとして使います。公開にしないでください
- 実案件のコード・データは入れません
