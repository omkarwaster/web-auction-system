<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Coming Soon</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@600&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f2f3f5;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    .message-box {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 10px 24px rgba(0,0,0,0.1);
      text-align: center;
      animation: fadeSlideIn 0.6s ease forwards;
    }

    @keyframes fadeSlideIn {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h1 {
      font-size: 32px;
      color: #333;
      margin-bottom: 10px;
    }

    p {
      color: #777;
      font-size: 16px;
    }

    a {
      display: inline-block;
      margin-top: 25px;
      text-decoration: none;
      color: white;
      background: #0077cc;
      padding: 10px 20px;
      border-radius: 6px;
      transition: background 0.3s;
    }

    a:hover {
      background: #005fa3;
    }
  </style>
</head>
<body>
  <div class="message-box">
    <h1>🚧 Page Under Development</h1>
    <p>We're working on this feature. Please check back later.</p>
    <a href="logout.php">Logout</a>
  </div>
</body>
</html>
