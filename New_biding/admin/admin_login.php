<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<style>
body{
  font-family:Arial;
  background:#f2f3f5;
}
.login-box{
  width:380px;
  margin:100px auto;
  background:#fff;
  padding:25px;
  border-radius:10px;
  box-shadow:0 4px 10px rgba(0,0,0,0.1);
}
h2{text-align:center;color:#007bff;}
input{
  width:100%;
  padding:10px;
  margin:10px 0;
  border-radius:6px;
  border:1px solid #ccc;
}
button{
  width:100%;
  padding:10px;
  background:#007bff;
  color:white;
  border:none;
  border-radius:6px;
  font-weight:bold;
}
button:hover{background:#0056b3;}
</style>
</head>
<body>

<div class="login-box">
  <h2>Admin Login</h2>
  <form method="post" action="admin_auth.php">
    <input type="email" name="email" placeholder="Admin Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</div>

</body>
</html>
