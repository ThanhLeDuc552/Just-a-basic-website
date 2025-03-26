<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Enhancements in Etech website">
  <title>Etech - Enhancements</title>
  <link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
  <link rel="stylesheet" href="styles/style.css"> <!-- General styling -->
  <link rel="stylesheet" href="styles/phpenhancements.css"> <!-- Enhancements page styling -->
</head>
<body>
<?php include_once 'header.inc'; ?>
<main>
  <div class="container">
  <h1>CSS Project Enhancements Documentation</h1>
  <div class="enhancement">
      <h2>Enhancement 1: Responsive Design</h2>
      
      <div class="enhancement-details">
          <h3>Description</h3>
          <p>Goes beyond basics with fluid layouts and media queries.</p>
          
          <h3>CSS Implementation Example</h3>
          <div class="code-snippet">
@media (max-width: 768px) {
    #menu-icon {
        font-size: 1.5rem;
        cursor: pointer;
        display: block;
    }
    
    #menu-toggle:checked ~ nav {
        display: block;
    }

    header nav {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: #faf5fc;
        text-align: center;
        width: 100%;
    }

    header nav a {
        padding: 1rem;
        display: block;
    }

    header nav a:hover {
		background-color: #c683e0;
	}

    .btn-login, .btn-register {
        display: none;
    }
}

/* Desktop styles */
@media (min-width: 768px) {
    #menu-icon, .link {
        display: none;
    }

    .btn-login, .btn-register {
        display: inline-block;
    }

    header nav {
        display: flex;
        justify-content: flex-end;
        flex-direction: row;
        position: static;
        background: transparent;
        box-shadow: none;
        width: auto;
        align-items: center;
    }

    header nav a {
        margin-right: 1.5rem;
        padding: 0;
        display: inline-block;
    }

    header nav a:hover {
        text-decoration: underline;
    }
}
          </div>
          <p><strong>Detailed Implementation Link:</strong> <a href="index.php">Responsiveness in navbar and homepage</a></p>
      </div>
  </div>
  <div class="enhancement">
      <h2>Enhancement 2: Flexbox</h2>
      
      <div class="enhancement-details">
          <h3>Description</h3>
          <p>Implements both flexbox and grid layout.</p>
          <p><strong>Detailed Implementation Link:</strong> <a href="jobs.php">Flexbox and grid implementation</a></p>
      </div>
  </div>
  <div class="enhancement">
    <h2>Enhancement 3: Transform</h2>
    <div class="enhancement-details">
      <h3>Description</h3>
      <p>Use transform for smoother hover effect</p>
      <p><strong>Detailed Implementation Link:</strong> <a href="index.php">Transform implementation (In comment section)</a></p>
    </div>
  </div>
  </div>
</main>
<?php include_once 'footer.inc'; ?>
</body>
</html>