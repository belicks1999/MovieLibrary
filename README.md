# Movie Library Website

A modern, responsive movie library website with contact form, favorites collection, and email functionality.

## Features

- 🎬 **Movie Search**: Search for movies and TV shows using TVMaze API
- ⭐ **Favorites Collection**: Save and manage your favorite movies
- 📧 **Contact Form**: Submit inquiries with email notifications
- 📱 **Responsive Design**: Works on all devices (desktop, tablet, mobile)
- 🎨 **Modern UI**: Dark theme with smooth animations
- ⚡ **Fast Loading**: Optimized with lazy loading and skeleton screens

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP
- **API**: TVMaze API for movie data
- **Email**: PHPMailer with SMTP support

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/belicks1999/MovieLibrary.git
cd MovieLibrary
```

### 2. Configure Environment

Create a `.env` file in the root directory:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=your-email@gmail.com
SMTP_FROM_NAME=Movie Library
USE_SMTP=true
```

**Note**: For Gmail, you need to generate an [App Password](https://myaccount.google.com/apppasswords).

### 3. Install Dependencies (Optional)

If using PHPMailer via Composer:

```bash
composer install
```

### 4. File Permissions

Make sure `submissions.json` is writable:

```bash
chmod 666 submissions.json
```

### 5. Deploy

Upload all files to your web server. Make sure:

- PHP 7.4+ is installed
- `.env` file is uploaded (not in `.gitignore`)
- `submissions.json` exists and is writable

## Project Structure

```
MovieLibrary/
├── assets/
│   ├── app.js          # JavaScript functionality
│   ├── styles.css      # All styles
│   ├── Logos.png       # Logo image
│   └── main.jpg        # Hero background
├── config/
│   └── email_config.php # Email configuration
├── includes/
│   ├── email_helper.php # Email sending functions
│   └── env_loader.php   # Environment variable loader
├── index.php            # Main page
├── submissions.json     # Form submissions storage
├── .env                 # Environment variables (not in repo)
├── .gitignore          # Git ignore rules
└── README.md           # This file
```

## Features Breakdown

### Movie Search

- Real-time search as you type
- Displays results in the same grid as favorites
- Uses TVMaze API for movie data

### Favorites

- Load more movies with "Load More" button
- Remove favorites with X button
- Persistent favorites grid

### Contact Form

- Form validation (frontend + backend)
- Email notifications to user and admins
- Terms & Conditions modal
- Loading state on submit button
- Auto-clear form after submission

### Responsive Design

- Mobile-first approach
- Hamburger menu on mobile
- Responsive grid layouts
- Touch-friendly interactions

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is for educational/assignment purposes.

## Author

Belicks Maxwell

## Repository

https://github.com/belicks1999/MovieLibrary
