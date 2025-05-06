# 🧠 MindMate: Mental Health & Wellness Platform

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

<p align="center">
  <img src="https://github.com/PsyCode404/MindMate-Clean/blob/master/images/mindfulness-illustration.svg" alt="MindMate Logo" width="300">
</p>

## 📋 Overview

MindMate is a comprehensive web-based mental health platform designed to help users track their moods, journal their thoughts, and access wellness tools. Built with PHP, JavaScript, and MySQL, MindMate provides a supportive environment for users to manage their mental well-being in a private, secure space.

> *"Supporting your mental wellness journey, every day."*

## ✨ Key Features

- **📊 Mood Tracking** - Log daily moods with customizable intensity levels and notes
  - Visual charts and graphs to identify patterns over time
  - Historical mood data analysis to recognize triggers and improvements

- **📓 Digital Journal** - Secure, private journaling system
  - Rich text formatting with emotion tagging
  - Searchable entries with calendar view

- **🧘 Wellness Exercises** - Guided meditation and mindfulness activities
  - Breathing exercises with visual guidance
  - Timed mindfulness sessions with audio cues

- **💬 AI Chat Support** - Engage with a supportive AI for mental health guidance
  - 24/7 availability for immediate support
  - Evidence-based therapeutic approaches

- **🔒 Privacy-Focused** - Secure user authentication and data protection
  - Password hashing and secure session management
  - Private user data with strict access controls

## 🖥️ Screenshots

<p align="center">
  <i>Screenshots coming soon!</i>
</p>

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/PsyCode404/MindMate-Clean.git
   cd MindMate-Clean
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Set up environment variables**

   ```bash
   # Create .env file (example values shown)
   echo "DB_HOST=localhost
   DB_NAME=mindmate
   DB_USER=root
   DB_PASS=your_password
   APP_ENV=development" > .env
   ```

4. **Set up the database**

   ```bash
   # Import the database schema
   mysql -u root -p < database/schema.sql
   ```

5. **Start your web server**

   ```bash
   # If using PHP's built-in server for testing
   php -S localhost:8000
   
   # Or configure your Apache/Nginx to point to the project directory
   ```

6. **Access the application**
   - Open your browser and navigate to `http://localhost:8000` (or your configured URL)

## 📁 Project Structure

```
MindMate/
├── api/            # Backend APIs for mood, journal, and chat
├── auth/           # Authentication system
├── config/         # Configuration files
├── css/            # Stylesheets and UI components
├── database/       # Database schema and migrations
├── images/         # UI assets and avatars
├── includes/       # Shared PHP components
├── js/             # Frontend JavaScript
├── pages/          # Main application pages
├── scripts/        # Utility scripts
├── sounds/         # Audio files for exercises
├── vendor/         # Composer dependencies
├── .env            # Environment configuration
├── .gitignore      # Git ignore file
├── composer.json   # Composer configuration
├── index.php       # Application entry point
└── README.md       # This documentation
```

## 💻 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: JavaScript, HTML5, CSS3
- **Libraries**:
  - [Chart.js](https://www.chartjs.org/) - For mood visualization
  - [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) - Environment management
  - Font Awesome - UI icons

## 🔧 Development

### Setting Up a Development Environment

1. Follow the installation steps above
2. Enable error reporting in your `.env` file:
   ```
   APP_ENV=development
   DISPLAY_ERRORS=true
   ```

### Running Tests

```bash
# Coming soon
```

## 🤝 Contributing

Contributions are welcome and appreciated! Here's how you can contribute:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Commit** your changes: `git commit -m 'Add some amazing feature'`
4. **Push** to the branch: `git push origin feature/amazing-feature`
5. **Open** a Pull Request

Please make sure to update tests as appropriate and adhere to the existing coding style.

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgements

- [Font Awesome](https://fontawesome.com/) for the icons
- [Chart.js](https://www.chartjs.org/) for data visualization
- All contributors who have helped shape MindMate

---

<p align="center">
  Made with ❤️ by <a href="https://github.com/PsyCode404">PsyCode404</a>
</p>
