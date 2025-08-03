# MindMate

MindMate is a comprehensive web-based mental health platform designed to help users track their moods, journal their thoughts, and access wellness tools. Built with PHP, JavaScript, and MySQL, MindMate provides a supportive environment for users to manage their mental well-being.

## Features

- **Mood Tracking**: Log daily moods, view trends, and identify emotional patterns over time.
- **Journal**: Secure, private digital journaling with easy entry management.
- **Wellness Exercises**: Access guided meditations, breathing exercises, and mindfulness activities.
- **Chat Support**:  Engage in supportive chat for mental health guidance.
- **User Authentication**: Secure registration and login system.

## Project Structure

```
├── api/            # Backend PHP APIs (mood, journal, chat)
├── auth/           # Authentication pages (login, register, logout)
├── config/         # Configuration files (e.g., database connection)
├── css/            # Stylesheets for UI
├── database/       # Database schema and migrations
├── images/         # UI and avatar images
├── includes/       # Shared PHP includes (header, footer, auth)
├── js/             # JavaScript for interactivity (mood tracker, journal, chat, etc.)
├── logs/           # Log files
├── pages/          # Main user-facing pages (mood tracker, journal, chat, exercises)
├── scripts/        # Utility and setup scripts
├── sounds/         # Audio files for mindfulness/meditation
├── vendor/         # Composer dependencies
├── .env            # Environment variables
├── composer.json   # PHP Composer dependencies
├── index.php       # Landing page
```

## Installation

1. **Clone the repository**

   ```bash
   git clone <repo-url>
   cd mindmate
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Set up environment variables**

   - Copy `.env.example` to `.env` and configure your database and environment settings.

4. **Set up the database**

   - Import the schema in `database/schema.sql` into your MySQL server:
     ```bash
     mysql -u <username> -p < database/schema.sql
     ```

5. **Run locally**

   - Use XAMPP or another local server to host the project directory.
   - Access via `http://localhost/mindmate` in your browser.

## Dependencies

- PHP 7.4+
- MySQL/MariaDB
- Composer
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) (for environment variables)
- JavaScript (with Chart.js for mood tracking visualization)
- CSS (custom styles, Font Awesome for icons)

## Usage

- Register or log in to your account.
- Track your daily mood and view trends.
- Write and manage journal entries.
- Explore wellness exercises and mindfulness tools.
- (Optional) Use chat support if enabled.

## Security & Privacy

- User data is protected with secure authentication and password hashing.
- Journal entries and mood logs are private to each user.

## Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License

This project is licensed under the MIT License.

---

*MindMate – Supporting your mental wellness journey, every day.*
