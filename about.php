<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="About the ETech team">
    <title>ETech - About</title>
    <link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/about.css">
</head>
<body>
<?php include_once 'header.inc'; ?>

<main class="container">
    <section class="group-info">
        <h2>Group Information</h2>
        <div class="card">
            <p><strong>Group Name:</strong> ETech</p>
            <p><strong>Group ID:</strong> G1234</p>
            <p><strong>Tutor’s Name:</strong> Vu Ngoc Binh</p>
        </div>
    </section>

    <section class="members">
        <h2>Members’ Contributions</h2>
        <div class="grid">
            <div class="member">
                <img src="styles/images/thanh.jpg" alt="Le Duc Thanh Avatar">
                <p><strong>Le Duc Thanh</strong><br>Home Page + Overviewing</p>
            </div>
            <div class="member">
                <img src="styles/images/anh.jpg" alt="Le Kim Anh Avatar">
                <p><strong>Le Kim Anh</strong><br>Job Page + Additional Functions</p>
            </div>
            <div class="member">
                <img src="styles/images/duong.jpg" alt="Do Tung Duong Avatar">
                <p><strong>Do Tung Duong</strong><br>About + Apply Page + Making Tweaks</p>
            </div>
            <div class="member">
                <img src="styles/images/an.jpg" alt="Trinh Van Hoang An Avatar">
                <p><strong>Trinh Van Hoang An</strong><br>Enhancement + Source Gathering</p>
            </div>
        </div>
    </section>

    <section class="photo">
        <figure>
            <img src="styles/images/group-photo.jpg" alt="ETech Team Photo" style="width: 100%; max-width: 600px; border-radius: 10px;">
            <figcaption>ETech Team</figcaption>
        </figure>
    </section>

    <section class="timetable">
        <h2>Weekly Timetable</h2>
        <table>
            <caption>Shared by All Members</caption>
            <thead>
            <tr>
                <th>Day</th>
                <th>Time</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Monday</td>
                <td>9:00 - 11:00</td>
            </tr>
            <tr>
                <td>Wednesday</td>
                <td>14:00 - 16:00</td>
            </tr>
            </tbody>
        </table>
    </section>

    <section class="contact">
        <h2>Contact Us</h2>
        <p>Email us at: <a href="mailto:group@example.com">group@example.com</a></p>
    </section>

    <section class="profile">
        <h2>Group Profile</h2>
        <div class="card">
            <p>We are a dedicated team of students working together on this web development project.</p>
            <p>Our team has diverse programming skills, including HTML, CSS, JavaScript, Python, Java, C++, PHP, and SQL.</p>
            <p>Favorite Books: "Clean Code", "Introduction to Algorithms", "Design Patterns"</p>
        </div>
    </section>
</main>

<?php include_once 'footer.inc'; ?>
</body>
</html>