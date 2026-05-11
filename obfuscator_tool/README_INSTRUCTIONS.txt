# YAK Pro - PHP Obfuscator Instructions (Windows)

This folder contains the Yakpro-po tool and its dependencies for scrambling your Laravel project code before delivery to clients.

## Folder Structure
- `yakpro-po/`: The core obfuscator script.
- `yakpro-po/PHP-Parser/`: Required dependency for parsing PHP code.
- `yakpro-po.bat`: Shortcut for running the tool on Windows.

## Preparation
1. Ensure **PHP** is installed on your computer and added to your system PATH.
2. Copy `yakpro-po/yakpro-po.cnf` to your Laravel project root (where `artisan` is).

## Important Configuration (Laravel)
In your `yakpro-po.cnf` file, ensure these settings for Laravel stability:
- `obfuscate_variable_names = true`
- `obfuscate_function_names = false` (Laravel routes/controllers need exact names)
- `obfuscate_class_names = false` (Laravel dependency injection needs exact names)
- `obfuscate_interface_names = false`
- `obfuscate_trait_names = false`

## Usage (How to scramble your code)
Open CMD/PowerShell in this `obfuscator_tool` directory and run:

### To scramble the entire 'app' directory:
```bash
yakpro-po.bat ../laravel-app/app -o ../laravel-app/app_obfuscated
```

### To scramble a specific file (e.g., Middleware):
```bash
yakpro-po.bat ../laravel-app/app/Http/Middleware/CheckLicenseActivated.php -o ../laravel-app/app/Http/Middleware/CheckLicenseActivated.php
```

## Workflow for Client Delivery
1. Keep your **original (readable) code** on your developer machine.
2. Run the obfuscation command.
3. Copy only the **obfuscated output** to the client's PC (via pendrive or transfer).
4. The client will never see your readable logic.
