<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Steo-Step Setup Projeck

Berikut Langkah-langkah untuk menyiapkan & Setup untuk projeck agar bisa berjalan, dimulai dari langkah setup sampai ke code terminalnya Berikut detail langkah-langkahnya :
1. Langkah pertama tuliskan wsl --install di terminal, agar teristall WSL 2 di docker.
2. masuk ke direktori ubuntu \Linuxt\Ubuntu\home dengan klik connect WSL using distro di application VsCode.
3. setelah projeck berada di komputer ketikan di CMD Application dengan menuliskan docker compose up untuk menginstall full projeck.
4. dilanjutkan dengan mengetik CMD docker exec -it laravel-api composer require laravel/sanctum. untuk mengistall composer di projeck
5. dilanjutkan dengan mengetik CMD docker exec -it laravel-api php artisan install:api. untuk API teristall di Projeck.
6. dilanjutkan dengan mengetik CMD docker exec -it laravel-api composer require barryvdh/laravel-dompdf. Digunakan untuk bisa memuat PDF
7. dilanjutkan dengan mengetik CMD docker exec -it laravel-api php artisan storage:link untuk memuat Storage di projeck
8. untuk meng-generate database dilakukan di CMD dengan mengetikan docker exec -it laravel-api php artisan Migrate --seed
9. - - - - -
    
## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
