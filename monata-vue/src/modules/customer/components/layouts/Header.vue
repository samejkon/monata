<template>
  <header>
    <section class="section-header">
      <div class="menu-toogle-display d-none" id="menu-toogle">
        <i class="icon fa fa-menu" id="close-menu-icon" onclick="toggleMenu()"></i>
        <ul class="menu-toogle-list">
          <li class="menu-toogle-item">Home</li>
          <li class="menu-toogle-item">Rooms</li>
          <li class="menu-toogle-item">About</li>
          <li class="menu-toogle-item">Blog</li>
          <li class="menu-toogle-item">Pages</li>
          <li class="menu-toogle-item">Contact</li>
          <li class="menu-toogle-item">
            <a href="#" class="menu-toogle-item-link text-decor-none" onclick="toggleModal()">Book A Room</a>
          </li>
        </ul>
      </div>
      <nav class="nav-bar">
        <div class="nav-start">
          <a href="#" class="nav-item text-decor-none active-under">Home</a>
          <a href="#" class="nav-item text-decor-none">Rooms</a>
          <a href="#" class="nav-item text-decor-none">About</a>
          <a href="#" class="nav-item text-decor-none">Blog</a>
          <a href="#" class="nav-item text-decor-none">Pages</a>
          <a href="#" class="nav-item text-decor-none">Contact</a>
        </div>
        <div class="logo">
          <img src="@/modules/customer/assets/img/logo.png" alt="motana logo" />
        </div>
        <div class="menu-icon-display" id="menu-icon" onclick="toggleMenu()">
          <img src="../assets/icon/buttonmenu.svg" alt="menu icon" />
        </div>
        <div class="nav-end">
          <div class="group-icon">
            <i class="fa fa-facebook"></i>
            <i class="fa fa-instagram"></i>
            <i class="fa fa-twitter"></i>
          </div>
          <button class="nav-booking" onclick="toggleModal()">
            Book A Room
          </button>
        </div>
      </nav>
      <div class="text-overlay">
        <div>
          <h3 class="text-overlay-heading">Montana Resort</h3>
          <p class="text-overlay-p">Unlock to enjoy the view of Martine</p>
        </div>
      </div>
    </section>
  </header>
</template>
