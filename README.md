# Study Crew - Your Campus Connection

**Study Crew** is a PHP-based web application designed to connect students for academic support. Students can find peer tutors, become tutors, and manage their learning journey in a collaborative campus environment.

---

## Features

- **User Authentication:** Sign up and log in as a student or assistant (tutor).
- **Role-based Dashboards:** Separate dashboards for students and assistants.
- **Course Management:** Browse, search, and filter courses.
- **Tutor Profiles:** View detailed tutor profiles, bios, and courses taught.
- **Contact System:** Students can contact tutors directly via a secure form.
- **Visit Tracking:** Tutor profile visits are tracked and displayed.
- **Responsive Design:** Mobile-friendly and desktop-ready.
- **Admin/Assistant Features:** Assistants can manage their courses and availability.

---

## Folder Structure

```
study-crew_app/
│
├── api/                   # API endpoints (like send-message.php)
├── includes/              # Reusable components (header.php, footer.php, db_functions.php)
├── style.css              # Main stylesheet
├── modal-styles.css       # Modal-specific styles
├── index.php              # Home page
├── about.php              # About page
├── contact.php            # Contact page
├── courses.php            # Courses listing
├── course-details.php     # Single course details
├── assistant-dashboard.php# Assistant dashboard
├── tutor-details.php      # Tutor profile page
├── config.php             # Configuration and DB connection
├── functions.php          # Helper and business logic functions
└── README.md              # This file
```

---

## Requirements

- PHP 7.4 or higher
- MySQL (tested with XAMPP, port 3306 by default)
- Web browser

---

## Setup Instructions

1. **Clone or Download the Repository**
   - Clone the repo into this folder in your pc - C:\xampp\htdocs\
   ```
   git clone git@github.com:matan-workneh7/study-crew_app.git
   ```

3. **Database Setup**
   - Create a MySQL database named `study_crew`.
   - Import the provided SQL schema (study-crew.sql).
   - Update `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` in `config.php` as needed.
   - Default port is `3306` for XAMPP. Change to `3307` if using default MySQL.

4. **Run Locally**
   - Start Apache and MySQL via XAMPP.
   - Visit [http://localhost/study-crew_app](http://localhost/study-crew_app) in your browser.

---

## Usage

- **Sign Up:** Register as a student or assistant.
- **Login:** Access your dashboard.
- **Browse Courses:** Find courses and available tutors.
- **View Tutor Profiles:** See tutor details and send messages.
- **Become a Tutor:** Assistants can manage their profile and courses.
- **Contact:** Use the contact form for support.

---

## Customization

- **Styling:** Edit `style.css` and `modal-styles.css` for custom themes.
- **Header/Footer:** Update `includes/header.php` and `includes/footer.php` for navigation changes.
- **Add Features:** Extend `functions.php` and `api/` for new business logic.

---

## Troubleshooting

- **Database Connection Errors:** Check your DB credentials and port in `config.php`.
- **Modals Not Displaying:** Ensure modal CSS is loaded and JavaScript is enabled.

---

## Security Notes

- Passwords are hashed before storage.
- User input is sanitized and validated.
- Sessions are used for authentication.

---

## Credits

- Built by students, for students.
- Icons by [Font Awesome](https://fontawesome.com/).
- Special thanks to all contributors and testers.

---

## Contact

For questions or support, use the [Contact Us](contact.php) page in the app.
