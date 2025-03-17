<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Apply for a job at TechConnect">
  <title>Etech - Apply</title>
  <link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/apply.css">
</head>
<body>
<?php include_once 'header.inc'; ?>

<main>
  <section class="form-container">
    <h2>Submit Your Application</h2>
    <form action="process_eoi.php" method="post" novalidate>
      <label for="jobref">Job Reference Number:</label>
      <input type="text" id="jobref" name="job-ref" pattern="[A-Za-z0-9]{5}" required maxlength="6" placeholder="e.g., TC001" value="<?php $value = isset($_GET['job-ref']) ? $_GET['job-ref'] : ""; echo $value;?>">

      <label for="fname">First Name:</label>
      <input type="text" id="fname" name="first_name" pattern="[A-Za-z]{1,20}" required maxlength="20" placeholder="John">

      <label for="lname">Last Name:</label>
      <input type="text" id="lname" name="last_name" pattern="[A-Za-z]{1,20}" required maxlength="20" placeholder="Doe">

      <label for="dob">Date of Birth:</label>
      <input type="text" id="dob" name="dob" pattern="\d{2}/\d{2}/\d{4}" required placeholder="dd/mm/yyyy">

      <label for="gender">Gender:</label>
      <select id="gender" name="gender" required>
        <option value="" disabled selected>Select Gender</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
        <option value="other">Other</option>
      </select>

      <label for="street">Street Address:</label>
      <input type="text" id="street" name="street_address" maxlength="40" required placeholder="123 Tech Lane">

      <label for="suburb">Suburb/Town:</label>
      <input type="text" id="suburb" name="suburb" maxlength="40" required placeholder="Melbourne">

      <label for="state">State:</label>
      <select id="state" name="state" required>
        <option value="" disabled selected>Select State</option>
        <option value="VIC">VIC</option>
        <option value="NSW">NSW</option>
        <option value="QLD">QLD</option>
        <option value="NT">NT</option>
        <option value="WA">WA</option>
        <option value="SA">SA</option>
        <option value="TAS">TAS</option>
        <option value="ACT">ACT</option>1055485051
      </select>

      <label for="postcode">Postcode:</label>
      <input type="text" id="postcode" name="postcode" pattern="\d{4}" required maxlength="4" placeholder="3000">

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required placeholder="you@example.com">

      <label for="phone">Phone:</label>
      <input type="text" id="phone" name="phone" pattern="[\d\s]{8,12}" required placeholder="0412 345 678">

      <label>Skills (Select all that apply):</label>
      <label for="skill1">HTML</label>
      <input type="checkbox" id="skill1" name="skill1" value="HTML">
      <label for="skill2">PHP</label>
      <input type="checkbox" id="skill2" name="skill2" value="PHP">
      <label for="skill3">SQL</label>
      <input type="checkbox" id="skill3" name="skill3" value="SQL">
      <label for="skill4">CSS</label>
      <input type="checkbox" id="skill4" name="skill4" value="CSS">

      <label for="extra_skills">Other Skills:</label>
      <textarea id="extra_skills" name="extra_skills" rows="4" placeholder="List additional skills here..."></textarea>

      <button type="submit">Apply</button>
    </form>
  </section>
</main>

<?php include_once 'footer.inc'; ?>
</body>
</html>