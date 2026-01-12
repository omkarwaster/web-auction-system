<header>
  <a class="logo" href="index.php">WebAuction</a>

  <!-- Hamburger container -->
  <div id="hamburger-container" aria-haspopup="true">
    <button id="hamburger-toggle" aria-expanded="false" aria-controls="hamburger-menu" title="Open menu">
      <i class="fas fa-bars" aria-hidden="true"></i>
      <span class="sr-only" style="position:absolute;left:-9999px">Menu</span>
    </button>

    <nav id="hamburger-menu" class="hamburger-menu" role="menu" aria-hidden="true">
      <div role="menuitem" onclick="window.location='user_profile.php'">Profile</div>
      <div role="menuitem" onclick="window.location='explore_items.php'">Explore</div>
    </nav>
  </div>
</header>
