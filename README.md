<h1 align="center">🗺️ Barcelona Space</h1>

<p align="center">
    <em>A website for Harbour.Space students to share their favorite spots around Barcelona</em>
</p>

---

## What is this?

**Barcelona Space** is a website created for Harbour.Space students to share their favorite spots around the city. Discover cafes, restaurants, study spots, and hidden gems recommended by fellow students.

## Why is this useful?

As a Harbour.Space student in Barcelona, you want to discover the best spots in the city. Barcelona Space helps you:

- **Find great places** recommended by fellow students
- **Share your discoveries** with the community
- **Connect with others** who share similar interests

## Prerequisites

To use this website, you'll need:

- **PHP**
- **Composer**
- **Laravel Herd** (recommended for local development)

## How do I get started?

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd barcelona-space
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Create database**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Start the application**
   
   If using Laravel Herd, the site should be automatically available.
   
   Otherwise:
   ```bash
   php artisan serve
   ```

6. **Visit the application** at `http://localhost` (Herd) or `http://localhost:8000`

## Contributing

Contributions are welcome! This is a hobby project for Harbour.Space students. We encourage:

- **UI/UX enhancements**
- **New features** to improve the website experience
- **Bug fixes** and performance improvements

Feel free to open issues or submit pull requests.

## Where can I get help?

If you need help or have questions:

- **Email**: [fdgbatarse@gmail.com](mailto:fdgbatarse@gmail.com)
- **Laravel Documentation**: [https://laravel.com/docs](https://laravel.com/docs)
- **Laravel Herd**: [https://herd.laravel.com/](https://herd.laravel.com/)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
